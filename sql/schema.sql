-- Meu Dinheiro — Controle Financeiro para Estudantes
-- Importar no phpMyAdmin ou: mysql -u root -p < sql/schema.sql

CREATE DATABASE IF NOT EXISTS meu_dinheiro
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meu_dinheiro;

CREATE TABLE IF NOT EXISTS usuarios (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome        VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  senha_hash  VARCHAR(255) NOT NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
  id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome    VARCHAR(50) NOT NULL,
  icone   VARCHAR(16) NOT NULL,
  cor     CHAR(7) NOT NULL,
  tipo    ENUM('entrada','gasto') NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categoria (tipo, nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS movimentacoes (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id    INT UNSIGNED NOT NULL,
  categoria_id  INT UNSIGNED NOT NULL,
  tipo          ENUM('entrada','gasto') NOT NULL,
  descricao     VARCHAR(120) NOT NULL,
  valor         DECIMAL(10,2) NOT NULL,
  data          DATE NOT NULL,
  observacao    VARCHAR(255) NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_mov_usuario_data (usuario_id, data),
  KEY idx_mov_usuario_tipo (usuario_id, tipo),
  CONSTRAINT fk_mov_usuario   FOREIGN KEY (usuario_id)   REFERENCES usuarios(id)   ON DELETE CASCADE,
  CONSTRAINT fk_mov_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS metas (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id    INT UNSIGNED NOT NULL,
  nome          VARCHAR(100) NOT NULL,
  icone         VARCHAR(16) NOT NULL DEFAULT '🎯',
  valor_alvo    DECIMAL(10,2) NOT NULL,
  valor_atual   DECIMAL(10,2) NOT NULL DEFAULT 0,
  prazo         DATE NULL,
  concluida_em  DATETIME NULL,
  criado_em     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_metas_usuario (usuario_id),
  CONSTRAINT fk_metas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recuperacoes (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  usuario_id  INT UNSIGNED NOT NULL,
  token_hash  CHAR(64) NOT NULL,
  expira_em   DATETIME NOT NULL,
  usado_em    DATETIME NULL,
  criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rec_token (token_hash),
  KEY idx_rec_usuario (usuario_id),
  CONSTRAINT fk_rec_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  chave       VARCHAR(100) NOT NULL,
  criado_em   INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_rate_chave (chave, criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias (nome, icone, cor, tipo) VALUES
  ('Alimentação', '🍔', '#16a34a', 'gasto'),
  ('Transporte',  '🚌', '#2563eb', 'gasto'),
  ('Lazer',       '🎮', '#f59e0b', 'gasto'),
  ('Estudos',     '📚', '#7c3aed', 'gasto'),
  ('Roupas',      '👕', '#ec4899', 'gasto'),
  ('Saúde',       '💊', '#ef4444', 'gasto'),
  ('Assinaturas', '📱', '#06b6d4', 'gasto'),
  ('Moradia',     '🏠', '#0f766e', 'gasto'),
  ('Outros',      '📦', '#64748b', 'gasto'),
  ('Mesada',              '💵', '#16a34a', 'entrada'),
  ('Salário',             '💼', '#15803d', 'entrada'),
  ('Bolsa de estudos',    '🎓', '#7c3aed', 'entrada'),
  ('Estágio',             '🏢', '#2563eb', 'entrada'),
  ('Trabalho freelancer', '💻', '#0891b2', 'entrada'),
  ('Outros recebimentos', '💰', '#64748b', 'entrada');
