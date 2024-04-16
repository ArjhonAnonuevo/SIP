<?php
session_start();

// Check if session has expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 4)) {
    // If session has expired (more than 4 seconds have passed since last activity)
    echo 'expired';
} else {
    // If session is still active (less than or equal to 4 seconds since last activity)
    echo 'active';
}
?>
