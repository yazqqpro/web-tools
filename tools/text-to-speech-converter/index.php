<?php
$page_title = "Text to Speech Converter";
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
.tts-converter-container {
    max-width: 1000px;
    margin: 0 auto;
}

/* Form Card Enhancement */
.converter-form-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    border: none;
    overflow: hidden;
    transition: var(--transition);
}

.converter-form-card:hover {
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

.text-input-wrapper {
    position: relative;
    margin-bottom: 1.5rem;
}

.text-input {
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 1rem;
    font-size: 1rem;
    transition: var(--transition);
    resize: vertical;
    min-height: 150px;
}

.text-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
}

.char-counter {
    position: absolute;
    bottom: 0.5rem;
    right: 1rem;
    font-size: 0.85rem;
    color: #6c757d;
    background: rgba(255, 255, 255, 0.9);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
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

.form-range {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
}

.form-range::-webkit-slider-thumb {
    background: var(--primary-gradient);
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
}

.form-range::-moz-range-thumb {
    background: var(--primary-gradient);
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
}

/* Enhanced Convert Button */
.convert-btn {
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

.convert-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.convert-btn:hover::before {
    left: 100%;
}

.convert-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.convert-btn:disabled {
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
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    overflow: hidden;
    transition: var(--transition);
}

.result-header {
    background: var(--success-gradient);
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.result-body {
    padding: 2rem;
}

.audio-player-wrapper {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    text-align: center;
}

.audio-player {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
}

.result-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
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

.btn-regenerate {
    background: var(--warning-gradient);
    color: white;
    box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
}

.btn-regenerate:hover {
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(240, 147, 251, 0.4);
}

/* Voice Selection Enhancement */
.voice-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.voice-option {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
}

.voice-option:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.voice-option.selected {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.1);
}

.voice-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #667eea;
}

.voice-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.voice-description {
    font-size: 0.85rem;
    color: #6c757d;
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

/* Responsive Design */
@media (max-width: 768px) {
    .form-header h1 {
        font-size: 2rem;
    }
    
    .form-body {
        padding: 1.5rem;
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
    
    .voice-grid {
        grid-template-columns: 1fr;
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

/* Range Input Styling */
.range-container {
    margin: 1rem 0;
}

.range-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.range-value {
    background: #667eea;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>

<div class="tts-converter-container">
    <div class="converter-form-card">
        <div class="form-header">
            <h1><i class="fas fa-volume-up me-2"></i>Text to Speech Converter</h1>
            <p>Transform your text into natural-sounding speech with AI-powered voices</p>
        </div>

        <div class="form-body">
            <div class="form-section">
                <h5><i class="fas fa-edit me-2"></i>Text Input</h5>
                <div class="text-input-wrapper">
                    <textarea id="textInput" class="form-control text-input" placeholder="Enter your text here... You can write up to 5000 characters for conversion to speech."></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span>/5000
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="form-section">
                        <h5><i class="fas fa-microphone me-2"></i>Voice Selection</h5>
                        <select id="voiceSelect" class="form-select">
                            <option value="en-US-AriaNeural">Aria (English US) - Female</option>
                            <option value="en-US-DavisNeural">Davis (English US) - Male</option>
                            <option value="en-US-JennyNeural">Jenny (English US) - Female</option>
                            <option value="en-US-GuyNeural">Guy (English US) - Male</option>
                            <option value="en-GB-SoniaNeural">Sonia (English UK) - Female</option>
                            <option value="en-GB-RyanNeural">Ryan (English UK) - Male</option>
                            <option value="id-ID-ArdiNeural">Ardi (Indonesian) - Male</option>
                            <option value="id-ID-GadisNeural">Gadis (Indonesian) - Female</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-section">
                        <h5><i class="fas fa-cog me-2"></i>Audio Settings</h5>
                        <div class="range-container">
                            <div class="range-label">
                                <label for="speedRange">Speech Speed</label>
                                <span class="range-value" id="speedValue">1.0x</span>
                            </div>
                            <input type="range" class="form-range" id="speedRange" min="0.5" max="2.0" step="0.1" value="1.0">
                        </div>
                        <div class="range-container">
                            <div class="range-label">
                                <label for="pitchRange">Pitch</label>
                                <span class="range-value" id="pitchValue">0%</span>
                            </div>
                            <input type="range" class="form-range" id="pitchRange" min="-50" max="50" step="5" value="0">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-3">
                <button id="convertBtn" class="btn convert-btn w-100">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" style="display: none;"></span>
                    <span id="btnText"><i class="fas fa-play me-2"></i>Convert to Speech</span>
                </button>
                <button type="button" class="btn action-btn btn-regenerate" data-bs-toggle="modal" data-bs-target="#supportModal">
                    <i class="fas fa-heart me-2"></i>Support Development
                </button>
            </div>
        </div>
    </div>
    
    <div id="loadingContainer" class="loading-container">
        <div class="loading-spinner"></div>
        <p class="loading-text">Converting your text to speech...</p>
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
        <p class="mt-2 text-muted">This may take 10-30 seconds</p>
    </div>
    
    <div id="resultContainer" class="result-container" style="display:none;">
        <div class="result-header">
            <h4><i class="fas fa-check-circle me-2"></i>Speech Generated Successfully!</h4>
        </div>
        <div class="result-body">
            <div class="audio-player-wrapper">
                <audio id="audioPlayer" class="audio-player" controls>
                    Your browser does not support the audio element.
                </audio>
            </div>
            <div class="result-actions">
                <a id="downloadBtn" href="#" class="action-btn btn-download" download="speech.mp3">
                    <i class="fas fa-download me-2"></i>Download Audio
                </a>
                <button id="regenerateBtn" class="action-btn btn-regenerate">
                    <i class="fas fa-sync-alt me-2"></i>Generate Again
                </button>
            </div>
        </div>
    </div>
    
    <div id="errorMessage" class="alert error-message" style="display: none;"></div>
</div>

<!-- Support Modal -->
<div class="modal fade" id="supportModal" tabindex="-1" aria-labelledby="supportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supportModalLabel">
                    <i class="fas fa-heart me-2"></i>Support Our Development
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p class="lead">Love using our Text to Speech Converter?</p>
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
    // DOM Elements
    const convertBtn = document.getElementById('convertBtn');
    const spinner = convertBtn.querySelector('.spinner-border');
    const btnText = convertBtn.querySelector('#btnText');
    const textInput = document.getElementById('textInput');
    const charCount = document.getElementById('charCount');
    const voiceSelect = document.getElementById('voiceSelect');
    const speedRange = document.getElementById('speedRange');
    const pitchRange = document.getElementById('pitchRange');
    const speedValue = document.getElementById('speedValue');
    const pitchValue = document.getElementById('pitchValue');
    const loadingContainer = document.getElementById('loadingContainer');
    const resultContainer = document.getElementById('resultContainer');
    const audioPlayer = document.getElementById('audioPlayer');
    const downloadBtn = document.getElementById('downloadBtn');
    const regenerateBtn = document.getElementById('regenerateBtn');
    const errorMessage = document.getElementById('errorMessage');

    // Character counter
    textInput.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        
        if (count > 5000) {
            charCount.style.color = '#dc3545';
            this.style.borderColor = '#dc3545';
        } else {
            charCount.style.color = '#6c757d';
            this.style.borderColor = '#e9ecef';
        }
    });

    // Range input updates
    speedRange.addEventListener('input', function() {
        speedValue.textContent = this.value + 'x';
    });

    pitchRange.addEventListener('input', function() {
        const value = parseInt(this.value);
        pitchValue.textContent = (value >= 0 ? '+' : '') + value + '%';
    });

    // Functions
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

    function showResult(audioUrl) {
        audioPlayer.src = audioUrl;
        downloadBtn.href = audioUrl;
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

    // Convert button click handler
    convertBtn.addEventListener('click', async function() {
        const text = textInput.value.trim();
        
        if (!text) {
            showError('Please enter some text to convert to speech.');
            textInput.focus();
            return;
        }

        if (text.length > 5000) {
            showError('Text is too long. Please limit to 5000 characters.');
            textInput.focus();
            return;
        }

        // Reset states
        hideLoading();
        resultContainer.style.display = 'none';
        errorMessage.style.display = 'none';
        
        // Show loading
        showLoading();
        convertBtn.disabled = true;
        spinner.style.display = 'inline-block';
        btnText.innerHTML = '<i class="fas fa-cog fa-spin me-2"></i>Converting...';
        
        try {
            const response = await fetch('tts_proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    text: text,
                    voice: voiceSelect.value,
                    speed: parseFloat(speedRange.value),
                    pitch: parseInt(pitchRange.value)
                })
            });
            
            const data = await response.json();
            
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to convert text to speech. Please try again.');
            }

            hideLoading();
            showResult(data.audioUrl);

        } catch (error) {
            console.error('Error:', error);
            hideLoading();
            
            let userMessage = `Conversion failed: ${error.message}`;
            if (error.message.includes('network') || error.message.includes('timeout')) {
                userMessage += '<br><br><strong>💡 Try:</strong> Check your internet connection and try again.';
            }
            showError(userMessage);
        } finally {
            convertBtn.disabled = false;
            spinner.style.display = 'none';
            btnText.innerHTML = '<i class="fas fa-play me-2"></i>Convert to Speech';
        }
    });

    // Regenerate button
    regenerateBtn.addEventListener('click', function() {
        convertBtn.click();
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

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