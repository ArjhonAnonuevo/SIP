$(document).ready(function () {
    // Variables to handle page visibility
    var hidden, visibilityChange;
    if (typeof document.hidden !== "undefined") {
        hidden = "hidden";
        visibilityChange = "visibilitychange";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
    }

    // Variables for session management
    var lastActivityTime = new Date().getTime();
    var inactivityTimeout = 3600000; // 1 hour in milliseconds

    var sessionInterval; // Variable to hold the interval reference

    // Function to check session status via AJAX
    function checkSessionStatus() {
        var startTime = new Date().getTime(); // Track start time
        $.ajax({
            url: '../header/check_session.php',
            method: 'POST',
            success: function (response) {
                var endTime = new Date().getTime(); // Track end time
                var responseTime = endTime - startTime; // Calculate response time
                console.log("Session check response time: " + responseTime + " ms");
                if (response === 'expired') {
                    // Update user status before logging out
                    updateStatusBeforeLogout();
                } else {
                    // Redirect to the index.html page if session is active
                    window.location.replace("../index.html");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error checking session status:", error);
            }
        });
    }

    // Function to reset session timeout
    function resetSessionTimeout() {
        lastActivityTime = new Date().getTime();
        console.log("User activity detected. Resetting session timeout.");
    }

    // Function to handle visibility change
    function handleVisibilityChange() {
        if (document[hidden]) {
            // Pause session reset if user switches to other apps or minimizes the browser
            clearInterval(sessionInterval);
            console.log("User switched to other apps or minimized the browser. Pausing session reset.");
        } else {
            // Resume session reset if user returns to the app or brings the browser to the foreground
            resetSessionTimeout();
            sessionInterval = setInterval(checkSessionStatus, 60000); // Restart session check
            console.log("User returned to the app or brought the browser to the foreground. Resuming session reset.");
        }
    }

    // Attach the visibility change event listener
    if (typeof document.addEventListener === "undefined" || hidden === undefined) {
        console.log("Page Visibility API not supported");
    } else {
        document.addEventListener(visibilityChange, handleVisibilityChange, false);
    }

    // Reset session timeout on user activity
    $(document).on('mousemove click scroll keydown', function () {
        resetSessionTimeout();
    });

    // Check for user inactivity
    setInterval(function () {
        var currentTime = new Date().getTime();
        if (currentTime - lastActivityTime > inactivityTimeout) {
            // Logout the user if inactive for 1 hour
            window.location.replace("../header/interns_logout.php");
        }
    }, 1000);

    // Function to update user status before logout
    function updateStatusBeforeLogout() {
        $.ajax({
            url: '../header/update_status.php',
            method: 'POST',
            success: function (response) {
                window.location.replace("../header/interns_logout.php");
            },
            error: function (xhr, status, error) {
                console.error("Error updating user status before logout:", error);
                window.location.replace("../header/interns_logout.php");
            }
        });
    }
});
