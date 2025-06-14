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
                <span>&copy; <?php echo date("Y"); ?> Webtools Directory. All rights reserved.</span>
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

<!-- Custom JS (Replikasi dari main.js) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Back to Top Button Logic ---
    const backToTopButton = document.getElementById('back-to-top');
    if (backToTopButton) {
        window.onscroll = function() {
            if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        };
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // --- Search Functionality ---
    const searchInput = document.getElementById('tool-search-input');
    const toolsGrid = document.getElementById('tools-grid');
    const noResults = document.getElementById('no-results');

    if (searchInput && toolsGrid) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const toolLinks = toolsGrid.querySelectorAll('.tool-card-link');
            let visibleCount = 0;

            toolLinks.forEach(link => {
                const toolName = link.getAttribute('data-tool-name');
                const toolDescription = link.getAttribute('data-tool-description');
                const isMatch = toolName.includes(searchTerm) || toolDescription.includes(searchTerm);
                
                if (isMatch) {
                    link.classList.remove('hidden');
                    visibleCount++;
                } else {
                    link.classList.add('hidden');
                }
            });

            if (noResults) {
                noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        });
    }

    // --- SCRIPT PELACAKAN PENGGUNAAN TOOLS (SERVER-SIDE) ---
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
                    // console.log('Tool usage tracked on server:', currentToolSlug);
                } else {
                    // console.error('Failed to track tool usage on server:', data.message); 
                }
            })
            .catch(error => {
                // console.error('Error sending tool usage to server:', error); 
            });
        }
    }
});
</script>

</body>
</html>