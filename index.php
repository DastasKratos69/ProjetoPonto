<?php
// index.php - Painel Principal do Sistema Escolar Digital

require_once __DIR__ . '/auth.php';
require_authentication();

$usuario = get_logged_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Principal - Sistema Escolar Digital</title>
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
                    <i data-lucide="layout-dashboard" style="width: 28px; height: 28px;"></i>
                    <h1>Painel de Controle</h1>
                </div>
            </div>

            <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
                Selecione uma das opções abaixo para gerenciar os registros do sistema escolar e da biblioteca.
            </p>

            <div class="grid-menu">
                <a href="alunos.php" class="menu-card">
                    <div class="menu-card-header">
                        <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                        <span>Alunos</span>
                    </div>
                    <p class="menu-card-desc">Cadastrar e visualizar a lista de alunos matriculados.</p>
                </a>

                <a href="usuarios.php" class="menu-card">
                    <div class="menu-card-header">
                        <i data-lucide="user-check" style="width: 24px; height: 24px;"></i>
                        <span>Usuários do Sistema</span>
                    </div>
                    <p class="menu-card-desc">Gerenciar contas de acesso e operadores do portal.</p>
                </a>

                <a href="autores.php" class="menu-card">
                    <div class="menu-card-header">
                        <i data-lucide="pen-tool" style="width: 24px; height: 24px;"></i>
                        <span>Autores</span>
                    </div>
                    <p class="menu-card-desc">Cadastrar autores de obras para o acervo da biblioteca.</p>
                </a>

                <a href="editoras.php" class="menu-card">
                    <div class="menu-card-header">
                        <i data-lucide="building" style="width: 24px; height: 24px;"></i>
                        <span>Editoras</span>
                    </div>
                    <p class="menu-card-desc">Gerenciar editoras parceiras e publicações.</p>
                </a>

                <a href="livros.php" class="menu-card">
                    <div class="menu-card-header">
                        <i data-lucide="book-open" style="width: 24px; height: 24px;"></i>
                        <span>Livros</span>
                    </div>
                    <p class="menu-card-desc">Cadastrar novas obras com ISBN, estoque e relacionamentos.</p>
                </a>

                <a href="relatorio.php" class="menu-card">
                    <div class="menu-card-header">
                        <i data-lucide="file-text" style="width: 24px; height: 24px;"></i>
                        <span>Relatório de Livros</span>
                    </div>
                    <p class="menu-card-desc">Consultar relatório integrado de acervo, autor e editora.</p>
                </a>
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
