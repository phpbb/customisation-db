// Filter by status
function updateStatus(selectElement) {
    const currentUrl = new URL(window.location.href);

    // Set or update the status parameter
    currentUrl.searchParams.set('status', selectElement.value);
    window.location.href = currentUrl.toString();
}

// Sort requirements
function updateSort(selectElement) {
    const currentUrl = new URL(window.location.href);

    // Set or update the sort parameter
    currentUrl.searchParams.set('sort', selectElement.value);
    window.location.href = currentUrl.toString();
}