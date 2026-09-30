
<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: produk.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Produk tidak ditemukan.'
    ];

    header('Location: produk.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_produk'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $harga = filter_var($_POST['harga'] ?? '', FILTER_VALIDATE_FLOAT);
    $stok = filter_var($_POST['stok'] ?? '', FILTER_VALIDATE_INT);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Pertahankan gambar lama jika tidak ada gambar baru
    $gambarUntukDB = $item['gambar'] ?? null;
    $fileBaru = null;
    $namaGambarBaru = null;

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

    // Periksa gambar baru jika dipilih
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
                    $namaGambarBaru = bin2hex(random_bytes(16))
                        . '.' . $tipeDiizinkan[$mime];

                    $fileBaru = $folderUpload . $namaGambarBaru;

                    if (move_uploaded_file($file['tmp_name'], $fileBaru)) {
                        $gambarUntukDB = $namaGambarBaru;
                    } else {
                        $error = 'Gambar gagal disimpan ke folder uploads/produk.';
                        $fileBaru = null;
                        $namaGambarBaru = null;
                    }
                }
            }
        }
    }

    // Perbarui data produk
    if ($error === '') {
        try {
            $stmt = $pdo->prepare(
                "UPDATE produk
                 SET nama = ?,
                     kategori = ?,
                     deskripsi = ?,
                     harga = ?,
                     stok = ?,
                     gambar = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $nama,
                $kategori,
                $deskripsi,
                $harga,
                $stok,
                $gambarUntukDB,
                $id
            ]);

            // Hapus gambar lama setelah pembaruan berhasil
            if (
                $namaGambarBaru !== null &&
                !empty($item['gambar'])
            ) {
                $gambarLama = basename($item['gambar']);
                $pathGambarLama = __DIR__ . '/../uploads/produk/' . $gambarLama;

                if (
                    $gambarLama !== $namaGambarBaru &&
                    is_file($pathGambarLama)
                ) {
                    unlink($pathGambarLama);
                }
            }

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Produk berhasil diperbarui.'
            ];

            header('Location: produk.php');
            exit;
        } catch (PDOException $e) {
            // Jangan biarkan file baru tertinggal jika database gagal
            if ($fileBaru !== null && is_file($fileBaru)) {
                unlink($fileBaru);
            }

            $error = 'Produk gagal diperbarui. Periksa kembali database.';
        }
    }

    // Pertahankan data yang sudah diisi jika validasi gagal
    $item['nama'] = $nama;
    $item['kategori'] = $kategori;
    $item['harga'] = $_POST['harga'] ?? '';
    $item['stok'] = $_POST['stok'] ?? '';
    $item['deskripsi'] = $deskripsi;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <h1>Edit Produk Bunga</h1>

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
            value="<?= htmlspecialchars($item['nama']) ?>"
        >

        <label>Kategori</label>
        <input
            type="text"
            name="kategori"
            required
            value="<?= htmlspecialchars($item['kategori']) ?>"
        >

        <label>Harga (Rp)</label>
        <input
            type="number"
            name="harga"
            min="1"
            step="0.01"
            required
            value="<?= htmlspecialchars((string) $item['harga']) ?>"
        >

        <label>Stok</label>
        <input
            type="number"
            name="stok"
            min="0"
            step="1"
            required
            value="<?= htmlspecialchars((string) $item['stok']) ?>"
        >

        <label>Deskripsi</label>
        <textarea name="deskripsi" rows="4"><?= htmlspecialchars($item['deskripsi'] ?? '') ?></textarea>

        <label>Gambar Produk Baru (opsional, maksimal 5 MB)</label>

        <?php if (!empty($item['gambar'])): ?>
            <div style="margin: 12px 0;">
                <p>Gambar saat ini:</p>
                <img
                    src="/bloom-petal/uploads/produk/<?= htmlspecialchars(basename($item['gambar'])) ?>"
                    alt="<?= htmlspecialchars($item['nama']) ?>"
                    style="width:180px;max-width:100%;height:auto;object-fit:cover;border-radius:10px;"
                >
            </div>
        <?php endif; ?>

        <input
            type="file"
            name="gambar"
            accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
        >

        <small>Kosongkan jika tidak ingin mengganti gambar.</small>

        <br><br>

        <button type="submit">Simpan Perubahan</button>
        <a href="produk.php">Batal</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
