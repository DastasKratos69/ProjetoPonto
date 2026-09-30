<?php
// salvar_livro.php - Processamento seguro de cadastro de livros via PDO

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $id_autor = filter_var($_POST['id_autor'] ?? 0, FILTER_VALIDATE_INT);
    $id_editora = filter_var($_POST['id_editora'] ?? 0, FILTER_VALIDATE_INT);
    $ano_publicacao = filter_var($_POST['ano_publicacao'] ?? 0, FILTER_VALIDATE_INT);
    $categoria = trim($_POST['categoria'] ?? '');
    $quantidade_estoque = filter_var($_POST['quantidade_estoque'] ?? 0, FILTER_VALIDATE_INT);

    if (
        !empty($titulo) &&
        !empty($isbn) &&
        $id_autor !== false && $id_autor > 0 &&
        $id_editora !== false && $id_editora > 0 &&
        $ano_publicacao !== false && $ano_publicacao > 0 &&
        !empty($categoria) &&
        $quantidade_estoque !== false && $quantidade_estoque >= 0
    ) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("INSERT INTO livros (titulo, isbn, id_autor, id_editora, ano_publicacao, categoria, quantidade_estoque) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $isbn, $id_autor, $id_editora, $ano_publicacao, $categoria, $quantidade_estoque]);

            $msg = "Livro '$titulo' cadastrado com sucesso!";
            header("Location: livros.php?msg=" . urlencode($msg) . "&tipo=success");
            exit();
        } catch (PDOException $e) {
            $msg = "Erro ao cadastrar livro no banco de dados.";
            header("Location: livros.php?msg=" . urlencode($msg) . "&tipo=danger");
            exit();
        }
    } else {
        $msg = "Preencha todos os campos do livro corretamente.";
        header("Location: livros.php?msg=" . urlencode($msg) . "&tipo=danger");
        exit();
    }
} else {
    header("Location: livros.php");
    exit();
}
