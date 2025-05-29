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
    endereco_cep CHAR(9) NOT NULL,
    PRIMARY KEY (cnpj),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE cidadao (
    cpf VARCHAR(14) NOT NULL UNIQUE,
    id_usuario INT NOT NULL,
    data_nasc DATE NOT NULL,
    endereco_rua VARCHAR(100) NOT NULL,
    endereco_numero VARCHAR(10) NOT NULL,
    endereco_complemento VARCHAR(50),
    endereco_bairro VARCHAR(50) NOT NULL,
    endereco_cidade VARCHAR(50) NOT NULL,
    endereco_estado CHAR(2) NOT NULL,
    endereco_pais VARCHAR(20) NOT NULL,
    endereco_cep CHAR(9) NOT NULL,
    PRIMARY KEY (cpf),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE perfil (
    id_perfil INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    descricao TEXT,
    foto VARCHAR(255),
    status_perfil ENUM('verificado', 'nao_verificado', 'banido') DEFAULT 'nao_verificado' NOT NULL,
    PRIMARY KEY (id_perfil),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE moderador_valida_perfil (
    id_moderador INT NOT NULL,
    id_perfil INT NOT NULL,
    descricao_validacao TEXT NOT NULL,
    data_validacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_moderador) REFERENCES moderador(id_moderador) ON DELETE CASCADE,
    FOREIGN KEY (id_perfil) REFERENCES perfil(id_perfil) ON DELETE CASCADE
);

CREATE TABLE publicacao (
    id_publicacao INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_publicacao ENUM('verificado', 'nao_verificado') DEFAULT 'nao_verificado' NOT NULL,
    tipo_publicacao ENUM('animal', 'resgate', 'informacao', 'outro') NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_impulsos INT DEFAULT 0,
    total_comentarios INT DEFAULT 0,
    endereco_rua VARCHAR(100),
    endereco_bairro VARCHAR(50),
    endereco_cidade VARCHAR(50),
    endereco_estado CHAR(2),
    PRIMARY KEY (id_publicacao),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE impulso_publicacao (
    id_usuario INT NOT NULL,
    id_publicacao INT NOT NULL,
    data_impulso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, id_publicacao),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_publicacao) REFERENCES publicacao(id_publicacao) ON DELETE CASCADE
);

CREATE TABLE moderador_valida_publicacao (
    id_moderador INT NOT NULL,
    id_publicacao INT NOT NULL,
    descricao_validacao TEXT NOT NULL,
    data_validacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_moderador) REFERENCES moderador(id_moderador) ON DELETE CASCADE,
    FOREIGN KEY (id_publicacao) REFERENCES publicacao(id_publicacao) ON DELETE CASCADE
);

CREATE TABLE comentario (
    id_comentario INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_publicacao INT DEFAULT NULL,
    conteudo TEXT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_comentario ENUM('verificado', 'nao_verificado') DEFAULT 'nao_verificado' NOT NULL,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_comentario),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_publicacao) REFERENCES publicacao(id_publicacao) ON DELETE CASCADE
);

CREATE TABLE moderador_valida_comentario (
    id_moderador INT NOT NULL,
    id_comentario INT NOT NULL,
    descricao_validacao TEXT NOT NULL,
    data_validacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_moderador) REFERENCES moderador(id_moderador) ON DELETE CASCADE,
    FOREIGN KEY (id_comentario) REFERENCES comentario(id_comentario) ON DELETE CASCADE
);

CREATE TABLE imagem (
    id_imagem INT NOT NULL AUTO_INCREMENT,
    id_publicacao INT,
    imagem_url VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_imagem),
    FOREIGN KEY (id_publicacao) REFERENCES publicacao(id_publicacao) ON DELETE CASCADE
);