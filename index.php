
<?php
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Bloom & Petal | Toko Bunga';
require_once __DIR__ . '/includes/header.php';

// Mengambil 3 produk terbaru
$stmt = $pdo->query(
    "SELECT id, nama, kategori, harga, gambar
     FROM produk
     ORDER BY created_at DESC
     LIMIT 3"
);

$produkUnggulan = $stmt->fetchAll();

function rupiah($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

// Gambar utama beranda menggunakan file Toko.jpg
$gambarHero = '/bloom-petal/uploads/produk/Toko.jpg';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-content">

        <div>
            <span class="eyebrow">Flowers for every moment</span>

            <h1>Setiap bunga punya cerita.</h1>

            <p>
                Temukan rangkaian bunga pilihan untuk menyampaikan
                perasaan, merayakan momen spesial, dan menghadirkan
                keindahan dalam keseharian.
            </p>

            <a href="/bloom-petal/produk.php" class="btn">
                Jelajahi Koleksi
            </a>
        </div>

        <div>
            <img
                class="hero-image"
                src="<?= htmlspecialchars($gambarHero) ?>"
                alt="Bloom & Petal"
            >
        </div>

    </div>
</section>

<!-- Produk Unggulan -->
<section class="section">
    <div class="container">

        <div class="section-heading">
            <span class="eyebrow">Our collection</span>

            <h2>Bunga Pilihan</h2>

            <p class="section-description">
                Pilihan bunga untuk momen yang berarti.
            </p>
        </div>

        <?php if (empty($produkUnggulan)): ?>

            <div class="empty-state">
                Belum ada produk yang tersedia.
            </div>

        <?php else: ?>

            <div class="product-grid">

                <?php foreach ($produkUnggulan as $produk): ?>

                    <article class="product-card">

                        <?php if (!empty($produk['gambar'])): ?>

                            <img
                                src="/bloom-petal/uploads/produk/<?= htmlspecialchars(basename($produk['gambar'])) ?>"
                                alt="<?= htmlspecialchars($produk['nama']) ?>"
                                loading="lazy"
                            >

                        <?php else: ?>

                            <img
                                src="/bloom-petal/uploads/produk/Toko.jpg"
                                alt="Bunga Bloom & Petal"
                                loading="lazy"
                            >

                        <?php endif; ?>

                        <div class="product-info">

                            <span class="category">
                                <?= htmlspecialchars($produk['kategori']) ?>
                            </span>

                            <h3>
                                <?= htmlspecialchars($produk['nama']) ?>
                            </h3>

                            <p class="price">
                                <?= rupiah($produk['harga']) ?>
                            </p>

                            <a
                                class="btn"
                                href="/bloom-petal/detail.php?id=<?= (int) $produk['id'] ?>"
                            >
                                Lihat Detail
                            </a>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <div style="text-align:center">
            <a
                href="/bloom-petal/produk.php"
                class="btn btn-secondary"
            >
                Lihat Semua Produk
            </a>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
