document.addEventListener('DOMContentLoaded', function() {
    // Only run on mobile devices
    function initCVDropdowns() {
        const headers = document.querySelectorAll('.cv-header');
        
        headers.forEach(header => {
            header.addEventListener('click', function() {
                // Only work on mobile (768px and below)
                if (window.innerWidth <= 768) {
                    const targetId = this.getAttribute('data-target');
                    const targetEntries = document.getElementById(targetId);
                    const arrow = this.querySelector('.cv-arrow');
                    
                    // Toggle expanded class on both header and entries
                    this.classList.toggle('expanded');
                    targetEntries.classList.toggle('expanded');
                    
                    // Change arrow direction
                    if (this.classList.contains('expanded')) {
                        arrow.textContent = '↑';
                    } else {
                        arrow.textContent = '↓';
                    }
                }
            });
        });
    }
    
    // Initialize dropdowns
    initCVDropdowns();
    
    // Re-initialize on window resize to handle desktop/mobile switches
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop: ensure all sections are visible and reset arrows
            const entries = document.querySelectorAll('.cv-entries');
            const headers = document.querySelectorAll('.cv-header');
            const arrows = document.querySelectorAll('.cv-arrow');
            
            entries.forEach(entry => {
                entry.classList.remove('expanded');
                entry.style.display = ''; // Remove inline style, let CSS handle
            });
            
            headers.forEach(header => {
                header.classList.remove('expanded');
            });
            
            arrows.forEach(arrow => {
                arrow.textContent = '↓';
            });
        } else {
            // Mobile: remove any inline styles and let CSS handle visibility
            const entries = document.querySelectorAll('.cv-entries');
            entries.forEach(entry => {
                entry.style.display = ''; // Remove inline styles
            });
        }
    });
});