document.addEventListener('DOMContentLoaded', function() {
    const stacks = document.querySelectorAll('.image-stack');
    
    stacks.forEach(stack => {
        const images = Array.from(stack.querySelectorAll('img'));
        if (images.length === 0) return;

        let currentIndex = 0;
        const totalImages = images.length;

        function updatePositions() {
            images.forEach((img, index) => {
                // Remove all possible position classes
                img.classList.remove('pos-1', 'pos-2', 'pos-3', 'pos-4', 'pos-5');
                
                // Calculate position relative to currentIndex
                // We want images to cycle through pos-1 to pos-5
                let pos = (index - currentIndex + totalImages) % totalImages;
                
                // If there are more than 5 images, we only show the first 5 in the stack positions
                if (pos < 5) {
                    img.classList.add(`pos-${pos + 1}`);
                }
            });
        }

        // Initial setup
        updatePositions();

        // Function to move to next (right to left movement in indices = left to right visually if we shift index down)
        function nextSlide() {
            // To move "left to right", the item at the end should come to the front.
            // This means decreasing the currentIndex.
            currentIndex = (currentIndex - 1 + totalImages) % totalImages;
            updatePositions();
        }

        // Set interval for 3 seconds
        let autoPlay = setInterval(nextSlide, 3000);

        // Pause on hover
        stack.addEventListener('mouseenter', () => clearInterval(autoPlay));
        stack.addEventListener('mouseleave', () => {
            autoPlay = setInterval(nextSlide, 3000);
        });
    });
});
