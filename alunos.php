<?php
// alunos.php - Cadastro e Listagem de Alunos

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

$usuario = get_logged_user();
$pdo = get_db_connection();

$msg = $_GET['msg'] ?? '';
$tipo_msg = $_GET['tipo'] ?? 'success';

// Buscar alunos
$stmt = $pdo->query("SELECT id, nome, idade, curso FROM alunos ORDER BY id DESC");
$alunos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Alunos - Sistema Escolar Digital</title>
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
                    <i data-lucide="users" style="width: 28px; height: 28px;"></i>
                    <h1>Gestão de Alunos</h1>
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

            <!-- Formulário de Cadastro de Aluno -->
            <form action="salvar_aluno.php" method="POST" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-primary);">Cadastrar Novo Aluno</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" placeholder="Ex: João da Silva" required>
                    </div>

                    <div class="form-group">
                        <label for="idade">Idade</label>
                        <input type="number" id="idade" name="idade" min="1" max="120" placeholder="Ex: 16" required>
                    </div>

                    <div class="form-group">
                        <label for="curso">Curso</label>
                        <input type="text" id="curso" name="curso" placeholder="Ex: Desenvolvimento de Sistemas" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Salvar Aluno
                </button>
            </form>

            <!-- Tabela de Alunos Cadastrados -->
            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-text);">Alunos Cadastrados</h2>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Idade</th>
                            <th>Curso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($alunos) > 0): ?>
                            <?php foreach ($alunos as $aluno): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($aluno['id']); ?></td>
                                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($aluno['idade']); ?> anos</td>
                                    <td><?php echo htmlspecialchars($aluno['curso']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--color-text-muted);">
                                    Nenhum aluno cadastrado até o momento.
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
