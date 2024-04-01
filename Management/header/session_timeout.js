$(document).ready(function(){
    function checkSessionStatus() {
        $.ajax({
            url: '../header/check_session.php',
            method: 'POST',
            success: function(response) {
                if (response === 'expired') {
                    window.location.replace("../header/logout.php");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error checking session status:", error);
            }
        });
    }

    function resetSessionTimeout() {
        $.ajax({
            url: '../header/reset_session.php',
            method: 'POST',
            success: function(response) {
                console.log("Session timeout reset successfully.");
            },
            error: function(xhr, status, error) {
                console.error("Error resetting session timeout:", error);
            }
        });
    }

    $(document).on('mousemove click scroll keydown', function(){
        resetSessionTimeout();
    });

    setInterval(checkSessionStatus, 60000); 
});
