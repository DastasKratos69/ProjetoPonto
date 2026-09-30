-- ============================================================
-- BANCO DE DADOS: escola
-- SGBD: MySQL / MariaDB / PostgreSQL / SQLite
-- Sistema Escolar e de Biblioteca Digital
-- ============================================================

CREATE DATABASE IF NOT EXISTS escola CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE escola;

-- ------------------------------------------------------------
-- 1. TABELA DE ALUNOS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS alunos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  idade INT NOT NULL,
  curso VARCHAR(100) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. TABELA DE USUÁRIOS (SISTEMA)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  senha VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. TABELA DE AUTORES DE LIVROS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS autor (
  id_autor INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cpf VARCHAR(14) NOT NULL,
  data_nascimento DATE NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. TABELA DE EDITORAS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS editora (
  id_editora INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  cidade VARCHAR(100) NOT NULL,
  uf CHAR(2) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 5. TABELA DE LIVROS
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS livros (
  id_livro INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  isbn VARCHAR(20) NOT NULL,
  id_autor INT NOT NULL,
  id_editora INT NOT NULL,
  ano_publicacao INT NOT NULL,
  categoria VARCHAR(50) NOT NULL,
  quantidade_estoque INT NOT NULL DEFAULT 0,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_livros_autor FOREIGN KEY (id_autor) REFERENCES autor(id_autor) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_livros_editora FOREIGN KEY (id_editora) REFERENCES editora(id_editora) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- INDÍCES DE DESEMPENHO
-- ------------------------------------------------------------
CREATE INDEX idx_alunos_nome ON alunos(nome);
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_autor_nome ON autor(nome);
CREATE INDEX idx_editora_nome ON editora(nome);
CREATE INDEX idx_livros_titulo ON livros(titulo);

-- ------------------------------------------------------------
-- DADOS INICIAIS / SEEDS
-- ------------------------------------------------------------

-- Usuário Administrador Padrão (Senha: 123456)
INSERT INTO usuarios (id, nome, email, senha) VALUES
(1, 'Administrador', 'admin@escola.com', '$2y$10$xQv1gS0tgtEb.qNqFbXKkefUboJ2yJcZgBAVFyWQewDH6t8fVhNU2')
ON DUPLICATE KEY UPDATE id=id;

-- Alunos Exemplo
INSERT INTO alunos (nome, idade, curso) VALUES
('Marcos Augusto Gomes', 18, 'Desenvolvimento de Sistemas'),
('Beatriz Silva', 20, 'Análise e Desenvolvimento de Sistemas'),
('Aline Santos', 22, 'Engenharia de Software');

-- Autores Exemplo
INSERT INTO autor (nome, cpf, data_nascimento) VALUES
('Machado de Assis', '111.222.333-44', '1839-06-21'),
('Clarice Lispector', '555.666.777-88', '1920-12-10');

-- Editoras Exemplo
INSERT INTO editora (nome, cidade, uf) VALUES
('Companhia das Letras', 'São Paulo', 'SP'),
('Editora Rocco', 'Rio de Janeiro', 'RJ');

-- Livros Exemplo
INSERT INTO livros (titulo, isbn, id_autor, id_editora, ano_publicacao, categoria, quantidade_estoque) VALUES
('Dom Casmurro', '978-8535902777', 1, 1, 1899, 'Romance', 15),
('A Hora da Estrela', '978-8532511010', 2, 2, 1977, 'Ficção', 10);
