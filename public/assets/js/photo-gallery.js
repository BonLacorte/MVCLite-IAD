const galleryItems = [
    {
        id: "strategic-planning-2021",
        title: "IAD STRATEGIC PLANNING 2021",
        date: "December 2021",
        location: "Conference Room",
        images: [
            "assets/field4.png",
            "assets/field1.png",
            "assets/field4.png",
            "assets/field1.png",
            "assets/field4.png",
            "assets/field1.png",
            "assets/field4.png",
        ]
    },
    {
        id: "office-planning-2024",
        title: "2024 IAD Office Planning",
        date: "February 21-23, 2024",
        location: "7/F DOF Function Room",
        images: [
            "assets/field2.jpg",
            "assets/field3.jpg",
            "assets/field2.jpg",
            "assets/field3.jpg",
            "assets/field2.jpg",
            "assets/field3.jpg",
        ]
    }
];

class PhotoGallery {
    constructor() {
        this.currentIndex = 0;
        this.imagesPerView = 3;
        this.initialize();
    }

    initialize() {
        const container = document.getElementById('gallery-container');
        if (!container) return;

        galleryItems.forEach((item, albumIndex) => {
            const galleryHTML = this.createGalleryHTML(item, albumIndex);
            container.innerHTML += galleryHTML;
        });

        this.setupEventListeners();
    }

    createGalleryHTML(item, albumIndex) {
        const maxIndex = Math.max(0, item.images.length - this.imagesPerView);
        const dotsHTML = Array.from({ length: Math.ceil(item.images.length / this.imagesPerView) })
            .map((_, index) => `
                <button
                    class="w-2 h-2 rounded-full transition-colors ${index === 0 ? 'bg-primary' : 'bg-muted'}"
                    data-slide="${index}"
                    data-album="${albumIndex}"
                    aria-label="Go to slide group ${index + 1}"
                ></button>
            `).join('');

        const imagesHTML = item.images.map((image, imageIndex) => `
            <div class="w-1/3 flex-shrink-0 p-1">
                <div class="relative aspect-video">
                    <img
                        src="${image}"
                        alt="${item.title} - Image ${imageIndex + 1}"
                        class="object-cover rounded-lg w-full h-full"
                    />
                </div>
            </div>
        `).join('');

        return `
            <div class="space-y-4">
                <div class="space-y-1">
                    <h3 class="text-xl font-bold">${item.title}</h3>
                    <p class="text-muted-foreground">${item.date} - ${item.location}</p>
                </div>
                <div class="card relative overflow-hidden">
                    <div class="p-0">
                        <div class="relative">
                            <div class="flex transition-transform duration-300 ease-in-out" data-slider="${albumIndex}">
                                ${imagesHTML}
                            </div>
                            <button
                                class="absolute left-2 top-1/2 -translate-y-1/2 bg-background/80 hover:bg-background/90 z-10 p-2 rounded-full hidden"
                                data-action="prev"
                                data-album="${albumIndex}"
                                aria-label="Previous images"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button
                                class="absolute right-2 top-1/2 -translate-y-1/2 bg-background/80 hover:bg-background/90 z-10 p-2 rounded-full"
                                data-action="next"
                                data-album="${albumIndex}"
                                aria-label="Next images"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex justify-center gap-2 p-4">
                            ${dotsHTML}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            const target = e.target.closest('[data-action], [data-slide]');
            if (!target) return;

            const albumIndex = parseInt(target.dataset.album);
            const slider = document.querySelector(`[data-slider="${albumIndex}"]`);
            
            if (target.dataset.action === 'next') {
                this.nextSlide(slider, albumIndex);
            } else if (target.dataset.action === 'prev') {
                this.prevSlide(slider, albumIndex);
            } else if (target.dataset.slide) {
                this.goToSlide(slider, parseInt(target.dataset.slide), albumIndex);
            }
        });
    }

    nextSlide(slider, albumIndex) {
        const currentTransform = this.getCurrentTransform(slider);
        const maxIndex = Math.max(0, galleryItems[albumIndex].images.length - this.imagesPerView);
        const newIndex = Math.min(currentTransform + 1, maxIndex);
        this.updateSlider(slider, newIndex, albumIndex);
    }

    prevSlide(slider, albumIndex) {
        const currentTransform = this.getCurrentTransform(slider);
        const newIndex = Math.max(currentTransform - 1, 0);
        this.updateSlider(slider, newIndex, albumIndex);
    }

    goToSlide(slider, index, albumIndex) {
        this.updateSlider(slider, index * this.imagesPerView, albumIndex);
    }

    getCurrentTransform(slider) {
        const transform = slider.style.transform;
        const match = transform.match(/-?\d+/);
        return match ? parseInt(match[0]) / (100 / this.imagesPerView) : 0;
    }

    updateSlider(slider, index, albumIndex) {
        slider.style.transform = `translateX(-${index * (100 / this.imagesPerView)}%)`;
        this.updateNavigationButtons(index, albumIndex);
        this.updateDots(index, albumIndex);
    }

    updateNavigationButtons(index, albumIndex) {
        const prevButton = document.querySelector(`[data-action="prev"][data-album="${albumIndex}"]`);
        const nextButton = document.querySelector(`[data-action="next"][data-album="${albumIndex}"]`);
        const maxIndex = Math.max(0, galleryItems[albumIndex].images.length - this.imagesPerView);

        prevButton.classList.toggle('hidden', index === 0);
        nextButton.classList.toggle('hidden', index >= maxIndex);
    }

    updateDots(index, albumIndex) {
        const dots = document.querySelectorAll(`[data-slide][data-album="${albumIndex}"]`);
        const currentDotIndex = Math.floor(index / this.imagesPerView);
        
        dots.forEach((dot, i) => {
            dot.classList.toggle('bg-primary', i === currentDotIndex);
            dot.classList.toggle('bg-muted', i !== currentDotIndex);
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new PhotoGallery();
});