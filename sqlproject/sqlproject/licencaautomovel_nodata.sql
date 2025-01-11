-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2025 at 10:08 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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

-- --------------------------------------------------------

--
-- Table structure for table `codigopostal`
--

CREATE TABLE `codigopostal` (
  `codigoPostal` varchar(8) NOT NULL,
  `nomeLocalidade` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacto`
--

CREATE TABLE `contacto` (
  `idContacto` int(11) NOT NULL,
  `valorContacto` varchar(45) NOT NULL,
  `idTipoContacto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `contratolicenca`
--

CREATE TABLE `contratolicenca` (
  `idContrato` int(11) NOT NULL,
  `idLicenca` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `licencaproduto`
--

CREATE TABLE `licencaproduto` (
  `idLicenca` int(11) NOT NULL,
  `idProduto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `pedidocriacaoconta`
--

CREATE TABLE `pedidocriacaoconta` (
  `idPedidoCriacaoConta` int(11) NOT NULL,
  `estadoPedidoCriacaoConta` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produto`
--

CREATE TABLE `produto` (
  `idProduto` int(11) NOT NULL,
  `nomeProduto` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipocliente`
--

CREATE TABLE `tipocliente` (
  `idTipoCliente` int(11) NOT NULL,
  `nomeTipoCliente` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipocontacto`
--

CREATE TABLE `tipocontacto` (
  `idTipoContacto` int(11) NOT NULL,
  `nomeTipoContacto` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipolicenca`
--

CREATE TABLE `tipolicenca` (
  `idTipoLicenca` int(11) NOT NULL,
  `nomeTipoLicenca` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipoutilizador`
--

CREATE TABLE `tipoutilizador` (
  `idTipoUtilizador` int(11) NOT NULL,
  `nomeTipoUtilizador` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
  MODIFY `idAgencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `analise`
--
ALTER TABLE `analise`
  MODIFY `idAnalise` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idCliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contacto`
--
ALTER TABLE `contacto`
  MODIFY `idContacto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `contrato`
--
ALTER TABLE `contrato`
  MODIFY `idContrato` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ficheiro`
--
ALTER TABLE `ficheiro`
  MODIFY `idFicheiro` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `licenca`
--
ALTER TABLE `licenca`
  MODIFY `idLicenca` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `morada`
--
ALTER TABLE `morada`
  MODIFY `idMorada` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `organizacao`
--
ALTER TABLE `organizacao`
  MODIFY `idOrganizacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
