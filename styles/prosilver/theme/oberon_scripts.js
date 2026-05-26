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

// Search customisations
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('custdb_search');

    if (!searchInput) return;

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();

            const query = searchInput.value.trim();
            if (query.length > 0) {
                // Use provided route or fallback
                const baseUrl = searchInput.dataset.searchUrl || window.location.pathname;

                // Build URL with existing query params
                const url = new URL(window.location.href);

                // Update the base path to your index route (if different)
                url.pathname = baseUrl;

                // Set/replace search parameter "q"
                url.searchParams.set('q', query);

                // Redirect with all parameters intact
                window.location.href = url.toString();
            }
        }
    });
});