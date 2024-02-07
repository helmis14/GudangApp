-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2024 at 10:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stokbarangs`
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
  `bukti_base64` text DEFAULT NULL,
  `status_barang` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_permintaan`
--

INSERT INTO `barang_permintaan` (`idbarang`, `idpermintaan`, `namabarang`, `unit`, `qtypermintaan`, `keterangan`, `bukti_base64`, `status_barang`) VALUES
(21, 62, 'CCTV', 'Pcs', 4, 'cctv untuk  di kantor pusat', NULL, 0),
(22, 62, 'keyboard', 'Pcs', 3, 'keyboard untuk di kantor pusat', NULL, 0),
(23, 63, 'keyboard baru 666', 'Pcs', 12, 'baru', NULL, 0),
(24, 63, 'keyboard baru 666', 'Pcs', 12, 'baru', NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_permintaan`
--
ALTER TABLE `barang_permintaan`
  ADD PRIMARY KEY (`idbarang`),
  ADD KEY `fk_idpermintaan` (`idpermintaan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang_permintaan`
--
ALTER TABLE `barang_permintaan`
  MODIFY `idbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang_permintaan`
--
ALTER TABLE `barang_permintaan`
  ADD CONSTRAINT `barang_permintaan_ibfk_1` FOREIGN KEY (`idpermintaan`) REFERENCES `permintaan` (`idpermintaan`),
  ADD CONSTRAINT `fk_idpermintaan` FOREIGN KEY (`idpermintaan`) REFERENCES `permintaan` (`idpermintaan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
