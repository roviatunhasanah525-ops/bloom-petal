
<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_produk'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $harga = filter_var($_POST['harga'] ?? '', FILTER_VALIDATE_FLOAT);
    $stok = filter_var($_POST['stok'] ?? '', FILTER_VALIDATE_INT);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $namaGambar = null;
    $fileBaru = null;

    // Validasi data produk
    if (
        $nama === '' ||
        $kategori === '' ||
        $harga === false ||
        $harga <= 0 ||
        $stok === false ||
        $stok < 0
    ) {
        $error = 'Lengkapi data dengan benar. Harga harus lebih dari 0 dan stok tidak boleh negatif.';
    }

    // Validasi gambar jika pengguna memilih file
    if (
        $error === '' &&
        isset($_FILES['gambar']) &&
        $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['gambar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Gambar gagal diunggah. Silakan coba kembali.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $error = 'Ukuran gambar maksimal 5 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);

            $tipeDiizinkan = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
            ];

            if (!isset($tipeDiizinkan[$mime])) {
                $error = 'Format gambar harus JPG, PNG, WEBP, atau GIF.';
            } else {
                $folderUpload = __DIR__ . '/../uploads/produk/';

                if (
                    !is_dir($folderUpload) &&
                    !mkdir($folderUpload, 0755, true) &&
                    !is_dir($folderUpload)
                ) {
                    $error = 'Folder penyimpanan gambar gagal dibuat.';
                } elseif (!is_writable($folderUpload)) {
                    $error = 'Folder penyimpanan gambar tidak dapat ditulisi.';
                } else {
                    $namaGambar = bin2hex(random_bytes(16))
                        . '.' . $tipeDiizinkan[$mime];

                    $fileBaru = $folderUpload . $namaGambar;

                    if (!move_uploaded_file($file['tmp_name'], $fileBaru)) {
                        $error = 'Gambar gagal disimpan ke folder uploads/produk.';
                        $namaGambar = null;
                        $fileBaru = null;
                    }
                }
            }
        }
    }

    // Simpan produk ke database
    if ($error === '') {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO produk
                    (nama, kategori, deskripsi, harga, stok, gambar)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $nama,
                $kategori,
                $deskripsi,
                $harga,
                $stok,
                $namaGambar
            ]);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Produk berhasil ditambahkan.'
            ];

            header('Location: produk.php');
            exit;
        } catch (PDOException $e) {
            // Hapus file baru jika penyimpanan database gagal
            if ($fileBaru !== null && is_file($fileBaru)) {
                unlink($fileBaru);
            }

            $error = 'Produk gagal disimpan. Periksa kembali database.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Tambah Produk Bunga</h1>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nama Produk</label>
        <input
            type="text"
            name="nama_produk"
            required
            value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>"
        >

        <label>Kategori</label>
        <input
            type="text"
            name="kategori"
            required
            value="<?= htmlspecialchars($_POST['kategori'] ?? '') ?>"
        >

        <label>Harga (Rp)</label>
        <input
            type="number"
            name="harga"
            min="1"
            step="0.01"
            required
            value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>"
        >

        <label>Stok</label>
        <input
            type="number"
            name="stok"
            min="0"
            step="1"
            required
            value="<?= htmlspecialchars($_POST['stok'] ?? '') ?>"
        >

        <label>Deskripsi</label>
        <textarea name="deskripsi" rows="4"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>

        <label>Gambar Produk (opsional, maksimal 5 MB)</label>
        <input
            type="file"
            name="gambar"
            accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
        >

        <button type="submit">Simpan Produk</button>
        <a href="produk.php">Batal</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
