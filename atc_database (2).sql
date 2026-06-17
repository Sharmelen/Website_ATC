-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 17, 2026 at 03:41 PM
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
-- Database: `atc_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `atc_bio`
--

CREATE TABLE `atc_bio` (
  `no` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `full_name` text NOT NULL,
  `rank` text NOT NULL,
  `no_ten` text NOT NULL,
  `base` text NOT NULL,
  `jawatan` text NOT NULL,
  `ttp` date NOT NULL,
  `elpt_lvl` int(11) NOT NULL,
  `elpt_expiry` date NOT NULL,
  `medical_expiry` date NOT NULL,
  `medical_class` text NOT NULL,
  `cat_expiry` date NOT NULL,
  `cat_loc` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `atc_bio`
--

INSERT INTO `atc_bio` (`no`, `username`, `full_name`, `rank`, `no_ten`, `base`, `jawatan`, `ttp`, `elpt_lvl`, `elpt_expiry`, `medical_expiry`, `medical_class`, `cat_expiry`, `cat_loc`) VALUES
(0, 'sharm1996', 'SHARMELEN A/L VASANTHAN', 'LT TUDM', '376348', 'PANGKALAN UDARA SUBANG', 'PEGAWAI MENARA KAWALAN', '2033-06-09', 5, '2028-06-17', '2026-06-25', '3', '2032-06-09', 'PANGKALAN UDARA SUBANG');

-- --------------------------------------------------------

--
-- Table structure for table `atc_user`
--

CREATE TABLE `atc_user` (
  `no` int(11) NOT NULL,
  `username` text NOT NULL,
  `full_name` text NOT NULL,
  `rank` text NOT NULL,
  `no_ten` varchar(9) NOT NULL,
  `pwd` text NOT NULL,
  `unit` text NOT NULL,
  `jawatan` text NOT NULL,
  `ic` text NOT NULL,
  `pangkat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `atc_user`
--

INSERT INTO `atc_user` (`no`, `username`, `full_name`, `rank`, `no_ten`, `pwd`, `unit`, `jawatan`, `ic`, `pangkat`) VALUES
(1, 'sharm1996', 'SHARMELEN A/L VASANTHAN', '', '376348', '72198b61b964759a2867a36f562ae3e3439b676c8805b792b657020460fca9bd', 'PANGKALAN UDARA SUBANG', 'PEGAWAI MENARA KAWALAN', '960107086317', 'LT TUDM');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `atc_bio`
--
ALTER TABLE `atc_bio`
  ADD UNIQUE KEY `no` (`no`);

--
-- Indexes for table `atc_user`
--
ALTER TABLE `atc_user`
  ADD PRIMARY KEY (`no`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
