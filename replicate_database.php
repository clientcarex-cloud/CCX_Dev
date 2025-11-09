<?php
/**
 * Simple database replication utility.
 *
 * Usage:
 *   php replicate_database.php
 *
 * Web UI:
 *   Open replicate_database.php in your browser to access a simple control
 *   panel with a button that streams real-time logs while the copy runs.
 *   Progress is tracked in replicate_state.json so retries resume where they
 *   left off. Delete that file to force a full rebuild.
 *
 * Cron usage:
 *   php replicate_database.php --cron
 *   (Optionally add --log=/path/to/file.log to control log location.)
 *
 * The script expects the following variables to be available either in the environment
 * or in the local .env file located next to this script:
 *
 *   APP_DB_HOSTNAME
 *   APP_DB_USERNAME
 *   APP_DB_PASSWORD
 *   APP_DB_NAME
 *   APP_DB_CHARSET      (optional, defaults to utf8mb4)
 *   APP_DB_COLLATION    (optional, defaults to utf8mb4_unicode_ci)
 *
 *   REPL_TARGET_DB_HOSTNAME
 *   REPL_TARGET_DB_USERNAME
 *   REPL_TARGET_DB_PASSWORD
 *   REPL_TARGET_DB_NAME
 *   REPL_TARGET_DB_CHARSET   (optional, defaults to utf8mb4)
 *   REPL_TARGET_DB_COLLATION (optional, defaults to utf8mb4_unicode_ci)
 *
 * A full copy from the source database into the target database will be performed.
 * All tables, views, and triggers currently present in the target database will be
 * dropped/replaced.
 */

declare(strict_types=1);

ini_set('memory_limit', '-1');
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_time_limit(0);

if (!defined('STDOUT')) {
    $stdout = fopen('php://output', 'w');
    if ($stdout === false) {
        throw new RuntimeException('Unable to open stdout stream.');
    }
    define('STDOUT', $stdout);
}

$GLOBALS['REPL_LOG_HANDLE'] = null;
$GLOBALS['REPL_SUPPRESS_STDOUT'] = false;

bootstrap(__DIR__);

/**
 * Application entry point.
 */
function main(string $rootDir): void
{
    loadEnvFile($rootDir . DIRECTORY_SEPARATOR . '.env');

    $source = buildConnectionConfig('APP_DB_');
    $target = buildConnectionConfig('REPL_TARGET_DB_');

    validateDistinctDatabases($source, $target);

    $sourcePdo = createPdoConnection($source);
    $targetPdo = createPdoConnection($target);

    relaxSqlMode($sourcePdo);
    relaxSqlMode($targetPdo);

    $progress = new ReplicationProgress($rootDir . DIRECTORY_SEPARATOR . 'replicate_state.json');

    $copier = new DatabaseCopier(
        $sourcePdo,
        $targetPdo,
        $progress,
        $source['name'],
        $target['name']
    );
    $copier->run();

    $progress->clear();

    emit_log();
    emit_log('Database copy completed successfully.');
}

/**
 * Reads a .env file and populates the current process environment.
 */
function loadEnvFile(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException("Unable to read .env file at {$path}");
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        [$key, $value] = $parts;
        $key = trim($key);
        $value = trim($value);

        if ($value === '') {
            $parsedValue = '';
        } else {
            $value = trim($value);
            if (
                (starts_with($value, '"') && ends_with($value, '"')) ||
                (starts_with($value, "'") && ends_with($value, "'"))
            ) {
                $parsedValue = substr($value, 1, -1);
            } else {
                $parsedValue = $value;
            }
        }

        putenv("{$key}={$parsedValue}");
        $_ENV[$key] = $parsedValue;
        $_SERVER[$key] = $parsedValue;
    }
}

/**
 * Builds a DB config array by reading prefixed environment variables.
 *
 * @return array{
 *     host: string,
 *     username: string,
 *     password: string,
 *     name: string,
 *     charset: string,
 *     collation: string
 * }
 */
function buildConnectionConfig(string $prefix): array
{
    $required = [
        'host' => $prefix . 'HOSTNAME',
        'username' => $prefix . 'USERNAME',
        'password' => $prefix . 'PASSWORD',
        'name' => $prefix . 'NAME',
    ];

    $config = [];
    foreach ($required as $key => $envKey) {
        $value = getenv($envKey);
        if ($value === false || $value === '') {
            throw new InvalidArgumentException("Missing required environment variable: {$envKey}");
        }
        $config[$key] = $value;
    }

    $config['charset'] = getenv($prefix . 'CHARSET') ?: 'utf8mb4';
    $config['collation'] = getenv($prefix . 'COLLATION') ?: 'utf8mb4_unicode_ci';

    return $config;
}

/**
 * Ensures the source and target databases are not the same.
 */
function validateDistinctDatabases(array $source, array $target): void
{
    if (
        $source['host'] === $target['host'] &&
        $source['name'] === $target['name'] &&
        $source['username'] === $target['username']
    ) {
        throw new InvalidArgumentException('Source and target database appear to be identical.');
    }
}

/**
 * Creates a PDO connection using the provided configuration.
 *
 * @param array{
 *     host: string,
 *     username: string,
 *     password: string,
 *     name: string,
 *     charset: string,
 *     collation: string
 * } $config
 */
function createPdoConnection(array $config): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['name'],
        $config['charset']
    );

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = sprintf(
            "SET NAMES %s COLLATE %s",
            $config['charset'],
            $config['collation']
        );
    }

    return new PDO($dsn, $config['username'], $config['password'], $options);
}

function relaxSqlMode(PDO $pdo): void
{
    try {
        $result = $pdo->query('SELECT @@SESSION.sql_mode');
        if ($result === false) {
            return;
        }

        $current = (string) $result->fetchColumn();
        if ($current === '') {
            return;
        }

        $modes = array_filter(array_map('trim', explode(',', $current)));
        $blocked = [
            'STRICT_TRANS_TABLES',
            'STRICT_ALL_TABLES',
            'NO_ZERO_DATE',
            'NO_ZERO_IN_DATE',
        ];

        $filtered = array_values(array_diff($modes, $blocked));
        $newMode = implode(',', $filtered);
        $escaped = str_replace("'", "''", $newMode);

        $pdo->exec("SET SESSION sql_mode='{$escaped}'");
    } catch (Throwable $exception) {
        emit_log('Warning: Unable to adjust SQL mode - ' . $exception->getMessage());
    }
}

/**
 * Handles the replication process.
 */
final class ReplicationProgress
{
    private array $state = [
        'tables_completed' => [],
        'views_completed' => [],
        'triggers_completed' => [],
    ];

    public function __construct(private readonly string $path)
    {
        $this->load();
    }

    public function isTableCompleted(string $name): bool
    {
        return !empty($this->state['tables_completed'][$name]);
    }

    public function markTableCompleted(string $name): void
    {
        if ($this->isTableCompleted($name)) {
            return;
        }

        $this->state['tables_completed'][$name] = true;
        $this->save();
    }

    public function forgetTable(string $name): void
    {
        if (!empty($this->state['tables_completed'][$name])) {
            unset($this->state['tables_completed'][$name]);
            $this->save();
        }
    }

    public function isViewCompleted(string $name): bool
    {
        return !empty($this->state['views_completed'][$name]);
    }

    public function markViewCompleted(string $name): void
    {
        if ($this->isViewCompleted($name)) {
            return;
        }

        $this->state['views_completed'][$name] = true;
        $this->save();
    }

    public function forgetView(string $name): void
    {
        if (!empty($this->state['views_completed'][$name])) {
            unset($this->state['views_completed'][$name]);
            $this->save();
        }
    }

    public function isTriggerCompleted(string $name): bool
    {
        return !empty($this->state['triggers_completed'][$name]);
    }

    public function markTriggerCompleted(string $name): void
    {
        if ($this->isTriggerCompleted($name)) {
            return;
        }

        $this->state['triggers_completed'][$name] = true;
        $this->save();
    }

    public function forgetTrigger(string $name): void
    {
        if (!empty($this->state['triggers_completed'][$name])) {
            unset($this->state['triggers_completed'][$name]);
            $this->save();
        }
    }

    public function clear(): void
    {
        $this->state = [
            'tables_completed' => [],
            'views_completed' => [],
            'triggers_completed' => [],
        ];

        if (is_file($this->path)) {
            @unlink($this->path);
        }
    }

    private function load(): void
    {
        if (!is_file($this->path) || !is_readable($this->path)) {
            return;
        }

        $raw = file_get_contents($this->path);
        if ($raw === false || trim($raw) === '') {
            return;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return;
        }

        foreach (['tables_completed', 'views_completed', 'triggers_completed'] as $key) {
            if (isset($decoded[$key]) && is_array($decoded[$key])) {
                // Normalize to associative array for quick lookups.
                $this->state[$key] = [];
                foreach ($decoded[$key] as $name => $value) {
                    if (is_string($name)) {
                        $this->state[$key][$name] = (bool) $value;
                    } elseif (is_string($value)) {
                        $this->state[$key][$value] = true;
                    }
                }
            }
        }
    }

    private function save(): void
    {
        $json = json_encode(
            $this->state,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new RuntimeException('Unable to encode replication progress to JSON.');
        }

        if (file_put_contents($this->path, $json, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write replication progress file.');
        }
    }
}

final class DatabaseCopier
{
    private const CHUNK_SIZE = 2000;

    public function __construct(
        private readonly PDO $source,
        private readonly PDO $target,
        private readonly ReplicationProgress $progress,
        private readonly string $sourceDatabase,
        private readonly string $targetDatabase
    ) {
    }

    public function run(): void
    {
        $this->target->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            $tables = $this->getTables('BASE TABLE');
            $views = $this->getTables('VIEW');

            $this->copyTables($tables);
            $this->copyViews($views);
            $this->copyTriggers();
        } finally {
            $this->target->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * @return array<int, string>
     */
    private function getTables(string $type): array
    {
        $sql = "SHOW FULL TABLES WHERE Table_type = :type";
        $stmt = $this->source->prepare($sql);
        $stmt->execute(['type' => $type]);

        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        return $tables;
    }

    /**
     * @param array<int, string> $tables
     */
    private function copyTables(array $tables): void
    {
        foreach ($tables as $table) {
            $hasTarget = $this->targetHasBaseTable($table);

            if ($this->progress->isTableCompleted($table) && $hasTarget) {
                emit_log("Skipping table {$table} (already replicated).");
                continue;
            }

            if ($this->progress->isTableCompleted($table) && !$hasTarget) {
                $this->progress->forgetTable($table);
            }

            $totalRows = $this->countRows($table);
            emit_log("Copying table {$table} ({$totalRows} rows)...");

            $this->target->exec(sprintf('DROP TABLE IF EXISTS `%s`', $table));

            $createSql = $this->getCreateStatement($table, false);
            $this->target->exec($createSql);

            $columns = $this->getColumnNames($table);
            if (empty($columns)) {
                emit_log("Finished table {$table} (no columns detected).");
                $this->progress->markTableCompleted($table);
                continue;
            }

            $selectStmt = $this->source->prepare(sprintf('SELECT * FROM `%s`', $table));
            $this->disableBufferedQuery($selectStmt);
            $selectStmt->execute();

            $batch = [];
            $processed = 0;
            $inTransaction = false;

            try {
                if (!$this->target->inTransaction()) {
                    $this->target->beginTransaction();
                    $inTransaction = true;
                }

                while ($row = $selectStmt->fetch(PDO::FETCH_ASSOC)) {
                    $batch[] = array_values($row);

                    if (count($batch) >= self::CHUNK_SIZE) {
                        $this->bulkInsert($table, $columns, $batch);
                        $processed += count($batch);
                        $this->emitProgress($table, $processed, $totalRows);
                        $batch = [];
                    }
                }

                if (!empty($batch)) {
                    $this->bulkInsert($table, $columns, $batch);
                    $processed += count($batch);
                    $this->emitProgress($table, $processed, $totalRows);
                }

                if ($processed < $totalRows) {
                    $this->emitProgress($table, $totalRows, $totalRows);
                }

                if ($inTransaction) {
                    $this->target->commit();
                }
            } catch (Throwable $exception) {
                if ($inTransaction && $this->target->inTransaction()) {
                    $this->target->rollBack();
                }
                throw $exception;
            }

            emit_log("Finished table {$table} ({$processed} rows copied).");
            $this->progress->markTableCompleted($table);
        }
    }

    private function disableBufferedQuery(PDOStatement $stmt): void
    {
        if (!defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
            return;
        }

        try {
            $stmt->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        } catch (PDOException $e) {
            // Some drivers (e.g., mysqlnd on older PHP builds) do not support toggling this attribute.
        }
    }

    private function countRows(string $table): int
    {
        $stmt = $this->source->query(sprintf('SELECT COUNT(*) AS total FROM `%s`', $table));
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return (int) ($result['total'] ?? 0);
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, array<int|null|string>> $rows
     */
    private function bulkInsert(string $table, array $columns, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $columnCount = count($columns);
        if ($columnCount === 0) {
            return;
        }

        $maxPlaceholders = 60000;
        $maxRowsPerInsert = max(1, intdiv($maxPlaceholders, $columnCount));
        if (count($rows) > $maxRowsPerInsert) {
            foreach (array_chunk($rows, $maxRowsPerInsert) as $chunk) {
                $this->bulkInsert($table, $columns, $chunk);
            }
            return;
        }

        $columnList = implode(',', array_map(static fn ($col) => "`{$col}`", $columns));
        $rowPlaceholder = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $placeholderGroups = implode(',', array_fill(0, count($rows), $rowPlaceholder));

        $flattened = [];
        foreach ($rows as $row) {
            foreach ($row as $value) {
                $flattened[] = $value;
            }
        }

        $sql = sprintf('INSERT INTO `%s` (%s) VALUES %s', $table, $columnList, $placeholderGroups);
        $stmt = $this->target->prepare($sql);
        $stmt->execute($flattened);
    }

    private function emitProgress(string $table, int $processed, int $total): void
    {
        if ($total > 0) {
            $clamped = min($processed, $total);
            $percent = number_format(($clamped / $total) * 100, 2);
            $remaining = max($total - $clamped, 0);
            emit_log(sprintf('[%s] %d / %d rows (%s%%, %d remaining)', $table, $clamped, $total, $percent, $remaining));
        } else {
            emit_log(sprintf('[%s] %d rows copied', $table, $processed));
        }
    }

    private function targetHasBaseTable(string $table): bool
    {
        $sql = 'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND TABLE_TYPE = :type';
        $stmt = $this->target->prepare($sql);
        $stmt->execute([
            'schema' => $this->targetDatabase,
            'table' => $table,
            'type' => 'BASE TABLE',
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function targetHasView(string $view): bool
    {
        $sql = 'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND TABLE_TYPE = :type';
        $stmt = $this->target->prepare($sql);
        $stmt->execute([
            'schema' => $this->targetDatabase,
            'table' => $view,
            'type' => 'VIEW',
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function targetHasTrigger(string $trigger): bool
    {
        $sql = 'SELECT COUNT(*) FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = :schema AND TRIGGER_NAME = :trigger';
        $stmt = $this->target->prepare($sql);
        $stmt->execute([
            'schema' => $this->targetDatabase,
            'trigger' => $trigger,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * @return array<string, bool>
     */
    private function getTargetViews(): array
    {
        $sql = 'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = :schema AND TABLE_TYPE = :type';
        $stmt = $this->target->prepare($sql);
        $stmt->execute([
            'schema' => $this->targetDatabase,
            'type' => 'VIEW',
        ]);

        $views = [];
        while ($name = $stmt->fetchColumn()) {
            if (is_string($name)) {
                $views[$name] = true;
            }
        }

        return $views;
    }

    /**
     * @return array<string, bool>
     */
    private function getTargetTriggers(): array
    {
        $sql = 'SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = :schema';
        $stmt = $this->target->prepare($sql);
        $stmt->execute([
            'schema' => $this->targetDatabase,
        ]);

        $triggers = [];
        while ($name = $stmt->fetchColumn()) {
            if (is_string($name)) {
                $triggers[$name] = true;
            }
        }

        return $triggers;
    }

    /**
     * @param array<int, string> $views
     */
    private function copyViews(array $views): void
    {
        $existingTargetViews = $this->getTargetViews();
        $existingNames = array_keys($existingTargetViews);

        $obsolete = array_diff($existingNames, $views);
        foreach ($obsolete as $view) {
            emit_log("Dropping target-only view {$view}...");
            $this->target->exec(sprintf('DROP VIEW IF EXISTS `%s`', $view));
            $this->progress->forgetView($view);
            unset($existingTargetViews[$view]);
        }

        foreach ($views as $view) {
            $hasTarget = isset($existingTargetViews[$view]) && $this->targetHasView($view);

            if ($this->progress->isViewCompleted($view) && $hasTarget) {
                emit_log("Skipping view {$view} (already replicated).");
                continue;
            }

            if ($this->progress->isViewCompleted($view) && !$hasTarget) {
                $this->progress->forgetView($view);
            }

            emit_log("Copying view {$view}...");
            $this->target->exec(sprintf('DROP VIEW IF EXISTS `%s`', $view));

            $createSql = $this->getCreateStatement($view, true);
            $this->target->exec($createSql);
            $this->progress->markViewCompleted($view);
            $existingTargetViews[$view] = true;
            emit_log("Finished view {$view}.");
        }
    }

    private function copyTriggers(): void
    {
        $triggerNames = $this->source->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
        $existingTargetTriggers = $this->getTargetTriggers();
        $targetTriggerNames = array_keys($existingTargetTriggers);

        $obsolete = array_diff($targetTriggerNames, $triggerNames);
        foreach ($obsolete as $trigger) {
            emit_log("Dropping target-only trigger {$trigger}...");
            $this->target->exec(sprintf('DROP TRIGGER IF EXISTS `%s`', $trigger));
            $this->progress->forgetTrigger($trigger);
            unset($existingTargetTriggers[$trigger]);
        }

        foreach ($triggerNames as $trigger) {
            $hasTarget = isset($existingTargetTriggers[$trigger]) && $this->targetHasTrigger($trigger);

            if ($this->progress->isTriggerCompleted($trigger) && $hasTarget) {
                emit_log("Skipping trigger {$trigger} (already replicated).");
                continue;
            }

            if ($this->progress->isTriggerCompleted($trigger) && !$hasTarget) {
                $this->progress->forgetTrigger($trigger);
            }

            emit_log("Copying trigger {$trigger}...");
            $createStmt = $this->source->query(sprintf('SHOW CREATE TRIGGER `%s`', $trigger));
            $row = $createStmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !isset($row['SQL Original Statement'])) {
                continue;
            }

            $createSql = $this->stripDefiner($row['SQL Original Statement']);
            $this->target->exec(sprintf('DROP TRIGGER IF EXISTS `%s`', $trigger));
            $this->target->exec($createSql);
            $this->progress->markTriggerCompleted($trigger);
            $existingTargetTriggers[$trigger] = true;
            emit_log("Finished trigger {$trigger}.");
        }
    }

    /**
     * @return array<int, string>
     */
    private function getColumnNames(string $table): array
    {
        $stmt = $this->source->prepare(sprintf('DESCRIBE `%s`', $table));
        $stmt->execute();

        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }

    private function getCreateStatement(string $object, bool $isView): string
    {
        $sql = $isView
            ? sprintf('SHOW CREATE VIEW `%s`', $object)
            : sprintf('SHOW CREATE TABLE `%s`', $object);

        $stmt = $this->source->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new RuntimeException("Unable to fetch CREATE statement for {$object}");
        }

        $key = $isView ? 'Create View' : 'Create Table';
        if (!isset($row[$key])) {
            throw new RuntimeException("CREATE statement missing expected key for {$object}");
        }

        $createSql = $row[$key];
        return $this->stripDefiner($createSql);
    }

    private function stripDefiner(string $sql): string
    {
        return preg_replace('/\sDEFINER=`[^`]+`@`[^`]+`\s/', ' ', $sql) ?? $sql;
    }
}

function bootstrap(string $rootDir): void
{
    if (PHP_SAPI === 'cli') {
        runCli($rootDir, $GLOBALS['argv'] ?? []);
        return;
    }

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (strtoupper($requestMethod) === 'POST') {
        $mode = getPostMode();

        if ($mode === 'cron') {
            handleCronTriggerRequest($rootDir);
            return;
        }

        if ($mode === 'compare') {
            handleComparisonRequest($rootDir);
            return;
        }

        prepareStreamingResponse();
        emit_log('Starting database replication...');

        try {
            main($rootDir);
        } catch (Throwable $exception) {
            if (!headers_sent()) {
                http_response_code(500);
            }
            emit_log('ERROR: ' . $exception->getMessage());
            emit_log(sprintf('Location: %s:%d', $exception->getFile(), $exception->getLine()));
        }

        return;
    }

    renderControlPanel($rootDir);
}

function prepareStreamingResponse(): void
{
    if (!headers_sent()) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');
    }

    @ini_set('output_buffering', 'off');
    @ini_set('zlib.output_compression', '0');

    while (ob_get_level() > 0) {
        @ob_end_flush();
    }

    ob_implicit_flush(true);
    echo str_repeat(' ', 2048);
    flush();
}

function renderControlPanel(string $rootDir): void
{
    loadEnvFile($rootDir . DIRECTORY_SEPARATOR . '.env');

    $sourceName = getenv('APP_DB_NAME') ?: 'Not configured';
    $sourceHost = getenv('APP_DB_HOSTNAME') ?: 'Not configured';
    $targetName = getenv('REPL_TARGET_DB_NAME') ?: 'Not configured';
    $targetHost = getenv('REPL_TARGET_DB_HOSTNAME') ?: 'Not configured';

    $escape = static function (?string $value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    };

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Database Replication Utility</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                margin: 40px;
                color: #1f2933;
                background-color: #f8f9fb;
            }
            h1 {
                margin-top: 0;
            }
            .panel {
                background: #fff;
                border-radius: 8px;
                padding: 24px;
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
            }
            .env-details {
                margin-bottom: 20px;
                padding: 16px;
                border: 1px solid #d2d6dc;
                border-radius: 6px;
                background-color: #f1f5f9;
                font-size: 14px;
            }
            .controls {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
            }
            button {
                padding: 12px 24px;
                font-size: 16px;
                border: none;
                border-radius: 6px;
                background-color: #2563eb;
                color: #fff;
                cursor: pointer;
                transition: background-color 0.2s ease;
            }
            button.secondary {
                background-color: #475467;
            }
            button.ghost {
                background-color: #334155;
            }
            button:hover:not(:disabled) {
                background-color: #1d4ed8;
            }
            button.secondary:hover:not(:disabled) {
                background-color: #334155;
            }
            button.ghost:hover:not(:disabled) {
                background-color: #1f2937;
            }
            button:disabled {
                background-color: #94a3b8;
                cursor: not-allowed;
            }
            #status {
                margin-left: 12px;
                font-weight: 600;
            }
            pre {
                margin-top: 24px;
                padding: 16px;
                background: #0f172a;
                color: #e2e8f0;
                max-height: 480px;
                overflow-y: auto;
                border-radius: 6px;
                font-size: 13px;
                line-height: 1.5;
            }
            .note {
                margin-top: 16px;
                font-size: 14px;
                color: #475467;
            }
        </style>
    </head>
    <body>
        <div class="panel">
            <h1>Database Replication Utility</h1>
            <p class="note">
                Press the button below to overwrite the target database with a fresh copy of the source database.
                Make sure no critical operations are running before starting the replication. Use the
                <em>Queue Cron Copy</em> button to launch the same process in the background (ideal for long runs).
            </p>
            <div class="env-details">
                <div><strong>Source:</strong> <?php echo $escape($sourceHost); ?> / <?php echo $escape($sourceName); ?></div>
                <div><strong>Target:</strong> <?php echo $escape($targetHost); ?> / <?php echo $escape($targetName); ?></div>
            </div>
            <div class="controls">
                <button id="replicate-btn" type="button">Start Database Copy</button>
                <button id="cron-btn" type="button" class="secondary">Queue Cron Copy</button>
                <button id="compare-btn" type="button" class="ghost">Compare Databases</button>
                <span id="status">Idle</span>
            </div>
            <pre id="log" aria-live="polite"></pre>
        </div>
        <script>
            (function () {
                const button = document.getElementById('replicate-btn');
                const cronButton = document.getElementById('cron-btn');
                const compareButton = document.getElementById('compare-btn');
                const status = document.getElementById('status');
                const log = document.getElementById('log');

                function setButtonsDisabled(value) {
                    button.disabled = value;
                    cronButton.disabled = value;
                    compareButton.disabled = value;
                }

                function formatComparisonSummary(summary) {
                    const lines = [];
                    lines.push(`Comparison summary (${summary.generated_at || 'unknown time'})`);
                    lines.push(`Source DB: ${summary.source_database} (tables: ${summary.source_table_count})`);
                    lines.push(`Target DB: ${summary.target_database} (tables: ${summary.target_table_count})`);
                    if (typeof summary.checked_tables === 'number') {
                        lines.push(`Checked tables: ${summary.checked_tables}`);
                    }
                    lines.push('');

                    if (summary.missing_in_target && summary.missing_in_target.length > 0) {
                        lines.push('Tables missing in target:');
                        summary.missing_in_target.slice(0, 50).forEach((table) => lines.push(`  - ${table}`));
                        if (summary.missing_in_target.length > 50) {
                            lines.push(`  …and ${summary.missing_in_target.length - 50} more`);
                        }
                        lines.push('');
                    } else {
                        lines.push('No tables missing in target.');
                    }

                    if (summary.missing_in_source && summary.missing_in_source.length > 0) {
                        lines.push('Tables missing in source:');
                        summary.missing_in_source.slice(0, 50).forEach((table) => lines.push(`  - ${table}`));
                        if (summary.missing_in_source.length > 50) {
                            lines.push(`  …and ${summary.missing_in_source.length - 50} more`);
                        }
                        lines.push('');
                    } else {
                        lines.push('No tables missing in source.');
                    }

                    if (summary.differences && summary.differences.length > 0) {
                        lines.push('Row / checksum differences:');
                        summary.differences.forEach((diff) => {
                            const srcRows = diff.source_rows ?? 'N/A';
                            const tgtRows = diff.target_rows ?? 'N/A';
                            const delta = diff.row_difference ?? 'N/A';
                            const rowInfo = `rows ${srcRows} vs ${tgtRows} (Δ ${delta})`;
                            let checksumInfo = '';
                            if (diff.checksum_status === 'match') {
                                checksumInfo = 'checksum: match';
                            } else if (diff.checksum_status === 'mismatch') {
                                checksumInfo = `checksum mismatch (${diff.checksum_source ?? 'N/A'} vs ${diff.checksum_target ?? 'N/A'})`;
                            } else {
                                checksumInfo = `checksum: ${diff.checksum_status}`;
                            }
                            lines.push(`  - ${diff.table}: ${rowInfo}; ${checksumInfo}`);
                            if (diff.notes && diff.notes.length) {
                                diff.notes.forEach((note) => lines.push(`      note: ${note}`));
                            }
                        });
                    } else {
                        lines.push('No row-count or checksum differences detected among shared tables.');
                    }

                    if (summary.warnings && summary.warnings.length > 0) {
                        lines.push('');
                        lines.push('Warnings:');
                        summary.warnings.forEach((warn) => lines.push(`  - ${warn}`));
                    }

                    return lines.join('\n');
                }

                async function runReplication() {
                    if (!confirm('This will overwrite the target database. Continue?')) {
                        return;
                    }

                    setButtonsDisabled(true);
                    log.textContent = '';
                    status.textContent = 'Running...';

                    try {
                        const body = new URLSearchParams({mode: 'web'});
                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                            },
                            body: body.toString()
                        });

                        const reader = response.body ? response.body.getReader() : null;
                        const decoder = new TextDecoder();

                        if (reader) {
                            let done = false;
                            while (!done) {
                                const result = await reader.read();
                                done = result.done;
                                if (result.value) {
                                    log.textContent += decoder.decode(result.value, {stream: !done});
                                    log.scrollTop = log.scrollHeight;
                                }
                            }
                        } else {
                            const text = await response.text();
                            log.textContent += text;
                        }

                        if (response.ok) {
                            status.textContent = 'Completed';
                        } else {
                            status.textContent = 'Failed';
                        }
                    } catch (error) {
                        log.textContent += '\nERROR: ' + error.message + '\n';
                        status.textContent = 'Failed';
                    } finally {
                        setButtonsDisabled(false);
                    }
                }

                async function runComparison() {
                    setButtonsDisabled(true);
                    status.textContent = 'Comparing...';
                    log.textContent = '';

                    try {
                        const body = new URLSearchParams({mode: 'compare'});
                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                            },
                            body: body.toString()
                        });

                        const text = await response.text();
                        let payload = null;
                        try {
                            payload = JSON.parse(text);
                        } catch (parseError) {
                            // ignore, handled below
                        }

                        if (response.ok && payload && payload.success && payload.summary) {
                            status.textContent = 'Comparison done';
                            log.textContent = formatComparisonSummary(payload.summary);
                        } else if (response.ok && payload && payload.success) {
                            status.textContent = 'Comparison done';
                            log.textContent = 'Comparison completed, but no summary was returned.';
                        } else {
                            status.textContent = 'Failed';
                            if (payload && payload.error) {
                                log.textContent += 'ERROR: ' + payload.error + '\n';
                            } else {
                                log.textContent += 'ERROR: Comparison failed.\nRaw response: ' + text + '\n';
                            }
                        }
                    } catch (error) {
                        log.textContent += 'ERROR: ' + error.message + '\n';
                        status.textContent = 'Failed';
                    } finally {
                        setButtonsDisabled(false);
                    }
                }

                async function triggerCron() {
                    if (!confirm('Queue the cron-style replication run in the background?')) {
                        return;
                    }

                    setButtonsDisabled(true);
                    status.textContent = 'Scheduling...';

                    try {
                        const body = new URLSearchParams({mode: 'cron'});
                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                            },
                            body: body.toString()
                        });

                        const text = await response.text();
                        let payload = null;
                        try {
                            payload = JSON.parse(text);
                        } catch (parseError) {
                            // Ignore, handled below.
                        }

                        let inlineFallback = false;

                        if (response.ok && payload && payload.success) {
                            if (payload.inline) {
                                inlineFallback = true;
                                status.textContent = 'Running (inline)';
                                log.textContent += 'Background execution not available; running job inline within this request.' + '\n';
                            } else {
                                status.textContent = 'Cron queued';
                                log.textContent += 'Cron run started in background.' + '\n';
                            }

                            if (payload.message) {
                                log.textContent += payload.message + '\n';
                            }
                            if (payload.reason) {
                                log.textContent += 'Reason: ' + payload.reason + '\n';
                            }
                            if (payload.log) {
                                log.textContent += 'Log file: ' + payload.log + '\n';
                            }
                            if (payload.command) {
                                log.textContent += 'Command: ' + payload.command + '\n';
                            }

                            if (inlineFallback) {
                                log.textContent += 'The process will continue server-side; refresh the log file to monitor progress.' + '\n';
                            }

                            setTimeout(() => setButtonsDisabled(false), inlineFallback ? 2000 : 0);
                        } else {
                            status.textContent = 'Failed';
                            if (payload && payload.error) {
                                log.textContent += 'ERROR: ' + payload.error + '\n';
                            } else {
                                log.textContent += 'ERROR: Unable to queue cron run.' + '\n';
                                log.textContent += 'Raw response: ' + text + '\n';
                            }
                            setButtonsDisabled(false);
                        }
                    } catch (error) {
                        log.textContent += 'ERROR: ' + error.message + '\n';
                        status.textContent = 'Failed';
                        setButtonsDisabled(false);
                    }
                }

                button.addEventListener('click', runReplication);
                cronButton.addEventListener('click', triggerCron);
                compareButton.addEventListener('click', runComparison);
            }());
        </script>
    </body>
    </html>
    <?php
}

function getPostMode(): string
{
    if (!empty($_POST['mode'])) {
        return (string) $_POST['mode'];
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        if ($raw !== false) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded['mode'])) {
                return (string) $decoded['mode'];
            }
        }
    }

    return 'web';
}

function handleCronTriggerRequest(string $rootDir): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    $result = null;
    $inline = false;
    $logPath = null;
    $reason = null;

    try {
        $result = triggerCronJob($rootDir);
        $inline = !empty($result['inline']);
        $logPath = $result['log'] ?? null;
        $reason = $result['reason'] ?? null;

        echo json_encode([
            'success' => true,
            'log' => $result['log'],
            'command' => $result['command'] ?? null,
            'inline' => $inline,
            'reason' => $reason,
            'message' => $inline
                ? ($result['message'] ?? 'Background execution is disabled on this server. Running the cron task inline; follow the log file for progress.')
                : ($result['message'] ?? null),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $exception) {
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo json_encode([
            'success' => false,
            'error' => $exception->getMessage(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    if ($inline && $logPath !== null) {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @ob_end_flush();
            flush();
        }

        ignore_user_abort(true);
        error_log('[replicate_database] Inline cron fallback engaged (reason: ' . ($reason ?: 'unknown') . ', log: ' . $logPath . ')');
        runCronInline($rootDir, $logPath);
        return;
    }
}

function handleComparisonRequest(string $rootDir): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
    }

    try {
        loadEnvFile($rootDir . DIRECTORY_SEPARATOR . '.env');

        $sourceConfig = buildConnectionConfig('APP_DB_');
        $targetConfig = buildConnectionConfig('REPL_TARGET_DB_');
        validateDistinctDatabases($sourceConfig, $targetConfig);

        $source = createPdoConnection($sourceConfig);
        $target = createPdoConnection($targetConfig);

        relaxSqlMode($source);
        relaxSqlMode($target);

        $summary = compareDatabases(
            $source,
            $target,
            $sourceConfig['name'],
            $targetConfig['name']
        );

        echo json_encode([
            'success' => true,
            'summary' => $summary,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $exception) {
        if (!headers_sent()) {
            http_response_code(500);
        }

        echo json_encode([
            'success' => false,
            'error' => $exception->getMessage(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

/**
 * @return array{
 *     generated_at: string,
 *     source_database: string,
 *     target_database: string,
 *     source_table_count: int,
 *     target_table_count: int,
 *     checked_tables: int,
 *     missing_in_target: array<int, string>,
 *     missing_in_source: array<int, string>,
 *     differences: array<int, array<string, mixed>>,
 *     warnings: array<int, string>
 * }
 */
function compareDatabases(PDO $source, PDO $target, string $sourceDb, string $targetDb): array
{
    $sourceTables = fetchBaseTables($source);
    $targetTables = fetchBaseTables($target);

    $sourceOnly = array_values(array_diff($sourceTables, $targetTables));
    $targetOnly = array_values(array_diff($targetTables, $sourceTables));

    $commonTables = array_values(array_intersect($sourceTables, $targetTables));

    $differences = [];
    $warnings = [];

    foreach ($commonTables as $table) {
        $notes = [];

        $sourceRows = null;
        $targetRows = null;

        try {
            $sourceRows = fetchRowCount($source, $table);
        } catch (Throwable $e) {
            $notes[] = 'Source row count error: ' . $e->getMessage();
            $warnings[] = $table . ': source row count error (' . $e->getMessage() . ')';
        }

        try {
            $targetRows = fetchRowCount($target, $table);
        } catch (Throwable $e) {
            $notes[] = 'Target row count error: ' . $e->getMessage();
            $warnings[] = $table . ': target row count error (' . $e->getMessage() . ')';
        }

        $checksumSource = getTableChecksum($source, $table);
        if ($checksumSource['note']) {
            $notes[] = 'Source checksum note: ' . $checksumSource['note'];
            $warnings[] = $table . ': source checksum note (' . $checksumSource['note'] . ')';
        }

        $checksumTarget = getTableChecksum($target, $table);
        if ($checksumTarget['note']) {
            $notes[] = 'Target checksum note: ' . $checksumTarget['note'];
            $warnings[] = $table . ': target checksum note (' . $checksumTarget['note'] . ')';
        }

        $rowDifference = null;
        if ($sourceRows !== null && $targetRows !== null) {
            $rowDifference = $targetRows - $sourceRows;
        }

        $checksumStatus = 'unavailable';
        if ($checksumSource['value'] !== null && $checksumTarget['value'] !== null) {
            $checksumStatus = $checksumSource['value'] === $checksumTarget['value'] ? 'match' : 'mismatch';
        } elseif ($checksumSource['value'] === null && $checksumTarget['value'] === null) {
            $checksumStatus = 'unavailable';
        } else {
            $checksumStatus = 'partial';
        }

        $hasDifference = false;
        if ($rowDifference !== null && $rowDifference !== 0) {
            $hasDifference = true;
        }
        if ($checksumStatus === 'mismatch') {
            $hasDifference = true;
        }

        if ($hasDifference) {
            $diffEntry = [
                'table' => $table,
                'source_rows' => $sourceRows,
                'target_rows' => $targetRows,
                'row_difference' => $rowDifference,
                'checksum_source' => $checksumSource['value'],
                'checksum_target' => $checksumTarget['value'],
                'checksum_status' => $checksumStatus,
            ];
            if (!empty($notes)) {
                $diffEntry['notes'] = $notes;
            }
            $differences[] = $diffEntry;
        }
    }

    return [
        'generated_at' => date('c'),
        'source_database' => $sourceDb,
        'target_database' => $targetDb,
        'source_table_count' => count($sourceTables),
        'target_table_count' => count($targetTables),
        'checked_tables' => count($commonTables),
        'missing_in_target' => $sourceOnly,
        'missing_in_source' => $targetOnly,
        'differences' => $differences,
        'warnings' => array_values(array_unique($warnings)),
    ];
}

/**
 * @return array<int, string>
 */
function fetchBaseTables(PDO $pdo): array
{
    $tables = [];
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            if (isset($row[0])) {
                $tables[] = $row[0];
            }
        }
    }

    sort($tables);
    return $tables;
}

function fetchRowCount(PDO $pdo, string $table): int
{
    $sql = sprintf('SELECT COUNT(*) AS cnt FROM `%s`', $table);
    $stmt = $pdo->query($sql);
    if (!$stmt) {
        throw new RuntimeException('Unable to fetch row count.');
    }
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int) ($row['cnt'] ?? 0);
}

/**
 * @return array{value: ?int, note: ?string}
 */
function getTableChecksum(PDO $pdo, string $table): array
{
    try {
        $stmt = $pdo->query(sprintf('CHECKSUM TABLE `%s`', $table));
        if (!$stmt) {
            return ['value' => null, 'note' => 'checksum query failed'];
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !array_key_exists('Checksum', $row)) {
            return ['value' => null, 'note' => 'checksum unavailable'];
        }

        if ($row['Checksum'] === null) {
            return ['value' => null, 'note' => 'checksum returned NULL'];
        }

        return ['value' => (int) $row['Checksum'], 'note' => null];
    } catch (Throwable $e) {
        return ['value' => null, 'note' => $e->getMessage()];
    }
}

function runCli(string $rootDir, array $argv): void
{
    $options = parseCliArguments($argv);
    $lockHandle = null;
    $exitCode = 0;

    try {
        if (!$options['no_lock']) {
            $lockHandle = acquireLock($rootDir . DIRECTORY_SEPARATOR . 'replicate.lock');
            emit_log('Acquired replication lock.');
        }

        if ($options['cron'] && $options['log_file'] === null) {
            $options['log_file'] = buildDefaultCronLogPath($rootDir);
        }

        if ($options['log_file'] !== null) {
            configureLogFile($options['log_file']);
            emit_log('Logging additional output to ' . $options['log_file']);
        }

        emit_log('Starting database replication (CLI mode)...');
        main($rootDir);
    } catch (Throwable $exception) {
        $exitCode = 1;
        emit_log('ERROR: ' . $exception->getMessage());
        emit_log(sprintf('Location: %s:%d', $exception->getFile(), $exception->getLine()));
    } finally {
        closeConfiguredLogFile();
        releaseLock($lockHandle);
    }

    exit($exitCode);
}

/**
 * @param array<int, string> $argv
 * @return array{cron: bool, log_file: ?string, no_lock: bool}
 */
function parseCliArguments(array $argv): array
{
    $options = [
        'cron' => false,
        'log_file' => null,
        'no_lock' => false,
    ];

    $count = count($argv);
    for ($i = 1; $i < $count; $i++) {
        $arg = $argv[$i];

        if ($arg === '--cron') {
            $options['cron'] = true;
            continue;
        }

        if ($arg === '--no-lock') {
            $options['no_lock'] = true;
            continue;
        }

        if (starts_with($arg, '--log=')) {
            $options['log_file'] = substr($arg, 6);
            continue;
        }

        if ($arg === '--log') {
            if ($i + 1 >= $count) {
                emit_log('ERROR: --log option requires a file path.');
                exit(1);
            }
            $options['log_file'] = $argv[++$i];
            continue;
        }

        emit_log('ERROR: Unknown option ' . $arg);
        exit(1);
    }

    return $options;
}

function buildDefaultCronLogPath(string $rootDir): string
{
    $logsDir = $rootDir . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'replication_logs';
    if (!is_dir($logsDir) && !mkdir($logsDir, 0755, true) && !is_dir($logsDir)) {
        throw new RuntimeException('Unable to create logs directory: ' . $logsDir);
    }

    return $logsDir . DIRECTORY_SEPARATOR . 'replicate-' . date('Ymd-His') . '.log';
}

/**
 * @return array{command: string, log: string}
 */
function triggerCronJob(string $rootDir): array
{
    $phpBinary = PHP_BINARY ?: 'php';
    $attempts = [];

    if (str_contains($phpBinary, 'php-fpm')) {
        $attempts[] = 'php-fpm-detected';
        $cliCandidates = [
            '/usr/local/bin/php',
            '/usr/bin/php',
            '/bin/php',
            PHP_BINDIR . '/php',
        ];
        foreach ($cliCandidates as $candidate) {
            if ($candidate && is_file($candidate) && is_executable($candidate)) {
                $phpBinary = $candidate;
                $attempts[] = 'php-cli-fallback';
                break;
            }
        }
    }

    $logPath = buildDefaultCronLogPath($rootDir);

    $commandParts = [
        $phpBinary,
        __FILE__,
        '--cron',
        '--log=' . $logPath,
    ];

    $commandString = implode(' ', array_map('escapeshellarg', $commandParts));

    if (stripos(PHP_OS, 'WIN') === 0) {
        if (isFunctionAvailable('popen')) {
            $background = 'start /B "" ' . $commandString;
            $process = @popen($background, 'r');
            if (is_resource($process)) {
                pclose($process);
                return [
                    'command' => $commandString,
                    'log' => $logPath,
                    'inline' => false,
                    'reason' => 'popen',
                ];
            }
            $attempts[] = 'popen-failed';
        } else {
            $attempts[] = 'popen-disabled';
        }

        return [
            'command' => $commandString,
            'log' => $logPath,
            'inline' => true,
            'reason' => $attempts ? implode(',', $attempts) : 'windows-background-disabled',
            'message' => 'Windows background execution unavailable; running inline instead.',
        ];
    }

    $spawned = false;

    if (isFunctionAvailable('proc_open')) {
        $descriptors = [
            0 => ['pipe', 'r'], // child stdin so we can push commands
            1 => ['file', '/dev/null', 'a'],
            2 => ['file', '/dev/null', 'a'],
        ];

        $process = @proc_open('/bin/sh', $descriptors, $pipes);
        if (is_resource($process)) {
            $stdin = $pipes[0] ?? null;
            if (is_resource($stdin)) {
                @fwrite($stdin, $commandString . " > /dev/null 2>&1 &\n");
                @fwrite($stdin, "exit\n");
                @fflush($stdin);
                @fclose($stdin);
            }
            @proc_close($process);
            $spawned = true;
            $attempts[] = 'proc_open';
        } else {
            $attempts[] = 'proc_open-failed';
        }
    } else {
        $attempts[] = 'proc_open-disabled';
    }

    if (!$spawned) {
        if (isFunctionAvailable('exec')) {
            $background = sprintf('(%s) > /dev/null 2>&1 &', $commandString);
            $result = @exec($background);
            if ($result !== false || $result === null) {
                $spawned = true;
                $attempts[] = 'exec';
            } else {
                $attempts[] = 'exec-failed';
            }
        } else {
            $attempts[] = 'exec-disabled';
        }
    }

    if (!$spawned) {
        if (isFunctionAvailable('shell_exec')) {
            $background = sprintf('(%s) > /dev/null 2>&1 &', $commandString);
            $result = @shell_exec($background);
            if ($result !== false || $result === null) {
                $spawned = true;
                $attempts[] = 'shell_exec';
            } else {
                $attempts[] = 'shell_exec-failed';
            }
        } else {
            $attempts[] = 'shell_exec-disabled';
        }
    }

    if (!$spawned) {
        return [
            'command' => $commandString,
            'log' => $logPath,
            'inline' => true,
            'reason' => implode(',', array_unique(array_filter($attempts))) ?: 'background-disabled',
            'message' => 'Background execution functions are disabled; running inline instead.',
        ];
    }

    return [
        'command' => $commandString,
        'log' => $logPath,
        'inline' => false,
        'reason' => implode(',', array_unique(array_filter($attempts))) ?: 'background-spawned',
    ];
}

function runCronInline(string $rootDir, string $logPath): void
{
    global $REPL_SUPPRESS_STDOUT;

    $previousSuppress = $REPL_SUPPRESS_STDOUT;
    $REPL_SUPPRESS_STDOUT = true;

    $lockHandle = null;
    try {
        $lockHandle = acquireLock($rootDir . DIRECTORY_SEPARATOR . 'replicate.lock');
        configureLogFile($logPath);
        emit_log('Starting database replication (inline fallback)...');
        main($rootDir);
        emit_log('Database replication completed (inline fallback).');
    } catch (Throwable $exception) {
        emit_log('ERROR: ' . $exception->getMessage());
        emit_log(sprintf('Location: %s:%d', $exception->getFile(), $exception->getLine()));
        error_log('[replicate_database] Inline cron error: ' . $exception->getMessage());
    } finally {
        closeConfiguredLogFile();
        releaseLock($lockHandle);
        $REPL_SUPPRESS_STDOUT = $previousSuppress;
    }
}

function configureLogFile(string $path): void
{
    global $REPL_LOG_HANDLE;

    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create log directory: ' . $directory);
    }

    $handle = fopen($path, 'ab');
    if ($handle === false) {
        throw new RuntimeException('Unable to open log file for writing: ' . $path);
    }

    $REPL_LOG_HANDLE = $handle;
}

function closeConfiguredLogFile(): void
{
    global $REPL_LOG_HANDLE;

    if (isset($REPL_LOG_HANDLE) && is_resource($REPL_LOG_HANDLE)) {
        fclose($REPL_LOG_HANDLE);
    }

    $REPL_LOG_HANDLE = null;
}

/**
 * @param resource|null $handle
 */
function releaseLock($handle): void
{
    if (is_resource($handle)) {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

/**
 * @return resource
 */
function acquireLock(string $path)
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create lock directory: ' . $directory);
    }

    $handle = fopen($path, 'c');
    if ($handle === false) {
        throw new RuntimeException('Unable to open lock file: ' . $path);
    }

    if (!flock($handle, LOCK_EX | LOCK_NB)) {
        fclose($handle);
        throw new RuntimeException('Another replication process appears to be running (lock not acquired).');
    }

    return $handle;
}

function emit_log(string $message = ''): void
{
    $output = $message;

    if ($output === '' || !preg_match('/\r?\n$/', $output)) {
        $output .= PHP_EOL;
    }

    global $REPL_LOG_HANDLE, $REPL_SUPPRESS_STDOUT;

    if (!$REPL_SUPPRESS_STDOUT) {
        fwrite(STDOUT, $output);
    }

    if (isset($REPL_LOG_HANDLE) && is_resource($REPL_LOG_HANDLE)) {
        fwrite($REPL_LOG_HANDLE, $output);
        fflush($REPL_LOG_HANDLE);
    }

    if (!$REPL_SUPPRESS_STDOUT && PHP_SAPI !== 'cli') {
        @ob_flush();
        flush();
    }
}

function isFunctionAvailable(string $function): bool
{
    if (!function_exists($function)) {
        return false;
    }

    $disabled = ini_get('disable_functions') ?: '';
    if ($disabled === '') {
        return true;
    }

    $list = array_map('trim', explode(',', strtolower($disabled)));
    return !in_array(strtolower($function), $list, true);
}

function starts_with(string $haystack, string $needle): bool
{
    if ($needle === '') {
        return true;
    }

    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

function ends_with(string $haystack, string $needle): bool
{
    if ($needle === '') {
        return true;
    }

    return substr($haystack, -strlen($needle)) === $needle;
}
