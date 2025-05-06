-- Script SQL para criar o banco de dados e tabelas do sistema de loteria
-- Autor: Seu Nome
-- Versão: 1.0

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS loteria;
USE loteria;

-- Tabela de usuários (admin, revendedor e apostador)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    cpf VARCHAR(14) UNIQUE,
    telefone VARCHAR(15),
    endereco TEXT,
    cidade VARCHAR(50),
    estado CHAR(2),
    cep VARCHAR(10),
    tipo ENUM('admin', 'revendedor', 'usuario') NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_login DATETIME,
    status ENUM('ativo', 'inativo', 'bloqueado') DEFAULT 'ativo',
    criado_por INT,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabela de tipos de jogos
CREATE TABLE IF NOT EXISTS jogos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    preco_unitario DECIMAL(10, 2) NOT NULL,
    numeros_por_jogo INT NOT NULL,
    numero_minimo INT NOT NULL,
    numero_maximo INT NOT NULL,
    acertos_minimos INT NOT NULL,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
);

-- Tabela de sorteios
CREATE TABLE IF NOT EXISTS sorteios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jogo_id INT NOT NULL,
    numero_concurso INT NOT NULL,
    data_sorteio DATETIME NOT NULL,
    numeros_sorteados VARCHAR(100),
    premio_estimado DECIMAL(15, 2) NOT NULL,
    status ENUM('aguardando', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'aguardando',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jogo_id) REFERENCES jogos(id) ON DELETE CASCADE
);

-- Tabela de apostas
CREATE TABLE IF NOT EXISTS apostas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    revendedor_id INT NOT NULL,
    jogo_id INT NOT NULL,
    sorteio_id INT,
    numeros_escolhidos VARCHAR(100) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_aposta DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'processando', 'concluido', 'premiado', 'nao_premiado', 'cancelado') DEFAULT 'pendente',
    valor_premio DECIMAL(15, 2) DEFAULT 0,
    data_premio DATETIME,
    observacoes TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (revendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (jogo_id) REFERENCES jogos(id) ON DELETE CASCADE,
    FOREIGN KEY (sorteio_id) REFERENCES sorteios(id) ON DELETE SET NULL
);

-- Tabela de comissões para revendedores
CREATE TABLE IF NOT EXISTS comissoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    revendedor_id INT NOT NULL,
    aposta_id INT NOT NULL,
    comissao DECIMAL(10, 2) NOT NULL,
    percentual DECIMAL(5, 2) NOT NULL,
    data_calculo DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'pago', 'cancelado') DEFAULT 'pendente',
    data_pagamento DATETIME,
    FOREIGN KEY (revendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (aposta_id) REFERENCES apostas(id) ON DELETE CASCADE
);

-- Tabela de pagamentos de prêmios
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    aposta_id INT NOT NULL,
    usuario_id INT NOT NULL,
    valor DECIMAL(15, 2) NOT NULL,
    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_pagamento DATETIME,
    metodo_pagamento VARCHAR(50),
    dados_pagamento TEXT,
    status ENUM('solicitado', 'processando', 'pago', 'cancelado') DEFAULT 'solicitado',
    comprovante VARCHAR(255),
    observacoes TEXT,
    FOREIGN KEY (aposta_id) REFERENCES apostas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela para registro de logs/atividades
CREATE TABLE IF NOT EXISTS logs_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    acao VARCHAR(50) NOT NULL,
    descricao TEXT,
    ip_address VARCHAR(45),
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Inserir administrador padrão (senha: admin123)
INSERT INTO usuarios (username, password, nome, email, tipo, data_cadastro) 
VALUES ('admin', '$2y$10$H.ELSvs2bQVV9TVjy0YFR.fYK2Jy5aQ2UtNKlXQGvC0VlWfcO1rTe', 'Administrador', 'admin@loteria.com', 'admin', NOW());

-- Inserir jogos de exemplo
INSERT INTO jogos (nome, descricao, preco_unitario, numeros_por_jogo, numero_minimo, numero_maximo, acertos_minimos) VALUES 
('Mega Loteria', 'Escolha 6 números de 1 a 60', 4.50, 6, 1, 60, 4),
('Quina Plus', 'Escolha 5 números de 1 a 80', 2.00, 5, 1, 80, 3),
('Super 7', 'Escolha 7 números de 1 a 50', 3.50, 7, 1, 50, 5),
('Lotofácil', 'Escolha 15 números de 1 a 25', 2.50, 15, 1, 25, 11);

-- Inserir sorteio de exemplo
INSERT INTO sorteios (jogo_id, numero_concurso, data_sorteio, premio_estimado) VALUES 
(1, 1001, DATE_ADD(NOW(), INTERVAL 7 DAY), 1000000.00),
(2, 2001, DATE_ADD(NOW(), INTERVAL 5 DAY), 500000.00),
(3, 3001, DATE_ADD(NOW(), INTERVAL 10 DAY), 750000.00),
(4, 4001, DATE_ADD(NOW(), INTERVAL 3 DAY), 350000.00);

-- Criar usuário revendedor de exemplo (senha: senha123)
INSERT INTO usuarios (username, password, nome, email, cpf, telefone, tipo, data_cadastro, criado_por) 
VALUES ('revenda1', '$2y$10$mtH2Z0t5lCkBhKrbuZMvd.fezHHT3k/LZd0r0W3PYqaJ7/YFZ/YKO', 'João Revendedor', 'revenda1@email.com', '123.456.789-00', '(11) 98765-4321', 'revendedor', NOW(), 1);

-- Criar usuário apostador de exemplo (senha: senha123)
INSERT INTO usuarios (username, password, nome, email, cpf, telefone, tipo, data_cadastro, criado_por) 
VALUES ('apostador1', '$2y$10$mtH2Z0t5lCkBhKrbuZMvd.fezHHT3k/LZd0r0W3PYqaJ7/YFZ/YKO', 'Maria Apostadora', 'apostador1@email.com', '987.654.321-00', '(11) 91234-5678', 'usuario', NOW(), 2);

-- Criar apostas de exemplo
INSERT INTO apostas (usuario_id, revendedor_id, jogo_id, sorteio_id, numeros_escolhidos, valor) VALUES
(3, 2, 1, 1, '10-20-30-40-50-60', 4.50),
(3, 2, 2, 2, '5-15-25-35-45', 2.00),
(3, 2, 3, 3, '7-14-21-28-35-42-49', 3.50);

-- Registrar comissões para apostas de exemplo (10% de comissão)
INSERT INTO comissoes (revendedor_id, aposta_id, comissao, percentual) VALUES
(2, 1, 0.45, 10.00),
(2, 2, 0.20, 10.00),
(2, 3, 0.35, 10.00);

-- Registrar algumas atividades de log iniciais
INSERT INTO logs_atividades (usuario_id, acao, descricao, ip_address) VALUES
(1, 'instalacao', 'Sistema instalado com sucesso', '127.0.0.1'),
(1, 'cadastro', 'Revendedor cadastrado: João Revendedor', '127.0.0.1'),
(2, 'cadastro', 'Apostador cadastrado: Maria Apostadora', '127.0.0.1'),
(2, 'aposta', 'Aposta registrada para o jogo Mega Loteria', '127.0.0.1'); 