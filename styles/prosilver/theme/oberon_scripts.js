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

// Initialize image gallery - click thumbnails to swap with main image
function initImageGallery() {
    const mainImageContainer = document.getElementById('custdb_main_image_container');
    if (!mainImageContainer) return;

    const mainImage = mainImageContainer.querySelector('.custdb_contribution_image');
    if (!mainImage) return;

    const thumbnails = document.querySelectorAll('.custdb_thumbnail');
    if (thumbnails.length === 0) return;

    // Mark the first thumbnail as active on load
    thumbnails.forEach((thumb, index) => {
        if (index === 0) {
            thumb.classList.add('active');
        }

        // Click handler for swapping images
        thumb.addEventListener('click', function() {
            swapMainImage(this, mainImage, thumbnails);
        });

        // Keyboard support: Enter and Space keys
        thumb.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                swapMainImage(this, mainImage, thumbnails);
            }
        });
    });
}

// Swap main image with clicked thumbnail
function swapMainImage(clickedThumbnail, mainImage, allThumbnails) {
    const newImageSrc = clickedThumbnail.dataset.fullSrc;
    if (!newImageSrc) return;

    // Fade out
    mainImage.style.opacity = '0.7';

    // Update image source
    setTimeout(() => {
        mainImage.src = newImageSrc;
        mainImage.parentElement.dataset.originalSrc = newImageSrc;

        // Fade in
        mainImage.style.opacity = '1';
    }, 150);

    // Update active class
    allThumbnails.forEach(thumb => thumb.classList.remove('active'));
    clickedThumbnail.classList.add('active');
}

// Search customisations
document.addEventListener('DOMContentLoaded', function () {
    // Initialize image gallery
    initImageGallery();

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