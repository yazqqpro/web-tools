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

<style>
/* Enhanced Professional Styling */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    --card-shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.15);
    --border-radius: 16px;
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hero Section Enhancement */
.hero-section {
    background: var(--primary-gradient);
    color: white;
    padding: 4rem 0;
    margin: -2rem -15px 3rem -15px;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hero-subtitle {
    font-size: 1.4rem;
    opacity: 0.9;
    font-weight: 300;
    max-width: 600px;
    margin: 0 auto;
}

.hero-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

/* Search Section Enhancement */
.search-section {
    margin: -1rem 0 3rem 0;
    position: relative;
    z-index: 3;
}

.search-container {
    max-width: 800px;
    margin: auto;
}

.search-input-wrapper {
    position: relative;
    box-shadow: var(--card-shadow);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.search-input {
    border: none;
    padding: 1.5rem 1.5rem 1.5rem 4rem;
    font-size: 1.2rem;
    border-radius: var(--border-radius);
    background: white;
    transition: var(--transition);
}

.search-input:focus {
    outline: none;
    box-shadow: var(--card-shadow-hover), 0 0 0 4px rgba(102, 126, 234, 0.2);
    transform: translateY(-2px);
}

.search-icon {
    position: absolute;
    left: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.3rem;
    color: #667eea;
}

/* Action Buttons Enhancement */
.action-buttons-section {
    padding: 2rem 1rem;
    text-align: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: var(--border-radius);
    margin: 2rem 0;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 28px;
    margin: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    color: white;
    background: var(--secondary-gradient);
    border: none;
    border-radius: 50px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.btn-action::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-action:hover::before {
    left: 100%;
}

.btn-action:hover {
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-action .material-icons {
    margin-right: 8px;
    font-size: 1.2rem;
}

/* Tools Grid Enhancement */
.tools-section {
    margin-top: 2rem;
}

.section-header {
    text-align: center;
    margin-bottom: 3rem;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.2rem;
    color: var(--text-secondary);
    max-width: 600px;
    margin: 0 auto;
}

.tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.tool-card-link {
    text-decoration: none;
    display: block;
    height: 100%;
}

.tool-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.tool-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--primary-gradient);
    transform: scaleX(0);
    transition: var(--transition);
}

.tool-card:hover::before {
    transform: scaleX(1);
}

.tool-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--card-shadow-hover);
}

.tool-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.tool-icon-wrapper {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 16px;
    background: var(--primary-gradient);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.tool-content {
    flex: 1;
}

.tool-name {
    font-weight: 700;
    font-size: 1.3rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.tool-description {
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.tool-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.tool-usage {
    display: flex;
    align-items: center;
    gap: 6px;
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
}

.tool-usage i {
    font-size: 1rem;
    color: #667eea;
}

.tool-status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    background: var(--success-gradient);
    color: white;
    box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
}

.maintenance-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 4px;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
}

.tool-card-link.tool-maintenance .tool-card {
    opacity: 0.7;
    filter: grayscale(0.3);
}

.tool-card-link.tool-maintenance .tool-card:hover {
    transform: none;
    box-shadow: var(--card-shadow);
}

/* No Results Enhancement */
.no-results {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow);
    margin: 2rem 0;
}

.no-results-icon {
    font-size: 5rem;
    color: #e9ecef;
    margin-bottom: 1.5rem;
}

.no-results h3 {
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.no-results p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

/* Stats Enhancement */
.stats-section {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    margin: 3rem 0;
    box-shadow: var(--card-shadow);
    text-align: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.stat-item {
    padding: 1.5rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: #667eea;
    display: block;
}

.stat-label {
    font-size: 1rem;
    color: var(--text-secondary);
    font-weight: 500;
    margin-top: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .tools-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .tool-card {
        padding: 1.5rem;
    }
    
    .search-input {
        padding: 1.2rem 1.2rem 1.2rem 3.5rem;
        font-size: 1.1rem;
    }
    
    .btn-action {
        padding: 10px 20px;
        font-size: 0.9rem;
        margin: 6px;
    }
}

/* Animation Enhancements */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tool-card {
    animation: fadeInUp 0.6s ease-out;
}

.tool-card:nth-child(1) { animation-delay: 0.1s; }
.tool-card:nth-child(2) { animation-delay: 0.2s; }
.tool-card:nth-child(3) { animation-delay: 0.3s; }
.tool-card:nth-child(4) { animation-delay: 0.4s; }
.tool-card:nth-child(5) { animation-delay: 0.5s; }
.tool-card:nth-child(6) { animation-delay: 0.6s; }
</style>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="text-center">
                <i class="material-icons hero-icon">build_circle</i>
                <h1 class="hero-title">Webtools Directory</h1>
                <p class="hero-subtitle">Koleksi alat bantu online modern untuk mempermudah alur kerja Anda dengan teknologi AI terdepan</p>
            </div>
        </div>
    </div>
</div>

<!-- Search Section -->
<div class="search-section">
    <div class="container">
        <div class="search-container">
            <div class="search-input-wrapper">
                <i class="material-icons search-icon">search</i>
                <input type="text" id="tool-search-input" class="form-control search-input" placeholder="Cari tools yang Anda butuhkan..." autocomplete="off">
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons Section -->
<div class="container">
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
</div>

<!-- Stats Section -->
<div class="container">
    <div class="stats-section">
        <h2 class="section-title">Platform Statistics</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($tools); ?></span>
                <div class="stat-label">Total Tools</div>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count(array_filter($tools, function($tool) { return isset($tool['status']) && $tool['status'] === 'active'; })); ?></span>
                <div class="stat-label">Active Tools</div>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo array_sum($usage_stats); ?></span>
                <div class="stat-label">Total Usage</div>
            </div>
        </div>
    </div>
</div>

<!-- Tools Section -->
<div class="container">
    <div class="tools-section">
        <div class="section-header">
            <h2 class="section-title">Explore Our Tools</h2>
            <p class="section-subtitle">Temukan berbagai alat bantu yang dirancang untuk meningkatkan produktivitas dan kreativitas Anda</p>
        </div>
        
        <div class="tools-grid" id="tools-grid">
        <?php if (!empty($tools)): ?>
            <?php foreach ($tools as $tool): ?>
                <?php
                    $is_maintenance = isset($tool['status']) && $tool['status'] === 'maintenance';
                    global $base_url; 
                    $tool_url = $is_maintenance ? '#' : ($base_url ?? '') . 'tools/' . htmlspecialchars($tool['slug'] ?? '') . '/';
                    $link_class = $is_maintenance ? 'tool-card-link tool-maintenance' : 'tool-card-link';
                    $tool_name_attr = strtolower(htmlspecialchars($tool['name'] ?? ''));
                    $tool_desc_attr = strtolower(htmlspecialchars($tool['description'] ?? ''));
                    $usage_count = $tool['usage_count'];
                ?>
                <a href="<?php echo $tool_url; ?>"
                   class="<?php echo $link_class; ?>"
                   data-tool-name="<?php echo $tool_name_attr; ?>"
                   data-tool-description="<?php echo $tool_desc_attr; ?>">
                    <div class="tool-card">
                        <div class="tool-header">
                            <div class="tool-icon-wrapper">
                                <i class="<?php echo htmlspecialchars($tool['icon'] ?? 'fas fa-question-circle'); ?>"></i>
                            </div>
                            <div class="tool-content">
                                <h3 class="tool-name"><?php echo htmlspecialchars($tool['name'] ?? 'Nama Tool'); ?></h3>
                                <p class="tool-description"><?php echo htmlspecialchars($tool['description'] ?? 'Deskripsi tidak tersedia.'); ?></p>
                            </div>
                        </div>
                        
                        <div class="tool-footer">
                            <div class="tool-usage">
                                <i class="material-icons">trending_up</i>
                                <span><?php echo $usage_count; ?> penggunaan</span>
                            </div>
                            <?php if (!$is_maintenance): ?>
                                <div class="tool-status-badge">Active</div>
                            <?php endif; ?>
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
                <div class="no-results">
                    <i class="material-icons no-results-icon">build_circle</i>
                    <h3>Belum Ada Tools</h3>
                    <p>Saat ini belum ada tools yang tersedia. Silakan kembali lagi nanti.</p>
                </div>
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
</div>

<!-- Coffee/QRIS Modal -->
<div class="modal fade" id="coffeeModal" tabindex="-1" aria-labelledby="coffeeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
      <div class="modal-header" style="border-bottom: 1px solid rgba(0,0,0,0.05);">
        <h5 class="modal-title" id="coffeeModalLabel">
            <i class="material-icons me-2" style="color: #667eea;">volunteer_activism</i>
            Dukung Kami
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center" style="padding: 2rem;">
        <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Pindai QRIS di bawah ini untuk mendukung operasional dan pengembangan webtools ini.</p>
        <img src="/qris.jpeg" class="img-fluid rounded" alt="QRIS Donasi" style="max-width: 250px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); border-radius: 12px;">
        <p class="text-muted small" style="margin-top: 1.5rem;">Dukungan Anda sangat berarti agar server tetap berjalan dan tools baru bisa terus dikembangkan. Terima kasih!</p>
      </div>
      <div class="modal-footer" style="border-top: 1px solid rgba(0,0,0,0.05);">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>