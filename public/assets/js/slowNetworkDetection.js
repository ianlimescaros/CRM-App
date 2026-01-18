// Show loading indicator if request takes too long (>2 seconds on slow networks)
function showSlowLoadingWarning() {
    let slowLoadTimeout = setTimeout(() => {
        if (ui && ui.showSpinner) {
            ui.showSpinner('Loading data... (slow network detected)');
        }
    }, 2000);
    
    // Clear the timeout when request completes
    return () => clearTimeout(slowLoadTimeout);
}
