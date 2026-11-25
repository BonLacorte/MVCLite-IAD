document.addEventListener('DOMContentLoaded', function() {
    // Initialize all carousels
    document.querySelectorAll('.carousel').forEach(initializeCarousel);
});

function initializeCarousel(carousel) {
    const items = carousel.querySelectorAll('.carousel-item');
    const totalItems = items.length;
    const carouselId = carousel.dataset.carousel;
    let currentIndex = Array.from(items).findIndex(item => item.classList.contains('selected'));

    // Get the buttons for this specific carousel
    const prevButton = carousel.nextElementSibling.querySelector('.carousel-prev');
    const nextButton = carousel.nextElementSibling.querySelector('.carousel-next');

    function moveToSelected(element) {
        let selected;
        let newIndex;
        
        if (element === "next") {
            newIndex = (currentIndex + 1) % totalItems;
            selected = items[newIndex];
        } else if (element === "prev") {
            newIndex = (currentIndex - 1 + totalItems) % totalItems;
            selected = items[newIndex];
        } else {
            selected = element;
            newIndex = parseInt(selected.dataset.index);
        }

        if (!selected) return;
        currentIndex = newIndex;

        // Reset all classes
        items.forEach(item => {
            item.className = 'carousel-item';
        });

        // Calculate positions relative to the selected item
        items.forEach((item, index) => {
            const position = (index - currentIndex + totalItems) % totalItems;
            const className = getClassName(position);
            item.classList.add(className);
        });
    }

    function getClassName(position) {
        switch (position) {
            case 0: return 'selected';
            case 1: return 'next';
            case 2: return 'nextRightSecond';
            case totalItems - 2: return 'prevLeftSecond';
            case totalItems - 1: return 'prev';
            default: return position > 2 ? 'hideRight' : 'hideLeft';
        }
    }

    // Event Listeners
    prevButton.addEventListener('click', () => moveToSelected('prev'));
    nextButton.addEventListener('click', () => moveToSelected('next'));

    items.forEach(item => {
        item.addEventListener('click', function() {
            moveToSelected(this);
        });
    });

    // Optional: Separate autoplay for each carousel
    let autoplay = setInterval(() => moveToSelected('next'), 5000);
    
    carousel.addEventListener('mouseenter', () => clearInterval(autoplay));
    carousel.addEventListener('mouseleave', () => {
        autoplay = setInterval(() => moveToSelected('next'), 5000);
    });
}