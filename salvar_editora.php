<?php
// salvar_editora.php - Processamento seguro de cadastro de editoras via PDO

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $uf = strtoupper(trim($_POST['uf'] ?? ''));

    if (!empty($nome) && !empty($cidade) && !empty($uf) && strlen($uf) === 2) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("INSERT INTO editora (nome, cidade, uf) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $cidade, $uf]);

            $msg = "Editora '$nome' cadastrada com sucesso!";
            header("Location: editoras.php?msg=" . urlencode($msg) . "&tipo=success");
            exit();
        } catch (PDOException $e) {
            $msg = "Erro ao cadastrar editora no banco de dados.";
            header("Location: editoras.php?msg=" . urlencode($msg) . "&tipo=danger");
            exit();
        }
    } else {
        $msg = "Preencha todos os campos da editora corretamente.";
        header("Location: editoras.php?msg=" . urlencode($msg) . "&tipo=danger");
        exit();
    }
} else {
    header("Location: editoras.php");
    exit();
}
