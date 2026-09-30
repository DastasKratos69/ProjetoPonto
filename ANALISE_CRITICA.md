# Análise Crítica do Código Original (Etec1)

Este documento apresenta uma análise crítica e técnica detalhada do código-fonte do projeto original encontrado em `Etec1`, destacando as vulnerabilidades de segurança, falhas arquiteturais, problemas de UI/UX e bugs operacionais, acompanhados das melhorias implementadas na nova versão do sistema.

---

## 1. Vulnerabilidades Graves de Segurança

### 1.1. SQL Injection (Injeção de SQL)
* **Problema Encontrado:** Em múltiplos arquivos (`cadastrolivro.php`, `cadastroautor.php`, `cadastroeditora.php` e `salvar.php`), as entradas enviadas via formulário HTTP `POST` eram concatenadas diretamente na string da consulta SQL sem qualquer sanitização ou uso de Prepared Statements.
  ```php
  // Exemplo encontrado em cadastrolivro.php e salvar.php:
  $sql = "INSERT INTO livros (titulo, ISBN, id_autor, id_editora, ano_publicacao, categoria, quantidade_estoque)
          VALUES ('$titulo', '$isbn', $id_autor, $id_editora, $ano_publicacao, '$categoria', $quantidade_estoque)";
  ```
* **Impacto:** Permite que atacantes executem comandos SQL arbitrários, podendo deletar tabelas, extrair senhas ou adulterar informações de todo o banco de dados.
* **Solução na Nova Versão:** Uso exclusivo de **PDO (PHP Data Objects)** com **Prepared Statements e Binds parametrizados** em 100% das operações de leitura e escrita no banco de dados.

### 1.2. Exposição de Erros do Banco de Dados
* **Problema Encontrado:** Mensagens internas do SGBD (`$conn->error` ou `mysqli_error()`) eram exibidas diretamente para o usuário final em caixas de diálogo JavaScript (`alert`) e telas HTML.
* **Impacto:** Exposição da estrutura interna das tabelas, nomes de colunas e versão do SGBD para potenciais atacantes (Information Disclosure).
* **Solução na Nova Versão:** Tratamento de exceções com blocos `try-catch` em PDO, exibindo mensagens amigáveis ao usuário e registrando erros técnicos em logs.

### 1.3. Cross-Site Scripting (XSS)
* **Problema Encontrado:** Dados gravados no banco de dados eram renderizados nas tabelas HTML sem escapamento apropriado (`htmlspecialchars`).
* **Impacto:** Possibilidade de execução de scripts maliciosos em JavaScript no navegador de outros usuários ou administradores.
* **Solução na Nova Versão:** Aplicação rigorosa de `htmlspecialchars()` na exibição de todas as variáveis trazidas do banco de dados para o HTML.

---

## 2. Falhas Arquiteturais e Organização de Código

### 2.1. Duplicação de Conexões e Configurações
* **Problema Encontrado:** Quase todos os arquivos PHP recriavam manualmente a lógica de conexão com o banco de dados MySQL (`$servidor = "localhost"; ...`), em vez de incluir um arquivo de configuração centralizado e reutilizável.
* **Solução na Nova Versão:** Centralização em `config/db.php`, com uma classe/função utilitária singleton para gerenciar a conexão PDO com suporte automático a SQLite e MySQL.

### 2.2. Código Misturado e Respostas HTTP Corrompidas
* **Problema Encontrado:** Em `relatoriolv.php`, foram encontrados múltiplos blocos `<?php ... ?>` tentando enviar cabeçalhos JSON (`header('Content-Type: application/json')`) intercalados com tags HTML `<doctype html>`.
* **Impacto:** Erro de "Headers already sent", respostas JSON malformadas e impossibilidade de carregar o relatório de livros via Ajax/Fetch.
* **Solução na Nova Versão:** Separação clara das rotas de ação/processamento (`salvar_*.php`) e visualização (`*.php`), garantindo dados limpos em cada requisição.

### 2.3. Links de Navegação Quebrados
* **Problema Encontrado:** O código referenciava arquivos inexistentes como `cadastro_livro.html`, `cadastro_autor.html`, `cadastro_editora.html` e `processar_autor.php`.
* **Solução na Nova Versão:** Mapeamento consistente de URLs e formulários apontando para arquivos PHP válidos e existentes no projeto.

---

## 3. Interface e Experiência do Usuário (UI / UX)

### 3.1. Violação de Diretrizes Visuais e Uso de Emojis
* **Problema Encontrado:** O código original utilizava múltiplos emojis informais diretamente nos títulos, botões e tabelas (`🚀`, `🎓`, `✨`, `👨‍🎓`, `👤`).
* **Solução na Nova Versão:** Proibição total de emojis conforme as diretrizes do sistema. Utilização exclusiva da biblioteca oficial **Lucide Icons** via CDN SVG para representação visual dos elementos.

### 3.2. Estilização, Tipografia e Paleta de Cores
* **Problema Encontrado:** O CSS original apresentava seletores redundantes, fundos escuros despadronizados e falta de alinhamento visual moderno.
* **Solução na Nova Versão:**
  * **Estilo Visual:** Interface limpa, minimalista, com fundo branco (`#FFFFFF`) e cinza gelo suave (`#F8FAFC`).
  * **Tipografia:** Fonte **Inter** via Google Fonts.
  * **Paleta de Cores Institucional:**
    * Primária: Azul Institucional (`#1D4ED8`)
    * Sucesso/Confirmação: Verde Menta (`#10B981`)
    * Alerta/Exclusão: Vermelho Suave (`#EF4444`)
    * Texto: Cinza Chumbo (`#334155`)
  * **Otimização:** Layout totalmente fluido para telas médias e grandes (Tablets e Desktops com largura superior a 768px).

---

## 4. Resumo da Reestruturação na Nova Versão

1. **Segurança:** PDO com Prepared Statements, controle de sessão seguro com hash `PASSWORD_DEFAULT`, e proteção contra XSS.
2. **Modularidade:** Estrutura limpa dividida em `config/`, `assets/`, páginas de interface (`.php`) e rotas de salvamento/processamento (`salvar_*.php`).
3. **Padrão UI/UX:** Atendimento 100% rigoroso às diretrizes estéticas escolares (Lucide Icons, sem emojis, tipografia Inter, paleta oficial).
