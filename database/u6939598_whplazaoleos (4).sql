-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2024 at 10:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u6939598_whplazaoleos`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang_permintaan`
--

CREATE TABLE `barang_permintaan` (
  `idbarang` int(11) NOT NULL,
  `idpermintaan` int(11) DEFAULT NULL,
  `namabarang` varchar(255) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `qtypermintaan` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `bukti_base64` longblob DEFAULT NULL,
  `status_barang` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keluar`
--

CREATE TABLE `keluar` (
  `idkeluar` int(11) NOT NULL,
  `idpermintaan` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `qty` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `penerima` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `activity` varchar(255) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `iduser` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login`
--

CREATE TABLE `login` (
  `iduser` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `role` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login`
--

INSERT INTO `login` (`iduser`, `email`, `password`, `role`) VALUES
(8, 'bm@plazaoleos.com', '12345', 'superadmin'),
(9, 'spv.e1@plazaoleos.com', '12345', 'supervisor'),
(47, 'warehouse@plazaoleos.com ', '12345', 'gudang'),
(48, 'dev@rohedagroup.com', 'DEV@rg2024!', 'dev'),
(52, 'ce@plazaoleos.com', '12345', 'superadmin'),
(53, 'spv.e2@plazaoleos.com', '12345', 'supervisor'),
(54, 'andre.christian@rohedagroup.com', '12345', 'superadmin'),
(55, 'carefast@plazaoleos.com', '12345', 'supervisorgudang'),
(56, 'hse@plazaoleos.com', '12345', 'supervisoradmin'),
(57, 'warehouse2@plazaoleos.com', '12345', 'gudang');

-- --------------------------------------------------------

--
-- Table structure for table `masuk`
--

CREATE TABLE `masuk` (
  `idmasuk` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `keterangan` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL,
  `penerima` varchar(50) NOT NULL,
  `bukti_masuk_base64` longblob NOT NULL,
  `distributor` varchar(100) NOT NULL,
  `deskripsi` varchar(50) NOT NULL,
  `status` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permintaan`
--

CREATE TABLE `permintaan` (
  `idpermintaan` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bukti_base64` longblob NOT NULL,
  `status` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permintaan_keluar`
--

CREATE TABLE `permintaan_keluar` (
  `idpermintaan` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gambar_base64` longblob NOT NULL,
  `bukti_wo` longblob NOT NULL,
  `status` int(3) NOT NULL,
  `status2` int(3) NOT NULL,
  `status3` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `retur`
--

CREATE TABLE `retur` (
  `no` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idretur` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `qtyretur` varchar(50) NOT NULL,
  `keterangan` varchar(50) NOT NULL,
  `gambar_base64` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `idbarang` int(11) NOT NULL,
  `namabarang` varchar(25) NOT NULL,
  `deskripsi` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `kategori` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_permintaan`
--
ALTER TABLE `barang_permintaan`
  ADD PRIMARY KEY (`idbarang`),
  ADD KEY `barang_permintaan_ibfk_1` (`idpermintaan`);

--
-- Indexes for table `keluar`
--
ALTER TABLE `keluar`
  ADD PRIMARY KEY (`idkeluar`),
  ADD KEY `fk_permintaan` (`idpermintaan`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_iduser` (`iduser`);

--
-- Indexes for table `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`iduser`);

--
-- Indexes for table `masuk`
--
ALTER TABLE `masuk`
  ADD PRIMARY KEY (`idmasuk`);

--
-- Indexes for table `permintaan`
--
ALTER TABLE `permintaan`
  ADD PRIMARY KEY (`idpermintaan`);

--
-- Indexes for table `permintaan_keluar`
--
ALTER TABLE `permintaan_keluar`
  ADD PRIMARY KEY (`idpermintaan`);

--
-- Indexes for table `retur`
--
ALTER TABLE `retur`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`idbarang`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang_permintaan`
--
ALTER TABLE `barang_permintaan`
  MODIFY `idbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `keluar`
--
ALTER TABLE `keluar`
  MODIFY `idkeluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

--
-- AUTO_INCREMENT for table `login`
--
ALTER TABLE `login`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `masuk`
--
ALTER TABLE `masuk`
  MODIFY `idmasuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permintaan`
--
ALTER TABLE `permintaan`
  MODIFY `idpermintaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permintaan_keluar`
--
ALTER TABLE `permintaan_keluar`
  MODIFY `idpermintaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `retur`
--
ALTER TABLE `retur`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `idbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang_permintaan`
--
ALTER TABLE `barang_permintaan`
  ADD CONSTRAINT `barang_permintaan_ibfk_1` FOREIGN KEY (`idpermintaan`) REFERENCES `permintaan` (`idpermintaan`),
  ADD CONSTRAINT `fk_idpermintaan` FOREIGN KEY (`idpermintaan`) REFERENCES `permintaan` (`idpermintaan`);

--
-- Constraints for table `keluar`
--
ALTER TABLE `keluar`
  ADD CONSTRAINT `fk_permintaan` FOREIGN KEY (`idpermintaan`) REFERENCES `permintaan_keluar` (`idpermintaan`),
  ADD CONSTRAINT `keluar_ibfk_1` FOREIGN KEY (`idpermintaan`) REFERENCES `permintaan_keluar` (`idpermintaan`);

--
-- Constraints for table `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `fk_iduser` FOREIGN KEY (`iduser`) REFERENCES `login` (`iduser`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
