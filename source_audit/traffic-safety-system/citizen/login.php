<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (current_citizen()) redirect('citizen/dashboard.php');
$pageTitle = 'Citizen Login';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $stmt = db()->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? AND role = 'citizen' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['citizen'] = ['id' => (int)$user['id'], 'full_name' => $user['full_name'], 'email' => $user['email'], 'role' => 'citizen'];
        audit_log('citizen.login', 'citizen', (string)$user['id'], 'Citizen signed in');
        redirect('citizen/dashboard.php');
    }
    $error = 'The email or password was not recognised.';
}
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap"><section class="panel auth-panel"><span class="auth-mark">T6</span><span class="eyebrow">CITIZEN PORTAL</span><h1>Sign in</h1><p class="muted-text">View your reports and account notifications.</p><?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-field"><label for="email">Email address</label><input id="email" name="email" type="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="form-field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div><button class="btn btn-primary btn-wide" type="submit">Sign in →</button></form><p class="auth-help">New citizen? <a href="<?= e(url('citizen_register.php')) ?>">Create an account</a></p></section></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
