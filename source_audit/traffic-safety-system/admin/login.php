<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (current_admin()) redirect('admin/dashboard.php');
$pageTitle = 'Authority Login';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Enter your registered email and password.';
    } else {
        $statement = db()->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? AND role IN ('admin','officer') LIMIT 1");
        $statement->execute([$email]);
        $user = $statement->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => (int)$user['id'], 'full_name' => $user['full_name'], 'email' => $user['email'], 'role' => $user['role']];
            redirect('admin/dashboard.php');
        }
        $error = 'The email or password was not recognised.';
    }
}
require __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap"><section class="panel auth-panel"><span class="auth-mark">↗</span><span class="eyebrow">RESTRICTED ACCESS</span><h1>Authority sign in</h1><p class="muted-text">For authorised project reviewers and administrators only.</p>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-field"><label for="email">Email address</label><input id="email" name="email" type="email" required autocomplete="username" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="form-field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div><button type="submit" class="btn btn-primary btn-wide">Sign In →</button></form><p class="auth-help">First setup? After importing the database, open <a href="<?= e(url('setup_admin.php')) ?>">initial administrator setup</a>.</p></section></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
