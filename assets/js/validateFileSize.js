function validateFileSize() {
    const fileInput = document.getElementById('resource') || document.getElementById('media') || document.getElementById('image') || document.getElementById('file');
    if (!fileInput) {
        console.error('No file input found with ID: resource, media, image, or file');
        return true; // Allow submission if no file input (unlikely case)
    }

    const file = fileInput.files[0];
    if (!file) {
        return true; // No file selected, allow submission
    }

    // Get max size from data attribute or form
    const maxSizeMB = parseFloat(fileInput.dataset.maxSize) || parseFloat(fileInput.closest('form').dataset.maxSize) || 10; // Fallback to 10MB
    const maxSizeBytes = maxSizeMB * 1024 * 1024; // Convert to bytes

    if (file.size > maxSizeBytes) {
        alert('File size exceeds ' + maxSizeMB + 'MB. Please select a smaller file.');
        fileInput.value = ''; // Clear the input
        return false; // Prevent form submission
    }
    return true; // Allow form submission
}