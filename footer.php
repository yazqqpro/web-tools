</main> <!-- Menutup tag main dari header.php -->

<footer class="footer mt-auto">
    <div class="container">
        <div class="footer-content">
            <div class="footer-links">
                <?php
                // Diasumsikan $base_url sudah ada dari header.php
                $feedback_url = ($base_url ?? '/') . 'kritik-saran.php';
                ?>
                <a href="<?php echo htmlspecialchars($feedback_url); ?>" class="footer-link">
                    <i class="material-icons">feedback</i>
                    <span>Kritik dan Saran</span>
                </a>
            </div>
            <div class="footer-copyright">
                <span>&copy; <?php echo date("Y"); ?> Webtools Directory. Dibuat dengan ❤️ untuk produktivitas Anda.</span>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" class="back-to-top-btn" title="Kembali ke atas">
    <i class="material-icons">keyboard_arrow_up</i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Back to Top Button Logic ---
    const backToTopButton = document.getElementById('back-to-top');
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
                backToTopButton.style.opacity = '1';
            } else {
                backToTopButton.style.opacity = '0';
                setTimeout(() => {
                    if (window.pageYOffset <= 300) {
                        backToTopButton.style.display = 'none';
                    }
                }, 300);
            }
        });
        
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({ 
                top: 0, 
                behavior: 'smooth' 
            });
        });
    }

    // --- Enhanced Search Functionality ---
    const searchInput = document.getElementById('tool-search-input');
    const toolsGrid = document.getElementById('tools-grid');
    const noResults = document.getElementById('no-results');

    if (searchInput && toolsGrid) {
        // Add search icon animation
        const searchIcon = document.querySelector('.search-icon');
        
        searchInput.addEventListener('focus', function() {
            if (searchIcon) {
                searchIcon.style.color = '#667eea';
                searchIcon.style.transform = 'translateY(-50%) scale(1.1)';
            }
        });
        
        searchInput.addEventListener('blur', function() {
            if (searchIcon) {
                searchIcon.style.color = '#718096';
                searchIcon.style.transform = 'translateY(-50%) scale(1)';
            }
        });

        // Enhanced search with debouncing
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.toLowerCase().trim();
                const toolLinks = toolsGrid.querySelectorAll('.tool-card-link');
                let visibleCount = 0;

                toolLinks.forEach((link, index) => {
                    const toolName = link.getAttribute('data-tool-name');
                    const toolDescription = link.getAttribute('data-tool-description');
                    const isMatch = toolName.includes(searchTerm) || toolDescription.includes(searchTerm);
                    
                    if (isMatch) {
                        link.classList.remove('hidden');
                        link.style.display = 'block';
                        // Add staggered animation
                        link.style.animationDelay = `${index * 0.1}s`;
                        visibleCount++;
                    } else {
                        link.classList.add('hidden');
                        link.style.display = 'none';
                    }
                });

                if (noResults) {
                    if (visibleCount === 0 && searchTerm !== '') {
                        noResults.style.display = 'block';
                        noResults.style.animation = 'fadeInUp 0.5s ease-out';
                    } else {
                        noResults.style.display = 'none';
                    }
                }
            }, 300);
        });
    }

    // --- Tool Usage Tracking ---
    const toolSlugMeta = document.querySelector('meta[name="tool-slug-stats"]');
    if (toolSlugMeta) {
        const currentToolSlug = toolSlugMeta.getAttribute('content');
        if (currentToolSlug) {
            const trackUsageUrl = 'https://app.andrias.web.id/track_tool_usage.php';
            
            fetch(trackUsageUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tool_slug: currentToolSlug })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('Tool usage tracked:', currentToolSlug);
                }
            })
            .catch(error => {
                console.error('Error tracking tool usage:', error);
            });
        }
    }

    // --- Enhanced Tool Card Interactions ---
    const toolCards = document.querySelectorAll('.tool-card');
    toolCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // --- Navbar Scroll Effect ---
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
            }
        });
    }

    // --- Loading States ---
    const links = document.querySelectorAll('a[href^="/tools/"]');
    links.forEach(link => {
        link.addEventListener('click', function() {
            this.classList.add('loading');
        });
    });

    // --- Intersection Observer for Animations ---
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe tool cards for scroll animations
    document.querySelectorAll('.tool-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
});
</script>

</body>
</html>