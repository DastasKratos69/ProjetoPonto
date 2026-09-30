<?php
// salvar_autor.php - Processamento seguro de cadastro de autores via PDO

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $data_nascimento = trim($_POST['data_nascimento'] ?? '');

    if (!empty($nome) && !empty($cpf) && !empty($data_nascimento)) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("INSERT INTO autor (nome, cpf, data_nascimento) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $cpf, $data_nascimento]);

            $msg = "Autor '$nome' cadastrado com sucesso!";
            header("Location: autores.php?msg=" . urlencode($msg) . "&tipo=success");
            exit();
        } catch (PDOException $e) {
            $msg = "Erro ao cadastrar autor no banco de dados.";
            header("Location: autores.php?msg=" . urlencode($msg) . "&tipo=danger");
            exit();
        }
    } else {
        $msg = "Preencha todos os campos do autor.";
        header("Location: autores.php?msg=" . urlencode($msg) . "&tipo=danger");
        exit();
    }
} else {
    header("Location: autores.php");
    exit();
}
