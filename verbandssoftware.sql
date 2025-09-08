-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 08. Sep 2025 um 07:48
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `verbandssoftware`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rollen_id` int(11) NOT NULL,
  `angelegt_am` datetime NOT NULL DEFAULT current_timestamp(),
  `geaendert_am` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `aktiv` tinyint(1) NOT NULL DEFAULT 1,
  `vorname` varchar(100) DEFAULT NULL,
  `nachname` varchar(100) DEFAULT NULL,
  `strasse` varchar(150) DEFAULT NULL,
  `hausnummer` varchar(20) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(100) DEFAULT NULL,
  `sepa_zustimmung` tinyint(1) DEFAULT 0,
  `kreditinstitut` varchar(150) DEFAULT NULL,
  `kontoinhaber` varchar(150) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `bic` varchar(11) DEFAULT NULL,
  `sepamandatsreferenz` varchar(100) DEFAULT NULL,
  `vereinsmitgliedschaft` varchar(150) DEFAULT NULL,
  `einzelmitglied` tinyint(1) DEFAULT 0,
  `familieneinzelmitglied` tinyint(1) DEFAULT 0,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `admins`
--

INSERT INTO `admins` (`id`, `email`, `password_hash`, `rollen_id`, `angelegt_am`, `geaendert_am`, `aktiv`, `vorname`, `nachname`, `strasse`, `hausnummer`, `plz`, `ort`, `sepa_zustimmung`, `kreditinstitut`, `kontoinhaber`, `iban`, `bic`, `sepamandatsreferenz`, `vereinsmitgliedschaft`, `einzelmitglied`, `familieneinzelmitglied`, `parent_id`) VALUES
(11, 'c.wulf@voelsing.de', '$2y$10$YFPrLrInJ5IPozbbeZFMOe9tmgMaqOQf9aVoWqRmHlljHS9aXfaVO', 2, '2025-06-03 13:12:56', '2025-07-07 21:01:07', 1, 'Christian', 'Wulf', 'Dechant-Bluel-Strasse', '15', '31180', 'Giesen', 1, 'Volksbank Hildesheim', 'Christian Wulf', 'DE06251933310005498400', '05498400', '123456789', NULL, 0, 0, NULL),
(17, 'kanusegeln@kanu-niedersachsen.de', '$2y$10$ilvgkCJaVCu9h08R8qbBDeu0N8DegucsRkS/k8xzQ2Ms3Iq9S0O.6', 11, '2025-06-07 23:10:38', '2025-07-02 08:25:33', 1, 'Birgit', 'Altvater', 'Ankerweg', '1a', '30453', 'Neustadt', 0, '', '', '', '', '', NULL, 0, 0, NULL),
(20, 'slalom@kanu-nds.de', '$2y$10$7f2IaPTu.R3lB.VszjXFWujLFST0IaB1UkuNkN2TobyB5WmETZ78G', 11, '2025-07-02 08:41:46', '2025-07-02 08:49:12', 1, 'Frank', 'Jahns', 'Hinter', '12', '31180', 'giesen', 1, 'voba Hildesheim', 'Christian Wulf', 'DE06251933310005498400', '05498400', '123654', NULL, 0, 0, NULL),
(21, 'slalom_trainer@kanu-nds.de', '$2y$10$CO7ClcLFDzYaqCXQO3qYWugg3Fw9P52m4iqKMx./Wio1fWubtoWga', 12, '2025-07-02 08:52:25', '2025-07-02 08:57:54', 1, 'Raphael', 'Schubert', 'Am Spitzhut', '12', '31134', 'Hildesheim', 0, '', '', '', '', NULL, NULL, 0, 0, NULL),
(23, 'm.baumhoefener@voelsing.de', '$2y$10$QHrnHDmvXs7GO8JhoPXcs.9ykjyDBC0N27dprl.CSycQo73i5uy2i', 2, '2025-08-11 10:12:18', '2025-08-11 10:12:26', 1, '', '', '', '', '', '', 0, '', '', '', '', NULL, NULL, 0, 0, NULL),
(24, 'leistungssport@kanu-niedersachsen.de', '$2y$10$wQyG1fD.giOc1AZPtW1GFu1ueLnOKKYwt/SvK9vbKfQljiT7voaF.', 11, '2025-08-12 16:27:09', '2025-08-19 10:53:04', 1, 'Andi', 'Wambach', 'Fontainestrasse', '', '', '', 1, 'Volksbank Hannover', 'Andreas Wambach', 'DE06251933310005498400', 'VOHADE2H', 'LKV1947002333', NULL, 0, 0, NULL),
(25, 'nachwuchstrainer@kanu-niedersachsen.de', '$2y$10$qwHY5ML5iklfEgboAXU9ne.dhyOUBMt5L2VlTxt8g0lfzzEB/5vnS', 12, '2025-08-12 16:30:34', '2025-08-12 16:31:04', 1, 'Kjell', 'Flechsig', '', '', '', '', 0, '', '', '', '', NULL, NULL, 0, 0, NULL),
(26, 'a.boeshans@voelsing.de', '$2y$10$bqqfkOgFlPw/6qciBAObY.5itZeePBTXaVj4vtuAXfkNSLigDB7Ya', 2, '2025-08-19 15:14:04', '2025-08-19 15:17:37', 1, 'Annabelle', 'Böshans', 'Eschenweg', '8a', '31180', 'Giesen', 1, 'Volksbank Hannover', 'Annabelle Böshans', 'DE06251933310005498400', 'GENODEF1PAT', 'LKV123456789', NULL, 0, 0, NULL),
(27, 'gf@kanu-niedersachsen.de', '$2y$10$D2H8xqQ57RjazPJWg0QgkuPysUXV5t04r9SDvXDvbHPxjFnjsAZSa', 2, '2025-08-19 15:43:13', '2025-08-19 15:44:20', 1, 'Emma', 'Grigull', 'h strasse', '12', '30453', 'Hannover', 0, '', '', '', '', NULL, NULL, 0, 0, NULL),
(28, 'info@kanu-niedersachsen.de', '$2y$10$9rD6d.GfWBiPTQAVwjMUluSAfVnIekM81gO6M.I.qHjEyy0wCGA3O', 15, '2025-08-19 15:47:49', '2025-08-19 16:11:10', 1, 'Annette', 'Behning', '', '', '', '', 0, '', '', '', '', NULL, NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admin_card_order`
--

CREATE TABLE `admin_card_order` (
  `admin_id` int(11) NOT NULL,
  `card_key` varchar(50) NOT NULL,
  `sort_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `admin_card_order`
--

INSERT INTO `admin_card_order` (`admin_id`, `card_key`, `sort_order`) VALUES
(11, 'antraege', 6),
(11, 'artikel_auswertung', 7),
(11, 'artikel_verwalten', 11),
(11, 'ausbildung', 6),
(11, 'ausbildung_fortbildung', 16),
(11, 'auslagen_einreichen', 5),
(11, 'benutzerverwaltung', 8),
(11, 'buchungen_mardorf', 8),
(11, 'buchungsverwaltung', 7),
(11, 'kalender', 9),
(11, 'kundenverwaltung', 2),
(11, 'lehrgang_abrechnung', 1),
(11, 'lehrgang_anlegen', 7),
(11, 'lehrgang_create', 4),
(11, 'lizenzen', 9),
(11, 'lizenzverwaltung', 17),
(11, 'mardorf_vertraege', 10),
(11, 'platz_verwalten', 19),
(11, 'preisliste_pflegen', 13),
(11, 'rechnungen', 3),
(11, 'rollenverwaltung', 14),
(11, 'sportartenverwaltung', 12),
(11, 'Verbandsdatenbank', 8),
(11, 'vereinsdatenbank', 15),
(16, 'antraege', 3),
(16, 'artikel_verwalten', 8),
(16, 'ausbildung_fortbildung', 13),
(16, 'auslagen_einreichen', 2),
(16, 'benutzerverwaltung', 4),
(16, 'buchungen_mardorf', 5),
(16, 'kundenverwaltung', 12),
(16, 'lehrgang_abrechnung', 10),
(16, 'lehrgang_anlegen', 0),
(16, 'lehrgang_create', 14),
(16, 'lizenzverwaltung', 11),
(16, 'preisliste_pflegen', 6),
(16, 'rechnungen', 9),
(16, 'rollenverwaltung', 1),
(16, 'sportartenverwaltung', 7),
(18, 'antraege', 6),
(18, 'artikel_auswertung', 2),
(18, 'artikel_verwalten', 3),
(18, 'ausbildung_fortbildung', 4),
(18, 'auslagen_einreichen', 5),
(18, 'benutzerverwaltung', 7),
(18, 'buchungen_mardorf', 8),
(18, 'buchungsverwaltung', 9),
(18, 'kundenverwaltung', 10),
(18, 'lehrgang_abrechnung', 12),
(18, 'lehrgang_create', 11),
(18, 'lizenzverwaltung', 13),
(18, 'mardorf_vertraege', 18),
(18, 'platz_verwalten', 14),
(18, 'preisliste_pflegen', 15),
(18, 'rechnungen', 1),
(18, 'rollenverwaltung', 16),
(18, 'sportartenverwaltung', 17),
(18, 'vereinsdatenbank', 19),
(22, 'antraege', 5),
(22, 'artikel_auswertung', 15),
(22, 'artikel_verwalten', 13),
(22, 'ausbildung_fortbildung', 9),
(22, 'auslagen_einreichen', 12),
(22, 'benutzerverwaltung', 1),
(22, 'buchungen_mardorf', 8),
(22, 'kundenverwaltung', 16),
(22, 'lehrgang_abrechnung', 7),
(22, 'lehrgang_create', 11),
(22, 'lizenzverwaltung', 10),
(22, 'mardorf_vertraege', 6),
(22, 'preisliste_pflegen', 14),
(22, 'rechnungen', 3),
(22, 'rollenverwaltung', 2),
(22, 'sportartenverwaltung', 4),
(23, 'antraege', 3),
(23, 'artikel_auswertung', 15),
(23, 'artikel_verwalten', 13),
(23, 'ausbildung_fortbildung', 9),
(23, 'auslagen_einreichen', 12),
(23, 'benutzerverwaltung', 1),
(23, 'buchungen_mardorf', 5),
(23, 'buchungsverwaltung', 7),
(23, 'kundenverwaltung', 16),
(23, 'lehrgang_abrechnung', 4),
(23, 'lehrgang_create', 11),
(23, 'lizenzverwaltung', 10),
(23, 'mardorf_vertraege', 8),
(23, 'preisliste_pflegen', 14),
(23, 'rechnungen', 17),
(23, 'rollenverwaltung', 2),
(23, 'sportartenverwaltung', 6),
(23, 'vereinsdatenbank', 18),
(24, 'antraege', 2),
(24, 'artikel_auswertung', 17),
(24, 'artikel_verwalten', 14),
(24, 'ausbildung_fortbildung', 12),
(24, 'auslagen_einreichen', 3),
(24, 'benutzerverwaltung', 1),
(24, 'buchungen_mardorf', 7),
(24, 'buchungsverwaltung', 8),
(24, 'kundenverwaltung', 18),
(24, 'lehrgang_abrechnung', 5),
(24, 'lehrgang_create', 6),
(24, 'lizenzverwaltung', 13),
(24, 'mardorf_vertraege', 11),
(24, 'platz_verwalten', 15),
(24, 'preisliste_pflegen', 16),
(24, 'rechnungen', 9),
(24, 'rollenverwaltung', 10),
(24, 'sportartenverwaltung', 4),
(24, 'vereinsdatenbank', 19),
(26, 'antraege', 5),
(26, 'artikel_auswertung', 2),
(26, 'artikel_verwalten', 3),
(26, 'ausbildung_fortbildung', 16),
(26, 'auslagen_einreichen', 4),
(26, 'benutzerverwaltung', 6),
(26, 'kalender', 7),
(26, 'kundenverwaltung', 8),
(26, 'lehrgang_abrechnung', 9),
(26, 'lehrgang_create', 10),
(26, 'lizenzverwaltung', 17),
(26, 'mardorf_vertraege', 14),
(26, 'preisliste_pflegen', 11),
(26, 'rechnungen', 1),
(26, 'rollenverwaltung', 12),
(26, 'sportartenverwaltung', 13),
(26, 'vereinsdatenbank', 15),
(28, 'antraege', 5),
(28, 'artikel_auswertung', 10),
(28, 'artikel_verwalten', 9),
(28, 'ausbildung_fortbildung', 16),
(28, 'auslagen_einreichen', 8),
(28, 'benutzerverwaltung', 1),
(28, 'buchungen_mardorf', 14),
(28, 'buchungsverwaltung', 15),
(28, 'kundenverwaltung', 2),
(28, 'lehrgang_abrechnung', 6),
(28, 'lehrgang_create', 12),
(28, 'lizenzverwaltung', 19),
(28, 'mardorf_vertraege', 11),
(28, 'platz_verwalten', 18),
(28, 'preisliste_pflegen', 13),
(28, 'rechnungen', 3),
(28, 'rollenverwaltung', 7),
(28, 'sportartenverwaltung', 4),
(28, 'vereinsdatenbank', 17);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admin_column_order`
--

CREATE TABLE `admin_column_order` (
  `admin_id` int(11) NOT NULL,
  `page` varchar(50) NOT NULL,
  `column_order` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admin_column_prefs`
--

CREATE TABLE `admin_column_prefs` (
  `admin_id` int(11) NOT NULL,
  `page` varchar(50) NOT NULL,
  `visible_columns` text DEFAULT NULL,
  `show_credits` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `admin_column_prefs`
--

INSERT INTO `admin_column_prefs` (`admin_id`, `page`, `visible_columns`, `show_credits`) VALUES
(11, 'artikel_auswertung', '{\"0\":1,\"1\":2,\"2\":3,\"3\":4,\"4\":5,\"5\":6,\"6\":7,\"7\":8,\"8\":9,\"9\":10,\"10\":11,\"11\":12,\"columns\":[1,2,3,4,7,8,10],\"order\":{\"summary\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"detail\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13]},\"length\":80}', 0),
(11, 'artikel_verwalten', '{\"columns\":[0,1,2,3,4,5,6,8],\"order\":[0,1,2,3,4,5,6,7,8,9],\"length\":250}', 0),
(11, 'auslagen_verwalten', '{\"columns\":[0,1,2,3,5,6,7,8,10,11,12],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":13,\"sort\":[0,\"asc\"],\"page\":0}', 0),
(11, 'buchungen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9],\"order\":[0,1,2,3,4,5,6,7,8,9,10],\"length\":10,\"sort\":[1,\"desc\"],\"page\":0,\"platz\":\"\",\"name\":\"\",\"past\":0}', 0),
(11, 'dauercamping_filter', '1', 0),
(11, 'kunden_verwalten', '{\"columns\":[0,1,3,5,6,7,13,17,30],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"length\":25}', 0),
(11, 'lehrgang_abrechnung', '{\"columns\":[1,2,3,4,5,6,7,8,9],\"order\":[0,1,2,3,4,5,6,7,8,9],\"length\":10}', 0),
(11, 'preisliste', '{\"length\":13}', 0),
(11, 'rechnungen', '{\"v\":4,\"columns\":[0,2,3,4,5,6,7,10,11,12,13,14,15],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":100}', 0),
(11, 'rechnungen_color', 'F1', 0),
(11, 'rechnungen_filter', '{\"showCorrections\":true,\"showCredits\":true,\"showOffers\":true,\"showOffersOnly\":false,\"showOpen\":true,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0),
(11, 'schuppen_filter', '1', 0),
(11, 'stromzaehler_mardorf', '{\"length\":7}', 0),
(11, 'stromzaehler_parent_mardorf', '{\"length\":1}', 0),
(11, 'vertraege', '{\"columns\":[0,1,7,8,12,20,21,22,26,27],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27],\"length\":106}', 0),
(11, 'vorgelaende_filter', '1', 0),
(16, 'artikel_verwalten', '{\"columns\":[0,1,2,3,4,5,6,8],\"order\":[0,1,2,3,4,5,6,7,8,9],\"length\":250}', 0),
(16, 'auslagen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,10,11],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0}', 0),
(16, 'buchungen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10],\"order\":[0,1,2,3,4,5,6,7,8,9,10],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0,\"platz\":\"\",\"name\":\"\",\"past\":0}', 0),
(16, 'kunden_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29]}', 0),
(16, 'rechnungen', '{\"v\":4,\"columns\":[2,3,4,5,6,7,8,10,11,14,15],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":10}', 0),
(16, 'rechnungen_filter', '{\"showCorrections\":false,\"showCredits\":false,\"showOffers\":false,\"showOffersOnly\":false,\"showOpen\":true,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0),
(18, 'artikel_auswertung', '{\"columns\":[1,2,4,5,6,7,8,11,12],\"order\":{\"summary\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"detail\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13]},\"length\":10}', 0),
(18, 'artikel_verwalten', '{\"columns\":[0,1,2,3,4,5,6,8,9],\"order\":[0,1,2,3,4,5,6,7,8,9],\"length\":5}', 0),
(18, 'auslagen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,10,11,12],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":10,\"sort\":[5,\"asc\"],\"page\":0}', 0),
(18, 'buchungen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9],\"order\":[0,1,2,3,4,5,6,7,8,9,10],\"length\":6,\"sort\":[6,\"desc\"],\"page\":0,\"platz\":\"\",\"name\":\"\",\"past\":0}', 0),
(18, 'kunden_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"length\":10}', 0),
(18, 'preisliste', '{\"length\":100}', 0),
(18, 'rechnungen', '{\"v\":4,\"columns\":[0,2,3,6,7,8,11,12,13,14,15],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":7}', 0),
(18, 'rechnungen_filter', '{\"showCorrections\":false,\"showCredits\":false,\"showOffers\":false,\"showOffersOnly\":false,\"showOpen\":true,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0),
(18, 'stromzaehler_mardorf', '{\"length\":10}', 0),
(18, 'stromzaehler_parent_mardorf', '{\"length\":10}', 0),
(20, 'auslagen_verwalten', '{\"length\":10}', 0),
(22, 'artikel_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9],\"order\":[0,1,2,3,4,5,6,7,8,9]}', 0),
(22, 'auslagen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,10,11,12],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0}', 0),
(22, 'buchungen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10],\"order\":[0,1,2,3,4,5,6,7,8,9,10],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0,\"platz\":\"\",\"name\":\"\",\"past\":0}', 0),
(22, 'dauercamping_filter', '1', 0),
(22, 'kunden_verwalten', '{\"columns\":[0,1,3,5,6,12,13,14,15,28],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"length\":10}', 0),
(22, 'rechnungen', '{\"v\":4,\"columns\":[2,3,4,5,6,7,8,10,12,13,14,15,16],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":10}', 0),
(22, 'rechnungen_color', 'F1', 0),
(22, 'rechnungen_filter', '{\"showCorrections\":true,\"showCredits\":true,\"showOffers\":false,\"showOffersOnly\":false,\"showOpen\":false,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0),
(23, 'artikel_auswertung', '{\"columns\":[1,2,3,4,5,6,7,8,9,10,11,12],\"order\":{\"summary\":[0,1,2,3,4,5,6,7,8,9,10,11,12]},\"length\":10}', 0),
(23, 'artikel_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9],\"order\":[0,1,2,3,4,5,6,7,8,9],\"length\":10}', 0),
(23, 'auslagen_verwalten', '{\"columns\":[],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0}', 0),
(23, 'buchungen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10],\"order\":[0,1,2,3,4,5,6,7,8,9,10],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0,\"platz\":\"\",\"name\":\"\",\"past\":0}', 0),
(23, 'kunden_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"length\":10}', 0),
(23, 'preisliste', '{\"length\":100}', 0),
(23, 'rechnungen', '{\"v\":4,\"columns\":[0,2,3,4,5,6,7,8,9,10,11,12,15,16,17],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":10}', 0),
(23, 'rechnungen_filter', '{\"showCorrections\":false,\"showCredits\":false,\"showOffers\":false,\"showOffersOnly\":false,\"showOpen\":true,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0),
(23, 'stromzaehler_mardorf', '{\"length\":10}', 0),
(23, 'stromzaehler_parent_mardorf', '{\"length\":10}', 0),
(24, 'artikel_verwalten', '{\"columns\":[0,1,2,3,4,5,6,8],\"order\":[0,1,2,3,4,5,6,7,8,9],\"length\":250}', 0),
(24, 'auslagen_verwalten', '{\"columns\":[0,2,3,4,5,6,7,8,10,11],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":10,\"sort\":[6,\"asc\"],\"page\":0}', 0),
(26, 'artikel_auswertung', '{\"columns\":[1,2,3,4,5,6,7,8,9,10,11,12],\"order\":{\"summary\":[0,1,2,3,4,5,6,7,8,9,10,11,12]},\"length\":10}', 0),
(26, 'artikel_verwalten', '{\"columns\":[0,1,2,3,4,5,6,8,9],\"order\":[0,1,2,3,4,5,6,7,8,9],\"length\":5}', 0),
(26, 'auslagen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,10,11,12],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0}', 0),
(26, 'buchungen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10],\"order\":[0,1,2,3,4,5,6,7,8,9,10],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0,\"platz\":\"\",\"name\":\"\",\"past\":0}', 0),
(26, 'kunden_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"length\":10}', 0),
(26, 'rechnungen', '{\"v\":4,\"columns\":[0,2,3,4,5,6,7,8,9,10,11,12,15,16,17],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":10}', 0),
(26, 'rechnungen_filter', '{\"showCorrections\":true,\"showCredits\":true,\"showOffers\":true,\"showOffersOnly\":false,\"showOpen\":false,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0),
(27, 'auslagen_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,10,11,12],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12],\"length\":10,\"sort\":[0,\"asc\"],\"page\":0}', 0),
(27, 'kunden_verwalten', '{\"columns\":[0,1,3,5,6,13,17],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"length\":10}', 0),
(27, 'rechnungen', '{\"v\":4,\"columns\":[0,2,3,4,5,6,7,8,9,10,11,12,15,16,17],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":10}', 0),
(27, 'rechnungen_filter', '{\"showCorrections\":false,\"showCredits\":false,\"showOffers\":false,\"showOffersOnly\":false,\"showOpen\":true,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0),
(28, 'kunden_verwalten', '{\"columns\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30],\"length\":10}', 0),
(28, 'rechnungen', '{\"v\":4,\"columns\":[2,3,4,5,6,8,10,12,15,16],\"order\":[0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19],\"length\":100}', 0),
(28, 'rechnungen_filter', '{\"showCorrections\":true,\"showCredits\":true,\"showOffers\":true,\"showOffersOnly\":false,\"showOpen\":false,\"showDeletedOnly\":false,\"hideDeleted\":false,\"showSepa\":false}', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admin_rollen`
--

CREATE TABLE `admin_rollen` (
  `admin_id` int(11) NOT NULL,
  `rollen_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `admin_rollen`
--

INSERT INTO `admin_rollen` (`admin_id`, `rollen_id`) VALUES
(11, 2),
(11, 10),
(11, 14),
(17, 11),
(20, 11),
(21, 12),
(23, 2),
(23, 10),
(24, 11),
(24, 13),
(24, 14),
(25, 12),
(26, 2),
(26, 10),
(26, 14),
(27, 2),
(27, 10),
(27, 11),
(27, 15),
(28, 15);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admin_sportarten`
--

CREATE TABLE `admin_sportarten` (
  `admin_id` int(11) NOT NULL,
  `sportart_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `admin_sportarten`
--

INSERT INTO `admin_sportarten` (`admin_id`, `sportart_id`) VALUES
(11, 9),
(17, 1),
(20, 2),
(21, 2),
(23, 4),
(24, 4),
(25, 4),
(26, 9),
(27, 11),
(28, 11);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `angebote`
--

CREATE TABLE `angebote` (
  `id` int(11) NOT NULL,
  `angebotsnummer` varchar(10) DEFAULT NULL,
  `rechnung_id` int(11) DEFAULT NULL,
  `empfaenger` varchar(255) NOT NULL,
  `empfaenger_id` int(11) DEFAULT NULL,
  `erstellt_am` date NOT NULL,
  `status` enum('angelegt','geprueft','versendet','bezahlt') NOT NULL DEFAULT 'angelegt',
  `kopftext` text DEFAULT NULL,
  `fusstext` text DEFAULT NULL,
  `bezahldatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `angebote`
--

INSERT INTO `angebote` (`id`, `angebotsnummer`, `rechnung_id`, `empfaenger`, `empfaenger_id`, `erstellt_am`, `status`, `kopftext`, `fusstext`, `bezahldatum`) VALUES
(34, 'AN000001', 36, 'Christian Wulf', 1, '2025-08-25', 'versendet', 'Rechnung für August', '', '2025-09-08');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `angebotpositionen`
--

CREATE TABLE `angebotpositionen` (
  `id` int(11) NOT NULL,
  `rechnung_id` int(11) NOT NULL,
  `artikelnummer` varchar(50) NOT NULL,
  `kurzbez` varchar(255) DEFAULT NULL,
  `langbez` text DEFAULT NULL,
  `bemerkung` text DEFAULT NULL,
  `einzelpreis` decimal(10,2) NOT NULL,
  `menge` int(11) NOT NULL,
  `rabatt` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `angebotpositionen`
--

INSERT INTO `angebotpositionen` (`id`, `rechnung_id`, `artikelnummer`, `kurzbez`, `langbez`, `bemerkung`, `einzelpreis`, `menge`, `rabatt`) VALUES
(57, 34, '0002', 'LLZ Doppelzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', '', 50.00, 2, 0.00);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `artikel`
--

CREATE TABLE `artikel` (
  `id` int(11) NOT NULL,
  `aid` varchar(50) NOT NULL,
  `kurzbez` varchar(255) DEFAULT NULL,
  `langbez` text DEFAULT NULL,
  `mwst` decimal(5,2) NOT NULL DEFAULT 0.00,
  `aktiv` tinyint(1) NOT NULL DEFAULT 1,
  `buchungskonto` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `artikel`
--

INSERT INTO `artikel` (`id`, `aid`, `kurzbez`, `langbez`, `mwst`, `aktiv`, `buchungskonto`) VALUES
(2, '0002', 'LLZ Doppelzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', 7.00, 1, ''),
(3, '0003', 'LLZ Dreibettzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', 7.00, 1, ''),
(4, '0004', 'LLZ Vierbettzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', 7.00, 1, ''),
(5, '0005', 'LLZ Saal', '', 19.00, 1, ''),
(6, '0006', 'LLZ Küche', 'Selbstkochküche vollausgestattet', 19.00, 1, ''),
(7, '0001', 'LLZ Einzelzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', 7.00, 1, '23100'),
(8, '0050', 'Mardorf Einzelzimmer', 'Einzelzimmer', 0.00, 1, ''),
(9, '0051', 'Mardorf Doppelzimmer', '', 0.00, 1, ''),
(10, '0055', 'Mardorf Saal', '', 19.00, 1, '999988'),
(12, '0100', 'LLZ Motorboot', 'Miete für Motorboot am Landesleistungszentrum', 7.00, 1, ''),
(14, '1000', 'Einzelmitgliedschaft', '', 0.00, 1, ''),
(15, '1001', 'Familienmitgliedschaft', 'Einzelmitglied mit Familienmitgliedschaft Partnerschaft inkl. 2 Kinder u. 18 Jahre', 0.00, 1, ''),
(16, '1002', 'Anschlussfamilienmitgliedschaft', 'ist in der Familienmitgliedschaft des Hauptmitgliedes enthalten', 0.00, 1, ''),
(17, '0200', 'Mardorf Stegplatz Größe 1', 'Bootsliegeplatz am Steg 32N Dickschiff bis 7,2 Meter', 0.00, 1, ''),
(18, '0201', 'Mardorf Stegplatz Größe 2', '', 0.00, 1, ''),
(19, '0202', 'Mardorf Vorgelände Größe 3', 'Katamaranplätze', 0.00, 1, ''),
(20, '0203', 'Mardorf Vorgelände Größe 2', 'Segeljolle', 0.00, 1, ''),
(21, '0204', 'Mardorf Vorgelände Größe 1', 'Liegeplatz für Kanusegler (IC und Taifun)', 0.00, 1, ''),
(22, '0210', 'Mardorf Dauercamping Größe I', 'Parzelle der Größe I auf dem Verbandsgelände in Mardorf', 0.00, 1, ''),
(23, '0250', 'Strom Dauercamping', 'Stromverbrauch für Dauercamping 0,50 cent / Kwh', 0.00, 1, ''),
(24, '0211', 'Mardorf Dauercamping Größe II', '', 0.00, 1, ''),
(25, '0220', 'Mardorf Schuppenplatz Kanu', '', 0.00, 1, ''),
(26, '0900', 'Mahngebühr', '', 0.00, 1, ''),
(27, '0800', 'Eigenbeteiligung Kanurennsport', '', 0.00, 1, ''),
(28, '0300', 'Mardorf - Zeltstandplatz', 'inkl. 2 Erwachsene, Parkplatz, Dusche', 7.00, 1, ''),
(29, '0301', 'Mardorf - Standplatz klein', '(Wohnmobil/Wohnwagen bis 7m) – inkl. 2 Erwachsene, Strom, Parkplatz, Dusche', 7.00, 1, ''),
(30, '0302', 'Mardorf - Standplatz groß', '(Wohnmobil/Wohnwagen über 7m) – inkl. 2 Erwachsene, Strom, Parkplatz, Dusche, 1 kleines Zusatzzelt', 7.00, 1, ''),
(31, '0310', 'Mardorf Düne - Weitere erwachsene Person', 'Zusatzleistung - Weitere erwachsene Person', 0.00, 1, ''),
(32, '0311', 'Mardorf Düne - Jugendliche (7–18 Jahre)', 'Zusatzleistung - Jugendliche (7–18 Jahre)', 0.00, 1, ''),
(33, '0312', 'Mardorf Düne - Kinder bis 6 Jahre', 'Zusatzleistung - Kinder bis 6 Jahre', 0.00, 1, ''),
(34, '0313', 'Mardorf Düne - Hund', 'Zusatzleistung - Hund', 0.00, 1, ''),
(35, '0314', 'Mardorf Düne - Weiterer PKW-Parkplatz', 'Zusatzleistung - Weiterer PKW-Parkplatz', 0.00, 1, ''),
(36, '0801', 'Trainer Honorar', '', 0.00, 1, ''),
(38, '0400', 'DKV Mardorf - Zeltstandplatz', 'inkl. 2 Erwachsene, Parkplatz, Dusche für DKV Mitglieder', 7.00, 1, ''),
(39, '0401', 'DKV Mardorf - Standplatz klein', '(Wohnmobil/Wohnwagen bis 7m) – inkl. 2 Erwachsene, Strom, Parkplatz, Dusche für DKV Mitglieder', 7.00, 1, ''),
(40, '0402', 'DKV Mardorf - Standplatz groß', '(Wohnmobil/Wohnwagen über 7m) – inkl. 2 Erwachsene, Strom, Parkplatz, Dusche, 1 kleines Zusatzzelt für DKV Mitglieder', 7.00, 1, ''),
(41, '0410', 'DKV Mardorf Düne - Weitere erwachsene Person', 'Zusatzleistung - Weitere erwachsene Person für DKV Mitglieder', 0.00, 1, ''),
(42, '0411', 'DKV Mardorf Düne - Jugendliche (7–18 Jahre)', 'Zusatzleistung - Jugendliche (7–18 Jahre) für DKV Mitglieder', 0.00, 1, ''),
(43, '0412', 'DKV Mardorf Düne - Kinder bis 6 Jahre', 'Zusatzleistung - Kinder bis 6 Jahre für DKV Mitglieder', 0.00, 1, ''),
(44, '0413', 'DKV Mardorf Düne - Hund', 'Zusatzleistung - Hund für DKV Mitglieder', 0.00, 1, ''),
(45, '0414', 'DKV Mardorf Düne - Weiterer PKW-Parkplatz', 'Zusatzleistung - Weiterer PKW-Parkplatz für DKV Mitglieder', 0.00, 1, ''),
(46, '0802', 'Leitungshonorar', 'Honorar für Lehrgangsleitung', 0.00, 1, ''),
(47, '0500', 'km Pauschale 19%', 'Kilometerpauschale pro gefahrenen km Hin- und Rückweg', 19.00, 1, ''),
(48, '1003', 'Jugendliche Einzelmitglieder 15-18 Jahre', '', 0.00, 1, ''),
(49, '1004', 'Schüler Einzelmitglieder', '', 0.00, 1, ''),
(50, '1005', 'Studenten, Auszubildende Einzelmitglieder', '', 0.00, 1, ''),
(51, '1006', 'Partner Einzelmitglieder', '', 0.00, 1, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `artikelgruppen`
--

CREATE TABLE `artikelgruppen` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(100) NOT NULL,
  `archiviert` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `artikelgruppen`
--

INSERT INTO `artikelgruppen` (`id`, `bezeichnung`, `archiviert`) VALUES
(1, 'LLZ Hannover', 0),
(3, 'Mardorf Kurzzeitcamping Gast', 0),
(4, 'Mardorf WKH', 0),
(5, 'Geschäftsstelle', 0),
(6, 'Lehrgänge', 0),
(7, 'Mardorf Kurzzeitcamping DKV', 0),
(11, 'Mardorf sonstige', 0),
(12, 'Mardorf Kiosk', 0),
(13, 'Einzelmitglieder', 0),
(14, 'Mardorf Jahresverträge', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `artikel_artikelgruppen`
--

CREATE TABLE `artikel_artikelgruppen` (
  `artikel_id` int(11) NOT NULL,
  `artikelgruppe_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `artikel_artikelgruppen`
--

INSERT INTO `artikel_artikelgruppen` (`artikel_id`, `artikelgruppe_id`) VALUES
(2, 1),
(18, 14),
(19, 14),
(20, 14),
(22, 14),
(17, 14),
(21, 14),
(24, 14),
(25, 14),
(10, 4),
(26, 5),
(35, 3),
(34, 3),
(33, 3),
(31, 3),
(32, 3),
(27, 6),
(3, 1),
(7, 1),
(29, 3),
(30, 3),
(28, 3),
(16, 13),
(23, 14),
(14, 13),
(39, 7),
(38, 7),
(40, 7),
(41, 7),
(42, 7),
(43, 7),
(44, 7),
(45, 7),
(47, 5),
(48, 13),
(49, 13),
(15, 13),
(51, 13),
(50, 13);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ausgaben`
--

CREATE TABLE `ausgaben` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `sportart_id` int(11) NOT NULL DEFAULT 1,
  `lehrgang_id` int(11) DEFAULT NULL,
  `veranstaltung_art` varchar(255) NOT NULL,
  `veranstaltung_datum` date NOT NULL,
  `vorschuss_erhalten` tinyint(1) NOT NULL,
  `vorschuss_betrag` decimal(10,2) DEFAULT NULL,
  `bemerkung` text DEFAULT NULL,
  `endbetrag` decimal(10,2) DEFAULT NULL,
  `eingereicht_am` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('eingereicht','geprueft','abgelehnt','ueberwiesen') NOT NULL DEFAULT 'eingereicht',
  `abgelehnt_am` datetime DEFAULT NULL,
  `ueberwiesen_am` datetime DEFAULT NULL,
  `freigegeben_am` datetime DEFAULT NULL,
  `freigegeben_von` int(11) DEFAULT NULL,
  `geprueft_am` datetime DEFAULT NULL,
  `geprueft_von` int(11) DEFAULT NULL,
  `konto_inhaber_abweichend` varchar(255) DEFAULT NULL,
  `kreditinstitut_abweichend` varchar(255) DEFAULT NULL,
  `iban_abweichend` varchar(34) DEFAULT NULL,
  `abgelehnt_von` int(11) DEFAULT NULL,
  `ueberwiesen_von` int(11) DEFAULT NULL,
  `datev_export_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ausgaben_belege`
--

CREATE TABLE `ausgaben_belege` (
  `id` int(11) NOT NULL,
  `ausgabe_id` int(11) NOT NULL,
  `bezeichnung` varchar(255) NOT NULL,
  `betrag` decimal(10,2) NOT NULL,
  `ueuv` tinyint(1) DEFAULT 0,
  `dateiname` varchar(255) DEFAULT NULL,
  `erstellt_am` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `buchungen`
--

CREATE TABLE `buchungen` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `vorname` varchar(100) DEFAULT NULL,
  `nachname` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `platz` varchar(50) DEFAULT NULL,
  `start` datetime NOT NULL,
  `ende` datetime NOT NULL,
  `erstellt_am` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('anfrage','belegt') NOT NULL DEFAULT 'anfrage',
  `strasse` varchar(100) DEFAULT NULL,
  `hausnummer` varchar(10) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(100) DEFAULT NULL,
  `zahlungsart` varchar(20) DEFAULT NULL,
  `kreditinstitut` varchar(100) DEFAULT NULL,
  `kontoinhaber` varchar(100) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `bic` varchar(11) DEFAULT NULL,
  `dkv_mitglied` varchar(10) NOT NULL DEFAULT 'nein',
  `zustimmung_sepa` varchar(10) DEFAULT 'nein',
  `sonstige_informationen` text DEFAULT NULL,
  `anzahl_erwachsene` int(11) DEFAULT NULL,
  `anzahl_jugendliche` int(11) DEFAULT NULL,
  `anzahl_kinder` int(11) DEFAULT NULL,
  `anzahl_hunde` int(11) DEFAULT NULL,
  `anzahl_pkw` int(11) DEFAULT NULL,
  `fahrzeuglaenge` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `buchung_artikel`
--

CREATE TABLE `buchung_artikel` (
  `id` int(11) NOT NULL,
  `buchung_id` int(11) NOT NULL,
  `artikel_id` int(11) NOT NULL,
  `menge` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dauercamping`
--

CREATE TABLE `dauercamping` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) NOT NULL,
  `artikel_id` int(11) DEFAULT NULL,
  `campingplatz` int(11) DEFAULT NULL,
  `platz_info` text DEFAULT NULL,
  `platz_foto` varchar(255) DEFAULT NULL,
  `stromzaehler_dauercamping` varchar(50) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `grid_slot` int(11) NOT NULL,
  `kuendigung_jahresende` tinyint(1) NOT NULL DEFAULT 0,
  `hat_nachfolger` tinyint(1) NOT NULL DEFAULT 0,
  `sucht_nachfolger` tinyint(1) NOT NULL DEFAULT 0,
  `gaspruefung` tinyint(1) NOT NULL DEFAULT 0,
  `gasversorgung_stillgelegt` tinyint(1) NOT NULL DEFAULT 0,
  `hundeplatz` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dauercamping_grid`
--

CREATE TABLE `dauercamping_grid` (
  `slot` int(11) NOT NULL,
  `row_num` int(11) NOT NULL,
  `col_num` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `dauercamping_grid`
--

INSERT INTO `dauercamping_grid` (`slot`, `row_num`, `col_num`, `active`) VALUES
(1, 1, 1, 0),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1),
(6, 1, 6, 1),
(7, 1, 7, 1),
(8, 1, 8, 1),
(9, 1, 9, 1),
(10, 1, 10, 1),
(11, 1, 11, 1),
(12, 1, 12, 1),
(13, 1, 13, 1),
(14, 1, 14, 1),
(15, 1, 15, 1),
(16, 1, 16, 1),
(17, 1, 17, 1),
(18, 1, 18, 0),
(19, 1, 19, 0),
(20, 1, 20, 0),
(21, 2, 1, 0),
(22, 2, 2, 1),
(23, 2, 3, 0),
(24, 2, 4, 0),
(25, 2, 5, 0),
(26, 2, 6, 0),
(27, 2, 7, 0),
(28, 2, 8, 0),
(29, 2, 9, 0),
(30, 2, 10, 0),
(31, 2, 11, 0),
(32, 2, 12, 0),
(33, 2, 13, 0),
(34, 2, 14, 0),
(35, 2, 15, 0),
(36, 2, 16, 0),
(37, 2, 17, 0),
(38, 2, 18, 0),
(39, 2, 19, 0),
(40, 2, 20, 0),
(41, 3, 1, 0),
(42, 3, 2, 1),
(43, 3, 3, 0),
(44, 3, 4, 1),
(45, 3, 5, 1),
(46, 3, 6, 0),
(47, 3, 7, 1),
(48, 3, 8, 1),
(49, 3, 9, 0),
(50, 3, 10, 1),
(51, 3, 11, 1),
(52, 3, 12, 0),
(53, 3, 13, 1),
(54, 3, 14, 1),
(55, 3, 15, 0),
(56, 3, 16, 1),
(57, 3, 17, 1),
(58, 3, 18, 0),
(59, 3, 19, 1),
(60, 3, 20, 0),
(61, 4, 1, 0),
(62, 4, 2, 1),
(63, 4, 3, 0),
(64, 4, 4, 1),
(65, 4, 5, 1),
(66, 4, 6, 0),
(67, 4, 7, 1),
(68, 4, 8, 1),
(69, 4, 9, 0),
(70, 4, 10, 1),
(71, 4, 11, 1),
(72, 4, 12, 0),
(73, 4, 13, 1),
(74, 4, 14, 1),
(75, 4, 15, 0),
(76, 4, 16, 1),
(77, 4, 17, 1),
(78, 4, 18, 0),
(79, 4, 19, 1),
(80, 4, 20, 0),
(81, 5, 1, 0),
(82, 5, 2, 1),
(83, 5, 3, 0),
(84, 5, 4, 1),
(85, 5, 5, 1),
(86, 5, 6, 0),
(87, 5, 7, 1),
(88, 5, 8, 1),
(89, 5, 9, 0),
(90, 5, 10, 1),
(91, 5, 11, 1),
(92, 5, 12, 0),
(93, 5, 13, 1),
(94, 5, 14, 1),
(95, 5, 15, 0),
(96, 5, 16, 1),
(97, 5, 17, 1),
(98, 5, 18, 0),
(99, 5, 19, 1),
(100, 5, 20, 0),
(101, 6, 1, 0),
(102, 6, 2, 1),
(103, 6, 3, 0),
(104, 6, 4, 1),
(105, 6, 5, 1),
(106, 6, 6, 0),
(107, 6, 7, 1),
(108, 6, 8, 1),
(109, 6, 9, 0),
(110, 6, 10, 1),
(111, 6, 11, 1),
(112, 6, 12, 0),
(113, 6, 13, 1),
(114, 6, 14, 1),
(115, 6, 15, 0),
(116, 6, 16, 1),
(117, 6, 17, 1),
(118, 6, 18, 0),
(119, 6, 19, 1),
(120, 6, 20, 0),
(121, 7, 1, 0),
(122, 7, 2, 1),
(123, 7, 3, 0),
(124, 7, 4, 1),
(125, 7, 5, 1),
(126, 7, 6, 0),
(127, 7, 7, 1),
(128, 7, 8, 1),
(129, 7, 9, 0),
(130, 7, 10, 1),
(131, 7, 11, 1),
(132, 7, 12, 0),
(133, 7, 13, 1),
(134, 7, 14, 1),
(135, 7, 15, 0),
(136, 7, 16, 1),
(137, 7, 17, 1),
(138, 7, 18, 0),
(139, 7, 19, 1),
(140, 7, 20, 0),
(141, 8, 1, 0),
(142, 8, 2, 1),
(143, 8, 3, 0),
(144, 8, 4, 1),
(145, 8, 5, 1),
(146, 8, 6, 0),
(147, 8, 7, 1),
(148, 8, 8, 1),
(149, 8, 9, 0),
(150, 8, 10, 1),
(151, 8, 11, 1),
(152, 8, 12, 0),
(153, 8, 13, 1),
(154, 8, 14, 1),
(155, 8, 15, 0),
(156, 8, 16, 1),
(157, 8, 17, 1),
(158, 8, 18, 0),
(159, 8, 19, 1),
(160, 8, 20, 0),
(161, 9, 1, 0),
(162, 9, 2, 0),
(163, 9, 3, 0),
(164, 9, 4, 0),
(165, 9, 5, 0),
(166, 9, 6, 0),
(167, 9, 7, 0),
(168, 9, 8, 0),
(169, 9, 9, 0),
(170, 9, 10, 0),
(171, 9, 11, 0),
(172, 9, 12, 0),
(173, 9, 13, 0),
(174, 9, 14, 0),
(175, 9, 15, 0),
(176, 9, 16, 0),
(177, 9, 17, 0),
(178, 9, 18, 0),
(179, 9, 19, 1),
(180, 9, 20, 0),
(181, 10, 1, 1),
(182, 10, 2, 1),
(183, 10, 3, 0),
(184, 10, 4, 1),
(185, 10, 5, 1),
(186, 10, 6, 0),
(187, 10, 7, 1),
(188, 10, 8, 1),
(189, 10, 9, 0),
(190, 10, 10, 1),
(191, 10, 11, 1),
(192, 10, 12, 0),
(193, 10, 13, 1),
(194, 10, 14, 1),
(195, 10, 15, 0),
(196, 10, 16, 1),
(197, 10, 17, 1),
(198, 10, 18, 0),
(199, 10, 19, 1),
(200, 10, 20, 0),
(201, 11, 1, 1),
(202, 11, 2, 1),
(203, 11, 3, 0),
(204, 11, 4, 1),
(205, 11, 5, 1),
(206, 11, 6, 0),
(207, 11, 7, 1),
(208, 11, 8, 1),
(209, 11, 9, 0),
(210, 11, 10, 1),
(211, 11, 11, 1),
(212, 11, 12, 0),
(213, 11, 13, 1),
(214, 11, 14, 1),
(215, 11, 15, 0),
(216, 11, 16, 1),
(217, 11, 17, 1),
(218, 11, 18, 0),
(219, 11, 19, 1),
(220, 11, 20, 0),
(221, 12, 1, 1),
(222, 12, 2, 1),
(223, 12, 3, 0),
(224, 12, 4, 1),
(225, 12, 5, 1),
(226, 12, 6, 0),
(227, 12, 7, 1),
(228, 12, 8, 1),
(229, 12, 9, 0),
(230, 12, 10, 1),
(231, 12, 11, 1),
(232, 12, 12, 0),
(233, 12, 13, 1),
(234, 12, 14, 1),
(235, 12, 15, 0),
(236, 12, 16, 1),
(237, 12, 17, 1),
(238, 12, 18, 0),
(239, 12, 19, 1),
(240, 12, 20, 0),
(241, 13, 1, 1),
(242, 13, 2, 1),
(243, 13, 3, 0),
(244, 13, 4, 1),
(245, 13, 5, 1),
(246, 13, 6, 0),
(247, 13, 7, 1),
(248, 13, 8, 1),
(249, 13, 9, 0),
(250, 13, 10, 1),
(251, 13, 11, 1),
(252, 13, 12, 0),
(253, 13, 13, 1),
(254, 13, 14, 1),
(255, 13, 15, 0),
(256, 13, 16, 0),
(257, 13, 17, 0),
(258, 13, 18, 0),
(259, 13, 19, 1),
(260, 13, 20, 0),
(261, 14, 1, 0),
(262, 14, 2, 0),
(263, 14, 3, 0),
(264, 14, 4, 0),
(265, 14, 5, 1),
(266, 14, 6, 0),
(267, 14, 7, 1),
(268, 14, 8, 1),
(269, 14, 9, 0),
(270, 14, 10, 1),
(271, 14, 11, 1),
(272, 14, 12, 0),
(273, 14, 13, 0),
(274, 14, 14, 0),
(275, 14, 15, 0),
(276, 14, 16, 0),
(277, 14, 17, 0),
(278, 14, 18, 0),
(279, 14, 19, 1),
(280, 14, 20, 0),
(281, 15, 1, 0),
(282, 15, 2, 0),
(283, 15, 3, 0),
(284, 15, 4, 0),
(285, 15, 5, 1),
(286, 15, 6, 0),
(287, 15, 7, 1),
(288, 15, 8, 0),
(289, 15, 9, 0),
(290, 15, 10, 0),
(291, 15, 11, 0),
(292, 15, 12, 0),
(293, 15, 13, 0),
(294, 15, 14, 0),
(295, 15, 15, 0),
(296, 15, 16, 0),
(297, 15, 17, 0),
(298, 15, 18, 0),
(299, 15, 19, 0),
(300, 15, 20, 0),
(301, 16, 1, 0),
(302, 16, 2, 0),
(303, 16, 3, 0),
(304, 16, 4, 0),
(305, 16, 5, 1),
(306, 16, 6, 0),
(307, 16, 7, 0),
(308, 16, 8, 0),
(309, 16, 9, 0),
(310, 16, 10, 0),
(311, 16, 11, 0),
(312, 16, 12, 0),
(313, 16, 13, 0),
(314, 16, 14, 0),
(315, 16, 15, 0),
(316, 16, 16, 0),
(317, 16, 17, 0),
(318, 16, 18, 0),
(319, 16, 19, 0),
(320, 16, 20, 0),
(321, 17, 1, 0),
(322, 17, 2, 0),
(323, 17, 3, 0),
(324, 17, 4, 0),
(325, 17, 5, 1),
(326, 17, 6, 0),
(327, 17, 7, 0),
(328, 17, 8, 0),
(329, 17, 9, 0),
(330, 17, 10, 0),
(331, 17, 11, 0),
(332, 17, 12, 0),
(333, 17, 13, 0),
(334, 17, 14, 0),
(335, 17, 15, 0),
(336, 17, 16, 0),
(337, 17, 17, 0),
(338, 17, 18, 0),
(339, 17, 19, 0),
(340, 17, 20, 0),
(341, 18, 1, 0),
(342, 18, 2, 0),
(343, 18, 3, 0),
(344, 18, 4, 0),
(345, 18, 5, 0),
(346, 18, 6, 0),
(347, 18, 7, 0),
(348, 18, 8, 0),
(349, 18, 9, 0),
(350, 18, 10, 0),
(351, 18, 11, 0),
(352, 18, 12, 0),
(353, 18, 13, 0),
(354, 18, 14, 0),
(355, 18, 15, 0),
(356, 18, 16, 0),
(357, 18, 17, 0),
(358, 18, 18, 0),
(359, 18, 19, 0),
(360, 18, 20, 0),
(361, 19, 1, 0),
(362, 19, 2, 0),
(363, 19, 3, 0),
(364, 19, 4, 0),
(365, 19, 5, 0),
(366, 19, 6, 0),
(367, 19, 7, 0),
(368, 19, 8, 0),
(369, 19, 9, 0),
(370, 19, 10, 0),
(371, 19, 11, 0),
(372, 19, 12, 0),
(373, 19, 13, 0),
(374, 19, 14, 0),
(375, 19, 15, 0),
(376, 19, 16, 0),
(377, 19, 17, 0),
(378, 19, 18, 0),
(379, 19, 19, 0),
(380, 19, 20, 0),
(381, 20, 1, 0),
(382, 20, 2, 0),
(383, 20, 3, 0),
(384, 20, 4, 0),
(385, 20, 5, 0),
(386, 20, 6, 0),
(387, 20, 7, 0),
(388, 20, 8, 0),
(389, 20, 9, 0),
(390, 20, 10, 0),
(391, 20, 11, 0),
(392, 20, 12, 0),
(393, 20, 13, 0),
(394, 20, 14, 0),
(395, 20, 15, 0),
(396, 20, 16, 0),
(397, 20, 17, 0),
(398, 20, 18, 0),
(399, 20, 19, 0),
(400, 20, 20, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fahrten`
--

CREATE TABLE `fahrten` (
  `id` int(11) NOT NULL,
  `ausgabe_id` int(11) NOT NULL,
  `startort` varchar(255) NOT NULL,
  `zielort` varchar(255) NOT NULL,
  `zweck` varchar(255) NOT NULL,
  `kilometer` decimal(10,2) NOT NULL,
  `pauschale_id` int(11) NOT NULL,
  `betrag` decimal(10,2) NOT NULL,
  `erstellt_am` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `funktionen_lkv`
--

CREATE TABLE `funktionen_lkv` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `funktionen_lkv`
--

INSERT INTO `funktionen_lkv` (`id`, `bezeichnung`) VALUES
(1, 'Präsident'),
(2, 'Vizepräsident Leistungssport'),
(3, 'Vizepräsident Freizeitsprot'),
(4, 'Vizepräsident Finanzen'),
(5, 'Geschäftsführung');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gutschriften`
--

CREATE TABLE `gutschriften` (
  `id` int(11) NOT NULL,
  `gutschriftnummer` varchar(10) DEFAULT NULL,
  `rechnung_id` int(11) DEFAULT NULL,
  `buchungen_id` int(11) DEFAULT NULL,
  `empfaenger` varchar(255) NOT NULL,
  `empfaenger_id` int(11) DEFAULT NULL,
  `erstellt_am` date NOT NULL,
  `status` enum('angelegt','geprueft','versendet','bezahlt') NOT NULL DEFAULT 'angelegt',
  `kopftext` text DEFAULT NULL,
  `fusstext` text DEFAULT NULL,
  `bezahldatum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `gutschriften`
--

INSERT INTO `gutschriften` (`id`, `gutschriftnummer`, `rechnung_id`, `buchungen_id`, `empfaenger`, `empfaenger_id`, `erstellt_am`, `status`, `kopftext`, `fusstext`, `bezahldatum`) VALUES
(11, 'GS000001', 36, NULL, 'Christian Wulf', 1, '2025-08-25', 'geprueft', 'Gutschrift Rechnungsnummer: RE000003', '', '2025-09-08');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `gutschriftpositionen`
--

CREATE TABLE `gutschriftpositionen` (
  `id` int(11) NOT NULL,
  `rechnung_id` int(11) NOT NULL,
  `artikelnummer` varchar(50) NOT NULL,
  `kurzbez` varchar(255) DEFAULT NULL,
  `langbez` text DEFAULT NULL,
  `bemerkung` text DEFAULT NULL,
  `einzelpreis` decimal(10,2) NOT NULL,
  `menge` int(11) NOT NULL,
  `rabatt` decimal(5,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `gutschriftpositionen`
--

INSERT INTO `gutschriftpositionen` (`id`, `rechnung_id`, `artikelnummer`, `kurzbez`, `langbez`, `bemerkung`, `einzelpreis`, `menge`, `rabatt`) VALUES
(139, 11, '0002', 'LLZ Doppelzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', '', 50.00, 2, 0.00);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_calendars`
--

CREATE TABLE `kalender_calendars` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `roles` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kalender_calendars`
--

INSERT INTO `kalender_calendars` (`id`, `name`, `roles`) VALUES
(1, 'LKVN - Geschäftsstelle', NULL),
(2, 'LKVN Leistungssport', NULL),
(3, 'Annabelles Test Kalender Nummer 1', 'Administrator'),
(10, 'cw', 'Administrator,Geschäftsstellenmitarbeiter,Superadmin'),
(11, 'Testkalender', 'Administrator,Benutzer,Geschäftsstellenmitarbeiter,Präsidium,Superadmin');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_categories`
--

CREATE TABLE `kalender_categories` (
  `id` int(11) NOT NULL,
  `kalender_id` int(11) NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3788d8',
  `roles` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kalender_categories`
--

INSERT INTO `kalender_categories` (`id`, `kalender_id`, `name`, `color`, `roles`) VALUES
(4, 1, 'Auslandsprojekte', '#e60f6c', NULL),
(5, 1, 'Behördentermine', '#73b5de', NULL),
(6, 1, 'DKV Termine', '#672a84', NULL),
(7, 1, 'Emma', '#de78db', NULL),
(8, 1, 'Steuerberater', '#e6cc70', NULL),
(9, 1, 'Termine allgemein', '#1c6cd4', NULL),
(10, 1, 'Urlaub', '#000000', NULL),
(11, 1, 'Zahlung/Daueraufträge', '#5d965a', NULL),
(12, 1, 'Lehrgänge', '#b9f0f9', NULL),
(14, 2, 'Drachenbot', '#7f4610', NULL),
(15, 2, 'Polo', '#f4bf2f', NULL),
(16, 2, 'RS Auslandsprojekte', '#0f6c32', NULL),
(17, 3, 'Allgemein', '#0d6efd', NULL),
(18, 3, 'Privat', '#dc3545', NULL),
(21, 2, 'RS Lehrgänge', '#063d74', NULL),
(22, 2, 'RS Regatta', '#85c8ea', NULL),
(23, 2, 'Slalom', '#a82a00', NULL),
(24, 2, 'WW Rennsport', '#94b950', NULL),
(33, 10, 'Allgemein', '#0d6efd', NULL),
(34, 10, 'Privat', '#dc3545', NULL),
(35, 10, 'Arbeit', '#be16d4', 'Administrator,Superadmin'),
(36, 11, 'Allgemein', '#0d6efd', NULL),
(37, 11, 'Privat', '#dc3545', NULL),
(38, 11, 'Test', '#691b8d', 'Administrator,Präsidium,Superadmin'),
(39, 11, 'Test Lehrgang', '#2d07e9', 'Administrator,Präsidium,Superadmin'),
(40, 11, 'Krankheit', '#e507e9', 'Fachwarte,Geschäftsstellenmitarbeiter,Präsidium,Superadmin');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kalender_events`
--

CREATE TABLE `kalender_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `all_day` tinyint(1) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL,
  `category_ids` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kalender_events`
--

INSERT INTO `kalender_events` (`id`, `title`, `start_date`, `start_time`, `end_date`, `end_time`, `all_day`, `category_id`, `category_ids`) VALUES
(39, 'Polen IN NDM (s. u.)', '2024-06-28', NULL, '2024-06-30', NULL, 1, 4, ''),
(40, 'Japan IN (s. u.)', '2024-08-25', NULL, '2024-08-30', NULL, 1, 4, ''),
(41, '11:00 h Rathausführung (Japan IN)', '2024-08-26', NULL, '2024-08-26', NULL, 1, 4, ''),
(42, 'Stegplatzmeldung zum 1. Juni', '2023-05-12', NULL, '2023-05-12', NULL, 1, 7, ''),
(43, 'Abschmelzungsbetrag Abgabe', '2024-05-23', NULL, '2024-05-23', NULL, 1, 7, ''),
(44, 'Jour Fix Phönix (Emma\\, Albert)', '2024-08-12', '11:00:00', '2024-08-12', '12:00:00', 0, 7, ''),
(45, 'Olympia Empfang in Brandenburg (Christian\\, Emma)', '2024-08-16', '16:30:00', '2024-08-16', '21:00:00', 0, 7, ''),
(46, 'Abstimmung Phönix II', '2024-08-21', '09:30:00', '2024-08-21', '10:30:00', 0, 7, ''),
(47, 'Schließanlage LLZ (Andi\\, Christian\\, Emma)', '2024-08-21', '15:30:00', '2024-08-21', '16:30:00', 0, 7, ''),
(48, 'Japan IN (s. u.)', '2024-08-25', NULL, '2024-08-30', NULL, 1, 7, ''),
(49, 'Projektplanungstreffen Ausland 2025 beim LSB (Christian\\, Emma)', '2024-08-26', '15:30:00', '2024-08-26', '16:30:00', 0, 7, ''),
(50, 'Abstimmung Athleten und Bundesstützpunkt Hannover DKV (Christian\\,', '2024-08-28', '09:00:00', '2024-08-28', '10:00:00', 0, 7, ''),
(51, 'Teamabend im LLZ', '2024-08-29', NULL, '2024-08-29', NULL, 1, 7, ''),
(52, 'Anrufen Herr Rupp Geländepflege LLZ', '2024-08-29', NULL, '2024-08-29', NULL, 1, 7, ''),
(53, 'Anrufen Herrn Herschel', '2024-08-30', '09:00:00', '2024-08-30', '09:30:00', 0, 7, ''),
(54, 'Klausurtagung zu Mardorf im LLZ (Annette\\, Albert\\, Christian\\, Bir', '2024-08-30', '10:00:00', '2024-08-30', '12:00:00', 0, 7, ''),
(55, 'Übergabe buchung@ von Albert an Annette (Albert\\, Annette\\, Emma)', '2024-09-02', NULL, '2024-09-02', NULL, 1, 7, ''),
(56, 'Phönix Abstimmung mit dem DKV (Emma\\, Albert)', '2024-09-02', '11:00:00', '2024-09-02', '12:00:00', 0, 7, ''),
(57, 'Telefonat mit Herrn Herschel Stadt Hannover', '2024-09-04', '09:00:00', '2024-09-04', '09:30:00', 0, 7, ''),
(58, 'Viko Jubiläumsabstimmung mit WSV Verden (Christian\\, Emma)', '2024-09-04', '17:00:00', '2024-09-04', '18:30:00', 0, 7, ''),
(59, 'Stiftungsforum Lotto-Sport-Stiftung (Christian\\, Emma)', '2024-09-05', '11:30:00', '2024-09-05', '16:30:00', 0, 7, ''),
(60, 'Eröffnung Deutsche Schülermeisterschaft Hildesheim (Günther\\, Ch', '2024-09-06', NULL, '2024-09-06', NULL, 1, 7, ''),
(61, 'Steuerbüro dhs Genge + Schmidtmeier GmbH & Co. KG (Christian\\, Alb', '2024-09-09', '15:00:00', '2024-09-09', '16:00:00', 0, 7, ''),
(62, 'LSB Leistungssport Abstimmung (Andi\\, Christian\\, Emma)', '2024-09-10', '12:00:00', '2024-09-10', '13:00:00', 0, 7, ''),
(63, 'Telefonat mit Herrn Herschel Stadt Hannover', '2024-09-11', '09:00:00', '2024-09-11', '09:30:00', 0, 7, ''),
(64, 'Austausch Polen OUT (Katharina\\, Christian\\, Andi\\, Emma)', '2024-09-19', NULL, '2024-09-21', NULL, 1, 7, ''),
(65, 'Empfang HKC + Sommerfest (Christian)', '2024-09-28', NULL, '2024-09-28', NULL, 1, 7, ''),
(66, 'Termin tel. Herschel Mardorf WKH', '2024-10-01', '10:00:00', '2024-10-01', '11:00:00', 0, 7, ''),
(67, 'Anfrage wegschneiden der Brombeerhecke LLZ', '2024-10-02', NULL, '2024-10-02', NULL, 1, 7, ''),
(68, 'Abstimmung Phönix mit Felix', '2024-10-02', '10:00:00', '2024-10-02', '11:00:00', 0, 7, ''),
(69, 'Abstimmung Jubiläum 2027 mit WSV (Christian\\, Martin\\, Emma)', '2024-10-02', '17:00:00', '2024-10-02', '18:00:00', 0, 7, ''),
(70, 'Phönix Abstimmung DKV', '2024-10-07', '11:00:00', '2024-10-07', '12:00:00', 0, 7, ''),
(71, 'Prüfung der Rauchmelder LLZ', '2024-10-10', '09:00:00', '2024-10-10', '10:00:00', 0, 7, ''),
(72, 'Abstimmung Konzept CJD zum WKH (Albert\\, Emma)', '2024-10-10', '12:00:00', '2024-10-10', '13:00:00', 0, 7, ''),
(73, 'Release-Info', '2024-10-10', '15:00:00', '2024-10-10', '16:00:00', 0, 7, ''),
(74, 'Glaß Dauerplatz (C. Wulf\\, GF/GS)', '2024-10-28', NULL, '2024-10-28', NULL, 1, 7, ''),
(75, 'Präsidiumssitzung (Christian\\, Albert\\, Martin\\, Emma\\, Günther)', '2024-10-29', '17:00:00', '2024-10-29', '19:00:00', 0, 7, ''),
(76, 'Antrag Nachwuchslehrgänge beim LSB', '2024-10-30', NULL, '2024-10-30', NULL, 1, 7, ''),
(77, 'Winterlager IC 43 Tjorben in Mardorf', '2024-11-03', NULL, '2024-11-03', NULL, 1, 7, ''),
(78, 'Phoenix II Jour Fix', '2024-11-04', '11:00:00', '2024-11-04', '12:00:00', 0, 7, ''),
(79, 'Abstimmung Axel DelMar (Christian\\, Albert\\, Emma)', '2024-11-05', '12:00:00', '2024-11-05', '13:00:00', 0, 7, ''),
(80, 'Ehrung der Rennsportler', '2024-11-05', '18:00:00', '2024-11-05', '22:00:00', 0, 7, ''),
(81, 'Gewärbeprüfung der Rentenversicherung nachfragen!', '2024-11-06', NULL, '2024-11-06', NULL, 1, 7, ''),
(82, 'Gas Prüfstandliste von Bernd Büntemeier', '2024-11-06', NULL, '2024-11-06', NULL, 1, 7, ''),
(83, 'Vorgespräch/Tagung Freizeitsport/Leistungssport', '2024-11-09', '12:00:00', '2024-11-09', '15:00:00', 0, 7, ''),
(84, 'LKV VA Abstimmungsveranstaltung Leistungssport und Freizeitsport', '2024-11-10', '10:00:00', '2024-11-10', '18:00:00', 0, 7, ''),
(85, 'Heinz Ehlers', '2024-11-13', '13:00:00', '2024-11-13', '14:30:00', 0, 7, ''),
(86, 'DKV VA in Duisburg', '2024-11-15', NULL, '2024-11-17', NULL, 1, 7, ''),
(87, 'Abstimmung Verbands App', '2024-11-18', '11:00:00', '2024-11-18', '12:00:00', 0, 7, ''),
(88, 'Abstimmung mit Jens NRW', '2024-11-19', '10:00:00', '2024-11-19', '10:45:00', 0, 7, ''),
(89, 'Planung C-Lizenz Niedersachsen', '2024-11-19', '19:00:00', '2024-11-19', '20:00:00', 0, 7, ''),
(90, 'Präsidiumsabstimmung zur Verbandsapp (Präsidium (Albert\\, Christi', '2024-11-21', '11:00:00', '2024-11-21', '12:00:00', 0, 7, ''),
(91, 'Treffen LRV Natascha\\, Reinhard (Christian)', '2024-11-25', '10:30:00', '2024-11-25', '11:30:00', 0, 7, ''),
(92, 'Abstimmung Pakettboden (Herr Hülsmann)', '2024-11-26', '10:00:00', '2024-11-26', '10:30:00', 0, 7, ''),
(93, 'Treffen Raffael Sachse DLRG', '2024-11-26', '11:00:00', '2024-11-26', '12:00:00', 0, 7, ''),
(94, 'Brandschutz Herr Gerstenkorn', '2024-11-26', '12:00:00', '2024-11-26', '13:00:00', 0, 7, ''),
(95, 'Phönix Jour Fixe DKV', '2024-12-02', '11:00:00', '2024-12-02', '12:00:00', 0, 7, ''),
(96, 'Jakob Abstimmung Ordnung im LLZ (Annette\\, Emma\\, Jakob)', '2024-12-03', '15:30:00', '2024-12-03', '16:30:00', 0, 7, ''),
(97, 'Präsidiumssitzung (Präsidium + Andi)', '2024-12-04', '19:00:00', '2024-12-04', '20:00:00', 0, 7, ''),
(98, 'Termin mit Herrn Aldag (Albert\\, Christian\\, Emma)', '2024-12-09', '16:30:00', '2024-12-09', '17:30:00', 0, 7, ''),
(99, 'Abstimmung zur Kooperationsvereinbarung CJD-LKVN (Christian\\, Alber', '2024-12-10', '14:00:00', '2024-12-10', '15:00:00', 0, 7, ''),
(100, 'Anrufen Herrn Herschel', '2024-12-11', '09:00:00', '2024-12-11', '09:30:00', 0, 7, ''),
(101, 'Abstimmung - Jens Strauch Trainer C- Ausbildung', '2024-12-11', '11:00:00', '2024-12-11', '12:00:00', 0, 7, ''),
(102, 'Meldung der Vereinsmitglieder und Vereine an den LSB', '2024-12-12', NULL, '2024-12-12', NULL, 1, 7, ''),
(103, 'RZV Abstimmung mit Kjell', '2024-12-17', '10:00:00', '2024-12-17', '11:00:00', 0, 7, ''),
(104, 'Haushaltsplan und Kontoabstimmung mit Albert', '2024-12-17', '12:30:00', '2024-12-17', '13:30:00', 0, 7, ''),
(105, 'Trailerwiese (Emma\\, Annette)', '2024-12-18', '11:00:00', '2024-12-18', '12:00:00', 0, 7, ''),
(106, 'Weihnachts\"feier\" LKV', '2024-12-19', NULL, '2024-12-19', NULL, 1, 7, ''),
(107, 'Rückmeldung an Vera', '2024-12-23', NULL, '2024-12-23', NULL, 1, 7, ''),
(108, 'Rechnung ans DelMar - Inventar', '2025-01-08', NULL, '2025-01-08', NULL, 1, 7, ''),
(109, 'Einladung für den VA und Kanu-Tag', '2025-01-08', NULL, '2025-01-08', NULL, 1, 7, ''),
(110, '13:30 Termin in der Schaumburger Str.12a', '2025-01-09', NULL, '2025-01-09', NULL, 1, 7, ''),
(111, 'Auf die Mittelzuweisung vom LSB achten!', '2025-01-10', NULL, '2025-01-10', NULL, 1, 7, ''),
(112, 'Präsidiumssitzung VA+Kanutag 2025 (Präsidium)', '2025-01-10', '20:00:00', '2025-01-10', '21:00:00', 0, 7, ''),
(113, 'Gruppe Nord', '2025-01-11', '11:00:00', '2025-01-11', '16:00:00', 0, 7, ''),
(114, 'Phönix II Jour Fixe', '2025-01-13', '11:00:00', '2025-01-13', '12:00:00', 0, 7, ''),
(115, 'Abgesagt -Wohnungsbesichtigung', '2025-01-13', '13:00:00', '2025-01-13', '14:00:00', 0, 7, ''),
(116, 'Steuerbüro Marx Termin (Albert\\, Emma)', '2025-01-13', '14:30:00', '2025-01-13', '15:30:00', 0, 7, ''),
(117, 'Rennsportplanung 2025 und RS01 mit Andi', '2025-01-14', '11:30:00', '2025-01-14', '12:30:00', 0, 7, ''),
(118, '2025 Planung mit CJD', '2025-01-14', '14:00:00', '2025-01-14', '15:00:00', 0, 7, ''),
(119, 'Termin LSB Betriebskostenzuschuss LLZ (Christian\\, Albert\\, Andi\\,', '2025-01-15', '10:00:00', '2025-01-15', '11:00:00', 0, 7, ''),
(120, 'ARAG versicherungsumfang besprechen (Christian\\, Emma)', '2025-01-15', '13:00:00', '2025-01-15', '14:00:00', 0, 7, ''),
(121, 'Hohmeier Wohnungsübergabe', '2025-01-16', '10:00:00', '2025-01-16', '11:00:00', 0, 7, ''),
(122, 'Abstimmung zur Verbandsapp (Karlotta\\, Christian)', '2025-01-20', '17:00:00', '2025-01-20', '18:00:00', 0, 7, ''),
(123, 'Termin mit der VR Bank', '2025-01-21', '09:00:00', '2025-01-21', '10:00:00', 0, 7, ''),
(124, 'Anrufen Manfred Kehm', '2025-01-21', '15:15:00', '2025-01-21', '16:15:00', 0, 7, ''),
(125, 'Austausch NDSxNRW (mit Jens Lüthge)', '2025-01-22', '10:00:00', '2025-01-22', '10:45:00', 0, 7, ''),
(126, 'A! Grundsteuerbescheid Finanzamt', '2025-01-23', '13:00:00', '2025-01-23', '16:00:00', 0, 7, ''),
(127, 'Auszeichnung engagementfreundlicher Sportverein', '2025-01-23', '14:00:00', '2025-01-23', '15:00:00', 0, 7, ''),
(128, 'Sportausschussitzung DKV', '2025-01-24', NULL, '2025-01-25', NULL, 1, 7, ''),
(129, 'Andi', '2025-01-27', '10:00:00', '2025-01-27', '13:30:00', 0, 7, ''),
(130, 'Einladung VA + Rechnungen an die Vereine', '2025-01-28', NULL, '2025-01-28', NULL, 1, 7, ''),
(131, 'Schlüsselübergabe Mardorf mit Sabine', '2025-01-28', '10:00:00', '2025-01-28', '11:00:00', 0, 7, ''),
(132, 'Abbau robbe und berking', '2025-01-31', '13:00:00', '2025-01-31', '14:00:00', 0, 7, ''),
(133, 'Gesamtverwendungsnachweis LSB', '2025-02-01', '12:00:00', '2025-02-01', '13:00:00', 0, 7, ''),
(134, 'Phönix JourFix DKV', '2025-02-03', NULL, '2025-02-03', NULL, 1, 7, ''),
(135, 'Auswertung der Vereinsmeldungen (Annette\\, Emma)', '2025-02-03', '11:00:00', '2025-02-03', '12:00:00', 0, 7, ''),
(136, 'Zimmermann\\, Steckdosenabddeckung neu', '2025-02-06', '16:30:00', '2025-02-06', '17:00:00', 0, 7, ''),
(137, 'Bezirkstag Braunschweig (Christian\\, Emma)', '2025-02-08', '13:00:00', '2025-02-08', '17:00:00', 0, 7, ''),
(138, 'Bramer Gutachten Deadline', '2025-02-10', NULL, '2025-02-10', NULL, 1, 7, ''),
(139, 'Schulung Phönix mit den Fachwarten', '2025-02-10', '19:00:00', '2025-02-10', '21:00:00', 0, 7, ''),
(140, 'Besuch Clive', '2025-02-11', '12:30:00', '2025-02-11', '13:30:00', 0, 7, ''),
(141, 'Technik für Martin rausstellen', '2025-02-11', '14:00:00', '2025-02-11', '15:00:00', 0, 7, ''),
(142, 'TJORBEN Anrufen', '2025-02-12', '14:00:00', '2025-02-12', '15:00:00', 0, 7, ''),
(143, 'Abstimmung Trainingslager Kanu-Segeln', '2025-02-12', '17:00:00', '2025-02-12', '18:00:00', 0, 7, ''),
(144, 'Gesamtverwendungsnachweis LSB (Emma)', '2025-02-13', NULL, '2025-02-13', NULL, 1, 7, ''),
(145, 'Ball des Sports (Andi\\, Kjell\\, Christian\\, Annette\\, Emma)', '2025-02-14', '19:00:00', '2025-02-14', '22:00:00', 0, 7, ''),
(146, 'KARI Schulung Slalom', '2025-02-15', NULL, '2025-02-15', NULL, 1, 7, ''),
(147, 'Deadline Einreichung Gesamtverwendungsnachweis LSB', '2025-02-15', '12:00:00', '2025-02-15', '13:00:00', 0, 7, ''),
(148, 'KSGH (Emma\\, Annette)', '2025-02-17', '11:00:00', '2025-02-17', '13:00:00', 0, 7, ''),
(149, 'Planung Trainingslager Mardorf', '2025-02-18', '08:30:00', '2025-02-18', '10:00:00', 0, 7, ''),
(150, 'Abfrage Stegaufbau - Ersatzmaterial', '2025-02-18', '10:00:00', '2025-02-18', '11:00:00', 0, 7, ''),
(151, 'Abstimmung Bramer (Emma\\, Andi)', '2025-02-19', '16:00:00', '2025-02-19', '17:00:00', 0, 7, ''),
(152, 'Jugendvollversammlung der Deutschen Kanujugend (Christian\\, Emma)', '2025-02-21', NULL, '2025-02-23', NULL, 1, 7, ''),
(153, 'Angebot von Jaschke - Sanitäranlagensanierung', '2025-02-25', NULL, '2025-02-25', NULL, 1, 7, ''),
(154, 'Kennenlernen Hans Arne Siekmann Verwalterwohnung', '2025-02-25', '14:30:00', '2025-02-25', '15:30:00', 0, 7, ''),
(155, 'LSB-Topteam-Förderung L.A. 2028 (Christian\\, Andi\\, Emma)', '2025-02-25', '16:00:00', '2025-02-25', '18:00:00', 0, 7, ''),
(156, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-02-26', '09:00:00', '2025-02-26', '10:00:00', 0, 7, ''),
(157, 'Abstimmung Bramer (Albert\\, Emma\\, Christian?)', '2025-02-26', '16:00:00', '2025-02-26', '17:00:00', 0, 7, ''),
(158, 'Online Stammtisch Ständigen Konferenz der Landesfachverbände', '2025-02-26', '18:30:00', '2025-02-26', '19:30:00', 0, 7, ''),
(159, 'P. Telefonieren mit Olli', '2025-02-27', '19:00:00', '2025-02-27', '20:00:00', 0, 7, ''),
(160, 'KaRi Schulung 01 Zoom-Zugang Silvia', '2025-03-01', NULL, '2025-03-02', NULL, 1, 7, ''),
(161, 'Jubiläum LKC-Lüneburg (Christian)', '2025-03-02', '11:15:00', '2025-03-02', '14:00:00', 0, 7, ''),
(162, 'Phönix JourFix DKV', '2025-03-03', '11:00:00', '2025-03-03', '12:00:00', 0, 7, ''),
(163, 'Steuerbüro vor Ort (Albert\\, Herr Marx)', '2025-03-03', '12:00:00', '2025-03-03', '16:00:00', 0, 7, ''),
(164, 'Kassenprüfung (Michael Henne und Gerd Bode\\, Albert)', '2025-03-04', '09:00:00', '2025-03-04', '15:00:00', 0, 7, ''),
(165, 'Datenschutz und Complience LKV', '2025-03-06', '11:00:00', '2025-03-06', '12:00:00', 0, 7, ''),
(166, 'Präsidiumsabstimmung zum Thema Bramer', '2025-03-06', '14:30:00', '2025-03-06', '15:00:00', 0, 7, ''),
(167, 'P Skypen mit Patrycja', '2025-03-06', '19:00:00', '2025-03-06', '20:00:00', 0, 7, ''),
(168, 'Anruf Frau Quest', '2025-03-07', NULL, '2025-03-07', NULL, 1, 7, ''),
(169, 'Beerdigung Horst Seifarth (Birgit\\, Christian\\, Emma)', '2025-03-07', '10:30:00', '2025-03-07', '11:30:00', 0, 7, ''),
(170, 'Leistungssport aktuell (online) (Christian\\, Emma\\, Andi (angemelde', '2025-03-07', '16:00:00', '2025-03-07', '19:00:00', 0, 7, ''),
(171, 'P FL', '2025-03-08', NULL, '2025-03-11', NULL, 1, 7, ''),
(172, 'Geburtstag Margot 1971', '2025-03-11', NULL, '2025-03-11', NULL, 1, 7, ''),
(173, 'Termin Team Leistungssport OSP (Albert\\, Christian\\, Emma)', '2025-03-13', '15:00:00', '2025-03-13', '16:00:00', 0, 7, ''),
(174, 'P FL', '2025-03-15', NULL, '2025-03-16', NULL, 1, 7, ''),
(175, 'Mail an Vereine', '2025-03-17', NULL, '2025-03-17', NULL, 1, 7, ''),
(176, 'SEPA Einzug 2. Rate Vereine Phönix!', '2025-03-18', NULL, '2025-03-18', NULL, 1, 7, ''),
(177, 'P Abholung Waschbeckenunterschrank', '2025-03-18', '17:30:00', '2025-03-18', '18:00:00', 0, 7, ''),
(178, 'Termin Thomas Iseke Kiosk + Wohnung (Albert\\, Christian)', '2025-03-19', '16:00:00', '2025-03-19', '17:00:00', 0, 7, ''),
(179, 'in Osnabrück', '2025-03-21', NULL, '2025-03-23', NULL, 1, 7, ''),
(180, 'VA und Kanu-Tag', '2025-03-22', NULL, '2025-03-23', NULL, 1, 7, ''),
(181, 'Haushaltsplanberatung Online (Christian)', '2025-03-22', NULL, '2025-03-22', NULL, 1, 7, ''),
(182, 'Rechnungen Einzelmitglieder!', '2025-03-24', '10:00:00', '2025-03-24', '11:00:00', 0, 7, ''),
(183, 'Jordy kommt zum Post packen', '2025-03-25', NULL, '2025-03-25', NULL, 1, 7, ''),
(184, 'Japan Austausch Slalom Abstimmung (Schubert\\, Pannek\\, Blume\\, Jahn', '2025-03-26', '19:30:00', '2025-03-26', '20:30:00', 0, 7, ''),
(185, 'Trainer C-Ausbildung Hannover', '2025-03-28', NULL, '2025-03-30', NULL, 1, 7, ''),
(186, 'Viko NRW Abstimmung zum DKV Leistungssport und GmbH (Jens Lüthge\\,', '2025-03-31', '18:00:00', '2025-03-31', '18:45:00', 0, 7, ''),
(187, 'Telefontermin Hampe', '2025-04-01', '14:00:00', '2025-04-01', '15:00:00', 0, 7, ''),
(188, 'P  Wübke preetz und Hannover', '2025-04-02', NULL, '2025-04-06', NULL, 1, 7, ''),
(189, 'Urlaub Emma', '2025-04-02', NULL, '2025-04-02', NULL, 1, 7, ''),
(190, 'P Kleidertausch Preetz', '2025-04-02', '18:30:00', '2025-04-02', '19:30:00', 0, 7, ''),
(191, 'Urlaub Emma', '2025-04-04', NULL, '2025-04-04', NULL, 1, 7, ''),
(192, 'Phönix JourFix', '2025-04-07', '11:00:00', '2025-04-07', '12:00:00', 0, 7, ''),
(193, 'Präsidiumssitzung (Martin\\, Chistian\\, Andi\\, Annette\\, Emma)', '2025-04-07', '15:00:00', '2025-04-07', '16:00:00', 0, 7, ''),
(194, '? Axel DelMar', '2025-04-08', '15:00:00', '2025-04-08', '16:00:00', 0, 7, ''),
(195, 'Austausch Drachenboot Großveranstaltungstreffen HKC & WKG & NRW', '2025-04-09', '18:00:00', '2025-04-09', '19:00:00', 0, 7, ''),
(196, 'Kira Geburtstag', '2025-04-11', '19:00:00', '2025-04-11', '23:00:00', 0, 7, ''),
(197, 'Arbeitseinsatz Mardorf', '2025-04-12', '10:00:00', '2025-04-12', '15:00:00', 0, 7, ''),
(198, 'P Geburtstagsparty Paul', '2025-04-12', '18:00:00', '2025-04-12', '23:00:00', 0, 7, ''),
(199, 'P Scott Übernachtung ab 18 Uhr', '2025-04-13', NULL, '2025-04-14', NULL, 1, 7, ''),
(200, 'DKV GmbH Kündigung', '2025-04-15', '20:00:00', '2025-04-15', '21:00:00', 0, 7, ''),
(201, 'Präsidiumsabstimmung Kanu-Sport', '2025-04-16', '19:00:00', '2025-04-16', '20:00:00', 0, 7, ''),
(202, 'Anrufen Sabine CJD wegen kanu-Kurs', '2025-04-23', NULL, '2025-04-23', NULL, 1, 7, ''),
(203, 'Abstimmungs Viko Auslandsbesprechung Japan (Christian\\, Katharina\\,', '2025-04-23', '09:00:00', '2025-04-23', '10:00:00', 0, 7, ''),
(204, 'Austausch NDSxNRW (mit Jens Lüthge)', '2025-04-23', '10:00:00', '2025-04-23', '10:45:00', 0, 7, ''),
(205, 'KANUTAG DKV (VA) (Christian\\, Emma)', '2025-04-25', NULL, '2025-04-27', NULL, 1, 7, ''),
(206, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-04-25', '09:00:00', '2025-04-25', '10:00:00', 0, 7, ''),
(207, 'Anmeldung für Ständigen Konferenz der Landesfachverbände 09.05.', '2025-04-28', NULL, '2025-04-28', NULL, 1, 7, ''),
(208, 'RZV light Gespräch im LLZ (Jens\\, Andi\\, Jan\\, Kjell\\, Christian\\,', '2025-04-29', '10:00:00', '2025-04-29', '14:00:00', 0, 7, ''),
(209, 'Emma', '2025-04-30', NULL, '2025-05-04', NULL, 1, 7, ''),
(210, 'Zahlung der Pacht KSGH', '2025-05-01', NULL, '2025-05-01', NULL, 1, 7, ''),
(211, 'Ständigen Konferenz der Landesfachverbände (Christian)', '2025-05-03', '17:30:00', '2025-05-03', '18:30:00', 0, 7, ''),
(212, 'Phönix JourFix', '2025-05-05', '11:00:00', '2025-05-05', '12:00:00', 0, 7, ''),
(213, 'Prüfung Gelände LLZ -Stadt Hannover (GS\\, Andi)', '2025-05-06', '13:00:00', '2025-05-06', '14:00:00', 0, 7, ''),
(214, 'Einführung und Vorstellung Vereins App- Zoom', '2025-05-07', '19:30:00', '2025-05-07', '20:00:00', 0, 7, ''),
(215, 'Arbeitstagung zur Umsetzung des Präventionskonzeptes zum Schutz vo', '2025-05-08', '17:00:00', '2025-05-08', '20:00:00', 0, 7, ''),
(216, 'PISG Arbeitstagung (Christian\\, Emma)', '2025-05-08', '17:00:00', '2025-05-08', '20:00:00', 0, 7, ''),
(217, 'Abstimmungswochenende NODM Markleeberg (Ole? Jemand weiteren aus de', '2025-05-09', NULL, '2025-05-10', NULL, 1, 7, ''),
(218, 'Ständigen Konferenz der Landesfachverbände LSB', '2025-05-09', '17:00:00', '2025-05-09', '20:30:00', 0, 7, ''),
(219, 'LSB Leistungssportkonferenz NORD', '2025-05-10', NULL, '2025-05-10', NULL, 1, 7, ''),
(220, 'Besuch von Jens Lüthge', '2025-05-12', '09:00:00', '2025-05-12', '16:00:00', 0, 7, ''),
(221, 'P Bürgeramt Anmeldung Hannover', '2025-05-13', '09:45:00', '2025-05-13', '10:00:00', 0, 7, ''),
(222, 'Vorstellungsgespräch Frank Hinze (Nachwuchstrainer)', '2025-05-14', '18:00:00', '2025-05-14', '19:00:00', 0, 7, ''),
(223, 'Abstimmung Phönix Seminarleiter', '2025-05-15', '15:00:00', '2025-05-15', '15:30:00', 0, 7, ''),
(224, '1. Ländergipfel der Deutschen Kanujugend 2025', '2025-05-15', '19:30:00', '2025-05-15', '20:30:00', 0, 7, ''),
(225, 'P Spargelregatta', '2025-05-16', NULL, '2025-05-18', NULL, 1, 7, ''),
(226, 'Jugendvollversammlung (Lukas Lamberti)', '2025-05-17', '11:00:00', '2025-05-17', '15:00:00', 0, 7, ''),
(227, 'Günther Ehrung / Treffen Landesmeisterschaft in Limmer', '2025-05-18', NULL, '2025-05-18', NULL, 1, 7, ''),
(228, 'Gruppe Nord Treffen Viko (SH\\, HB\\, HH\\, MV\\, NDS)', '2025-05-20', '20:00:00', '2025-05-20', '21:00:00', 0, 7, ''),
(229, 'Besuch von Thorsten Weil', '2025-05-23', '14:00:00', '2025-05-23', '16:00:00', 0, 7, ''),
(230, 'Platzversammlung Mardorf', '2025-05-24', NULL, '2025-05-24', NULL, 1, 7, ''),
(231, 'Phönix Schulung Seminare und Lizenzen', '2025-05-26', '09:30:00', '2025-05-26', '12:30:00', 0, 7, ''),
(232, 'Urlaub Emma', '2025-05-28', NULL, '2025-05-30', NULL, 1, 7, ''),
(233, 'P DM Berlin', '2025-05-29', NULL, '2025-06-01', NULL, 1, 7, ''),
(234, 'Ausschüttung des Sparkassenfonts', '2025-05-30', NULL, '2025-05-30', NULL, 1, 7, ''),
(235, 'Phönix JourFix', '2025-06-02', '11:00:00', '2025-06-02', '12:00:00', 0, 7, ''),
(236, 'Gespräch mit Anna Marit Blunk (Andi\\, Christian)', '2025-06-02', '18:00:00', '2025-06-02', '19:00:00', 0, 7, ''),
(237, 'Andi beim Notar Vereinsregistereintrag (ANDI)', '2025-06-03', '11:00:00', '2025-06-03', '12:00:00', 0, 7, ''),
(238, 'Telefontermin Hampe bezüglich Sache Bramer', '2025-06-05', '15:00:00', '2025-06-05', '16:00:00', 0, 7, ''),
(239, 'P Pfingstwettfahrten Mardorf', '2025-06-07', NULL, '2025-06-08', NULL, 1, 7, ''),
(240, 'Abendessen mit Annette', '2025-06-10', '19:00:00', '2025-06-10', '20:00:00', 0, 7, ''),
(241, 'Austausch NDSxNRW (mit Jens Lüthge)', '2025-06-11', '10:00:00', '2025-06-11', '10:45:00', 0, 7, ''),
(242, 'Projektantrag Makro/Mirkro Projekte LSB', '2025-06-12', '10:00:00', '2025-06-12', '11:00:00', 0, 7, ''),
(243, 'P Thorge Geburtstagsparty', '2025-06-13', NULL, '2025-06-13', NULL, 1, 7, ''),
(244, 'P Wochenende in Kiel', '2025-06-13', NULL, '2025-06-15', NULL, 1, 7, ''),
(245, 'Jannik Baumann', '2025-06-16', '10:00:00', '2025-06-16', '11:00:00', 0, 7, ''),
(246, 'Satzungsfindungskommission DKV (Christian)', '2025-06-17', '19:00:00', '2025-06-17', '20:00:00', 0, 7, ''),
(247, 'SEPA Einzug 3. Rate Vereine Phönix!', '2025-06-18', '09:00:00', '2025-06-18', '10:00:00', 0, 7, ''),
(248, 'Antrag für Baumaßnahmen bis 30.06. LSB', '2025-06-18', '12:00:00', '2025-06-18', '13:00:00', 0, 7, ''),
(249, 'Anmeldung zum Jubiläum Bederkesa am 5.7.', '2025-06-19', '10:00:00', '2025-06-19', '11:00:00', 0, 7, ''),
(250, 'P Rapscup Preetz', '2025-06-21', NULL, '2025-06-22', NULL, 1, 7, ''),
(251, 'Tag der offenen Tür KC Limmer', '2025-06-22', '14:00:00', '2025-06-22', '18:00:00', 0, 7, ''),
(252, 'Ausbildungsabstimmung DKV (Jens Strauch\\, Christian)', '2025-06-23', '19:00:00', '2025-06-23', '20:00:00', 0, 7, ''),
(253, 'LLZ Gasumstellung (Annette/Emma)', '2025-06-24', '08:00:00', '2025-06-24', '10:00:00', 0, 7, ''),
(254, 'LSB Leistungssport aktuell (Andi)', '2025-06-24', '17:00:00', '2025-06-24', '19:00:00', 0, 7, ''),
(255, 'Gruppe Nord Treffen', '2025-06-25', '20:00:00', '2025-06-25', '21:00:00', 0, 7, ''),
(256, 'NRW NDS Abstimmung mit Jens', '2025-06-27', '10:00:00', '2025-06-27', '11:00:00', 0, 7, ''),
(257, 'Online-Austausch der Landesfachverbände mit LSB', '2025-07-01', '19:00:00', '2025-07-01', '20:00:00', 0, 7, ''),
(258, 'Abstimmung Bilanz 2024 Marx (Albert\\, Herr Marx)', '2025-07-04', NULL, '2025-07-04', NULL, 1, 7, ''),
(259, 'Norddeutsche Meisterschaft in Hamburg', '2025-07-04', NULL, '2025-07-06', NULL, 1, 7, ''),
(260, 'P Regatta Bad Segeberg', '2025-07-05', NULL, '2025-07-06', NULL, 1, 7, ''),
(261, 'Jubiläum 75 Jahre WS Bederkesa (Christian? Martin)', '2025-07-05', '12:00:00', '2025-07-05', '15:00:00', 0, 7, ''),
(262, 'Auslandstreffen LSB (Jan\\, Christian\\, Christoph/Frank\\, Emma)', '2025-07-07', '12:00:00', '2025-07-07', '13:00:00', 0, 7, ''),
(263, 'EPP Abstimmung mit Kjell', '2025-07-09', '09:30:00', '2025-07-09', '10:30:00', 0, 7, ''),
(264, 'Abstimmung Förderantrag Carsten', '2025-07-09', '14:30:00', '2025-07-09', '15:15:00', 0, 7, ''),
(265, 'P Treffen mit Marius', '2025-07-09', '16:30:00', '2025-07-09', '20:00:00', 0, 7, ''),
(266, 'Rückmeldung Katharina - 26.08. LSB Afrika Projekt', '2025-07-10', '10:00:00', '2025-07-10', '10:15:00', 0, 7, ''),
(267, 'P Sommerfest bei Anna', '2025-07-12', NULL, '2025-07-12', NULL, 1, 7, ''),
(268, 'P telefonieren mit Olli', '2025-07-14', '19:00:00', '2025-07-14', '20:00:00', 0, 7, ''),
(269, 'Überweisung Grundsteuer Realgemeinde', '2025-07-16', NULL, '2025-07-16', NULL, 1, 7, ''),
(270, 'Vesterling - Waschbecken Toilette Herren EG', '2025-07-18', '07:45:00', '2025-07-18', '08:45:00', 0, 7, ''),
(271, 'Emma Urlaub', '2025-07-19', NULL, '2025-08-03', NULL, 1, 7, ''),
(272, 'P EC Brixham', '2025-07-20', NULL, '2025-07-26', NULL, 1, 7, ''),
(273, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-07-25', '09:00:00', '2025-07-25', '10:00:00', 0, 7, ''),
(274, 'Frist Jahresrechnung und Rücklagenermittlung 2024 (Emma\\, Albert)', '2025-08-04', NULL, '2025-08-04', NULL, 1, 7, ''),
(275, 'Eintragung Interantionale Projekte 2026 (Christian)', '2025-08-04', NULL, '2025-08-04', NULL, 1, 7, ''),
(276, 'P Sylt bei Anton', '2025-08-08', NULL, '2025-08-10', NULL, 1, 7, ''),
(277, 'Emma Urlaub', '2025-08-08', NULL, '2025-08-08', NULL, 1, 7, ''),
(278, 'Urlaubsübergabe (Annette)', '2025-08-08', '11:00:00', '2025-08-08', '12:00:00', 0, 7, ''),
(279, 'Essengehn mit Karl Hauck (Andi\\, Christian\\, Karl)', '2025-08-10', '19:30:00', '2025-08-10', '20:30:00', 0, 7, ''),
(280, 'Termin Finals 2026 in Hannover (Andi\\, Alex\\, Karl Hauck und Finals', '2025-08-11', NULL, '2025-08-11', NULL, 1, 7, ''),
(281, 'P Fahrzeug Zulassung', '2025-08-13', '10:30:00', '2025-08-13', '10:45:00', 0, 7, ''),
(282, 'Anmeldung Sportjugend Niedersachsen (??)', '2025-08-13', '12:00:00', '2025-08-13', '13:00:00', 0, 7, ''),
(283, 'P Telefonieren mit Olli', '2025-08-14', '19:00:00', '2025-08-14', '22:00:00', 0, 7, ''),
(284, 'P Herbstwettfahrten Mardorf', '2025-08-15', NULL, '2025-08-17', NULL, 1, 7, ''),
(285, 'Termin Jan Steuer (Andi\\, Christian\\, Jan\\, Emma)', '2025-08-19', '18:30:00', '2025-08-19', '19:30:00', 0, 7, ''),
(286, 'P Einladung zur Ressorttagung Kanu-Segeln', '2025-08-20', '16:00:00', '2025-08-20', '17:00:00', 0, 7, ''),
(287, 'Info termin \"Finals 2026\" (Andi)', '2025-08-21', '19:00:00', '2025-08-21', '20:00:00', 0, 7, ''),
(288, 'CJD in Mardorf (Martin\\, Christian\\, Emma\\, Sabine\\, Lucas)', '2025-08-22', '14:00:00', '2025-08-22', '15:00:00', 0, 7, ''),
(289, 'Betriebskostenzuschuss einreichen!', '2025-08-25', NULL, '2025-08-25', NULL, 1, 7, ''),
(290, 'Slalomabstimmung (Frank\\, Christoph\\, Andi\\, Christian\\, Emma)', '2025-08-25', '19:00:00', '2025-08-25', '20:00:00', 0, 7, ''),
(291, 'Regionstreffen EasternCape LSB (Christian\\, Jan)', '2025-08-26', '10:45:00', '2025-08-26', '13:30:00', 0, 7, ''),
(292, '!!! Deadline Betriebskostenzuschuss', '2025-08-29', NULL, '2025-08-29', NULL, 1, 7, ''),
(293, 'P Herbstwettfahrten Ratzeburg', '2025-08-30', NULL, '2025-08-31', NULL, 1, 7, ''),
(294, 'JourFix Phönix', '2025-09-01', '11:00:00', '2025-09-01', '12:00:00', 0, 7, ''),
(295, 'P Flensburg liebt dich Marathon', '2025-09-07', '09:00:00', '2025-09-07', '14:00:00', 0, 7, ''),
(296, 'Besuch der Geschäftsstelle NRW (Andi\\, Annette\\, Emma)', '2025-09-10', '15:00:00', '2025-09-11', '18:00:00', 0, 7, ''),
(297, 'Rückmeldung Ehrenamtspreis Christian', '2025-09-15', '10:00:00', '2025-09-15', '11:00:00', 0, 7, ''),
(298, 'Tagung der Geschäftsführer*innen von Landesfachverbänden und Spo', '2025-09-16', '09:30:00', '2025-09-16', '16:30:00', 0, 7, ''),
(299, 'Informationstermine (ONLINE) zur Erstellung der Jahresberichtsmaske', '2025-09-17', '12:00:00', '2025-09-17', '13:00:00', 0, 7, ''),
(300, 'P Regatta Bad Zwischenahn', '2025-09-19', NULL, '2025-09-21', NULL, 1, 7, ''),
(301, 'Vollversammlung der Sportjugend Niedersachsen (?)', '2025-09-20', NULL, '2025-09-21', NULL, 1, 7, ''),
(302, 'Jubiläum Saarländischer Kanu-Bund', '2025-09-21', NULL, '2025-09-21', NULL, 1, 7, ''),
(303, 'Leistungssport Aktuell LSB', '2025-09-23', '17:00:00', '2025-09-23', '19:00:00', 0, 7, ''),
(304, 'Pachtzahlung Realgemeinde Mardorf', '2025-09-24', '10:00:00', '2025-09-24', '11:00:00', 0, 7, ''),
(305, 'Jubiläumsfeier LKV Bremen', '2025-09-28', '10:30:00', '2025-09-28', '15:30:00', 0, 7, ''),
(306, 'Essen für die Flüge vorbestellen', '2025-10-06', NULL, '2025-10-06', NULL, 1, 7, ''),
(307, 'P Geburtstagswochenende', '2025-10-10', NULL, '2025-10-12', NULL, 1, 7, ''),
(308, 'Emma Urlaub', '2025-10-10', NULL, '2025-10-10', NULL, 1, 7, ''),
(309, 'Ressorttagung Kanu-Rennsport DKV', '2025-10-10', NULL, '2025-10-12', NULL, 1, 7, ''),
(310, 'Japan Austausch Slalom', '2025-10-14', NULL, '2025-10-22', NULL, 1, 7, ''),
(311, 'Ressorttagung Kanu-Slalom', '2025-10-17', NULL, '2025-10-19', NULL, 1, 7, ''),
(312, 'P Rückmeldung vom Finanzamt Steuerrückzahlung', '2025-10-24', NULL, '2025-10-24', NULL, 1, 7, ''),
(313, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-10-27', '09:00:00', '2025-10-27', '10:00:00', 0, 7, ''),
(314, 'Freizeitsporttagung', '2025-11-02', '10:00:00', '2025-11-02', '17:00:00', 0, 7, ''),
(315, 'Anträge Auslandsprojekte', '2025-11-06', '10:00:00', '2025-11-06', '11:00:00', 0, 7, ''),
(316, 'Lehrgang für die Trainer', '2025-11-07', '18:00:00', '2025-11-07', '21:15:00', 0, 7, ''),
(317, 'Mittelabruf Lotto Sport Internatanträge', '2025-11-11', '09:00:00', '2025-11-11', '10:00:00', 0, 7, ''),
(318, 'Abrechnung NK2 Förderung Slalom + Rennsport', '2025-11-13', '11:00:00', '2025-11-13', '12:00:00', 0, 7, ''),
(319, 'P PS Konzert', '2025-11-13', '20:00:00', '2025-11-13', '22:30:00', 0, 7, ''),
(320, 'DKV VA Duisburg (Christian und Emma)', '2025-11-21', NULL, '2025-11-23', NULL, 1, 7, ''),
(321, 'VA DKV (Christian\\, Emma)', '2025-11-22', NULL, '2025-11-22', NULL, 1, 7, ''),
(322, 'LKV VA??', '2025-11-23', NULL, '2025-11-23', NULL, 1, 7, ''),
(323, 'LSB 32. Trainerseminar', '2025-12-01', NULL, '2025-12-02', NULL, 1, 7, ''),
(324, 'Planung eines EM - Lehrgang / Training', '2026-01-07', NULL, '2026-01-07', NULL, 1, 7, ''),
(325, 'Nebenkostenabrechnungen erstellen!', '2026-01-08', '12:00:00', '2026-01-08', '13:00:00', 0, 7, ''),
(326, 'Abschlussberichte an die LottoSport', '2026-01-15', '09:00:00', '2026-01-15', '10:00:00', 0, 7, ''),
(327, 'Trainer-C-Ausbildung', '2026-02-07', '09:30:00', '2026-02-07', '11:00:00', 0, 7, ''),
(328, 'Verwendungsnachweis Betriebskostenzuschuss (Emma)', '2026-03-23', NULL, '2026-03-23', NULL, 1, 7, ''),
(329, 'VA DKV (Christian\\, Emma)', '2026-04-18', NULL, '2026-04-18', NULL, 1, 7, ''),
(330, 'Deadline !!!Betriebskostenzuschussabgabe!!!', '2026-04-27', NULL, '2026-04-27', NULL, 1, 7, ''),
(331, 'Verlängerung Phönix', '2026-09-15', NULL, '2026-09-15', NULL, 1, 7, ''),
(332, 'Vertragsende Phönix vom 11.12.203', '2026-12-11', NULL, '2026-12-11', NULL, 1, 7, ''),
(333, '? Kanu-Tag', '2027-02-21', NULL, '2027-02-21', NULL, 1, 7, ''),
(334, 'Urlaub Annette (Annette)', '2023-03-28', NULL, '2023-03-28', NULL, 1, 10, ''),
(335, ' (Hannelore und Andreas)', '2023-04-27', NULL, '2023-04-27', NULL, 1, 10, ''),
(336, ' (Annette)', '2023-05-19', NULL, '2023-05-19', NULL, 1, 10, ''),
(337, ' (Annette)', '2023-05-22', NULL, '2023-05-26', NULL, 1, 10, ''),
(338, ' (Annette)', '2023-05-30', NULL, '2023-06-02', NULL, 1, 10, ''),
(339, ' (Annette)', '2023-09-04', NULL, '2023-09-08', NULL, 1, 10, ''),
(340, 'Annette', '2023-09-11', NULL, '2023-09-15', NULL, 1, 10, ''),
(341, 'Annette', '2023-12-04', NULL, '2023-12-08', NULL, 1, 10, ''),
(342, 'Annette', '2023-12-22', NULL, '2023-12-22', NULL, 1, 10, ''),
(343, 'Annette', '2023-12-27', NULL, '2023-12-29', NULL, 1, 10, ''),
(344, 'Annette', '2024-01-02', NULL, '2024-01-05', NULL, 1, 10, ''),
(345, 'Albert', '2024-03-16', NULL, '2024-03-23', NULL, 1, 10, ''),
(346, 'Karfreitag', '2024-03-29', NULL, '2024-03-29', NULL, 1, 10, ''),
(347, 'Ostern', '2024-03-31', NULL, '2024-04-01', NULL, 1, 10, ''),
(348, 'Emma\\, Annette (Emma\\, Annette)', '2024-04-02', NULL, '2024-04-02', NULL, 1, 10, ''),
(349, '1. Mai', '2024-05-01', NULL, '2024-05-01', NULL, 1, 10, ''),
(350, 'Himmelfahrt', '2024-05-09', NULL, '2024-05-09', NULL, 1, 10, ''),
(351, 'Emma\\, Annette', '2024-05-10', NULL, '2024-05-10', NULL, 1, 10, ''),
(352, 'Annette', '2024-05-10', NULL, '2024-05-10', NULL, 1, 10, ''),
(353, 'Pfingsten', '2024-05-19', NULL, '2024-05-20', NULL, 1, 10, ''),
(354, 'Annette', '2024-05-24', NULL, '2024-05-27', NULL, 1, 10, ''),
(355, 'Emma', '2024-06-24', NULL, '2024-06-25', NULL, 1, 10, ''),
(356, 'Christian', '2024-06-29', NULL, '2024-07-14', NULL, 1, 10, ''),
(357, 'Annette', '2024-07-08', NULL, '2024-07-19', NULL, 1, 10, ''),
(358, 'Birgit', '2024-07-18', NULL, '2024-07-29', NULL, 1, 10, ''),
(359, 'Emma Urlaub/WM IC Travemünde (Emma)', '2024-07-20', NULL, '2024-07-29', NULL, 1, 10, ''),
(360, 'Albert', '2024-09-14', NULL, '2024-10-06', NULL, 1, 10, ''),
(361, 'Andi (OP)', '2024-09-23', NULL, '2024-09-27', NULL, 1, 10, ''),
(362, 'Andi in der Reha in Hamburg', '2024-09-30', NULL, '2024-10-18', NULL, 1, 10, ''),
(363, 'Urlaub Annette', '2024-09-30', NULL, '2024-10-02', NULL, 1, 10, ''),
(364, 'Tag der deutschen Einheit', '2024-10-03', NULL, '2024-10-03', NULL, 1, 10, ''),
(365, 'Urlaub Annette', '2024-10-04', NULL, '2024-10-04', NULL, 1, 10, ''),
(366, 'Urlaub Emma', '2024-10-12', NULL, '2024-10-27', NULL, 1, 10, ''),
(367, 'Reformationstag', '2024-10-31', NULL, '2024-10-31', NULL, 1, 10, ''),
(368, 'Annette', '2024-12-23', NULL, '2024-12-23', NULL, 1, 10, ''),
(369, 'Heiligabend', '2024-12-24', NULL, '2024-12-24', NULL, 1, 10, ''),
(370, 'Weihnachten', '2024-12-25', NULL, '2024-12-26', NULL, 1, 10, ''),
(371, 'Annette', '2024-12-27', NULL, '2024-12-27', NULL, 1, 10, ''),
(372, 'Annette', '2024-12-30', NULL, '2024-12-30', NULL, 1, 10, ''),
(373, 'Silvester', '2024-12-31', NULL, '2024-12-31', NULL, 1, 10, ''),
(374, 'Neujahr', '2025-01-01', NULL, '2025-01-01', NULL, 1, 10, ''),
(375, 'Annette', '2025-01-02', NULL, '2025-01-03', NULL, 1, 10, ''),
(376, 'Urlaub Emma', '2025-04-02', NULL, '2025-04-02', NULL, 1, 10, ''),
(377, 'Urlaub Emma', '2025-04-04', NULL, '2025-04-04', NULL, 1, 10, ''),
(378, 'Martin Urlaub', '2025-04-12', NULL, '2025-04-20', NULL, 1, 10, ''),
(379, 'OSTERN', '2025-04-18', NULL, '2025-04-21', NULL, 1, 10, ''),
(380, 'Emma', '2025-04-30', NULL, '2025-05-04', NULL, 1, 10, ''),
(381, 'Annette Urlaub', '2025-05-01', NULL, '2025-05-04', NULL, 1, 10, ''),
(382, 'Andi Urlaub', '2025-05-19', NULL, '2025-05-30', NULL, 1, 10, ''),
(383, 'Annette Urlaub', '2025-05-27', NULL, '2025-06-06', NULL, 1, 10, ''),
(384, 'Urlaub Emma', '2025-05-28', NULL, '2025-05-30', NULL, 1, 10, ''),
(385, 'Pfingsten', '2025-06-09', NULL, '2025-06-09', NULL, 1, 10, ''),
(386, 'Annette Urlaub', '2025-06-27', NULL, '2025-06-27', NULL, 1, 10, ''),
(387, 'Annette Urlaub', '2025-07-11', NULL, '2025-07-11', NULL, 1, 10, ''),
(388, 'Emma Urlaub', '2025-07-19', NULL, '2025-08-03', NULL, 1, 10, ''),
(389, 'Vera Urlaub', '2025-08-01', NULL, '2025-08-21', NULL, 1, 10, ''),
(390, 'Emma Urlaub', '2025-08-08', NULL, '2025-08-08', NULL, 1, 10, ''),
(391, 'Annette Urlaub', '2025-08-11', NULL, '2025-08-22', NULL, 1, 10, ''),
(392, 'Annette Geburtstag', '2025-08-19', NULL, '2025-08-19', NULL, 1, 10, ''),
(393, 'Annette Urlaub', '2025-09-29', NULL, '2025-10-02', NULL, 1, 10, ''),
(394, 'Emma Urlaub', '2025-10-10', NULL, '2025-10-10', NULL, 1, 10, ''),
(395, '1. Rate DKV-Beitrag (Annette)', '2023-02-01', NULL, '2023-02-02', NULL, 1, 6, ''),
(396, 'Mitgliederzahlen an DKV', '2023-03-10', NULL, '2023-03-10', NULL, 1, 6, ''),
(397, '2. Rate DKV-Beitrag (Annette)', '2023-04-01', NULL, '2023-04-02', NULL, 1, 6, ''),
(398, 'DKV-Tag (VA DKV)', '2023-04-21', NULL, '2023-04-23', NULL, 1, 6, ''),
(399, '3. Rate DKV-Beitrag (Annette)', '2023-07-01', NULL, '2023-07-02', NULL, 1, 6, ''),
(400, 'VA DKV (CW)', '2023-11-18', NULL, '2023-11-18', NULL, 1, 6, ''),
(401, 'Ressortagung Parakanu 2025 (online)', '2025-02-18', NULL, '2025-02-18', NULL, 1, 6, ''),
(402, 'Frühjahrstagung Kanu-Polo (online)', '2025-03-01', NULL, '2025-03-01', NULL, 1, 6, ''),
(403, 'DKV GmbH Kündigung', '2025-04-15', '20:00:00', '2025-04-15', '21:00:00', 0, 6, ''),
(404, 'Jubiläum Saarländischer Kanu-Bund', '2025-09-21', NULL, '2025-09-21', NULL, 1, 6, ''),
(405, 'Ressorttagung Kanu-Rennsport DKV', '2025-10-10', NULL, '2025-10-12', NULL, 1, 6, ''),
(406, 'Monatsmeldung Tourismus (Annette)', '2023-01-04', NULL, '2023-01-04', NULL, 1, 9, ''),
(407, 'Berichte für VA und LKV-Tag anfordern (Annette)', '2023-01-13', NULL, '2023-01-13', NULL, 1, 9, ''),
(408, 'Einladung LKV-Tag VA', '2023-01-16', NULL, '2023-01-16', NULL, 1, 9, ''),
(409, 'Rechnungen versenden', '2023-01-16', NULL, '2023-01-16', NULL, 1, 9, ''),
(410, 'Kassenberichte der Vereine prüfen', '2023-01-19', NULL, '2023-01-19', NULL, 1, 9, ''),
(411, 'LSB EM melden (Präsident)', '2023-01-25', NULL, '2023-01-25', NULL, 1, 9, ''),
(412, 'Rechnungen LLZ (Annette)', '2023-01-27', NULL, '2023-01-27', NULL, 1, 9, ''),
(413, 'Adressänderung Vereine abfragen (Annette)', '2023-02-11', NULL, '2023-02-11', NULL, 1, 9, ''),
(414, 'Kassenprüfung (Albert)', '2023-03-14', NULL, '2023-03-14', NULL, 1, 9, ''),
(415, 'VA LKVN (Verbandsausschuss LKVN)', '2023-03-18', '10:00:00', '2023-03-18', '18:00:00', 0, 9, ''),
(416, 'VA Abendessen', '2023-03-18', '19:00:00', '2023-03-18', '22:00:00', 0, 9, ''),
(417, 'Kanutag LKVN (LKVN)', '2023-03-19', '10:00:00', '2023-03-19', '16:00:00', 0, 9, ''),
(418, 'Stegplatzmeldung zum 1. Juni', '2023-05-12', NULL, '2023-05-12', NULL, 1, 9, ''),
(419, 'Zahlung Pacht und Grundsteuer Realgemeinde Mardorf (GS)', '2023-09-28', NULL, '2023-09-28', NULL, 1, 9, ''),
(420, 'Kalenderbestellung', '2023-10-09', NULL, '2023-10-09', NULL, 1, 9, ''),
(421, 'DKV Nachwuchtagung', '2023-10-26', NULL, '2023-10-28', NULL, 1, 9, ''),
(422, 'Stegabbau', '2023-11-15', NULL, '2023-11-15', NULL, 1, 9, ''),
(423, 'Stegplanung (Bedarf Holz ermitteln und ggf. nachbestellen)', '2023-12-15', NULL, '2023-12-15', NULL, 1, 9, ''),
(424, 'Ablesung Wasserzähler (GS)', '2023-12-15', NULL, '2023-12-15', NULL, 1, 9, ''),
(425, 'Rechnungen LLZ (Annette)', '2023-12-19', NULL, '2023-12-19', NULL, 1, 9, ''),
(426, 'Weihnachten', '2023-12-25', NULL, '2023-12-26', NULL, 1, 9, ''),
(427, 'Neujahr', '2024-01-01', NULL, '2024-01-01', NULL, 1, 9, ''),
(428, 'Seglerliste SVN anfordern (GS)', '2024-02-05', NULL, '2024-02-05', NULL, 1, 9, ''),
(429, 'Umzug GS', '2024-02-19', NULL, '2024-02-21', NULL, 1, 9, ''),
(430, 'VA', '2024-03-09', NULL, '2024-03-09', NULL, 1, 9, ''),
(431, 'KSGH Mardorf Abstimmung', '2024-03-11', '18:00:00', '2024-03-11', '19:30:00', 0, 9, ''),
(432, 'Nds. Landesforsten (GS)', '2024-03-13', NULL, '2024-03-13', NULL, 1, 9, ''),
(433, ' (Annette)', '2024-03-14', NULL, '2024-03-14', NULL, 1, 9, ''),
(434, 'LSB BKZ (GS)', '2024-03-15', NULL, '2024-03-15', NULL, 1, 9, ''),
(435, 'Monatsmeldung Tourismus (Annette)', '2024-04-04', NULL, '2024-04-04', NULL, 1, 9, ''),
(436, 'Abstimmung Mardorf Booking.com (Annette\\, Albert\\, Christian\\, Brig', '2024-04-04', '10:00:00', '2024-04-04', '11:00:00', 0, 9, ''),
(437, 'Phönix II Jour Fix (Albert\\, Emma)', '2024-04-08', '11:00:00', '2024-04-08', '12:00:00', 0, 9, ''),
(438, 'Abstimmung zu Mardorf (Albert\\, Christian\\, Emma)', '2024-04-09', NULL, '2024-04-09', NULL, 1, 9, ''),
(439, 'Phönix Abstimmung Kaderlisten (Andi\\, Christian\\, Emma)', '2024-04-09', '15:00:00', '2024-04-09', '16:00:00', 0, 9, ''),
(440, 'Phönix Schulung Fakturierung (Emma)', '2024-04-10', '10:00:00', '2024-04-10', '11:30:00', 0, 9, ''),
(441, 'Besprechung Freizeitsport (Martin\\, Emma)', '2024-04-15', '15:00:00', '2024-04-15', '16:00:00', 0, 9, ''),
(442, 'Abstimmung Prozesse und Aufgabenverteilung (Albert\\, Christian\\, An', '2024-04-15', '16:00:00', '2024-04-15', '18:00:00', 0, 9, ''),
(443, 'Gespräch mit Peter in Mardorf (Christian\\, Emma)', '2024-04-16', '14:00:00', '2024-04-16', '15:00:00', 0, 9, ''),
(444, 'DKV VA Osnabrück', '2024-04-19', NULL, '2024-04-21', NULL, 1, 9, ''),
(445, 'VIKO Platzbeirat Mardorf (Christian\\, Albert\\, Emma)', '2024-04-23', '19:30:00', '2024-04-23', '20:30:00', 0, 9, ''),
(446, 'Ständigen Konferenz 1/2024 der Landesfachverbände (Christian)', '2024-05-03', '17:30:00', '2024-05-03', '19:30:00', 0, 9, ''),
(447, 'Rückriem Termin (Albert\\, Emma)', '2024-05-13', '14:00:00', '2024-05-13', '16:00:00', 0, 9, ''),
(448, 'Booking.com mit Liesa (Albert\\, Birgit\\, Christian\\, Annette\\, Emma', '2024-05-13', '18:30:00', '2024-05-13', '19:30:00', 0, 9, ''),
(449, 'Schulung Phönix II Thema Seminare (Emma)', '2024-05-16', NULL, '2024-05-16', NULL, 1, 9, ''),
(450, 'Kennenlerntermin SSV (Emma)', '2024-05-27', '19:00:00', '2024-05-27', '20:00:00', 0, 9, ''),
(451, 'Mardorf Platzversammlung (Christian\\, Albert\\, Emma)', '2024-06-14', NULL, '2024-06-15', NULL, 1, 9, ''),
(452, 'Termin in Mardorf Frau Sellmann (Albert)', '2024-06-17', '16:00:00', '2024-06-17', '17:00:00', 0, 9, ''),
(453, 'Abstimmung SV Nienburg Steganlage (Christian\\, Emma\\, Albert)', '2024-06-17', '19:00:00', '2024-06-17', '20:00:00', 0, 9, ''),
(454, 'Peter Stude', '2024-06-18', NULL, '2024-06-18', NULL, 1, 9, ''),
(455, 'Meldung GLL Stegliegeplätze Mardorf Steg Nr 32 (Geschäftsstelle)', '2024-06-24', NULL, '2024-06-24', NULL, 1, 9, ''),
(456, 'LSB Leistungssport Termin in Hannover (Christian\\, Andi\\, Emma)', '2024-06-26', '11:15:00', '2024-06-26', '12:45:00', 0, 9, ''),
(457, 'NDM Rennsport in Hamburg (Emma)', '2024-06-29', NULL, '2024-06-29', NULL, 1, 9, ''),
(458, 'NDM HH (Annette)', '2024-06-29', NULL, '2024-06-29', NULL, 1, 9, ''),
(459, '09:30 h\\, Feuerlöscherprüfung (Andreas Schlösser\\nGloria Kundend', '2024-07-01', NULL, '2024-07-01', NULL, 1, 9, ''),
(460, 'DATEV Abstimmung (Emma\\, Albert)', '2024-07-01', '10:00:00', '2024-07-01', '16:00:00', 0, 9, ''),
(461, 'Jour Fix Phönix mit dem DKV (Emma\\, Albert)', '2024-07-01', '11:00:00', '2024-07-01', '12:00:00', 0, 9, ''),
(462, 'LRVN (AB)', '2024-07-03', NULL, '2024-07-03', NULL, 1, 9, ''),
(463, 'LRVN (AB)', '2024-07-03', NULL, '2024-07-03', NULL, 1, 9, ''),
(464, 'Unterlagen an LSB Post aus Mardorf (Emma)', '2024-07-17', NULL, '2024-07-17', NULL, 1, 9, ''),
(465, 'Abstimmung KSGH zur Steganlage (Christian\\, Albert)', '2024-07-22', '19:00:00', '2024-07-22', '20:00:00', 0, 9, ''),
(466, 'Termin Mardorf Stadt Hannover WKH (Albert\\, Christian\\, Emma)', '2024-08-06', '15:00:00', '2024-08-06', '16:00:00', 0, 9, ''),
(467, 'Jour Fix Phönix (Emma\\, Albert)', '2024-08-12', '11:00:00', '2024-08-12', '12:00:00', 0, 9, ''),
(468, 'Olympia Empfang in Brandenburg (Christian\\, Emma)', '2024-08-16', '16:30:00', '2024-08-16', '21:00:00', 0, 9, ''),
(469, 'Projektplanungstreffen Ausland 2025 beim LSB (Christian\\, Emma)', '2024-08-26', '15:30:00', '2024-08-26', '16:30:00', 0, 9, ''),
(470, 'Klausurtagung zu Mardorf im LLZ (Annette\\, Albert\\, Christian\\, Bir', '2024-08-30', '10:00:00', '2024-08-30', '12:00:00', 0, 9, ''),
(471, 'Übergabe buchung@ von Albert an Annette (Albert\\, Annette\\, Emma)', '2024-09-02', NULL, '2024-09-02', NULL, 1, 9, ''),
(472, 'Monatsmeldung Tourismus (Annette)', '2024-09-04', NULL, '2024-09-04', NULL, 1, 9, ''),
(473, 'Viko Jubiläumsabstimmung mit WSV Verden (Christian\\, Emma)', '2024-09-04', '17:00:00', '2024-09-04', '18:30:00', 0, 9, ''),
(474, 'Stiftungsforum Lotto-Sport-Stiftung (Christian\\, Emma)', '2024-09-05', '11:30:00', '2024-09-05', '16:30:00', 0, 9, ''),
(475, 'Gespräch mit CJD (Albert\\, Martin)', '2024-09-09', '13:30:00', '2024-09-09', '14:30:00', 0, 9, ''),
(476, 'Steuerbüro dhs Genge + Schmidtmeier GmbH & Co. KG (Christian\\, Alb', '2024-09-09', '15:00:00', '2024-09-09', '16:00:00', 0, 9, ''),
(477, 'LSB Leistungssport Abstimmung (Andi\\, Christian\\, Emma)', '2024-09-10', '12:00:00', '2024-09-10', '13:00:00', 0, 9, ''),
(478, ' (Albert Emmerich)', '2024-09-12', NULL, '2024-09-12', NULL, 1, 9, ''),
(479, 'VN an LSB', '2024-09-13', NULL, '2024-09-13', NULL, 1, 9, ''),
(480, 'Antrag Baumaßnahme LLZ (Emma)', '2024-09-17', NULL, '2024-09-17', NULL, 1, 9, ''),
(481, 'LSB Geschäftsführertagung (Emma Grigull)', '2024-09-19', NULL, '2024-09-19', NULL, 1, 9, ''),
(482, 'Empfang HKC + Sommerfest (Christian)', '2024-09-28', NULL, '2024-09-28', NULL, 1, 9, ''),
(483, 'Abstimmung Jubiläum 2027 mit WSV (Christian\\, Martin\\, Emma)', '2024-10-02', '17:00:00', '2024-10-02', '18:00:00', 0, 9, ''),
(484, 'Phönix Abstimmung DKV', '2024-10-07', '11:00:00', '2024-10-07', '12:00:00', 0, 9, ''),
(485, 'Prüfung der Rauchmelder LLZ', '2024-10-10', '09:00:00', '2024-10-10', '10:00:00', 0, 9, ''),
(486, 'Abstimmung Konzept CJD zum WKH (Albert\\, Emma)', '2024-10-10', '12:00:00', '2024-10-10', '13:00:00', 0, 9, ''),
(487, 'Empfang Olympia OSP LSB für die Sportler', '2024-10-16', '13:00:00', '2024-10-16', '14:00:00', 0, 9, ''),
(488, 'AHA Abfall Mardorf (Emma / Annette)', '2024-10-17', NULL, '2024-10-17', NULL, 1, 9, ''),
(489, 'Ressorttagung Kanu-Rennsport (Christian\\, Günther (ohne Andi))', '2024-10-18', NULL, '2024-10-20', NULL, 1, 9, ''),
(490, 'Glaß Dauerplatz (C. Wulf\\, GF/GS)', '2024-10-28', NULL, '2024-10-28', NULL, 1, 9, ''),
(491, 'Leistungssportkonferenz Zuschüsse für 2025 (Leistungssportwarte\\,', '2024-10-28', NULL, '2024-10-29', NULL, 1, 9, ''),
(492, 'Präsidiumssitzung (Christian\\, Albert\\, Martin\\, Emma\\, Günther)', '2024-10-29', '17:00:00', '2024-10-29', '19:00:00', 0, 9, ''),
(493, 'Antrag Nachwuchslehrgänge beim LSB', '2024-10-30', NULL, '2024-10-30', NULL, 1, 9, ''),
(494, 'Freizeitsport Konferenz Planung 2025', '2024-11-03', NULL, '2024-11-03', NULL, 1, 9, ''),
(495, 'Abstimmung Axel DelMar (Christian\\, Albert\\, Emma)', '2024-11-05', '12:00:00', '2024-11-05', '13:00:00', 0, 9, ''),
(496, 'Ehrung der Rennsportler', '2024-11-05', '18:00:00', '2024-11-05', '22:00:00', 0, 9, ''),
(497, 'Vorgespräch/Tagung Freizeitsport/Leistungssport', '2024-11-09', '12:00:00', '2024-11-09', '15:00:00', 0, 9, ''),
(498, 'LKV VA Abstimmungsveranstaltung Leistungssport und Freizeitsport', '2024-11-10', '10:00:00', '2024-11-10', '18:00:00', 0, 9, ''),
(499, 'Der Fuger', '2024-11-12', NULL, '2024-11-12', NULL, 1, 9, ''),
(500, 'Heinz Ehlers', '2024-11-13', '13:00:00', '2024-11-13', '14:30:00', 0, 9, ''),
(501, 'Der Fuger', '2024-11-14', NULL, '2024-11-14', NULL, 1, 9, ''),
(502, 'Anträge Lotto-Sport-Stiftung stellen (Vizepräsident Leistungsspor', '2024-11-20', NULL, '2024-11-20', NULL, 1, 9, ''),
(503, 'Herausnehmen der Beschädigten Scheiben im WKH (Birgit)', '2024-11-20', '10:30:00', '2024-11-20', '14:00:00', 0, 9, ''),
(504, 'Treffen LRV Natascha\\, Reinhard (Christian)', '2024-11-25', '10:30:00', '2024-11-25', '11:30:00', 0, 9, ''),
(505, 'Ulli Sonntag', '2024-12-03', '09:30:00', '2024-12-03', '10:30:00', 0, 9, ''),
(506, 'Jakob Abstimmung Ordnung im LLZ (Annette\\, Emma\\, Jakob)', '2024-12-03', '15:30:00', '2024-12-03', '16:30:00', 0, 9, ''),
(507, 'Termin mit Herrn Aldag (Albert\\, Christian\\, Emma)', '2024-12-09', '16:30:00', '2024-12-09', '17:30:00', 0, 9, ''),
(508, 'Abstimmung zur Kooperationsvereinbarung CJD-LKVN (Christian\\, Alber', '2024-12-10', '14:00:00', '2024-12-10', '15:00:00', 0, 9, ''),
(509, 'Meldung der Vereinsmitglieder und Vereine an den LSB', '2024-12-12', NULL, '2024-12-12', NULL, 1, 9, ''),
(510, 'LRVN (AB)', '2024-12-16', NULL, '2024-12-16', NULL, 1, 9, ''),
(511, 'LRVN (AB)', '2024-12-16', NULL, '2024-12-16', NULL, 1, 9, ''),
(512, 'Trailerwiese (Emma\\, Annette)', '2024-12-18', '11:00:00', '2024-12-18', '12:00:00', 0, 9, ''),
(513, 'Weihnachts\"feier\" LKV', '2024-12-19', NULL, '2024-12-19', NULL, 1, 9, ''),
(514, 'Rechnungen LLZ (Annette)', '2024-12-19', NULL, '2024-12-19', NULL, 1, 9, ''),
(515, 'Wasserzähler Mardorf', '2024-12-20', NULL, '2024-12-20', NULL, 1, 9, ''),
(516, 'Monatsmeldung Tourismus (Annette)', '2025-01-07', NULL, '2025-01-07', NULL, 1, 9, ''),
(517, 'Einladung für den VA und Kanu-Tag', '2025-01-08', NULL, '2025-01-08', NULL, 1, 9, ''),
(518, 'Präsidiumssitzung VA+Kanutag 2025 (Präsidium)', '2025-01-10', '20:00:00', '2025-01-10', '21:00:00', 0, 9, ''),
(519, 'Vesterling', '2025-01-21', '07:30:00', '2025-01-21', '08:30:00', 0, 9, ''),
(520, 'Adressänderung Bothor (Annette)', '2025-01-31', NULL, '2025-01-31', NULL, 1, 9, ''),
(521, 'Gesamtverwendungsnachweis LSB', '2025-02-01', '12:00:00', '2025-02-01', '13:00:00', 0, 9, ''),
(522, 'Auswertung der Vereinsmeldungen (Annette\\, Emma)', '2025-02-03', '11:00:00', '2025-02-03', '12:00:00', 0, 9, ''),
(523, 'Schließanlage LLZ wird eingebaut', '2025-02-04', NULL, '2025-02-04', NULL, 1, 9, ''),
(524, 'Einbau neue Schließanlage LLZ', '2025-02-04', '09:00:00', '2025-02-04', '13:00:00', 0, 9, ''),
(525, 'Einbau Wimpelscheiben im WKH (Birgit)', '2025-02-05', NULL, '2025-02-05', NULL, 1, 9, ''),
(526, 'Schulung Phönix mit den Fachwarten', '2025-02-10', '19:00:00', '2025-02-10', '21:00:00', 0, 9, ''),
(527, 'Gesamtverwendungsnachweis LSB (Emma)', '2025-02-13', NULL, '2025-02-13', NULL, 1, 9, ''),
(528, 'Ball des Sports (Andi\\, Kjell\\, Christian\\, Annette\\, Emma)', '2025-02-14', '19:00:00', '2025-02-14', '22:00:00', 0, 9, ''),
(529, 'KARI Schulung Slalom', '2025-02-15', NULL, '2025-02-15', NULL, 1, 9, ''),
(530, 'Deadline Einreichung Gesamtverwendungsnachweis LSB', '2025-02-15', '12:00:00', '2025-02-15', '13:00:00', 0, 9, ''),
(531, 'Ökoschulung Martin im LLZ', '2025-02-16', '10:00:00', '2025-02-16', '16:00:00', 0, 9, ''),
(532, 'KSGH (Emma\\, Annette)', '2025-02-17', '11:00:00', '2025-02-17', '13:00:00', 0, 9, ''),
(533, 'Jugendvollversammlung der Deutschen Kanujugend (Christian\\, Emma)', '2025-02-21', NULL, '2025-02-23', NULL, 1, 9, ''),
(534, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-02-26', '09:00:00', '2025-02-26', '10:00:00', 0, 9, ''),
(535, 'KaRi Schulung 01 Zoom-Zugang Silvia', '2025-03-01', NULL, '2025-03-02', NULL, 1, 9, ''),
(536, 'Kassenprüfung (Michael Henne und Gerd Bode\\, Albert)', '2025-03-04', '09:00:00', '2025-03-04', '15:00:00', 0, 9, ''),
(537, 'AHA Abfall Mardorf (Emma / Annette)', '2025-03-13', NULL, '2025-03-13', NULL, 1, 9, ''),
(538, 'Stegaufbau Mardorf (Bartling in Mardorf)', '2025-03-13', NULL, '2025-03-18', NULL, 1, 9, ''),
(539, 'Mail an Vereine', '2025-03-17', NULL, '2025-03-17', NULL, 1, 9, ''),
(540, 'VA und Kanu-Tag', '2025-03-22', NULL, '2025-03-23', NULL, 1, 9, ''),
(541, 'Haushaltsplanberatung Online (Christian)', '2025-03-22', NULL, '2025-03-22', NULL, 1, 9, ''),
(542, 'Rechnungen Einzelmitglieder!', '2025-03-24', '10:00:00', '2025-03-24', '11:00:00', 0, 9, ''),
(543, 'Jordy kommt zum Post packen', '2025-03-25', NULL, '2025-03-25', NULL, 1, 9, ''),
(544, 'ao Mitglieder (Annette)', '2025-03-25', NULL, '2025-03-25', NULL, 1, 9, ''),
(545, 'Trainer C-Ausbildung Hannover', '2025-03-28', NULL, '2025-03-30', NULL, 1, 9, ''),
(546, 'Präsidiumssitzung (Martin\\, Chistian\\, Andi\\, Annette\\, Emma)', '2025-04-07', '15:00:00', '2025-04-07', '16:00:00', 0, 9, '');
INSERT INTO `kalender_events` (`id`, `title`, `start_date`, `start_time`, `end_date`, `end_time`, `all_day`, `category_id`, `category_ids`) VALUES
(547, 'Austausch Drachenboot Großveranstaltungstreffen HKC & WKG & NRW', '2025-04-09', '18:00:00', '2025-04-09', '19:00:00', 0, 9, ''),
(548, 'Arbeitseinsatz Mardorf', '2025-04-12', '10:00:00', '2025-04-12', '15:00:00', 0, 9, ''),
(549, 'KANUTAG DKV (VA) (Christian\\, Emma)', '2025-04-25', NULL, '2025-04-27', NULL, 1, 9, ''),
(550, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-04-25', '09:00:00', '2025-04-25', '10:00:00', 0, 9, ''),
(551, 'Strom Ablesung und Rechnungsstellung DelMar (Annette)', '2025-04-28', '10:00:00', '2025-04-28', '11:00:00', 0, 9, ''),
(552, 'RZV light Gespräch im LLZ (Jens\\, Andi\\, Jan\\, Kjell\\, Christian\\,', '2025-04-29', '10:00:00', '2025-04-29', '14:00:00', 0, 9, ''),
(553, 'Zoom-Link versenden (Annette)', '2025-05-06', NULL, '2025-05-06', NULL, 1, 9, ''),
(554, 'Einführung und Vorstellung Vereins App- Zoom', '2025-05-07', '19:30:00', '2025-05-07', '20:00:00', 0, 9, ''),
(555, 'PISG Arbeitstagung (Christian\\, Emma)', '2025-05-08', '17:00:00', '2025-05-08', '20:00:00', 0, 9, ''),
(556, 'LSB Leistungssportkonferenz NORD', '2025-05-10', NULL, '2025-05-10', NULL, 1, 9, ''),
(557, 'Jugendvollversammlung (Lukas Lamberti)', '2025-05-17', '11:00:00', '2025-05-17', '15:00:00', 0, 9, ''),
(558, 'Antidoping Nachweis an LSB (Annette)', '2025-05-23', NULL, '2025-05-23', NULL, 1, 9, ''),
(559, 'Gesamtverwendungsnachweis LSB Post (Emma)', '2025-05-26', NULL, '2025-05-26', NULL, 1, 9, ''),
(560, 'Jannik Baumann', '2025-06-16', '10:00:00', '2025-06-16', '11:00:00', 0, 9, ''),
(561, 'Antrag für Baumaßnahmen bis 30.06. LSB', '2025-06-18', '12:00:00', '2025-06-18', '13:00:00', 0, 9, ''),
(562, 'Norddeutsche Meisterschaft in Hamburg', '2025-07-04', NULL, '2025-07-06', NULL, 1, 9, ''),
(563, 'Erbpachtvertrag (Annette)', '2025-07-07', NULL, '2025-07-07', NULL, 1, 9, ''),
(564, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-07-25', '09:00:00', '2025-07-25', '10:00:00', 0, 9, ''),
(565, 'Eintragung Interantionale Projekte 2026 (Christian)', '2025-08-04', NULL, '2025-08-04', NULL, 1, 9, ''),
(566, 'Schornsteinfeger Mardorf (Annette kümmert sich)', '2025-08-06', '10:00:00', '2025-08-06', '13:00:00', 0, 9, ''),
(567, 'Urlaubsübergabe (Annette)', '2025-08-08', '11:00:00', '2025-08-08', '12:00:00', 0, 9, ''),
(568, 'Schornsteinfeger Mardorf (Birgit)', '2025-08-11', '07:45:00', '2025-08-11', '08:15:00', 0, 9, ''),
(569, 'Anmeldung Sportjugend Niedersachsen (??)', '2025-08-13', '12:00:00', '2025-08-13', '13:00:00', 0, 9, ''),
(570, 'Türreparatur Damen Mardorf', '2025-08-21', NULL, '2025-08-21', NULL, 1, 9, ''),
(571, 'Emdener Kanu-Club 50Jahre Jubiläum (Uli Sonntag)', '2025-08-22', NULL, '2025-08-22', NULL, 1, 9, ''),
(572, '50 Jahre Kanuwanderer Rotenburg (Eckehard)', '2025-08-23', '11:00:00', '2025-08-23', '17:00:00', 0, 9, ''),
(573, 'Betriebskostenzuschuss einreichen!', '2025-08-25', NULL, '2025-08-25', NULL, 1, 9, ''),
(574, '!!! Deadline Betriebskostenzuschuss', '2025-08-29', NULL, '2025-08-29', NULL, 1, 9, ''),
(575, '75 Jahre WSV Nordenham', '2025-09-06', NULL, '2025-09-06', NULL, 1, 9, ''),
(576, 'Besuch der Geschäftsstelle NRW (Andi\\, Annette\\, Emma)', '2025-09-10', '15:00:00', '2025-09-11', '18:00:00', 0, 9, ''),
(577, 'Rückmeldung Ehrenamtspreis Christian', '2025-09-15', '10:00:00', '2025-09-15', '11:00:00', 0, 9, ''),
(578, 'Vollversammlung der Sportjugend Niedersachsen (?)', '2025-09-20', NULL, '2025-09-21', NULL, 1, 9, ''),
(579, 'Pachtzahlung Realgemeinde Mardorf', '2025-09-24', '10:00:00', '2025-09-24', '11:00:00', 0, 9, ''),
(580, 'Zahlung Pacht und Grundsteuer Realgemeinde Mardorf (GS)', '2025-09-28', NULL, '2025-09-28', NULL, 1, 9, ''),
(581, 'Jubiläumsfeier LKV Bremen', '2025-09-28', '10:30:00', '2025-09-28', '15:30:00', 0, 9, ''),
(582, 'Ressorttagung Kanu-Rennsport DKV', '2025-10-10', NULL, '2025-10-12', NULL, 1, 9, ''),
(583, 'Ressorttagung Kanu-Slalom', '2025-10-17', NULL, '2025-10-19', NULL, 1, 9, ''),
(584, 'Lastschrifteinzüge Kasse Mardorf prüfen und einlösen (Emma oder', '2025-10-27', '09:00:00', '2025-10-27', '10:00:00', 0, 9, ''),
(585, 'Freizeitsporttagung', '2025-11-02', '10:00:00', '2025-11-02', '17:00:00', 0, 9, ''),
(586, 'Anträge Auslandsprojekte', '2025-11-06', '10:00:00', '2025-11-06', '11:00:00', 0, 9, ''),
(587, 'Kadertest Remmsport', '2025-11-08', NULL, '2025-11-08', NULL, 1, 9, ''),
(588, 'VA DKV (Christian\\, Emma)', '2025-11-22', NULL, '2025-11-22', NULL, 1, 9, ''),
(589, 'Fachwartetagung Rennsport  LKV', '2025-11-22', NULL, '2025-11-22', NULL, 1, 9, ''),
(590, 'LKV VA??', '2025-11-23', NULL, '2025-11-23', NULL, 1, 9, ''),
(591, 'Erinnerung an Vereine', '2025-12-15', NULL, '2025-12-15', NULL, 1, 9, ''),
(592, 'Nebenkostenabrechnungen erstellen!', '2026-01-08', '12:00:00', '2026-01-08', '13:00:00', 0, 9, ''),
(593, 'Bezirkstag Braunschweig', '2026-02-14', NULL, '2026-02-14', NULL, 1, 9, ''),
(594, 'Gesamtverwendungsnachweis LSB Post (Emma)', '2026-03-10', NULL, '2026-03-10', NULL, 1, 9, ''),
(595, 'Verwendungsnachweis Betriebskostenzuschuss (Emma)', '2026-03-23', NULL, '2026-03-23', NULL, 1, 9, ''),
(596, 'VA DKV (Christian\\, Emma)', '2026-04-18', NULL, '2026-04-18', NULL, 1, 9, ''),
(597, 'Deadline !!!Betriebskostenzuschussabgabe!!!', '2026-04-27', NULL, '2026-04-27', NULL, 1, 9, ''),
(598, 'Verlängerung Phönix', '2026-09-15', NULL, '2026-09-15', NULL, 1, 9, ''),
(599, 'Vertragsende Phönix vom 11.12.203', '2026-12-11', NULL, '2026-12-11', NULL, 1, 9, ''),
(600, '? Kanu-Tag', '2027-02-21', NULL, '2027-02-21', NULL, 1, 9, ''),
(601, 'LSB (AE)', '2024-02-12', NULL, '2024-02-12', NULL, 1, 5, ''),
(602, 'LSB Quartalszahlung', '2025-02-21', NULL, '2025-02-21', NULL, 1, 5, ''),
(603, 'Prüfung Gelände LLZ -Stadt Hannover (GS\\, Andi)', '2025-05-06', '13:00:00', '2025-05-06', '14:00:00', 0, 5, ''),
(604, 'Ständigen Konferenz der Landesfachverbände LSB', '2025-05-09', '17:00:00', '2025-05-09', '20:30:00', 0, 5, ''),
(605, '1. Ländergipfel der Deutschen Kanujugend 2025', '2025-05-15', '19:30:00', '2025-05-15', '20:30:00', 0, 5, ''),
(606, 'LSB Quartalszahlung', '2025-05-22', NULL, '2025-05-22', NULL, 1, 5, ''),
(607, 'Online-Austausch der Landesfachverbände mit LSB', '2025-07-01', '19:00:00', '2025-07-01', '20:00:00', 0, 5, ''),
(608, 'LSB Quartalszahlung', '2025-08-21', NULL, '2025-08-21', NULL, 1, 5, ''),
(609, 'Info termin \"Finals 2026\" (Andi)', '2025-08-21', '19:00:00', '2025-08-21', '20:00:00', 0, 5, ''),
(610, 'LSB Quartalszahlung', '2025-11-21', NULL, '2025-11-21', NULL, 1, 5, ''),
(611, 'Pacht Parkplatz Meerstraße überweisen', '2023-01-02', NULL, '2023-01-02', NULL, 1, 11, ''),
(612, 'Lohn und Gehälter überweisen (Annette)', '2023-01-27', NULL, '2023-01-27', NULL, 1, 11, ''),
(613, '1. Rate DKV-Beitrag (Annette)', '2023-02-01', NULL, '2023-02-02', NULL, 1, 11, ''),
(614, 'Zins/Tilgung Darlehen Emmerich (Annette)', '2023-03-20', NULL, '2023-03-20', NULL, 1, 11, ''),
(615, '2. Rate DKV-Beitrag (Annette)', '2023-04-01', NULL, '2023-04-02', NULL, 1, 11, ''),
(616, ' (Annette)', '2023-05-15', NULL, '2023-05-15', NULL, 1, 11, ''),
(617, 'Lohn und Gehälter überweisen (Annette)', '2023-05-17', NULL, '2023-05-17', NULL, 1, 11, ''),
(618, 'Tilgung Stammkapital Darlehen AE (Annette)', '2023-06-30', NULL, '2023-06-30', NULL, 1, 11, ''),
(619, '3. Rate DKV-Beitrag (Annette)', '2023-07-01', NULL, '2023-07-02', NULL, 1, 11, ''),
(620, 'Lohn und Gehälter überweisen (Annette)', '2023-12-19', NULL, '2023-12-19', NULL, 1, 11, ''),
(621, 'Pacht Anwuchsfläche (GS)', '2024-01-02', NULL, '2024-01-02', NULL, 1, 11, ''),
(622, 'LRVN Rechnungen (GS)', '2024-03-27', NULL, '2024-03-27', NULL, 1, 11, ''),
(623, ' (Annette/Emma)', '2024-04-22', NULL, '2024-04-22', NULL, 1, 11, ''),
(624, ' (Annette)', '2024-06-27', NULL, '2024-06-27', NULL, 1, 11, ''),
(625, 'Lohn und Gehälter überweisen (Annette)', '2024-12-19', NULL, '2024-12-19', NULL, 1, 11, ''),
(626, '! FA (Annette)', '2025-02-12', NULL, '2025-02-12', NULL, 1, 11, ''),
(627, 'Grundsteuer Mardorf', '2025-02-15', NULL, '2025-02-15', NULL, 1, 11, ''),
(628, 'Wasserverband Garbsen', '2025-02-17', NULL, '2025-02-17', NULL, 1, 11, ''),
(629, 'Lohn und Gehälter überweisen (Annette)', '2025-02-25', NULL, '2025-02-25', NULL, 1, 11, ''),
(630, 'FA (Annette)', '2025-03-05', NULL, '2025-03-05', NULL, 1, 11, ''),
(631, '1. Rate SSB Hannover', '2025-04-10', NULL, '2025-04-10', NULL, 1, 11, ''),
(632, 'FA (Annette)', '2025-05-12', NULL, '2025-05-12', NULL, 1, 11, ''),
(633, 'Wasserverband Garbsen', '2025-05-15', NULL, '2025-05-15', NULL, 1, 11, ''),
(634, 'FA (Annette)', '2025-06-04', NULL, '2025-06-04', NULL, 1, 11, ''),
(635, '2. Rate SSB Hannover', '2025-07-01', NULL, '2025-07-01', NULL, 1, 11, ''),
(636, 'Überweisung Grundsteuer Realgemeinde', '2025-07-16', NULL, '2025-07-16', NULL, 1, 11, ''),
(637, 'FA (Annette)', '2025-08-11', NULL, '2025-08-11', NULL, 1, 11, ''),
(638, 'Wasserverband Garbsen', '2025-08-15', NULL, '2025-08-15', NULL, 1, 11, ''),
(639, 'FA (Annette)', '2025-09-05', NULL, '2025-09-05', NULL, 1, 11, ''),
(640, 'FA (Annette)', '2025-11-10', NULL, '2025-11-10', NULL, 1, 11, ''),
(641, 'Wasserverband Garbsen', '2025-11-17', NULL, '2025-11-17', NULL, 1, 11, ''),
(642, 'FA (Annette)', '2025-12-05', NULL, '2025-12-05', NULL, 1, 11, ''),
(643, ' (Kader + Fachwart)', '2023-03-11', NULL, '2023-03-12', NULL, 1, 24, ''),
(644, ' (Kader + Fachwart)', '2023-03-18', NULL, '2023-03-18', NULL, 1, 24, ''),
(645, ' (Kader + Fachwart)', '2023-03-18', NULL, '2023-03-18', NULL, 1, 24, ''),
(646, ' (Kader + Fachwart)', '2023-03-19', NULL, '2023-03-19', NULL, 1, 24, ''),
(647, ' (Kader)', '2023-03-25', NULL, '2023-03-26', NULL, 1, 24, ''),
(648, ' (Kader + Fachwart)', '2023-04-01', NULL, '2023-04-02', NULL, 1, 24, ''),
(649, ' (Kader / Förderkreis / Fachwart)', '2023-04-03', NULL, '2023-04-11', NULL, 1, 24, ''),
(650, ' (alle Sportler + Fachwart)', '2023-04-15', NULL, '2023-04-16', NULL, 1, 24, ''),
(651, ' (Kader)', '2023-04-21', NULL, '2023-04-23', NULL, 1, 24, ''),
(652, ' (alle Sportler + Fachwart)', '2023-05-06', NULL, '2023-05-07', NULL, 1, 24, ''),
(653, ' (Quailifizierte Sportler)', '2023-05-17', NULL, '2023-05-21', NULL, 1, 24, ''),
(654, ' (Kader / Förderkreis / Fachwart)', '2023-05-27', NULL, '2023-05-30', NULL, 1, 24, ''),
(655, ' (Qualifizierte Sporrtler)', '2023-06-09', NULL, '2023-06-11', NULL, 1, 24, ''),
(656, ' (Qualifizierte Sportler)', '2023-06-14', NULL, '2023-06-16', NULL, 1, 24, ''),
(657, ' (Kader + Fachwart)', '2023-06-24', NULL, '2023-06-25', NULL, 1, 24, ''),
(658, ' (Qualifizierte Sportler)', '2023-08-05', NULL, '2023-08-06', NULL, 1, 24, ''),
(659, ' (Qualifizierte Sportler)', '2023-08-29', NULL, '2023-08-30', NULL, 1, 24, ''),
(660, ' (qualifizierte Sportler)', '2023-08-31', NULL, '2023-09-01', NULL, 1, 24, ''),
(661, ' (alle Sportler + Fachwart)', '2023-09-02', NULL, '2023-09-03', NULL, 1, 24, ''),
(662, ' (Kader + Fachwart)', '2023-09-09', NULL, '2023-09-10', NULL, 1, 24, ''),
(663, ' (alle Sportler + Fachwart)', '2023-09-23', NULL, '2023-09-24', NULL, 1, 24, ''),
(664, ' (Kader / Förderkreis / Fachwart)', '2023-10-10', NULL, '2023-10-13', NULL, 1, 24, ''),
(665, ' (Kader/ Förderkreis / Fachwart)', '2023-10-14', NULL, '2023-10-15', NULL, 1, 24, ''),
(666, ' (Fachwart)', '2023-10-21', NULL, '2023-10-21', NULL, 1, 24, ''),
(667, 'Sportler/Fachwarte Ehrung', '2023-10-28', NULL, '2023-10-28', NULL, 1, 24, ''),
(668, ' (alle Sportler + Fachwart)', '2023-11-19', NULL, '2023-11-19', NULL, 1, 24, ''),
(669, 'Kampfrichter Lehrgang', '2023-01-21', NULL, '2023-01-21', NULL, 1, 23, ''),
(670, 'Trainer Fortbildung', '2023-02-18', NULL, '2023-02-19', NULL, 1, 23, ''),
(671, 'TL Markkleeberg', '2023-03-11', NULL, '2023-03-12', NULL, 1, 23, ''),
(672, 'TL Sch Hildesheim', '2023-03-18', NULL, '2023-03-19', NULL, 1, 23, ''),
(673, 'TL Markkleeberg', '2023-03-25', NULL, '2023-03-26', NULL, 1, 23, ''),
(674, 'TL Augsburg', '2023-03-27', NULL, '2023-03-31', NULL, 1, 23, ''),
(675, 'TL Markkleeberg', '2023-04-01', NULL, '2023-04-02', NULL, 1, 23, ''),
(676, 'Berlin', '2023-04-15', NULL, '2023-04-16', NULL, 1, 23, ''),
(677, 'TL Markkleeberg', '2023-04-19', NULL, '2023-04-20', NULL, 1, 23, ''),
(678, 'Quali Markkleeberg', '2023-04-21', NULL, '2023-04-23', NULL, 1, 23, ''),
(679, 'TL Augsburg', '2023-04-24', NULL, '2023-04-27', NULL, 1, 23, ''),
(680, 'Quali Augsburg', '2023-04-28', NULL, '2023-04-30', NULL, 1, 23, ''),
(681, 'NDM Braunschweig', '2023-05-06', NULL, '2023-05-07', NULL, 1, 23, ''),
(682, 'LG Lofer', '2023-05-13', NULL, '2023-05-18', NULL, 1, 23, ''),
(683, 'TL Haynsburg', '2023-05-19', NULL, '2023-05-21', NULL, 1, 23, ''),
(684, 'DC Lofer', '2023-05-19', NULL, '2023-05-21', NULL, 1, 23, ''),
(685, 'TL Roudnice', '2023-05-22', NULL, '2023-05-26', NULL, 1, 23, ''),
(686, 'Roudnice', '2023-05-27', NULL, '2023-05-28', NULL, 1, 23, ''),
(687, 'TL Roudnice', '2023-05-29', NULL, '2023-05-29', NULL, 1, 23, ''),
(688, 'LG Hildesheim', '2023-06-07', NULL, '2023-06-09', NULL, 1, 23, ''),
(689, 'SLP Hildesheim', '2023-06-10', NULL, '2023-06-11', NULL, 1, 23, ''),
(690, 'Luhdorf', '2023-06-17', NULL, '2023-06-18', NULL, 1, 23, ''),
(691, 'TL Roudnice', '2023-06-22', NULL, '2023-06-23', NULL, 1, 23, ''),
(692, 'DC Roudnice', '2023-06-24', NULL, '2023-06-25', NULL, 1, 23, ''),
(693, 'Rotenburg', '2023-07-01', NULL, '2023-07-02', NULL, 1, 23, ''),
(694, 'ECA Solkan', '2023-07-15', NULL, '2023-07-16', NULL, 1, 23, ''),
(695, 'Lüneburg', '2023-08-26', NULL, '2023-08-27', NULL, 1, 23, ''),
(696, 'TL Haynsburg', '2023-09-06', NULL, '2023-09-08', NULL, 1, 23, ''),
(697, 'DSM Haynsburg', '2023-09-09', NULL, '2023-09-10', NULL, 1, 23, ''),
(698, 'Luhdorf 50', '2023-09-23', NULL, '2023-09-23', NULL, 1, 23, ''),
(699, 'TL Markkleeberg', '2023-09-27', NULL, '2023-09-28', NULL, 1, 23, ''),
(700, 'DM Markkleeberg', '2023-09-29', NULL, '2023-10-01', NULL, 1, 23, ''),
(701, 'GM Dorsten', '2023-10-06', NULL, '2023-10-07', NULL, 1, 23, ''),
(702, 'DKV Ressorttagung', '2023-10-21', NULL, '2023-10-22', NULL, 1, 23, ''),
(703, 'Sportler/Fachwarte Ehrung', '2023-10-28', NULL, '2023-10-28', NULL, 1, 23, ''),
(704, 'Fachwarte', '2023-12-03', NULL, '2023-12-03', NULL, 1, 23, ''),
(705, ' (Rennsportregatta)', '2023-04-28', '07:00:00', '2023-04-30', '08:00:00', 0, 22, ''),
(706, ' (Rennsportregatta)', '2023-05-05', NULL, '2023-05-07', NULL, 1, 22, ''),
(707, ' (1 WC Racice)', '2023-05-12', NULL, '2023-05-14', NULL, 1, 22, ''),
(708, ' (Landensmeisterschaft Limmer)', '2023-05-13', NULL, '2023-05-14', NULL, 1, 22, ''),
(709, ' (Int. Jun. U23 Brandenburg)', '2023-05-13', NULL, '2023-05-14', NULL, 1, 22, ''),
(710, ' (Marathon DM)', '2023-05-19', NULL, '2023-05-21', NULL, 1, 22, ''),
(711, ' (Rennsportregatta NM-Cup)', '2023-05-20', NULL, '2023-05-21', NULL, 1, 22, ''),
(712, ' (Rennsportregatta)', '2023-06-17', NULL, '2023-06-18', NULL, 1, 22, ''),
(713, ' (Euro Games)', '2023-06-21', NULL, '2023-06-25', NULL, 1, 22, ''),
(714, ' (Finals Diusburg)', '2023-07-06', NULL, '2023-07-09', NULL, 1, 22, ''),
(715, ' (Jun. U23 WM Auronzo)', '2023-07-06', NULL, '2023-07-09', NULL, 1, 22, ''),
(716, ' (NDM Hamburg)', '2023-07-14', NULL, '2023-07-16', NULL, 1, 22, ''),
(717, ' (Rennsportregatta)', '2023-08-19', NULL, '2023-08-20', NULL, 1, 22, ''),
(718, ' (WM Duisburg)', '2023-08-23', NULL, '2023-08-27', NULL, 1, 22, ''),
(719, ' (DM Köln)', '2023-08-29', NULL, '2023-09-03', NULL, 1, 22, ''),
(720, ' (Rennsportregatta)', '2023-09-09', NULL, '2023-09-10', NULL, 1, 22, ''),
(721, ' (Olympic Hopes)', '2023-09-09', NULL, '2023-09-10', NULL, 1, 22, ''),
(722, ' (Olympic Hopes)', '2023-09-16', NULL, '2023-09-17', NULL, 1, 22, ''),
(723, ' (Rennsportregatta)', '2023-09-22', NULL, '2023-09-23', NULL, 1, 22, ''),
(724, ' (Rennsportregatta)', '2023-09-30', NULL, '2023-10-01', NULL, 1, 22, ''),
(725, 'Sportler/Fachwarte Ehrung', '2023-10-28', NULL, '2023-10-28', NULL, 1, 22, ''),
(726, ' (L Kadertest)', '2023-11-11', NULL, '2023-11-11', NULL, 1, 22, ''),
(727, 'RS07 1.Rangliste Duisburg (Jan Francik)', '2024-04-05', NULL, '2024-04-07', NULL, 1, 22, ''),
(728, 'RS08 WW-Lehrgang / Vorbereitung 2.Rangliste (Jan Francik) (Jan Fran', '2024-04-09', NULL, '2024-04-15', NULL, 1, 22, ''),
(729, 'RS09 2.Rangliste Duisburg (Jan Francik)', '2024-04-19', NULL, '2024-04-21', NULL, 1, 22, ''),
(730, 'Frühjahrsregatta Essen', '2024-04-26', NULL, '2024-04-28', NULL, 1, 22, ''),
(731, 'Gr.Brandenburger Regatta', '2024-05-03', NULL, '2024-05-05', NULL, 1, 22, ''),
(732, 'Wettkampfbegleitung Szeged European Olympic-Qualifikation (Jan Fran', '2024-05-08', NULL, '2024-05-09', NULL, 1, 22, ''),
(733, 'Wettkampfbegleitung Szeged World-Cup (Jan Francik)', '2024-05-10', NULL, '2024-05-12', NULL, 1, 22, ''),
(734, 'Landesmeisterschaft Niedersachsen (KC Limmer) (KC Limmer)', '2024-05-11', NULL, '2024-05-12', NULL, 1, 22, ''),
(735, 'Wettkampfbegleitung Bratislava Intern.Regatta (Jun / U23) (Jan Fran', '2024-05-24', NULL, '2024-05-26', NULL, 1, 22, ''),
(736, 'Wettkampfbegleitung Poznan World-Cup (LK / U23) (Jan Francik)', '2024-05-24', NULL, '2024-05-26', NULL, 1, 22, ''),
(737, 'Wettkampfbegleitung Szeged EM (LK) (Jan Francik)', '2024-06-13', NULL, '2024-06-16', NULL, 1, 22, ''),
(738, 'Wettkampfbegleitung Bratislava EM (Jun/U23) (Jan Francik)', '2024-06-27', NULL, '2024-06-30', NULL, 1, 22, ''),
(739, 'Norddeutsche Meisterschaft Hamburg LKV / RG Nord (LKV / RG Nord)', '2024-06-28', NULL, '2024-06-30', NULL, 1, 22, ''),
(740, 'Wettkampfbegleitung Plovdiv WM (Jun/U23) (Jan Francik)', '2024-07-17', NULL, '2024-07-21', NULL, 1, 22, ''),
(741, 'Wettkampfbegleitung Olympia Paris (Jan Francik + Andi Wambach + Chr', '2024-08-06', NULL, '2024-08-10', NULL, 1, 22, ''),
(742, 'Wettkampfbegleitung Deutsche Meisterschaft Brandenburg (Jan Francik', '2024-08-13', NULL, '2024-08-18', NULL, 1, 22, ''),
(743, 'Wettkampfbegleitung NO WM (Jan Francik)', '2024-08-30', NULL, '2024-09-01', NULL, 1, 22, ''),
(744, 'Deutschen Schülermeisterschaften im Kanuslalom in Hildesheim', '2024-09-06', NULL, '2024-09-08', NULL, 1, 22, ''),
(745, ' (75. Jahre Regatta Hann.Münden)', '2024-09-14', NULL, '2024-09-15', NULL, 1, 22, ''),
(746, 'Polen Out Wettkampfbegleitung Posnan Internationale Einladngsregatt', '2024-09-19', NULL, '2024-09-21', NULL, 1, 22, ''),
(747, ' (WSV Verden)', '2024-09-27', NULL, '2024-09-29', NULL, 1, 22, ''),
(748, 'Große Brandenburger', '2025-05-01', NULL, '2025-05-04', NULL, 1, 22, ''),
(749, 'Frühjahrsregatta Essen Baldeneysee', '2025-05-09', NULL, '2025-05-11', NULL, 1, 22, ''),
(750, '1.World Cup (Jan Francik Wettkampfbegleitung)', '2025-05-15', NULL, '2025-05-18', NULL, 1, 22, ''),
(751, 'Landesmeisterschaft Niedersachsen (KC Limmer)', '2025-05-17', NULL, '2025-05-18', NULL, 1, 22, ''),
(752, '2.World Cup (Wettkampfbegleitung Jan Francik)', '2025-05-22', NULL, '2025-05-25', NULL, 1, 22, ''),
(753, 'Int. Junioren + U23 Regatta (Jan Francik Wettkampfbegleitung)', '2025-05-22', NULL, '2025-05-24', NULL, 1, 22, ''),
(754, 'Europameisterschaft LK (Jan Francik Wettkampfbegleitung)', '2025-06-19', NULL, '2025-06-22', NULL, 1, 22, ''),
(755, 'NDM in Hamburg (Gruppe Nord)', '2025-07-03', NULL, '2025-07-06', NULL, 1, 22, ''),
(756, 'Europameisterschaft Junioren / U23', '2025-07-03', NULL, '2025-07-06', NULL, 1, 22, ''),
(757, 'Polen IN (LKVN Andi Wambach\\, Christian Wulf)', '2025-07-03', NULL, '2025-07-06', NULL, 1, 22, ''),
(758, 'Westdeutsche Meisterschaft\\, Vorbereitung\\, Test zur DM (Andi)', '2025-07-11', NULL, '2025-07-13', NULL, 1, 22, ''),
(759, 'Jan Francik U23 / Junioren WM Portugal (Jan Francik)', '2025-07-24', NULL, '2025-07-27', NULL, 1, 22, ''),
(760, 'Finals Dresden (Jan Francik\\, Andi Wambach)', '2025-07-31', NULL, '2025-08-03', NULL, 1, 22, ''),
(761, 'Harle Regatta (WSV Harle)', '2025-08-16', NULL, '2025-08-17', NULL, 1, 22, ''),
(762, 'Jan Francik Wettkampfbegleiung WM Mailand (Jan Francik)', '2025-08-20', NULL, '2025-08-24', NULL, 1, 22, ''),
(763, 'Wettkampfbegleitung DM Köln (Andi Wambach\\, Jan Francik)', '2025-08-26', NULL, '2025-08-31', NULL, 1, 22, ''),
(764, 'Hann. Münden Herbstregatta (MKC Münden)', '2025-09-13', NULL, '2025-09-14', NULL, 1, 22, ''),
(765, 'Polen OUT Int. Einladungsregatta ??? (LKVN)', '2025-09-18', NULL, '2025-09-21', NULL, 1, 22, ''),
(766, 'Verden Herbstregatta (WSV Verden)', '2025-09-27', NULL, '2025-09-28', NULL, 1, 22, ''),
(767, 'Norddeutsche Kanurennsport Meisterschaft Hamburg (Jan Francik\\, And', '2026-07-09', '13:00:00', '2026-07-12', '16:00:00', 0, 22, ''),
(768, 'Finals Hannover (Andi\\, Jan)', '2026-07-23', NULL, '2026-07-26', NULL, 1, 22, ''),
(769, 'Deutsche Kanurennsport Meisterschaft Brandenburg (Jan Francik\\, And', '2026-09-01', '13:10:00', '2026-09-06', '16:00:00', 0, 22, ''),
(770, ' (RS1 Winterlehrgang)', '2023-01-03', '12:00:00', '2023-01-08', '13:00:00', 0, 21, ''),
(771, 'RS 2 Athletiklehrgang 1/4 (RS 2 Athletiklehrgang)', '2023-01-05', NULL, '2023-01-08', NULL, 1, 21, ''),
(772, ' (VIKO)', '2023-01-18', '19:00:00', '2023-01-18', '20:00:00', 0, 21, ''),
(773, 'Gruppe Nord (Fachwarte aus: Meck-Pomm\\, SH\\, HB\\, HH\\, Nds.)', '2023-01-21', NULL, '2023-01-21', NULL, 1, 21, ''),
(774, ' (David Appelhans)', '2023-01-29', NULL, '2023-02-15', NULL, 1, 21, ''),
(775, 'RS3 Warmwasserlehrgang 2/4 (RS3 Warmwasserlehrgang)', '2023-02-02', '09:30:00', '2023-02-15', '10:30:00', 0, 21, ''),
(776, ' (RS4 Wasservorbereitungslehrgang 1)', '2023-03-03', NULL, '2023-03-04', NULL, 1, 21, ''),
(777, 'Kienbaum', '2023-03-03', NULL, '2023-03-04', NULL, 1, 21, ''),
(778, 'Portugal (RS5 2 Warmwasserlehrgang)', '2023-03-14', NULL, '2023-03-26', NULL, 1, 21, ''),
(779, ' (VA)', '2023-03-18', NULL, '2023-03-18', NULL, 1, 21, ''),
(780, ' (Kanutag)', '2023-03-19', NULL, '2023-03-19', NULL, 1, 21, ''),
(781, ' (1.Rangliste Kader)', '2023-04-07', '12:00:00', '2023-04-08', '13:00:00', 0, 21, ''),
(782, ' (RS6 Lehrgang Walcz)', '2023-04-11', NULL, '2023-04-19', NULL, 1, 21, ''),
(783, ' (2. Rangliste Kader)', '2023-04-21', NULL, '2023-04-23', NULL, 1, 21, ''),
(784, ' (Kienbaum Kader)', '2023-04-30', NULL, '2023-04-30', NULL, 1, 21, ''),
(785, ' (RS Lehrgang Kienbaum Kader)', '2023-05-05', NULL, '2023-05-10', NULL, 1, 21, ''),
(786, 'Schüler C/ B LLZ Limmer', '2023-05-06', NULL, '2023-05-07', NULL, 1, 21, ''),
(787, ' (RS Lehrgang Kienbaum Nationalmannschaft)', '2023-05-21', NULL, '2023-05-28', NULL, 1, 21, ''),
(788, ' (RS 7 Wasservorbereitungslehrgang 2)', '2023-06-03', NULL, '2023-06-04', NULL, 1, 21, ''),
(789, ' (RS TL Duisburg Nationalmannschaft)', '2023-06-11', NULL, '2023-06-25', NULL, 1, 21, ''),
(790, ' (RS 8 Wasservorbereitungslehrgang 3)', '2023-07-01', NULL, '2023-07-02', NULL, 1, 21, ''),
(791, ' (RS Grundlagenlehrgang Nationalmannschaft)', '2023-07-06', NULL, '2023-07-22', NULL, 1, 21, ''),
(792, 'RS 09 (UVW DM)', '2023-08-04', NULL, '2023-08-15', NULL, 1, 21, ''),
(793, 'Nationalmannschaft (UWV München zur WM Duisburg)', '2023-08-06', NULL, '2023-08-20', NULL, 1, 21, ''),
(794, 'RS 10 (UWV DM Poznan / Walc)', '2023-08-08', NULL, '2023-08-20', NULL, 1, 21, ''),
(795, ' (OHG)', '2023-09-07', NULL, '2023-09-10', NULL, 1, 21, ''),
(796, 'Sportler/Fachwarte Ehrung', '2023-10-28', NULL, '2023-10-28', NULL, 1, 21, ''),
(797, 'Portugal Lehrgang (Francik)', '2023-10-31', NULL, '2023-11-10', NULL, 1, 21, ''),
(798, ' (Sportwartetagung)', '2023-11-12', NULL, '2023-11-12', NULL, 1, 21, ''),
(799, 'RS02 Ski-Lehrgang Polen (Jan Francik)', '2024-01-02', NULL, '2024-01-07', NULL, 1, 21, ''),
(800, 'RS01 / NWKR 1 (Andi Wambach\\, Jan Steuer)', '2024-01-04', NULL, '2024-01-07', NULL, 1, 21, ''),
(801, ' (Jan Francik)', '2024-01-12', NULL, '2024-01-13', NULL, 1, 21, ''),
(802, 'RG Nord / LKVN (Günter Stahlschmidt Christian Wulf)', '2024-01-13', NULL, '2024-01-13', NULL, 1, 21, ''),
(803, 'RS03 TL Portugal Jan Francik (Jan Francik)', '2024-01-17', NULL, '2024-01-23', NULL, 1, 21, ''),
(804, 'VA-Sitzung (LKVN)', '2024-01-27', NULL, '2024-01-27', NULL, 1, 21, ''),
(805, 'RS04 Warmwasserlehrgang Portugal (Jan Francik)', '2024-02-08', NULL, '2024-02-19', NULL, 1, 21, ''),
(806, ' (LKV)', '2024-02-16', NULL, '2024-02-16', NULL, 1, 21, ''),
(807, 'RS05 Junioren Athletik RL Kienbaum (Jan Francik)', '2024-03-01', NULL, '2024-03-03', NULL, 1, 21, ''),
(808, 'Kampfrichtertagung LKV Mardorf (Silvia Kudlacik)', '2024-03-01', NULL, '2024-03-03', NULL, 1, 21, ''),
(809, 'VA -  LLZ Hannover (LKV)', '2024-03-09', NULL, '2024-03-09', NULL, 1, 21, ''),
(810, 'RS06 WW-Lehrgang / Vorbereitung 1.Rangliste (Jan Francik)', '2024-03-10', NULL, '2024-03-21', NULL, 1, 21, ''),
(811, 'Fa. Sennheiser Drehgenehmigung Jakob Thordsen (JAn Francik + Andi W', '2024-03-25', NULL, '2024-03-25', NULL, 1, 21, ''),
(812, 'RS07 1.Rangliste Duisburg (Jan Francik)', '2024-04-05', NULL, '2024-04-07', NULL, 1, 21, ''),
(813, 'RS08 WW-Lehrgang / Vorbereitung 2.Rangliste (Jan Francik) (Jan Fran', '2024-04-09', NULL, '2024-04-15', NULL, 1, 21, ''),
(814, 'RS10 / NWKR2 SchülerB 1.Nachwuchslehrgang (Andi + Sabine Wambach)', '2024-04-19', NULL, '2024-04-21', NULL, 1, 21, ''),
(815, 'RS09 2.Rangliste Duisburg (Jan Francik)', '2024-04-19', NULL, '2024-04-21', NULL, 1, 21, ''),
(816, 'DKV VA Tagung (LKV)', '2024-04-20', NULL, '2024-04-20', NULL, 1, 21, ''),
(817, 'Wettkampfbegleitung Szeged European Olympic-Qualifikation (Jan Fran', '2024-05-08', NULL, '2024-05-09', NULL, 1, 21, ''),
(818, 'Wettkampfbegleitung Szeged World-Cup (Jan Francik)', '2024-05-10', NULL, '2024-05-12', NULL, 1, 21, ''),
(819, 'RS11 / NWKR3 Nachwuchs Wasservorbereitungslehrgang (Andi Wambach +', '2024-05-24', NULL, '2024-05-26', NULL, 1, 21, ''),
(820, ' (Christoph Steinkamp NRW)', '2024-05-27', NULL, '2024-06-02', NULL, 1, 21, ''),
(821, 'RS12 / NWKR4 / SchülerB Nachwuchslehrgang (Andi Wambach Sabine Wam', '2024-06-07', NULL, '2024-06-09', NULL, 1, 21, ''),
(822, 'Kanu NRW (Christoph Steinkamp)', '2024-07-15', NULL, '2024-07-21', NULL, 1, 21, ''),
(823, 'RS13 / NWKR5 UWV Hannover für DM Brandenburg 13.8.-18.8.24 (Andi W', '2024-07-22', NULL, '2024-07-28', NULL, 1, 21, ''),
(824, 'RS14 / UWV UWV für DM Brandenburg 13.8.-18.8.24 (Jan Francik)', '2024-07-23', NULL, '2024-08-03', NULL, 1, 21, ''),
(825, 'RS13.1 / NWKR5.1 UWV Neumünster für DM Brandenburg 13.8.-18.8.24', '2024-07-28', NULL, '2024-08-03', NULL, 1, 21, ''),
(826, 'Kanu NRW (Christoph Steinkamp)', '2024-07-29', NULL, '2024-08-04', NULL, 1, 21, ''),
(827, 'DKV Resorttagung Berlin (Christian Wulf\\, Günter Stahlschmidt\\,  A', '2024-10-19', NULL, '2024-10-20', NULL, 1, 21, ''),
(828, 'Leistungssportkonferenz', '2024-10-28', NULL, '2024-10-29', NULL, 1, 21, ''),
(829, 'RS17 Kadertest (Andi Wambach)', '2024-11-01', NULL, '2024-11-03', NULL, 1, 21, ''),
(830, 'VA Sitzung (LKVN)', '2024-11-09', NULL, '2024-11-09', NULL, 1, 21, ''),
(831, 'Infoveranstaltung', '2024-11-10', NULL, '2024-11-10', NULL, 1, 21, ''),
(832, 'RS16 Portugal (Jan Francik)', '2024-11-10', NULL, '2024-11-24', NULL, 1, 21, ''),
(833, 'Sportwartetagung Kanurensport (Andi Wambach)', '2024-11-16', NULL, '2024-11-16', NULL, 1, 21, ''),
(834, 'RS01 / NWKR01 Athletik-/ Paddeln (Kjell Flechsig\\, Jan Steuer Andi', '2025-01-02', '07:30:00', '2025-01-05', '13:30:00', 0, 21, ''),
(835, 'RS02 Ski-Lehrgang (Jan Francik)', '2025-01-06', NULL, '2025-01-11', NULL, 1, 21, ''),
(836, 'RG Nord Treffen Hamburg (LKVN / Gruppe Nord)', '2025-01-11', NULL, '2025-01-11', NULL, 1, 21, ''),
(837, 'RS03 Portugal (Jan Francik)', '2025-01-30', NULL, '2025-02-12', NULL, 1, 21, ''),
(838, 'RS04 Athletik Test Junioren (Jan Francik)', '2025-02-21', NULL, '2025-02-23', NULL, 1, 21, ''),
(839, 'RS05 Portugal Vorbereitung 1.Rangliste (Jan Francik)', '2025-03-02', NULL, '2025-03-15', NULL, 1, 21, ''),
(840, 'RS06 NWKR02 (Kjell Flechsig\\, Jan Steuer\\, Andi Wambach)', '2025-03-21', NULL, '2025-03-23', NULL, 1, 21, ''),
(841, 'VA Osnabrück (LKVN)', '2025-03-22', NULL, '2025-03-22', NULL, 1, 21, ''),
(842, 'RS06.1 Leistungssportgruppe (Jan Francik)', '2025-03-22', NULL, '2025-03-22', NULL, 1, 21, ''),
(843, 'Kanutag LKVN (LKVN)', '2025-03-23', NULL, '2025-03-23', NULL, 1, 21, ''),
(844, 'RS07 1.Rangliste / Qualifikation Duisburg (Jan Francik + Andi Wamba', '2025-04-04', NULL, '2025-04-06', NULL, 1, 21, ''),
(845, 'RS08 Walcz Vorbereitung 2.Rangliste (Jan Francik)', '2025-04-12', NULL, '2025-04-19', NULL, 1, 21, ''),
(846, 'RS09 2.Qualifikation (Jan Francik)', '2025-04-23', NULL, '2025-04-27', NULL, 1, 21, ''),
(847, 'RS10 NWKR SchülerB (Sabine Wambach)', '2025-04-25', NULL, '2025-04-27', NULL, 1, 21, ''),
(848, 'RS11 NWKR (Kjell Flechsig Jan Steuer Holger Dupree Andi Wambach)', '2025-06-20', NULL, '2025-06-22', NULL, 1, 21, ''),
(849, 'UWV RS12 Kanurennsport Hannover für DM Köln 26.8.-31.8.25 (Andi W', '2025-08-01', NULL, '2025-08-12', NULL, 1, 21, ''),
(850, 'RS13 UWV Jan Francik (Jan Francik)', '2025-08-05', NULL, '2025-08-16', NULL, 1, 21, ''),
(851, 'DKV Ressorttagung Rennsport', '2025-10-10', NULL, '2025-10-12', NULL, 1, 21, ''),
(852, '? Lehrgang Sexualisierte Gewalt und Verbandsstruktur', '2025-11-07', '18:00:00', '2025-11-07', '21:15:00', 0, 21, ''),
(853, 'Kadertest (LKVN Andi Wambach\\, Jan Francik)', '2025-11-08', NULL, '2025-11-08', NULL, 1, 21, ''),
(854, 'Sportwartetagung (Andi Wambach)', '2025-11-22', NULL, '2025-11-22', NULL, 1, 21, ''),
(855, 'RS / NWKR Athletik-/Paddellehrgang (Andi\\, Kjell\\, Jan St.\\, Holger', '2026-01-30', NULL, '2026-02-03', NULL, 1, 21, ''),
(856, 'RS UWV (Andi\\, Kjell\\, Jan St.\\, Holger)', '2026-07-31', NULL, '2026-08-11', NULL, 1, 21, ''),
(857, 'SüdAfrika IN (Gegenbesuch Südafrika)', '2023-07-07', NULL, '2023-07-17', NULL, 1, 16, ''),
(858, 'Polen IN (Austausch Posen)', '2023-07-13', NULL, '2023-07-17', NULL, 1, 16, ''),
(859, 'ENTFÄLLT Japan IN (Japan)', '2023-08-18', NULL, '2023-08-28', NULL, 1, 16, ''),
(860, 'Japan Out (Japan)', '2023-09-20', NULL, '2023-09-28', NULL, 1, 16, ''),
(861, 'Sportler/Fachwarte Ehrung', '2023-10-28', NULL, '2023-10-28', NULL, 1, 16, ''),
(862, 'SüdAfrika Out (Lehrgang Südafrika)', '2023-11-23', NULL, '2023-12-06', NULL, 1, 16, ''),
(863, 'Polen Out Wettkampfbegleitung Posnan Internationale Einladngsregatt', '2024-09-19', NULL, '2024-09-21', NULL, 1, 16, ''),
(864, 'Polen IN (LKVN Andi Wambach\\, Christian Wulf)', '2025-07-03', NULL, '2025-07-06', NULL, 1, 16, ''),
(865, 'Polen OUT Int. Einladungsregatta ??? (LKVN)', '2025-09-18', NULL, '2025-09-21', NULL, 1, 16, ''),
(866, 'Sportler/Fachwarte Ehrung', '2023-10-28', NULL, '2023-10-28', NULL, 1, 15, ''),
(867, 'Informationsgespräch Drachenboot (Torsten Markert)', '2023-03-25', '10:30:00', '2023-03-25', '16:30:00', 0, 14, ''),
(868, 'Sportler/Fachwarte Ehrung', '2023-10-28', NULL, '2023-10-28', NULL, 1, 14, ''),
(891, 'RS - 1 Nachwuchslehrgang | Kanurennsport | Präsenz', '2025-08-29', '10:00:00', '2025-08-31', '16:00:00', 0, 34, ''),
(892, 'neu', '2025-08-27', '08:00:00', '2025-08-27', '17:30:00', 0, 34, ''),
(893, 'neu 2', '2025-08-25', '03:00:00', '2025-08-25', '07:30:00', 0, 33, ''),
(894, 'fdbhfghsf', '2025-08-29', NULL, '2025-08-30', NULL, 1, 33, ''),
(895, 'Test', '2025-08-11', NULL, '2025-08-15', NULL, 1, 39, ''),
(896, 'Arbeit', '2025-07-23', '09:00:00', '2025-07-23', '17:00:00', 0, 38, ''),
(897, 'Test Lehrgang | Geschäftsstelle | Online/Präsenz', '2025-08-25', '18:00:00', '2025-08-26', '02:00:00', 0, 36, ''),
(898, 'TEST Online | Online', '2025-08-29', '12:05:00', '2025-08-31', '14:05:00', 0, 36, ''),
(899, 'Test Lehrgang 1', '2025-08-04', NULL, '2025-08-08', NULL, 1, 39, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kmpauschale`
--

CREATE TABLE `kmpauschale` (
  `id` int(11) NOT NULL,
  `artikelnummer` varchar(50) DEFAULT NULL,
  `kurzbezeichnung` varchar(255) DEFAULT NULL,
  `langbezeichnung` text DEFAULT NULL,
  `datum_ab` date NOT NULL,
  `datum_bis` date DEFAULT NULL,
  `preis` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kmpauschale`
--

INSERT INTO `kmpauschale` (`id`, `artikelnummer`, `kurzbezeichnung`, `langbezeichnung`, `datum_ab`, `datum_bis`, `preis`) VALUES
(1, 'KM-PKW', 'Kilometerpauschale PKW', 'Pauschale pro gefahrenem Kilometer mit dem Privat-PKW', '2024-01-01', NULL, 0.30);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunden`
--

CREATE TABLE `kunden` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `angelegt_am` datetime NOT NULL DEFAULT current_timestamp(),
  `geaendert_am` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `aktiv` tinyint(1) NOT NULL DEFAULT 1,
  `vorname` varchar(100) DEFAULT NULL,
  `nachname` varchar(100) DEFAULT NULL,
  `geschlecht` enum('m','w','d') DEFAULT NULL,
  `strasse` varchar(150) DEFAULT NULL,
  `hausnummer` varchar(20) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(100) DEFAULT NULL,
  `mobilnummer` varchar(50) DEFAULT NULL,
  `sepa_zustimmung` tinyint(1) DEFAULT 0,
  `kreditinstitut` varchar(150) DEFAULT NULL,
  `kontoinhaber` varchar(150) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `bic` varchar(11) DEFAULT NULL,
  `sepamandatsreferenz` varchar(100) DEFAULT NULL,
  `firmenname` varchar(150) DEFAULT NULL,
  `vereinsmitgliedschaft` varchar(150) DEFAULT NULL,
  `einzelmitglied` tinyint(1) DEFAULT 0,
  `familieneinzelmitglied` tinyint(1) DEFAULT 0,
  `parent_id` int(11) DEFAULT NULL,
  `eintrittsdatum` date DEFAULT NULL,
  `austrittsdatum` date DEFAULT NULL,
  `geburtsdatum` date DEFAULT NULL,
  `beitrag_aid` int(11) DEFAULT NULL,
  `notizen` text DEFAULT NULL,
  `unterschrift_datum` date DEFAULT NULL,
  `unterschrift_ort` varchar(150) DEFAULT NULL,
  `fa_volljaehrig` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kunden`
--

INSERT INTO `kunden` (`id`, `email`, `angelegt_am`, `geaendert_am`, `aktiv`, `vorname`, `nachname`, `geschlecht`, `strasse`, `hausnummer`, `plz`, `ort`, `mobilnummer`, `sepa_zustimmung`, `kreditinstitut`, `kontoinhaber`, `iban`, `bic`, `sepamandatsreferenz`, `firmenname`, `vereinsmitgliedschaft`, `einzelmitglied`, `familieneinzelmitglied`, `parent_id`, `eintrittsdatum`, `austrittsdatum`, `geburtsdatum`, `beitrag_aid`, `notizen`, `unterschrift_datum`, `unterschrift_ort`, `fa_volljaehrig`) VALUES
(1, 'c.wulf@voelsing.de', '2025-07-05 18:06:06', '2025-08-22 08:16:51', 1, 'Christian', 'Wulf', 'm', 'Dechant Bluel Strasse', '14', '31180', 'Giesen', '015116572594', 1, 'Volksbank Hildesheim', 'Christian Wulf', 'DE06251933310005498400', 'GENODEF1PAT', 'LKVN0000417', '', 'Hannoverscher Kanu Club von 1921', 1, 0, NULL, NULL, NULL, '1999-11-02', 15, 'Möchte im kommenden Jahr einen neuen Platz', '2021-10-11', 'Hasede', 0),
(173, 'delkeskamp@web.de', '2025-08-22 12:13:46', '2025-08-22 12:15:40', 1, 'Thomas', 'Beiß-Delkeskamp', 'm', 'Alter Hof 20', '', '38542', 'Leiferde-Delldorf', '', 0, '', '', '', '', NULL, '', '', 1, 0, NULL, NULL, NULL, NULL, 14, '', NULL, '', 0),
(174, 'm.bockermann09@gmail.com', '2025-08-22 12:13:46', '2025-08-22 12:16:27', 1, 'Maxim', 'Bockermann', 'm', 'Braustraße 25', '', '04107', 'Leipzig', '', 0, '', '', '', '', NULL, '', '', 1, 0, NULL, NULL, NULL, NULL, 50, '', NULL, '', 0),
(175, 'boehnke.nadine@gmx.de', '2025-08-22 12:13:46', '2025-08-22 12:17:15', 1, 'Nadine', 'Böhnke', 'w', 'Hoffmann-von-Fallersleben-Straße 47', '', '38304', 'Wolfenbüttel', '', 0, '', '', '', '', NULL, '', '', 1, 0, NULL, NULL, NULL, NULL, 14, '', NULL, '', 0),
(176, 'w.bordes@mail.de', '2025-08-22 12:13:46', '2025-08-22 12:17:24', 1, 'Wolfgang', 'Bordes', 'm', 'Lindenallee 59', '', '26122', 'Oldenburg', '', 0, '', '', '', '', NULL, '', '', 1, 0, NULL, NULL, NULL, NULL, 14, '', NULL, '', 0),
(177, 'susanne-both1@web.de', '2025-08-22 12:13:46', '2025-08-22 12:17:35', 1, 'Susanne', 'Bothor', 'w', 'Stiftsallee 50 a', '', '32425', 'Minden', '', 0, '', '', '', '', NULL, '', '', 1, 0, NULL, NULL, NULL, NULL, 14, '', NULL, '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunden_extra`
--

CREATE TABLE `kunden_extra` (
  `kunde_id` int(11) NOT NULL,
  `mitgliedsnummer` varchar(50) DEFAULT NULL,
  `nationalitaet` varchar(100) DEFAULT NULL,
  `land` varchar(100) DEFAULT NULL,
  `telefon_privat` varchar(50) DEFAULT NULL,
  `bezirk` varchar(100) DEFAULT NULL,
  `sperre_beginn` date DEFAULT NULL,
  `sperre_ende` date DEFAULT NULL,
  `sperre_begruendung` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kunden_extra`
--

INSERT INTO `kunden_extra` (`kunde_id`, `mitgliedsnummer`, `nationalitaet`, `land`, `telefon_privat`, `bezirk`, `sperre_beginn`, `sperre_ende`, `sperre_begruendung`) VALUES
(1, '1111', 'deutsch', 'Deutschland', '', 'Hannover', NULL, NULL, ''),
(173, '11052', 'Deutschland', 'Deutschland', '0', '* Eigener Verband', NULL, NULL, ''),
(174, '12052', 'Deutschland', 'Deutschland', '0', '* Eigener Verband', NULL, NULL, ''),
(175, '12398', 'Deutschland', 'Deutschland', '0', '* Eigener Verband', NULL, NULL, ''),
(176, '11787', 'Deutschland', 'Deutschland', '0', '* Eigener Verband', NULL, NULL, ''),
(177, '11312', 'Deutschland', 'Deutschland', '0', '* Eigener Verband', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunden_funktionen`
--

CREATE TABLE `kunden_funktionen` (
  `id` int(11) NOT NULL,
  `kunden_id` int(11) NOT NULL,
  `funktion_id` int(11) NOT NULL,
  `gueltig_von` date NOT NULL,
  `gueltig_bis` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunden_jahresvertraege`
--

CREATE TABLE `kunden_jahresvertraege` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `artikel_id` int(11) NOT NULL,
  `angelegt_am` datetime NOT NULL DEFAULT current_timestamp(),
  `startdatum` date DEFAULT NULL,
  `enddatum` date DEFAULT NULL,
  `ratenzahlung` tinyint(1) NOT NULL DEFAULT 0,
  `raten_anzahl` int(11) DEFAULT NULL,
  `preis` decimal(10,2) DEFAULT NULL,
  `raten_monate` text DEFAULT NULL,
  `pdf_dateiname` varchar(255) DEFAULT NULL,
  `bootsliegeplatz` varchar(255) DEFAULT NULL,
  `campingplatz` int(11) DEFAULT NULL,
  `schuppenplatz` varchar(255) DEFAULT NULL,
  `stromzaehler` varchar(64) DEFAULT NULL,
  `vorgelaende_platz` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `kunden_jahresvertraege`
--

INSERT INTO `kunden_jahresvertraege` (`id`, `kunde_id`, `artikel_id`, `angelegt_am`, `startdatum`, `enddatum`, `ratenzahlung`, `raten_anzahl`, `preis`, `raten_monate`, `pdf_dateiname`, `bootsliegeplatz`, `campingplatz`, `schuppenplatz`, `stromzaehler`, `vorgelaende_platz`) VALUES
(729, 1, 17, '2025-08-25 11:03:18', NULL, NULL, 0, NULL, 450.00, NULL, NULL, '1', NULL, '1', NULL, '1');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrgaenge`
--

CREATE TABLE `lehrgaenge` (
  `id` int(11) NOT NULL,
  `kalender_event_id` int(11) DEFAULT NULL,
  `kalender_category_id` int(11) DEFAULT NULL,
  `bezeichnung` varchar(100) DEFAULT NULL,
  `datum_von` date DEFAULT NULL,
  `datum_bis` date DEFAULT NULL,
  `kosten_mitglied` decimal(10,2) DEFAULT NULL,
  `kosten_extern` decimal(10,2) DEFAULT NULL,
  `honorar_trainer` decimal(10,2) DEFAULT NULL,
  `honorar_leitung` decimal(10,2) DEFAULT NULL,
  `min_teilnehmer` int(11) DEFAULT NULL,
  `max_teilnehmer` int(11) DEFAULT NULL,
  `start_ab_anzahl` int(11) DEFAULT NULL,
  `strasse` varchar(100) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(100) DEFAULT NULL,
  `status` enum('angelegt','abrechnungsreif','teilweise_abgerechnet','abgerechnet') DEFAULT 'angelegt',
  `foerder_ls_olympische` tinyint(1) DEFAULT 0,
  `foerder_ls_training` tinyint(1) DEFAULT 0,
  `foerder_af_ausbildung` tinyint(1) DEFAULT 0,
  `foerder_af_training` tinyint(1) DEFAULT 0,
  `foerder_af_arbeitstagungen` tinyint(1) DEFAULT 0,
  `foerder_af_nicht_olympische` tinyint(1) DEFAULT 0,
  `foerder_af_oea` tinyint(1) DEFAULT 0,
  `foerder_af_nuel` tinyint(1) DEFAULT 0,
  `archiviert` tinyint(1) DEFAULT 0,
  `sportart_id` int(11) DEFAULT NULL,
  `lehrgangsleitung` varchar(255) DEFAULT NULL,
  `online_start` datetime DEFAULT NULL,
  `online_ende` datetime DEFAULT NULL,
  `praesenz_start` datetime DEFAULT NULL,
  `praesenz_ende` datetime DEFAULT NULL,
  `leitung_anwesenheit_von` datetime DEFAULT NULL,
  `leitung_anwesenheit_bis` datetime DEFAULT NULL,
  `leitung_uebernachtung` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `lehrgaenge`
--

INSERT INTO `lehrgaenge` (`id`, `kalender_event_id`, `kalender_category_id`, `bezeichnung`, `datum_von`, `datum_bis`, `kosten_mitglied`, `kosten_extern`, `honorar_trainer`, `honorar_leitung`, `min_teilnehmer`, `max_teilnehmer`, `start_ab_anzahl`, `strasse`, `plz`, `ort`, `status`, `foerder_ls_olympische`, `foerder_ls_training`, `foerder_af_ausbildung`, `foerder_af_training`, `foerder_af_arbeitstagungen`, `foerder_af_nicht_olympische`, `foerder_af_oea`, `foerder_af_nuel`, `archiviert`, `sportart_id`, `lehrgangsleitung`, `online_start`, `online_ende`, `praesenz_start`, `praesenz_ende`, `leitung_anwesenheit_von`, `leitung_anwesenheit_bis`, `leitung_uebernachtung`) VALUES
(50, 891, 34, 'RS - 1 Nachwuchslehrgang', '2025-08-29', '2025-08-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', 'angelegt', 0, 0, 0, 0, 0, 0, 0, 0, 0, 4, NULL, NULL, NULL, '2025-08-29 10:00:00', '2025-08-31 16:00:00', NULL, NULL, 1),
(51, 897, 36, 'Test Lehrgang', '2025-08-25', '2025-08-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', 'angelegt', 0, 0, 0, 0, 0, 0, 0, 0, 0, 11, NULL, '2025-08-26 12:04:00', '2025-08-26 13:34:00', '2025-08-25 18:00:00', '2025-08-26 02:00:00', NULL, NULL, 1),
(52, 898, 36, 'TEST Online', '2025-08-29', '2025-08-31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', '', '', 'angelegt', 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, '2025-08-29 12:05:00', '2025-08-31 14:05:00', NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrgang_auslagen`
--

CREATE TABLE `lehrgang_auslagen` (
  `id` int(11) NOT NULL,
  `lehrgang_id` int(11) NOT NULL,
  `beschreibung` varchar(255) DEFAULT NULL,
  `betrag` decimal(10,2) DEFAULT NULL,
  `erstatter` varchar(100) DEFAULT NULL,
  `beleg_pfad` varchar(255) DEFAULT NULL,
  `erstellt_am` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrgang_dokumente`
--

CREATE TABLE `lehrgang_dokumente` (
  `id` int(11) NOT NULL,
  `lehrgang_id` int(11) NOT NULL,
  `dateiname` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT '',
  `bemerkung` varchar(255) DEFAULT NULL,
  `hochgeladen_am` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrgang_leitung`
--

CREATE TABLE `lehrgang_leitung` (
  `id` int(11) NOT NULL,
  `lehrgang_id` int(11) NOT NULL,
  `kunde_id` int(11) NOT NULL,
  `honorar` decimal(10,2) DEFAULT NULL,
  `anwesenheit_von` datetime DEFAULT NULL,
  `anwesenheit_bis` datetime DEFAULT NULL,
  `uebernachtung` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrgang_teilnahmen`
--

CREATE TABLE `lehrgang_teilnahmen` (
  `id` int(11) NOT NULL,
  `lehrgang_id` int(11) NOT NULL,
  `kunde_id` int(11) NOT NULL,
  `angemeldet_am` datetime DEFAULT current_timestamp(),
  `ist_mitglied` tinyint(1) DEFAULT NULL,
  `kosten` decimal(10,2) DEFAULT NULL,
  `zahler_verein` tinyint(1) DEFAULT NULL,
  `anwesenheit_von` datetime DEFAULT NULL,
  `anwesenheit_bis` datetime DEFAULT NULL,
  `uebernachtung` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `lehrgang_trainer`
--

CREATE TABLE `lehrgang_trainer` (
  `id` int(11) NOT NULL,
  `lehrgang_id` int(11) NOT NULL,
  `kunde_id` int(11) NOT NULL,
  `honorar` decimal(10,2) DEFAULT NULL,
  `anwesenheit_von` datetime DEFAULT NULL,
  `anwesenheit_bis` datetime DEFAULT NULL,
  `uebernachtung` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `page_info`
--

CREATE TABLE `page_info` (
  `page` varchar(50) NOT NULL,
  `info` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `plaetze`
--

CREATE TABLE `plaetze` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(255) NOT NULL,
  `platzgruppe_id` int(11) NOT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `plaetze`
--

INSERT INTO `plaetze` (`id`, `bezeichnung`, `platzgruppe_id`, `aktiv`) VALUES
(1, 'Dünenplatz 1', 2, 1),
(2, 'Dünenplatz 2', 2, 1),
(3, 'Dünenplatz 3', 2, 1),
(4, 'Zeltplatz 1', 1, 1),
(5, 'Zeltplatz 2', 1, 1),
(6, 'Zeltplatz 3', 1, 1),
(7, 'Zimmer Elbe', 3, 1),
(8, 'Zimmer Innerste', 3, 1),
(9, 'Zimmer Lachte', 3, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `platzgruppen`
--

CREATE TABLE `platzgruppen` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `platzgruppen`
--

INSERT INTO `platzgruppen` (`id`, `bezeichnung`) VALUES
(1, 'Zeltplatz'),
(2, 'Dünenplatz'),
(3, 'WKH-Zimmer');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `preisliste`
--

CREATE TABLE `preisliste` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `preis` decimal(10,2) NOT NULL,
  `preis_ab` date NOT NULL,
  `preis_bis` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `preisliste`
--

INSERT INTO `preisliste` (`id`, `aid`, `preis`, `preis_ab`, `preis_bis`) VALUES
(2, 2, 50.00, '2025-07-04', NULL),
(3, 3, 60.00, '2025-07-04', NULL),
(4, 4, 70.00, '2025-07-04', NULL),
(5, 5, 100.00, '2025-07-04', NULL),
(6, 6, 50.00, '2025-07-04', NULL),
(8, 7, 35.00, '2025-07-04', NULL),
(10, 8, 50.00, '2025-07-04', NULL),
(11, 9, 60.00, '2025-07-04', NULL),
(12, 10, 100.00, '2025-07-04', NULL),
(13, 12, 50.00, '2025-07-06', NULL),
(15, 14, 85.00, '2025-07-12', NULL),
(16, 15, 155.00, '2025-07-12', NULL),
(17, 16, 0.00, '2025-07-12', NULL),
(18, 17, 450.00, '2025-07-12', NULL),
(19, 18, 500.00, '2025-07-12', NULL),
(20, 21, 40.00, '2025-07-12', NULL),
(21, 19, 350.00, '2025-07-12', NULL),
(22, 20, 180.00, '2025-07-12', NULL),
(23, 22, 700.00, '2025-07-12', NULL),
(24, 23, 0.50, '2025-07-14', NULL),
(25, 24, 650.00, '2025-07-14', NULL),
(26, 25, 20.00, '2015-01-01', NULL),
(27, 26, 5.00, '2025-08-06', NULL),
(28, 27, 250.00, '2025-08-06', NULL),
(29, 28, 16.00, '2025-08-06', NULL),
(30, 29, 24.00, '2025-08-06', NULL),
(31, 30, 32.00, '2025-08-06', NULL),
(32, 35, 6.00, '2025-08-06', NULL),
(33, 33, 0.00, '2025-08-06', NULL),
(34, 34, 4.00, '2025-08-06', NULL),
(35, 32, 4.00, '2025-08-06', NULL),
(36, 31, 6.00, '2025-08-06', NULL),
(37, 38, 12.00, '2025-08-12', NULL),
(38, 39, 18.00, '2025-08-12', NULL),
(39, 40, 24.00, '2025-08-12', NULL),
(40, 41, 4.50, '2025-08-12', NULL),
(41, 42, 3.00, '2025-08-12', NULL),
(42, 43, 0.00, '2025-08-12', NULL),
(43, 44, 3.00, '2025-08-12', NULL),
(44, 45, 4.50, '2025-08-12', NULL),
(45, 47, 0.30, '2025-01-01', NULL),
(46, 48, 25.00, '2025-08-21', NULL),
(47, 49, 15.00, '2025-08-21', NULL),
(48, 50, 70.00, '2025-08-21', NULL),
(49, 51, 45.00, '2025-08-21', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rechnungen`
--

CREATE TABLE `rechnungen` (
  `id` int(11) NOT NULL,
  `rechnungsnummer` varchar(10) DEFAULT NULL,
  `empfaenger` varchar(255) NOT NULL,
  `empfaenger_id` int(11) DEFAULT NULL,
  `erstellt_am` date NOT NULL,
  `status` enum('angelegt','geprueft','versendet','bezahlt','geloescht') NOT NULL DEFAULT 'angelegt',
  `archiviert_am` datetime DEFAULT NULL,
  `kopftext` text DEFAULT NULL,
  `fusstext` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `bezahldatum` date DEFAULT NULL,
  `sepa` tinyint(1) NOT NULL DEFAULT 0,
  `sepa_ref` varchar(100) DEFAULT NULL,
  `ratenzahlung` tinyint(1) NOT NULL DEFAULT 0,
  `jahresrechnung` tinyint(1) NOT NULL DEFAULT 0,
  `einzelmitgliedsrechnung` tinyint(1) NOT NULL DEFAULT 0,
  `mahnstufe` tinyint(1) NOT NULL DEFAULT 0,
  `geloescht_von` int(11) DEFAULT NULL,
  `geloescht_grund` text DEFAULT NULL,
  `geloescht_am` datetime DEFAULT NULL,
  `buchungen_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `rechnungen`
--

INSERT INTO `rechnungen` (`id`, `rechnungsnummer`, `empfaenger`, `empfaenger_id`, `erstellt_am`, `status`, `archiviert_am`, `kopftext`, `fusstext`, `parent_id`, `bezahldatum`, `sepa`, `sepa_ref`, `ratenzahlung`, `jahresrechnung`, `einzelmitgliedsrechnung`, `mahnstufe`, `geloescht_von`, `geloescht_grund`, `geloescht_am`, `buchungen_id`) VALUES
(34, 'RE000001', 'Johanna Wulf', 7, '2025-08-22', 'angelegt', NULL, '', '', NULL, '2025-08-22', 0, '', 0, 0, 1, 0, NULL, NULL, NULL, NULL),
(35, 'RE000002', 'Florian Wulf', 6, '2025-08-22', 'angelegt', NULL, '', '', NULL, '2025-08-22', 0, '', 0, 0, 1, 0, NULL, NULL, NULL, NULL),
(36, 'RE000003', 'Christian Wulf', 1, '2025-08-25', 'bezahlt', NULL, 'Rechnung für August', '', NULL, '2025-09-08', 0, '', 0, 0, 0, 0, NULL, NULL, NULL, NULL),
(37, 'RE000004', 'Thomas Beiß-Delkeskamp', 173, '2025-08-25', 'geprueft', NULL, 'test', '', NULL, '2025-09-08', 0, '', 0, 0, 0, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rechnungskopftexte`
--

CREATE TABLE `rechnungskopftexte` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `aktiv` tinyint(1) DEFAULT 1,
  `erstellt_am` datetime DEFAULT current_timestamp(),
  `bearbeitet_am` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `erstellt_von` int(11) DEFAULT NULL,
  `bearbeitet_von` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rechnungspositionen`
--

CREATE TABLE `rechnungspositionen` (
  `id` int(11) NOT NULL,
  `rechnung_id` int(11) NOT NULL,
  `artikelnummer` varchar(50) NOT NULL,
  `kurzbez` varchar(255) DEFAULT NULL,
  `langbez` text DEFAULT NULL,
  `einzelpreis` decimal(10,2) NOT NULL,
  `menge` int(11) NOT NULL,
  `rabatt` decimal(5,2) NOT NULL DEFAULT 0.00,
  `bemerkung` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `rechnungspositionen`
--

INSERT INTO `rechnungspositionen` (`id`, `rechnung_id`, `artikelnummer`, `kurzbez`, `langbez`, `einzelpreis`, `menge`, `rabatt`, `bemerkung`) VALUES
(38, 34, '1006', 'Partner Einzelmitglieder', '', 45.00, 1, 0.00, ''),
(39, 35, '1000', 'Einzelmitgliedschaft', '', 85.00, 1, 0.00, ''),
(40, 36, '0002', 'LLZ Doppelzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', 50.00, 2, 0.00, ''),
(41, 37, '0002', 'LLZ Doppelzimmer', 'Übernachtungszimmer im Landesleistungszentrum Hannover/Ahlem', 50.00, 2, 0.00, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rollen`
--

CREATE TABLE `rollen` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(50) NOT NULL,
  `beschreibung` text DEFAULT NULL,
  `archiviert` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `rollen`
--

INSERT INTO `rollen` (`id`, `bezeichnung`, `beschreibung`, `archiviert`) VALUES
(2, 'Administrator', 'hat fast alle Rechte', 0),
(3, 'Benutzer', 'hat eingeschränkte Rechte, kann Ausgaben und Buchungen anlegen', 0),
(4, 'test', 'tesrolle', 1),
(10, 'Superadmin', 'hat uneingeschränkte Rechte', 0),
(11, 'Fachwarte', 'darf Ausgaben seiner Sparte freigeben', 0),
(12, 'Trainer', 'kann Ausgaben einreichen', 0),
(13, 'Ressortleiter', 'Ressortleiter, darf Ausgaben seiner Sparte freigeben', 1),
(14, 'Präsidium', 'kann übergeordnete Ausgaben freigeben', 0),
(15, 'Geschäftsstellenmitarbeiter', 'hat beschränkte Rechte, fast kompletter Zugang', 0),
(16, 'Einzelmitglied', 'Einzelmitglied im LKV', 1),
(17, 'Einzelmitglied- Familie', 'Familienteil eines Einzelmitgliedes', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schuppen`
--

CREATE TABLE `schuppen` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `artikel_id` int(11) DEFAULT NULL,
  `bootsliegeplatz` varchar(255) DEFAULT NULL,
  `platz_info` text DEFAULT NULL,
  `platz_foto` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `boot_name` varchar(255) DEFAULT NULL,
  `boot_typ` varchar(255) DEFAULT NULL,
  `boot_laenge` varchar(50) DEFAULT NULL,
  `grid_slot` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `schuppen`
--

INSERT INTO `schuppen` (`id`, `kunde_id`, `artikel_id`, `bootsliegeplatz`, `platz_info`, `platz_foto`, `sort_order`, `boot_name`, `boot_typ`, `boot_laenge`, `grid_slot`) VALUES
(3, 1, 17, '1', '', NULL, 0, 'luna', 'etc', '6 m', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `schuppen_grid`
--

CREATE TABLE `schuppen_grid` (
  `slot` int(11) NOT NULL,
  `row_num` int(11) NOT NULL,
  `col_num` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `schuppen_grid`
--

INSERT INTO `schuppen_grid` (`slot`, `row_num`, `col_num`, `active`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1),
(6, 1, 6, 1),
(7, 1, 7, 1),
(8, 1, 8, 1),
(9, 1, 9, 1),
(10, 1, 10, 1),
(11, 1, 11, 1),
(12, 1, 12, 1),
(13, 1, 13, 1),
(14, 1, 14, 1),
(15, 1, 15, 1),
(16, 1, 16, 1),
(17, 1, 17, 1),
(18, 1, 18, 1),
(19, 1, 19, 1),
(20, 1, 20, 1),
(21, 2, 1, 1),
(22, 2, 2, 1),
(23, 2, 3, 1),
(24, 2, 4, 1),
(25, 2, 5, 1),
(26, 2, 6, 0),
(27, 2, 7, 1),
(28, 2, 8, 1),
(29, 2, 9, 1),
(30, 2, 10, 1),
(31, 2, 11, 1),
(32, 2, 12, 1),
(33, 2, 13, 1),
(34, 2, 14, 1),
(35, 2, 15, 1),
(36, 2, 16, 1),
(37, 2, 17, 1),
(38, 2, 18, 1),
(39, 2, 19, 1),
(40, 2, 20, 1),
(41, 3, 1, 1),
(42, 3, 2, 1),
(43, 3, 3, 1),
(44, 3, 4, 1),
(45, 3, 5, 1),
(46, 3, 6, 1),
(47, 3, 7, 1),
(48, 3, 8, 1),
(49, 3, 9, 1),
(50, 3, 10, 1),
(51, 3, 11, 1),
(52, 3, 12, 1),
(53, 3, 13, 1),
(54, 3, 14, 0),
(55, 3, 15, 1),
(56, 3, 16, 1),
(57, 3, 17, 1),
(58, 3, 18, 1),
(59, 3, 19, 1),
(60, 3, 20, 1),
(61, 4, 1, 1),
(62, 4, 2, 1),
(63, 4, 3, 1),
(64, 4, 4, 1),
(65, 4, 5, 1),
(66, 4, 6, 1),
(67, 4, 7, 1),
(68, 4, 8, 1),
(69, 4, 9, 1),
(70, 4, 10, 1),
(71, 4, 11, 1),
(72, 4, 12, 1),
(73, 4, 13, 1),
(74, 4, 14, 1),
(75, 4, 15, 1),
(76, 4, 16, 1),
(77, 4, 17, 1),
(78, 4, 18, 1),
(79, 4, 19, 1),
(80, 4, 20, 1),
(81, 5, 1, 1),
(82, 5, 2, 1),
(83, 5, 3, 1),
(84, 5, 4, 1),
(85, 5, 5, 1),
(86, 5, 6, 1),
(87, 5, 7, 1),
(88, 5, 8, 1),
(89, 5, 9, 1),
(90, 5, 10, 1),
(91, 5, 11, 1),
(92, 5, 12, 1),
(93, 5, 13, 1),
(94, 5, 14, 0),
(95, 5, 15, 1),
(96, 5, 16, 1),
(97, 5, 17, 1),
(98, 5, 18, 1),
(99, 5, 19, 1),
(100, 5, 20, 1),
(101, 6, 1, 1),
(102, 6, 2, 1),
(103, 6, 3, 1),
(104, 6, 4, 1),
(105, 6, 5, 1),
(106, 6, 6, 1),
(107, 6, 7, 1),
(108, 6, 8, 1),
(109, 6, 9, 1),
(110, 6, 10, 1),
(111, 6, 11, 1),
(112, 6, 12, 1),
(113, 6, 13, 1),
(114, 6, 14, 1),
(115, 6, 15, 1),
(116, 6, 16, 1),
(117, 6, 17, 1),
(118, 6, 18, 1),
(119, 6, 19, 1),
(120, 6, 20, 1),
(121, 7, 1, 1),
(122, 7, 2, 1),
(123, 7, 3, 1),
(124, 7, 4, 1),
(125, 7, 5, 1),
(126, 7, 6, 1),
(127, 7, 7, 1),
(128, 7, 8, 1),
(129, 7, 9, 1),
(130, 7, 10, 1),
(131, 7, 11, 1),
(132, 7, 12, 1),
(133, 7, 13, 1),
(134, 7, 14, 1),
(135, 7, 15, 1),
(136, 7, 16, 1),
(137, 7, 17, 1),
(138, 7, 18, 1),
(139, 7, 19, 1),
(140, 7, 20, 1),
(141, 8, 1, 1),
(142, 8, 2, 1),
(143, 8, 3, 1),
(144, 8, 4, 1),
(145, 8, 5, 1),
(146, 8, 6, 1),
(147, 8, 7, 1),
(148, 8, 8, 1),
(149, 8, 9, 1),
(150, 8, 10, 1),
(151, 8, 11, 1),
(152, 8, 12, 1),
(153, 8, 13, 1),
(154, 8, 14, 1),
(155, 8, 15, 1),
(156, 8, 16, 1),
(157, 8, 17, 1),
(158, 8, 18, 1),
(159, 8, 19, 1),
(160, 8, 20, 1),
(161, 9, 1, 1),
(162, 9, 2, 1),
(163, 9, 3, 1),
(164, 9, 4, 1),
(165, 9, 5, 1),
(166, 9, 6, 1),
(167, 9, 7, 1),
(168, 9, 8, 1),
(169, 9, 9, 1),
(170, 9, 10, 1),
(171, 9, 11, 1),
(172, 9, 12, 1),
(173, 9, 13, 1),
(174, 9, 14, 1),
(175, 9, 15, 1),
(176, 9, 16, 1),
(177, 9, 17, 1),
(178, 9, 18, 1),
(179, 9, 19, 1),
(180, 9, 20, 1),
(181, 10, 1, 1),
(182, 10, 2, 1),
(183, 10, 3, 1),
(184, 10, 4, 1),
(185, 10, 5, 1),
(186, 10, 6, 1),
(187, 10, 7, 1),
(188, 10, 8, 1),
(189, 10, 9, 1),
(190, 10, 10, 1),
(191, 10, 11, 0),
(192, 10, 12, 0),
(193, 10, 13, 0),
(194, 10, 14, 1),
(195, 10, 15, 1),
(196, 10, 16, 1),
(197, 10, 17, 1),
(198, 10, 18, 1),
(199, 10, 19, 1),
(200, 10, 20, 1),
(201, 11, 1, 1),
(202, 11, 2, 1),
(203, 11, 3, 1),
(204, 11, 4, 1),
(205, 11, 5, 1),
(206, 11, 6, 1),
(207, 11, 7, 1),
(208, 11, 8, 1),
(209, 11, 9, 1),
(210, 11, 10, 1),
(211, 11, 11, 1),
(212, 11, 12, 1),
(213, 11, 13, 1),
(214, 11, 14, 1),
(215, 11, 15, 1),
(216, 11, 16, 1),
(217, 11, 17, 1),
(218, 11, 18, 1),
(219, 11, 19, 1),
(220, 11, 20, 1),
(221, 12, 1, 1),
(222, 12, 2, 1),
(223, 12, 3, 1),
(224, 12, 4, 1),
(225, 12, 5, 1),
(226, 12, 6, 1),
(227, 12, 7, 1),
(228, 12, 8, 1),
(229, 12, 9, 1),
(230, 12, 10, 1),
(231, 12, 11, 1),
(232, 12, 12, 1),
(233, 12, 13, 1),
(234, 12, 14, 1),
(235, 12, 15, 1),
(236, 12, 16, 1),
(237, 12, 17, 1),
(238, 12, 18, 1),
(239, 12, 19, 1),
(240, 12, 20, 1),
(241, 13, 1, 1),
(242, 13, 2, 1),
(243, 13, 3, 1),
(244, 13, 4, 1),
(245, 13, 5, 1),
(246, 13, 6, 1),
(247, 13, 7, 1),
(248, 13, 8, 1),
(249, 13, 9, 1),
(250, 13, 10, 1),
(251, 13, 11, 1),
(252, 13, 12, 1),
(253, 13, 13, 1),
(254, 13, 14, 1),
(255, 13, 15, 1),
(256, 13, 16, 1),
(257, 13, 17, 1),
(258, 13, 18, 1),
(259, 13, 19, 1),
(260, 13, 20, 1),
(261, 14, 1, 1),
(262, 14, 2, 1),
(263, 14, 3, 1),
(264, 14, 4, 1),
(265, 14, 5, 1),
(266, 14, 6, 1),
(267, 14, 7, 1),
(268, 14, 8, 1),
(269, 14, 9, 1),
(270, 14, 10, 1),
(271, 14, 11, 1),
(272, 14, 12, 1),
(273, 14, 13, 1),
(274, 14, 14, 1),
(275, 14, 15, 1),
(276, 14, 16, 1),
(277, 14, 17, 1),
(278, 14, 18, 1),
(279, 14, 19, 1),
(280, 14, 20, 1),
(281, 15, 1, 1),
(282, 15, 2, 1),
(283, 15, 3, 1),
(284, 15, 4, 1),
(285, 15, 5, 1),
(286, 15, 6, 1),
(287, 15, 7, 1),
(288, 15, 8, 1),
(289, 15, 9, 1),
(290, 15, 10, 1),
(291, 15, 11, 1),
(292, 15, 12, 1),
(293, 15, 13, 1),
(294, 15, 14, 1),
(295, 15, 15, 1),
(296, 15, 16, 1),
(297, 15, 17, 1),
(298, 15, 18, 1),
(299, 15, 19, 1),
(300, 15, 20, 1),
(301, 16, 1, 1),
(302, 16, 2, 1),
(303, 16, 3, 1),
(304, 16, 4, 1),
(305, 16, 5, 1),
(306, 16, 6, 1),
(307, 16, 7, 1),
(308, 16, 8, 1),
(309, 16, 9, 1),
(310, 16, 10, 1),
(311, 16, 11, 1),
(312, 16, 12, 1),
(313, 16, 13, 1),
(314, 16, 14, 1),
(315, 16, 15, 1),
(316, 16, 16, 1),
(317, 16, 17, 1),
(318, 16, 18, 1),
(319, 16, 19, 1),
(320, 16, 20, 1),
(321, 17, 1, 1),
(322, 17, 2, 1),
(323, 17, 3, 1),
(324, 17, 4, 1),
(325, 17, 5, 1),
(326, 17, 6, 1),
(327, 17, 7, 1),
(328, 17, 8, 1),
(329, 17, 9, 1),
(330, 17, 10, 1),
(331, 17, 11, 1),
(332, 17, 12, 1),
(333, 17, 13, 1),
(334, 17, 14, 1),
(335, 17, 15, 1),
(336, 17, 16, 1),
(337, 17, 17, 1),
(338, 17, 18, 1),
(339, 17, 19, 1),
(340, 17, 20, 1),
(341, 18, 1, 1),
(342, 18, 2, 1),
(343, 18, 3, 1),
(344, 18, 4, 1),
(345, 18, 5, 1),
(346, 18, 6, 1),
(347, 18, 7, 1),
(348, 18, 8, 1),
(349, 18, 9, 1),
(350, 18, 10, 1),
(351, 18, 11, 1),
(352, 18, 12, 1),
(353, 18, 13, 1),
(354, 18, 14, 1),
(355, 18, 15, 1),
(356, 18, 16, 1),
(357, 18, 17, 1),
(358, 18, 18, 1),
(359, 18, 19, 1),
(360, 18, 20, 1),
(361, 19, 1, 1),
(362, 19, 2, 1),
(363, 19, 3, 1),
(364, 19, 4, 1),
(365, 19, 5, 1),
(366, 19, 6, 1),
(367, 19, 7, 1),
(368, 19, 8, 1),
(369, 19, 9, 1),
(370, 19, 10, 1),
(371, 19, 11, 1),
(372, 19, 12, 1),
(373, 19, 13, 1),
(374, 19, 14, 1),
(375, 19, 15, 1),
(376, 19, 16, 1),
(377, 19, 17, 1),
(378, 19, 18, 1),
(379, 19, 19, 1),
(380, 19, 20, 1),
(381, 20, 1, 1),
(382, 20, 2, 1),
(383, 20, 3, 1),
(384, 20, 4, 1),
(385, 20, 5, 1),
(386, 20, 6, 1),
(387, 20, 7, 1),
(388, 20, 8, 1),
(389, 20, 9, 1),
(390, 20, 10, 1),
(391, 20, 11, 1),
(392, 20, 12, 1),
(393, 20, 13, 1),
(394, 20, 14, 1),
(395, 20, 15, 1),
(396, 20, 16, 1),
(397, 20, 17, 1),
(398, 20, 18, 1),
(399, 20, 19, 1),
(400, 20, 20, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `seiten_info`
--

CREATE TABLE `seiten_info` (
  `id` int(11) NOT NULL,
  `seite` varchar(100) NOT NULL,
  `info` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sportarten`
--

CREATE TABLE `sportarten` (
  `id` int(11) NOT NULL,
  `bezeichnung` varchar(100) NOT NULL,
  `archiviert` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `sportarten`
--

INSERT INTO `sportarten` (`id`, `bezeichnung`, `archiviert`) VALUES
(1, 'Kanusegeln', 0),
(2, 'Kanuslalom', 0),
(3, 'Kanuwildwasser', 0),
(4, 'Kanurennsport', 0),
(5, 'Kanupolo', 0),
(6, 'SUP', 0),
(7, 'Kanuwandern', 0),
(8, 'Ausbildung', 0),
(9, 'Präsidium', 0),
(10, 'Freizeitsport', 0),
(11, 'Geschäftsstelle', 0),
(12, 'Mardorf WKH', 0),
(13, 'Mardorf Platz', 0),
(14, 'LLZ', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stegbelegung`
--

CREATE TABLE `stegbelegung` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `artikel_id` int(11) DEFAULT NULL,
  `bootsliegeplatz` varchar(255) DEFAULT NULL,
  `boot_name` varchar(255) DEFAULT NULL,
  `boot_typ` varchar(255) DEFAULT NULL,
  `boot_laenge` varchar(50) DEFAULT NULL,
  `platz_info` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seite` enum('links','rechts') NOT NULL DEFAULT 'links',
  `platz_foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stromzaehler_dauercamping_jahresverbrauch`
--

CREATE TABLE `stromzaehler_dauercamping_jahresverbrauch` (
  `zaehlernummer` varchar(50) NOT NULL,
  `jahr` int(11) NOT NULL,
  `startdatum` date NOT NULL,
  `enddatum` date NOT NULL,
  `verbrauch` decimal(10,2) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stromzaehler_mardorf`
--

CREATE TABLE `stromzaehler_mardorf` (
  `id` int(11) NOT NULL,
  `zaehlernummer` varchar(50) NOT NULL,
  `erfassungsdatum` date NOT NULL,
  `zaehlerstand` decimal(10,2) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stromzaehler_mardorf_jahresverbrauch`
--

CREATE TABLE `stromzaehler_mardorf_jahresverbrauch` (
  `zaehlernummer` varchar(50) NOT NULL,
  `jahr` int(11) NOT NULL,
  `startdatum` date NOT NULL,
  `enddatum` date NOT NULL,
  `verbrauch` decimal(10,2) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stromzaehler_parent_mardorf`
--

CREATE TABLE `stromzaehler_parent_mardorf` (
  `id` int(11) NOT NULL,
  `nummer` varchar(50) NOT NULL,
  `bezeichnung` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stromzaehler_parent_mardorf_jahresverbrauch`
--

CREATE TABLE `stromzaehler_parent_mardorf_jahresverbrauch` (
  `zaehler_id` int(11) NOT NULL,
  `jahr` int(11) NOT NULL,
  `startdatum` date NOT NULL,
  `enddatum` date NOT NULL,
  `verbrauch` decimal(10,2) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stromzaehler_parent_mardorf_werte`
--

CREATE TABLE `stromzaehler_parent_mardorf_werte` (
  `id` int(11) NOT NULL,
  `zaehler_id` int(11) NOT NULL,
  `erfassungsdatum` date NOT NULL,
  `zaehlerstand` decimal(10,2) NOT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `strom_dauercamping`
--

CREATE TABLE `strom_dauercamping` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `artikel_id` int(11) DEFAULT NULL,
  `zaehlernummer` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `strom_dauercamping`
--

INSERT INTO `strom_dauercamping` (`id`, `kunde_id`, `artikel_id`, `zaehlernummer`) VALUES
(17, 1, 23, '9');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `todos`
--

CREATE TABLE `todos` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `week_start` date NOT NULL,
  `todo_date` date NOT NULL,
  `priority` enum('hoch','mittel','niedrig') NOT NULL DEFAULT 'mittel',
  `start_time` time DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `due_time` time DEFAULT NULL,
  `repeat_freq` enum('none','daily','weekly','monthly') NOT NULL DEFAULT 'none',
  `repeat_until` date DEFAULT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT 0,
  `archived_at` datetime DEFAULT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `created_by_admin` int(11) DEFAULT NULL,
  `in_progress_by_admin` int(11) DEFAULT NULL,
  `in_progress_at` datetime DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `remind_at` datetime DEFAULT NULL,
  `reminder_sent_at` datetime DEFAULT NULL,
  `snooze_until` datetime DEFAULT NULL,
  `is_done` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=offen,1=erledigt,2=in_bearbeitung',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `completed_by_admin` int(11) DEFAULT NULL,
  `sent_scope` enum('single','users','all') NOT NULL DEFAULT 'single',
  `dispatch_group` varchar(32) DEFAULT NULL,
  `is_forwarded` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vertraege`
--

CREATE TABLE `vertraege` (
  `id` int(11) NOT NULL,
  `vertragsnummer` varchar(20) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `strasse` varchar(255) DEFAULT NULL,
  `hausnummer` varchar(20) DEFAULT NULL,
  `plz` varchar(10) DEFAULT NULL,
  `ort` varchar(255) DEFAULT NULL,
  `verein` varchar(255) DEFAULT NULL,
  `vertragstyp` varchar(50) DEFAULT NULL,
  `bootsliegeplatz` varchar(100) DEFAULT NULL,
  `boot_name` varchar(100) DEFAULT NULL,
  `boot_typ` varchar(100) DEFAULT NULL,
  `boot_laenge` decimal(5,2) DEFAULT NULL,
  `campingplatz` varchar(100) DEFAULT NULL,
  `wohnwagentyp` varchar(100) DEFAULT NULL,
  `wohnwagenbreite` decimal(5,2) DEFAULT NULL,
  `wohnwagenlaenge` decimal(5,2) DEFAULT NULL,
  `stromzaehler` varchar(100) DEFAULT NULL,
  `hundeplatz` tinyint(1) NOT NULL DEFAULT 0,
  `erstellt_am` date NOT NULL DEFAULT current_timestamp(),
  `gueltig_ab` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'aktiv',
  `geloescht` tinyint(1) DEFAULT 0,
  `bemerkung` text DEFAULT NULL,
  `archiviert_am` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vorgelaende`
--

CREATE TABLE `vorgelaende` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `artikel_id` int(11) DEFAULT NULL,
  `bootsliegeplatz` varchar(255) DEFAULT NULL,
  `platz_info` text DEFAULT NULL,
  `platz_foto` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `grid_slot` int(11) NOT NULL,
  `boot_name` varchar(255) DEFAULT NULL,
  `boot_typ` varchar(255) DEFAULT NULL,
  `boot_laenge` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `vorgelaende`
--

INSERT INTO `vorgelaende` (`id`, `kunde_id`, `artikel_id`, `bootsliegeplatz`, `platz_info`, `platz_foto`, `sort_order`, `grid_slot`, `boot_name`, `boot_typ`, `boot_laenge`) VALUES
(6, 1, 17, '1', '', NULL, 0, 121, 'luna', 'etc', '6 m');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vorgelaende_grid`
--

CREATE TABLE `vorgelaende_grid` (
  `slot` int(11) NOT NULL,
  `row_num` int(11) NOT NULL,
  `col_num` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `vorgelaende_grid`
--

INSERT INTO `vorgelaende_grid` (`slot`, `row_num`, `col_num`, `active`) VALUES
(1, 1, 1, 0),
(2, 1, 2, 0),
(3, 1, 3, 0),
(4, 1, 4, 0),
(5, 1, 5, 0),
(6, 1, 6, 0),
(7, 1, 7, 1),
(8, 1, 8, 1),
(9, 1, 9, 1),
(10, 1, 10, 1),
(11, 1, 11, 1),
(12, 1, 12, 1),
(13, 1, 13, 1),
(14, 1, 14, 1),
(15, 1, 15, 1),
(16, 1, 16, 1),
(17, 1, 17, 1),
(18, 1, 18, 1),
(19, 1, 19, 0),
(20, 1, 20, 0),
(21, 2, 1, 0),
(22, 2, 2, 0),
(23, 2, 3, 0),
(24, 2, 4, 0),
(25, 2, 5, 0),
(26, 2, 6, 0),
(27, 2, 7, 0),
(28, 2, 8, 0),
(29, 2, 9, 0),
(30, 2, 10, 0),
(31, 2, 11, 0),
(32, 2, 12, 0),
(33, 2, 13, 0),
(34, 2, 14, 0),
(35, 2, 15, 0),
(36, 2, 16, 0),
(37, 2, 17, 0),
(38, 2, 18, 0),
(39, 2, 19, 0),
(40, 2, 20, 0),
(41, 3, 1, 0),
(42, 3, 2, 0),
(43, 3, 3, 0),
(44, 3, 4, 0),
(45, 3, 5, 0),
(46, 3, 6, 0),
(47, 3, 7, 0),
(48, 3, 8, 0),
(49, 3, 9, 0),
(50, 3, 10, 0),
(51, 3, 11, 0),
(52, 3, 12, 0),
(53, 3, 13, 0),
(54, 3, 14, 0),
(55, 3, 15, 0),
(56, 3, 16, 0),
(57, 3, 17, 0),
(58, 3, 18, 0),
(59, 3, 19, 0),
(60, 3, 20, 1),
(61, 4, 1, 0),
(62, 4, 2, 0),
(63, 4, 3, 0),
(64, 4, 4, 0),
(65, 4, 5, 0),
(66, 4, 6, 0),
(67, 4, 7, 0),
(68, 4, 8, 0),
(69, 4, 9, 0),
(70, 4, 10, 0),
(71, 4, 11, 0),
(72, 4, 12, 0),
(73, 4, 13, 0),
(74, 4, 14, 0),
(75, 4, 15, 0),
(76, 4, 16, 0),
(77, 4, 17, 0),
(78, 4, 18, 0),
(79, 4, 19, 0),
(80, 4, 20, 1),
(81, 5, 1, 0),
(82, 5, 2, 0),
(83, 5, 3, 0),
(84, 5, 4, 0),
(85, 5, 5, 0),
(86, 5, 6, 0),
(87, 5, 7, 0),
(88, 5, 8, 1),
(89, 5, 9, 1),
(90, 5, 10, 0),
(91, 5, 11, 1),
(92, 5, 12, 1),
(93, 5, 13, 0),
(94, 5, 14, 0),
(95, 5, 15, 1),
(96, 5, 16, 0),
(97, 5, 17, 1),
(98, 5, 18, 1),
(99, 5, 19, 0),
(100, 5, 20, 1),
(101, 6, 1, 0),
(102, 6, 2, 0),
(103, 6, 3, 0),
(104, 6, 4, 0),
(105, 6, 5, 0),
(106, 6, 6, 0),
(107, 6, 7, 0),
(108, 6, 8, 1),
(109, 6, 9, 1),
(110, 6, 10, 0),
(111, 6, 11, 1),
(112, 6, 12, 1),
(113, 6, 13, 0),
(114, 6, 14, 0),
(115, 6, 15, 1),
(116, 6, 16, 0),
(117, 6, 17, 1),
(118, 6, 18, 1),
(119, 6, 19, 0),
(120, 6, 20, 1),
(121, 7, 1, 1),
(122, 7, 2, 0),
(123, 7, 3, 0),
(124, 7, 4, 1),
(125, 7, 5, 1),
(126, 7, 6, 0),
(127, 7, 7, 0),
(128, 7, 8, 1),
(129, 7, 9, 1),
(130, 7, 10, 0),
(131, 7, 11, 1),
(132, 7, 12, 1),
(133, 7, 13, 0),
(134, 7, 14, 0),
(135, 7, 15, 1),
(136, 7, 16, 0),
(137, 7, 17, 1),
(138, 7, 18, 1),
(139, 7, 19, 0),
(140, 7, 20, 1),
(141, 8, 1, 1),
(142, 8, 2, 0),
(143, 8, 3, 0),
(144, 8, 4, 1),
(145, 8, 5, 1),
(146, 8, 6, 0),
(147, 8, 7, 0),
(148, 8, 8, 1),
(149, 8, 9, 1),
(150, 8, 10, 0),
(151, 8, 11, 1),
(152, 8, 12, 1),
(153, 8, 13, 0),
(154, 8, 14, 0),
(155, 8, 15, 1),
(156, 8, 16, 0),
(157, 8, 17, 1),
(158, 8, 18, 1),
(159, 8, 19, 0),
(160, 8, 20, 1),
(161, 9, 1, 1),
(162, 9, 2, 0),
(163, 9, 3, 1),
(164, 9, 4, 1),
(165, 9, 5, 1),
(166, 9, 6, 0),
(167, 9, 7, 0),
(168, 9, 8, 1),
(169, 9, 9, 1),
(170, 9, 10, 0),
(171, 9, 11, 1),
(172, 9, 12, 1),
(173, 9, 13, 0),
(174, 9, 14, 0),
(175, 9, 15, 1),
(176, 9, 16, 0),
(177, 9, 17, 1),
(178, 9, 18, 1),
(179, 9, 19, 0),
(180, 9, 20, 0),
(181, 10, 1, 1),
(182, 10, 2, 0),
(183, 10, 3, 1),
(184, 10, 4, 1),
(185, 10, 5, 1),
(186, 10, 6, 0),
(187, 10, 7, 0),
(188, 10, 8, 1),
(189, 10, 9, 1),
(190, 10, 10, 0),
(191, 10, 11, 1),
(192, 10, 12, 1),
(193, 10, 13, 0),
(194, 10, 14, 0),
(195, 10, 15, 1),
(196, 10, 16, 0),
(197, 10, 17, 1),
(198, 10, 18, 1),
(199, 10, 19, 0),
(200, 10, 20, 0),
(201, 11, 1, 1),
(202, 11, 2, 0),
(203, 11, 3, 1),
(204, 11, 4, 1),
(205, 11, 5, 1),
(206, 11, 6, 0),
(207, 11, 7, 0),
(208, 11, 8, 1),
(209, 11, 9, 1),
(210, 11, 10, 0),
(211, 11, 11, 1),
(212, 11, 12, 1),
(213, 11, 13, 0),
(214, 11, 14, 0),
(215, 11, 15, 1),
(216, 11, 16, 0),
(217, 11, 17, 1),
(218, 11, 18, 1),
(219, 11, 19, 0),
(220, 11, 20, 0),
(221, 12, 1, 1),
(222, 12, 2, 0),
(223, 12, 3, 1),
(224, 12, 4, 1),
(225, 12, 5, 1),
(226, 12, 6, 0),
(227, 12, 7, 0),
(228, 12, 8, 1),
(229, 12, 9, 1),
(230, 12, 10, 0),
(231, 12, 11, 1),
(232, 12, 12, 1),
(233, 12, 13, 0),
(234, 12, 14, 0),
(235, 12, 15, 1),
(236, 12, 16, 0),
(237, 12, 17, 1),
(238, 12, 18, 1),
(239, 12, 19, 0),
(240, 12, 20, 0),
(241, 13, 1, 1),
(242, 13, 2, 0),
(243, 13, 3, 1),
(244, 13, 4, 1),
(245, 13, 5, 1),
(246, 13, 6, 0),
(247, 13, 7, 0),
(248, 13, 8, 0),
(249, 13, 9, 0),
(250, 13, 10, 0),
(251, 13, 11, 0),
(252, 13, 12, 0),
(253, 13, 13, 0),
(254, 13, 14, 0),
(255, 13, 15, 0),
(256, 13, 16, 0),
(257, 13, 17, 0),
(258, 13, 18, 0),
(259, 13, 19, 0),
(260, 13, 20, 0),
(261, 14, 1, 1),
(262, 14, 2, 0),
(263, 14, 3, 1),
(264, 14, 4, 1),
(265, 14, 5, 1),
(266, 14, 6, 0),
(267, 14, 7, 0),
(268, 14, 8, 0),
(269, 14, 9, 0),
(270, 14, 10, 0),
(271, 14, 11, 0),
(272, 14, 12, 0),
(273, 14, 13, 0),
(274, 14, 14, 0),
(275, 14, 15, 0),
(276, 14, 16, 0),
(277, 14, 17, 0),
(278, 14, 18, 0),
(279, 14, 19, 0),
(280, 14, 20, 0),
(281, 15, 1, 0),
(282, 15, 2, 0),
(283, 15, 3, 0),
(284, 15, 4, 0),
(285, 15, 5, 0),
(286, 15, 6, 0),
(287, 15, 7, 0),
(288, 15, 8, 0),
(289, 15, 9, 0),
(290, 15, 10, 0),
(291, 15, 11, 0),
(292, 15, 12, 0),
(293, 15, 13, 0),
(294, 15, 14, 0),
(295, 15, 15, 0),
(296, 15, 16, 0),
(297, 15, 17, 0),
(298, 15, 18, 0),
(299, 15, 19, 0),
(300, 15, 20, 0),
(301, 16, 1, 0),
(302, 16, 2, 0),
(303, 16, 3, 0),
(304, 16, 4, 0),
(305, 16, 5, 0),
(306, 16, 6, 0),
(307, 16, 7, 0),
(308, 16, 8, 0),
(309, 16, 9, 0),
(310, 16, 10, 0),
(311, 16, 11, 0),
(312, 16, 12, 0),
(313, 16, 13, 0),
(314, 16, 14, 0),
(315, 16, 15, 0),
(316, 16, 16, 0),
(317, 16, 17, 0),
(318, 16, 18, 0),
(319, 16, 19, 0),
(320, 16, 20, 0),
(321, 17, 1, 0),
(322, 17, 2, 0),
(323, 17, 3, 0),
(324, 17, 4, 0),
(325, 17, 5, 0),
(326, 17, 6, 0),
(327, 17, 7, 0),
(328, 17, 8, 0),
(329, 17, 9, 0),
(330, 17, 10, 0),
(331, 17, 11, 0),
(332, 17, 12, 0),
(333, 17, 13, 0),
(334, 17, 14, 0),
(335, 17, 15, 0),
(336, 17, 16, 0),
(337, 17, 17, 0),
(338, 17, 18, 0),
(339, 17, 19, 0),
(340, 17, 20, 0),
(341, 18, 1, 0),
(342, 18, 2, 0),
(343, 18, 3, 0),
(344, 18, 4, 0),
(345, 18, 5, 0),
(346, 18, 6, 0),
(347, 18, 7, 0),
(348, 18, 8, 0),
(349, 18, 9, 0),
(350, 18, 10, 0),
(351, 18, 11, 0),
(352, 18, 12, 0),
(353, 18, 13, 0),
(354, 18, 14, 0),
(355, 18, 15, 0),
(356, 18, 16, 0),
(357, 18, 17, 0),
(358, 18, 18, 0),
(359, 18, 19, 0),
(360, 18, 20, 0),
(361, 19, 1, 0),
(362, 19, 2, 0),
(363, 19, 3, 0),
(364, 19, 4, 0),
(365, 19, 5, 0),
(366, 19, 6, 0),
(367, 19, 7, 0),
(368, 19, 8, 0),
(369, 19, 9, 0),
(370, 19, 10, 0),
(371, 19, 11, 0),
(372, 19, 12, 0),
(373, 19, 13, 0),
(374, 19, 14, 0),
(375, 19, 15, 0),
(376, 19, 16, 0),
(377, 19, 17, 0),
(378, 19, 18, 0),
(379, 19, 19, 0),
(380, 19, 20, 0),
(381, 20, 1, 0),
(382, 20, 2, 0),
(383, 20, 3, 0),
(384, 20, 4, 0),
(385, 20, 5, 0),
(386, 20, 6, 0),
(387, 20, 7, 0),
(388, 20, 8, 0),
(389, 20, 9, 0),
(390, 20, 10, 0),
(391, 20, 11, 0),
(392, 20, 12, 0),
(393, 20, 13, 0),
(394, 20, 14, 0),
(395, 20, 15, 0),
(396, 20, 16, 0),
(397, 20, 17, 0),
(398, 20, 18, 0),
(399, 20, 19, 0),
(400, 20, 20, 0);

-- --------------------------------------------------------

--
-- Stellvertreter-Struktur des Views `vw_buchung_belegung`
-- (Siehe unten für die tatsächliche Ansicht)
--
CREATE TABLE `vw_buchung_belegung` (
`id` int(11)
,`platz` varchar(50)
,`start` datetime
,`ende` datetime
,`naechte` bigint(21)
,`anzahl_erwachsene` decimal(43,0)
,`anzahl_jugendliche` decimal(42,0)
,`anzahl_kinder` decimal(42,0)
,`anzahl_hunde` decimal(42,0)
,`anzahl_pkw` decimal(42,0)
);

-- --------------------------------------------------------

--
-- Struktur des Views `vw_buchung_belegung`
--
DROP TABLE IF EXISTS `vw_buchung_belegung`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_buchung_belegung`  AS SELECT `b`.`id` AS `id`, `b`.`platz` AS `platz`, `b`.`start` AS `start`, `b`.`ende` AS `ende`, `n`.`naechte` AS `naechte`, 2 * max(case when `a`.`id` in (28,29,30,38,39,40) then 1 else 0 end) + ifnull(sum(case when `a`.`id` in (31,41) then cast(`ba`.`menge` / `n`.`naechte` as unsigned) else 0 end),0) AS `anzahl_erwachsene`, ifnull(sum(case when `a`.`id` in (32,42) then cast(`ba`.`menge` / `n`.`naechte` as unsigned) else 0 end),0) AS `anzahl_jugendliche`, ifnull(sum(case when `a`.`id` in (33,43) then cast(`ba`.`menge` / `n`.`naechte` as unsigned) else 0 end),0) AS `anzahl_kinder`, ifnull(sum(case when `a`.`id` in (34,44) then cast(`ba`.`menge` / `n`.`naechte` as unsigned) else 0 end),0) AS `anzahl_hunde`, ifnull(sum(case when `a`.`id` in (35,45) then cast(`ba`.`menge` / `n`.`naechte` as unsigned) else 0 end),0) AS `anzahl_pkw` FROM (((`buchungen` `b` join (select `buchungen`.`id` AS `id`,greatest(1,timestampdiff(DAY,`buchungen`.`start`,`buchungen`.`ende`)) AS `naechte` from `buchungen`) `n` on(`n`.`id` = `b`.`id`)) left join `buchung_artikel` `ba` on(`ba`.`buchung_id` = `b`.`id`)) left join `artikel` `a` on(`a`.`id` = `ba`.`artikel_id`)) GROUP BY `b`.`id`, `b`.`platz`, `b`.`start`, `b`.`ende`, `n`.`naechte` ;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `sepamandatsreferenz` (`sepamandatsreferenz`),
  ADD KEY `fk_admins_rollen` (`rollen_id`);

--
-- Indizes für die Tabelle `admin_card_order`
--
ALTER TABLE `admin_card_order`
  ADD PRIMARY KEY (`admin_id`,`card_key`);

--
-- Indizes für die Tabelle `admin_column_order`
--
ALTER TABLE `admin_column_order`
  ADD PRIMARY KEY (`admin_id`,`page`);

--
-- Indizes für die Tabelle `admin_column_prefs`
--
ALTER TABLE `admin_column_prefs`
  ADD PRIMARY KEY (`admin_id`,`page`);

--
-- Indizes für die Tabelle `admin_rollen`
--
ALTER TABLE `admin_rollen`
  ADD PRIMARY KEY (`admin_id`,`rollen_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `rollen_id` (`rollen_id`);

--
-- Indizes für die Tabelle `admin_sportarten`
--
ALTER TABLE `admin_sportarten`
  ADD PRIMARY KEY (`admin_id`,`sportart_id`),
  ADD KEY `sportart_id` (`sportart_id`);

--
-- Indizes für die Tabelle `angebote`
--
ALTER TABLE `angebote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rechnung_id` (`rechnung_id`);

--
-- Indizes für die Tabelle `angebotpositionen`
--
ALTER TABLE `angebotpositionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rechnung_id` (`rechnung_id`);

--
-- Indizes für die Tabelle `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `artikelgruppen`
--
ALTER TABLE `artikelgruppen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `ausgaben`
--
ALTER TABLE `ausgaben`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `sportart_id` (`sportart_id`),
  ADD KEY `lehrgang_id` (`lehrgang_id`);

--
-- Indizes für die Tabelle `ausgaben_belege`
--
ALTER TABLE `ausgaben_belege`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ausgabe_id` (`ausgabe_id`);

--
-- Indizes für die Tabelle `buchungen`
--
ALTER TABLE `buchungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `buchung_artikel`
--
ALTER TABLE `buchung_artikel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `buchung_id` (`buchung_id`),
  ADD KEY `artikel_id` (`artikel_id`);

--
-- Indizes für die Tabelle `dauercamping`
--
ALTER TABLE `dauercamping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kunde_id` (`kunde_id`),
  ADD KEY `artikel_id` (`artikel_id`),
  ADD KEY `grid_slot` (`grid_slot`);

--
-- Indizes für die Tabelle `dauercamping_grid`
--
ALTER TABLE `dauercamping_grid`
  ADD PRIMARY KEY (`slot`);

--
-- Indizes für die Tabelle `fahrten`
--
ALTER TABLE `fahrten`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ausgabe_id` (`ausgabe_id`),
  ADD KEY `pauschale_id` (`pauschale_id`);

--
-- Indizes für die Tabelle `funktionen_lkv`
--
ALTER TABLE `funktionen_lkv`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `gutschriften`
--
ALTER TABLE `gutschriften`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rechnung_id` (`rechnung_id`),
  ADD KEY `fk_gutschriften_buchungen` (`buchungen_id`);

--
-- Indizes für die Tabelle `gutschriftpositionen`
--
ALTER TABLE `gutschriftpositionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rechnung_id` (`rechnung_id`);

--
-- Indizes für die Tabelle `kalender_calendars`
--
ALTER TABLE `kalender_calendars`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `kalender_categories`
--
ALTER TABLE `kalender_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kalender_categories_cal` (`kalender_id`);

--
-- Indizes für die Tabelle `kalender_events`
--
ALTER TABLE `kalender_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kalender_events_category` (`category_id`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Indizes für die Tabelle `kmpauschale`
--
ALTER TABLE `kmpauschale`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `kunden`
--
ALTER TABLE `kunden`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `kunden_extra`
--
ALTER TABLE `kunden_extra`
  ADD PRIMARY KEY (`kunde_id`);

--
-- Indizes für die Tabelle `kunden_funktionen`
--
ALTER TABLE `kunden_funktionen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_kunde_funktion_zeitraum` (`kunden_id`,`funktion_id`,`gueltig_von`,`gueltig_bis`),
  ADD KEY `funktion_id` (`funktion_id`);

--
-- Indizes für die Tabelle `kunden_jahresvertraege`
--
ALTER TABLE `kunden_jahresvertraege`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bootsliegeplatz` (`bootsliegeplatz`),
  ADD KEY `kunde_id` (`kunde_id`),
  ADD KEY `artikel_id` (`artikel_id`);

--
-- Indizes für die Tabelle `lehrgaenge`
--
ALTER TABLE `lehrgaenge`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lehrgaenge_sportart_id` (`sportart_id`);

--
-- Indizes für die Tabelle `lehrgang_auslagen`
--
ALTER TABLE `lehrgang_auslagen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lehrgang_id` (`lehrgang_id`);

--
-- Indizes für die Tabelle `lehrgang_dokumente`
--
ALTER TABLE `lehrgang_dokumente`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lehrgang_id` (`lehrgang_id`);

--
-- Indizes für die Tabelle `lehrgang_leitung`
--
ALTER TABLE `lehrgang_leitung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lehrgang_id` (`lehrgang_id`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `lehrgang_teilnahmen`
--
ALTER TABLE `lehrgang_teilnahmen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lehrgang_teilnahmen_ibfk_1` (`lehrgang_id`),
  ADD KEY `lehrgang_teilnahmen_ibfk_2` (`kunde_id`);

--
-- Indizes für die Tabelle `lehrgang_trainer`
--
ALTER TABLE `lehrgang_trainer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lehrgang_id` (`lehrgang_id`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `page_info`
--
ALTER TABLE `page_info`
  ADD PRIMARY KEY (`page`);

--
-- Indizes für die Tabelle `plaetze`
--
ALTER TABLE `plaetze`
  ADD PRIMARY KEY (`id`),
  ADD KEY `platzgruppe_id` (`platzgruppe_id`);

--
-- Indizes für die Tabelle `platzgruppen`
--
ALTER TABLE `platzgruppen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `preisliste`
--
ALTER TABLE `preisliste`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aid` (`aid`);

--
-- Indizes für die Tabelle `rechnungen`
--
ALTER TABLE `rechnungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `fk_rechnungen_buchungen_id` (`buchungen_id`);

--
-- Indizes für die Tabelle `rechnungskopftexte`
--
ALTER TABLE `rechnungskopftexte`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `rechnungspositionen`
--
ALTER TABLE `rechnungspositionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rechnung_id` (`rechnung_id`);

--
-- Indizes für die Tabelle `rollen`
--
ALTER TABLE `rollen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bezeichnung` (`bezeichnung`);

--
-- Indizes für die Tabelle `schuppen`
--
ALTER TABLE `schuppen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schuppen_artikel` (`artikel_id`),
  ADD KEY `schuppen_kunde` (`kunde_id`),
  ADD KEY `grid_slot` (`grid_slot`),
  ADD KEY `idx_grid_slot` (`grid_slot`);

--
-- Indizes für die Tabelle `schuppen_grid`
--
ALTER TABLE `schuppen_grid`
  ADD PRIMARY KEY (`slot`);

--
-- Indizes für die Tabelle `seiten_info`
--
ALTER TABLE `seiten_info`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `sportarten`
--
ALTER TABLE `sportarten`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bezeichnung` (`bezeichnung`);

--
-- Indizes für die Tabelle `stegbelegung`
--
ALTER TABLE `stegbelegung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kunde_id` (`kunde_id`),
  ADD KEY `artikel_id` (`artikel_id`),
  ADD KEY `sort_order` (`sort_order`),
  ADD KEY `seite` (`seite`);

--
-- Indizes für die Tabelle `stromzaehler_dauercamping_jahresverbrauch`
--
ALTER TABLE `stromzaehler_dauercamping_jahresverbrauch`
  ADD PRIMARY KEY (`zaehlernummer`,`jahr`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `stromzaehler_mardorf`
--
ALTER TABLE `stromzaehler_mardorf`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `stromzaehler_mardorf_jahresverbrauch`
--
ALTER TABLE `stromzaehler_mardorf_jahresverbrauch`
  ADD PRIMARY KEY (`zaehlernummer`,`jahr`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `stromzaehler_parent_mardorf`
--
ALTER TABLE `stromzaehler_parent_mardorf`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `stromzaehler_parent_mardorf_jahresverbrauch`
--
ALTER TABLE `stromzaehler_parent_mardorf_jahresverbrauch`
  ADD PRIMARY KEY (`zaehler_id`,`jahr`),
  ADD KEY `zaehler_id` (`zaehler_id`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `stromzaehler_parent_mardorf_werte`
--
ALTER TABLE `stromzaehler_parent_mardorf_werte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zaehler_id` (`zaehler_id`);

--
-- Indizes für die Tabelle `strom_dauercamping`
--
ALTER TABLE `strom_dauercamping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kunde_id` (`kunde_id`),
  ADD KEY `artikel_id` (`artikel_id`);

--
-- Indizes für die Tabelle `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_week` (`admin_id`,`week_start`),
  ADD KEY `idx_admin_date` (`admin_id`,`todo_date`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `fk_todos_created_by_admin` (`created_by_admin`),
  ADD KEY `fk_todos_in_prog_admin` (`in_progress_by_admin`),
  ADD KEY `fk_todos_completed_by` (`completed_by_admin`);

--
-- Indizes für die Tabelle `vertraege`
--
ALTER TABLE `vertraege`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vertragsnummer` (`vertragsnummer`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `vorgelaende`
--
ALTER TABLE `vorgelaende`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vorgelaende_artikel` (`artikel_id`),
  ADD KEY `vorgelaende_kunde` (`kunde_id`),
  ADD KEY `grid_slot` (`grid_slot`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT für Tabelle `angebote`
--
ALTER TABLE `angebote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT für Tabelle `angebotpositionen`
--
ALTER TABLE `angebotpositionen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT für Tabelle `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT für Tabelle `artikelgruppen`
--
ALTER TABLE `artikelgruppen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT für Tabelle `ausgaben`
--
ALTER TABLE `ausgaben`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT für Tabelle `ausgaben_belege`
--
ALTER TABLE `ausgaben_belege`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT für Tabelle `buchungen`
--
ALTER TABLE `buchungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT für Tabelle `buchung_artikel`
--
ALTER TABLE `buchung_artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT für Tabelle `dauercamping`
--
ALTER TABLE `dauercamping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT für Tabelle `fahrten`
--
ALTER TABLE `fahrten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT für Tabelle `funktionen_lkv`
--
ALTER TABLE `funktionen_lkv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `gutschriften`
--
ALTER TABLE `gutschriften`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT für Tabelle `gutschriftpositionen`
--
ALTER TABLE `gutschriftpositionen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT für Tabelle `kalender_calendars`
--
ALTER TABLE `kalender_calendars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT für Tabelle `kalender_categories`
--
ALTER TABLE `kalender_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT für Tabelle `kalender_events`
--
ALTER TABLE `kalender_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=900;

--
-- AUTO_INCREMENT für Tabelle `kmpauschale`
--
ALTER TABLE `kmpauschale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `kunden`
--
ALTER TABLE `kunden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT für Tabelle `kunden_funktionen`
--
ALTER TABLE `kunden_funktionen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kunden_jahresvertraege`
--
ALTER TABLE `kunden_jahresvertraege`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=730;

--
-- AUTO_INCREMENT für Tabelle `lehrgaenge`
--
ALTER TABLE `lehrgaenge`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT für Tabelle `lehrgang_auslagen`
--
ALTER TABLE `lehrgang_auslagen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT für Tabelle `lehrgang_dokumente`
--
ALTER TABLE `lehrgang_dokumente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT für Tabelle `lehrgang_leitung`
--
ALTER TABLE `lehrgang_leitung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT für Tabelle `lehrgang_teilnahmen`
--
ALTER TABLE `lehrgang_teilnahmen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1145;

--
-- AUTO_INCREMENT für Tabelle `lehrgang_trainer`
--
ALTER TABLE `lehrgang_trainer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=459;

--
-- AUTO_INCREMENT für Tabelle `plaetze`
--
ALTER TABLE `plaetze`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `platzgruppen`
--
ALTER TABLE `platzgruppen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `preisliste`
--
ALTER TABLE `preisliste`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT für Tabelle `rechnungen`
--
ALTER TABLE `rechnungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT für Tabelle `rechnungskopftexte`
--
ALTER TABLE `rechnungskopftexte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `rechnungspositionen`
--
ALTER TABLE `rechnungspositionen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT für Tabelle `rollen`
--
ALTER TABLE `rollen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT für Tabelle `schuppen`
--
ALTER TABLE `schuppen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `seiten_info`
--
ALTER TABLE `seiten_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT für Tabelle `sportarten`
--
ALTER TABLE `sportarten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT für Tabelle `stegbelegung`
--
ALTER TABLE `stegbelegung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=288;

--
-- AUTO_INCREMENT für Tabelle `stromzaehler_mardorf`
--
ALTER TABLE `stromzaehler_mardorf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT für Tabelle `stromzaehler_parent_mardorf`
--
ALTER TABLE `stromzaehler_parent_mardorf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT für Tabelle `stromzaehler_parent_mardorf_werte`
--
ALTER TABLE `stromzaehler_parent_mardorf_werte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT für Tabelle `strom_dauercamping`
--
ALTER TABLE `strom_dauercamping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT für Tabelle `vertraege`
--
ALTER TABLE `vertraege`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `vorgelaende`
--
ALTER TABLE `vorgelaende`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admins_rollen` FOREIGN KEY (`rollen_id`) REFERENCES `rollen` (`id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `admin_rollen`
--
ALTER TABLE `admin_rollen`
  ADD CONSTRAINT `admin_rollen_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_rollen_ibfk_2` FOREIGN KEY (`rollen_id`) REFERENCES `rollen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_admin_rollen_admins` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_admin_rollen_rollen` FOREIGN KEY (`rollen_id`) REFERENCES `rollen` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `admin_sportarten`
--
ALTER TABLE `admin_sportarten`
  ADD CONSTRAINT `admin_sportarten_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_sportarten_sportart` FOREIGN KEY (`sportart_id`) REFERENCES `sportarten` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `angebote`
--
ALTER TABLE `angebote`
  ADD CONSTRAINT `angebote_ibfk_1` FOREIGN KEY (`rechnung_id`) REFERENCES `rechnungen` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `angebotpositionen`
--
ALTER TABLE `angebotpositionen`
  ADD CONSTRAINT `angebotpositionen_ibfk_1` FOREIGN KEY (`rechnung_id`) REFERENCES `angebote` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `ausgaben`
--
ALTER TABLE `ausgaben`
  ADD CONSTRAINT `ausgaben_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `fk_ausgaben_lehrgang` FOREIGN KEY (`lehrgang_id`) REFERENCES `lehrgaenge` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ausgaben_sportart` FOREIGN KEY (`sportart_id`) REFERENCES `sportarten` (`id`);

--
-- Constraints der Tabelle `ausgaben_belege`
--
ALTER TABLE `ausgaben_belege`
  ADD CONSTRAINT `ausgaben_belege_ibfk_1` FOREIGN KEY (`ausgabe_id`) REFERENCES `ausgaben` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `buchungen`
--
ALTER TABLE `buchungen`
  ADD CONSTRAINT `fk_buchungen_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `buchung_artikel`
--
ALTER TABLE `buchung_artikel`
  ADD CONSTRAINT `fk_buchung_artikel_buchung` FOREIGN KEY (`buchung_id`) REFERENCES `buchungen` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `dauercamping`
--
ALTER TABLE `dauercamping`
  ADD CONSTRAINT `dauercamping_artikel` FOREIGN KEY (`artikel_id`) REFERENCES `artikel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dauercamping_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `fahrten`
--
ALTER TABLE `fahrten`
  ADD CONSTRAINT `fahrten_ibfk_1` FOREIGN KEY (`ausgabe_id`) REFERENCES `ausgaben` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fahrten_ibfk_2` FOREIGN KEY (`pauschale_id`) REFERENCES `kmpauschale` (`id`);

--
-- Constraints der Tabelle `gutschriften`
--
ALTER TABLE `gutschriften`
  ADD CONSTRAINT `fk_gutschriften_buchungen` FOREIGN KEY (`buchungen_id`) REFERENCES `buchungen` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `gutschriften_ibfk_1` FOREIGN KEY (`rechnung_id`) REFERENCES `rechnungen` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `gutschriftpositionen`
--
ALTER TABLE `gutschriftpositionen`
  ADD CONSTRAINT `gutschriftpositionen_ibfk_1` FOREIGN KEY (`rechnung_id`) REFERENCES `gutschriften` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `kalender_categories`
--
ALTER TABLE `kalender_categories`
  ADD CONSTRAINT `fk_kalender_categories_cal` FOREIGN KEY (`kalender_id`) REFERENCES `kalender_calendars` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `kalender_events`
--
ALTER TABLE `kalender_events`
  ADD CONSTRAINT `fk_kalender_events_category` FOREIGN KEY (`category_id`) REFERENCES `kalender_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `kunden_extra`
--
ALTER TABLE `kunden_extra`
  ADD CONSTRAINT `kunden_extra_ibfk_1` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `kunden_funktionen`
--
ALTER TABLE `kunden_funktionen`
  ADD CONSTRAINT `kunden_funktionen_ibfk_1` FOREIGN KEY (`kunden_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kunden_funktionen_ibfk_2` FOREIGN KEY (`funktion_id`) REFERENCES `funktionen_lkv` (`id`);

--
-- Constraints der Tabelle `kunden_jahresvertraege`
--
ALTER TABLE `kunden_jahresvertraege`
  ADD CONSTRAINT `kjv_artikel` FOREIGN KEY (`artikel_id`) REFERENCES `artikel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kjv_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `lehrgaenge`
--
ALTER TABLE `lehrgaenge`
  ADD CONSTRAINT `fk_lehrgaenge_sportart_id` FOREIGN KEY (`sportart_id`) REFERENCES `sportarten` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `lehrgang_auslagen`
--
ALTER TABLE `lehrgang_auslagen`
  ADD CONSTRAINT `lehrgang_auslagen_ibfk_1` FOREIGN KEY (`lehrgang_id`) REFERENCES `lehrgaenge` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `lehrgang_dokumente`
--
ALTER TABLE `lehrgang_dokumente`
  ADD CONSTRAINT `lehrgang_dokumente_ibfk_1` FOREIGN KEY (`lehrgang_id`) REFERENCES `lehrgaenge` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `lehrgang_leitung`
--
ALTER TABLE `lehrgang_leitung`
  ADD CONSTRAINT `lehrgang_leitung_ibfk_1` FOREIGN KEY (`lehrgang_id`) REFERENCES `lehrgaenge` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lehrgang_leitung_ibfk_2` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `lehrgang_teilnahmen`
--
ALTER TABLE `lehrgang_teilnahmen`
  ADD CONSTRAINT `lehrgang_teilnahmen_ibfk_1` FOREIGN KEY (`lehrgang_id`) REFERENCES `lehrgaenge` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lehrgang_teilnahmen_ibfk_2` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `lehrgang_trainer`
--
ALTER TABLE `lehrgang_trainer`
  ADD CONSTRAINT `lehrgang_trainer_ibfk_1` FOREIGN KEY (`lehrgang_id`) REFERENCES `lehrgaenge` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lehrgang_trainer_ibfk_2` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `plaetze`
--
ALTER TABLE `plaetze`
  ADD CONSTRAINT `plaetze_ibfk_1` FOREIGN KEY (`platzgruppe_id`) REFERENCES `platzgruppen` (`id`);

--
-- Constraints der Tabelle `preisliste`
--
ALTER TABLE `preisliste`
  ADD CONSTRAINT `fk_preisliste_artikel` FOREIGN KEY (`aid`) REFERENCES `artikel` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `rechnungen`
--
ALTER TABLE `rechnungen`
  ADD CONSTRAINT `fk_rechnungen_parent` FOREIGN KEY (`parent_id`) REFERENCES `rechnungen` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `rechnungspositionen`
--
ALTER TABLE `rechnungspositionen`
  ADD CONSTRAINT `rechnungspositionen_ibfk_1` FOREIGN KEY (`rechnung_id`) REFERENCES `rechnungen` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `schuppen`
--
ALTER TABLE `schuppen`
  ADD CONSTRAINT `schuppen_artikel` FOREIGN KEY (`artikel_id`) REFERENCES `artikel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schuppen_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `stegbelegung`
--
ALTER TABLE `stegbelegung`
  ADD CONSTRAINT `stegbelegung_artikel` FOREIGN KEY (`artikel_id`) REFERENCES `artikel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stegbelegung_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `stromzaehler_parent_mardorf_jahresverbrauch`
--
ALTER TABLE `stromzaehler_parent_mardorf_jahresverbrauch`
  ADD CONSTRAINT `fk_spm_jv_parent` FOREIGN KEY (`zaehler_id`) REFERENCES `stromzaehler_parent_mardorf` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `stromzaehler_parent_mardorf_werte`
--
ALTER TABLE `stromzaehler_parent_mardorf_werte`
  ADD CONSTRAINT `fk_spm_zaehler` FOREIGN KEY (`zaehler_id`) REFERENCES `stromzaehler_parent_mardorf` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `strom_dauercamping`
--
ALTER TABLE `strom_dauercamping`
  ADD CONSTRAINT `strom_dauercamping_artikel` FOREIGN KEY (`artikel_id`) REFERENCES `artikel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `strom_dauercamping_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `todos`
--
ALTER TABLE `todos`
  ADD CONSTRAINT `fk_todos_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `fk_todos_completed_by` FOREIGN KEY (`completed_by_admin`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_todos_created_by_admin` FOREIGN KEY (`created_by_admin`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_todos_in_prog_admin` FOREIGN KEY (`in_progress_by_admin`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `vertraege`
--
ALTER TABLE `vertraege`
  ADD CONSTRAINT `vertraege_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE SET NULL;

--
-- Constraints der Tabelle `vorgelaende`
--
ALTER TABLE `vorgelaende`
  ADD CONSTRAINT `vorgelaende_artikel` FOREIGN KEY (`artikel_id`) REFERENCES `artikel` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vorgelaende_kunde` FOREIGN KEY (`kunde_id`) REFERENCES `kunden` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
