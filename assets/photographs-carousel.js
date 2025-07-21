// Photographs Carousel Functionality
document.addEventListener('DOMContentLoaded', function() {
    const photoCarousels = document.querySelectorAll('.photo-carousel');
    
    photoCarousels.forEach(carousel => {
        const slides = carousel.querySelectorAll('.photo-slide');
        
        if (slides.length <= 1) return; // Skip if only one slide
        
        let currentSlide = 0;
        
        function nextSlide() {
            // Remove active class from current slide
            slides[currentSlide].classList.remove('active');
            
            // Move to next slide (loop back to 0 if at end)
            currentSlide = (currentSlide + 1) % slides.length;
            
            // Add active class to new slide
            slides[currentSlide].classList.add('active');
        }
        
        // Start the carousel with 3 second interval
        setInterval(nextSlide, 3000);
    });
});