
<?php
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Koleksi Bunga | Bloom & Petal';

// Mengambil input pencarian dan filter
$keyword = trim($_GET['keyword'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

// Mengambil daftar kategori dari database
$stmtKategori = $pdo->query(
    "SELECT DISTINCT kategori
     FROM produk
     ORDER BY kategori ASC"
);
$daftarKategori = $stmtKategori->fetchAll(PDO::FETCH_COLUMN);

// Menentukan jumlah produk per halaman
$perHalaman = 6;
$halaman = max(1, (int) ($_GET['halaman'] ?? 1));

// Menyiapkan kondisi pencarian
$where = [];
$params = [];

if ($keyword !== '') {
    $where[] = "nama LIKE :keyword";
    $params['keyword'] = "%{$keyword}%";
}

if ($kategori !== '' && in_array($kategori, $daftarKategori, true)) {
    $where[] = "kategori = :kategori";
    $params['kategori'] = $kategori;
}

$whereSQL = $where
    ? 'WHERE ' . implode(' AND ', $where)
    : '';

// Menghitung jumlah produk sesuai pencarian dan filter
$sqlJumlah = "SELECT COUNT(*) FROM produk $whereSQL";
$stmtJumlah = $pdo->prepare($sqlJumlah);
$stmtJumlah->execute($params);

$totalProduk = (int) $stmtJumlah->fetchColumn();
$totalHalaman = max(1, (int) ceil($totalProduk / $perHalaman));

$halaman = min($halaman, $totalHalaman);
$offset = ($halaman - 1) * $perHalaman;

// Mengambil produk untuk halaman saat ini
$sqlProduk = "
    SELECT id, nama, kategori, deskripsi, harga, stok, gambar
    FROM produk
    $whereSQL
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmtProduk = $pdo->prepare($sqlProduk);

foreach ($params as $key => $value) {
    $stmtProduk->bindValue(':' . $key, $value, PDO::PARAM_STR);
}

$stmtProduk->bindValue(':limit', $perHalaman, PDO::PARAM_INT);
$stmtProduk->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmtProduk->execute();
$daftarProduk = $stmtProduk->fetchAll();

function formatRupiah($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

// Menyiapkan parameter untuk tautan pagination
$parameterURL = [];

if ($keyword !== '') {
    $parameterURL['keyword'] = $keyword;
}

if ($kategori !== '' && in_array($kategori, $daftarKategori, true)) {
    $parameterURL['kategori'] = $kategori;
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="eyebrow">Bloom &amp; Petal Collection</span>
        <h1>Koleksi Bunga</h1>
        <p>Temukan bunga yang tepat untuk setiap momen berharga.</p>
    </div>
</section>

<section class="section">
    <div class="container">

        <!-- Form pencarian dan filter -->
        <form method="GET" action="produk.php"
              class="search-form"
              style="display:grid;grid-template-columns:2fr 1fr auto;gap:12px;margin-bottom:30px;">

            <input
                type="search"
                name="keyword"
                placeholder="Cari nama bunga..."
                value="<?= htmlspecialchars($keyword) ?>"
            >

            <select name="kategori">
                <option value="">Semua Kategori</option>

                <?php foreach ($daftarKategori as $namaKategori): ?>
                    <option
                        value="<?= htmlspecialchars($namaKategori) ?>"
                        <?= $kategori === $namaKategori ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($namaKategori) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn" style="margin-top:0;">
                Cari
            </button>
        </form>

        <div style="margin-bottom:24px;">
            <p>
                Menampilkan
                <strong><?= $totalProduk ?></strong>
                produk
                <?php if ($keyword !== ''): ?>
                    untuk pencarian
                    <strong>"<?= htmlspecialchars($keyword) ?>"</strong>
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($daftarProduk)): ?>
            <div class="empty-state">
                <h2>Produk tidak ditemukan</h2>
                <p>Coba kata kunci atau kategori lainnya.</p>
                <a href="produk.php" class="btn">Lihat Semua Produk</a>
            </div>
        <?php else: ?>

            <div class="product-grid">
                <?php foreach ($daftarProduk as $produk): ?>
                    <article class="product-card">

                        <?php if (!empty($produk['gambar'])): ?>
                            <img
                                src="/bloom-petal/uploads/produk/<?= htmlspecialchars($produk['gambar']) ?>"
                                alt="<?= htmlspecialchars($produk['nama']) ?>"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <img
                                src="https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=700&q=80"
                                alt="Bunga dari Bloom & Petal"
                                loading="lazy"
                            >
                        <?php endif; ?>

                        <div class="product-info">
                            <span class="category">
                                <?= htmlspecialchars($produk['kategori']) ?>
                            </span>

                            <h3><?= htmlspecialchars($produk['nama']) ?></h3>

                            <p class="price">
                                <?= formatRupiah($produk['harga']) ?>
                            </p>

                            <p>
                                <?= htmlspecialchars(
                                    mb_strimwidth($produk['deskripsi'], 0, 90, '...')
                                ) ?>
                            </p>

                            <a
                                href="detail.php?id=<?= (int) $produk['id'] ?>"
                                class="btn"
                            >
                                Lihat Detail
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalHalaman > 1): ?>
                <nav aria-label="Navigasi halaman"
                     style="display:flex;justify-content:center;align-items:center;gap:10px;margin-top:35px;flex-wrap:wrap;">

                    <?php if ($halaman > 1): ?>
                        <a
                            class="btn btn-secondary"
                            style="margin-top:0;"
                            href="?<?= http_build_query(array_merge($parameterURL, ['halaman' => $halaman - 1])) ?>"
                        >
                            Sebelumnya
                        </a>
                    <?php endif; ?>

                    <span>
                        Halaman <?= $halaman ?> dari <?= $totalHalaman ?>
                    </span>

                    <?php if ($halaman < $totalHalaman): ?>
                        <a
                            class="btn"
                            style="margin-top:0;"
                            href="?<?= http_build_query(array_merge($parameterURL, ['halaman' => $halaman + 1])) ?>"
                        >
                            Berikutnya
                        </a>
                    <?php endif; ?>

                </nav>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
