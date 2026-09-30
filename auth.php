<?php
// auth.php - Helper para controle de sessão e autenticação de usuários

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está autenticado. Caso contrário, redireciona para o login.
 */
function require_authentication() {
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Retorna os dados do usuário logado na sessão atual.
 */
function get_logged_user() {
    if (isset($_SESSION['usuario_id'])) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'] ?? 'Usuário',
            'email' => $_SESSION['usuario_email'] ?? ''
        ];
    }
    return null;
}
