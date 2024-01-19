-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Jan 2024 pada 02.22
-- Versi server: 10.4.22-MariaDB
-- Versi PHP: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stokbarang`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `keluar`
--

CREATE TABLE `keluar` (
  `idkeluar` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `penerima` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `keluar`
--

INSERT INTO `keluar` (`idkeluar`, `idbarang`, `tanggal`, `penerima`, `qty`) VALUES
(1, 4, '2023-12-19 07:17:09', 'deni', 100),
(2, 4, '2023-12-19 07:32:54', 'deni', 100),
(3, 5, '2023-12-19 07:48:41', 'iwing', 500),
(4, 4, '2023-12-19 08:37:33', 'jeni', 10000),
(5, 11, '2023-12-20 08:21:56', 'deni', 100),
(6, 11, '2023-12-20 08:22:37', 'deni', 500),
(7, 13, '2023-12-20 08:27:52', 'deni', 100),
(8, 14, '2023-12-20 08:33:16', 'owi', 500),
(9, 15, '2023-12-20 08:39:15', 'deni', 500),
(10, 16, '2023-12-20 08:45:56', 'owi', 100),
(11, 17, '2023-12-22 07:03:22', 'roni', 100),
(12, 18, '2023-12-27 03:05:57', 'udin', 100),
(13, 19, '2023-12-27 03:07:40', 'iwing', 100),
(14, 18, '2023-12-27 03:08:30', 'deni', 20),
(16, 22, '2023-12-28 02:26:04', 'windu', 200),
(18, 24, '2023-12-28 03:08:56', 'deni', 10),
(20, 27, '2024-01-17 09:32:15', 'owi', 200);

-- --------------------------------------------------------

--
-- Struktur dari tabel `login`
--

CREATE TABLE `login` (
  `iduser` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `role` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `login`
--

INSERT INTO `login` (`iduser`, `email`, `password`, `role`) VALUES
(8, 'it.admin@rohedagroup.com', '12345', ''),
(9, 'user@rohedagroup.com', '12345', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `masuk`
--

CREATE TABLE `masuk` (
  `idmasuk` int(11) NOT NULL,
  `idbarang` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `keterangan` varchar(50) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `masuk`
--

INSERT INTO `masuk` (`idmasuk`, `idbarang`, `tanggal`, `keterangan`, `qty`) VALUES
(6, 4, '2023-12-19 07:15:53', 'windu', 500),
(7, 5, '2023-12-19 07:16:02', 'windu', 500),
(8, 4, '2023-12-19 08:36:51', 'owi', 10000),
(9, 6, '2023-12-20 06:40:44', 'windu', 620),
(10, 7, '2023-12-20 07:55:53', 'windu', 300),
(11, 8, '2023-12-20 08:08:59', 'deni', 50),
(12, 9, '2023-12-20 08:14:05', 'owi', 300),
(13, 10, '2023-12-20 08:18:03', 'owi', 300),
(14, 11, '2023-12-20 08:22:17', 'owi', 200),
(15, 12, '2023-12-20 08:25:33', 'deni', -200),
(16, 13, '2023-12-20 08:27:26', 'deni', 200),
(17, 14, '2023-12-20 08:33:03', 'deni', 200),
(18, 15, '2023-12-20 08:39:07', 'owi', 600),
(19, 16, '2023-12-20 08:45:44', 'windu', 200),
(20, 17, '2023-12-22 07:02:41', 'udin', 100),
(21, 18, '2023-12-27 03:05:07', 'mulki', 100),
(22, 19, '2023-12-27 03:07:11', 'ahmad', 100),
(23, 20, '2023-12-27 07:52:35', 'deni', 200),
(24, 21, '2023-12-27 08:46:34', 'deni', 200),
(27, 22, '2023-12-28 01:35:16', 'deni', 200),
(30, 27, '2024-01-17 09:31:57', 'deni', 200);

-- --------------------------------------------------------

--
-- Struktur dari tabel `permintaan`
--

CREATE TABLE `permintaan` (
  `idpermintaan` int(11) NOT NULL,
  `namabarang` varchar(100) NOT NULL,
  `unit` varchar(100) NOT NULL,
  `qtypermintaan` bigint(100) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bukti` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `permintaan`
--

INSERT INTO `permintaan` (`idpermintaan`, `namabarang`, `unit`, `qtypermintaan`, `keterangan`, `tanggal`, `bukti`) VALUES
(4, 'samsung s23', 'pcs', 200, 'gudang mulki', '2024-01-18 03:41:48', 0x616c616d6174207869616f6d692063656e7472652e504e47);

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock`
--

CREATE TABLE `stock` (
  `idbarang` int(11) NOT NULL,
  `namabarang` varchar(25) NOT NULL,
  `deskripsi` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL,
  `lokasi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `keluar`
--
ALTER TABLE `keluar`
  ADD PRIMARY KEY (`idkeluar`);

--
-- Indeks untuk tabel `login`
--
ALTER TABLE `login`
  ADD PRIMARY KEY (`iduser`);

--
-- Indeks untuk tabel `masuk`
--
ALTER TABLE `masuk`
  ADD PRIMARY KEY (`idmasuk`);

--
-- Indeks untuk tabel `permintaan`
--
ALTER TABLE `permintaan`
  ADD PRIMARY KEY (`idpermintaan`);

--
-- Indeks untuk tabel `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`idbarang`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `keluar`
--
ALTER TABLE `keluar`
  MODIFY `idkeluar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `login`
--
ALTER TABLE `login`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `masuk`
--
ALTER TABLE `masuk`
  MODIFY `idmasuk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `permintaan`
--
ALTER TABLE `permintaan`
  MODIFY `idpermintaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `stock`
--
ALTER TABLE `stock`
  MODIFY `idbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
