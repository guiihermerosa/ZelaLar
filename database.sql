-- ===== ZELALAR - BANCO DE DADOS OTIMIZADO =====
-- PADRÃO 2024 - OTIMIZAÇÃO DE ÍNDICES, TIPOS E RELAÇÕES

CREATE DATABASE IF NOT EXISTS zelalar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zelalar_db;

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT DEFAULT NULL,
    icone VARCHAR(100) DEFAULT NULL,
    ativa BOOLEAN DEFAULT TRUE,
    ordem INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de admins
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de profissionais
CREATE TABLE IF NOT EXISTS profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL UNIQUE,
    categoria_id INT NOT NULL,
    descricao TEXT,
    foto VARCHAR(255),
    endereco VARCHAR(200),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    email VARCHAR(100),
    senha VARCHAR(255) NOT NULL,
    disponivel BOOLEAN DEFAULT TRUE,
    media_avaliacao DECIMAL(3,2) DEFAULT 0.00,
    total_avaliacoes INT DEFAULT 0,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
);
CREATE INDEX idx_profissionais_categoria_id ON profissionais(categoria_id);
CREATE INDEX idx_profissionais_disponivel ON profissionais(disponivel);

-- Tabela de avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profissional_id INT NOT NULL,
    cliente_id INT NULL,
    cliente_nome VARCHAR(100),
    cliente_telefone VARCHAR(20),
    nota INT NOT NULL CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    status ENUM('pendente', 'aprovada', 'reprovada') DEFAULT 'aprovada',
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);
CREATE INDEX idx_avaliacoes_profissional_id ON avaliacoes(profissional_id);
CREATE INDEX idx_avaliacoes_cliente_id ON avaliacoes(cliente_id);
CREATE INDEX idx_avaliacoes_status ON avaliacoes(status);
CREATE INDEX idx_avaliacoes_data ON avaliacoes(data_avaliacao);

-- Estatísticas automáticas: disparo por trigger após avaliação (pode migrar para código)
DELIMITER //
CREATE TRIGGER after_insert_avaliacao
AFTER INSERT ON avaliacoes
FOR EACH ROW
BEGIN
  IF NEW.status = 'aprovada' THEN
    UPDATE profissionais SET 
      media_avaliacao = (SELECT COALESCE(AVG(nota),0) FROM avaliacoes WHERE profissional_id = NEW.profissional_id AND status = 'aprovada'),
      total_avaliacoes = (SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = NEW.profissional_id AND status = 'aprovada')
    WHERE id = NEW.profissional_id;
  END IF;
END;//
DELIMITER ;

-- Dados de exemplo devem ser inseridos apenas em ambiente de desenvolvimento.