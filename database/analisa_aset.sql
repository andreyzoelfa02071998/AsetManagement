-- MariaDB dump 10.19  Distrib 10.4.27-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: analisa_aset
-- ------------------------------------------------------
-- Server version	10.10.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `analisa_aset`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `analisa_aset` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `analisa_aset`;

--
-- Table structure for table `ai_settings`
--

DROP TABLE IF EXISTS `ai_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `provider` enum('chatgpt','gemini','claude','qwen','llama','custom') NOT NULL DEFAULT 'chatgpt',
  `api_key` varchar(255) DEFAULT NULL,
  `base_url` varchar(255) DEFAULT NULL,
  `model` varchar(120) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_settings_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_settings`
--

LOCK TABLES `ai_settings` WRITE;
/*!40000 ALTER TABLE `ai_settings` DISABLE KEYS */;
INSERT INTO `ai_settings` VALUES (1,1,1,'gemini','AIzaSyAv3lscZ2pFKfzIdcXxb4QNGc0V9G8QsbM','','gemini-3.6-flash','2026-09-29 11:19:03','2026-09-29 13:23:29');
/*!40000 ALTER TABLE `ai_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `type` enum('emas','saham','reksa_dana','crypto','properti','kas','rdn','lainnya') NOT NULL DEFAULT 'lainnya',
  `symbol` varchar(30) DEFAULT NULL,
  `platform` varchar(80) DEFAULT NULL,
  `portfolio_name` varchar(120) DEFAULT NULL,
  `quantity_current` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `unit` varchar(20) NOT NULL DEFAULT 'unit',
  `avg_price` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `market_price` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `market_value` decimal(18,2) NOT NULL DEFAULT 0.00,
  `invested_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `target_buy_price` decimal(18,4) DEFAULT NULL,
  `lot_size` int(10) unsigned NOT NULL DEFAULT 1,
  `min_purchase_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `target_allocation` decimal(6,2) NOT NULL DEFAULT 0.00,
  `is_planned` tinyint(1) NOT NULL DEFAULT 1,
  `price_alert_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `price_alert_target` decimal(18,4) DEFAULT NULL,
  `price_alert_direction` enum('below','above') NOT NULL DEFAULT 'below',
  `price_alert_triggered_at` datetime DEFAULT NULL,
  `price_alert_last_price` decimal(18,4) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_assets_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (5,1,'BBCA','saham','BBCA','Stockbit','Saham Tabungan',400.0000,'share',6453.4200,6050.0000,2420000.00,2581366.00,NULL,100,620000.00,0.00,1,1,6000.0000,'below',NULL,6050.0000,'Import Stockbit: 4 lot, market value Rp 2.480.000, P&L Rp -101.366 (-3.93%)','2026-09-28 16:33:17','2026-10-01 13:43:21'),(6,1,'BBRI','saham','BBRI','Stockbit','Saham Tabungan',100.0000,'share',3845.7600,3110.0000,311000.00,384576.00,NULL,100,315000.00,0.00,1,0,NULL,'below',NULL,NULL,'Import Stockbit: 1 lot, market value Rp 315.000, P&L Rp -69.576 (-18.09%)','2026-09-28 16:33:17','2026-10-01 13:41:19'),(7,1,'BUKA','saham','BUKA','Stockbit','Jual Cepat',300.0000,'share',157.9200,106.0000,31800.00,47376.00,NULL,100,10300.00,0.00,0,0,NULL,'below',NULL,NULL,'Import Stockbit: 3 lot, market value Rp 30.900, P&L Rp -16.476 (-34.78%)','2026-09-28 16:33:17','2026-10-01 13:43:21'),(8,1,'GOTO','saham','GOTO','Stockbit','Jual Cepat',500.0000,'share',60.6900,28.0000,14000.00,30345.00,NULL,100,4300.00,0.00,0,0,NULL,'below',NULL,NULL,'Import Stockbit: 5 lot, market value Rp 21.500, P&L Rp -8.845 (-29.15%)','2026-09-28 16:33:17','2026-10-01 09:29:49'),(9,1,'MIDI','saham','MIDI','Stockbit','Jual Cepat',100.0000,'share',350.5200,258.0000,25800.00,35052.00,NULL,100,26000.00,0.00,0,0,NULL,'below',NULL,NULL,'Import Stockbit: 1 lot, market value Rp 26.000, P&L Rp -9.052 (-25.83%)','2026-09-28 16:33:17','2026-10-01 12:57:09'),(11,1,'TLKM','saham','TLKM','Stockbit','Saham Tabungan',100.0000,'share',3465.1900,2240.0000,224000.00,346519.00,NULL,100,236000.00,0.00,1,0,NULL,'below',NULL,NULL,'Import Stockbit: 1 lot, market value Rp 236.000, P&L Rp -110.519 (-31.89%)','2026-09-28 16:33:17','2026-10-01 13:41:19'),(12,1,'Saldo RDN','rdn','IDR','RDN','Dana Suplay',2429438.9000,'idr',1.0000,1.0000,2429438.90,2429438.90,NULL,1,0.00,0.00,0,0,NULL,'below',NULL,NULL,'Sisa uang di RDN abis beli saham','2026-09-29 09:44:08','2026-09-30 11:29:34'),(13,1,'Emas Tring','emas','XAU','Tring','Emas Tabungan',1.6468,'gram',29147.0000,23460.0000,3863392.80,4800000.00,24000.0000,1,24440.00,0.00,1,1,24000.0000,'below',NULL,24440.0000,'Template Tring dari konteks lama. Koreksi angka sesuai screenshot.','2026-09-29 10:04:32','2026-10-01 10:33:15'),(14,1,'Majoris Pasar Uang Syariah Indonesia','reksa_dana','','Bibit','Dana Tabungan',3394.0835,'unit',1460.4827,1495.1869,5074789.19,4957000.00,NULL,1,10000.00,0.00,0,0,NULL,'below',NULL,NULL,'AI Vision: Reksa dana pasar uang di Bibit (portofolio Dana Tabungan). Nilai sekarang Rp5.074.789.','2026-09-29 11:39:53','2026-10-01 13:00:00'),(15,1,'Majoris Pasar Uang Syariah Indonesia','reksa_dana','','Bibit','Uang septi',819.0518,'unit',1465.1088,1495.1862,1224634.95,1200000.00,NULL,1,10000.00,0.00,0,0,NULL,'below',NULL,NULL,'Gambar 1: AI Vision: Reksa dana pasar uang pada platform Bibit di dalam portofolio Uang septi','2026-09-30 14:59:02','2026-10-01 13:00:02'),(16,1,'Sucorinvest Sharia Money Market Fund','reksa_dana','','Bibit','Uang septi',1021.2887,'unit',1417.8383,1499.4790,1531400.96,1448022.00,NULL,1,10000.00,0.00,0,0,NULL,'below',NULL,NULL,'Gambar 2: AI Vision:','2026-09-30 14:59:02','2026-10-01 13:00:05'),(17,1,'Sucorinvest Sharia Money Market Fund','reksa_dana','','Bibit','Tabungan Audrey',1082.5710,'unit',1431.8493,1499.4800,1623293.56,1550079.00,NULL,1,10000.00,0.00,0,0,NULL,'below',NULL,NULL,'Gambar 1: AI Vision: Jenis reksa dana: Pasar Uang','2026-09-30 15:04:56','2026-10-01 13:00:07'),(18,1,'Majoris Pasar Uang Syariah Indonesia','reksa_dana','','Bibit','Tabungan Audrey',1558.4758,'unit',1461.9398,1495.1865,2330211.98,2278398.00,NULL,1,10000.00,0.00,0,0,NULL,'below',NULL,NULL,'Gambar 2: AI Vision: Kategori: Reksa Dana Pasar Uang','2026-09-30 15:04:56','2026-10-01 13:00:04');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gold_prices`
--

DROP TABLE IF EXISTS `gold_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gold_prices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `price_date` date NOT NULL,
  `price` decimal(18,2) NOT NULL,
  `buy_price` decimal(18,2) DEFAULT NULL,
  `unit` varchar(20) NOT NULL DEFAULT '0.01',
  `source` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_gold_price_date` (`price_date`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gold_prices`
--

LOCK TABLES `gold_prices` WRITE;
/*!40000 ALTER TABLE `gold_prices` DISABLE KEYS */;
INSERT INTO `gold_prices` VALUES (1,'2026-09-28',24570.00,23580.00,'0.01','Pegadaian/Tring','hargaJual = harga user beli; hargaBeli = harga user jual','2026-09-28 15:07:09'),(3,'2026-09-29',24360.00,23380.00,'0.01','Pegadaian/Tring','hargaJual = harga user beli; hargaBeli = harga user jual','2026-09-29 09:00:00'),(4,'2026-09-30',24570.00,23580.00,'0.01','Pegadaian/Tring','hargaJual = harga user beli; hargaBeli = harga user jual','2026-09-30 11:10:59'),(5,'2026-10-01',24440.00,23460.00,'0.01','Pegadaian/Tring','hargaJual = harga user beli; hargaBeli = harga user jual','2026-10-01 09:29:50');
/*!40000 ALTER TABLE `gold_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolio_import_items`
--

DROP TABLE IF EXISTS `portfolio_import_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_import_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `import_id` int(10) unsigned NOT NULL,
  `asset_type` enum('emas','saham','reksa_dana','crypto','properti','kas','rdn','lainnya') NOT NULL DEFAULT 'lainnya',
  `platform` varchar(80) DEFAULT NULL,
  `portfolio_name` varchar(120) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `symbol` varchar(30) DEFAULT NULL,
  `quantity` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `unit` varchar(20) NOT NULL DEFAULT 'unit',
  `avg_price` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `market_price` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `invested_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `confidence` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_import_id` (`import_id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio_import_items`
--

LOCK TABLES `portfolio_import_items` WRITE;
/*!40000 ALTER TABLE `portfolio_import_items` DISABLE KEYS */;
INSERT INTO `portfolio_import_items` VALUES (1,1,'emas','Stockbit',NULL,'Emas Tring','XAU',1.6468,'gram',29147.0000,24570.0000,4800000.00,40,'Contoh dari konteks lama. Koreksi dulu sebelum simpan.','2026-09-28 15:39:36'),(2,2,'emas','Tring',NULL,'Emas Tring','XAU',1.6468,'gram',29147.0000,24570.0000,4800000.00,55,'Template Tring dari konteks lama. Koreksi angka sesuai screenshot.','2026-09-28 16:20:03'),(3,3,'saham','Stockbit',NULL,'BBCA','BBCA',400.0000,'share',6453.4100,6200.0000,2581366.00,85,'Import Stockbit: 4 lot, market value Rp 2.480.000, P&L Rp -101.366 (-3.93%)','2026-09-28 16:20:34'),(4,3,'saham','Stockbit',NULL,'BBRI','BBRI',100.0000,'share',3845.7600,3150.0000,384576.00,85,'Import Stockbit: 1 lot, market value Rp 315.000, P&L Rp -69.576 (-18.09%)','2026-09-28 16:20:34'),(5,3,'saham','Stockbit',NULL,'BUKA','BUKA',300.0000,'share',157.9200,103.0000,47376.00,85,'Import Stockbit: 3 lot, market value Rp 30.900, P&L Rp -16.476 (-34.78%)','2026-09-28 16:20:34'),(6,3,'saham','Stockbit',NULL,'GOTO','GOTO',500.0000,'share',60.6900,43.0000,30345.00,85,'Import Stockbit: 5 lot, market value Rp 21.500, P&L Rp -8.845 (-29.15%)','2026-09-28 16:20:34'),(7,3,'saham','Stockbit',NULL,'MIDI','MIDI',100.0000,'share',350.5200,260.0000,35052.00,85,'Import Stockbit: 1 lot, market value Rp 26.000, P&L Rp -9.052 (-25.83%)','2026-09-28 16:20:34'),(8,3,'saham','Stockbit',NULL,'REAL','REAL',10200.0000,'share',50.9900,43.0000,520179.00,85,'Import Stockbit: 102 lot, market value Rp 438.600, P&L Rp -81.579 (-15.68%)','2026-09-28 16:20:34'),(9,3,'saham','Stockbit',NULL,'TLKM','TLKM',100.0000,'share',3465.1900,2360.0000,346519.00,85,'Import Stockbit: 1 lot, market value Rp 236.000, P&L Rp -110.519 (-31.89%)','2026-09-28 16:20:34'),(10,4,'saham','Stockbit',NULL,'BBCA','BBCA',400.0000,'share',6453.4100,6200.0000,2581366.00,85,'Import Stockbit: 4 lot, market value Rp 2.480.000, P&L Rp -101.366 (-3.93%)','2026-09-28 16:31:01'),(11,4,'saham','Stockbit',NULL,'BBRI','BBRI',100.0000,'share',3845.7600,3150.0000,384576.00,85,'Import Stockbit: 1 lot, market value Rp 315.000, P&L Rp -69.576 (-18.09%)','2026-09-28 16:31:01'),(12,4,'saham','Stockbit',NULL,'BUKA','BUKA',300.0000,'share',157.9200,103.0000,47376.00,85,'Import Stockbit: 3 lot, market value Rp 30.900, P&L Rp -16.476 (-34.78%)','2026-09-28 16:31:01'),(13,4,'saham','Stockbit',NULL,'GOTO','GOTO',500.0000,'share',60.6900,43.0000,30345.00,85,'Import Stockbit: 5 lot, market value Rp 21.500, P&L Rp -8.845 (-29.15%)','2026-09-28 16:31:01'),(14,4,'saham','Stockbit',NULL,'MIDI','MIDI',100.0000,'share',350.5200,260.0000,35052.00,85,'Import Stockbit: 1 lot, market value Rp 26.000, P&L Rp -9.052 (-25.83%)','2026-09-28 16:31:01'),(15,4,'saham','Stockbit',NULL,'REAL','REAL',10200.0000,'share',50.9900,43.0000,520179.00,85,'Import Stockbit: 102 lot, market value Rp 438.600, P&L Rp -81.579 (-15.68%)','2026-09-28 16:31:01'),(16,4,'saham','Stockbit',NULL,'TLKM','TLKM',100.0000,'share',3465.1900,2360.0000,346519.00,85,'Import Stockbit: 1 lot, market value Rp 236.000, P&L Rp -110.519 (-31.89%)','2026-09-28 16:31:01'),(24,5,'saham','Stockbit',NULL,'BBCA','BBCA',4000000.0000,'share',645341.0000,6200.0000,2581366.00,100,'Import Stockbit: 4 lot, market value Rp 2.480.000, P&L Rp -101.366 (-3.93%)','2026-09-28 16:33:17'),(25,5,'saham','Stockbit',NULL,'BBRI','BBRI',1000000.0000,'share',384576.0000,3150.0000,384576.00,100,'Import Stockbit: 1 lot, market value Rp 315.000, P&L Rp -69.576 (-18.09%)','2026-09-28 16:33:17'),(26,5,'saham','Stockbit',NULL,'BUKA','BUKA',3000000.0000,'share',15792.0000,103.0000,47376.00,100,'Import Stockbit: 3 lot, market value Rp 30.900, P&L Rp -16.476 (-34.78%)','2026-09-28 16:33:17'),(27,5,'saham','Stockbit',NULL,'GOTO','GOTO',5000000.0000,'share',6069.0000,43.0000,30345.00,100,'Import Stockbit: 5 lot, market value Rp 21.500, P&L Rp -8.845 (-29.15%)','2026-09-28 16:33:17'),(28,5,'saham','Stockbit',NULL,'MIDI','MIDI',1000000.0000,'share',35052.0000,260.0000,35052.00,100,'Import Stockbit: 1 lot, market value Rp 26.000, P&L Rp -9.052 (-25.83%)','2026-09-28 16:33:17'),(29,5,'saham','Stockbit',NULL,'REAL','REAL',102000000.0000,'share',5099.0000,43.0000,520179.00,100,'Import Stockbit: 102 lot, market value Rp 438.600, P&L Rp -81.579 (-15.68%)','2026-09-28 16:33:17'),(30,5,'saham','Stockbit',NULL,'TLKM','TLKM',1000000.0000,'share',346519.0000,2360.0000,346519.00,100,'Import Stockbit: 1 lot, market value Rp 236.000, P&L Rp -110.519 (-31.89%)','2026-09-28 16:33:17'),(32,6,'emas','Tring','','Emas Tring','XAU',1.6468,'gram',29147.0000,24570.0000,4800000.00,100,'Template Tring dari konteks lama. Koreksi angka sesuai screenshot.','2026-09-29 10:04:32'),(33,12,'saham','Stockbit',NULL,'BBCA','BBCA',400.0000,'share',6453.4100,6200.0000,2581366.00,85,'Import Stockbit: 4 lot, market value Rp 2.480.000, P&L Rp -101.366 (-3.93%)','2026-09-29 10:14:55'),(34,12,'saham','Stockbit',NULL,'BBRI','BBRI',100.0000,'share',3845.7600,3150.0000,384576.00,85,'Import Stockbit: 1 lot, market value Rp 315.000, P&L Rp -69.576 (-18.09%)','2026-09-29 10:14:55'),(35,12,'saham','Stockbit',NULL,'BUKA','BUKA',300.0000,'share',157.9200,103.0000,47376.00,85,'Import Stockbit: 3 lot, market value Rp 30.900, P&L Rp -16.476 (-34.78%)','2026-09-29 10:14:55'),(36,12,'saham','Stockbit',NULL,'GOTO','GOTO',500.0000,'share',60.6900,43.0000,30345.00,85,'Import Stockbit: 5 lot, market value Rp 21.500, P&L Rp -8.845 (-29.15%)','2026-09-29 10:14:55'),(37,12,'saham','Stockbit',NULL,'MIDI','MIDI',100.0000,'share',350.5200,260.0000,35052.00,85,'Import Stockbit: 1 lot, market value Rp 26.000, P&L Rp -9.052 (-25.83%)','2026-09-29 10:14:55'),(38,12,'saham','Stockbit',NULL,'REAL','REAL',10200.0000,'share',50.9900,43.0000,520179.00,85,'Import Stockbit: 102 lot, market value Rp 438.600, P&L Rp -81.579 (-15.68%)','2026-09-29 10:14:55'),(39,12,'saham','Stockbit',NULL,'TLKM','TLKM',100.0000,'share',3465.1900,2360.0000,346519.00,85,'Import Stockbit: 1 lot, market value Rp 236.000, P&L Rp -110.519 (-31.89%)','2026-09-29 10:14:55'),(40,13,'saham','Stockbit',NULL,'BBCA','BBCA',400.0000,'share',6453.4100,6200.0000,2581366.00,85,'Import Stockbit: 4 lot, market value Rp 2.480.000, P&L Rp -101.366 (-3.93%)','2026-09-29 10:15:04'),(41,13,'saham','Stockbit',NULL,'BBRI','BBRI',100.0000,'share',3845.7600,3150.0000,384576.00,85,'Import Stockbit: 1 lot, market value Rp 315.000, P&L Rp -69.576 (-18.09%)','2026-09-29 10:15:04'),(42,13,'saham','Stockbit',NULL,'BUKA','BUKA',300.0000,'share',157.9200,103.0000,47376.00,85,'Import Stockbit: 3 lot, market value Rp 30.900, P&L Rp -16.476 (-34.78%)','2026-09-29 10:15:04'),(43,13,'saham','Stockbit',NULL,'GOTO','GOTO',500.0000,'share',60.6900,43.0000,30345.00,85,'Import Stockbit: 5 lot, market value Rp 21.500, P&L Rp -8.845 (-29.15%)','2026-09-29 10:15:04'),(44,13,'saham','Stockbit',NULL,'MIDI','MIDI',100.0000,'share',350.5200,260.0000,35052.00,85,'Import Stockbit: 1 lot, market value Rp 26.000, P&L Rp -9.052 (-25.83%)','2026-09-29 10:15:04'),(45,13,'saham','Stockbit',NULL,'REAL','REAL',10200.0000,'share',50.9900,43.0000,520179.00,85,'Import Stockbit: 102 lot, market value Rp 438.600, P&L Rp -81.579 (-15.68%)','2026-09-29 10:15:04'),(46,13,'saham','Stockbit',NULL,'TLKM','TLKM',100.0000,'share',3465.1900,2360.0000,346519.00,85,'Import Stockbit: 1 lot, market value Rp 236.000, P&L Rp -110.519 (-31.89%)','2026-09-29 10:15:04'),(47,14,'emas','Tring',NULL,'Emas Tring','XAU',1.6468,'gram',29147.0000,24570.0000,4800000.00,55,'Template Tring dari konteks lama. Koreksi angka sesuai screenshot.','2026-09-29 10:15:14'),(55,15,'reksa_dana','Bibit','','Sucorinvest Sharia Money Market Fund','',1021.2887,'unit',1417.8400,1499.4800,1448022.00,90,'Import Bibit: Pasar Uang, nilai sekarang Rp 1.531.401, keuntungan Rp 83.379.','2026-09-29 10:18:25'),(59,16,'reksa_dana','Bibit','Dana Tabungan','Majoris Pasar Uang Syariah Indonesia','',3394.0835,'unit',1460.4800,1495.1900,4957000.00,76,'Import Bibit: Pasar Uang, nilai sekarang Rp 5.074.789, keuntungan Rp 117.789.','2026-09-29 10:38:02'),(60,17,'reksa_dana','Bibit','Uang Septi','Majoris Pasar Uang Syariah Indonesia','',819.0518,'unit',1465.1100,1495.1900,1200000.00,76,'Import Bibit: Pasar Uang, nilai sekarang Rp 1.224.635, keuntungan Rp 24.635.','2026-09-29 10:38:02'),(61,18,'reksa_dana','Bibit','Uang Septi','Majoris Pasar Uang Syariah Indonesia','',819.0518,'unit',1465.1100,1495.1900,1200000.00,76,'Import Bibit: Pasar Uang, nilai sekarang Rp 1.224.635, keuntungan Rp 24.635.','2026-09-29 10:38:03'),(62,19,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'Belum terbaca otomatis. Isi manual dari screenshot.','2026-09-29 10:39:15'),(63,20,'lainnya','Bibit','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'Belum terbaca otomatis. Isi manual dari screenshot.','2026-09-29 10:39:26'),(64,21,'lainnya','Bibit','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'Belum terbaca otomatis. Isi manual dari screenshot.','2026-09-29 10:40:29'),(65,22,'lainnya','Bibit','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'Belum terbaca otomatis. Isi manual dari screenshot.','2026-09-29 10:47:30'),(66,23,'lainnya','Bibit','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'Belum terbaca otomatis. Isi manual dari screenshot.','2026-09-29 10:48:10'),(67,24,'lainnya','Bibit','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 10:50:45'),(68,25,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI belum aktif atau API key belum diisi. OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:19:13'),(69,26,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI belum aktif atau API key belum diisi. OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:19:24'),(70,27,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI belum aktif atau API key belum diisi. OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:22:29'),(71,28,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: SSL certificate problem: unable to get local issuer certificate OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:22:57'),(72,29,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI API gagal. HTTP 404: {\n  \"error\": {\n    \"code\": 404,\n    \"message\": \"This model models/gemini-2.5-flash is no longer available to new users. Please update your code to use models/gemini-3.8-flash for the latest features and improvements. We recommend you to use OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:31:59'),(73,30,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI API gagal. HTTP 429: {\n  \"error\": {\n    \"message\": \"You have no credits remaining. Add credits to continue using the API at https://platform.openai.com/settings/organization/billing/.\",\n    \"type\": \"insufficient_quota\",\n    \"param\": null,\n    \"code\": \"credit_ba OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:33:41'),(74,31,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI API gagal. HTTP 429: {\n  \"error\": {\n    \"message\": \"You have no credits remaining. Add credits to continue using the API at https://platform.openai.com/settings/organization/billing/.\",\n    \"type\": \"insufficient_quota\",\n    \"param\": null,\n    \"code\": \"credit_ba OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:34:31'),(75,32,'reksa_dana','Bibit','Uang septi','Majoris Pasar Uang Syariah Indonesia','',819.0518,'unit',1465.1088,1495.1900,1200000.00,0,'AI Vision: Nilai sekarang: Rp1.224.635','2026-09-29 11:35:31'),(76,32,'reksa_dana','Bibit','Uang septi','Sucorinvest Sharia Money Market Fund','',0.0000,'unit',0.0000,0.0000,1550079.00,0,'AI Vision: Sebagian data unit dan harga beli terpotong di bagian bawah. Nilai sekarang: Rp1.623.293','2026-09-29 11:35:31'),(78,33,'reksa_dana','Bibit','Dana Tabungan','Majoris Pasar Uang Syariah Indonesia','',3394.0835,'unit',1460.4827,1495.1869,4957000.00,100,'AI Vision: Reksa dana pasar uang di Bibit (portofolio Dana Tabungan). Nilai sekarang Rp5.074.789.','2026-09-29 11:39:53'),(79,34,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:40:28'),(80,35,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:40:42'),(81,36,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 11:41:11'),(82,37,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: gemini-3.7-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n | gemini-3.8-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n | gemini-flash-latest: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n | gemini-3.6-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 13:22:36'),(83,38,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: gemini-3.6-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n | gemini-3.8-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n | gemini-flash-latest: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n | gemini-3.7-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again later.\",\n    \"status\": \"UNAVAILABLE\"\n  }\n}\n OCR tidak membaca teks dari gambar. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-29 13:23:53'),(84,39,'reksa_dana','Bibit','Uang septi','Majoris Pasar Uang Syariah Indonesia','',819.0518,'unit',1465.1088,1495.1863,1200000.00,0,'AI Vision: ','2026-09-30 14:47:18'),(85,39,'reksa_dana','Bibit','Uang septi','Sucorinvest Sharia Money Market Fund','',0.0000,'unit',0.0000,0.0000,1550079.00,0,'AI Vision: Rincian harga beli dan jumlah unit terpotong di bagian bawah gambar','2026-09-30 14:47:18'),(86,40,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: gemini-3.6-flash: Operation timed out after 12004 milliseconds with 0 bytes received OCR tidak membaca teks dari gambar 1. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-30 14:51:06'),(87,40,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: JSON AI gagal dibaca: [\n  {\n    \"asset_type\": \"reksa_dana\",\n    \"platform\": \"Bibit\",\n    \"portfolio_name\": \"Uang septi\",\n    \"name\": \"Majoris Pasar Uang Syariah Indonesia\",\n    \"symbol\": \"\",\n    \"quantity\": 819.0518,\n    \"unit\": \"unit\",\n    \" OCR tidak membaca teks dari gambar 2. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-30 14:51:06'),(88,41,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: JSON AI gagal dibaca: [\n  {\n    \"asset_type\": \"reksa_dana\",\n    \"platform\": \"Bibit\",\n    \"portfolio_name\": \"Pasar Uang\",\n    \"name\": \"Reksa Dana Pasar Uang (Terhalang Notifikasi)\",\n    \"symbol\": \"\",\n    \"quantity\": 1 OCR tidak membaca teks dari gambar 1. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-30 14:51:48'),(89,41,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI: JSON AI gagal dibaca: [\n  {\n    \"asset_type\": \"reksa_dana\",\n    \"platform\": \"Bibit\",\n    \"portfolio_name\": \"Uang septi\",\n    \"name\": \"Majoris Pasar Uang Syariah Indonesia\",\n    \"symbol\": \"\",\n    \"quantity\": 819. OCR tidak membaca teks dari gambar 2. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-30 14:51:48'),(90,42,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI belum menghasilkan JSON lengkap. OCR tidak membaca teks dari gambar 1. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-30 14:56:41'),(91,42,'reksa_dana','Bibit','Uang septi','Majoris Pasar Uang Syariah Indonesia','',819.0518,'unit',1465.1088,1495.1863,1200000.00,0,'Gambar 2: AI Vision: Reksa dana pasar uang syariah','2026-09-30 14:56:41'),(92,42,'reksa_dana','Bibit','Uang septi','Sucorinvest Sharia Money Market Fund','',0.0000,'unit',0.0000,0.0000,1550079.00,0,'Gambar 2: AI Vision: Jumlah unit dan harga beli terpotong pada tampilan screenshot; nilai sekarang Rp1.623.293','2026-09-30 14:56:41'),(95,43,'reksa_dana','Bibit','Uang septi','Majoris Pasar Uang Syariah Indonesia','',819.0518,'unit',1465.1088,1495.1862,1200000.00,100,'Gambar 1: AI Vision: Reksa dana pasar uang pada platform Bibit di dalam portofolio Uang septi','2026-09-30 14:59:02'),(96,43,'reksa_dana','Bibit','Uang septi','Sucorinvest Sharia Money Market Fund','',1021.2887,'unit',1417.8383,1499.4790,1448022.00,100,'Gambar 2: AI Vision:','2026-09-30 14:59:02'),(97,44,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI belum menghasilkan JSON lengkap. OCR tidak membaca teks dari gambar 1. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-30 15:00:24'),(98,44,'reksa_dana','Bibit','Uang thr audrey','Majoris Pasar Uang Syariah Indonesia','',1558.4758,'unit',1461.9398,1495.1865,2278398.00,0,'Gambar 2: AI Vision: Ekstraksi dari screenshot aplikasi Bibit untuk reksa dana pasar uang.','2026-09-30 15:00:24'),(99,45,'reksa_dana','Bibit','Pasar Uang','Sucorinvest Sharia Money Market Fund','',1082.5710,'unit',1431.8493,1499.4794,1550079.00,0,'Gambar 1: AI Vision:','2026-09-30 15:01:10'),(100,45,'reksa_dana','Bibit','Uang thr audrey','Majoris Pasar Uang Syariah Indonesia','',1558.4758,'unit',1461.9398,1495.1865,2278398.00,0,'Gambar 2: AI Vision: market_price dihitung dari Nilai Sekarang (2.330.212) / Jumlah Unit (1.558,4758)','2026-09-30 15:01:10'),(101,46,'lainnya','','','','',0.0000,'unit',0.0000,0.0000,0.00,0,'AI belum menghasilkan JSON lengkap. OCR tidak membaca teks dari gambar 1. Coba crop screenshot lebih dekat ke kartu aset lalu upload ulang.','2026-09-30 15:02:41'),(102,46,'reksa_dana','Bibit','Uang thr audrey','Majoris Pasar Uang Syariah Indonesia','',1558.4758,'unit',1461.9398,1495.1865,2278398.00,0,'Gambar 2: AI Vision: Reksa dana pasar uang di Bibit pada portofolio Uang thr audrey','2026-09-30 15:02:41'),(105,47,'reksa_dana','Bibit','Uang thr audrey','Sucorinvest Sharia Money Market Fund','',1082.5710,'unit',1431.8493,1499.4800,1550079.00,100,'Gambar 1: AI Vision: Jenis reksa dana: Pasar Uang','2026-09-30 15:04:56'),(106,47,'reksa_dana','Bibit','Uang thr audrey','Majoris Pasar Uang Syariah Indonesia','',1558.4758,'unit',1461.9398,1495.1865,2278398.00,100,'Gambar 2: AI Vision: Kategori: Reksa Dana Pasar Uang','2026-09-30 15:04:56');
/*!40000 ALTER TABLE `portfolio_import_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `portfolio_imports`
--

DROP TABLE IF EXISTS `portfolio_imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `portfolio_imports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `source_type` enum('screenshot','manual') NOT NULL DEFAULT 'manual',
  `platform` varchar(80) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `processor_type` enum('ai','ocr') NOT NULL DEFAULT 'ocr',
  `processor_name` varchar(120) DEFAULT NULL,
  `processor_model` varchar(120) DEFAULT NULL,
  `processor_notes` varchar(255) DEFAULT NULL,
  `status` enum('review','saved') NOT NULL DEFAULT 'review',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_imports_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio_imports`
--

LOCK TABLES `portfolio_imports` WRITE;
/*!40000 ALTER TABLE `portfolio_imports` DISABLE KEYS */;
INSERT INTO `portfolio_imports` VALUES (1,1,'screenshot','Stockbit','uploads/imports/8f1885300e83572bd0d58691bd4b9b34.png','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-28 15:39:36'),(2,1,'screenshot','Tring','uploads/imports/652cbbaa2a6209eb5412eaf9f1e61943.png','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-28 16:20:03'),(3,1,'screenshot','Stockbit','uploads/imports/8a77cf94a52f2df485a7ab95e64e3529.png','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-28 16:20:34'),(4,1,'screenshot','Stockbit','uploads/imports/f881d2f7794d5122631012967315cbfe.png','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-28 16:31:00'),(5,1,'screenshot','Stockbit','uploads/imports/d0ae2d3ac0bccbc0b77db276a7b0820f.png','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'saved','2026-09-28 16:33:09'),(6,1,'screenshot','Tring','uploads/imports/a5f5c2e558989b6bbe1c687bad79f341.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'saved','2026-09-29 10:04:08'),(7,1,'screenshot','Bibit','uploads/imports/7448606891b993307c1f4bf2c9423fd7.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:05:10'),(8,1,'screenshot','Bibit','uploads/imports/5fe13b2d993b86f1bc0001e6d16dd9bb.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:05:22'),(9,1,'screenshot','Bibit','uploads/imports/8651d1b18db806ef1d3670656b1530b5.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:13:59'),(10,1,'screenshot','Bibit','uploads/imports/fc55b8d69ac61b585c9fc8c0565f92ae.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:14:17'),(11,1,'screenshot','Bareksa','uploads/imports/7c8729a3cca610093495de6715dc6d68.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:14:28'),(12,1,'screenshot','Stockbit','uploads/imports/377dae12f0d09bd298fb36123f86d880.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:14:55'),(13,1,'screenshot','Stockbit','uploads/imports/6b61d748299e2fbe273f02b2ee27d6d2.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:15:04'),(14,1,'screenshot','Tring','uploads/imports/ffab6e74d4706ee7b632139179bf3d80.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:15:14'),(15,1,'screenshot','Stockbit','uploads/imports/16746f2e141a0c21b0d0fb9c76b6321a.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:15:34'),(16,1,'screenshot','','uploads/imports/51051f532739716fe8aa021fc4345de4.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:30:08'),(17,1,'screenshot','Bibit','uploads/imports/382bcfffc272929e26d8c3e0d10b3236.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:30:24'),(18,1,'screenshot','','uploads/imports/e057fd1d6f636bde37b06afcc60c976f.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:30:42'),(19,1,'screenshot','','uploads/imports/10def75914a9b0621655356f4e85d2f9.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:39:14'),(20,1,'screenshot','Bibit','uploads/imports/c9f7b8caa3352dbd5de62dec267bdb06.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:39:26'),(21,1,'screenshot','Bibit','uploads/imports/1ef7b707a28f89ffbec02cbc3c4d42dd.jpeg','ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:40:29'),(22,1,'screenshot','Bibit',NULL,'ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:47:30'),(23,1,'screenshot','Bibit',NULL,'ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:48:10'),(24,1,'screenshot','Bibit',NULL,'ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 10:50:45'),(25,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 11:19:13'),(26,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 11:19:24'),(27,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 11:22:29'),(28,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal',NULL,'review','2026-09-29 11:22:57'),(29,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: AI API gagal. HTTP 404: {\n  \"error\": {\n    \"code\": 404,\n    \"message\": \"This model models/gemini-2.5-flash is no longer available to new users. Please update your code to use model Fallback ke OCR lokal.','review','2026-09-29 11:31:59'),(30,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: AI API gagal. HTTP 429: {\n  \"error\": {\n    \"message\": \"You have no credits remaining. Add credits to continue using the API at https://platform.openai.com/settings/organization/bil Fallback ke OCR lokal.','review','2026-09-29 11:33:41'),(31,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: AI API gagal. HTTP 429: {\n  \"error\": {\n    \"message\": \"You have no credits remaining. Add credits to continue using the API at https://platform.openai.com/settings/organization/bil Fallback ke OCR lokal.','review','2026-09-29 11:34:31'),(32,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-flash-latest','Diproses dengan AI Vision.','review','2026-09-29 11:35:31'),(33,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-flash-latest','Diproses dengan AI Vision.','saved','2026-09-29 11:36:55'),(34,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again l Fallback ke OCR lokal.','review','2026-09-29 11:40:28'),(35,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again l Fallback ke OCR lokal.','review','2026-09-29 11:40:42'),(36,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary. Please try again l Fallback ke OCR lokal.','review','2026-09-29 11:41:11'),(37,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: gemini-3.7-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary.  Fallback ke OCR lokal.','review','2026-09-29 13:22:36'),(38,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','AI tidak dipakai/ gagal: gemini-3.6-flash: AI API gagal. HTTP 503: {\n  \"error\": {\n    \"code\": 503,\n    \"message\": \"This model is currently experiencing high demand. Spikes in demand are usually temporary.  Fallback ke OCR lokal.','review','2026-09-29 13:23:53'),(39,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-3.6-flash','Diproses dengan AI Vision.','review','2026-09-30 14:47:18'),(40,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','Diproses 2 gambar. AI: 0, OCR lokal: 2. Gambar 1 AI gagal: gemini-3.6-flash: Operation timed out after 12004 milliseconds with 0 bytes received Fallback OCR lokal. | Gambar 2 AI gagal: JSON AI gagal dibaca: [\n  {\n    \"asset_type\": \"reksa_dana\",\n    \"platf','review','2026-09-30 14:51:06'),(41,1,'screenshot','',NULL,'ocr','OCR lokal','Windows OCR + parser lokal','Diproses 2 gambar. AI: 0, OCR lokal: 2. Gambar 1 AI gagal: JSON AI gagal dibaca: [\n  {\n    \"asset_type\": \"reksa_dana\",\n    \"platform\": \"Bibit\",\n    \"portfolio_name\": \"Pasar Uang\", Fallback OCR lokal. | Gambar 2 AI gagal: JSON AI gagal dibaca: [\n  {\n    \"a','review','2026-09-30 14:51:48'),(42,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-3.6-flash','Diproses 2 gambar. AI: 1, OCR lokal: 1. Gambar 1 AI belum menghasilkan JSON lengkap. Fallback OCR lokal. | Gambar 2 diproses dengan AI Vision.','review','2026-09-30 14:56:41'),(43,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-3.6-flash','Diproses 2 gambar. AI: 2, OCR lokal: 0. Gambar 1 diproses dengan AI Vision. | Gambar 2 diproses dengan AI Vision.','saved','2026-09-30 14:58:28'),(44,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-3.6-flash','Diproses 2 gambar. AI: 1, OCR lokal: 1. Gambar 1 AI belum menghasilkan JSON lengkap. Fallback OCR lokal. | Gambar 2 diproses dengan AI Vision.','review','2026-09-30 15:00:24'),(45,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-3.6-flash','Diproses 2 gambar. AI: 2, OCR lokal: 0. Gambar 1 diproses dengan AI Vision. | Gambar 2 diproses dengan AI Vision.','review','2026-09-30 15:01:10'),(46,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-3.6-flash','Diproses 2 gambar. AI: 1, OCR lokal: 1. Gambar 1 AI belum menghasilkan JSON lengkap. Fallback OCR lokal. | Gambar 2 diproses dengan AI Vision.','review','2026-09-30 15:02:41'),(47,1,'screenshot','',NULL,'ai','Gemini / Google AI Studio','gemini-3.6-flash','Diproses 2 gambar. AI: 2, OCR lokal: 0. Gambar 1 diproses dengan AI Vision. | Gambar 2 diproses dengan AI Vision.','saved','2026-09-30 15:04:09');
/*!40000 ALTER TABLE `portfolio_imports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` enum('buy','sell') NOT NULL,
  `quantity` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `price` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `fee` decimal(18,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_transactions_asset` (`asset_id`),
  CONSTRAINT `fk_transactions_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Andrey','Andreyzoelfa@gmail.com','$2y$10$nDsm46P6S19kkn5rY6xkyutiUxf428EWPvv3itam.4F8xEPLKn1RC','2026-09-28 15:35:24');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-01 13:44:00
