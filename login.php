<?php
// Session mit eindeutigem Namen starten
session_name('admin_session');
session_start([
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);
require_once __DIR__ . '/csrf.php';

// Initiale Variablen
$error = '';
$version = file_exists(__DIR__ . '/vers.txt') ? trim(file_get_contents(__DIR__ . '/vers.txt')) : 'v?.?';

['id' => $csrf_id, 'token' => $csrf_token] = csrf_issue();

// Wenn ein Login-POST erfolgt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify_post()) {
        http_response_code(403);
        exit('CSRF verification failed');
    }

    require __DIR__ . '/db.php'; // Verbindung zur Datenbank

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Admin anhand der E-Mail finden
    $stmt = $pdo->prepare('
            SELECT *
            FROM admins
            WHERE email = ?
        ');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    // Zugehörige Rollen abrufen
    $rollenStmt = $pdo->prepare('
            SELECT r.id, r.bezeichnung
            FROM admin_rollen ar
            JOIN rollen r ON ar.rollen_id = r.id
            WHERE ar.admin_id = ?
        ');
    $rollenStmt->execute([$admin['id'] ?? 0]);
    $rollen = $rollenStmt->fetchAll(PDO::FETCH_ASSOC);

    // Passwort prüfen
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];

        $rollenNamen = array_column($rollen, 'bezeichnung');
        $rollenIds   = array_column($rollen, 'id');

        $_SESSION['rollen_namen'] = $rollenNamen;
        $_SESSION['rollen_ids']   = $rollenIds;
        $_SESSION['rolle_name']   = $rollenNamen[0] ?? '';
        $_SESSION['rolle']        = $rollenIds[0] ?? 0;
        header('Location: intro.php');
        exit;
    } else {
        $error = '❌ E-Mail oder Passwort ist falsch.';
    }
}
?>


<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="custom.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
  <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
      <div class="text-center mb-3">
        <img src="images/logo_lkvn.png" alt="Logo" width="100">
      </div>
      <h5 class="text-center mb-3">Login</h5>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" novalidate>
        <input type="hidden" name="csrf_id" value="<?= htmlspecialchars($csrf_id, ENT_QUOTES) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
        <div class="mb-3">
          <label for="email" class="form-label">E-Mail</label>
          <input type="email" class="form-control" name="email" id="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Passwort</label>
          <div class="input-group">
            <input type="password" class="form-control" name="password" id="password" required>
            <span class="input-group-text" id="togglePassword" style="cursor: pointer;"><i class="bi bi-eye"></i></span>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
      <small class="text-end d-block mt-3 text-muted text-center">Version <?= htmlspecialchars($version) ?> – © 2025 cw</small>
    </div>
  </div>

  <script>
    const toggle = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    toggle.addEventListener('click', () => {
      const type = password.type === 'password' ? 'text' : 'password';
      password.type = type;
      toggle.innerHTML = type === 'text' ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  </script>
</body>
</html>