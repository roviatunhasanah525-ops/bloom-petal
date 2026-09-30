
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Bloom & Petal | Flower Shop';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="/bloom-petal/assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container navbar">
        <a href="/bloom-petal/index.php" class="brand">
            Bloom <span>&amp; Petal</span>
        </a>

        <nav class="nav-links">
            <a href="/bloom-petal/index.php"
               class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                Beranda
            </a>
            <a href="/bloom-petal/produk.php"
               class="<?= in_array($currentPage, ['produk.php', 'detail.php']) ? 'active' : '' ?>">
                Koleksi Bunga
            </a>
            <a href="/bloom-petal/tentang.php"
               class="<?= $currentPage === 'tentang.php' ? 'active' : '' ?>">
                Tentang Kami
            </a>
            <a href="/bloom-petal/kontak.php"
               class="<?= $currentPage === 'kontak.php' ? 'active' : '' ?>">
                Kontak
            </a>
        </nav>
    </div>
</header>

<main>
