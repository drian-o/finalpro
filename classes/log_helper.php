<?php
// classes/log_helper.php

function write_log($logFilePath, $message) {
    $logFileDir = dirname($logFilePath);
    if (!is_dir($logFileDir)) {
        @mkdir($logFileDir, 0755, true);
    }
    $entry = "[" . date("Y-m-d H:i:s") . "] " . $message . "\n";
    file_put_contents($logFilePath, $entry, FILE_APPEND | LOCK_EX);
}
?>