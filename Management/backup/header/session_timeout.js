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
                var endTime = new Date().getTime(); 
                var responseTime = endTime - startTime; 
                console.log("Session check response time: " + responseTime + " ms");
                if (response === 'expired') {
                    window.location.replace("../header/logout.php");
                }
            },
            error: function (xhr, status, error) {
                console.error("Error checking session status:", error);
            }
        });
    }

    function resetSessionTimeout() {
        lastActivityTime = new Date().getTime();
    }

    function handleVisibilityChange() {
        if (document[hidden]) {
            clearInterval(sessionInterval);
        } else {
            resetSessionTimeout();
            sessionInterval = setInterval(checkSessionStatus, 60000);
        }
    }

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
            window.location.replace("../header/logout.php");
        }
    }, 1000); // Check every second for inactivity
});
