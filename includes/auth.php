<?php
/**
 * Arquivo para autenticação de usuários
 * Gerencia o login, logout e controle de acesso por tipo de usuário
 */

// Incluir conexão com o banco de dados
require_once 'db.php';

/**
 * Realiza a autenticação do usuário
 * @param string $username Nome de usuário
 * @param string $password Senha do usuário
 * @param string $tipo Tipo de usuário (admin, revendedor, usuario)
 * @return array Retorna array com informações do usuário ou false em caso de falha
 */
function authenticate_user($username, $password, $tipo) {
    // Escapar valores para evitar SQL Injection
    $username = escape_string($username);
    
    // Consulta SQL para verificar usuário
    $sql = "SELECT id, username, password, nome, email, tipo, status FROM usuarios WHERE username = ? AND tipo = ? AND status = 'ativo'";
    $result = prepared_query($sql, "ss", [$username, $tipo]);
    
    if (empty($result)) {
        return false;
    }
    
    $user = $result[0];
    
    // Verificar senha usando password_verify
    if (password_verify($password, $user['password'])) {
        // Armazenar informações na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['tipo'] = $user['tipo'];
        $_SESSION['logged_in'] = true;
        
        // Registrar último login
        $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
        prepared_execute($sql, "i", [$user['id']]);
        
        // Registrar atividade
        register_activity($user['id'], 'login', 'Usuário realizou login no sistema');
        
        return $user;
    }
    
    return false;
}

/**
 * Verifica se o usuário está autenticado com o tipo correto
 * @param string $required_type Tipo de usuário requerido para acessar a página
 * @return bool Retorna true se autenticado e com tipo correto, ou redireciona
 */
function verify_user_auth($required_type) {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // Não está logado, redirecionar para a página de login
        header("Location: ../index.php?error=auth");
        exit;
    }
    
    // Verificar se o tipo do usuário é o correto
    if ($_SESSION['tipo'] !== $required_type) {
        // Tipo incorreto, redirecionar para a página correta
        switch ($_SESSION['tipo']) {
            case 'admin':
                header("Location: ../admin/index.php");
                break;
            case 'revendedor':
                header("Location: ../revendedor/index.php");
                break;
            case 'usuario':
                header("Location: ../usuario/index.php");
                break;
            default:
                // Tipo desconhecido, fazer logout
                logout_user();
        }
        exit;
    }
    
    return true;
}

/**
 * Faz o logout do usuário
 */
function logout_user() {
    // Registrar atividade antes de fazer logout
    if (isset($_SESSION['user_id'])) {
        register_activity($_SESSION['user_id'], 'logout', 'Usuário realizou logout do sistema');
    }
    
    // Limpar todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir a sessão
    session_destroy();
    
    // Redirecionar para a página de login
    header("Location: ../index.php");
    exit;
}

/**
 * Registra atividade do usuário
 * @param int $user_id ID do usuário
 * @param string $action Ação realizada
 * @param string $description Descrição da atividade
 */
function register_activity($user_id, $action, $description) {
    $sql = "INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address, data_hora) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
    
    prepared_execute($sql, "isss", [$user_id, $action, $description, $ip]);
}

// Processar login ou logout se for uma requisição POST ou GET com parâmetro logout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    
    $user = authenticate_user($username, $password, $tipo);
    
    if ($user) {
        // Login bem-sucedido, redirecionar para a página apropriada
        switch ($tipo) {
            case 'admin':
                header("Location: ../admin/index.php");
                break;
            case 'revendedor':
                header("Location: ../revendedor/index.php");
                break;
            case 'usuario':
                header("Location: ../usuario/index.php");
                break;
            default:
                header("Location: ../index.php");
        }
        exit;
    } else {
        // Login falhou
        header("Location: ../index.php?error=1");
        exit;
    }
} elseif (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // Processar logout
    logout_user();
}
?> 