<?php
// livros.php - Gestão de Livros

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

$usuario = get_logged_user();
$pdo = get_db_connection();

$msg = $_GET['msg'] ?? '';
$tipo_msg = $_GET['tipo'] ?? 'success';

// Carregar autores para dropdown
$stmtAutores = $pdo->query("SELECT id_autor, nome FROM autor ORDER BY nome ASC");
$autores = $stmtAutores->fetchAll();

// Carregar editoras para dropdown
$stmtEditoras = $pdo->query("SELECT id_editora, nome FROM editora ORDER BY nome ASC");
$editoras = $stmtEditoras->fetchAll();

// Carregar livros cadastrados
$stmtLivros = $pdo->query("SELECT
    l.id_livro, l.titulo, l.isbn, l.ano_publicacao, l.categoria, l.quantidade_estoque,
    a.nome AS autor_nome,
    e.nome AS editora_nome
    FROM livros l
    LEFT JOIN autor a ON l.id_autor = a.id_autor
    LEFT JOIN editora e ON l.id_editora = e.id_editora
    ORDER BY l.id_livro DESC");
$livros = $stmtLivros->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Livros - Sistema Escolar Digital</title>
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
                    <i data-lucide="book-open" style="width: 28px; height: 28px;"></i>
                    <h1>Gestão de Livros</h1>
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

            <!-- Formulário de Cadastro de Livro -->
            <form action="salvar_livro.php" method="POST" style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-primary);">Cadastrar Novo Livro</h2>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="titulo">Título do Livro</label>
                        <input type="text" id="titulo" name="titulo" placeholder="Ex: Dom Casmurro" required>
                    </div>

                    <div class="form-group">
                        <label for="isbn">ISBN</label>
                        <input type="text" id="isbn" name="isbn" placeholder="Ex: 978-85-359-0277-7" required>
                    </div>

                    <div class="form-group">
                        <label for="id_autor">Autor</label>
                        <select id="id_autor" name="id_autor" required>
                            <option value="">Selecione um autor</option>
                            <?php foreach ($autores as $a): ?>
                                <option value="<?php echo $a['id_autor']; ?>"><?php echo htmlspecialchars($a['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_editora">Editora</label>
                        <select id="id_editora" name="id_editora" required>
                            <option value="">Selecione uma editora</option>
                            <?php foreach ($editoras as $e): ?>
                                <option value="<?php echo $e['id_editora']; ?>"><?php echo htmlspecialchars($e['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ano_publicacao">Ano de Publicação</label>
                        <input type="number" id="ano_publicacao" name="ano_publicacao" min="1000" max="2100" placeholder="Ex: 1899" required>
                    </div>

                    <div class="form-group">
                        <label for="categoria">Categoria / Gênero</label>
                        <input type="text" id="categoria" name="categoria" placeholder="Ex: Romance" required>
                    </div>

                    <div class="form-group">
                        <label for="quantidade_estoque">Estoque Inicial</label>
                        <input type="number" id="quantidade_estoque" name="quantidade_estoque" min="0" placeholder="Ex: 10" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i data-lucide="save" style="width: 18px; height: 18px;"></i>
                    Salvar Livro
                </button>
            </form>

            <!-- Tabela de Livros Cadastrados -->
            <h2 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--color-text);">Livros Cadastrados</h2>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Livro</th>
                            <th>Título</th>
                            <th>ISBN</th>
                            <th>Autor</th>
                            <th>Editora</th>
                            <th>Ano</th>
                            <th>Categoria</th>
                            <th>Estoque</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($livros) > 0): ?>
                            <?php foreach ($livros as $livro): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($livro['id_livro']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($livro['titulo']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($livro['isbn']); ?></td>
                                    <td><?php echo htmlspecialchars($livro['autor_nome'] ?? 'Não informado'); ?></td>
                                    <td><?php echo htmlspecialchars($livro['editora_nome'] ?? 'Não informada'); ?></td>
                                    <td><?php echo htmlspecialchars($livro['ano_publicacao']); ?></td>
                                    <td><?php echo htmlspecialchars($livro['categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($livro['quantidade_estoque']); ?> un.</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--color-text-muted);">
                                    Nenhum livro cadastrado até o momento.
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
