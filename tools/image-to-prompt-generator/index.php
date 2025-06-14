<?php
// session_start(); // Dihapus

$page_title = "Image-to-Prompt Generator";
$path_prefix = '../../'; 
$tool_slug_for_stats = "image-to-prompt-generator"; 

// Fungsi CSRF dihapus
// $csrf_token = generate_csrf_token_image_prompt(); 

if (file_exists($path_prefix . 'header.php')) {
    include $path_prefix . 'header.php';
} else {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>" . htmlspecialchars($page_title) . "</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' rel='stylesheet'>";
    echo "<style> body { padding-top: 20px; padding-bottom: 20px; } .footer { padding-top: 1rem; padding-bottom: 1rem; margin-top: 2rem; border-top: 1px solid #dee2e6; text-align: center; } </style>";
    echo "</head><body><main class='container'>";
}
?>

<style>
    .preview-container {
        width: 100%;
        max-width: 300px;
        height: auto;
        min-height: 150px;
        border: 2px dashed #ccc;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        margin: 0 auto 1rem auto;
        background-color: #f8f9fa;
    }
    .preview-container img {
        max-width: 100%;
        max-height: 300px;
        object-fit: contain;
    }
    .preview-container p {
        color: #6c757d;
        margin: 0;
        padding: 1rem;
        text-align: center;
    }
    #imageFile { 
        display: none;
    }
    .local-loading-container {
        display: none; 
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        margin-top: 1rem; 
        border: 1px solid #e0e0e0;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
    }
    .local-loading-container .spinner-border {
        width: 2.5rem; 
        height: 2.5rem;
        color: var(--bs-primary); 
    }
    .local-loading-container p {
        font-size: 1rem;
        margin-top: 0.75rem;
        color: #495057;
    }
    .result-prompt {
        background-color: #e9ecef;
        padding: 1.25rem;
        border-radius: 0.5rem;
        white-space: pre-wrap; 
        word-wrap: break-word;
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.1rem;
        border-left: 5px solid var(--bs-primary);
    }
    .result-actions-container {
        display: flex;
        flex-wrap: wrap; 
        gap: 0.5rem; 
        margin-top: 1rem;
    }
    .analysis-card {
        background-color: #fff;
        border-radius: 0.5rem;
        padding: 1rem;
        border: 1px solid #dee2e6;
        height: 100%;
    }
    .analysis-card h5 {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
        color: var(--bs-primary);
    }
     .analysis-card p {
        margin-bottom: 0.25rem;
        color: #495057;
    }
    .analysis-tags .badge {
        font-size: 0.9rem;
        margin: 0.2rem;
    }
</style>

<div class='tool-page-container'>
    <div class="text-center mb-4">
        <h1><i class="fas fa-image-magic me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="lead text-muted">Unggah gambar dan dapatkan inspirasi prompt kreatif beserta analisisnya!</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form id="promptGeneratorForm" enctype="multipart/form-data">
                <!-- Input CSRF Token Dihapus -->
                <div class="mb-4">
                    <label for="imageFile" class="form-label fs-5">Unggah Gambar (Wajib):</label> 
                    <div id="imagePreviewContainer" class="preview-container" role="button" tabindex="0" aria-label="Klik atau jatuhkan gambar di sini">
                        <img id="imagePreview" src="#" alt="Pratinjau Gambar" style="display:none;">
                        <p id="previewText">Klik atau jatuhkan gambar di sini</p>
                    </div>
                    <input type="file" id="imageFile" name="image" class="form-input" accept="image/png, image/jpeg, image/webp" required>
                    <small class="form-text text-muted">Format yang didukung: PNG, JPG, WEBP. Ukuran maksimal: 5MB.</small>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="styleSelect" class="form-label">Gaya (Opsional):</label> 
                        <select id="styleSelect" name="style" class="form-select form-select-lg">
                            <option value="realistic" selected>Realistis</option>
                            <option value="artistic">Artistik</option>
                            <option value="cinematic">Sinematik</option>
                            <option value="minimalist">Minimalis</option>
                            <option value="abstract">Abstrak</option>
                            <option value="">Tidak Ada Gaya Spesifik</option> 
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="moodSelect" class="form-label">Suasana (Opsional):</label> 
                        <select id="moodSelect" name="mood" class="form-select form-select-lg">
                            <option value="neutral" selected>Netral</option>
                            <option value="happy">Ceria</option>
                            <option value="dramatic">Dramatis</option>
                            <option value="peaceful">Tenang</option>
                            <option value="mysterious">Misterius</option>
                            <option value="">Tidak Ada Mood Spesifik</option> 
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-12">
                        <label for="languageSelect" class="form-label">Bahasa Output Prompt (Opsional):</label> 
                        <select id="languageSelect" name="language" class="form-select form-select-lg">
                            <option value="id" selected>Indonesia</option>
                            <option value="en">Inggris</option>
                        </select>
                    </div>
                </div>
                <button type="submit" id="generateBtn" class="btn btn-primary btn-lg w-100 mt-3 shadow-sm">
                    <i class="fas fa-cogs me-2"></i>Generate Prompt
                </button>
                <div id="localLoadingContainer" class="local-loading-container">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>
                    <p id="localLoadingMessage">Menganalisis gambar dan membuat prompt...</p>
                    <p>Estimasi waktu: <span id="localTimerSeconds">0</span> detik</p>
                </div>
            </form>
            
            <div id="resultContainer" class="mt-4" style="display: none;">
                <!-- Hasil akan di-generate oleh JavaScript di sini -->
            </div>
            <div id="errorMessage" class="alert alert-danger mt-4" style="display: none;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('promptGeneratorForm');
    const imageUpload = document.getElementById('imageFile'); 
    const imagePreviewContainer = document.getElementById('imagePreviewContainer'); 
    const imagePreview = document.getElementById('imagePreview');
    const previewText = document.getElementById('previewText');
    const generateBtn = document.getElementById('generateBtn');
    const localLoadingContainer = document.getElementById('localLoadingContainer');
    const localTimerSeconds = document.getElementById('localTimerSeconds');
    const resultContainer = document.getElementById('resultContainer');
    const errorMessage = document.getElementById('errorMessage');
    
    imagePreviewContainer.addEventListener('click', () => {
        imageUpload.click();
    });

    let timerInterval;
    let secondsElapsed;

    function displayImagePreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.src = e.target.result;
            imagePreview.style.display = 'block';
            previewText.style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
    
    function resetImagePreview() {
        imagePreview.src = '#';
        imagePreview.style.display = 'none';
        previewText.style.display = 'block';
    }

    imagePreviewContainer.addEventListener('dragover', (event) => {
        event.preventDefault();
        imagePreviewContainer.style.borderColor = '#0d6efd';
    });
    imagePreviewContainer.addEventListener('dragleave', () => {
        imagePreviewContainer.style.borderColor = '#ccc';
    });
    imagePreviewContainer.addEventListener('drop', (event) => {
        event.preventDefault();
        imagePreviewContainer.style.borderColor = '#ccc';
        if (event.dataTransfer.files && event.dataTransfer.files[0]) {
            const file = event.dataTransfer.files[0];
            if (file.type.startsWith('image/')) {
                imageUpload.files = event.dataTransfer.files;
                displayImagePreview(file);
            } else {
                alert('Hanya file gambar yang diizinkan (PNG, JPG, WEBP).');
            }
        }
    });

    imageUpload.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            if (file.type.startsWith('image/png') || file.type.startsWith('image/jpeg') || file.type.startsWith('image/webp')) {
                displayImagePreview(file);
                resultContainer.style.display = 'none';
                errorMessage.style.display = 'none';
            } else {
                alert('Format file tidak didukung. Pilih PNG, JPG, atau WEBP.');
                this.value = '';
                resetImagePreview();
            }
        } else {
            resetImagePreview();
        }
    });
     
     function startTimer() {
        secondsElapsed = 0;
        localTimerSeconds.textContent = secondsElapsed;
        if(timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            secondsElapsed++;
            localTimerSeconds.textContent = secondsElapsed;
        }, 1000);
    }
     function stopTimer() {
        clearInterval(timerInterval);
    }

    form.addEventListener('submit', async function(event) {
        event.preventDefault();
        if (!imageUpload.files || imageUpload.files.length === 0) {
            errorMessage.textContent = 'Silakan unggah gambar terlebih dahulu.';
            errorMessage.style.display = 'block';
            resultContainer.style.display = 'none';
            return;
        }
        localLoadingContainer.style.display = 'flex'; 
        errorMessage.style.display = 'none';
        resultContainer.style.display = 'none';
        generateBtn.disabled = true;
        startTimer();
        
        const formData = new FormData(form);
        const proxyUrl = 'api_proxy.php'; 
        
        try {
            const response = await fetch(proxyUrl, { method: 'POST', body: formData });
            stopTimer();

            const responseText = await response.text(); 
            let responseData;
            try {
                responseData = JSON.parse(responseText); 
            } catch(e) {
                console.error("Failed to parse JSON:", responseText);
                throw new Error(`Respons tidak valid diterima. Status: ${response.status}. Respons mentah: ${responseText.substring(0, 200)}...`);
            }

            if (response.ok && responseData.success === true) {
                displayFormattedResults(responseData.data);
            } else {
                throw new Error(responseData.message || 'Respons dari server tidak berhasil atau tidak valid.');
            }
        } catch (error) {
            stopTimer();
            console.error('Error:', error);
            errorMessage.textContent = 'Terjadi kesalahan: ' + error.message;
            errorMessage.style.display = 'block';
        } finally {
            localLoadingContainer.style.display = 'none';
            generateBtn.disabled = false;
        }
    });
    
    function displayFormattedResults(data) {
        resultContainer.innerHTML = ''; 
        resultContainer.style.display = 'block';

        if (!data || !data.prompt || !data.analysis) {
            errorMessage.textContent = 'Format data dari API tidak lengkap.';
            errorMessage.style.display = 'block';
            return;
        }

        const promptSection = `
            <div>
                <h4><i class="fas fa-lightbulb text-primary me-2"></i>Prompt yang Dihasilkan</h4>
                <div id="generatedPromptOutput" class="result-prompt p-3">${data.prompt}</div>
                <div class="result-actions-container">
                    <button id="copyPromptBtn" class="btn btn-success">
                        <i class="fas fa-copy me-2"></i>Salin Prompt
                    </button>
                    <button id="generateNewBtn" class="btn btn-info">
                        <i class="fas fa-sync-alt me-2"></i>Generate Baru
                    </button>
                    <a href="https://app.andrias.web.id/tools/ai-image-generator/" target="_blank" id="generateImageLinkBtn" class="btn btn-warning">
                        <i class="fas fa-paint-brush me-2"></i>Generate Image
                    </a>
                </div>
                <div id="copyMessage" class="form-text text-success mt-1" style="display: none;">Prompt berhasil disalin!</div>
            </div>
        `;

        const analysis = data.analysis;
        const analysisSection = `
            <div class="mt-4">
                <h4><i class="fas fa-chart-pie text-primary me-2"></i>Analisis Gambar</h4>
                <div class="row g-3 mt-1">
                    <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-bullseye me-2"></i>Subjek</h5><p>${analysis.subject || 'N/A'}</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-palette me-2"></i>Warna</h5><p>${analysis.colors || 'N/A'}</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-paint-brush me-2"></i>Gaya</h5><p>${analysis.style || 'N/A'}</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-theater-masks me-2"></i>Suasana (Mood)</h5><p>${analysis.mood || 'N/A'}</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-compress-arrows-alt me-2"></i>Komposisi</h5><p>${analysis.composition || 'N/A'}</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-lightbulb me-2"></i>Pencahayaan</h5><p>${analysis.lighting || 'N/A'}</p></div>
                    </div>
                     <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-expand me-2"></i>Resolusi</h5><p>${analysis.resolution || 'N/A'}</p></div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="analysis-card"><h5><i class="fas fa-file-image me-2"></i>Format</h5><p>${analysis.format || 'N/A'}</p></div>
                    </div>
                </div>
            </div>
        `;

        let tagsHtml = '';
        if (analysis.tags && analysis.tags.length > 0) {
            tagsHtml = `<div class="mt-4">
                <h4><i class="fas fa-tags text-primary me-2"></i>Tags yang Disarankan</h4>
                <div class="analysis-card analysis-tags">
                    ${analysis.tags.map(tag => `<span class="badge bg-secondary m-1">${tag}</span>`).join(' ')}
                </div>
            </div>`;
        }
        
        resultContainer.innerHTML = promptSection + analysisSection + tagsHtml;
    }

    resultContainer.addEventListener('click', function(event){
        if(event.target.id === 'copyPromptBtn' || event.target.closest('#copyPromptBtn')){
            const textToCopy = document.getElementById('generatedPromptOutput').textContent;
            const copyMessageEl = document.getElementById('copyMessage');
            if (navigator.clipboard && window.isSecureContext) { 
                navigator.clipboard.writeText(textToCopy).then(() => {
                    copyMessageEl.textContent = 'Prompt berhasil disalin!';
                    copyMessageEl.style.display = 'block';
                    setTimeout(() => { copyMessageEl.style.display = 'none'; }, 2000);
                });
            } else {
                const textArea = document.createElement("textarea");
                textArea.value = textToCopy;
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    copyMessageEl.textContent = 'Prompt berhasil disalin!';
                } catch (err) {
                    copyMessageEl.textContent = 'Gagal menyalin.';
                }
                document.body.removeChild(textArea);
                copyMessageEl.style.display = 'block';
                setTimeout(() => { copyMessageEl.style.display = 'none'; }, 2000);
            }
        }
        
        if(event.target.id === 'generateNewBtn' || event.target.closest('#generateNewBtn')){
            window.location.reload();
        }
    });

});
</script>

<?php
if (file_exists($path_prefix . 'footer.php')) {
    include $path_prefix . 'footer.php';
} else {
    echo "<footer class='footer'><p>&copy; " . date("Y") . " My Web Tool. All rights reserved.</p></footer>";
    echo "</main></body></html>";
}
?>