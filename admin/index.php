
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Masukkan email dan password yang valid.';
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, nama, email, password
             FROM admins
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Email atau password tidak sesuai.';
    }
}

$pageTitle = 'Login Admin | Bloom & Petal';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
    <div class="container">
        <form method="POST" class="form-card">
            <span class="eyebrow">Administrator</span>
            <h1 style="margin:10px 0 20px;">Login Admin</h1>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </div>

            <button type="submit" class="btn">Masuk</button>
            <p style="margin-top:20px;">
                <a href="/bloom-petal/index.php">Kembali ke website</a>
            </p>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
