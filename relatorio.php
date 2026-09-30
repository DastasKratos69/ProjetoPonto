<?php
// relatorio.php - Relatório Completo do Acervo da Biblioteca

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

$usuario = get_logged_user();
$pdo = get_db_connection();

// Buscar relatório completo com JOINs
$sql = "SELECT
            l.id_livro,
            l.titulo,
            l.isbn,
            l.ano_publicacao,
            l.categoria,
            l.quantidade_estoque,
            a.nome AS autor_nome,
            e.nome AS editora_nome
        FROM livros l
        INNER JOIN autor a ON l.id_autor = a.id_autor
        INNER JOIN editora e ON l.id_editora = e.id_editora
        ORDER BY l.titulo ASC";

$stmt = $pdo->query($sql);
$relatorio = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Livros - Sistema Escolar Digital</title>
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
                    <i data-lucide="file-text" style="width: 28px; height: 28px;"></i>
                    <h1>Relatório Integrado do Acervo</h1>
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="livros.php" class="btn btn-primary">
                        <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                        Novo Livro
                    </a>
                    <a href="index.php" class="btn btn-outline">
                        <i data-lucide="arrow-left" style="width: 16px; height: 16px;"></i>
                        Voltar
                    </a>
                </div>
            </div>

            <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
                Visão consolidada de livros, autores correspondentes, editoras publicadoras e saldos em estoque.
            </p>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Livro</th>
                            <th>Título</th>
                            <th>ISBN</th>
                            <th>Autor</th>
                            <th>Editora</th>
                            <th>Ano Publicação</th>
                            <th>Categoria</th>
                            <th>Estoque</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($relatorio) > 0): ?>
                            <?php foreach ($relatorio as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['id_livro']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['titulo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['isbn']); ?></td>
                                    <td><?php echo htmlspecialchars($item['autor_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($item['editora_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($item['ano_publicacao']); ?></td>
                                    <td><?php echo htmlspecialchars($item['categoria']); ?></td>
                                    <td>
                                        <span style="font-weight: 600; color: <?php echo $item['quantidade_estoque'] > 0 ? 'var(--color-success)' : 'var(--color-alert)'; ?>;">
                                            <?php echo htmlspecialchars($item['quantidade_estoque']); ?> un.
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--color-text-muted); padding: 2rem;">
                                    Nenhum livro registrado com autor e editora vinculados.
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
