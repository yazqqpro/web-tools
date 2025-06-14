<?php
$page_title = "AI Image Generator";
$path_prefix = '../../'; 
include $path_prefix . 'header.php';
?>
<style>
    /* Styling tidak berubah */
    .history-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; min-height: 200px; }
    .history-card { border-radius: 0.5rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer; transition: all 0.2s ease; text-decoration: none; color: inherit; display: block; }
    .history-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    .history-image-wrapper { width: 100%; padding-top: 133.33%; position: relative; background-color: #f0f0f0; }
    .history-image-wrapper img { position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; }
    .history-info { padding: 0.75rem; background-color: #fff; }
    .history-info p { margin: 0; font-size: 0.8rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .history-info .ip-info { font-weight: 500; color: #495057; }
    .modal-image { max-width: 100%; height: auto; border-radius: 0.5rem; }
    .modal-prompt { margin-top: 1rem; background-color: #f8f9fa; padding: 1rem; border-radius: 0.25rem; font-family: monospace; word-wrap: break-word; max-height: 200px; overflow-y: auto; }
    .modal-history-info { border-top: 1px solid #dee2e6; padding-top: 1rem; margin-top: 1rem; }
    .prompt-wrapper { position: relative; }
    .random-prompt-icon { position: absolute; top: 45px; right: 15px; cursor: pointer; color: #6c757d; font-size: 1.2rem; transition: all 0.2s ease; z-index: 10; }
    .random-prompt-icon:hover { color: #0d6efd; transform: scale(1.1); }
    .result-actions { display: flex; gap: 0.5rem; margin-top: 1rem; justify-content: center; flex-wrap: wrap; }
    .history-loader {
        display: none; /* Sembunyikan loader secara default */
        text-align: center;
        padding: 2rem;
    }
</style>

<div class='tool-page-container'>
    <div class="text-center mb-4">
        <h1><i class="fas fa-paint-brush me-2"></i>AI Image Generator</h1>
        <p class="lead text-muted">Buat gambar unik dari teks deskriptif Anda.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="mb-3 prompt-wrapper">
                <label for="promptInput" class="form-label fs-5">Masukkan Prompt:</label>
                <textarea id="promptInput" class="form-control form-control-lg" rows="4" placeholder="Contoh: a majestic lion king on a dark throne, cinematic lighting"></textarea>
                <i id="randomPromptBtn" class="fas fa-random random-prompt-icon" title="Generate Prompt Acak" data-bs-toggle="tooltip"></i>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="sizeSelect" class="form-label">Ukuran Gambar:</label>
                    <select id="sizeSelect" class="form-select">
                        <option value="1024x1024" selected>Persegi (1024x1024)</option>
                        <option value="512x512">Persegi Kecil (512x512)</option>
                        <option value="720x1280">Potret (720x1280)</option>
                        <option value="1280x720">Lanskap (1280x720)</option>
                        <option value="1792x1024">Lanskap Lebar (1792x1024)</option>
                        <option value="1024x1792">Potret Tinggi (1024x1792)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="modelSelect" class="form-label">Model AI:</label>
                    <select id="modelSelect" class="form-select">
                        <option value="flux" selected>Flux (Default, Cepat & Bagus)</option>
                        <option value="turbo">Turbo (Mendukung NSFW)</option>
                        <option value="dalle3">DALL-E 3</option>
                        <option value="stability">Stability AI</option>
                    </select>
                </div>
            </div>
            <div class="form-check form-switch my-3">
                <input class="form-check-input" type="checkbox" role="switch" id="safeFilterSwitch" checked>
                <label class="form-check-label" for="safeFilterSwitch">Filter Aman (Safe)</label>
            </div>
            
            <div class="d-grid gap-2">
                <button id="generateImageBtn" class="btn btn-primary btn-lg">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                    <span id="btnText"><i class="fas fa-image me-2"></i>Generate Gambar</span>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#coffeeModal">
                    <i class="fas fa-coffee me-1"></i> Dukung Pengembang
                </button>
            </div>
        </div>
    </div>
    
    <div id="resultContainer" class="text-center mt-4" style="display:none;">
        <img id="generatedImage" class="img-fluid rounded shadow" style="max-width: 720px; width: 100%; height: auto;" src="#" alt="Generated Image">
        <div class="result-actions">
            <a id="downloadLink" href="#" class="btn btn-success" download="generated-image.webp"><i class="fas fa-download me-2"></i>Unduh Gambar</a>
        </div>
    </div>
    <div id="errorMessage" class="alert alert-danger mt-4" style="display: none;"></div>

    <div class="mt-5">
        <h3 class="text-center mb-4">Hasil Generate Terakhir</h3>
        <div id="historyGrid" class="history-grid"></div>
        <div class="history-loader" id="historyLoader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div id="paginationContainer" class="d-flex justify-content-center mt-4"></div>
        <p id="noHistoryMessage" class="text-center text-muted" style="display: none;">Belum ada riwayat gambar yang tersimpan.</p>
    </div>
</div>

<!-- Modal Detail Gambar -->
<div class="modal fade" id="imageDetailModal" tabindex="-1" aria-labelledby="imageDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageDetailModalLabel">Detail Gambar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalFullImage" src="#" class="modal-image" alt="Full Size Image">
                <div id="modalPrompt" class="modal-prompt text-start"></div>
                <div id="modalHistoryInfo" class="modal-history-info text-start"></div>
            </div>
            <div class="modal-footer">
                <a id="downloadModalImageBtn" href="#" class="btn btn-success" download="generated-image.webp"><i class="fas fa-download me-2"></i>Unduh Gambar</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Donasi QRIS -->
<div class="modal fade" id="coffeeModal" tabindex="-1" aria-labelledby="coffeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="coffeeModalLabel"><i class="fas fa-qrcode me-2"></i>Dukung Kami via QRIS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Suka dengan alat ini? Anda bisa mendukung pengembangan lebih lanjut dengan donasi melalui QRIS.</p>
                <img src="/qris.jpeg" alt="Donasi QRIS" class="img-fluid rounded" style="max-width: 300px;">
                <p class="mt-3">Setiap dukungan sangat berarti. Terima kasih!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---- ELEMEN DOM ----
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

    const randomPrompts = [
        "a majestic lion king on a dark throne, cinematic lighting",
        "futuristic cyberpunk city at night, with flying cars and neon signs, rain pouring down"
    ];

    // ---- FUNGSI ----

    /**
     * Mengambil dan merender riwayat gambar untuk halaman tertentu.
     * @param {number} page Nomor halaman yang akan diambil.
     */
    async function renderHistoryPage(page) {
        historyLoader.style.display = 'block';
        historyGrid.innerHTML = '';
        paginationContainer.innerHTML = '';
        noHistoryMessage.style.display = 'none';

        try {
            const response = await fetch(`history_loader.php?page=${page}`);
            if (!response.ok) {
                throw new Error('Gagal memuat riwayat.');
            }
            const data = await response.json();

            if (!data.items || data.items.length === 0) {
                noHistoryMessage.style.display = 'block';
                return;
            }

            data.items.forEach(item => {
                const ipAddress = item.ip || 'unknown';
                const timestamp = new Date((item.timestamp || 0) * 1000);
                const timestampFormatted = timestamp.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                const card = `
                    <a href="#" class="history-card" 
                       data-bs-toggle="modal" 
                       data-bs-target="#imageDetailModal" 
                       data-full-image="${escapeHTML(item.imagekit_url)}"
                       data-prompt="${escapeHTML(item.prompt)}"
                       data-ip-address="${escapeHTML(ipAddress)}"
                       data-timestamp="${escapeHTML(timestampFormatted)}">
                        <div class="history-image-wrapper">
                            <img src="${escapeHTML(item.imagekit_url)}" alt="Generated Image from History" loading="lazy">
                        </div>
                        <div class="history-info">
                            <p class="ip-info"><i class="fas fa-user-secret me-1"></i> ${escapeHTML(ipAddress)}</p>
                            <p><i class="fas fa-clock me-1"></i> ${escapeHTML(timestampFormatted)}</p>
                        </div>
                    </a>`;
                historyGrid.innerHTML += card;
            });

            renderPagination(data.totalPages, page);

        } catch (error) {
            console.error('Error fetching history:', error);
            noHistoryMessage.textContent = 'Gagal memuat riwayat. Coba lagi nanti.';
            noHistoryMessage.style.display = 'block';
        } finally {
            historyLoader.style.display = 'none';
        }
    }
    
    /**
     * Merender kontrol paginasi.
     * @param {number} totalPages Jumlah total halaman.
     * @param {number} currentPage Halaman saat ini.
     */
function renderPagination(totalPages, currentPage) {
    if (totalPages <= 1) return;
    let paginationHTML = '<nav><ul class="pagination">';

    paginationHTML += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;

    let startPage = Math.max(currentPage - 2, 1);
    let endPage = Math.min(currentPage + 2, totalPages);

    if (startPage > 1) {
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
        if (startPage > 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
    }

    paginationHTML += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
    paginationHTML += '</ul></nav>';

    paginationContainer.innerHTML = paginationHTML;
}

    function escapeHTML(str) {
        if (!str) return '';
        const p = document.createElement('p');
        p.textContent = str;
        return p.innerHTML;
    }

    // ---- EVENT LISTENERS ----

    if (randomPromptBtn) {
        randomPromptBtn.addEventListener('click', function() {
            const randomIndex = Math.floor(Math.random() * randomPrompts.length);
            promptInput.value = randomPrompts[randomIndex];
        });
    }

    generateBtn.addEventListener('click', async function() {
        // Logika generate tetap sama
        const prompt = promptInput.value.trim();
        if (!prompt) {
            errorMessage.textContent = 'Prompt tidak boleh kosong.';
            errorMessage.style.display = 'block';
            return;
        }
        errorMessage.style.display = 'none';
        resultContainer.style.display = 'none';
        downloadLink.style.display = 'none';
        generateBtn.disabled = true;
        spinner.style.display = 'inline-block';
        btnText.textContent = 'Memproses ...';
        
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
                throw new Error(data.message || 'Gagal menghasilkan gambar.');
            }
            generatedImage.src = data.imageData; 
            downloadLink.href = data.imageData; 
            resultContainer.style.display = 'block';
            downloadLink.style.display = 'inline-block';
            
            // Muat ulang riwayat dari halaman pertama untuk menampilkan item baru
            renderHistoryPage(1);

        } catch (error) {
            console.error('Error:', error);
            let userMessage = 'Terjadi kesalahan: ' + error.message;
            if (error.message.includes('API eksternal') || error.message.includes('layanan penyimpanan')) {
                userMessage += '<br><br><strong>Saran:</strong> Coba Generate lagi atau ganti <strong>Model AI</strong> (misalnya ke Flux). Layanan eksternal mungkin sedang sibuk.';
            }
            errorMessage.innerHTML = userMessage;
            errorMessage.style.display = 'block';
        } finally {
            generateBtn.disabled = false;
            spinner.style.display = 'none';
            btnText.innerHTML = '<i class="fas fa-image me-2"></i>Generate Gambar';
        }
    });

    paginationContainer.addEventListener('click', function(e) {
        e.preventDefault();
        const target = e.target;
        if (target.tagName === 'A' && target.hasAttribute('data-page')) {
            const page = parseInt(target.getAttribute('data-page'));
            const currentPageLi = paginationContainer.querySelector('.page-item.active a');
            const currentPage = currentPageLi ? parseInt(currentPageLi.dataset.page) : 1;
            
            if (page > 0 && page !== currentPage) {
                 renderHistoryPage(page);
            }
        }
    });
    
    const imageDetailModal = document.getElementById('imageDetailModal');
    if (imageDetailModal) {
        imageDetailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            modalFullImage.src = button.getAttribute('data-full-image');
            modalPrompt.textContent = button.getAttribute('data-prompt');
            downloadModalImageBtn.href = button.getAttribute('data-full-image');
            modalHistoryInfo.innerHTML = `
                <p class="mb-0"><i class="fas fa-user-secret me-2 text-muted"></i> ${button.getAttribute('data-ip-address')}</p>
                <p class="mb-0"><i class="fas fa-clock me-2 text-muted"></i> ${button.getAttribute('data-timestamp')}</p>
            `;
        });
    }

    // ---- INISIALISASI ----
    renderHistoryPage(1); // Muat halaman pertama riwayat saat halaman dibuka.
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php 
if (file_exists($path_prefix . 'footer.php')) {
    include $path_prefix . 'footer.php';
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script></body></html>";
}
?>
