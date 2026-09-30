<?php
// salvar_aluno.php - Processamento seguro de cadastro de alunos via PDO

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $idade = filter_var($_POST['idade'] ?? 0, FILTER_VALIDATE_INT);
    $curso = trim($_POST['curso'] ?? '');

    if (!empty($nome) && $idade !== false && $idade > 0 && !empty($curso)) {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("INSERT INTO alunos (nome, idade, curso) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $idade, $curso]);

            $msg = "Aluno '$nome' cadastrado com sucesso!";
            header("Location: alunos.php?msg=" . urlencode($msg) . "&tipo=success");
            exit();
        } catch (PDOException $e) {
            $msg = "Erro ao cadastrar aluno no banco de dados.";
            header("Location: alunos.php?msg=" . urlencode($msg) . "&tipo=danger");
            exit();
        }
    } else {
        $msg = "Preencha todos os campos corretamente.";
        header("Location: alunos.php?msg=" . urlencode($msg) . "&tipo=danger");
        exit();
    }
} else {
    header("Location: alunos.php");
    exit();
}
