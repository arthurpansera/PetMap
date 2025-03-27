CREATE DATABASE petmap;
USE petmap;

CREATE TABLE usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefone VARCHAR(20),
    senha VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_usuario)
);

CREATE TABLE moderador (
    id_moderador INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    PRIMARY KEY (id_moderador),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE perfil (
    id_perfil INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    descricao TEXT,
    foto BLOB,
    PRIMARY KEY (id_perfil),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);