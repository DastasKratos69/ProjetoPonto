<?php
// config/db.php - Conexão PDO segura com o banco de dados e inicialização de schema

function get_db_connection() {
    static $pdo = null;

    if ($pdo === null) {
        $dbPath = __DIR__ . '/../data/escola.sqlite';
        $dataDir = __DIR__ . '/../data';

        if (!file_exists($dataDir)) {
            mkdir($dataDir, 0777, true);
        }

        try {
            $pdo = new PDO("sqlite:" . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Inicializar tabelas caso não existam
            init_db_schema($pdo);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}

function init_db_schema(PDO $pdo) {
    // Tabela de Alunos
    $pdo->exec("CREATE TABLE IF NOT EXISTS alunos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        idade INTEGER NOT NULL,
        curso TEXT NOT NULL
    )");

    // Tabela de Usuários
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        senha TEXT NOT NULL,
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Tabela de Autores
    $pdo->exec("CREATE TABLE IF NOT EXISTS autor (
        id_autor INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        cpf TEXT NOT NULL,
        data_nascimento DATE NOT NULL
    )");

    // Tabela de Editoras
    $pdo->exec("CREATE TABLE IF NOT EXISTS editora (
        id_editora INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        cidade TEXT NOT NULL,
        uf TEXT NOT NULL
    )");

    // Tabela de Livros
    $pdo->exec("CREATE TABLE IF NOT EXISTS livros (
        id_livro INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        isbn TEXT NOT NULL,
        id_autor INTEGER NOT NULL,
        id_editora INTEGER NOT NULL,
        ano_publicacao INTEGER NOT NULL,
        categoria TEXT NOT NULL,
        quantidade_estoque INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (id_autor) REFERENCES autor(id_autor) ON DELETE CASCADE,
        FOREIGN KEY (id_editora) REFERENCES editora(id_editora) ON DELETE CASCADE
    )");

    // Garante que exista um usuário padrão se a tabela estiver vazia
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    if ($stmt->fetchColumn() == 0) {
        $defaultPasswordHash = password_hash('123456', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $insertAdmin->execute(['Administrador', 'admin@escola.com', $defaultPasswordHash]);
    }
}
