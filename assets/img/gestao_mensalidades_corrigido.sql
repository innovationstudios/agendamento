-- Banco de dados: gestao_mensalidades
CREATE DATABASE IF NOT EXISTS `gestao_mensalidades`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `gestao_mensalidades`;

-- Tabela: atividades
CREATE TABLE IF NOT EXISTS `atividades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) DEFAULT NULL,
  `descricao` text,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `atividades`;

-- Tabela: configuracoes
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(20) NOT NULL COMMENT 'email, whatsapp',
  `chave` varchar(50) NOT NULL,
  `valor` text,
  `instance_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipo` (`tipo`,`chave`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `configuracoes`;
INSERT INTO `configuracoes` (`id`, `tipo`, `chave`, `valor`, `instance_name`) VALUES
(4, 'whatsapp', 'api_key', 'B6D711FCDE4D4FD5936544120E713976', 'teste'),
(5, 'whatsapp', 'api_url', '177.153.58.252:8080', 'teste'),
(6, 'whatsapp', 'numero', 'Teste', 'teste');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `fornecedores`;
INSERT INTO `fornecedores` (`id`, `nome`, `descricao`, `contato`, `telefone`, `email`, `ativo`) VALUES
(1, 'teste', '123', '5551611', '5551611', 'teste@gmail.com', 1);

-- Tabela: mensagem_templates
CREATE TABLE IF NOT EXISTS `mensagem_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) NOT NULL,
  `mensagem` text NOT NULL,
  `dias_antes` int DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `mensagem_templates`;
INSERT INTO `mensagem_templates` (`id`, `titulo`, `mensagem`, `dias_antes`, `ativo`) VALUES
(1, 'TESTE', 'Olá {cliente_nome}, sua mensalidade do plano {plano_nome} venceu em {data_vencimento}. Valor: R$ {valor}', -31, 1);

-- Tabela: usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `usuarios`;
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `telefone`, `data_cadastro`, `ativo`) VALUES
(1, 'Usuário Teste', 'teste@teste.com', 'senha123', '51998584947', '2025-07-30 14:51:53', 1),
(2, 'terwrds', 'teste@teste.com.br', '$2y$10$.1w/hiJO/omiVUAIhx3Lye56O9uKTI7tZKiH6Bmr6roKS7TtYpQtm', '123123', '2025-07-30 15:47:47', 1);

-- Tabela: planos
CREATE TABLE IF NOT EXISTS `planos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `descricao` text,
  `valor` decimal(10,2) NOT NULL,
  `ciclo` varchar(20) NOT NULL,
  `id_fornecedor` int DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_fornecedor` (`id_fornecedor`),
  CONSTRAINT `planos_ibfk_1` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `planos`;
INSERT INTO `planos` (`id`, `nome`, `descricao`, `valor`, `ciclo`, `id_fornecedor`, `ativo`, `data_cadastro`) VALUES
(2, 'teste', '123', 123.00, 'trimestral', 1, 1, '2025-07-30 15:50:26');

-- Tabela: mensalidades
CREATE TABLE IF NOT EXISTS `mensalidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_plano` int NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `data_pagamento` date DEFAULT NULL,
  `status` enum('pendente','pago','vencido') DEFAULT 'pendente',
  `observacao` text,
  `data_cadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_plano` (`id_plano`),
  CONSTRAINT `mensalidades_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `mensalidades_ibfk_2` FOREIGN KEY (`id_plano`) REFERENCES `planos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `mensalidades`;
INSERT INTO `mensalidades` (`id`, `id_usuario`, `id_plano`, `valor`, `data_vencimento`, `data_pagamento`, `status`, `observacao`, `data_cadastro`) VALUES
(1, 1, 2, 123.00, '2025-07-01', NULL, 'pendente', NULL, '2025-07-30 15:58:45'),
(2, 2, 2, 123.00, '2025-08-01', '2025-08-01', 'pago', '123', '2025-07-30 15:58:45');

-- Tabela: mensagem_logs
CREATE TABLE IF NOT EXISTS `mensagem_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mensalidade` int NOT NULL,
  `id_template` int NOT NULL,
  `data_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('enviado','falha') DEFAULT 'enviado',
  `resposta` text,
  PRIMARY KEY (`id`),
  KEY `id_mensalidade` (`id_mensalidade`),
  KEY `id_template` (`id_template`),
  CONSTRAINT `mensagem_logs_ibfk_1` FOREIGN KEY (`id_mensalidade`) REFERENCES `mensalidades` (`id`),
  CONSTRAINT `mensagem_logs_ibfk_2` FOREIGN KEY (`id_template`) REFERENCES `mensagem_templates` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELETE FROM `mensagem_logs`;
/* Os inserts de mensagem_logs foram omitidos por brevidade, mas podem ser incluídos caso deseje */