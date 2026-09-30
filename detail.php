
<?php
require_once __DIR__ . '/config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: produk.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = :id");
$stmt->execute(['id' => $id]);
$produk = $stmt->fetch();

if (!$produk) {
    http_response_code(404);
    $pageTitle = 'Produk Tidak Ditemukan | Bloom & Petal';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="section container"><div class="empty-state"><h2>Produk tidak ditemukan</h2><a class="btn" href="produk.php">Kembali ke Katalog</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $produk['nama'] . ' | Bloom & Petal';
require_once __DIR__ . '/includes/header.php';

function hargaDetail($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}
?>

<section class="section">
    <div class="container">
        <p><a href="produk.php">Koleksi Bunga</a> / Detail Produk</p>

        <div class="detail-grid">
            <div>
                <?php if (!empty($produk['gambar'])): ?>
                    <img
                        class="detail-image"
                        src="/bloom-petal/uploads/produk/<?= htmlspecialchars($produk['gambar']) ?>"
                        alt="<?= htmlspecialchars($produk['nama']) ?>"
                    >
                <?php else: ?>
                    <img
                        class="detail-image"
                        src="https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=1000&q=85"
                        alt="Bunga Bloom & Petal"
                    >
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <span class="category">
                    <?= htmlspecialchars($produk['kategori']) ?>
                </span>

                <h1><?= htmlspecialchars($produk['nama']) ?></h1>

                <p class="price detail-price">
                    <?= hargaDetail($produk['harga']) ?>
                </p>

                <p><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?></p>

                <p class="stock">
                    <?= (int) $produk['stok'] > 0
                        ? 'Stok tersedia: ' . (int) $produk['stok']
                        : 'Stok sedang habis' ?>
                </p>

                <a href="produk.php" class="btn">Kembali ke Koleksi</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
