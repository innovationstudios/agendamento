
-- Banco de dados: gestao_mensalidades
CREATE DATABASE IF NOT EXISTS `gestao_mensalidades`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `gestao_mensalidades`;

-- Tabela: usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `senha` varchar(255) NOT NULL,
    `ativo` tinyint(1) DEFAULT '1',
    `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: fornecedores
CREATE TABLE IF NOT EXISTS `fornecedores` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `descricao` text,
    `contato` varchar(100) DEFAULT NULL,
    `telefone` varchar(20) DEFAULT NULL,
    `email` varchar(100) DEFAULT NULL,
    `ativo` tinyint(1) DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: planos
CREATE TABLE IF NOT EXISTS `planos` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(100) NOT NULL,
    `descricao` text,
    `valor` decimal(10,2) NOT NULL,
    `ciclo` varchar(20) DEFAULT 'mensal',
    `id_fornecedor` int DEFAULT NULL,
    `ativo` tinyint(1) DEFAULT '1',
    PRIMARY KEY (`id`),
    KEY `id_fornecedor` (`id_fornecedor`),
    CONSTRAINT `planos_ibfk_1` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: clientes
CREATE TABLE IF NOT EXISTS `clientes` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nome` varchar(255) NOT NULL,
    `email` varchar(255),
    `telefone` varchar(20),
    `status` enum('ativo','inativo') DEFAULT 'ativo',
    `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: mensalidades
CREATE TABLE IF NOT EXISTS `mensalidades` (
    `id` int NOT NULL AUTO_INCREMENT,
    `id_usuario` int NOT NULL,
    `id_plano` int NOT NULL,
    `valor` decimal(10,2) NOT NULL,
    `data_vencimento` date NOT NULL,
    `data_pagamento` date DEFAULT NULL,
    `status` enum('pendente','vencido','pago') DEFAULT 'pendente',
    `observacao` text,
    PRIMARY KEY (`id`),
    KEY `id_usuario` (`id_usuario`),
    KEY `id_plano` (`id_plano`),
    CONSTRAINT `mensalidades_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
    CONSTRAINT `mensalidades_ibfk_2` FOREIGN KEY (`id_plano`) REFERENCES `planos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: mensagem_templates
CREATE TABLE IF NOT EXISTS `mensagem_templates` (
    `id` int NOT NULL AUTO_INCREMENT,
    `titulo` varchar(100) NOT NULL,
    `mensagem` text NOT NULL,
    `dias_antes` int DEFAULT '1',
    `ativo` tinyint(1) DEFAULT '1',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: atividades (logs)
CREATE TABLE IF NOT EXISTS `atividades` (
    `id` int NOT NULL AUTO_INCREMENT,
    `tipo` varchar(50) DEFAULT NULL,
    `descricao` text,
    `data` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela: configuracoes
CREATE TABLE IF NOT EXISTS `configuracoes` (
    `id` int NOT NULL AUTO_INCREMENT,
    `tipo` varchar(20) NOT NULL COMMENT 'email, whatsapp',
    `chave` varchar(50) NOT NULL,
    `valor` text,
    `instance_name` varchar(50) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `tipo` (`tipo`,`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
