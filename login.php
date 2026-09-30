<?php
// login.php - Página de autenticação do sistema

require_once __DIR__ . '/config/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($email) && !empty($senha)) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];

                header("Location: index.php");
                exit();
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
        } catch (Exception $e) {
            $erro = "Erro de autenticação no servidor.";
        }
    } else {
        $erro = "Preencha todos os campos do formulário.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal Escolar</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="login-body">

    <div class="login-card">
        <div class="login-header">
            <i data-lucide="graduation-cap" style="width: 48px; height: 48px; color: var(--color-primary);"></i>
            <h1 class="login-title">Portal Escolar</h1>
            <p class="login-subtitle">Acesse o sistema com suas credenciais</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert-box alert-danger">
                <i data-lucide="alert-circle" style="width: 20px; height: 20px;"></i>
                <span><?php echo htmlspecialchars($erro); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="seu.email@escola.com" required>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
                Entrar no Sistema
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
