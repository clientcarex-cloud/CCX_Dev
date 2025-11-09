<?php

use Carbon\Carbon;

defined('BASEPATH') or exit('No direct script access allowed');

class Flexibackup_module
{
    private $ci;

    private string $backupFolder = "";
    private string $backup_folder_name = "";

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    public function get_next_scheduled_backup($type)
    {
        $backup_schedule = ($type == 'file') ? intval(get_option('flexibackup_files_backup_schedule')) : intval(get_option('flexibackup_database_backup_schedule'));
        $last_flexi_backup = get_option('last_flexi_backup_' . $type);
        //it is manual backup or no backup settings configured yet
        if ($backup_schedule == 1 || $backup_schedule == 0) {
            return false;
        }
        //get the set time for backup
        $backupTime = get_option('flexibackup_auto_backup_time') ? get_option('flexibackup_auto_backup_time') : '22:00';
        $backupTime = explode(':', $backupTime);
        $backupHour = (int)$backupTime[0];
        $backupMinute = (int)$backupTime[1];
        $now = $this->get_time_now();
        if (!$last_flexi_backup) {
            //that means the first scheduled backup will be later tonight say, 10pm tonight
            $today = Carbon::today();
            $setBackupTime = $today->setTime($backupHour, $backupMinute, 0);
            if($now > $setBackupTime->timestamp){
                //it means the backup time has passed for today, we need to schedule it for tomorrow
                $setBackupTime = $today->addDay();
            }
            return $setBackupTime->timestamp;
        } else {
            //calculate next scheduled backup based on the last backup
            $last_flexi_backup = Carbon::createFromTimestamp($last_flexi_backup);
            switch ($backup_schedule) {
                case 2:
                    //two hours
                    return $last_flexi_backup->addHours(2)->timestamp;
                    break;
                case 3:
                    //every four hours
                    return $last_flexi_backup->addHours(4)->timestamp;
                    break;
                case 4:
                    //every 8hours
                    return $last_flexi_backup->addHours(8)->timestamp;
                    break;
                case 5:
                    //every 12hours
                    return $last_flexi_backup->addHours(12)->timestamp;
                case 6:
                    //daily
                    //return $last_flexi_backup->addDay()->timestamp;
                    return $last_flexi_backup->addDay()->setTime($backupHour,$backupMinute,0)->timestamp;
                    break;
                case 7:
                    //weekly
                    //return $last_flexi_backup->addWeek()->timestamp;
                    return $last_flexi_backup->addWeek()->setTime($backupHour,$backupMinute,0)->timestamp;
                    break;
                case 8:
                    //fortnightly
                    //return $last_flexi_backup->addWeeks(2)->timestamp;
                    return $last_flexi_backup->addWeeks(2)->setTime($backupHour,$backupMinute,0)->timestamp;
                    break;
                case 9:
                    //monthly
                    //return $last_flexi_backup->addMonth()->timestamp;
                    return $last_flexi_backup->addMonth()->setTime($backupHour,$backupMinute,0)->timestamp;
                    break;
            }
        }
    }

    public function get_time_now()
    {
        return Carbon::now()->timestamp;
    }

    public function perform_cron_backup(){
        $backup_types  = array('file','database');
        foreach ($backup_types as $backup_type){
            $last_flexi_backup = get_option('last_flexi_backup_'.$backup_type);
            $next_schduled_backup = $this->get_next_scheduled_backup($backup_type);
            $now = $this->get_time_now();
            $buffer_window = 600; //10 minutes buffer window;
            $runbackup = false;
            if($next_schduled_backup){
                if($next_schduled_backup == $now){
                    $runbackup = true;
                }elseif($now > $next_schduled_backup) {
                    //it means the last backup was missed, we need to run it now
                    $runbackup = true;
                }else{
                    //if the backup is supposed to run at 10pm, we can still run it between 09:59pm and 10:00pm
                    if(($next_schduled_backup > $now && (($next_schduled_backup - $now) < $buffer_window))){
                        //we need to be sure the last backup did not occur in the last buffer window
                        if($last_flexi_backup){
                            if($now - $last_flexi_backup > $buffer_window){
                                $runbackup = true;
                            }
                        }else{
                            $runbackup = true;
                        }
                    }
                }
            }
            if($runbackup){
                $this->perform_backup(array('file'=>($backup_type == 'file')?true:false,'database'=>($backup_type == 'database')?true:false),array(),true);
                update_option('last_flexi_backup_'.$backup_type, Carbon::now()->timestamp, 1);
            }
        }
    }

    /**
     * @param $options
     * @return bool
     */
    public function perform_backup($options = array('file' => true, 'database' => true), $file_settings = array(),$isCron = false)
    {
        if (!$options['file'] && !$options['database']) {
            return false;
        }
        //create a log file and add it to the backup folder
        set_time_limit(0); //Unlimited max execution time to allow the backup process to complete
        $this->handle_memory_limit_error();
        $this->create_storage_directory();
        //add backup to database
        $this->ci->load->model('flexibackup/flexibackup_model');
        //load remote library
        $this->ci->load->library("flexibackup/flexibackup_remote_module");
        if ($options['file']) {
            $this->backup_folder_init();
            $files_sql_data = array(
                'type'=>'file',
                'name'=> $this->backup_folder_name,
                'note' => $options['note']
            );
            $backup_id = $this->ci->flexibackup_model->add_backup($files_sql_data);
            $this->backup_files($backup_id, $file_settings);
            //upload to remote storage
            if(get_option('flexibackup_auto_backup_to_remote_enabled') == 1 && $isCron){
                $this->upload_backup_to_remote($backup_id);
            }
        }
        //if the options allow database
        if ($options['database']) {
            $this->backup_folder_init('database');
            $db_sql_data = array(
                'type'=>'database',
                'name'=> $this->backup_folder_name,
                'note' => $options['note']
            );
            $backup_id = $this->ci->flexibackup_model->add_backup($db_sql_data);
            $this->backup_database($backup_id);
            //upload to remote storage
            if(get_option('flexibackup_auto_backup_to_remote_enabled') == 1 && $isCron){
                $this->upload_backup_to_remote($backup_id);
            }
        }
        //clean up
        return true;
    }

    public function get_backup_file($type, $backup,$unzip = false){
        //get backup folder name
        $backup_folder_name = $backup->backup_name;
        $backup_folder = FLEXIBACKUP_FOLDER . $backup_folder_name;
        //db files
        if($type == "database"){
            $file = $backup_folder.'/database.sql';
            if(!file_exists($file)){
                return false;
            }
            return $file;
        }

        //zipped files
        $file = $backup_folder.'/'.$type.'.zip';
        if(file_exists($file)){
            if($unzip){
                return $this->unzip($file, $backup_folder.'/'.$type);
            }else{
                return $file;
            }
        }
    }

    private function unzip ($file, $extractPath){
        //check if folder exists, if it does, just return the folder
        if(is_dir($extractPath) && !$this->dir_is_empty($extractPath)){
            return $this->buildTreeData($extractPath);
        }
        $zip = new ZipArchive;
        if ($zip->open($file) === TRUE) {
            $zip->extractTo($extractPath);
            $zip->close();
            return $this->buildTreeData($extractPath);
        } else {
            return false;
        }
    }

    private function dir_is_empty($dir) {
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                closedir($handle);
                return false;
            }
        }
        closedir($handle);
        return true;
    }

    private function buildTreeData($dir) {
        $data = array();
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $filePath = $dir . "/" . $file;
                if (is_dir($filePath)) {
                    $data[] = array(
                        "text" => $file,
                        "children" => $this->buildTreeData($filePath),
                        "icon" => "jstree-folder"
                    );
                } else {
                    $data[] = array("text" => $file, "icon" => "jstree-file");
                }
            }
        }
        return $data;
    }

    public function create_storage_directory()
    {
        if (!is_dir(FLEXIBACKUP_FOLDER)) {
            mkdir(FLEXIBACKUP_FOLDER, 0755);
            $fp = fopen(rtrim(FLEXIBACKUP_FOLDER, '/') . '/' . 'index.html', 'w');
            fclose($fp);
            fopen(FLEXIBACKUP_FOLDER . '.htaccess', 'w');
            $fp = fopen(FLEXIBACKUP_FOLDER . '.htaccess', 'a+');
            if ($fp) {
                fwrite($fp, 'Order Deny,Allow' . PHP_EOL . 'Deny from all');
                fclose($fp);
            }
        }
    }

    public function create_email_template(){
        $templateMessage = 'Hi there! <br /><br /> Please find attached a copy of your {backup_type} created on {backup_date}.  <br /><br /> Regards.';
        create_email_template('New Backup Available  - {backup_name}', $templateMessage, 'staff', 'Flexi Backup Notification', 'flexibackup-new-backup-to-staff');
    }

    private function backup_files($backup_id, $file_settings = array())
    {
        try{
            $backupFolder = $this->backupFolder;
            $folders = $this->get_folders($file_settings);
            //copy all the files and subfolders from folders to a backup folder
            foreach ($folders as $folder) {
                $this->append_to_log_file($folder.' Files Backup started at ' . date('Y-m-d H:i:s'));
                if($folder == 'others'){
                    $this->copyOthersFiles($backupFolder.'/'.$folder);
                }else{
                    $this->copyFiles(FCPATH . $folder, $backupFolder . '/' . $folder);
                }
                $this->ci->flexibackup_model->update_backup(array('key'=>$folder), $backup_id);
                $this->zipFolder($backupFolder.'/'.$folder);
                //$this->clean_up_dir($backupFolder.'/'.$folder);
                $this->append_to_log_file($folder.' Files Backup completed at ' . date('Y-m-d H:i:s'));
            }
            //add to lof file
            log_activity('Files Backup [' . $backupFolder . ']', null);
        }catch (Exception $e) {
            //add to log file
            $this->append_to_log_file(' Error Occurred when Backing up files' . $e->getMessage());
        }
    }

    private function get_folders($file_settings = array()): array
    {
        $folders = [
            'application',
            'assets',
            'media',
            'modules',
            'resources',
            'system',
            'uploads',
            'others'
        ];
        //if we have file settings, it means we are performing a manual backup
        if($file_settings){
            //filter settings array where value is 1
            $folders = array_filter($file_settings, function($value){
                return $value == 1;
            });
            return array_keys($folders);
        }
        if (get_option('flexibackup_include_modules') == 0) {
            $key = array_search('modules', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        if (get_option('flexibackup_include_application') == 0) {
            $key = array_search('application', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        if (get_option('flexibackup_include_uploads') == 0) {
            $key = array_search('uploads', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        if (get_option('flexibackup_include_assets') == 0) {
            $key = array_search('assets', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        if (get_option('flexibackup_include_system') == 0) {
            $key = array_search('system', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        if (get_option('flexibackup_include_resources') == 0) {
            $key = array_search('resources', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        if (get_option('flexibackup_include_media') == 0) {
            $key = array_search('media', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        if (get_option('flexibackup_include_others') == 0) {
            $key = array_search('others', $folders);
            if ($key !== false) {
                unset($folders[$key]);
            }
        }
        //print all the options above
        return $folders;
    }

    private function copyFiles($source, $destination)
    {
        try{
            //add log file
            if (is_file($source)) {
                $result = copy($source, $destination);
                return $result;
            } elseif (is_dir($source)) {
                if (!is_dir($destination)) {
                    mkdir($destination, 0777, true);
                }
                $dir = opendir($source);
                while (($entry = readdir($dir)) !== false) {
                    if ($entry == '.' || $entry == '..') {
                        continue;
                    }
                    $srcPath = $source . '/' . $entry;
                    $dstPath = $destination . '/' . $entry;
                    $this->copyFiles($srcPath, $dstPath);
                }
                closedir($dir);
                return true;
            }
            return false;
        }catch (Exception $e){
            //add to log file
            $this->append_to_log_file(' Error Occurred when copying files from '.$source. $e->getMessage());
        }
    }

    //copy all the files in the root folder
    private function copyOthersFiles($destination, $source = FCPATH){
        $files = scandir($source);
        //create an others folder to put the files
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }
        foreach($files as $file) {
            if (is_file($source . $file)) {
                //return $this->copyFiles($source . $file, $destination);
                copy($source . $file, $destination.'/'.$file);
            }
        }
        return true;
    }

    private function backup_database($backup_id)
    {
        try{
            //add to log file
            $this->append_to_log_file('Database backup started at ' . date('Y-m-d H:i:s'));
            $backupFolder = $this->backupFolder;
            $this->ci->load->dbutil();
            $prefs = [
                'format' => 'sql',
                'filename' => date('Y-m-d-H-i-s') . '_backup.sql',
            ];
            $backup = @$this->ci->dbutil->backup($prefs);
            $backup_name = 'database.sql';
            $save_backup_path = $backupFolder .'/'. $backup_name;
            $this->ci->load->helper('file');
            if (@write_file($save_backup_path, $backup)) {
                log_activity('Database Backup [' . $backup_name . ']', null);
                //add to log file
                $this->append_to_log_file('Database backup completed at ' . date('Y-m-d H:i:s'));
                return true;
            }
            return true;
        }catch (Exception $e){
            //add to log file
            $this->append_to_log_file(' Error Occurred when Backing up database' . $e->getMessage());
        }
    }

    public function upload_backup_to_remote($backup_id){
        $this->ci->load->model('flexibackup/flexibackup_model');
        $this->ci->load->library("flexibackup/flexibackup_remote_module");
        $backup = $this->ci->flexibackup_model->get_backup($backup_id);
        $file_types  = [];
        $result = ['status'=>false,'message'=>''];
        if($backup){
            if($backup->backup_type == "database") {
                $file_types[] = "database";
            }else{
                $file_types = explode(',',$backup->backup_data);
            }
            $uploaded_to_remote = [];
            foreach ($file_types as $file_type){
                $file = $this->get_backup_file($file_type,$backup);
                if(!$file || !file_exists($file)){
                    $result['message'] = 'Backup file not found';
                    return $result;
                }
                try{
                    $remoteResponse = $this->ci->flexibackup_remote_module->send_backup_to_remote_storage($file,$backup);
                    if($remoteResponse){
                        $result['status'] = true;
                        $uploaded_to_remote[] = $file_type;
                    }else{
                        $result['message'] = 'An error occurred '.$file_type.' to remote storage';
                    }
                }catch (Exception $e){
                    $result['message'] = $result['message'] .' '.$e->getMessage();
                }
            }
            //update backup, it is uploaded to remote
            if(count($uploaded_to_remote) == count($file_types)){
                $this->ci->flexibackup_model->update_uploaded_to_remote($backup_id);
                //if auto delete is enabled, delete the backup
                if(get_option('flexibackup_auto_delete_remote_backups') == 1){
                    $this->delete_backup($backup_id,'all');
                }
            }
        }else{
            $result['message'] = 'Backup not found';
        }
        return $result;
    }

    public function restore_backup($backup_id, $restore_files = array())
    {
        $this->ci->load->model('flexibackup/flexibackup_model');
        $backup = $this->ci->flexibackup_model->get_backup($backup_id);
        if (!$backup) {
            return array('message'=>"Backup not found",'status'=>false);
        }
        $backupFolder = FLEXIBACKUP_FOLDER . $backup->backup_name;
        if (!is_dir($backupFolder)) {
            return array('message'=>"Backup folder not found",'status'=>false);
        }
        if (in_array('database', $restore_files)) {
            return $this->restore_backup_database($backupFolder);
        }else{
            return $this->restore_files($backupFolder, $restore_files);
        }
    }

    private function startsWith($string, $prefix) {
        return substr($string, 0, strlen($prefix)) === $prefix;
    }

    private function restore_backup_database($backupFolder){
        $response = array('message'=>'','status'=>false);
        $this->ci->load->dbutil();
        $file = $backupFolder.'/database.sql';
        if(!file_exists($file)){
            $response['message'] = 'Backup file not found';
            return $response;
        }
        //let us remove the comments from the sql file and save the output to another file
        $inputFile = $file;
        $outputFile = $backupFolder.'/db_output.sql';

        // Open the input and output files
        $inputHandle = fopen($inputFile, 'r');
        $outputHandle = fopen($outputFile, 'w');

        if ($inputHandle && $outputHandle) {
            while (($line = fgets($inputHandle)) !== false) {
                // Check if the line starts with '#' (comment) and skip it
                if (!$this->startsWith($line, '#')) {
                    fwrite($outputHandle, $line);
                }
            }

            fclose($inputHandle);
            fclose($outputHandle);
        } else {
            $response['message'] = 'Error opening Database files.';
        }
        $sql = file_get_contents($outputFile);
        $queries = explode(";\n", $sql);
        $this->ci->db->query('SET foreign_key_checks = 0');
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                // Execute each query
                $this->ci->db->query($query);
            }
        }
        $this->ci->db->query('SET foreign_key_checks = 1');
        $response['status'] = true;
        log_activity('Database Restore [' . str_replace(FCPATH,'', $backupFolder) . ']', null);
        return $response;
    }
    private function restore_files($backupFolder,$restore_files)
    {
        $response = array('message'=>'','status'=>false);
       foreach ($restore_files as $restore_file) {
           $file = $backupFolder . '/' . $restore_file . '.zip';
           $backupTempDirectory =  $backupFolder.'/'. $restore_file;
           if($restore_file == 'others'){
               $targetDirectory = FCPATH;
           }else{
               $targetDirectory = FCPATH . $restore_file;
           }
           if (file_exists($file)) {
               //check if the target backup directory exists
                if (!is_dir($backupTempDirectory)) {
                    $this->unzip($file, $backupTempDirectory);
                }
                if($restore_file == 'others'){
                    if(!$this->copyOthersFiles($targetDirectory,$backupTempDirectory)){
                        $response['message'] = 'Failed to copy to ROOT directory. please check if the ROOT directory content is Writable';
                    };
                }else{
                    $old_dir = $targetDirectory.'_old';
                    $folder_renamed = false;
                    if(is_dir($targetDirectory)){
                        if(rename($targetDirectory,$old_dir)){
                            $folder_renamed = true;
                        }else{
                            $response['message'] = 'Failed to rename the existing directory. please check if the directory (/'.$restore_file.') and its content is Writable';
                        }
                    }
                    //if the folder is renamed, copy the files to the target directory
                    if($folder_renamed){
                        if ($this->copyFiles($backupTempDirectory, $targetDirectory)) {
                            $this->clean_up_dir($backupTempDirectory);
                            $response['status'] = true;
                            //return true;
                        } else {
                            $response['message'] = 'Failed to replace the existing directory. please check if the directory (/'.$restore_file.') and its content is Writable';
                        }
                    }

                    if($folder_renamed){
                        //rename it back if we couldn't copy the files
                        if(!is_dir($targetDirectory)){
                            rename($old_dir,$targetDirectory);
                        }else{
                            //it is successful, let us clean the old directory
                            $this->clean_up_dir($old_dir);
                        }
                    }
                }
           }else{
                $response['message'] = 'Backup file not found';
           }
       }
       if($response['message'] == '') {
           //add to lof file
           log_activity('Files Restore [' .  str_replace(FCPATH,'',$backupFolder) . ']', null);
       }
       return $response;
    }

    private function zipFolder($folder)
    {
        try{
            //add to log file
            $this->append_to_log_file('Zipping folder - '.$folder . ' started at ' . date('Y-m-d H:i:s'));
            $rootPath = $folder;
            // Initialize archive object
            $filename = $folder . '.zip';
            $zip = new ZipArchive();
            $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            // Create recursive directory iterator
            /** @var SplFileInfo[] $files */
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                // Skip directories (they would be added automatically)
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            //add to log file
            $this->append_to_log_file('Zipping folder - '.$folder . ' completed at ' . date('Y-m-d H:i:s'));
            // Zip archive will be created only after closing object
        }catch (Exception $e){
            //add to log file
            $this->append_to_log_file(' Error Occurred when zipping folder - '.$folder . $e->getMessage());
        }
    }

    private function clean_up_dir($path)
    {
        if (is_dir($path)) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                $filePath = $path . '/' . $file;
                if (is_dir($filePath)) {
                    $this->clean_up_dir($filePath);
                } else {
                    unlink($filePath);
                }
            }
            return rmdir($path);
        }
        return false;
    }

    private function handle_memory_limit_error()
    {
        register_shutdown_function(function () {
            $error = error_get_last();

            if (null !== $error) {
                if (strpos($error['message'], 'Allowed memory size of') !== false) {
                    echo '<h2>A a fatal error has been triggered during backup because of PHP memory limit.</h2>';
                    echo '<div style="font-size:18px;">';
                    echo '<p>Your current PHP memory limit is ' . ini_get('memory_limit') . ' which seems <b>too low</b> to process the backup.</p>';
                    echo '<p>As a suggestion please try the following:</p>';
                    echo '<ul>';
                    echo '<li>Increase the PHP memory limit and try again to perform a backup again.</li>';
                    echo '</ul>';
                    echo '</div>';
                } else {
                    trigger_error('Fatal error: ' . $error['message'] . 'in ' . $error['file'] . ' on line ' . $error['line'], E_USER_ERROR);
                }
            }
        });
    }

    private function get_backup_name_prefix()
    {
        return get_option('flexibackup_backup_name_prefix') == '' ? 'backup' : get_option('flexibackup_backup_name_prefix');
    }

    private function append_to_log_file($text = '')
    {
        $backupFolder = $this->backupFolder;
        $log_file = $backupFolder . '/log.txt';
        $fp = @fopen($log_file, 'a+');
        $text =  $text.PHP_EOL;
        $text = str_replace(FCPATH, '', $text); //we don't want to show the full file path
        fwrite($fp, $text);
        fclose($fp);
    }

    private function backup_folder_init($type = "files")
    {
        try{
            $backup_name_prefix = $this->get_backup_name_prefix();
            $backup_folder_name = $type.'_'.$backup_name_prefix.'_' . date('Y-m-d_H-i-s');
            $backupFolder = FLEXIBACKUP_FOLDER . $backup_folder_name;
            mkdir($backupFolder, 0755);
            $this->backupFolder = $backupFolder;
            $this->backup_folder_name = $backup_folder_name;
            $this->append_to_log_file('Backup folder created at ' . date('Y-m-d H:i:s'));
            return $backupFolder;
        }catch (Exception $e){
            $this->append_to_log_file(' Error Occurred when creating backup folder' . $e->getMessage());
        }
    }

    public function delete_backup($backup_id,$type){
        $this->ci->load->model('flexibackup/flexibackup_model');
        $backup = $this->ci->flexibackup_model->get_backup($backup_id);
        if(!$backup){
            return false;
        }
        if($type == "all"){
            $path = FCPATH.'flexibackup/'.$backup->backup_name;
            if (is_dir($path)) {
                $this->clean_up_dir($path);
            }
            $this->ci->flexibackup_model->delete_backup($backup->id);
            return true;
        }else{
            if($type == "database"){
                $ext = "sql";
            }else{
                $ext = "zip";
            }
            $dir = FCPATH.'flexibackup/'.$backup->backup_name.'/'.$type;
            $path = $dir.'.'.$ext;
            if (file_exists($path)) {
                $this->ci->flexibackup_model->remove_backup_data($backup, $type);
                unlink($path);
                //lets clean the directory as well
                $this->clean_up_dir($dir);
                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    public function perform_cron_delete_backups(){
        if(!empty(get_option(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING))){
            $this->ci->load->model('flexibackup/flexibackup_model');

            $this->ci->db->trans_begin();

            try{
                $backups = $this->ci->flexibackup_model->get_deletable_backups(get_option(FLEXIBACKUP_RETAIN_FOR_DAYS_SETTING));

                foreach($backups as $backup){
                    $this->delete_backup($backup['id'], 'all');
                }
                
                $this->ci->db->trans_commit();
            }catch(Exception $e){
                $this->ci->db->trans_rollback();
                //add to log file
                $this->append_to_log_file('Error Occurred when deleting backups - ' . $e->getMessage());
            }
        }
    }
}