window.onload = function() {
    if (window.history && window.history.pushState) {
        window.history.pushState('forward', null, './#forward');
        $(window).on('popstate', function() {
            if (window.location.hash === '#forward') {
                window.history.pushState('forward', null, './#forward');
            } else {
                window.history.pushState('forward', null, './#forward');
                window.location.replace('error/error.html'); 
            }
        });
    }
}
