<?php
// reservaciones/admin/login.php
session_start();

// Simple hardcoded auth for demo (in production, use database with hashed passwords)
$ADMIN_USER = 'admin';
$ADMIN_PASS = password_hash('rosa2026', PASSWORD_BCRYPT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if ($user === $ADMIN_USER && password_verify($pass, $ADMIN_PASS)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
}

// If already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Rosa Mezcal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }

        .login-card {
            background: var(--surface-dark);
            padding: var(--spacing-xl);
            border-radius: var(--radius-lg);
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .form-group label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: var(--spacing-md);
            background: var(--surface-hover);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-family: var(--font-family);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .error {
            color: var(--error);
            padding: var(--spacing-sm);
            background: rgba(207, 102, 121, 0.1);
            border-radius: var(--radius-sm);
            margin-bottom: var(--spacing-md);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <h1>🔐 Admin Panel</h1>
                    <p style="color: var(--text-secondary);">Rosa Mezcal</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="username">Usuario</label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Iniciar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>