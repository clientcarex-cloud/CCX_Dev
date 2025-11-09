<div>
    <pre><?php $logfile = fopen($file, "r") or die("Unable to open file!");echo fread($logfile, filesize($file));
        fclose($logfile);
        ?>
    </pre>
</div>
<input type="hidden" id="flexi_log_url" value="<?php echo admin_url('flexibackup/download_backup/'.$id.'/log') ?>" />
