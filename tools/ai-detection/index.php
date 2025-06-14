<?php
// Anda bisa menyertakan file header standar Anda di sini jika ada
$page_title = "Sightengine Content Moderation";
$path_prefix = '../../'; // Sesuaikan jika perlu
if (file_exists($path_prefix . 'header.php')) {
    include $path_prefix . 'header.php';
} else {
    // Fallback header jika tidak ada
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>" . htmlspecialchars($page_title) . "</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css' rel='stylesheet'>";
    echo "<style> body { padding-top: 20px; padding-bottom: 20px; background-color: #f8f9fa; } .footer { padding: 1rem 0; margin-top: 2rem; border-top: 1px solid #dee2e6; text-align: center; } </style>";
    echo "</head><body><main class='container'>";
}
?>

<style>
    .result-card {
        margin-top: 1.5rem;
    }
    .preview-container {
        width: 100%;
        max-width: 350px;
        min-height: 200px;
        border: 2px dashed #adb5bd;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        background-color: #fff;
        position: relative;
    }
    .preview-container img {
        max-width: 100%;
        max-height: 350px;
        object-fit: contain;
    }
    .preview-text {
        color: #6c757d;
        text-align: center;
    }
    .progress-bar-label {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .badge-score {
        font-size: 0.9em;
    }
    .json-result {
        background-color: #212529;
        color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
        max-height: 400px;
        overflow-y: auto;
        white-space: pre-wrap;
        font-family: monospace;
        font-size: 0.85rem;
    }
    /* PERUBAHAN: CSS untuk animasi progress bar */
    @keyframes progress-bar-stripes-animation {
        from { background-position-x: 1rem; }
        to { background-position-x: 0; }
    }
    .progress-bar-animated {
        animation: progress-bar-stripes-animation 1s linear infinite;
    }
    .progress-bar {
        transition: width 0.6s ease;
    }
    .analysis-summary-card {
        text-align: center;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        color: white;
    }
    .analysis-summary-card h4 {
        margin: 0;
        font-size: 1.5rem;
    }
    .analysis-form {
        display: none; 
    }
</style>

<div class="tool-page-container">
    <div class="text-center mb-4">
        <h1><i class="fas fa-shield-alt me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
        <p class="lead text-muted">Analisis dan moderasi konten gambar & teks menggunakan AI.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="mb-4">
                <label for="analysisType" class="form-label fs-5">Pilih Tipe Analisis:</label>
                <select id="analysisType" class="form-select form-select-lg">
                    <option value="image_api" selected>Moderasi Gambar (Image APIs)</option>
                    <option value="text_moderation">Moderasi Teks (Text Moderation)</option>
                    <option value="ai_detection">Deteksi Gambar AI (AI Image Detection)</option>
                </select>
            </div>

            <form id="imageApiForm" class="analysis-form">
                <div class="mb-3">
                    <label for="imageUpload" class="form-label">Unggah Gambar:</label>
                    <div class="preview-container">
                        <img id="imagePreview" src="#" alt="Pratinjau Gambar" style="display:none;">
                        <p class="preview-text">Pratinjau Gambar Akan Tampil di Sini</p>
                    </div>
                    <input type="file" class="form-control" id="imageUpload" name="image" accept="image/png, image/jpeg, image/webp" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Model Deteksi (pilih satu atau lebih):</label>
                    <div class="row">
                        <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="models[]" value="nudity" id="modelNudity" checked><label class="form-check-label" for="modelNudity">Nudity</label></div></div>
                        <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="models[]" value="wad" id="modelWad" checked><label class="form-check-label" for="modelWad">Weapon, Alcohol, Drugs</label></div></div>
                        <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="models[]" value="offensive" id="modelOffensive" checked><label class="form-check-label" for="modelOffensive">Offensive Content</label></div></div>
                        <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="models[]" value="scam" id="modelScam" checked><label class="form-check-label" for="modelScam">Scam / Penipuan</label></div></div>
                        <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="models[]" value="faces" id="modelFace" checked><label class="form-check-label" for="modelFace">Face Detection</label></div></div>
                        <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="models[]" value="properties" id="modelProperties" checked><label class="form-check-label" for="modelProperties">Image Properties</label></div></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2">Analisis Gambar</button>
            </form>

            <form id="textModerationForm" class="analysis-form">
                <div class="mb-3">
                    <label for="textInput" class="form-label">Masukkan Teks:</label>
                    <textarea id="textInput" name="text" class="form-control" rows="6" placeholder="Ketik atau tempel teks yang ingin Anda analisis di sini..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2">Analisis Teks</button>
            </form>

            <form id="aiDetectionForm" class="analysis-form">
                <div class="mb-3">
                    <label for="aiImageUpload" class="form-label">Unggah Gambar untuk Deteksi AI:</label>
                    <div class="preview-container">
                        <img id="aiImagePreview" src="#" alt="Pratinjau Gambar" style="display:none;">
                        <p class="preview-text">Pratinjau Gambar Akan Tampil di Sini</p>
                    </div>
                    <input type="file" class="form-control" id="aiImageUpload" name="image" accept="image/png, image/jpeg, image/webp" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2">Deteksi Gambar AI</button>
            </form>

        </div>
    </div>

    <div id="loadingIndicator" class="text-center mt-4" style="display: none;">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Menganalisis konten, mohon tunggu...</p>
    </div>

    <div id="resultContainer" class="result-card" style="display:none;"></div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const analysisTypeSelect = document.getElementById('analysisType');
    const forms = document.querySelectorAll('.analysis-form');
    
    const imageApiForm = document.getElementById('imageApiForm');
    const textModerationForm = document.getElementById('textModerationForm');
    const aiDetectionForm = document.getElementById('aiDetectionForm');

    const imageUpload = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    const aiImageUpload = document.getElementById('aiImageUpload');
    const aiImagePreview = document.getElementById('aiImagePreview');
    
    const loadingIndicator = document.getElementById('loadingIndicator');
    const resultContainer = document.getElementById('resultContainer');

    function updateFormVisibility() {
        const formIdMap = {
            'image_api': 'imageApiForm',
            'text_moderation': 'textModerationForm',
            'ai_detection': 'aiDetectionForm'
        };
        const selectedFormId = formIdMap[analysisTypeSelect.value];
        forms.forEach(form => {
            form.style.display = (form.id === selectedFormId) ? 'block' : 'none';
        });
        resultContainer.style.display = 'none';
    }

    analysisTypeSelect.addEventListener('change', updateFormVisibility);

    function displayPreview(inputElement, previewElement) {
        const file = inputElement.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewElement.src = e.target.result;
                previewElement.style.display = 'block';
                if (previewElement.nextElementSibling && previewElement.nextElementSibling.tagName === 'P') {
                    previewElement.nextElementSibling.style.display = 'none';
                }
            }
            reader.readAsDataURL(file);
        }
    }
    imageUpload.addEventListener('change', () => displayPreview(imageUpload, imagePreview));
    aiImageUpload.addEventListener('change', () => displayPreview(aiImageUpload, aiImagePreview));

    async function handleFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        
        loadingIndicator.style.display = 'block';
        resultContainer.innerHTML = '';
        resultContainer.style.display = 'none';

        const formData = new FormData(form);
        const analysisType = analysisTypeSelect.value;
        formData.append('analysis_type', analysisType);

        try {
            const response = await fetch('sightengine_proxy.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error?.message || `Error: ${response.status}`);
            }

            displayResults(data, analysisType);

        } catch (error) {
            console.error('Error:', error);
            resultContainer.innerHTML = `<div class="card shadow-sm"><div class="card-body"><div class="alert alert-danger mb-0"><strong>Terjadi Kesalahan:</strong> ${error.message}</div></div></div>`;
        } finally {
            loadingIndicator.style.display = 'none';
            resultContainer.style.display = 'block';
        }
    }

    imageApiForm.addEventListener('submit', handleFormSubmit);
    textModerationForm.addEventListener('submit', handleFormSubmit);
    aiDetectionForm.addEventListener('submit', handleFormSubmit);

    // PERUBAHAN UTAMA: Fungsi displayResults dimodifikasi
    function displayResults(data, type) {
        resultContainer.innerHTML = ''; 
        
        const card = document.createElement('div');
        card.className = 'card shadow-sm';
        
        const cardHeader = document.createElement('div');
        cardHeader.className = 'card-header';
        let statusBadgeClass = data.status === 'success' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis';
        cardHeader.innerHTML = `<h5><i class="fas fa-poll me-2"></i>Hasil Analisis <span class="badge rounded-pill ${statusBadgeClass}">${data.status}</span></h5>`;
        
        const cardBody = document.createElement('div');
        cardBody.className = 'card-body';

        if (data.status === 'success') {
            if (type === 'image_api') {
                cardBody.innerHTML = buildImageApiResultHtml(data);
            } else if (type === 'text_moderation') {
                cardBody.innerHTML = buildTextModerationResultHtml(data);
            } else if (type === 'ai_detection') {
                cardBody.innerHTML = buildAiDetectionResultHtml(data);
            }
        } else {
            cardBody.innerHTML = `<div class="alert alert-danger mb-0"><strong>Pesan Error:</strong> ${data.error ? data.error.message : 'Tidak ada detail'}</div>`;
        }
        
        // PERUBAHAN: Menghapus bagian footer yang menampilkan JSON mentah
        // const cardFooter = document.createElement('div');
        // ... (kode footer lama dihapus)

        card.appendChild(cardHeader);
        card.appendChild(cardBody);
        // card.appendChild(cardFooter); // Footer tidak lagi ditambahkan
        resultContainer.appendChild(card);

        // Panggil animasi setelah card dan isinya ditambahkan ke DOM
        // Ini penting agar transisi CSS bisa berjalan
        animateProgressBars();
    }
    
    function createProgressBar(label, value) {
        const percentage = (value * 100);
        let barClass = 'bg-success';
        if (percentage > 75) barClass = 'bg-danger';
        else if (percentage > 50) barClass = 'bg-warning';
        return `
            <div class="mb-3">
                <div class="progress-bar-label d-flex justify-content-between">
                    <span>${label}</span>
                    <span class="badge rounded-pill text-bg-light border">${percentage.toFixed(1)}%</span>
                </div>
                <div class="progress" role="progressbar" aria-label="${label}" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100" style="height: 10px;">
                    <div class="progress-bar ${barClass}" style="width: 0%; transition: width 1s ease-out;"></div>
                </div>
            </div>
        `;
    }

    // Fungsi untuk menganimasikan progress bar setelah elemen ditambahkan ke DOM
    function animateProgressBars() {
        const progressBars = resultContainer.querySelectorAll('.progress-bar');
        setTimeout(() => {
            progressBars.forEach(bar => {
                const finalWidth = bar.closest('.progress').getAttribute('aria-valuenow') + '%';
                bar.style.width = finalWidth;
            });
        }, 100); // Delay kecil untuk memastikan elemen sudah di-render
    }

    function buildImageApiResultHtml(data) {
        let html = '<div class="row">';
        if (data.nudity) {
            html += '<div class="col-lg-6">';
            html += '<h6><i class="fas fa-eye-slash me-2"></i>Deteksi Nudity</h6>';
            html += createProgressBar('Raw (Mentah)', data.nudity.raw);
            html += createProgressBar('Partial (Sebagian)', data.nudity.partial);
            html += createProgressBar('Safe (Aman)', data.nudity.safe);
            html += '</div>';
        }
        if (data.weapon) {
            html += '<div class="col-lg-6">';
            html += `<h6><i class="fas fa-bomb me-2"></i>Senjata</h6>`;
             html += createProgressBar('Probabilitas Senjata', data.weapon);
            html += '</div>';
        }
        if (data.alcohol) {
            html += '<div class="col-lg-6">';
            html += `<h6><i class="fas fa-wine-bottle me-2"></i>Alkohol</h6>`;
            html += createProgressBar('Probabilitas Alkohol', data.alcohol);
            html += '</div>';
        }
        if (data.drugs) {
            html += '<div class="col-lg-6">';
            html += `<h6><i class="fas fa-pills me-2"></i>Obat Terlarang</h6>`;
             html += createProgressBar('Probabilitas Obat', data.drugs);
            html += '</div>';
        }
         if (data.offensive) {
            html += '<div class="col-lg-6">';
            html += `<h6><i class="fas fa-angry me-2"></i>Konten Ofensif</h6>`;
             html += createProgressBar('Probabilitas Ofensif', data.offensive.prob);
            html += '</div>';
        }
        if (data.faces) {
            html += `<div class="col-lg-6"><h6><i class="fas fa-users me-2"></i>Wajah Terdeteksi: ${data.faces.length}</h6></div>`;
        }
         if (data.scam) {
            html += '<div class="col-lg-6">';
            html += `<h6><i class="fas fa-money-bill-wave me-2"></i>Scam/Penipuan</h6>`;
             html += createProgressBar('Probabilitas Scam', data.scam.prob);
            html += '</div>';
        }
        if(data.properties) {
            html += `<div class="col-lg-6"><h6><i class="fas fa-image me-2"></i>Properti Gambar</h6><p class="mb-0">Tipe: ${data.properties.type}<br>Warna Dominan: <span style="display:inline-block; width: 12px; height: 12px; background-color:${data.properties.dominant_color.hex}; border: 1px solid #ccc;"></span> ${data.properties.dominant_color.hex}</p></div>`;
        }
        html += '</div>';
        return html;
    }

    function buildTextModerationResultHtml(data) {
        let html = '<h6><i class="fas fa-file-alt me-2"></i>Hasil Moderasi Teks</h6>';
        if(data.profanity && data.profanity.matches && data.profanity.matches.length > 0) {
            html += `<p class="mb-2"><strong><i class="fas fa-exclamation-triangle text-danger me-2"></i>Kata Kasar Terdeteksi (${data.profanity.matches.length}):</strong></p><ul class="list-group">`;
            data.profanity.matches.forEach(match => {
                html += `<li class="list-group-item">"${match.match}" <span class="text-muted">(di posisi ${match.index})</span></li>`;
            });
            html += '</ul>';
        } else {
            html += '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Tidak ada kata kasar yang terdeteksi.</div>';
        }
        return html;
    }

    // PERBAIKAN: Membaca path JSON yang benar
    function buildAiDetectionResultHtml(data) {
        let html = '<h6><i class="fas fa-robot me-2"></i>Hasil Deteksi Gambar AI</h6>';
        // Pengecekan baru: data.type ada, dan data.type.ai_generated ada (dan bukan null)
        if(data.type && data.type.ai_generated !== undefined && data.type.ai_generated !== null) {
            const prob = data.type.ai_generated; // Mengambil nilai probabilitas
            let summaryText = 'Tidak dapat ditentukan.';
            let summaryClass = 'text-muted';
            if (prob > 0.8) {
                summaryText = 'Sangat Mungkin Dibuat oleh AI';
                summaryClass = 'text-danger fw-bold';
            } else if (prob > 0.5) {
                summaryText = 'Kemungkinan Dibuat oleh AI';
                summaryClass = 'text-warning fw-bold';
            } else {
                 summaryText = 'Kemungkinan Besar Gambar Asli';
                 summaryClass = 'text-success fw-bold';
            }
            
            html += `<div class="analysis-summary-card ${summaryClass.includes('danger') ? 'bg-danger' : (summaryClass.includes('warning') ? 'bg-warning text-dark' : 'bg-success')}">
                        <p class="mb-1">Kesimpulan:</p>
                        <h4>${summaryText}</h4>
                     </div>`;
            html += createProgressBar('Probabilitas Dibuat oleh AI', prob);
        } else {
             html += '<p class="text-muted">Tidak ada data deteksi AI yang diterima.</p>';
        }
        return html;
    }
    
    // Inisialisasi tampilan form saat halaman dimuat
    updateFormVisibility();
});
</script>

<?php
if (file_exists($path_prefix . 'footer.php')) {
    include $path_prefix . 'footer.php';
} else {
    echo "</main><footer class='footer'><p>&copy; " . date("Y") . " My Web Tool. All rights reserved.</p></footer></body></html>";
}
?>