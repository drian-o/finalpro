<?php
// logging.php

function log_message($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents('index_debug.log', $log_entry, FILE_APPEND | LOCK_EX);
}
?>