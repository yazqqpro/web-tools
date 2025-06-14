<?php
$page_title = "Webtools Directory";
include 'header.php'; // Memuat header dengan desain baru

/**
 * Fungsi untuk mengambil data definisi tools dari tools.json.
 * @return array Data tools atau array kosong jika gagal.
 */
function get_tools_data() {
    $json_file = 'tools.json';
    if (file_exists($json_file)) {
        $json_data = file_get_contents($json_file);
        $tools = json_decode($json_data, true);
        return is_array($tools) ? $tools : [];
    }
    return [];
}

/**
 * Fungsi untuk mengambil data statistik penggunaan dari tool_usage_stats.json.
 * @return array Data statistik atau array kosong jika gagal.
 */
function get_usage_stats() {
    $stats_file = 'tool_usage_stats.json';
    if (file_exists($stats_file)) {
        $stats_data = file_get_contents($stats_file);
        $stats = json_decode($stats_data, true);
        return is_array($stats) ? $stats : [];
    }
    return [];
}

$tools = get_tools_data();
$usage_stats = get_usage_stats();

// PERUBAHAN: Gabungkan statistik ke dalam data tools untuk pengurutan
foreach ($tools as $index => $tool) {
    // Pastikan slug ada untuk menghindari error
    if (isset($tool['slug'])) {
        $tools[$index]['usage_count'] = $usage_stats[$tool['slug']] ?? 0;
    } else {
        $tools[$index]['usage_count'] = 0;
    }
}

// PERUBAHAN: Urutkan tools berdasarkan usage_count (paling populer di atas)
usort($tools, function($a, $b) {
    return $b['usage_count'] - $a['usage_count'];
});
?>
<!-- PERUBAHAN BARU: CSS untuk Tombol Aksi -->
<style>
.action-buttons-section {
    padding: 1.5rem 1rem;
    text-align: center;
    background-color: #f8f9fa; /* Warna latar yang soft, sesuaikan jika perlu */
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 22px;
    margin: 5px;
    font-size: 0.95rem;
    font-weight: 500;
    text-decoration: none;
    color: #fff;
    background-image: linear-gradient(45deg, #667eea, #764ba2); /* Gradient ungu-biru yang elegan */
    border: none;
    border-radius: 50px; /* Membuatnya seperti pil */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.btn-action:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.btn-action .material-icons {
    margin-right: 8px;
    font-size: 1.1rem;
}
</style>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">
            <i class="material-icons hero-icon">build_circle</i>
            Webtools Directory
        </h1>
        <p class="hero-subtitle">Koleksi alat bantu online modern untuk mempermudah alur kerja Anda</p>
    </div>
</div>

<!-- Search Section -->
<div class="search-section">
    <div class="search-container">
        <div class="search-input-wrapper">
            <i class="material-icons search-icon">search</i>
            <input type="text" id="tool-search-input" class="form-control search-input" placeholder="Cari tools yang Anda butuhkan..." autocomplete="off">
        </div>
    </div>
</div>

<!-- PERUBAHAN BARU: Action Buttons Section -->
<div class="action-buttons-section">
    <button type="button" class="btn btn-action" data-bs-toggle="modal" data-bs-target="#coffeeModal">
        <i class="material-icons">coffee</i>
        <span>Traktir Kopi Admin</span>
    </button>
    <a href="https://app.andrias.web.id/api/" target="_blank" class="btn btn-action">
        <i class="material-icons">key</i>
        <span>API Key AI</span>
    </a>
</div>

<!-- Tools Grid -->
<div class="tools-section">
    <div class="tools-grid" id="tools-grid">
    <?php if (!empty($tools)): ?>
        <?php foreach ($tools as $tool): ?>
            <?php
                $is_maintenance = isset($tool['status']) && $tool['status'] === 'maintenance';
                // Pastikan $base_url sudah didefinisikan (biasanya di header.php)
                global $base_url; 
                $tool_url = $is_maintenance ? '#' : ($base_url ?? '') . 'tools/' . htmlspecialchars($tool['slug'] ?? '') . '/';
                $link_class = $is_maintenance ? 'tool-card-link tool-maintenance' : 'tool-card-link';
                $tool_name_attr = strtolower(htmlspecialchars($tool['name'] ?? ''));
                $tool_desc_attr = strtolower(htmlspecialchars($tool['description'] ?? ''));

                // Ambil jumlah penggunaan yang sudah digabungkan
                $usage_count = $tool['usage_count'];
            ?>
            <a href="<?php echo $tool_url; ?>"
               class="<?php echo $link_class; ?>"
               data-tool-name="<?php echo $tool_name_attr; ?>"
               data-tool-description="<?php echo $tool_desc_attr; ?>">
                <div class="tool-card">
                    <div class="tool-icon-wrapper">
                        <i class="<?php echo htmlspecialchars($tool['icon'] ?? 'fas fa-question-circle'); ?>"></i>
                    </div>
                    <div class="tool-content">
                        <h3 class="tool-name"><?php echo htmlspecialchars($tool['name'] ?? 'Nama Tool'); ?></h3>
                        <p class="tool-description"><?php echo htmlspecialchars($tool['description'] ?? 'Deskripsi tidak tersedia.'); ?></p>
                        <!-- Menampilkan data penggunaan -->
                        <div class="tool-usage">
                            <i class="material-icons">trending_up</i>
                            <span><?php echo $usage_count; ?> penggunaan</span>
                        </div>
                    </div>
                    <?php if ($is_maintenance): ?>
                    <div class="maintenance-badge">
                        <i class="material-icons">build</i>
                        <span>Maintenance</span>
                    </div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <p class="text-center text-muted">Saat ini belum ada tools yang tersedia.</p>
        </div>
    <?php endif; ?>
    </div>
    <!-- No Results Message -->
    <div id="no-results" class="no-results" style="display: none;">
        <div class="no-results-content">
            <i class="material-icons no-results-icon">search_off</i>
            <h3>Tidak ada tools yang ditemukan</h3>
            <p>Coba gunakan kata kunci yang berbeda atau periksa ejaan Anda.</p>
        </div>
    </div>
</div>

<!-- PERUBAHAN BARU: Coffee/QRIS Modal -->
<div class="modal fade" id="coffeeModal" tabindex="-1" aria-labelledby="coffeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="coffeeModalLabel"><i class="material-icons me-2">volunteer_activism</i>Dukung Kami</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p>Pindai QRIS di bawah ini untuk mendukung operasional dan pengembangan webtools ini.</p>
        <!-- Ganti URL src dengan path ke gambar QRIS Anda yang sebenarnya -->
        <img src="/qris.jpeg" class="img-fluid rounded mb-3" alt="QRIS Donasi" style="max-width: 250px;">
        <p class="text-muted small">Dukungan Anda sangat berarti agar server tetap berjalan dan tools baru bisa terus dikembangkan. Terima kasih!</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
