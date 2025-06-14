<?php
session_start();
// Base URL untuk path aset yang konsisten.
$base_url = '/';

// --- LOGIKA BARU: MENGAMBIL SLUG DARI URL ---
$page_slug = ''; // Inisialisasi variabel slug
// Ambil path URL tanpa query string
$request_uri = strtok($_SERVER['REQUEST_URI'], '?');

// Gunakan regex untuk mencocokkan pola /tools/slug-tool/ dan mengambil slug-nya
if (preg_match('/\/tools\/([a-zA-Z0-9_-]+)\/?$/', $request_uri, $matches)) {
    // Jika cocok, $matches[1] akan berisi slug-nya
    $page_slug = $matches[1];
}
// --- AKHIR LOGIKA BARU ---
?>
<!DOCTYPE html>
<html lang="id" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Webtools Directory' : 'Webtools Directory'; ?></title>
    
    <?php // Meta tag ini hanya akan muncul jika $page_slug berhasil ditemukan dari URL
    if (!empty($page_slug)): ?>
    <meta name="tool-slug-stats" content="<?php echo htmlspecialchars($page_slug); ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Google+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4285F4;
            --primary-dark: #3367D6;
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --bg-light: #f8f9fa;
            --border-color: #e0e0e0;
            --shadow: 0 1px 2px 0 rgba(60,64,67,0.3), 0 1px 3px 1px rgba(60,64,67,0.15);
            --shadow-hover: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }

        body {
            font-family: 'Inter', 'Google Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-primary);
        }

        /* Navbar */
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .navbar-brand .brand-container {
            display: flex;
            align-items: center;
        }
        .navbar-brand .brand-icon {
            background-color: var(--primary-color);
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }
        .navbar-brand .brand-text {
            font-family: 'Google Sans', sans-serif;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        .nav-link i { font-size: 20px; }

        /* Hero Section */
        .hero-section {
            text-align: center;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .hero-title {
            font-family: 'Google Sans', sans-serif;
            font-weight: 700;
            font-size: 2.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            color: var(--text-primary);
        }
        .hero-icon { font-size: 3.5rem; color: var(--primary-color); }
        .hero-subtitle { font-size: 1.25rem; color: var(--text-secondary); }

        /* Search Section */
        .search-section { text-align: center; }
        .search-container { max-width: 700px; margin: auto; }
        .search-input-wrapper { position: relative; }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); }
        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            border-radius: 50px;
            border: 1px solid var(--border-color);
            font-size: 1.1rem;
            transition: box-shadow 0.2s;
        }
        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.3);
        }

        /* Tools Grid */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .tool-card-link { text-decoration: none; }
        .tool-card-link.hidden { display: none; } /* Untuk search */
        .tool-card {
            background-color: #fff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            height: 100%;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
            overflow: hidden;
        }
        .tool-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .tool-icon-wrapper {
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--bg-light);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .tool-content { flex-grow: 1; }
        .tool-name { font-weight: 600; font-size: 1.1rem; color: var(--text-primary); }
        .tool-description { color: var(--text-secondary); font-size: 0.9rem; }
        .tool-usage { display: flex; align-items: center; gap: 4px; color: var(--text-secondary); font-size: 0.8rem; margin-top: 0.5rem; }
        .tool-usage i { font-size: 16px; }

        .maintenance-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ffc107;
            color: #212529;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .maintenance-badge i { font-size: 14px; }
        .tool-card-link.tool-maintenance .tool-card { opacity: 0.7; }

        /* No Results */
        .no-results { text-align: center; padding: 3rem 0; }
        .no-results-icon { font-size: 4rem; color: #bdc1c6; }
        .no-results h3 { font-weight: 600; }

        /* Footer */
        .footer { background-color: #fff; padding: 1.5rem 0; margin-top: 3rem; border-top: 1px solid var(--border-color); }
        .footer-content { display: flex; justify-content: space-between; align-items: center; }
        .footer-link { display: flex; align-items: center; gap: 8px; text-decoration: none; color: var(--text-secondary); }
        .footer-copyright { color: var(--text-secondary); }

        /* Back to Top */
        .back-to-top-btn {
            display: none;
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            box-shadow: var(--shadow);
        }
    </style>
</head>
<body class="d-flex flex-column h-100">

<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $base_url; ?>">
            <div class="brand-container">
                <div class="brand-icon"><i class="material-icons">build_circle</i></div>
                <span class="brand-text">Webtools Directory</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_url; ?>">
                        <i class="material-icons">home</i>
                        <span>Home</span>
                    </a>
                </li>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_url; ?>admin/dashboard.php">
                           <i class="material-icons">dashboard</i>
                           <span>Admin</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-5 flex-grow-1">
