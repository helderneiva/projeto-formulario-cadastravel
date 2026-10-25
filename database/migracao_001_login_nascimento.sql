-- Migração 001: user id (login) e data de nascimento.
--
-- Use SOMENTE se o banco ex004 já existe com a tabela "usuarios" antiga.
-- Em instalações novas, basta rodar o schema.sql.
-- Execute uma única vez: rodar de novo causa erro de coluna duplicada.
--
-- Os usuários antigos ficam com login e data_nascimento NULL e continuam
-- entrando pelo e-mail.

USE ex004;

ALTER TABLE usuarios
    ADD COLUMN login VARCHAR(30) NULL AFTER nome,
    ADD COLUMN data_nascimento DATE NULL AFTER email,
    ADD UNIQUE KEY uq_usuarios_login (login);