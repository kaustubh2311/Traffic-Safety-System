<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (current_citizen()) redirect('citizen/dashboard.php');
$pageTitle = 'Citizen Registration';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $error = 'Enter a valid full name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $error = 'Enter a valid email address.';
    elseif (mb_strlen($password) < 4) $error = 'Use a password with at least 4 characters.';
    elseif (!hash_equals($password, $confirm)) $error = 'Passwords do not match.';
    else {
        $check = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1'); $check->execute([$email]);
        if ($check->fetch()) { $error = 'An account already uses this email address.'; }
        else {
            $stmt = db()->prepare('INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'citizen']);
            $id = (int)db()->lastInsertId();
            audit_log('citizen.register', 'citizen', (string)$id, 'Citizen account created');
            flash('success', 'Registration successful. You can now sign in.');
            redirect('citizen/login.php');
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap"><section class="panel auth-panel"><span class="auth-mark">＋</span><span class="eyebrow">CITIZEN ACCOUNT</span><h1>Create an account</h1><p class="muted-text">Register to track your submissions and receive in-app updates.</p><?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?><form method="post" class="stack-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="form-field"><label for="full_name">Full name</label><input id="full_name" name="full_name" required maxlength="100" value="<?= e($_POST['full_name'] ?? '') ?>"></div><div class="form-field"><label for="email">Email address</label><input id="email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></div><div class="form-field"><label for="password">Password</label><input id="password" name="password" type="password" minlength="4" required autocomplete="new-password"></div><div class="form-field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" minlength="4" required autocomplete="new-password"></div><button class="btn btn-primary btn-wide" type="submit">Create account →</button></form><p class="auth-help">Already registered? <a href="<?= e(url('citizen/login.php')) ?>">Sign in</a></p></section></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
