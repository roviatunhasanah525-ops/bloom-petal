
<?php
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: produk.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'ID produk tidak valid.'
    ];

    header('Location: produk.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM produk WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['flash'] = [
        'type' => 'success',
        'message' => 'Produk berhasil dihapus.'
    ];
} else {
    $_SESSION['flash'] = [
        'type' => 'error',
        'message' => 'Produk tidak ditemukan.'
    ];
}

header('Location: produk.php');
exit;
