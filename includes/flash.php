
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['flash'])):
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type = $flash['type'] ?? 'success';
    $message = $flash['message'] ?? '';
?>
    <div class="alert alert-<?= htmlspecialchars($type) ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>
