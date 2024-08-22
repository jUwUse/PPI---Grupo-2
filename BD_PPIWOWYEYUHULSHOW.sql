/*
-- O código abaixo permite que o user26 tenha permissão para acessar os recursos necessários.
SELECT host FROM mysql.user WHERE User = 'root';

GRANT ALL PRIVILEGES ON *.* TO 'user26'@'%';
FLUSH PRIVILEGES;
*/ 

CREATE DATABASE if not exists BD_PPIWOW;

USE BD_PPIWOW;

-- DROP table PROFESSOR;
-- DROP table SETORUSUARIO;
-- DROP table USUARIO;
-- DROP table USUARIO_TIPO;

CREATE TABLE if not exists USUARIO (
	IDUSER INT NOT NULL AUTO_INCREMENT UNIQUE,
    NOME VARCHAR(100),
    SENHA VARCHAR(255),
    EMAIL varchar(255),
    TIPO VARCHAR(30),
    
    PRIMARY KEY (IDUSER)
);

CREATE TABLE if not exists PROFESSOR (
	IDPROF INT,
    SIAPE INT,
    
    FOREIGN KEY (IDPROF) REFERENCES USUARIO(IDUSER)
);

SELECT * FROM USUARIO; 
SELECT * FROM PROFESSOR; 

CREATE TABLE if not exists SETORUSUARIO (
	IDSETORUSUARIO INT,
    ATIVIDADES VARCHAR(300), -- Isso é temporário
    
    FOREIGN KEY(IDSETORUSUARIO) REFERENCES USUARIO(IDUSER)
);

SELECT * FROM SETORUSUARIO; 
