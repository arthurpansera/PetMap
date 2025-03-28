CREATE DATABASE petmap;

USE petmap;

CREATE TABLE usuario (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_usuario)
);

CREATE TABLE contato (
    id_usuario INT NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(100) NOT NULL UNIQUE,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

CREATE TABLE moderador (
    id_moderador INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    PRIMARY KEY (id_moderador),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE ong (
    cnpj VARCHAR(18) NOT NULL UNIQUE,
    id_usuario INT NOT NULL,
    endereco_rua VARCHAR(100) NOT NULL,
    endereco_numero VARCHAR(10) NOT NULL,
    endereco_complemento VARCHAR(50),
    endereco_bairro VARCHAR(50) NOT NULL,
    endereco_cidade VARCHAR(50) NOT NULL,
    endereco_estado CHAR(2) NOT NULL,
    endereco_pais VARCHAR(20) NOT NULL,
    endereco_cep CHAR(8) NOT NULL,
    PRIMARY KEY (cnpj),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE cidadao (
    cpf VARCHAR(14) NOT NULL UNIQUE,
    id_usuario INT NOT NULL,
    data_nasc DATE NOT NULL,
    PRIMARY KEY (cpf),
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