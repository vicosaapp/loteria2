-- Execute este script no phpMyAdmin

CREATE DATABASE loteria;

USE loteria;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha VARCHAR(255),
    tipo ENUM('admin', 'revendedor', 'apostador') DEFAULT 'apostador',
    revendedor_id INT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE jogos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50),
    descricao TEXT,
    ativo BOOLEAN DEFAULT TRUE
);

CREATE TABLE apostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    jogo_id INT,
    numeros TEXT,
    comprovante VARCHAR(100),
    data_aposta DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (jogo_id) REFERENCES jogos(id)
);

CREATE TABLE resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jogo_id INT,
    numeros_sorteados TEXT,
    data_sorteio DATE,
    FOREIGN KEY (jogo_id) REFERENCES jogos(id)
);

-- Usuários de teste (senhas: 123456)
INSERT INTO usuarios (nome, email, senha, tipo) VALUES
('Admin', 'admin@admin.com', '$2y$10$KkRHIY1Dx.X9GZqB7WnS/eFzEeJlVpQZtNcOaI5rUwM6i8o7TmH/S', 'admin'),
('Revendedor', 'rev@rev.com', '$2y$10$KkRHIY1Dx.X9GZqB7WnS/eFzEeJlVpQZtNcOaI5rUwM6i8o7TmH/S', 'revendedor'),
('Apostador', 'user@user.com', '$2y$10$KkRHIY1Dx.X9GZqB7WnS/eFzEeJlVpQZtNcOaI5rUwM6i8o7TmH/S', 'apostador');