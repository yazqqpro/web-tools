<?php
session_start(); 

// --- KONFIGURASI DASAR ---
$page_title = "Admin Dashboard";
$path_prefix = '../'; // Path dari folder admin ke root
include 'auth.php'; // Memastikan admin sudah login
if (file_exists($path_prefix . 'header.php')) {
    include $path_prefix . 'header.php';
} else {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Admin Error</title></head><body><h1>Error: File header.php utama tidak ditemukan.</h1></body></html>";
    exit;
}

$tools_file = $path_prefix . 'tools.json';
$feedback_file_path = $path_prefix . 'feedback.json'; 
$tool_usage_stats_file = $path_prefix . 'tool_usage_stats.json';

$current_admin_page = $_GET['page'] ?? 'tools'; 

// --- FUNGSI-FUNGSI DARI VERSI SEBELUMNYA ---
function get_tools_data_admin() {
    global $tools_file;
    if (!file_exists($tools_file)) return [];
    $json_data = file_get_contents($tools_file);
    $decoded_data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_data)) return [];
    return $decoded_data;
}
function get_all_feedback($file_path) {
    if (!file_exists($file_path)) return [];
    $json_data = file_get_contents($file_path);
    if ($json_data === false) return [];
    $feedback_array = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($feedback_array)) return [];
    usort($feedback_array, function($a, $b) {
        return strtotime($b['timestamp'] ?? 0) - strtotime($a['timestamp'] ?? 0);
    });
    return $feedback_array;
}

$tools = get_tools_data_admin();
$total_tools = count($tools);
$active_tools_count = 0;
$maintenance_tools_count = 0;
if(is_array($tools)){
    foreach ($tools as $tool) {
        if (isset($tool['status']) && $tool['status'] === 'active') {
            $active_tools_count++;
        } else {
            $maintenance_tools_count++;
        }
    }
}
$editing_tool = null;
$form_title = 'Tambah Tool Baru';
$form_action = 'add_tool';
$submit_button_text = '<i class="fas fa-plus me-2"></i>Tambah Tool';
$form_visible_class = '';
if ($current_admin_page === 'tools' && isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    foreach ($tools as $tool_item) {
        if (isset($tool_item['id']) && $tool_item['id'] === $_GET['id']) {
            $editing_tool = $tool_item;
            $form_title = 'Edit Tool: ' . htmlspecialchars($editing_tool['name']);
            $form_action = 'edit_tool';
            $submit_button_text = '<i class="fas fa-save me-2"></i>Simpan Perubahan';
            $form_visible_class = 'form-visible';
            break;
        }
    }
}


// === LOGIKA UNTUK FILE MANAGER ===
$file_manager_message = '';
$file_manager_error = '';
$base_tools_path = realpath($path_prefix . 'tools');

function sanitize_name($name) {
    return preg_replace('/[^a-zA-Z0-9\-\._]/', '', $name);
}

function is_path_safe($path, $base_path) {
    $real_path = realpath($path);
    // Pastikan base path tidak kosong sebelum melakukan pengecekan
    if (!$base_path) return false;
    return $real_path && strpos($real_path, $base_path) === 0;
}

if ($current_admin_page === 'file_manager' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'save_file':
            if (isset($_POST['file_path'], $_POST['file_content'])) {
                $file_to_save_path = $_POST['file_path'];
                if (is_path_safe($file_to_save_path, $base_tools_path)) {
                    if (is_writable($file_to_save_path)) {
                        file_put_contents($file_to_save_path, $_POST['file_content']) !== false
                            ? $file_manager_message = "File '" . htmlspecialchars(basename($file_to_save_path)) . "' berhasil disimpan."
                            : $file_manager_error = "Gagal menulis ke file '" . htmlspecialchars(basename($file_to_save_path)) . "'.";
                    } else {
                        $file_manager_error = "File tidak dapat ditulis. Periksa izin.";
                    }
                } else {
                    $file_manager_error = "Akses ditolak: path file tidak valid.";
                }
            } else {
                $file_manager_error = "Data tidak lengkap untuk menyimpan file.";
            }
            break;
        case 'create_folder':
            if (isset($_POST['new_folder_name'])) {
                $new_folder_name = sanitize_name($_POST['new_folder_name']);
                if (!empty($new_folder_name)) {
                    $new_folder_path = $base_tools_path . DIRECTORY_SEPARATOR . $new_folder_name;
                    if (!file_exists($new_folder_path)) {
                        mkdir($new_folder_path, 0755, true)
                            ? $file_manager_message = "Folder '" . htmlspecialchars($new_folder_name) . "' berhasil dibuat."
                            : $file_manager_error = "Gagal membuat folder. Periksa izin direktori 'tools'.";
                    } else {
                        $file_manager_error = "Folder dengan nama '" . htmlspecialchars($new_folder_name) . "' sudah ada.";
                    }
                } else {
                    $file_manager_error = "Nama folder tidak valid.";
                }
            }
            break;
        case 'create_file':
            if (isset($_POST['new_file_name'], $_POST['current_dir'])) {
                $new_file_name = sanitize_name($_POST['new_file_name']);
                $current_dir = basename($_POST['current_dir']);
                $current_dir_path = $base_tools_path . DIRECTORY_SEPARATOR . $current_dir;
                if (is_path_safe($current_dir_path, $base_tools_path) && !empty($new_file_name)) {
                    $new_file_path = $current_dir_path . DIRECTORY_SEPARATOR . $new_file_name;
                    if (!file_exists($new_file_path)) {
                        touch($new_file_path)
                            ? $file_manager_message = "File '" . htmlspecialchars($new_file_name) . "' berhasil dibuat."
                            : $file_manager_error = "Gagal membuat file. Periksa izin folder.";
                    } else {
                        $file_manager_error = "File dengan nama '" . htmlspecialchars($new_file_name) . "' sudah ada.";
                    }
                } else {
                    $file_manager_error = "Nama file atau direktori tidak valid.";
                }
            }
            break;
        case 'delete_file':
            if (isset($_POST['file_to_delete'])) {
                $file_to_delete_path = $_POST['file_to_delete'];
                if (is_path_safe($file_to_delete_path, $base_tools_path)) {
                    unlink($file_to_delete_path)
                        ? $file_manager_message = "File '" . htmlspecialchars(basename($file_to_delete_path)) . "' berhasil dihapus."
                        : $file_manager_error = "Gagal menghapus file.";
                } else {
                    $file_manager_error = "Akses ditolak: path file tidak valid.";
                }
            }
            break;
        case 'delete_folder':
            if (isset($_POST['dir_to_delete'])) {
                $dir_to_delete_path = $_POST['dir_to_delete'];
                if (is_path_safe($dir_to_delete_path, $base_tools_path)) {
                    if (count(scandir($dir_to_delete_path)) == 2) {
                        rmdir($dir_to_delete_path)
                            ? $file_manager_message = "Folder '" . htmlspecialchars(basename($dir_to_delete_path)) . "' berhasil dihapus."
                            : $file_manager_error = "Gagal menghapus folder.";
                    } else {
                        $file_manager_error = "Gagal menghapus. Folder '" . htmlspecialchars(basename($dir_to_delete_path)) . "' tidak kosong.";
                    }
                } else {
                     $file_manager_error = "Akses ditolak: path direktori tidak valid.";
                }
            }
            break;
    }
}
?>

<div class="admin-dashboard-enhanced container-fluid px-lg-4 mt-4 mb-4">
    <header class="admin-header py-3 mb-4 border-bottom">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0 text-dark-emphasis"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3">Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>!</span>
                <a href="logout.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
        <nav class="nav nav-pills mt-3">
            <a class="nav-link <?php echo ($current_admin_page === 'tools') ? 'active' : ''; ?>" href="dashboard.php?page=tools"><i class="fas fa-tools me-1"></i> Manajemen Tools</a>
            <a class="nav-link <?php echo ($current_admin_page === 'feedback') ? 'active' : ''; ?>" href="dashboard.php?page=feedback"><i class="fas fa-comments me-1"></i> Kritik dan Saran</a>
            <a class="nav-link <?php echo ($current_admin_page === 'stats') ? 'active' : ''; ?>" href="dashboard.php?page=stats"><i class="fas fa-chart-bar me-1"></i> Statistik</a>
            <a class="nav-link <?php echo ($current_admin_page === 'file_manager') ? 'active' : ''; ?>" href="dashboard.php?page=file_manager"><i class="fas fa-folder-open me-1"></i> File Manager</a>
        </nav>
    </header>

    <?php if ($current_admin_page === 'tools'): ?>
    <section class="mb-4">
        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <div class="stat-card card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center"><div class="stat-icon bg-primary text-white me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;"><i class="fas fa-tools fa-2x"></i></div><div><h5 class="card-title mb-1">Total Tools</h5><p class="card-text fs-2 fw-bold mb-0"><?php echo $total_tools; ?></p></div></div></div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="stat-card card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center"><div class="stat-icon bg-success text-white me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;"><i class="fas fa-check-circle fa-2x"></i></div><div><h5 class="card-title mb-1">Tools Aktif</h5><p class="card-text fs-2 fw-bold mb-0"><?php echo $active_tools_count; ?></p></div></div></div>
            </div>
            <div class="col-lg-4 col-md-12">
                <div class="stat-card card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center"><div class="stat-icon bg-warning text-dark me-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;"><i class="fas fa-exclamation-triangle fa-2x"></i></div><div><h5 class="card-title mb-1">Tools Maintenance</h5><p class="card-text fs-2 fw-bold mb-0"><?php echo $maintenance_tools_count; ?></p></div></div></div>
            </div>
        </div>
    </section>
    <section class="mb-3 text-end" id="addToolButtonContainer">
         <button id="showAddToolFormBtn" class="btn btn-lg btn-primary shadow-sm"><i class="fas fa-plus-circle me-2"></i>Tambah Tool Baru</button>
    </section>
    <section class="mb-4 <?php echo $form_visible_class; ?>" id="manageToolFormSection" style="max-height: 0; overflow: hidden; transition: max-height 0.5s ease-in-out;">
        <div class="card form-manage-tool border-0 shadow-sm"><div class="card-header bg-light py-3 d-flex justify-content-between align-items-center"><h5 class="mb-0"><i id="formIcon" class="<?php echo $editing_tool ? 'fas fa-edit' : 'fas fa-plus-circle'; ?> me-2"></i><span id="formTitleText"><?php echo $form_title; ?></span></h5><button type="button" class="btn-close" id="hideFormBtn" aria-label="Close" data-bs-toggle="tooltip" title="Tutup Form"></button></div><div class="card-body p-4"><form action="tool_actions.php" method="POST" id="toolForm"><input type="hidden" name="action" id="formAction" value="<?php echo htmlspecialchars($form_action); ?>"><input type="hidden" name="tool_id" id="toolId" value="<?php echo htmlspecialchars($editing_tool['id'] ?? ''); ?>"><div class="row g-3"><div class="col-md-6 mb-3"><label for="tool_name" class="form-label">Nama Tool <span class="text-danger">*</span></label><input type="text" class="form-control form-control-lg" id="tool_name" name="tool_name" value="<?php echo htmlspecialchars($editing_tool['name'] ?? ''); ?>" required></div><div class="col-md-6 mb-3"><label for="tool_slug" class="form-label">Slug <span class="text-danger">*</span></label><input type="text" class="form-control form-control-lg" id="tool_slug" name="tool_slug" value="<?php echo htmlspecialchars($editing_tool['slug'] ?? ''); ?>" <?php echo $editing_tool ? 'readonly title="Slug tidak dapat diubah setelah dibuat."' : 'required'; ?> pattern="[a-z0-9]+(?:-[a-z0-9]+)*" title="Hanya huruf kecil, angka, dan tanda hubung. Contoh: my-tool-keren"><small id="slugHelpText" class="form-text text-muted"><?php echo $editing_tool ? 'Slug tidak dapat diubah.' : 'Akan dibuat otomatis jika dikosongkan, atau isi manual (huruf kecil, angka, strip).'; ?></small></div></div><div class="row g-3"><div class="col-md-6 mb-3"><label for="tool_icon" class="form-label">Ikon (Font Awesome) <span class="text-danger">*</span></label><div class="input-group input-group-lg"><span class="input-group-text"><i id="iconPreview" class="<?php echo htmlspecialchars($editing_tool['icon'] ?? 'fas fa-tools'); ?>"></i></span><input type="text" class="form-control" id="tool_icon" name="tool_icon" value="<?php echo htmlspecialchars($editing_tool['icon'] ?? 'fas fa-tools'); ?>" required placeholder="e.g., fas fa-cog"></div><small class="form-text text-muted">Lihat ikon di <a href="https://fontawesome.com/icons" target="_blank" rel="noopener noreferrer">Font Awesome</a>. Contoh: <code>fas fa-star</code></small></div><div class="col-md-6 mb-3"><label for="tool_status" class="form-label">Status <span class="text-danger">*</span></label><select class="form-select form-select-lg" id="tool_status" name="tool_status" required><option value="active" <?php echo (isset($editing_tool['status']) && $editing_tool['status'] === 'active') ? 'selected' : ''; ?>>Aktif</option><option value="maintenance" <?php echo (isset($editing_tool['status']) && $editing_tool['status'] === 'maintenance') ? 'selected' : ''; ?>>Maintenance</option></select></div></div><div class="mb-3"><label for="tool_description" class="form-label">Deskripsi Singkat <span class="text-danger">*</span></label><textarea class="form-control form-control-lg" id="tool_description" name="tool_description" rows="3" required><?php echo htmlspecialchars($editing_tool['description'] ?? ''); ?></textarea></div><div class="d-flex justify-content-end"><button type="button" id="cancelFormBtn" class="btn btn-outline-secondary btn-lg me-2" style="display: <?php echo $editing_tool ? 'inline-block' : 'none'; ?>;"><i class="fas fa-times me-2"></i>Batal</button><button type="submit" class="btn btn-primary btn-lg" id="submitFormBtn"><?php echo $submit_button_text; ?></button></div></form></div></div></section>
    <section><div class="card list-tools-card border-0 shadow-sm"><div class="card-header bg-light py-3"><h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Daftar Tools Tersedia</h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-striped mb-0 admin-tools-table"><thead class="table-light"><tr><th scope="col" style="width: 5%;">No.</th><th scope="col" style="width: 25%;">Nama Tool</th><th scope="col" style="width: 20%;">Slug</th><th scope="col" style="width: 15%;">Status</th><th scope="col" style="width: 15%;">Folder Tool</th><th scope="col" class="text-center" style="width: 20%;">Aksi</th></tr></thead><tbody><?php if (empty($tools)): ?><tr><td colspan="6" class="text-center py-4"><i class="fas fa-info-circle fa-2x text-muted mb-2"></i><br>Belum ada tools yang ditambahkan.</td></tr><?php else: ?><?php foreach ($tools as $index => $tool): ?><tr id="tool-row-<?php echo htmlspecialchars($tool['id'] ?? ''); ?>"><td><?php echo $index + 1; ?></td><td><i class="<?php echo htmlspecialchars($tool['icon'] ?? ''); ?> me-2 text-primary"></i><?php echo htmlspecialchars($tool['name'] ?? ''); ?></td><td><code><?php echo htmlspecialchars($tool['slug'] ?? ''); ?></code></td><td><?php if (isset($tool['status']) && $tool['status'] === 'active'): ?><span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2"><i class="fas fa-check-circle me-1"></i>Aktif</span><?php else: ?><span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i>Maintenance</span><?php endif; ?></td><td><?php $tool_folder_path = $path_prefix . 'tools/' . ($tool['slug'] ?? ''); if (!empty($tool['slug']) && is_dir($tool_folder_path)) { echo '<span class="text-success"><i class="fas fa-folder-open me-1"></i>Ada</span>'; } else { echo '<span class="text-danger"><i class="fas fa-folder-minus me-1"></i>Tidak Ada</span>'; } ?></td><td class="text-center action-buttons"><a href="dashboard.php?page=tools&action=edit&id=<?php echo htmlspecialchars($tool['id'] ?? ''); ?>#manageToolFormSection" class="btn btn-sm btn-outline-primary me-1 edit-tool-btn" data-tool-id="<?php echo htmlspecialchars($tool['id'] ?? ''); ?>" data-bs-toggle="tooltip" title="Edit Tool"><i class="fas fa-edit"></i></a><form action="tool_actions.php" method="POST" class="d-inline"><input type="hidden" name="action" value="toggle_maintenance"><input type="hidden" name="tool_id" value="<?php echo htmlspecialchars($tool['id'] ?? ''); ?>"><button type="submit" class="btn btn-sm btn-outline-<?php echo (isset($tool['status']) && $tool['status'] === 'active') ? 'warning' : 'success'; ?> me-1" data-bs-toggle="tooltip" title="<?php echo (isset($tool['status']) && $tool['status'] === 'active') ? 'Set ke Maintenance' : 'Set ke Aktif'; ?>"><i class="fas <?php echo (isset($tool['status']) && $tool['status'] === 'active') ? 'fa-tools' : 'fa-play-circle'; ?>"></i></button></form><form action="tool_actions.php" method="POST" class="d-inline" onsubmit="return confirmDeleteTool('<?php echo htmlspecialchars(addslashes($tool['name'] ?? '')); ?>');"><input type="hidden" name="action" value="delete_tool"><input type="hidden" name="tool_id" value="<?php echo htmlspecialchars($tool['id'] ?? ''); ?>"><button type="submit" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Hapus Tool"><i class="fas fa-trash-alt"></i></button></form></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div></div></section>
    
    <?php elseif ($current_admin_page === 'feedback'): ?>
    <section id="feedbackSection">
        <h2 class="mb-4"><i class="fas fa-comments"></i> Daftar Kritik dan Saran</h2>
        <?php $feedback_list = get_all_feedback($feedback_file_path); if (empty($feedback_list)): ?>
            <div class="alert alert-info" role="alert">Belum ada kritik dan saran yang diterima.</div>
        <?php else: ?>
            <div class="card shadow-sm"><div class="card-header bg-light py-3">Total Feedback: <?php echo count($feedback_list); ?></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-striped table-hover mb-0 admin-feedback-table"><thead class="table-light"><tr><th scope="col" style="width: 5%;">No.</th><th scope="col" style="width: 15%;">Timestamp</th><th scope="col" style="width: 20%;">Tool</th><th scope="col" style="width: 15%;">Pengkritik</th><th scope="col" style="width: 30%;">Saran</th><th scope="col" style="width: 10%;">IP</th><th scope="col" class="text-center" style="width: 5%;">Aksi</th></tr></thead><tbody><?php foreach ($feedback_list as $index => $feedback): ?><tr><td><?php echo $index + 1; ?></td><td><?php echo htmlspecialchars($feedback['timestamp'] ?? 'N/A'); ?></td><td><?php echo htmlspecialchars($feedback['tool_name'] ?? 'N/A'); ?></td><td><?php echo htmlspecialchars($feedback['critic_name'] ?? 'Anonim'); ?></td><td><small><?php echo nl2br(htmlspecialchars($feedback['suggestion'] ?? 'N/A')); ?></small></td><td><?php echo htmlspecialchars($feedback['ip_address'] ?? 'N/A'); ?></td><td class="text-center"><a href="dashboard.php?page=feedback&action=delete_feedback&id=<?php echo urlencode($feedback['timestamp'] ?? ''); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Anda yakin ingin menghapus feedback ini?');" data-bs-toggle="tooltip" title="Hapus Feedback"><i class="fas fa-trash-alt"></i></a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
        <?php endif; ?>
    </section>

    <?php elseif ($current_admin_page === 'stats'): ?>
    <section id="toolUsageStatsSection">
        <h2 class="mb-4"><i class="fas fa-chart-line"></i> Statistik Penggunaan Tools</h2>
        <div class="alert alert-warning" role="alert"><i class="fas fa-info-circle me-2"></i><strong>Catatan:</strong> Statistik berikut diambil dari file <code>tool_usage_stats.json</code> di server.</div>
        <?php 
            $tool_usage_stats_display_data = [];
            $error_loading_usage_stats = '';
            if (file_exists($tool_usage_stats_file)) {
                $stats_content = file_get_contents($tool_usage_stats_file);
                $decoded_stats = json_decode($stats_content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_stats)) {
                    $tool_name_map = [];
                    foreach ($tools as $tool_info) {
                        $tool_name_map[$tool_info['slug']] = $tool_info['name'];
                    }
                    foreach ($decoded_stats as $slug => $count) {
                        $tool_usage_stats_display_data[] = ['name' => $tool_name_map[$slug] ?? "Tool (slug: " . htmlspecialchars($slug) . ")", 'slug' => htmlspecialchars($slug), 'count' => intval($count)];
                    }
                    usort($tool_usage_stats_display_data, function($a, $b) { return $b['count'] - $a['count']; });
                } else { $error_loading_usage_stats = "Gagal membaca format file statistik."; }
            } else { $error_loading_usage_stats = "File statistik penggunaan tidak ditemukan."; }
        ?>
        <?php if (!empty($error_loading_usage_stats)): ?><div class="alert alert-danger"><?php echo $error_loading_usage_stats; ?></div><?php endif; ?>
        <div class="card shadow-sm"><div class="card-header bg-light py-3">Data Penggunaan Tools</div><div class="card-body">
            <?php if (empty($tool_usage_stats_display_data)): ?><p class="text-muted">Belum ada data penggunaan tools yang tercatat.</p><?php else: ?><div class="list-group">
                <?php foreach ($tool_usage_stats_display_data as $stat_item): ?><div class="list-group-item d-flex justify-content-between align-items-center"><?php echo htmlspecialchars($stat_item['name']); ?><span class="badge bg-primary rounded-pill"><?php echo $stat_item['count']; ?> kali</span></div><?php endforeach; ?>
            </div><?php endif; ?>
        </div></div>
    </section>

    <?php elseif ($current_admin_page === 'file_manager'): ?>
    <section id="fileManagerSection">
        <h2 class="mb-4"><i class="fas fa-folder-open"></i> File Manager (Direktori: /tools)</h2>
        <?php if (!empty($file_manager_message)): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo $file_manager_message; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
        <?php if (!empty($file_manager_error)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo $file_manager_error; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
        <div class="row"><div class="col-lg-4 mb-4 mb-lg-0"><div class="card shadow-sm"><div class="card-header d-flex justify-content-between align-items-center"><span>Folder Tools</span><button class="btn btn-sm btn-outline-primary" id="addFolderBtn" data-bs-toggle="tooltip" title="Tambah Folder Baru"><i class="fas fa-plus"></i></button></div><div class="list-group list-group-flush"><?php $dirs = array_filter(glob($base_tools_path . '/*'), 'is_dir'); if (empty($dirs)) { echo '<div class="list-group-item text-muted">Tidak ada folder ditemukan.</div>'; } else { foreach ($dirs as $dir) { $dir_name = basename($dir); $is_active_dir = (isset($_GET['dir']) && $_GET['dir'] === $dir_name); echo '<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ' . ($is_active_dir ? 'active' : '') . '">'; echo '<a href="dashboard.php?page=file_manager&dir=' . urlencode($dir_name) . '" class="text-decoration-none stretched-link ' . ($is_active_dir ? 'text-white' : 'text-body') . '">'; echo '<i class="fas fa-folder me-2"></i>' . htmlspecialchars($dir_name); echo '</a>'; echo '<form method="POST" action="dashboard.php?page=file_manager" class="d-inline" onsubmit="return confirm(\'Anda yakin ingin menghapus folder ini? HANYA BISA JIKA FOLDER KOSONG!\');">'; echo '<input type="hidden" name="action" value="delete_folder">'; echo '<input type="hidden" name="dir_to_delete" value="' . htmlspecialchars($dir) . '">'; echo '<button type="submit" class="btn btn-sm btn-outline-danger border-0" data-bs-toggle="tooltip" title="Hapus Folder"><i class="fas fa-trash-alt"></i></button>'; echo '</form>'; echo '</div>'; } } ?></div></div></div>
            <div class="col-lg-8"><?php if (isset($_GET['dir'])): $selected_dir_name = basename($_GET['dir']); $current_dir_path = $base_tools_path . DIRECTORY_SEPARATOR . $selected_dir_name; if (is_path_safe($current_dir_path, $base_tools_path) && is_dir($current_dir_path)): ?><div class="card shadow-sm mb-4"><div class="card-header d-flex justify-content-between align-items-center"><span>File di /tools/<?php echo htmlspecialchars($selected_dir_name); ?></span><button class="btn btn-sm btn-outline-primary" id="addFileBtn" data-current-dir="<?php echo htmlspecialchars($selected_dir_name); ?>" data-bs-toggle="tooltip" title="Tambah File Baru"><i class="fas fa-plus"></i></button></div><div class="list-group list-group-flush"><?php $files = new DirectoryIterator($current_dir_path); $file_found = false; foreach ($files as $fileinfo) { if (!$fileinfo->isDot() && $fileinfo->isFile()) { $file_found = true; $file_name = $fileinfo->getFilename(); $is_active_file = (isset($_GET['file']) && $_GET['file'] === $file_name); $file_path_full = $fileinfo->getPathname(); echo '<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center ' . ($is_active_file ? 'active' : '') . '">'; echo '<a href="dashboard.php?page=file_manager&dir=' . urlencode($selected_dir_name) . '&file=' . urlencode($file_name) . '" class="text-decoration-none stretched-link ' . ($is_active_file ? 'text-white' : 'text-body') . '">'; $icon = 'fa-file-alt'; $icon_prefix = 'fas'; $ext = strtolower($fileinfo->getExtension()); if (in_array($ext, ['php', 'html'])) $icon = 'fa-code'; if (in_array($ext, ['js'])) {$icon = 'fa-js-square'; $icon_prefix = 'fab';} if (in_array($ext, ['css'])) {$icon = 'fa-css3-alt'; $icon_prefix = 'fab';} if (in_array($ext, ['json'])) $icon = 'fa-file-code'; echo '<i class="' . $icon_prefix . ' ' . $icon . ' me-2"></i>' . htmlspecialchars($file_name); echo '</a>'; echo '<form method="POST" action="dashboard.php?page=file_manager&dir=' . urlencode($selected_dir_name) . '" class="d-inline" onsubmit="return confirm(\'Anda yakin ingin menghapus file ini?\');">'; echo '<input type="hidden" name="action" value="delete_file">'; echo '<input type="hidden" name="file_to_delete" value="' . htmlspecialchars($file_path_full) . '">'; echo '<button type="submit" class="btn btn-sm btn-outline-danger border-0" data-bs-toggle="tooltip" title="Hapus File"><i class="fas fa-trash-alt"></i></button>'; echo '</form>'; echo '</div>'; } } if (!$file_found) { echo '<div class="list-group-item text-muted">Tidak ada file ditemukan.</div>'; } ?></div></div><?php endif; endif; ?><?php if (isset($_GET['dir']) && isset($_GET['file'])): $selected_dir_name = basename($_GET['dir']); $selected_file_name = basename($_GET['file']); $file_to_edit_path = $base_tools_path . DIRECTORY_SEPARATOR . $selected_dir_name . DIRECTORY_SEPARATOR . $selected_file_name; if (is_path_safe($file_to_edit_path, $base_tools_path) && file_exists($file_to_edit_path) && is_readable($file_to_edit_path)): $file_content = file_get_contents($file_to_edit_path); ?><div class="card shadow-sm"><div class="card-header d-flex justify-content-between align-items-center"><span>Mengedit: <?php echo htmlspecialchars($selected_file_name); ?></span><span class="badge bg-secondary"><?php echo round(filesize($file_to_edit_path) / 1024, 2); ?> KB</span></div><div class="card-body"><form method="POST" action="dashboard.php?page=file_manager&dir=<?php echo urlencode($selected_dir_name); ?>&file=<?php echo urlencode($selected_file_name); ?>"><input type="hidden" name="action" value="save_file"><input type="hidden" name="file_path" value="<?php echo htmlspecialchars($file_to_edit_path); ?>"><div class="mb-3"><textarea name="file_content" class="form-control" rows="20" style="font-family: monospace; font-size: 0.9rem;"><?php echo htmlspecialchars($file_content); ?></textarea></div><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Perubahan</button></form></div></div><?php else: ?><div class="alert alert-danger">File tidak ditemukan atau tidak dapat diakses.</div><?php endif; endif; ?></div></div></section>

    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Halaman tidak ditemukan. Silakan pilih menu di atas.
        </div>
    <?php endif; ?>

</div> 

<script>
document.addEventListener('DOMContentLoaded', function() {
    const currentAdminPage = '<?php echo $current_admin_page; ?>';
    
    if (currentAdminPage === 'tools') {
        const toolNameInput = document.getElementById('tool_name');
        const toolSlugInput = document.getElementById('tool_slug');
        const toolIconInput = document.getElementById('tool_icon');
        const iconPreview = document.getElementById('iconPreview');
        const manageToolFormSection = document.getElementById('manageToolFormSection');
        const showAddToolFormBtn = document.getElementById('showAddToolFormBtn');
        const hideFormBtn = document.getElementById('hideFormBtn');
        const cancelFormBtn = document.getElementById('cancelFormBtn');
        const toolForm = document.getElementById('toolForm');
        const formTitleText = document.getElementById('formTitleText');
        const formIcon = document.getElementById('formIcon');
        const formActionInput = document.getElementById('formAction');
        const toolIdInput = document.getElementById('toolId');
        const submitFormBtn = document.getElementById('submitFormBtn');
        const slugHelpText = document.getElementById('slugHelpText');
        const addToolButtonContainer = document.getElementById('addToolButtonContainer');

        function createSlug(str) {
            if (!str) return '';
            str = str.replace(/^\s+|\s+$/g, ''); 
            str = str.toLowerCase();
            var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to   = "aaaaeeeeiiiioooouuuunc------";
            for (var i=0, l=from.length ; i<l ; i++) {
                str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }
            str = str.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
            return str;
        }

        if (toolNameInput && toolSlugInput && !toolSlugInput.hasAttribute('readonly')) {
            toolNameInput.addEventListener('keyup', function() {
                if (!toolSlugInput.hasAttribute('readonly')) {
                     toolSlugInput.value = createSlug(this.value);
                }
            });
        }

        if (toolIconInput && iconPreview) {
            toolIconInput.addEventListener('keyup', function() {
                iconPreview.className = this.value || 'fas fa-tools';
            });
            if(iconPreview) iconPreview.className = toolIconInput.value || 'fas fa-tools';
        }

        function resetFormToAddMode() {
            if(toolForm) toolForm.reset();
            if(iconPreview) iconPreview.className = 'fas fa-tools';
            if(formTitleText) formTitleText.textContent = 'Tambah Tool Baru';
            if(formIcon) formIcon.className = 'fas fa-plus-circle me-2';
            if(formActionInput) formActionInput.value = 'add_tool';
            if(toolIdInput) toolIdInput.value = '';
            if(submitFormBtn) submitFormBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Tambah Tool';
            if(toolSlugInput) toolSlugInput.removeAttribute('readonly');
            if(slugHelpText) slugHelpText.textContent = 'Akan dibuat otomatis jika dikosongkan, atau isi manual (huruf kecil, angka, strip).';
            if(cancelFormBtn) cancelFormBtn.style.display = 'none';
            const url = new URL(window.location);
            url.searchParams.delete('action');
            url.searchParams.delete('id');
            window.history.pushState({}, '', url.pathname + '?page=tools');
        }

        function showForm() {
            if(manageToolFormSection) {
                manageToolFormSection.classList.add('form-visible');
                manageToolFormSection.style.maxHeight = manageToolFormSection.scrollHeight + "px";
                manageToolFormSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            if(addToolButtonContainer) addToolButtonContainer.style.display = 'none';
        }

        function hideForm() {
            if(manageToolFormSection) {
                manageToolFormSection.classList.remove('form-visible');
                manageToolFormSection.style.maxHeight = "0px"; 
            }
            if(addToolButtonContainer) addToolButtonContainer.style.display = 'block';
            resetFormToAddMode();
        }
        
        if (showAddToolFormBtn) {
            showAddToolFormBtn.addEventListener('click', function() {
                resetFormToAddMode();
                showForm();
            });
        }

        if (hideFormBtn) hideFormBtn.addEventListener('click', hideForm);
        if (cancelFormBtn) cancelFormBtn.addEventListener('click', hideForm);

        if (manageToolFormSection && manageToolFormSection.classList.contains('form-visible')) {
            if(addToolButtonContainer) addToolButtonContainer.style.display = 'none';
            if(cancelFormBtn) cancelFormBtn.style.display = 'inline-block';
            manageToolFormSection.style.maxHeight = manageToolFormSection.scrollHeight + "px";
        } else if (manageToolFormSection) {
            manageToolFormSection.style.maxHeight = "0px"; 
        }

        window.confirmDeleteTool = function(toolName) {
            return confirm(`Apakah Anda yakin ingin menghapus tool "${toolName}"? Folder tool terkait juga perlu dihapus manual jika ada.`);
        }
    } 

    if (currentAdminPage === 'file_manager') {
        function postForm(action, data) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'dashboard.php?page=file_manager' + (data.current_dir ? '&dir=' + encodeURIComponent(data.current_dir) : '');
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = action;
            form.appendChild(actionInput);
            for (const key in data) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }
            document.body.appendChild(form);
            form.submit();
        }

        const addFolderBtn = document.getElementById('addFolderBtn');
        if (addFolderBtn) {
            addFolderBtn.addEventListener('click', function() {
                const newFolderName = prompt('Masukkan nama folder baru (hanya huruf, angka, strip, garis bawah):');
                if (newFolderName) {
                    postForm('create_folder', { new_folder_name: newFolderName });
                }
            });
        }
        
        const addFileBtn = document.getElementById('addFileBtn');
        if (addFileBtn) {
            addFileBtn.addEventListener('click', function() {
                const newFileName = prompt('Masukkan nama file baru (termasuk ekstensi, misal: index.php):');
                if (newFileName) {
                    postForm('create_file', { 
                        new_file_name: newFileName,
                        current_dir: this.getAttribute('data-current-dir')
                    });
                }
            });
        }
    }
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>

<?php 
if (file_exists($path_prefix . 'footer.php')) {
    include $path_prefix . 'footer.php';
} else {
    echo "</body></html>";
}
?>