// Network status detection and error handling
window.addEventListener('offline', () => {
    ui.showToast('⚠️ No internet connection. Some features may not work.', 'error');
});

window.addEventListener('online', () => {
    ui.showToast('✓ Connection restored', 'success');
});

// Global error handler for uncaught promise rejections
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled Promise Rejection:', event.reason);
    // Don't show error for user-intentional cancellations
    if (event.reason?.message?.includes('abort')) {
        return;
    }
    ui.showToast('Something went wrong. Please try again.', 'error');
});
