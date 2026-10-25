-- Script de criação do banco de dados do projeto

CREATE DATABASE IF NOT EXISTS ex004
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ex004;

CREATE TABLE IF NOT EXISTS usuarios (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome       VARCHAR(150) NOT NULL,
    email      VARCHAR(254) NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    tipo       ENUM('admin', 'comum') NOT NULL DEFAULT 'comum',
    foto       VARCHAR(255) NULL,
    criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feedbacks (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id INT UNSIGNED NOT NULL,
    mensagem   VARCHAR(1000) NOT NULL,
    nota       TINYINT UNSIGNED NOT NULL,
    criado_em  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT ck_feedbacks_nota CHECK (nota BETWEEN 0 AND 10),
    CONSTRAINT fk_feedbacks_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;