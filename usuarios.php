<?php
// usuarios.php - Gestão de Usuários do Sistema

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

$usuario = get_logged_user();
$pdo = get_db_connection();

$msg = $_GET['msg'] ?? '';
$tipo_msg = $_GET['tipo'] ?? 'success';

// Buscar usuários
$stmt = $pdo->query("SELECT id, nome, email, criado_em FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários - Sistema Escolar Digital</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

    <header>
        <div class="header-container">
            <a href="index.php" class="logo-area">
                <i data-lucide="graduation-cap" style="width: 28px; height: 28px;"></i>
                <span>Sistema Escolar Digital</span>
            </a>
            <div class="user-nav">
                <div class="user-info">
                    <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                    <span><?php echo htmlspecialchars($usuario['nome']); ?></span>
                </div>
                <a href="logout.php" class="btn btn-outline" style="color: #FFFFFF; border-color: rgba(255,255,255,0.4);">
                    <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                    Sair
                </a>
            </div>
        </div>
    </header>

    <main class="main-wrapper">
        <div class="card">
            <div class="page-header">
                <div class="page-title">
                    <i data-lucide="user-check" style="width: 28px; height: 28px;"></i>
                    <h1>Gestão de Usuários</h1>
                </div>
                <a href="index.php" class="btn btn-outline">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                    Voltar ao Início
                </a>
            </div>

            <?php if (!empty($msg)): ?>
                <div class="alert-box <?php echo $tipo_msg === 'success' ? 'alert-success' : 'alert-danger'; ?>">
                    <i data-lucide="<?php echo $tipo_msg === 'success' ? 'check-circle' : 'alert-triangle'; ?>" style="width: 20px; height: 20px;"></i>
                    <span><?php echo htmlspecialchars($msg); ?></span>
                </div>
            <?php endif; ?>

            <!-- Formulário de Cadastro de Usuário -->
            <form action="salvar_usuario.php" method="POST" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-primary);">Cadastrar Novo Usuário</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" placeholder="Ex: Maria Souza" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" placeholder="maria@escola.com" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" placeholder="Digite a senha" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Salvar Usuário
                </button>
            </form>

            <!-- Tabela de Usuários Cadastrados -->
            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-text);">Usuários Cadastrados</h2>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Data de Cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($usuarios) > 0): ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['id']); ?></td>
                                    <td><?php echo htmlspecialchars($u['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['criado_em']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--color-text-muted);">
                                    Nenhum usuário cadastrado até o momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema Escolar Digital — Todos os direitos reservados.</p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
