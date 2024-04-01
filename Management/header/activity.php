<?php
session_start();

// Reset session timeout
$_SESSION['LAST_ACTIVITY'] = time();
?>
