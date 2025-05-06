<?php
/**
 * Arquivo para conexão com o banco de dados
 * Gerencia a conexão com o banco de dados MySQL para o sistema de loteria
 */

// Configurações do banco de dados
$db_host = 'localhost';      // Host do banco de dados
$db_user = 'root';           // Usuário do banco de dados (altere conforme necessário)
$db_pass = '';               // Senha do banco de dados (altere conforme necessário)
$db_name = 'loteria';        // Nome do banco de dados

// Função para criar a conexão com o banco de dados
function connect_db() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    // Criar conexão
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Verificar conexão
    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }
    
    // Configurar charset para UTF-8
    $conn->set_charset("utf8");
    
    return $conn;
}

// Função para executar consultas SELECT
function query($sql) {
    $conn = connect_db();
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Erro na consulta: " . $conn->error);
    }
    
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    $conn->close();
    return $data;
}

// Função para executar consultas INSERT, UPDATE, DELETE
function execute($sql) {
    $conn = connect_db();
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Erro ao executar operação: " . $conn->error);
    }
    
    $affected_rows = $conn->affected_rows;
    $last_id = $conn->insert_id;
    
    $conn->close();
    
    return [
        'success' => true,
        'affected_rows' => $affected_rows,
        'last_id' => $last_id
    ];
}

// Função para escapar strings e prevenir SQL Injection
function escape_string($string) {
    $conn = connect_db();
    $escaped = $conn->real_escape_string($string);
    $conn->close();
    return $escaped;
}

// Função para executar consulta preparada (para maior segurança)
function prepared_query($sql, $types, $params) {
    $conn = connect_db();
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Erro ao preparar consulta: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $data = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    
    return $data;
}

// Função para executar consulta preparada sem retornar resultados (INSERT, UPDATE, DELETE)
function prepared_execute($sql, $types, $params) {
    $conn = connect_db();
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Erro ao preparar execução: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$params);
    $result = $stmt->execute();
    
    $affected_rows = $stmt->affected_rows;
    $last_id = $conn->insert_id;
    
    $stmt->close();
    $conn->close();
    
    return [
        'success' => $result,
        'affected_rows' => $affected_rows,
        'last_id' => $last_id
    ];
}

// Iniciar sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?> 