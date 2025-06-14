<?php
$page_title = "AI Image Generator";
$path_prefix = '../../'; 
include $path_prefix . 'header.php';
?>

<style>
/* Enhanced Professional Styling */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    --card-shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
    --border-radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Main Container Enhancement */
.ai-generator-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Form Card Enhancement */
.generator-form-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: none;
    overflow: hidden;
    transition: var(--transition);
}

.generator-form-card:hover {
    box-shadow: var(--card-shadow-hover);
}

.form-header {
    background: var(--primary-gradient);
    color: white;
    padding: 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.form-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="pattern" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23pattern)"/></svg>');
}

.form-header h1 {
    position: relative;
    z-index: 2;
    margin: 0;
    font-size: 2.5rem;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-header p {
    position: relative;
    z-index: 2;
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

/* Enhanced Form Styling */
.form-body {
    padding: 2.5rem;
}

.form-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
    border-left: 4px solid #667eea;
}

.form-section h5 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.prompt-wrapper {
    position: relative;
    margin-bottom: 1.5rem;
}

.prompt-input {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 1rem 3rem 1rem 1rem;
    font-size: 1rem;
    transition: var(--transition);
    resize: vertical;
    min-height: 120px;
}

.prompt-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
}

.random-prompt-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    cursor: pointer;
    color: #6c757d;
    font-size: 1.3rem;
    transition: var(--transition);
    z-index: 10;
    padding: 0.5rem;
    border-radius: 50%;
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.random-prompt-icon:hover {
    color: #667eea;
    transform: scale(1.1) rotate(180deg);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

/* Enhanced Form Controls */
.form-select, .form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    transition: var(--transition);
}

.form-select:focus, .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

/* Enhanced Generate Button */
.generate-btn {
    background: var(--primary-gradient);
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    color: white;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.generate-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.generate-btn:hover::before {
    left: 100%;
}

.generate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.generate-btn:disabled {
    opacity: 0.7;
    transform: none;
    cursor: not-allowed;
}

/* Loading Animation Enhancement */
.loading-container {
    display: none;
    text-align: center;
    padding: 3rem 2rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin-top: 2rem;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    font-size: 1.1rem;
    color: #495057;
    margin: 0;
}

.loading-progress {
    width: 100%;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    margin-top: 1rem;
    overflow: hidden;
}

.loading-progress-bar {
    height: 100%;
    background: var(--primary-gradient);
    border-radius: 3px;
    animation: progress 2s ease-in-out infinite;
}

@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}

/* Result Container Enhancement */
.result-container {
    margin-top: 2rem;
    text-align: center;
}

.result-image-wrapper {
    position: relative;
    display: inline-block;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
}

.result-image-wrapper:hover {
    transform: scale(1.02);
    box-shadow: var(--card-shadow-hover);
}

.result-image {
    max-width: 100%;
    height: auto;
    display: block;
    border-radius: var(--border-radius);
}

.result-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1.5rem;
}

.action-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-download {
    background: var(--success-gradient);
    color: white;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
}

.btn-download:hover {
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(79, 172, 254, 0.4);
}

.btn-support {
    background: var(--warning-gradient);
    color: white;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
}

.btn-support:hover {
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(240, 147, 251, 0.4);
}

/* History Section Enhancement */
.history-section {
    margin-top: 4rem;
}

.history-header {
    text-align: center;
    margin-bottom: 2rem;
}

.history-title {
    font-size: 2rem;
    font-weight: 700;
    color: #495057;
    margin-bottom: 0.5rem;
}

.history-subtitle {
    color: #6c757d;
    font-size: 1.1rem;
}

.history-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.history-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    text-decoration: none;
    color: inherit;
    display: block;
}

.history-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--card-shadow-hover);
    color: inherit;
    text-decoration: none;
}

.history-image-wrapper {
    width: 100%;
    padding-top: 75%;
    position: relative;
    background: #f8f9fa;
    overflow: hidden;
}

.history-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
}

.history-card:hover .history-image {
    transform: scale(1.05);
}

.history-info {
    padding: 1rem;
}

.history-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
}

.history-ip {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 500;
}

.history-time {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Pagination Enhancement */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: var(--card-shadow);
}

.page-link {
    border: none;
    padding: 0.75rem 1rem;
    color: #495057;
    transition: var(--transition);
}

.page-link:hover {
    background: #667eea;
    color: white;
}

.page-item.active .page-link {
    background: var(--primary-gradient);
    border: none;
}

/* Loading States */
.history-loader {
    display: none;
    text-align: center;
    padding: 3rem;
}

.history-loader .spinner-border {
    width: 3rem;
    height: 3rem;
    color: #667eea;
}

/* Error Messages */
.error-message {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
    border: none;
}

/* Modal Enhancements */
.modal-content {
    border-radius: var(--border-radius);
    border: none;
    box-shadow: var(--card-shadow-hover);
}

.modal-header {
    background: var(--primary-gradient);
    color: white;
    border-bottom: none;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.modal-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.modal-prompt {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    word-wrap: break-word;
    max-height: 200px;
    overflow-y: auto;
    margin-top: 1rem;
    border-left: 4px solid #667eea;
}

.modal-history-info {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
    margin-top: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-header h1 {
        font-size: 2rem;
    }
    
    .form-body {
        padding: 1.5rem;
    }
    
    .history-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .result-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .action-btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-up {
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<div class="ai-generator-container">
    <div class="generator-form-card">
        <div class="form-header">
            <h1><i class="fas fa-paint-brush me-2"></i>AI Image Generator</h1>
            <p>Transform your imagination into stunning visuals with advanced AI technology</p>
        </div>

        <div class="form-body">
            <div class="form-section">
                <h5><i class="fas fa-lightbulb me-2"></i>Creative Prompt</h5>
                <div class="prompt-wrapper">
                    <textarea id="promptInput" class="form-control prompt-input" placeholder="Describe your vision in detail... e.g., 'A majestic dragon soaring through a sunset sky over ancient mountains, cinematic lighting, highly detailed, 8K resolution'"></textarea>
                    <i id="randomPromptBtn" class="fas fa-dice random-prompt-icon" title="Generate Random Prompt" data-bs-toggle="tooltip"></i>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-section">
                        <h5><i class="fas fa-expand-arrows-alt me-2"></i>Image Dimensions</h5>
                        <select id="sizeSelect" class="form-select">
                            <option value="1024x1024" selected>Square (1024×1024) - Perfect Balance</option>
                            <option value="512x512">Small Square (512×512) - Quick Generation</option>
                            <option value="720x1280">Portrait (720×1280) - Mobile Wallpaper</option>
                            <option value="1280x720">Landscape (1280×720) - Desktop Wallpaper</option>
                            <option value="1792x1024">Wide Landscape (1792×1024) - Panoramic</option>
                            <option value="1024x1792">Tall Portrait (1024×1792) - Poster Style</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-section">
                        <h5><i class="fas fa-robot me-2"></i>AI Model</h5>
                        <select id="modelSelect" class="form-select">
                            <option value="flux" selected>Flux - Balanced Quality & Speed</option>
                            <option value="turbo">Turbo - Fast Generation (NSFW Supported)</option>
                            <option value="dalle3">DALL-E 3 - Premium Quality</option>
                            <option value="stability">Stability AI - Artistic Style</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="safeFilterSwitch" checked>
                    <label class="form-check-label" for="safeFilterSwitch">
                        <i class="fas fa-shield-alt me-2"></i>Safe Content Filter (Recommended)
                    </label>
                </div>
            </div>
            
            <div class="d-grid gap-3">
                <button id="generateImageBtn" class="btn generate-btn w-100">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                    <span id="btnText"><i class="fas fa-magic me-2"></i>Generate Masterpiece</span>
                </button>
                <button type="button" class="btn action-btn btn-support" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fas fa-heart me-2"></i>Support Development
                </button>
            </div>
        </div>
    </div>
    
    <div id="loadingContainer" class="loading-container">
        <div class="loading-spinner"></div>
        <p class="loading-text">Creating your masterpiece...</p>
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
        <p class="mt-2 text-muted">This may take 30-60 seconds</p>
    </div>
    
    <div id="resultContainer" class="result-container" style="display:none;">
        <div class="result-image-wrapper">
            <img id="generatedImage" class="result-image" src="#" alt="Generated Masterpiece">
        </div>
        <div class="result-actions">
            <a id="downloadLink" href="#" class="action-btn btn-download" download="ai-generated-masterpiece.webp">
                <i class="fas fa-download me-2"></i>Download Image
            </a>
        </div>
    </div>
    
    <div id="errorMessage" class="alert error-message" style="display: none;"></div>

    <div class="history-section">
        <div class="history-header">
            <h2 class="history-title">Recent Creations</h2>
            <p class="history-subtitle">Explore the latest AI-generated masterpieces from our community</p>
        </div>
        <div id="historyGrid" class="history-grid"></div>
        <div class="history-loader" id="historyLoader">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading gallery...</p>
        </div>
        <div id="paginationContainer" class="pagination-container"></div>
        <p id="noHistoryMessage" class="text-center text-muted" style="display: none;">No creations found. Be the first to generate something amazing!</p>
    </div>
</div>

<!-- Enhanced Image Detail Modal -->
<div class="modal fade" id="imageDetailModal" tabindex="-1" aria-labelledby="imageDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageDetailModalLabel">
                    <i class="fas fa-image me-2"></i>Masterpiece Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalFullImage" src="#" class="modal-image" alt="Full Size Image">
                <div id="modalPrompt" class="modal-prompt text-start"></div>
                <div id="modalHistoryInfo" class="modal-history-info text-start"></div>
            </div>
            <div class="modal-footer">
                <a id="downloadModalImageBtn" href="#" class="btn action-btn btn-download" download="ai-masterpiece.webp">
                    <i class="fas fa-download me-2"></i>Download Image
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Support Modal -->
<div class="modal fade" id="coffeeModal" tabindex="-1" aria-labelledby="coffeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="coffeeModalLabel">
                    <i class="fas fa-heart me-2"></i>Support Our Development
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="lead">Love creating with our AI Image Generator?</p>
                <p>Your support helps us maintain servers and develop new features!</p>
                <img src="/qris.jpeg" alt="Support via QRIS" class="img-fluid rounded shadow" style="max-width: 300px;">
                <p class="mt-3 text-muted">Scan the QR code above to support us via QRIS</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Every contribution helps keep this service free and improving!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced DOM Elements
    const generateBtn = document.getElementById('generateImageBtn');
    const spinner = generateBtn.querySelector('.spinner-border');
    const btnText = generateBtn.querySelector('#btnText');
    const promptInput = document.getElementById('promptInput');
    const resultContainer = document.getElementById('resultContainer');
    const generatedImage = document.getElementById('generatedImage');
    const errorMessage = document.getElementById('errorMessage');
    const sizeSelect = document.getElementById('sizeSelect');
    const modelSelect = document.getElementById('modelSelect');
    const safeFilterSwitch = document.getElementById('safeFilterSwitch');
    const randomPromptBtn = document.getElementById('randomPromptBtn');
    const downloadLink = document.getElementById('downloadLink');
    const historyGrid = document.getElementById('historyGrid');
    const paginationContainer = document.getElementById('paginationContainer');
    const noHistoryMessage = document.getElementById('noHistoryMessage');
    const historyLoader = document.getElementById('historyLoader');
    const loadingContainer = document.getElementById('loadingContainer');

    // Enhanced Random Prompts
    const randomPrompts = [
        "A majestic phoenix rising from golden flames against a starlit sky, cinematic lighting, highly detailed, 8K resolution",
        "Futuristic cyberpunk cityscape at night with neon reflections on wet streets, flying cars, and holographic advertisements",
        "Ancient mystical forest with glowing mushrooms, ethereal mist, and magical creatures hiding among towering trees",
        "Steampunk airship floating above Victorian-era London, brass gears, steam clouds, and intricate mechanical details",
        "Underwater palace made of coral and pearls, with bioluminescent sea creatures swimming around ornate architecture",
        "Dragon perched on a crystal mountain peak during aurora borealis, scales reflecting northern lights, epic fantasy art",
        "Space station orbiting a distant planet with multiple moons, detailed sci-fi architecture, cosmic background",
        "Enchanted library with floating books, magical orbs of light, and ancient scrolls in a tower reaching the clouds",
        "Samurai warrior in cherry blossom garden during sunset, traditional armor, katana, peaceful yet powerful atmosphere",
        "Crystal cave with rainbow light refractions, underground waterfall, and precious gems embedded in rock formations"
    ];

    // Enhanced Functions
    function getRandomPrompt() {
        return randomPrompts[Math.floor(Math.random() * randomPrompts.length)];
    }

    function showLoading() {
        loadingContainer.style.display = 'block';
        loadingContainer.classList.add('fade-in');
        resultContainer.style.display = 'none';
        errorMessage.style.display = 'none';
    }

    function hideLoading() {
        loadingContainer.style.display = 'none';
        loadingContainer.classList.remove('fade-in');
    }

    function showResult(imageUrl) {
        generatedImage.src = imageUrl;
        downloadLink.href = imageUrl;
        resultContainer.style.display = 'block';
        resultContainer.classList.add('slide-up');
        
        // Smooth scroll to result
        setTimeout(() => {
            resultContainer.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }, 100);
    }

    function showError(message) {
        errorMessage.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>${message}</div>
            </div>
        `;
        errorMessage.style.display = 'block';
        errorMessage.classList.add('fade-in');
    }

    // Enhanced History Rendering
    async function renderHistoryPage(page) {
        historyLoader.style.display = 'block';
        historyGrid.innerHTML = '';
        paginationContainer.innerHTML = '';
        noHistoryMessage.style.display = 'none';

        try {
            const response = await fetch(`history_loader.php?page=${page}`);
            if (!response.ok) {
                throw new Error('Failed to load gallery');
            }
            const data = await response.json();

            if (!data.items || data.items.length === 0) {
                noHistoryMessage.style.display = 'block';
                return;
            }

            data.items.forEach((item, index) => {
                const ipAddress = item.ip || 'anonymous';
                const timestamp = new Date((item.timestamp || 0) * 1000);
                const timestampFormatted = timestamp.toLocaleString('en-US', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });

                const card = document.createElement('a');
                card.href = '#';
                card.className = 'history-card';
                card.setAttribute('data-bs-toggle', 'modal');
                card.setAttribute('data-bs-target', '#imageDetailModal');
                card.setAttribute('data-full-image', escapeHTML(item.imagekit_url));
                card.setAttribute('data-prompt', escapeHTML(item.prompt));
                card.setAttribute('data-ip-address', escapeHTML(ipAddress));
                card.setAttribute('data-timestamp', escapeHTML(timestampFormatted));
                
                // Add animation delay
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');

                card.innerHTML = `
                    <div class="history-image-wrapper">
                        <img src="${escapeHTML(item.imagekit_url)}" alt="AI Generated Art" class="history-image" loading="lazy">
                    </div>
                    <div class="history-info">
                        <div class="history-meta">
                            <span class="history-ip">
                                <i class="fas fa-user-secret"></i>
                                ${escapeHTML(ipAddress)}
                            </span>
                            <span class="history-time">
                                <i class="fas fa-clock"></i>
                                ${escapeHTML(timestampFormatted)}
                            </span>
                        </div>
                    </div>
                `;
                
                historyGrid.appendChild(card);
            });

            renderPagination(data.totalPages, page);

        } catch (error) {
            console.error('Error fetching history:', error);
            noHistoryMessage.textContent = 'Failed to load gallery. Please try again later.';
            noHistoryMessage.style.display = 'block';
        } finally {
            historyLoader.style.display = 'none';
        }
    }

    function renderPagination(totalPages, currentPage) {
        if (totalPages <= 1) return;
        
        let paginationHTML = '<nav><ul class="pagination">';

        // Previous button
        paginationHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;

        // Page numbers
        let startPage = Math.max(currentPage - 2, 1);
        let endPage = Math.min(currentPage + 2, totalPages);

        if (startPage > 1) {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // Next button
        paginationHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;

        paginationHTML += '</ul></nav>';
        paginationContainer.innerHTML = paginationHTML;
    }

    function escapeHTML(str) {
        if (!str) return '';
        const p = document.createElement('p');
        p.textContent = str;
        return p.innerHTML;
    }

    // Enhanced Event Listeners
    randomPromptBtn.addEventListener('click', function() {
        promptInput.value = getRandomPrompt();
        promptInput.focus();
        
        // Add visual feedback
        this.style.transform = 'scale(1.2) rotate(360deg)';
        setTimeout(() => {
            this.style.transform = '';
        }, 300);
    });

    generateBtn.addEventListener('click', async function() {
        const prompt = promptInput.value.trim();
        if (!prompt) {
            showError('Please enter a creative prompt to generate your masterpiece.');
            promptInput.focus();
            return;
        }

        // Reset states
        hideLoading();
        resultContainer.style.display = 'none';
        errorMessage.style.display = 'none';
        
        // Show loading
        showLoading();
        generateBtn.disabled = true;
        spinner.style.display = 'inline-block';
        btnText.innerHTML = '<i class="fas fa-magic me-2"></i>Creating Magic...';
        
        try {
            const response = await fetch('imagen_proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    prompt: prompt,
                    size: sizeSelect.value,
                    model: modelSelect.value,
                    safeFilter: safeFilterSwitch.checked
                })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to generate image. Please try again.');
            }

            hideLoading();
            showResult(data.imageData);
            
            // Reload history to show new creation
            renderHistoryPage(1);

        } catch (error) {
            console.error('Error:', error);
            hideLoading();
            
            let userMessage = `Generation failed: ${error.message}`;
            if (error.message.includes('API eksternal') || error.message.includes('layanan penyimpanan')) {
                userMessage += '<br><br><strong>💡 Try:</strong> Switch to a different <strong>AI Model</strong> or try again in a moment. External services may be busy.';
            }
            showError(userMessage);
        } finally {
            generateBtn.disabled = false;
            spinner.style.display = 'none';
            btnText.innerHTML = '<i class="fas fa-magic me-2"></i>Generate Masterpiece';
        }
    });

    // Enhanced pagination handling
    paginationContainer.addEventListener('click', function(e) {
        e.preventDefault();
        const target = e.target.closest('a[data-page]');
        if (target) {
            const page = parseInt(target.getAttribute('data-page'));
            const currentPageLi = paginationContainer.querySelector('.page-item.active a');
            const currentPage = currentPageLi ? parseInt(currentPageLi.dataset.page) : 1;
            
            if (page > 0 && page !== currentPage) {
                renderHistoryPage(page);
                
                // Smooth scroll to gallery
                historyGrid.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }
        }
    });
    
    // Enhanced modal handling
    const imageDetailModal = document.getElementById('imageDetailModal');
    if (imageDetailModal) {
        imageDetailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const modalFullImage = document.getElementById('modalFullImage');
            const modalPrompt = document.getElementById('modalPrompt');
            const downloadModalImageBtn = document.getElementById('downloadModalImageBtn');
            const modalHistoryInfo = document.getElementById('modalHistoryInfo');
            
            modalFullImage.src = button.getAttribute('data-full-image');
            modalPrompt.textContent = button.getAttribute('data-prompt');
            downloadModalImageBtn.href = button.getAttribute('data-full-image');
            modalHistoryInfo.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1">
                            <i class="fas fa-user-secret me-2 text-muted"></i>
                            <strong>Creator:</strong> ${button.getAttribute('data-ip-address')}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1">
                            <i class="fas fa-clock me-2 text-muted"></i>
                            <strong>Created:</strong> ${button.getAttribute('data-timestamp')}
                        </p>
                    </div>
                </div>
            `;
        });
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize gallery
    renderHistoryPage(1);
    
    // Add smooth scrolling for better UX
    document.documentElement.style.scrollBehavior = 'smooth';
});
</script>

<?php 
if (file_exists($path_prefix . 'footer.php')) {
    include $path_prefix . 'footer.php';
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script></body></html>";
}
?>