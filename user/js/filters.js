// Advanced filter functionality
function initAdvancedFilters() {
    // Rating stars
    const ratingFilter = document.getElementById('filterRating');
    if (ratingFilter) {
        // Add visual rating stars
        const starsContainer = document.createElement('div');
        starsContainer.className = 'rating-stars mt-2';
        for (let i = 5; i >= 1; i--) {
            const star = document.createElement('span');
            star.className = 'star';
            star.innerHTML = '★'.repeat(i);
            star.dataset.rating = i;
            star.style.cursor = 'pointer';
            star.style.color = i >= 4 ? '#ffc107' : '#adb5bd';
            star.style.marginRight = '5px';
            star.style.fontSize = '1.2rem';
            
            star.addEventListener('click', function() {
                ratingFilter.value = this.dataset.rating;
                document.getElementById('filterForm').submit();
            });
            
            starsContainer.appendChild(star);
        }
        ratingFilter.parentElement.appendChild(starsContainer);
    }
    
    // Genre chips
    const genreFilter = document.getElementById('filterGenre');
    if (genreFilter) {
        const chipsContainer = document.createElement('div');
        chipsContainer.className = 'genre-chips mt-2';
        genreFilter.querySelectorAll('option').forEach(option => {
            if (option.value !== 'all') {
                const chip = document.createElement('span');
                chip.className = 'genre-chip badge bg-secondary me-2 mb-2';
                chip.textContent = option.textContent;
                chip.style.cursor = 'pointer';
                
                chip.addEventListener('click', function() {
                    genreFilter.value = option.value;
                    document.getElementById('filterForm').submit();
                });
                
                chipsContainer.appendChild(chip);
            }
        });
        genreFilter.parentElement.appendChild(chipsContainer);
    }
}