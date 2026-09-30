<?php
// salvar_usuario.php - Cadastro seguro de usuários no sistema

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config/db.php';
require_authentication();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $senha = trim($_POST['senha'] ?? '');
    $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');

    if (empty($nome) || !$email || empty($senha)) {
        $msg = "Preencha todos os campos com dados válidos.";
        header("Location: usuarios.php?msg=" . urlencode($msg) . "&tipo=danger");
        exit();
    }

    if ($senha !== $confirmar_senha) {
        $msg = "As senhas não coincidem.";
        header("Location: usuarios.php?msg=" . urlencode($msg) . "&tipo=danger");
        exit();
    }

    try {
        $pdo = get_db_connection();

        // Verificar se e-mail já existe
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetchColumn() > 0) {
            $msg = "O e-mail informado já está cadastrado.";
            header("Location: usuarios.php?msg=" . urlencode($msg) . "&tipo=danger");
            exit();
        }

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $senha_hash]);

        $msg = "Usuário '$nome' cadastrado com sucesso!";
        header("Location: usuarios.php?msg=" . urlencode($msg) . "&tipo=success");
        exit();
    } catch (PDOException $e) {
        $msg = "Erro ao cadastrar usuário no banco de dados.";
        header("Location: usuarios.php?msg=" . urlencode($msg) . "&tipo=danger");
        exit();
    }
} else {
    header("Location: usuarios.php");
    exit();
}
