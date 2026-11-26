<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Initial Administrator Setup';
$adminExists = (int)db()->query("SELECT COUNT(*) FROM users WHERE role IN ('admin','officer')")->fetchColumn() > 0;
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$adminExists) {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter a name up to 100 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 10) $errors[] = 'Choose a password of at least 10 characters.';
    if ($password !== $confirm) $errors[] = 'The passwords do not match.';
    if (!$errors) {
        try {
            $statement = db()->prepare("INSERT INTO users (full_name, email, password_hash, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            flash('success', 'Administrator account created. Sign in to continue. For safety, remove setup_admin.php after setup.');
            redirect('admin/login.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') $errors[] = 'That email address is already registered.';
            else { error_log('Admin setup failed: ' . $exception->getMessage()); $errors[] = 'Account creation failed. Check database setup and try again.'; }
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap"><section class="panel auth-panel"><span class="eyebrow">ONE-TIME SETUP</span><h1>Create the first administrator</h1><p class="muted-text">This page only works when the database has no authority accounts. Choose a strong password and do not share it.</p>
<?php if ($adminExists): ?><div class="alert alert-info">An administrator or officer account already exists. This setup page is disabled. Go to the authority login page. Delete <code>setup_admin.php</code> from the project folder after setup.</div><a class="btn btn-primary btn-wide" href="<?= e(url('admin/login.php')) ?>">Go to Authority Login</a>
<?php else: ?>
<?php if ($errors): ?><div class="alert alert-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-field"><label for="full_name">Full name *</label><input id="full_name" name="full_name" required maxlength="100" value="<?= e($_POST['full_name'] ?? '') ?>"></div><div class="form-field"><label for="email">Administrator email *</label><input id="email" type="email" name="email" required maxlength="190" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="form-field"><label for="password">Password (10+ characters) *</label><input id="password" type="password" name="password" required minlength="10" autocomplete="new-password"></div><div class="form-field"><label for="confirm_password">Confirm password *</label><input id="confirm_password" type="password" name="confirm_password" required minlength="10" autocomplete="new-password"></div><button class="btn btn-primary btn-wide" type="submit">Create Administrator</button></form>
<?php endif; ?></section></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
