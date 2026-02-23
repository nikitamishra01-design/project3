/**
 * Testimonials Manager - Slider JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize testimonials slider
        $('.testimonials-container').each(function() {
            var $container = $(this);
            var $slides = $container.find('.testimonial-slide');
            var $prevBtn = $container.find('.testimonial-prev');
            var $nextBtn = $container.find('.testimonial-next');
            var $dots = $container.find('.testimonial-dot');
            
            var currentIndex = 0;
            var totalSlides = $slides.length;
            var autoPlayInterval;
            var isAnimating = false;

            // Function to show specific slide
            function showSlide(index) {
                if (isAnimating || index === currentIndex) {
                    return;
                }

                isAnimating = true;

                // Remove active class from current slide
                $slides.eq(currentIndex).removeClass('active');
                $dots.eq(currentIndex).removeClass('active');

                // Update index
                currentIndex = index;

                // Handle wrapping
                if (currentIndex < 0) {
                    currentIndex = totalSlides - 1;
                } else if (currentIndex >= totalSlides) {
                    currentIndex = 0;
                }

                // Add active class to new slide
                $slides.eq(currentIndex).addClass('active');
                $dots.eq(currentIndex).addClass('active');

                // Reset animation flag after transition
                setTimeout(function() {
                    isAnimating = false;
                }, 500);
            }

            // Function to go to next slide
            function nextSlide() {
                showSlide(currentIndex + 1);
            }

            // Function to go to previous slide
            function prevSlide() {
                showSlide(currentIndex - 1);
            }

            // Initialize - show first slide
            if (totalSlides > 0) {
                $slides.eq(0).addClass('active');
                $dots.eq(0).addClass('active');
            }

            // Previous button click
            $prevBtn.on('click', function(e) {
                e.preventDefault();
                prevSlide();
                resetAutoPlay();
            });

            // Next button click
            $nextBtn.on('click', function(e) {
                e.preventDefault();
                nextSlide();
                resetAutoPlay();
            });

            // Dot navigation click
            $dots.on('click', function(e) {
                e.preventDefault();
                var index = $(this).data('index');
                showSlide(index);
                resetAutoPlay();
            });

            // Keyboard navigation
            $(document).on('keydown', function(e) {
                if ($container.is(':visible')) {
                    if (e.key === 'ArrowLeft') {
                        prevSlide();
                        resetAutoPlay();
                    } else if (e.key === 'ArrowRight') {
                        nextSlide();
                        resetAutoPlay();
                    }
                }
            });

            // Touch swipe support
            var touchStartX = 0;
            var touchEndX = 0;

            $container.on('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            });

            $container.on('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });

            function handleSwipe() {
                var swipeThreshold = 50;
                var diff = touchStartX - touchEndX;

                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        // Swipe left - next slide
                        nextSlide();
                    } else {
                        // Swipe right - previous slide
                        prevSlide();
                    }
                    resetAutoPlay();
                }
            }

            // Auto play functionality
            function startAutoPlay() {
                autoPlayInterval = setInterval(function() {
                    nextSlide();
                }, 5000); // Change slide every 5 seconds
            }

            function stopAutoPlay() {
                if (autoPlayInterval) {
                    clearInterval(autoPlayInterval);
                }
            }

            function resetAutoPlay() {
                stopAutoPlay();
                startAutoPlay();
            }

            // Start auto play
            if (totalSlides > 1) {
                startAutoPlay();
            }

            // Pause on hover
            $container.on('mouseenter', function() {
                stopAutoPlay();
            });

            $container.on('mouseleave', function() {
                if (totalSlides > 1) {
                    startAutoPlay();
                }
            });
        });
    });

})(jQuery);
