<?php
// autores.php - Gestão de Autores de Livros

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

$usuario = get_logged_user();
$pdo = get_db_connection();

$msg = $_GET['msg'] ?? '';
$tipo_msg = $_GET['tipo'] ?? 'success';

// Buscar autores
$stmt = $pdo->query("SELECT id_autor, nome, cpf, data_nascimento FROM autor ORDER BY nome ASC");
$autores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Autores - Sistema Escolar Digital</title>
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
                    <i data-lucide="pen-tool" style="width: 28px; height: 28px;"></i>
                    <h1>Gestão de Autores</h1>
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

            <!-- Formulário de Cadastro de Autor -->
            <form action="salvar_autor.php" method="POST" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-primary);">Cadastrar Novo Autor</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome do Autor</label>
                        <input type="text" id="nome" name="nome" placeholder="Ex: Machado de Assis" required>
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
                    </div>

                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Salvar Autor
                </button>
            </form>

            <!-- Tabela de Autores Cadastrados -->
            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-text);">Autores Cadastrados</h2>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Autor</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Data de Nascimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($autores) > 0): ?>
                            <?php foreach ($autores as $autor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($autor['id_autor']); ?></td>
                                    <td><?php echo htmlspecialchars($autor['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($autor['cpf']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($autor['data_nascimento']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--color-text-muted);">
                                    Nenhum autor cadastrado até o momento.
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
