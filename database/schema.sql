-- Script de criação do banco de dados do projeto

CREATE DATABASE IF NOT EXISTS ex004
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ex004;

-- "login" é o user id escolhido no cadastro (minúsculas, 3 a 30 caracteres).
-- As colunas "login" e "data_nascimento" aceitam NULL somente para que
-- usuários criados antes desta versão continuem existindo; o cadastro
-- novo sempre exige os dois campos (validação feita em src/Models/Usuario.php).
CREATE TABLE IF NOT EXISTS usuarios (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome            VARCHAR(150) NOT NULL,
    login           VARCHAR(30) NULL,
    email           VARCHAR(254) NOT NULL,
    data_nascimento DATE NULL,
    senha_hash      VARCHAR(255) NOT NULL,
    tipo            ENUM('admin', 'comum') NOT NULL DEFAULT 'comum',
    foto            VARCHAR(255) NULL,
    criado_em       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_login (login)
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