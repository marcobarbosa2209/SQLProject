-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2025 at 11:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `licencaautomovel`
--

-- --------------------------------------------------------

--
-- Table structure for table `agencia`
--

CREATE TABLE `agencia` (
  `idAgencia` int(11) NOT NULL,
  `nomeAgencia` varchar(45) NOT NULL,
  `idContacto` int(11) DEFAULT NULL,
  `idUtilizador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `agencia`
--

INSERT INTO `agencia` (`idAgencia`, `nomeAgencia`, `idContacto`, `idUtilizador`) VALUES
(11, 'Agência Central', 1, 1),
(12, 'Agência Norte', 2, 2),
(13, 'Agência Sul', 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `analise`
--

CREATE TABLE `analise` (
  `idAnalise` int(11) NOT NULL,
  `estadoAnalise` varchar(45) DEFAULT NULL,
  `descricaoAnalise` varchar(150) DEFAULT NULL,
  `idContrato` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `analise`
--

INSERT INTO `analise` (`idAnalise`, `estadoAnalise`, `descricaoAnalise`, `idContrato`) VALUES
(1, 'Aceitado', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque molestie ultrices lectus et ultrices. Class aptent taciti sociosqu ad litora.', 1),
(2, 'Negado', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque molestie ultrices lectus et ultrices. Class aptent taciti sociosqu ad litora.', 2),
(3, 'Aceitado', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque molestie ultrices lectus et ultrices. Class aptent taciti sociosqu ad litora.', 3),
(4, 'Pendente', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque molestie ultrices lectus et ultrices. Class aptent taciti sociosqu ad litora.', 4);

-- --------------------------------------------------------

--
-- Table structure for table `cliente`
--

CREATE TABLE `cliente` (
  `idCliente` int(11) NOT NULL,
  `nomeCliente` varchar(45) NOT NULL,
  `idAgencia` int(11) DEFAULT NULL,
  `idTipoCliente` int(11) DEFAULT NULL,
  `idContacto` int(11) DEFAULT NULL,
  `idUtilizador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `cliente`
--

INSERT INTO `cliente` (`idCliente`, `nomeCliente`, `idAgencia`, `idTipoCliente`, `idContacto`, `idUtilizador`) VALUES
(85, 'João Silva', 11, 1, 1, 1),
(86, 'Maria Santos', 12, 1, 2, 2),
(87, 'Carlos Pereira', 13, 2, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `codigopostal`
--

CREATE TABLE `codigopostal` (
  `codigoPostal` varchar(8) NOT NULL,
  `nomeLocalidade` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `codigopostal`
--

INSERT INTO `codigopostal` (`codigoPostal`, `nomeLocalidade`) VALUES
('1111-111', 'organizacao 1 cidade'),
('5236-325', 'esemriz'),
('5283502', 'aiosmdiadnm'),
('5823-424', 'zinde city'),
('5879-524', 'famalicoum'),
('6576-694', 'fixe cidade'),
('teste', 'etse');

-- --------------------------------------------------------

--
-- Table structure for table `contacto`
--

CREATE TABLE `contacto` (
  `idContacto` int(11) NOT NULL,
  `valorContacto` varchar(45) NOT NULL,
  `idTipoContacto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `contacto`
--

INSERT INTO `contacto` (`idContacto`, `valorContacto`, `idTipoContacto`) VALUES
(1, 'clientes1@cliente.pt', 1),
(2, '124891841', 2),
(3, '124891841', 2),
(4, '124891841', 2),
(5, 'secondclient@gmail.com', 1),
(6, '1241241241', 2),
(8, '9124124551', 2),
(9, '1240@gmail.com', 1),
(10, 'agencia2@agencias.pt', 1),
(14, '523585273', 2),
(15, '151235125', 2);

-- --------------------------------------------------------

--
-- Table structure for table `contrato`
--

CREATE TABLE `contrato` (
  `idContrato` int(11) NOT NULL,
  `nomeContrato` varchar(45) DEFAULT NULL,
  `estadoContrato` varchar(45) DEFAULT NULL,
  `idOrganizacao` int(11) NOT NULL,
  `idAgencia` int(11) NOT NULL,
  `idCliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `contrato`
--

INSERT INTO `contrato` (`idContrato`, `nomeContrato`, `estadoContrato`, `idOrganizacao`, `idAgencia`, `idCliente`) VALUES
(1, 'Contrato de Maria Santos', NULL, 45, 11, 86),
(2, 'Contrato de João Silva', NULL, 44, 13, 85),
(3, 'Contrato de Carlos Pereira', NULL, 45, 12, 87),
(4, 'Contrato de João Silva', NULL, 44, 11, 85),
(5, 'Test412312', 'pending', 46, 11, 86);

-- --------------------------------------------------------

--
-- Table structure for table `contratolicenca`
--

CREATE TABLE `contratolicenca` (
  `idContrato` int(11) NOT NULL,
  `idLicenca` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `contratolicenca`
--

INSERT INTO `contratolicenca` (`idContrato`, `idLicenca`) VALUES
(1, 2),
(2, 1),
(5, 2);

-- --------------------------------------------------------

--
-- Table structure for table `ficheiro`
--

CREATE TABLE `ficheiro` (
  `idFicheiro` int(11) NOT NULL,
  `nomeFicheiro` varchar(255) NOT NULL,
  `idContrato` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licenca`
--

CREATE TABLE `licenca` (
  `idLicenca` int(11) NOT NULL,
  `nomeLicenca` varchar(45) DEFAULT NULL,
  `idTipoLicenca` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `licenca`
--

INSERT INTO `licenca` (`idLicenca`, `nomeLicenca`, `idTipoLicenca`) VALUES
(1, 'Test1', 1),
(2, 'Test2', 2);

-- --------------------------------------------------------

--
-- Table structure for table `licencaproduto`
--

CREATE TABLE `licencaproduto` (
  `idLicenca` int(11) NOT NULL,
  `idProduto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `licencaproduto`
--

INSERT INTO `licencaproduto` (`idLicenca`, `idProduto`) VALUES
(1, 2),
(1, 3),
(2, 2),
(2, 5),
(2, 8),
(2, 19);

-- --------------------------------------------------------

--
-- Table structure for table `morada`
--

CREATE TABLE `morada` (
  `idMorada` int(11) NOT NULL,
  `nomeMorada` varchar(45) NOT NULL,
  `porta` int(11) NOT NULL,
  `codigoPostal` varchar(8) NOT NULL,
  `idAgencia` int(11) DEFAULT NULL,
  `idOrganizacao` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `morada`
--

INSERT INTO `morada` (`idMorada`, `nomeMorada`, `porta`, `codigoPostal`, `idAgencia`, `idOrganizacao`) VALUES
(3, 'rua fixer', 6, '5879-524', 1, NULL),
(4, 'ruia agencia 2', 515, '5236-325', 2, NULL),
(8, 'rua fixe top ', 24, '6576-694', NULL, NULL),
(9, 'rua fixe organizacao', 3, '1111-111', NULL, 1),
(10, 'aksdmadm', 1245, '5283502', NULL, 2);

-- --------------------------------------------------------

--
-- Table structure for table `organizacao`
--

CREATE TABLE `organizacao` (
  `idOrganizacao` int(11) NOT NULL,
  `nomeOrganizacao` varchar(45) NOT NULL,
  `idAgencia` int(11) NOT NULL,
  `idContacto` int(11) NOT NULL,
  `idUtilizador` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `organizacao`
--

INSERT INTO `organizacao` (`idOrganizacao`, `nomeOrganizacao`, `idAgencia`, `idContacto`, `idUtilizador`) VALUES
(44, 'AutoMax', 11, 1, 1),
(45, 'CarLife', 12, 2, 2),
(46, 'DriveSafe', 13, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `pedidocriacaoconta`
--

CREATE TABLE `pedidocriacaoconta` (
  `idPedidoCriacaoConta` int(11) NOT NULL,
  `estadoPedidoCriacaoConta` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `pedidocriacaoconta`
--

INSERT INTO `pedidocriacaoconta` (`idPedidoCriacaoConta`, `estadoPedidoCriacaoConta`) VALUES
(2, 'accepted'),
(4, 'denied'),
(5, 'denied'),
(6, 'denied'),
(7, 'pending'),
(8, 'accepted');

-- --------------------------------------------------------

--
-- Table structure for table `produto`
--

CREATE TABLE `produto` (
  `idProduto` int(11) NOT NULL,
  `nomeProduto` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `produto`
--

INSERT INTO `produto` (`idProduto`, `nomeProduto`) VALUES
(1, 'Ford Mustang GT'),
(2, 'Chevrolet Camaro ZL1'),
(3, 'Dodge Charger SRT Hellcat'),
(4, 'Tesla Model S Plaid'),
(5, 'Porsche 911 Carrera'),
(6, 'Lamborghini Aventador SVJ'),
(7, 'Ferrari F8 Tributo'),
(8, 'Bugatti Chiron Super Sport'),
(9, 'McLaren 720S'),
(10, 'Aston Martin DB11'),
(11, 'Nissan GT-R Nismo'),
(12, 'Toyota Supra GR'),
(13, 'Mazda RX-7 Spirit R'),
(14, 'Subaru WRX STI'),
(15, 'Mitsubishi Lancer Evolution X'),
(16, 'Audi R8 V10 Performance'),
(17, 'BMW M4 Competition'),
(18, 'Mercedes-AMG GT Black Series'),
(19, 'Jaguar F-Type R'),
(20, 'Koenigsegg Jesko Absolut'),
(21, 'Pagani Huayra Roadster'),
(22, 'Volkswagen Golf R'),
(23, 'Mini Cooper S John Cooper Works'),
(24, 'Hyundai Veloster N'),
(25, 'Kia Stinger GT'),
(26, 'Alfa Romeo Giulia Quadrifoglio'),
(27, 'Ford F-150 Raptor'),
(28, 'Chevrolet Silverado ZR2'),
(29, 'Ram 1500 TRX'),
(30, 'Jeep Wrangler Rubicon 392');

-- --------------------------------------------------------

--
-- Table structure for table `tipocliente`
--

CREATE TABLE `tipocliente` (
  `idTipoCliente` int(11) NOT NULL,
  `nomeTipoCliente` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tipocliente`
--

INSERT INTO `tipocliente` (`idTipoCliente`, `nomeTipoCliente`) VALUES
(1, 'Individual'),
(2, 'Professional');

-- --------------------------------------------------------

--
-- Table structure for table `tipocontacto`
--

CREATE TABLE `tipocontacto` (
  `idTipoContacto` int(11) NOT NULL,
  `nomeTipoContacto` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tipocontacto`
--

INSERT INTO `tipocontacto` (`idTipoContacto`, `nomeTipoContacto`) VALUES
(1, 'Email'),
(2, 'Phone');

-- --------------------------------------------------------

--
-- Table structure for table `tipolicenca`
--

CREATE TABLE `tipolicenca` (
  `idTipoLicenca` int(11) NOT NULL,
  `nomeTipoLicenca` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tipolicenca`
--

INSERT INTO `tipolicenca` (`idTipoLicenca`, `nomeTipoLicenca`) VALUES
(1, 'tipo licenca 1'),
(2, 'tipo licenca 2');

-- --------------------------------------------------------

--
-- Table structure for table `tipoutilizador`
--

CREATE TABLE `tipoutilizador` (
  `idTipoUtilizador` int(11) NOT NULL,
  `nomeTipoUtilizador` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tipoutilizador`
--

INSERT INTO `tipoutilizador` (`idTipoUtilizador`, `nomeTipoUtilizador`) VALUES
(1, 'Administrador'),
(2, 'Cliente'),
(3, 'Organizacao'),
(4, 'Agencia');

-- --------------------------------------------------------

--
-- Table structure for table `utilizador`
--

CREATE TABLE `utilizador` (
  `idUtilizador` int(11) NOT NULL,
  `nomeUtilizador` varchar(45) NOT NULL,
  `emailUtilizador` varchar(45) DEFAULT NULL,
  `passwordUtilizador` varchar(255) NOT NULL,
  `idTipoUtilizador` int(11) DEFAULT NULL,
  `idPedidoCriacaoConta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `utilizador`
--

INSERT INTO `utilizador` (`idUtilizador`, `nomeUtilizador`, `emailUtilizador`, `passwordUtilizador`, `idTipoUtilizador`, `idPedidoCriacaoConta`) VALUES
(1, 'admin', 'admin@admin.pt', '$2y$10$SsjRlLadm2K98Cb93zunleLGf3UN3Xw7LZ8DlqamZz2Q0h42oJm2a', 1, 2),
(2, 'cliente', 'cliente@cliente.pt', '$2y$10$16tewAnjNJqWxALnSencA..gnVMVNOlhnaAsI4r/j9mi9r4RDdoPq', 2, 7),
(3, 'organizacomuitofixe', 'organizacao@gmail.com', '$2y$10$asybwiqmvn6sVWPnMaEp9euXNJJBJoa9bWb.QgGSyQg37M5XcHdnq', 3, 8);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agencia`
--
ALTER TABLE `agencia`
  ADD PRIMARY KEY (`idAgencia`),
  ADD KEY `FK_Agencia_Contacto` (`idContacto`),
  ADD KEY `FK_Agencia_Utilizador` (`idUtilizador`);

--
-- Indexes for table `analise`
--
ALTER TABLE `analise`
  ADD PRIMARY KEY (`idAnalise`),
  ADD KEY `FK_Analise_Contrato` (`idContrato`);

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idCliente`),
  ADD KEY `FK_Cliente_TipoCliente` (`idTipoCliente`),
  ADD KEY `FK_Cliente_Contacto` (`idContacto`),
  ADD KEY `FK_Cliente_Utilizador` (`idUtilizador`),
  ADD KEY `FK_Cliente_Agencia` (`idAgencia`);

--
-- Indexes for table `codigopostal`
--
ALTER TABLE `codigopostal`
  ADD PRIMARY KEY (`codigoPostal`);

--
-- Indexes for table `contacto`
--
ALTER TABLE `contacto`
  ADD PRIMARY KEY (`idContacto`),
  ADD KEY `FK_Contacto_TipoContacto` (`idTipoContacto`);

--
-- Indexes for table `contrato`
--
ALTER TABLE `contrato`
  ADD PRIMARY KEY (`idContrato`),
  ADD KEY `FK_Contrato_Organizacao` (`idOrganizacao`),
  ADD KEY `FK_Contrato_Cliente` (`idCliente`),
  ADD KEY `FK_Contrato_Agencia` (`idAgencia`);

--
-- Indexes for table `contratolicenca`
--
ALTER TABLE `contratolicenca`
  ADD PRIMARY KEY (`idContrato`,`idLicenca`),
  ADD KEY `FK_ContratoLicenca_Contrato` (`idContrato`),
  ADD KEY `FK_ContratoLicenca_Licenca` (`idLicenca`);

--
-- Indexes for table `ficheiro`
--
ALTER TABLE `ficheiro`
  ADD PRIMARY KEY (`idFicheiro`),
  ADD KEY `FK_Ficheiro_Contrato` (`idContrato`);

--
-- Indexes for table `licenca`
--
ALTER TABLE `licenca`
  ADD PRIMARY KEY (`idLicenca`),
  ADD KEY `FK_Licenca_TipoLicenca` (`idTipoLicenca`);

--
-- Indexes for table `licencaproduto`
--
ALTER TABLE `licencaproduto`
  ADD PRIMARY KEY (`idLicenca`,`idProduto`),
  ADD KEY `FK_LicencaProduto_Licenca` (`idLicenca`),
  ADD KEY `FK_LicencaProduto_Produto` (`idProduto`);

--
-- Indexes for table `morada`
--
ALTER TABLE `morada`
  ADD PRIMARY KEY (`idMorada`),
  ADD KEY `FK_Morada_CodigoPostal` (`codigoPostal`),
  ADD KEY `FK_Morada_Organizacao` (`idOrganizacao`),
  ADD KEY `FK_Morada_Agencia` (`idAgencia`);

--
-- Indexes for table `organizacao`
--
ALTER TABLE `organizacao`
  ADD PRIMARY KEY (`idOrganizacao`),
  ADD KEY `FK_Organizacao_Contacto` (`idContacto`),
  ADD KEY `FK_Organizacao_Utilizador` (`idUtilizador`),
  ADD KEY `FK_Organizacao_Agencia` (`idAgencia`);

--
-- Indexes for table `pedidocriacaoconta`
--
ALTER TABLE `pedidocriacaoconta`
  ADD PRIMARY KEY (`idPedidoCriacaoConta`);

--
-- Indexes for table `produto`
--
ALTER TABLE `produto`
  ADD PRIMARY KEY (`idProduto`);

--
-- Indexes for table `tipocliente`
--
ALTER TABLE `tipocliente`
  ADD PRIMARY KEY (`idTipoCliente`);

--
-- Indexes for table `tipocontacto`
--
ALTER TABLE `tipocontacto`
  ADD PRIMARY KEY (`idTipoContacto`);

--
-- Indexes for table `tipolicenca`
--
ALTER TABLE `tipolicenca`
  ADD PRIMARY KEY (`idTipoLicenca`);

--
-- Indexes for table `tipoutilizador`
--
ALTER TABLE `tipoutilizador`
  ADD PRIMARY KEY (`idTipoUtilizador`);

--
-- Indexes for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD PRIMARY KEY (`idUtilizador`),
  ADD UNIQUE KEY `emailUtilizador` (`emailUtilizador`),
  ADD KEY `FK_Utilizador_TipoUtilizador` (`idTipoUtilizador`),
  ADD KEY `FK_Utilizador_PedidoCriacaoConta` (`idPedidoCriacaoConta`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agencia`
--
ALTER TABLE `agencia`
  MODIFY `idAgencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `analise`
--
ALTER TABLE `analise`
  MODIFY `idAnalise` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `contacto`
--
ALTER TABLE `contacto`
  MODIFY `idContacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `contrato`
--
ALTER TABLE `contrato`
  MODIFY `idContrato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ficheiro`
--
ALTER TABLE `ficheiro`
  MODIFY `idFicheiro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `licenca`
--
ALTER TABLE `licenca`
  MODIFY `idLicenca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `morada`
--
ALTER TABLE `morada`
  MODIFY `idMorada` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `organizacao`
--
ALTER TABLE `organizacao`
  MODIFY `idOrganizacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `pedidocriacaoconta`
--
ALTER TABLE `pedidocriacaoconta`
  MODIFY `idPedidoCriacaoConta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `produto`
--
ALTER TABLE `produto`
  MODIFY `idProduto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `tipocliente`
--
ALTER TABLE `tipocliente`
  MODIFY `idTipoCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipocontacto`
--
ALTER TABLE `tipocontacto`
  MODIFY `idTipoContacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipolicenca`
--
ALTER TABLE `tipolicenca`
  MODIFY `idTipoLicenca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tipoutilizador`
--
ALTER TABLE `tipoutilizador`
  MODIFY `idTipoUtilizador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `utilizador`
--
ALTER TABLE `utilizador`
  MODIFY `idUtilizador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agencia`
--
ALTER TABLE `agencia`
  ADD CONSTRAINT `FK_Agencia_Contacto` FOREIGN KEY (`idContacto`) REFERENCES `contacto` (`idContacto`),
  ADD CONSTRAINT `FK_Agencia_Utilizador` FOREIGN KEY (`idUtilizador`) REFERENCES `utilizador` (`idUtilizador`);

--
-- Constraints for table `analise`
--
ALTER TABLE `analise`
  ADD CONSTRAINT `FK_Analise_Contrato` FOREIGN KEY (`idContrato`) REFERENCES `contrato` (`idContrato`) ON DELETE CASCADE;

--
-- Constraints for table `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `FK_Cliente_Agencia` FOREIGN KEY (`idAgencia`) REFERENCES `agencia` (`idAgencia`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Cliente_Contacto` FOREIGN KEY (`idContacto`) REFERENCES `contacto` (`idContacto`),
  ADD CONSTRAINT `FK_Cliente_TipoCliente` FOREIGN KEY (`idTipoCliente`) REFERENCES `tipocliente` (`idTipoCliente`),
  ADD CONSTRAINT `FK_Cliente_Utilizador` FOREIGN KEY (`idUtilizador`) REFERENCES `utilizador` (`idUtilizador`);

--
-- Constraints for table `contacto`
--
ALTER TABLE `contacto`
  ADD CONSTRAINT `FK_Contacto_TipoContacto` FOREIGN KEY (`idTipoContacto`) REFERENCES `tipocontacto` (`idTipoContacto`);

--
-- Constraints for table `contrato`
--
ALTER TABLE `contrato`
  ADD CONSTRAINT `FK_Contrato_Agencia` FOREIGN KEY (`idAgencia`) REFERENCES `agencia` (`idAgencia`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Contrato_Cliente` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`),
  ADD CONSTRAINT `FK_Contrato_Organizacao` FOREIGN KEY (`idOrganizacao`) REFERENCES `organizacao` (`idOrganizacao`);

--
-- Constraints for table `contratolicenca`
--
ALTER TABLE `contratolicenca`
  ADD CONSTRAINT `FK_ContratoLicenca_Contrato` FOREIGN KEY (`idContrato`) REFERENCES `contrato` (`idContrato`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_ContratoLicenca_Licenca` FOREIGN KEY (`idLicenca`) REFERENCES `licenca` (`idLicenca`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ficheiro`
--
ALTER TABLE `ficheiro`
  ADD CONSTRAINT `FK_Ficheiro_Contrato` FOREIGN KEY (`idContrato`) REFERENCES `contrato` (`idContrato`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `licenca`
--
ALTER TABLE `licenca`
  ADD CONSTRAINT `licenca_ibfk_1` FOREIGN KEY (`idTipoLicenca`) REFERENCES `tipolicenca` (`idTipoLicenca`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `licencaproduto`
--
ALTER TABLE `licencaproduto`
  ADD CONSTRAINT `FK_LicencaProduto_Licenca` FOREIGN KEY (`idLicenca`) REFERENCES `licenca` (`idLicenca`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_LicencaProduto_Produto` FOREIGN KEY (`idProduto`) REFERENCES `produto` (`idProduto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `morada`
--
ALTER TABLE `morada`
  ADD CONSTRAINT `FK_Morada_Agencia` FOREIGN KEY (`idAgencia`) REFERENCES `agencia` (`idAgencia`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Morada_CodigoPostal` FOREIGN KEY (`codigoPostal`) REFERENCES `codigopostal` (`codigoPostal`),
  ADD CONSTRAINT `FK_Morada_Organizacao` FOREIGN KEY (`idOrganizacao`) REFERENCES `organizacao` (`idOrganizacao`);

--
-- Constraints for table `organizacao`
--
ALTER TABLE `organizacao`
  ADD CONSTRAINT `FK_Organizacao_Agencia` FOREIGN KEY (`idAgencia`) REFERENCES `agencia` (`idAgencia`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_Organizacao_Contacto` FOREIGN KEY (`idContacto`) REFERENCES `contacto` (`idContacto`),
  ADD CONSTRAINT `FK_Organizacao_Utilizador` FOREIGN KEY (`idUtilizador`) REFERENCES `utilizador` (`idUtilizador`);

--
-- Constraints for table `utilizador`
--
ALTER TABLE `utilizador`
  ADD CONSTRAINT `FK_Utilizador_PedidoCriacaoConta` FOREIGN KEY (`idPedidoCriacaoConta`) REFERENCES `pedidocriacaoconta` (`idPedidoCriacaoConta`),
  ADD CONSTRAINT `FK_Utilizador_TipoUtilizador` FOREIGN KEY (`idTipoUtilizador`) REFERENCES `tipoutilizador` (`idTipoUtilizador`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
