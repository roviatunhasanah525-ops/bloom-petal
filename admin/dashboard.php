
<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/database.php';

// Menghitung jumlah produk
$stmt = $pdo->query("SELECT COUNT(*) FROM produk");
$total_produk = $stmt->fetchColumn();

// Menghitung total stok
$stmt = $pdo->query("SELECT COALESCE(SUM(stok), 0) FROM produk");
$total_stok = $stmt->fetchColumn();

// Menghitung jumlah kategori
$stmt = $pdo->query("SELECT COUNT(DISTINCT kategori) FROM produk");
$total_kategori = $stmt->fetchColumn();

// Mengambil lima produk terbaru
$stmt = $pdo->query(
    "SELECT * FROM produk ORDER BY id DESC LIMIT 5"
);
$produk_terbaru = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Dashboard Admin</h1>

    <p>
        Selamat datang,
        <?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin') ?>!
    </p>

    <?php require_once __DIR__ . '/../includes/flash.php'; ?>

    <p>
        <a href="produk.php" class="btn">Kelola Produk</a>
        <a href="logout.php" class="btn">Logout</a>
    </p>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h3>Total Produk</h3>
            <p><?= (int) $total_produk ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Stok</h3>
            <p><?= (int) $total_stok ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Kategori</h3>
            <p><?= (int) $total_kategori ?></p>
        </div>
    </div>

    <h2>Produk Terbaru</h2>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($produk_terbaru)): ?>
                    <tr>
                        <td colspan="5">Belum ada produk.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($produk_terbaru as $i => $item): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($item['nama']) ?></td>
                            <td><?= htmlspecialchars($item['kategori']) ?></td>
                            <td>
                                Rp <?= number_format(
                                    (float) $item['harga'],
                                    0, ',', '.'
                                ) ?>
                            </td>
                            <td><?= (int) $item['stok'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
