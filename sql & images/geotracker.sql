-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.24-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.0.0.6468
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para geotracker
CREATE DATABASE IF NOT EXISTS `geotracker` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `geotracker`;

-- Copiando estrutura para tabela geotracker.categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(255) DEFAULT NULL,
  `enabled` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela geotracker.categorias: ~5 rows (aproximadamente)
INSERT INTO `categorias` (`id`, `categoria`, `enabled`) VALUES
	(1, 'Enchente', 1);
INSERT INTO `categorias` (`id`, `categoria`, `enabled`) VALUES
	(2, 'Queda de Árvore', 1);
INSERT INTO `categorias` (`id`, `categoria`, `enabled`) VALUES
	(3, 'Trânsito', 1);
INSERT INTO `categorias` (`id`, `categoria`, `enabled`) VALUES
	(4, 'Queda de Poste', 1);
INSERT INTO `categorias` (`id`, `categoria`, `enabled`) VALUES
	(5, 'Acidente de Trânsito', 1);

-- Copiando estrutura para tabela geotracker.ocorrencias
CREATE TABLE IF NOT EXISTS `ocorrencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `bairro` varchar(255) DEFAULT NULL,
  `rua` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT 3,
  `categoria` int(11) DEFAULT NULL,
  `latitude` decimal(20,6) DEFAULT NULL,
  `longitude` decimal(20,6) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela geotracker.ocorrencias: ~55 rows (aproximadamente)
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(1, 'cadastrador@geotracker.com', 'Carlos André', 'Acidente de trânsito', 'Vila Paraíba', 'Rua Capitão Neco', 1, 5, -22.813100, -45.192300, '2024-06-24 00:39:16');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(2, 'cadastrador@geotracker.com', 'Amanda Oliveira', 'Queda de árvore', 'Jardim Coelho', 'Rua Marquês do Herval', 2, 2, -22.819400, -45.197200, '2024-06-24 16:41:16');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(3, 'cadastrador@geotracker.com', 'Juliana Costa', 'Trânsito congestionado', 'Centro', 'Avenida Rui Barbosa', 2, 3, -22.817900, -45.195600, '2024-06-24 09:28:31');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(4, 'cadastrador@geotracker.com', 'Pedro Santos', 'Queda de poste', 'Santa Luzia', 'Rua Antônio de Souza Barros', 1, 4, -22.811200, -45.183800, '2024-06-24 21:18:51');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(5, 'cadastrador@geotracker.com', 'Fernanda Almeida', 'Acidente de trânsito', 'Engenheiro Neiva', 'Rua São Pedro', 1, 5, -22.813500, -45.196400, '2024-06-24 06:08:39');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(6, 'cadastrador@geotracker.com', 'Rafaela Lima', 'Enchente na rua', 'Vila Comendador Rodrigues Alves', 'Rua Humaitá', 1, 1, -22.814900, -45.198700, '2024-06-24 14:46:45');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(7, 'cadastrador@geotracker.com', 'Marcos Pereira', 'Queda de árvore', 'Vila Municipal', 'Rua Doutor Martiniano', 2, 2, -22.814100, -45.197100, '2024-06-24 07:27:48');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(8, 'cadastrador@geotracker.com', 'Laura Santos', 'Trânsito lento', 'Jardim do Vale', 'Rua Doutor José Luiz Cembranelli', 1, 3, -22.808200, -45.204300, '2024-06-24 16:58:44');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(9, 'cadastrador@geotracker.com', 'Rodrigo Costa', 'Queda de poste', 'Vila São José', 'Rua Alcides Ramos Nogueira', 1, 4, -22.810500, -45.191500, '2024-06-24 14:30:16');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(10, 'cadastrador@geotracker.com', 'Carla Oliveira', 'Acidente de trânsito', 'Jardim do Vale II', 'Rua Maria de Lourdes Friggi da Rocha', 2, 5, -22.808500, -45.203800, '2024-06-24 21:35:08');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(11, 'cadastrador@geotracker.com', 'Renato Almeida', 'Enchente na rua', 'Jardim Santa Luzia', 'Rua Santa Rita de Cássia', 1, 1, -22.819300, -45.205700, '2024-06-24 16:24:54');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(12, 'cadastrador@geotracker.com', 'Patrícia Ferreira', 'Queda de árvore', 'Vila São João', 'Rua Guilherme Augusto de Miranda', 1, 2, -22.813800, -45.196200, '2024-06-24 17:19:07');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(13, 'cadastrador@geotracker.com', 'Marcos Oliveira', 'Trânsito congestionado', 'Jardim das Palmeiras', 'Rua das Palmeiras', 3, 3, -22.810300, -45.199700, '2024-06-24 13:20:51');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(14, 'cadastrador@geotracker.com', 'Mariana Silva', 'Queda de poste', 'Vila Nova', 'Rua das Violetas', 1, 4, -22.812600, -45.201400, '2024-06-24 14:46:56');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(15, 'cadastrador@geotracker.com', 'João Santos', 'Acidente de trânsito', 'Jardim do Vale III', 'Rua das Acácias', 3, 5, -22.811700, -45.198300, '2024-06-24 09:52:09');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(16, 'cadastrador@geotracker.com', 'Ana Costa', 'Enchente na rua', 'Vila Paraíso', 'Rua das Margaridas', 1, 1, -22.819700, -45.198100, '2024-06-24 04:59:57');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(17, 'cadastrador@geotracker.com', 'José Oliveira', 'Queda de árvore', 'Vila Maria', 'Rua Padre Edmundo Colella', 2, 2, -22.821400, -45.202500, '2024-06-24 19:23:20');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(18, 'cadastrador@geotracker.com', 'Lúcia Almeida', 'Trânsito lento', 'Vila Rica', 'Rua José da Silva Ribeiro', 1, 3, -22.808900, -45.206700, '2024-06-24 09:56:48');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(19, 'cadastrador@geotracker.com', 'Paulo Santos', 'Queda de poste', 'Jardim do Lago', 'Rua Manoel de Souza Leite', 2, 4, -22.807200, -45.195600, '2024-06-24 15:33:59');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(20, 'cadastrador@geotracker.com', 'Carolina Costa', 'Acidente de trânsito', 'Jardim São José', 'Rua Antônio de Almeida Leite', 2, 5, -22.813600, -45.199200, '2024-06-24 23:59:31');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(21, 'cadastrador@geotracker.com', 'Lucas Oliveira', 'Enchente na rua', 'Vila Santa Rita', 'Rua José Benedito Lopes', 1, 1, -22.807300, -45.199800, '2024-06-24 01:15:41');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(22, 'cadastrador@geotracker.com', 'Mariana Almeida', 'Queda de árvore', 'Vila São Judas Tadeu', 'Rua Major Rômulo Ramos de Oliveira', 2, 2, -22.811800, -45.187900, '2024-06-24 06:19:50');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(23, 'cadastrador@geotracker.com', 'Marcos Costa', 'Trânsito congestionado', 'Jardim das Orquídeas', 'Rua das Orquídeas', 1, 3, -22.815400, -45.203500, '2024-06-24 03:52:07');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(24, 'cadastrador@geotracker.com', 'Larissa Santos', 'Queda de poste', 'Vila São Francisco', 'Rua Professor José Roberto Salgado', 1, 4, -22.816200, -45.186300, '2024-06-24 00:21:09');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(25, 'cadastrador@geotracker.com', 'Ricardo Oliveira', 'Acidente de trânsito', 'Jardim São Miguel', 'Rua João Antônio de Oliveira', 3, 5, -22.819100, -45.200700, '2024-06-24 14:09:21');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(26, 'cadastrador@geotracker.com', 'Fernanda Costa', 'Enchente na rua', 'Jardim América', 'Rua dos Cravos', 3, 1, -22.812300, -45.193700, '2024-06-24 21:43:19');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(27, 'cadastrador@geotracker.com', 'Gabriel Almeida', 'Queda de árvore', 'Vila Industrial', 'Rua Sargento Nilton Oliveira Santos', 2, 2, -22.814600, -45.198500, '2024-06-24 18:08:31');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(28, 'cadastrador@geotracker.com', 'Camila Santos', 'Trânsito lento', 'Jardim do Trevo', 'Rua dos Hibiscos', 1, 3, -22.809600, -45.202400, '2024-06-24 01:32:40');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(29, 'cadastrador@geotracker.com', 'Guilherme Oliveira', 'Queda de poste', 'Vila Brasil', 'Rua das Magnólias', 3, 4, -22.816500, -45.194700, '2024-06-24 01:17:49');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(30, 'cadastrador@geotracker.com', 'Beatriz Costa', 'Acidente de trânsito', 'Jardim das Oliveiras', 'Rua das Oliveiras', 2, 5, -22.810400, -45.204200, '2024-06-24 01:51:05');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(31, 'cadastrador@geotracker.com', 'José da Silva', 'Acidente de trânsito', 'Centro', 'Rua Aluísio Alves Amaral', 1, 5, -22.849200, -45.231500, '2024-06-24 05:21:59');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(32, 'cadastrador@geotracker.com', 'Maria Santos', 'Queda de árvore', 'Jardim Paraíso', 'Rua Santo Antônio', 1, 2, -22.849800, -45.240300, '2024-06-24 21:16:39');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(33, 'cadastrador@geotracker.com', 'Antônio Oliveira', 'Trânsito congestionado', 'Vila Rica', 'Rua Antônio Raimundo Batista', 1, 3, -22.838100, -45.239600, '2024-06-24 18:17:20');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(34, 'cadastrador@geotracker.com', 'Luiza Costa', 'Queda de poste', 'Jardim das Palmeiras', 'Rua Juscelino Kubitschek de Oliveira', 1, 4, -22.833700, -45.232900, '2024-06-24 03:36:45');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(35, 'cadastrador@geotracker.com', 'Rafael Santos', 'Acidente de trânsito', 'Jardim Santa Rita', 'Rua Doutor Arnaldo Caetano Ribeiro', 2, 5, -22.825300, -45.231200, '2024-06-24 11:11:43');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(36, 'cadastrador@geotracker.com', 'Ana Oliveira', 'Enchente na rua', 'Vila Nova', 'Rua José Vicente de Paula', 2, 1, -22.820100, -45.231800, '2024-06-24 21:08:23');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(37, 'cadastrador@geotracker.com', 'Gustavo Costa', 'Queda de árvore', 'Jardim São José', 'Rua Manoel Antônio Pereira', 1, 2, -22.817400, -45.235700, '2024-06-24 00:06:44');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(38, 'cadastrador@geotracker.com', 'Fernanda Oliveira', 'Trânsito lento', 'Vila Paraíso', 'Rua Isaias Antônio da Silva', 1, 3, -22.824600, -45.239200, '2024-06-24 09:08:31');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(39, 'cadastrador@geotracker.com', 'Marcos Costa', 'Queda de poste', 'Jardim das Oliveiras', 'Rua Professor Antônio Rabelo', 1, 4, -22.829800, -45.236400, '2024-06-24 21:22:25');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(40, 'cadastrador@geotracker.com', 'Amanda Santos', 'Acidente de trânsito', 'Jardim das Flores', 'Rua José Antônio Ribeiro', 2, 5, -22.837200, -45.237900, '2024-06-24 07:26:34');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(41, 'cadastrador@geotracker.com', 'Roberto Oliveira', 'Enchente na rua', 'Vila São José', 'Rua Francisco Carlos de Almeida', 1, 1, -22.840500, -45.233100, '2024-06-24 21:05:32');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(42, 'cadastrador@geotracker.com', 'Laura Costa', 'Queda de árvore', 'Vila Santa Rita', 'Rua Doutor Manoel Marcondes', 1, 2, -22.835700, -45.229600, '2024-06-24 11:08:01');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(43, 'cadastrador@geotracker.com', 'Pedro Oliveira', 'Trânsito congestionado', 'Jardim Bela Vista', 'Rua Maria Emília Torres', 3, 3, -22.824300, -45.240500, '2024-06-24 16:23:29');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(44, 'cadastrador@geotracker.com', 'Carla Costa', 'Queda de poste', 'Jardim Imperial', 'Rua Antônio de Almeida Barbosa', 1, 4, -22.822400, -45.238100, '2024-06-24 00:33:24');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(45, 'cadastrador@geotracker.com', 'Rodrigo Santos', 'Acidente de trânsito', 'Jardim das Palmas', 'Rua Antônio Joaquim de Oliveira', 2, 5, -22.830400, -45.233800, '2024-06-24 01:36:31');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(46, 'cadastrador@geotracker.com', 'João Silva', 'Acidente de trânsito', 'Centro', 'Rua das Flores', 2, 5, -22.845600, -45.236700, '2024-06-17 19:15:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(47, 'cadastrador@geotracker.com', 'Patrícia Oliveira', 'Queda de árvore', 'Jardim América', 'Rua João Pessoa', 1, 2, -22.841200, -45.242100, '2024-06-17 19:30:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(48, 'cadastrador@geotracker.com', 'Josué Costa', 'Trânsito congestionado', 'Vila dos Passarinhos', 'Rua das Oliveiras', 1, 3, -22.837900, -45.239800, '2024-06-17 19:45:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(49, 'cadastrador@geotracker.com', 'Marina Santos', 'Queda de poste', 'Jardim Primavera', 'Rua das Acácias', 3, 4, -22.832100, -45.235200, '2024-06-17 20:00:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(50, 'cadastrador@geotracker.com', 'Felipe Oliveira', 'Acidente de trânsito', 'Jardim das Hortênsias', 'Rua das Tulipas', 1, 5, -22.826500, -45.234400, '2024-06-17 20:15:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(51, 'cadastrador@geotracker.com', 'Camila Costa', 'Enchente na rua', 'Vila dos Girassóis', 'Rua das Margaridas', 2, 1, -22.823600, -45.231700, '2024-06-17 20:30:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(52, 'cadastrador@geotracker.com', 'Márcio Santos', 'Queda de árvore', 'Jardim das Orquídeas', 'Rua das Violetas', 2, 2, -22.818900, -45.237600, '2024-06-17 20:45:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(53, 'cadastrador@geotracker.com', 'Isabela Oliveira', 'Trânsito lento', 'Vila dos Pinheiros', 'Rua das Bromélias', 1, 3, -22.826300, -45.241000, '2024-06-17 21:00:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(54, 'cadastrador@geotracker.com', 'Daniel Costa', 'Queda de poste', 'Jardim das Cerejeiras', 'Rua das Camélias', 1, 4, -22.831700, -45.239500, '2024-06-17 21:15:00');
INSERT INTO `ocorrencias` (`id`, `email`, `nome`, `descricao`, `bairro`, `rua`, `status`, `categoria`, `latitude`, `longitude`, `created_at`) VALUES
	(55, 'cadastrador@geotracker.com', 'Juliana Santos', 'Acidente de trânsito', 'Jardim dos Ipês', 'Rua das Azaléias', 3, 5, -22.839400, -45.236800, '2024-06-17 21:30:00');

-- Copiando estrutura para tabela geotracker.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'https://i.imgur.com/ztjLCMr.png',
  `active` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `user_type` int(11) DEFAULT 1,
  `first_login` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- Copiando dados para a tabela geotracker.users: ~4 rows (aproximadamente)
INSERT INTO `users` (`id`, `email`, `password`, `name`, `image`, `active`, `created_at`, `user_type`, `first_login`) VALUES
	(1, 'admin@geotracker.com', '$2y$10$Kee0t7C.Nyq/mDNUV/zmjuDlLzG/QGn7qDOVI5bdl4h7ezqH7Tlyu', 'João Cleber', 'https://static.vecteezy.com/system/resources/thumbnails/006/737/316/small_2x/astronaut-floating-in-space-cartoon-icon-illustration-space-technology-icon-concept-isolated-premium-flat-cartoon-style-vector.jpg', 1, '2024-06-17 10:14:13', 4, 0);
INSERT INTO `users` (`id`, `email`, `password`, `name`, `image`, `active`, `created_at`, `user_type`, `first_login`) VALUES
	(2, 'visualizador@geotracker.com', '$2y$10$aEFyhpY2eZw7v/NudELCa.HXWmox5UMEh5RIWkfuc06X07it6gX6e', 'Robertinho Santos', 'https://www.rainforest-alliance.org/wp-content/uploads/2021/06/scarlet-macaw-square-2-400x400.jpg.optimal.jpg', 1, '2024-06-24 10:09:00', 1, 0);
INSERT INTO `users` (`id`, `email`, `password`, `name`, `image`, `active`, `created_at`, `user_type`, `first_login`) VALUES
	(3, 'validador@geotracker.com', '$2y$10$c.kOrIg8pIgtUXeWx7qL6eFkmb0YDuy6ZzHXi67/XtsO7DVQLJ/9u', 'Pedro Carlos', 'https://img2.cdn.91app.com.my/webapi/imagesV3/Original/SalePage/18414/0/636583432728530000?v=1', 1, '2024-06-24 10:09:17', 2, 0);
INSERT INTO `users` (`id`, `email`, `password`, `name`, `image`, `active`, `created_at`, `user_type`, `first_login`) VALUES
	(4, 'cadastrador@geotracker.com', '$2y$10$2jcEbRUlyH1NyEvs0A.cxeo0PnXwmwzE/t4hkwpndtY.Yc/rj2gXe', 'Erasmo Carlinhos', 'https://tudosobrecachorros.com.br/wp-content/uploads/rottweiler1-400x400.jpg', 1, '2024-06-24 10:09:47', 3, 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
