-- ===== ZELALAR - BANCO DE DADOS =====
-- Sistema de Marketplace de Profissionais

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS zelalar_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zelalar_db;

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE,
    descricao TEXT,
    icone VARCHAR(100),
    ativa BOOLEAN DEFAULT TRUE,
    ordem INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de profissionais
CREATE TABLE IF NOT EXISTS profissionais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL UNIQUE,
    categoria VARCHAR(100) NOT NULL,
    descricao TEXT,
    foto VARCHAR(255),
    endereco VARCHAR(200),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    avaliacao DECIMAL(3,2) DEFAULT 0.00,
    total_avaliacoes INT DEFAULT 0,
    disponivel BOOLEAN DEFAULT TRUE,
    senha VARCHAR(255),
    email VARCHAR(100),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de avaliações
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profissional_id INT NOT NULL,
    cliente_nome VARCHAR(100),
    cliente_telefone VARCHAR(20),
    nota INT NOT NULL CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'aprovada', 'rejeitada') DEFAULT 'pendente',
    FOREIGN KEY (profissional_id) REFERENCES profissionais(id) ON DELETE CASCADE
);

-- Inserir categorias padrão
INSERT INTO categorias (nome, descricao, icone, ordem) VALUES
('CFTV', 'Sistemas de Câmeras e Segurança', 'fas fa-video', 1),
('Pedreiro', 'Construção e Reformas', 'fas fa-hammer', 2),
('Pintor', 'Pintura Residencial e Comercial', 'fas fa-paint-roller', 3),
('Encanador', 'Instalações Hidráulicas', 'fas fa-wrench', 4),
('Eletricista', 'Instalações Elétricas', 'fas fa-bolt', 5),
('Jardineiro', 'Jardinagem e Paisagismo', 'fas fa-seedling', 6),
('Limpeza', 'Serviços de Limpeza', 'fas fa-broom', 7),
('Manutenção', 'Manutenção Geral', 'fas fa-tools', 8);

-- Inserir profissionais de exemplo
INSERT INTO profissionais (nome, telefone, categoria, descricao, endereco, cidade, estado, cep, senha, email) VALUES
('João Silva', '+5511999999999', 'Pedreiro', 'Pedreiro experiente com mais de 15 anos de experiência em construções e reformas. Especializado em alvenaria, acabamentos e pequenas reformas.', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'joao@email.com'),
('Maria Santos', '+5511888888888', 'Pintor', 'Pintora profissional com vasta experiência em pintura residencial e comercial. Trabalho limpo e organizado.', 'Av. Paulista, 456', 'São Paulo', 'SP', '01310-100', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maria@email.com'),
('Carlos Oliveira', '+5511777777777', 'Encanador', 'Encanador especializado em instalações hidráulicas, reparos e manutenção. Atendo emergências 24h.', 'Rua Augusta, 789', 'São Paulo', 'SP', '01212-000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'carlos@email.com');

-- Inserir avaliações de exemplo
INSERT INTO avaliacoes (profissional_id, cliente_nome, cliente_telefone, nota, comentario, status) VALUES
(1, 'Ana Costa', '+5511666666666', 5, 'Excelente trabalho! Muito profissional e pontual.', 'aprovada'),
(1, 'Pedro Lima', '+5511555555555', 4, 'Bom trabalho, recomendo.', 'aprovada'),
(2, 'Lucia Ferreira', '+5511444444444', 5, 'Pintura perfeita, muito caprichosa.', 'aprovada'),
(3, 'Roberto Alves', '+5511333333333', 5, 'Resolveu meu problema rapidamente. Muito bom!', 'aprovada');

-- Atualizar estatísticas dos profissionais
UPDATE profissionais SET 
    media_avaliacao = (SELECT AVG(nota) FROM avaliacoes WHERE profissional_id = id AND status = 'aprovada'),
    total_avaliacoes = (SELECT COUNT(*) FROM avaliacoes WHERE profissional_id = id AND status = 'aprovada')
WHERE id IN (1, 2, 3);

-- Criar índices para melhor performance
CREATE INDEX idx_profissionais_categoria ON profissionais(categoria);
CREATE INDEX idx_profissionais_cidade ON profissionais(cidade);
CREATE INDEX idx_profissionais_disponivel ON profissionais(disponivel);
CREATE INDEX idx_avaliacoes_profissional ON avaliacoes(profissional_id);
CREATE INDEX idx_avaliacoes_status ON avaliacoes(status);
CREATE INDEX idx_avaliacoes_data ON avaliacoes(data_avaliacao);
