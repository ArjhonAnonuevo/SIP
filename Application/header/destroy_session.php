<?php
session_start();
// Destroy the session
session_destroy();
// Redirect to the home page
header('Location: index.html'); 
exit;
?>
