
<?php
session_name('admin_session');
session_start();

$allowedRoles = ['Administrator','Geschäftsstellenmitarbeiter','Präsidium','Superadmin'];
$userRoles = $_SESSION['rollen_namen'] ?? [];
if (!is_array($userRoles)) {
    $userRoles = [$userRoles];
}
if (!isset($_SESSION['admin_logged_in']) || !array_intersect($allowedRoles, $userRoles)) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/csrf.php';
csrf_token();

if (isset($_GET['buchung_id']) && ctype_digit($_GET['buchung_id'])) {
    $buchung_id = (int)$_GET['buchung_id'];

    // Prüfen, ob bereits eine Rechnung existiert
    $stmt = $pdo->prepare('SELECT id FROM rechnungen WHERE buchungen_id = ? LIMIT 1');
    $stmt->execute([$buchung_id]);
    if ($existingId = $stmt->fetchColumn()) {
        header('Location: rechnungen.php?edit=' . $existingId);
        exit;
    }

    $_SESSION['invoice_buchung_id'] = $buchung_id;

    $stmt = $pdo->prepare('
        SELECT * FROM buchungen WHERE id = ?
    ');
    $stmt->execute([$buchung_id]);
    $buchung = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($buchung) {
        $kunde = null;
        $createdNewCustomer = false;
        if (!empty($buchung['kunde_id'])) {
            $stmt = $pdo->prepare('SELECT * FROM kunden WHERE id = ?');
            $stmt->execute([$buchung['kunde_id']]);
            $kunde = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif (!empty($buchung['email'])) {
            $stmt = $pdo->prepare('SELECT * FROM kunden WHERE email = ?');
            $stmt->execute([$buchung['email']]);
            if ($kunde = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (strcasecmp($kunde['vorname'] ?? '', $buchung['vorname']) === 0 && strcasecmp($kunde['nachname'] ?? '', $buchung['nachname']) === 0) {
                    $pdo->prepare('UPDATE buchungen SET kunde_id=? WHERE id=?')->execute([$kunde['id'], $buchung_id]);
                    $buchung['kunde_id'] = $kunde['id'];
                } else {
                    $sepaZust = ($buchung['zahlungsart'] === 'sepa' || strtolower($buchung['zustimmung_sepa'] ?? '') === 'ja') ? 1 : 0;
                    $mandatsRef = null;
                    $ins = $pdo->prepare('INSERT INTO kunden (email, vorname, nachname, strasse, hausnummer, plz, ort, sepa_zustimmung, kreditinstitut, kontoinhaber, iban, bic, sepamandatsreferenz) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                    $ins->execute([
                        $buchung['email'],
                        $buchung['vorname'],
                        $buchung['nachname'],
                        $buchung['strasse'],
                        $buchung['hausnummer'],
                        $buchung['plz'],
                        $buchung['ort'],
                        $sepaZust,
                        $buchung['kreditinstitut'],
                        $buchung['kontoinhaber'],
                        $buchung['iban'],
                        $buchung['bic'],
                        $mandatsRef
                    ]);
                    $kundeId = $pdo->lastInsertId();
                    $pdo->prepare('UPDATE buchungen SET kunde_id=? WHERE id=?')->execute([$kundeId, $buchung_id]);
                    $buchung['kunde_id'] = $kundeId;
                    $kunde = [
                        'id' => $kundeId,
                        'sepa_zustimmung' => $sepaZust,
                        'sepamandatsreferenz' => $mandatsRef
                    ];
                    $createdNewCustomer = true;
                }
            } else {
                $sepaZust = ($buchung['zahlungsart'] === 'sepa' || strtolower($buchung['zustimmung_sepa'] ?? '') === 'ja') ? 1 : 0;
                $mandatsRef = null;
                $ins = $pdo->prepare('INSERT INTO kunden (email, vorname, nachname, strasse, hausnummer, plz, ort, sepa_zustimmung, kreditinstitut, kontoinhaber, iban, bic, sepamandatsreferenz) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $ins->execute([
                    $buchung['email'],
                    $buchung['vorname'],
                    $buchung['nachname'],
                    $buchung['strasse'],
                    $buchung['hausnummer'],
                    $buchung['plz'],
                    $buchung['ort'],
                    $sepaZust,
                    $buchung['kreditinstitut'],
                    $buchung['kontoinhaber'],
                    $buchung['iban'],
                    $buchung['bic'],
                    $mandatsRef
                ]);
                $kundeId = $pdo->lastInsertId();
                $pdo->prepare('UPDATE buchungen SET kunde_id=? WHERE id=?')->execute([$kundeId, $buchung_id]);
                $buchung['kunde_id'] = $kundeId;
                $kunde = [
                    'id' => $kundeId,
                    'sepa_zustimmung' => $sepaZust,
                    'sepamandatsreferenz' => $mandatsRef
                ];
                $createdNewCustomer = true;
            }
        }

        $sepaFlag = ($buchung['zahlungsart'] === 'sepa' || strtolower($buchung['zustimmung_sepa'] ?? '') === 'ja' || ($kunde['sepa_zustimmung'] ?? 0) == 1) ? 1 : 0;
        $sepaRef = $kunde['sepamandatsreferenz'] ?? '';

        $_SESSION['invoice_data'] = [
            'empfaenger'   => trim($buchung['vorname'] . ' ' . $buchung['nachname']),
            'empfaenger_id'=> (int)($kunde['id'] ?? 0),
            'datum'        => date('Y-m-d'),
            'bezahldatum'  => date('Y-m-d', strtotime('+14 days')),
            'status'       => 'angelegt',
            'kopftext'     => sprintf(
                'Rechnung zur Buchung %s vom %s-%s',
                $buchung['platz'],
                date('d.m.y', strtotime($buchung['start'])),
                date('d.m.y', strtotime($buchung['ende']))
            ),
            'fusstext'     => '',
            'sepa'         => $sepaFlag,
            'sepa_ref'     => $sepaRef
        ];

        $_SESSION['invoice_positions'] = [];

        $kundenName = trim($buchung['vorname'] . ' ' . $buchung['nachname']);
        if ($createdNewCustomer) {
            $_SESSION['invoice_message'] = 'Rechnung für Buchung wird erstellt. Kunde ' . $kundenName . ' wurde automatisch angelegt.';
        } else {
            $_SESSION['invoice_message'] = 'Rechnung wird für Buchung des Kunden ' . $kundenName . ' erstellt.';
        }

        // Hauptartikel (Standplatz)
        $platzArtId = getStandplatzArtikelId(
            $buchung['fahrzeuglaenge'] ?? '',
            $buchung['dkv_mitglied'] ?? 'nein'
        );
        if ($platzArtId) {
            $preis = getPreis($platzArtId, $pdo);
            $_SESSION['invoice_positions'][] = [
                'artikelnummer' => getArtikelnummer($platzArtId, $pdo),
                'kurzbez' => getKurzbez($platzArtId, $pdo),
                'langbez' => getLangbez($platzArtId, $pdo),
                'einzelpreis' => $preis,
                'menge' => max(1, getNaechte($buchung['start'], $buchung['ende'])),
                'rabatt' => 0,
                'mwst' => getMwst($platzArtId, $pdo)
            ];
        }

        // Zusatzartikel (ohne Hauptartikel)
        $sqlArt = 'SELECT a.id, a.aid, a.kurzbez, a.langbez, a.mwst, ba.menge
                   FROM buchung_artikel ba
                   JOIN artikel a ON a.id = ba.artikel_id
                   WHERE ba.buchung_id = ?';
        $params = [$buchung_id];
        if ($platzArtId) {
            $sqlArt .= ' AND a.id <> ?';
            $params[] = $platzArtId;
        }
        $stmtArt = $pdo->prepare($sqlArt);
        $stmtArt->execute($params);
        foreach ($stmtArt->fetchAll(PDO::FETCH_ASSOC) as $art) {
            if ((int)$art['menge'] < 1) continue;
            $preis = getPreis($art['id'], $pdo);
            $_SESSION['invoice_positions'][] = [
                'artikelnummer' => $art['aid'],
                'kurzbez' => $art['kurzbez'],
                'langbez' => $art['langbez'],
                'einzelpreis' => $preis,
                'menge' => $art['menge'],
                'rabatt' => 0,
                'mwst' => $art['mwst']
            ];
        }

        header('Location: rechnungen.php'); // Weiter zur Bearbeitungsmaske
        exit;
    }
}

function getPreis(int $artikel_id, PDO $pdo): float {
    $stmt = $pdo->prepare('SELECT preis FROM preisliste WHERE aid = ? AND preis_ab <= CURDATE() ORDER BY preis_ab DESC LIMIT 1');
    $stmt->execute([$artikel_id]);
    return (float)$stmt->fetchColumn();
}

function getKurzbez(int $artikel_id, PDO $pdo): string {
    $stmt = $pdo->prepare('SELECT kurzbez FROM artikel WHERE id = ?');
    $stmt->execute([$artikel_id]);
    return $stmt->fetchColumn() ?: '';
}

function getLangbez(int $artikel_id, PDO $pdo): string {
    $stmt = $pdo->prepare('SELECT langbez FROM artikel WHERE id = ?');
    $stmt->execute([$artikel_id]);
    return $stmt->fetchColumn() ?: '';
}

function getArtikelnummer(int $artikel_id, PDO $pdo): string {
    $stmt = $pdo->prepare('SELECT aid FROM artikel WHERE id = ?');
    $stmt->execute([$artikel_id]);
    return $stmt->fetchColumn() ?: '';
}

function getMwst(int $artikel_id, PDO $pdo): float {
    $stmt = $pdo->prepare('SELECT mwst FROM artikel WHERE id = ?');
    $stmt->execute([$artikel_id]);
    return (float)$stmt->fetchColumn();
}

function getNaechte(string $start, string $ende): int {
    $startDate = new DateTime($start);
    $endDate = new DateTime($ende);
    return max(1, $startDate->diff($endDate)->days);
}

function getStandplatzArtikelId(string $fahrzeuglaenge, string $dkvMitglied): ?int {
    $dkv = strtolower($dkvMitglied) === 'ja';
    if ($fahrzeuglaenge === 'ueber7m') return $dkv ? 40 : 30;
    if ($fahrzeuglaenge === 'unter7m') return $dkv ? 39 : 29;
    return $dkv ? 38 : 28; // Zelt
}

function buildRateFootText(string $invoiceDate, array $schedules): string {
    $parts = [];
    foreach ($schedules as $s) {
        if (!empty($s['months'])) {
            $months = array_map('intval', (array)$s['months']);
            sort($months);
            $anzahl = count($months);
            $gesamt = (float)($s['preis'] ?? 0);
            $rate   = $anzahl > 0 ? $gesamt / $anzahl : 0;
            $invYear  = (int)date('Y', strtotime($invoiceDate));
            $invMonth = (int)date('n', strtotime($invoiceDate));
            $lines = [];
            foreach ($months as $i => $m) {
                $year = $m >= $invMonth ? $invYear : $invYear + 1;
                $due = new DateTime(sprintf('%04d-%02d-01', $year, $m));
                $lines[] = ($i + 1) . '. Rate ' . $due->format('d.m.Y') . ' ' . number_format($rate, 2, ',', '.') . ' €';
            }
        } else {
            $anzahl = max(1, (int)($s['anzahl'] ?? 1));
            $gesamt = (float)($s['preis'] ?? 0);
            $rate  = $anzahl > 0 ? $gesamt / $anzahl : 0;
            $startDate = $s['start'] ?: $invoiceDate;
            $endDate   = $s['end'] ?? null;

            $start = new DateTime($startDate);
            if ($endDate) {
                $end = new DateTime($endDate);
            } else {
                $end = clone $start;
                $startMonth = (int)$start->format('n');
                $monthsLeft = $startMonth > 3 ? 12 - ($startMonth - 1) : 12;
                $end->modify('+' . ($monthsLeft - 1) . ' month');
            }

            $diff = $start->diff($end);
            $months = $diff->y * 12 + $diff->m + ($diff->d > 0 ? 1 : 0);
            $stepMonths = $anzahl > 1 ? intdiv($months, ($anzahl - 1)) : 0;
            if ($stepMonths < 1) $stepMonths = 1;

            $lines = [];
            for ($i = 0; $i < $anzahl; $i++) {
                if ($i === $anzahl - 1) {
                    $due = clone $end;
                } else {
                    $due = (clone $start)->modify('+' . ($stepMonths * $i) . ' month');
                    if ($due > $end) $due = clone $end;
                }
                $lines[] = ($i + 1) . '. Rate ' . $due->format('d.m.Y') . ' ' . number_format($rate, 2, ',', '.') . ' €';
            }
        }
        $artBez = isset($s['artikel']) ? trim($s['artikel']) : '';
        $preisBez = '';
        if (!empty($s['preis'])) {
            $preisBez = number_format((float)$s['preis'], 2, ',', '.') . ' €';
        }
        $base = trim($artBez . ($preisBez !== '' ? ' ' . $preisBez : ''));

        $parts[] = 'Fälligkeiten Ratenzahlung "' . $base . '"' . "\n" .
            'Folgende Raten werden von oben genannten Konto eingezogen "' . $base . '": ' .
            implode(', ', $lines);
    }
    return implode("\n", $parts);
}

function refreshRateFootText(): void {
    $schedules = [];
    foreach ($_SESSION['invoice_positions'] as $p) {
        if (!empty($p['ratenzahlung']) && isset($p['raten_preis'])) {
            if (!empty($p['raten_monate'])) {
                $schedules[] = [
                    'months'  => $p['raten_monate'],
                    'preis'   => $p['raten_preis'],
                    'artikel' => $p['kurzbez'] ?? ''
                ];
            } elseif (!empty($p['raten_anzahl'])) {
                $schedules[] = [
                    'anzahl'  => $p['raten_anzahl'],
                    'preis'   => $p['raten_preis'],
                    'start'   => $p['raten_start'] ?? ($_SESSION['invoice_data']['datum'] ?? date('Y-m-d')),
                    'end'     => $p['raten_end'] ?? null,
                    'artikel' => $p['kurzbez'] ?? ''
                ];
            }
        }
    }
    $_SESSION['rate_schedules'] = $schedules;
    $_SESSION['has_rates'] = $schedules ? 1 : ($_SESSION['has_rates'] ?? 0);
    if ($schedules) {
        $_SESSION['invoice_data']['fusstext'] = buildRateFootText($_SESSION['invoice_data']['datum'] ?? date('Y-m-d'), $schedules);
    }
}

function nextInvoiceNumber(PDO $pdo): string {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(rechnungsnummer, 3) AS UNSIGNED)) FROM rechnungen WHERE rechnungsnummer LIKE 'RE%' AND rechnungsnummer IS NOT NULL");
    $max = (int)$stmt->fetchColumn();
    return 'RE' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
}

function nextOfferNumber(PDO $pdo): string {
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(angebotsnummer, 3) AS UNSIGNED)) FROM angebote WHERE angebotsnummer LIKE 'AN%' AND angebotsnummer IS NOT NULL");
    $max = (int)$stmt->fetchColumn();
    return 'AN' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
}

/**
 * Liefert die Brutto-Gesamtsumme einer Rechnung. Falls diese nicht bereits
 * vorab berechnet wurde (oder 0 ist), werden die Positionen der Rechnung
 * direkt summiert und in den Cache ($cache) geschrieben.
 */
function invoiceTotalBrutto(PDO $pdo, array &$cache, int $invoiceId): float {
    if (!isset($cache[$invoiceId]) || ($cache[$invoiceId]['brutto'] ?? 0) == 0) {
        $stmt = $pdo->prepare('SELECT SUM(einzelpreis * menge - einzelpreis * menge * rabatt / 100) FROM rechnungspositionen WHERE rechnung_id = ?');
        $stmt->execute([$invoiceId]);
        $brutto = (float)$stmt->fetchColumn();
        if (!isset($cache[$invoiceId])) {
            $cache[$invoiceId] = ['brutto' => $brutto, 'netto' => $brutto];
        } else {
            $cache[$invoiceId]['brutto'] = $brutto;
            if (!isset($cache[$invoiceId]['netto']) || $cache[$invoiceId]['netto'] == 0) {
                $cache[$invoiceId]['netto'] = $brutto;
            }
        }
        return $brutto;
    }
    return (float)$cache[$invoiceId]['brutto'];
}
$version = file_exists(__DIR__ . '/vers.txt') ? trim(file_get_contents(__DIR__ . '/vers.txt')) : 'v?.?';
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_name = 'Unbekannt';
if ($admin_id) {
    $stmt = $pdo->prepare('SELECT vorname, nachname FROM admins WHERE id = ?');
    $stmt->execute([$admin_id]);
    if ($row = $stmt->fetch()) {
        $admin_name = trim($row['vorname'] . ' ' . $row['nachname']);
    }
}
$rolle_name = $_SESSION['rolle_name'] ?? '';
$isSuperAdmin = in_array('Superadmin', $userRoles);
$canEdit = $isSuperAdmin || in_array('Geschäftsstellenmitarbeiter', $userRoles);

// gespeicherte Filtereinstellungen der Session abrufen
$filterState = $_SESSION['invoice_filter_state'] ?? [];
$filterStateLoaded = isset($filterState['showCorrections']);
if ($admin_id && (!isset($filterState['columns']) || !is_array($filterState['columns']))) {
    $stmt = $pdo->prepare('SELECT visible_columns FROM admin_column_prefs WHERE admin_id = ? AND page = ?');
    $stmt->execute([$admin_id, 'rechnungen']);
    $cols = $stmt->fetchColumn();
    if ($cols) {
        $tmp = json_decode($cols, true);
        if (is_array($tmp)) {
            if (isset($tmp['v'])) {
                $filterState['version'] = (int)$tmp['v'];
                $filterState['columns'] = isset($tmp['columns']) && is_array($tmp['columns'])
                    ? array_map('intval', $tmp['columns']) : [];
                if (isset($tmp['order']) && is_array($tmp['order'])) {
                    $filterState['order'] = array_map('intval', $tmp['order']);
                }
                if (isset($tmp['length'])) {
                    $filterState['length'] = (int)$tmp['length'];
                }
            } else {
                $filterState['version'] = 1;
                $filterState['columns'] = array_map('intval', $tmp);
            }
        }
        $_SESSION['invoice_filter_state'] = $filterState;
    }
}
if ($admin_id && !$filterStateLoaded) {
    $stmt = $pdo->prepare('SELECT visible_columns FROM admin_column_prefs WHERE admin_id = ? AND page = ?');
    $stmt->execute([$admin_id, 'rechnungen_filter']);
    if ($json = $stmt->fetchColumn()) {
        $tmp = json_decode($json, true);
        if (is_array($tmp)) {
            foreach (['showCorrections','showCredits','showOffers','showOffersOnly','showOpen','showDeletedOnly','hideDeleted','showSepa'] as $k) {
                if (isset($tmp[$k])) $filterState[$k] = !empty($tmp[$k]);
            }
        }
        $_SESSION['invoice_filter_state'] = $filterState;
    }
}
$showCorrections = !empty($filterState['showCorrections']);
$showCredits    = !empty($filterState['showCredits']);
$showOffers     = !empty($filterState['showOffers']);
$showOffersOnly = !empty($filterState['showOffersOnly']);
$showDelete     = !empty($filterState['showDelete']);
$showLinks      = !empty($filterState['showLinks']);
$showOpen       = !isset($filterState['showOpen']) ? true : !empty($filterState['showOpen']);
$showSepa       = !empty($filterState['showSepa']);
$showDeletedOnly = !empty($filterState['showDeletedOnly']);
$hideDeleted    = !empty($filterState['hideDeleted']);
$visibleColumns = isset($filterState['columns']) && is_array($filterState['columns'])
    ? array_map('intval', $filterState['columns'])
    : null;
$pageLength = isset($filterState['length']) ? max(1, (int)$filterState['length']) : null;
// Bei Einführung zusätzlicher Spalten wurden die Indizes angepasst.
// Einstellungen älterer Versionen (<3) werden hier korrigiert.
if ($visibleColumns && (($filterState['version'] ?? 1) < 2)) {
    $visibleColumns = array_map(function($c){ return $c >= 7 ? $c + 1 : $c; }, $visibleColumns);
}
if ($visibleColumns && (($filterState['version'] ?? 1) < 3)) {
    $visibleColumns = array_map(function($c){ return $c >= 4 ? $c + 1 : $c; }, $visibleColumns);
}
if ($visibleColumns && (($filterState['version'] ?? 1) < 4)) {
    $visibleColumns = array_map(function($c){ return $c >= 9 ? $c + 1 : $c; }, $visibleColumns);
}

$colorScheme = $_SESSION['invoice_color_scheme'] ?? null;
if ($admin_id && !$colorScheme) {
    $stmt = $pdo->prepare('SELECT visible_columns FROM admin_column_prefs WHERE admin_id = ? AND page = ?');
    $stmt->execute([$admin_id, 'rechnungen_color']);
    $colorScheme = $stmt->fetchColumn() ?: 'F1';
    $_SESSION['invoice_color_scheme'] = $colorScheme;
}
if (!$colorScheme) $colorScheme = 'F1';

if (!isset($_SESSION['invoice_positions'])) {
    $_SESSION['invoice_positions'] = [];
}
if (!isset($_SESSION['invoice_data'])) {
    $_SESSION['invoice_data'] = [];
}
if (!isset($_SESSION['rate_schedules'])) {
    $_SESSION['rate_schedules'] = [];
}
if (!isset($_SESSION['has_rates'])) {
    $_SESSION['has_rates'] = 0;
}
refreshRateFootText();

// Standardtexte
const DEFAULT_FUSSTEXT = '';
const KOPFTEXT_PLACEHOLDER = 'Rechnungskopftext eingeben...';

// Jahresrechnung aus Kundenvertrag erstellen
if (isset($_GET['annual']) && ctype_digit($_GET['annual']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $kid = (int)$_GET['annual'];
    $kstmt = $pdo->prepare('SELECT vorname, nachname, sepa_zustimmung, sepamandatsreferenz FROM kunden WHERE id=?');
    $kstmt->execute([$kid]);
    if ($krow = $kstmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['invoice_positions'] = [];
        $_SESSION['invoice_data'] = [
            'empfaenger' => trim($krow['vorname'] . ' ' . $krow['nachname']),
            'empfaenger_id' => $kid,
            'datum'      => date('Y-m-d'),
            'bezahldatum'=> date('Y-m-d', strtotime('+14 day')),
            'status'     => 'angelegt',
            'kopftext'   => '',
            'fusstext'   => '',
            'sepa'       => (int)$krow['sepa_zustimmung'],
            'sepa_ref'   => $krow['sepamandatsreferenz'] ?? ''
        ];
        $vstmt = $pdo->prepare('SELECT v.artikel_id, v.preis, v.startdatum, v.enddatum, v.ratenzahlung, v.raten_anzahl, v.raten_monate, a.aid AS artikelnummer, a.kurzbez, a.langbez, a.mwst FROM kunden_jahresvertraege v JOIN artikel a ON a.id=v.artikel_id WHERE v.kunde_id=?');
        $vstmt->execute([$kid]);
        $pstmt = $pdo->prepare('SELECT preis FROM preisliste WHERE aid=? AND preis_ab<=CURDATE() AND (preis_bis IS NULL OR preis_bis>=CURDATE()) ORDER BY preis_ab DESC LIMIT 1');
        $rateSchedules = [];
        while ($vrow = $vstmt->fetch(PDO::FETCH_ASSOC)) {
            $preis = $vrow['preis'];
            if ($preis === null) {
                $pstmt->execute([$vrow['artikel_id']]);
                $preis = $pstmt->fetchColumn();
            }
            $origPreis = $preis;
            $menge = 1;
            $bem = '';
            if ($vrow['ratenzahlung']) {
                $monate = $vrow['raten_monate'] !== null && $vrow['raten_monate'] !== ''
                    ? array_map('intval', explode(',', $vrow['raten_monate']))
                    : [];
                $anzahl = $monate ? count($monate) : max(1, (int)($vrow['raten_anzahl'] ?: 1));
                $preisRate = (float)$preis / $anzahl;
                $menge = $anzahl;
                $bem = $anzahl . ' Raten à ' . number_format($preisRate, 2, ',', '.') . ' €';
                $preis = $preisRate;
                if ($monate) {
                    $rateSchedules[] = [
                        'months' => $monate,
                        'preis'   => $origPreis,
                        'artikel' => $vrow['kurzbez']
                    ];
                } else {
                    $rateSchedules[] = [
                        'anzahl'  => $anzahl,
                        'preis'   => $origPreis,
                        'start'   => $vrow['startdatum'] ?: $_SESSION['invoice_data']['datum'],
                        'end'     => $vrow['enddatum'] ?: null,
                        'artikel' => $vrow['kurzbez']
                    ];
                }
            }
            if ($vrow['startdatum'] || $vrow['enddatum']) {
                $von = $vrow['startdatum'] ? $vrow['startdatum'] : '';
                $bis = $vrow['enddatum'] ? $vrow['enddatum'] : '';
                $dates = trim($von . ' - ' . $bis);
                $bem = $bem === '' ? $dates : $dates . ' ' . $bem;
            }
            $pos = [
                'artikelnummer' => $vrow['artikelnummer'],
                'kurzbez' => $vrow['kurzbez'],
                'langbez' => $vrow['langbez'],
                'bemerkung' => $bem,
                'einzelpreis' => (float)$preis,
                'menge' => $menge,
                'rabatt' => 0.0,
                'mwst' => (float)$vrow['mwst']
            ];
            if ($vrow['ratenzahlung']) {
                $pos['ratenzahlung'] = 1;
                $pos['raten_anzahl'] = $anzahl;
                $pos['raten_preis'] = $origPreis;
                $pos['raten_start'] = $vrow['startdatum'] ?: $_SESSION['invoice_data']['datum'];
                $pos['raten_end']   = $vrow['enddatum'] ?: null;
                if (!empty($monate)) {
                    $pos['raten_monate'] = $monate;
                }
            }
            $_SESSION['invoice_positions'][] = $pos;
        }
        if ($rateSchedules) {
            $_SESSION['invoice_data']['fusstext'] = buildRateFootText($_SESSION['invoice_data']['datum'], $rateSchedules);
            $_SESSION['has_rates'] = 1;
        } else {
            $_SESSION['has_rates'] = 0;
        }
        header('Location: rechnungen.php');
        exit;
    }
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : (int)($_POST['invoice_id'] ?? 0);
$offerEditId = isset($_GET['offer_edit']) ? (int)$_GET['offer_edit'] : (int)($_POST['offer_id'] ?? 0);
$editNr = '';
$offerNrEdit = '';
$empfaenger_edit = $_SESSION['invoice_data']['empfaenger'] ?? '';
$empfaenger_id_edit = $_SESSION['invoice_data']['empfaenger_id'] ?? '';
$datum_edit = $_SESSION['invoice_data']['datum'] ?? date('Y-m-d');
$status_edit = $_SESSION['invoice_data']['status'] ?? 'angelegt';
$kopftext_edit = $_SESSION['invoice_data']['kopftext'] ?? '';
if ($kopftext_edit === KOPFTEXT_PLACEHOLDER) { $kopftext_edit = ''; }
$fusstext_edit = $_SESSION['invoice_data']['fusstext'] ?? '';
$bezahldatum_edit = $_SESSION['invoice_data']['bezahldatum'] ?? date('Y-m-d', strtotime($datum_edit . ' +14 day'));
$sepa_edit = $_SESSION['invoice_data']['sepa'] ?? 0;
$sepa_ref_edit = $_SESSION['invoice_data']['sepa_ref'] ?? '';
if ($editId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM rechnungen WHERE id = ?');
    $stmt->execute([$editId]);
    if ($re = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $editNr = $re['rechnungsnummer'] ?? '';
        if (in_array($re['status'], ['versendet','bezahlt'])) {
            $error = 'Rechnung ist bereits versendet und kann nicht mehr geändert werden.';
            $editId = 0;
        } elseif ($re['status'] === 'geloescht') {
            $delName = '';
            if (!empty($re['geloescht_von'])) {
                $a = $pdo->prepare('SELECT vorname,nachname FROM admins WHERE id=?');
                $a->execute([$re['geloescht_von']]);
                if ($row = $a->fetch()) {
                    $delName = trim($row['vorname'].' '.$row['nachname']);
                }
            }
            $delDate = $re['geloescht_am'] ? date('d.m.Y', strtotime($re['geloescht_am'])) : '';
            $grund = $re['geloescht_grund'] ?? '';
            $msg = 'Rechnung wurde gelöscht';
            if($delName) $msg .= ' von '.$delName;
            if($delDate) $msg .= ' am '.$delDate;
            if($grund!=='') $msg .= ' – Grund: '.$grund;
            $error = $msg;
            $editId = 0;
        } else {
            $empfaenger_edit = $re['empfaenger'];
            $empfaenger_id_edit = $re['empfaenger_id'] ?? 0;
            $datum_edit = $re['erstellt_am'];
            $status_edit = $re['status'];
            $kopftext_edit = $re['kopftext'] ?? '';
            if ($kopftext_edit === KOPFTEXT_PLACEHOLDER) { $kopftext_edit = ''; }
            $fusstext_edit = $re['fusstext'] ?? '';
            $bezahldatum_edit = $re['bezahldatum'] ?? date('Y-m-d', strtotime($datum_edit . ' +14 day'));
            $sepa_edit = (int)($re['sepa'] ?? 0);
            $sepa_ref_edit = $re['sepa_ref'] ?? '';
            $_SESSION['invoice_positions'] = [];
            $_SESSION['invoice_data'] = [
                'empfaenger' => $empfaenger_edit,
                'empfaenger_id' => $empfaenger_id_edit,
                'datum'      => $datum_edit,
                'bezahldatum'=> $bezahldatum_edit,
                'status'     => $status_edit,
                'kopftext'   => $kopftext_edit,
                'fusstext'   => $fusstext_edit,
                'sepa'       => $sepa_edit,
                'sepa_ref'   => $sepa_ref_edit
            ];
            $_SESSION['has_rates'] = (int)($re['ratenzahlung'] ?? 0);
            $pstmt = $pdo->prepare('SELECT * FROM rechnungspositionen WHERE rechnung_id = ? ORDER BY id');
            $pstmt->execute([$editId]);
            $mwstStmt = $pdo->prepare('SELECT mwst FROM artikel WHERE aid = ? LIMIT 1');
            while ($row = $pstmt->fetch(PDO::FETCH_ASSOC)) {
                $mwstStmt->execute([$row['artikelnummer']]);
                $mwst = (float)($mwstStmt->fetchColumn() ?? 0);
                $_SESSION['invoice_positions'][] = [
                    'artikelnummer' => $row['artikelnummer'],
                    'kurzbez' => $row['kurzbez'],
                    'langbez' => $row['langbez'],
                    'bemerkung' => $row['bemerkung'] ?? '',
                    'einzelpreis' => $row['einzelpreis'],
                    'menge' => $row['menge'],
                    'rabatt' => $row['rabatt'],
                    'mwst' => $mwst
                ];
            }
        }
    } else {
        $editId = 0;
    }
}

if ($offerEditId > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM angebote WHERE id = ?');
    $stmt->execute([$offerEditId]);
    if ($offer = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (in_array($offer['status'], ['versendet','bezahlt'])) {
            $error = 'Angebot ist bereits versendet und kann nicht mehr geändert werden.';
            $offerEditId = 0;
        } else {
            $offerNrEdit = $offer['angebotsnummer'] ?? '';
            $empfaenger_edit = $offer['empfaenger'];
            $empfaenger_id_edit = $offer['empfaenger_id'] ?? 0;
            $datum_edit = $offer['erstellt_am'];
            $status_edit = $offer['status'];
            $kopftext_edit = $offer['kopftext'] ?? '';
            if ($kopftext_edit === KOPFTEXT_PLACEHOLDER) { $kopftext_edit = ''; }
            $fusstext_edit = $offer['fusstext'] ?? '';
            $bezahldatum_edit = $offer['bezahldatum'] ?? date('Y-m-d', strtotime($datum_edit . ' +14 day'));
            $_SESSION['invoice_positions'] = [];
            $_SESSION['invoice_data'] = [
                'empfaenger' => $empfaenger_edit,
                'empfaenger_id' => $empfaenger_id_edit,
                'datum'      => $datum_edit,
                'bezahldatum'=> $bezahldatum_edit,
                'status'     => $status_edit,
                'kopftext'   => $kopftext_edit,
                'fusstext'   => $fusstext_edit,
                'sepa'       => 0,
                'sepa_ref'   => ''
            ];
            $_SESSION['has_rates'] = 0;
            $pstmt = $pdo->prepare('SELECT * FROM angebotpositionen WHERE rechnung_id = ? ORDER BY id');
            $pstmt->execute([$offerEditId]);
            $mwstStmt = $pdo->prepare('SELECT mwst FROM artikel WHERE aid = ? LIMIT 1');
            while ($row = $pstmt->fetch(PDO::FETCH_ASSOC)) {
                $mwstStmt->execute([$row['artikelnummer']]);
                $mwst = (float)($mwstStmt->fetchColumn() ?? 0);
                $_SESSION['invoice_positions'][] = [
                    'artikelnummer' => $row['artikelnummer'],
                    'kurzbez' => $row['kurzbez'],
                    'langbez' => $row['langbez'],
                    'bemerkung' => $row['bemerkung'] ?? '',
                    'einzelpreis' => $row['einzelpreis'],
                    'menge' => $row['menge'],
                    'rabatt' => $row['rabatt'],
                    'mwst' => $mwst
                ];
            }
        }
    } else {
        $offerEditId = 0;
    }
}

$error = '';
$error_is_html = false;
$success = '';
if (isset($_SESSION['invoice_message'])) {
    $success = $_SESSION['invoice_message'];
    unset($_SESSION['invoice_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['add','remove'])) {
        $empfaenger_edit = trim($_POST['empfaenger'] ?? '') ?: $empfaenger_edit;
        $empfaenger_id_edit = isset($_POST['empfaenger_id']) ? (int)$_POST['empfaenger_id'] : $empfaenger_id_edit;
        $datum_edit = ($_POST['datum'] ?? '') ?: $datum_edit;
        $status_edit = ($_POST['status'] ?? '') ?: $status_edit;
        if(isset($_POST['bezahldatum']) && $_POST['bezahldatum'] !== '') {
            $bezahldatum_edit = $_POST['bezahldatum'];
        } else {
            $bezahldatum_edit = date('Y-m-d', strtotime($datum_edit . ' +14 day'));
        }
        $kopftext_edit = isset($_POST['kopftext']) ? trim($_POST['kopftext']) : $kopftext_edit;
        if ($kopftext_edit === KOPFTEXT_PLACEHOLDER) { $kopftext_edit = ''; }
        $fusstext_edit = isset($_POST['fusstext']) ? trim($_POST['fusstext']) : $fusstext_edit;
        $status = $_POST['status'] ?? 'angelegt';
        $kstmt = $pdo->prepare('SELECT sepa_zustimmung, sepamandatsreferenz FROM kunden WHERE id=?');
        $kstmt->execute([$empfaenger_id_edit]);
        $kinfo = $kstmt->fetch(PDO::FETCH_ASSOC);
        $sepa_edit = (int)($kinfo['sepa_zustimmung'] ?? 0);
        $sepa_ref_edit = $sepa_edit ? ($kinfo['sepamandatsreferenz'] ?? '') : '';
        $hasRates = !empty($_SESSION['rate_schedules']) ? 1 : ($_SESSION['has_rates'] ?? 0);
        $_SESSION['invoice_data'] = [
            'empfaenger' => $empfaenger_edit,
            'empfaenger_id' => $empfaenger_id_edit,
            'datum'      => $datum_edit,
            'bezahldatum'=> $bezahldatum_edit,
            'status'     => $status_edit,
            'kopftext'   => $kopftext_edit,
            'fusstext'   => $fusstext_edit,
            'sepa'       => $sepa_edit,
            'sepa_ref'   => $sepa_ref_edit
        ];
        $editId = isset($_POST['invoice_id']) ? (int)$_POST['invoice_id'] : $editId;

        $preisePost = $_POST['preise'] ?? [];
        $mengenPost = $_POST['mengen'] ?? [];
        $rabattePost = $_POST['rabatte'] ?? [];
        $bemPost = $_POST['bemerkungen'] ?? [];
        foreach ($_SESSION['invoice_positions'] as $i => &$p) {
            if (isset($preisePost[$i])) $p['einzelpreis'] = (float)$preisePost[$i];
            if (isset($mengenPost[$i])) $p['menge'] = max(1, (int)$mengenPost[$i]);
            if (isset($rabattePost[$i])) $p['rabatt'] = (float)$rabattePost[$i];
            if (isset($bemPost[$i])) $p['bemerkung'] = trim($bemPost[$i]);
        }
        unset($p);
        refreshRateFootText();
    }
    if ($action === 'add') {
        $nummer = trim($_POST['artikelnummer'] ?? '');
        $menge = max(1, (int)($_POST['menge'] ?? 1));
        $preisInput = isset($_POST['einzelpreis']) && $_POST['einzelpreis'] !== '' ? (float)$_POST['einzelpreis'] : null;
        $bemerkungAdd = trim($_POST['bemerkung'] ?? '');
        $raten = isset($_POST['ratenzahlung']);
        $ratenMonate = $raten ? array_filter(array_map('intval', $_POST['raten_monate'] ?? [])) : [];
        $ratenAnzahl = count($ratenMonate);
        if ($nummer !== '') {
            $stmt = $pdo->prepare("SELECT a.id,a.aid AS artikelnummer,a.kurzbez,a.langbez,a.mwst,(SELECT preis FROM preisliste p WHERE p.aid=a.id AND p.preis_ab<=CURDATE() AND (p.preis_bis IS NULL OR p.preis_bis>=CURDATE()) ORDER BY p.preis_ab DESC LIMIT 1) AS preis FROM artikel a WHERE a.aid=? AND a.aktiv=1 LIMIT 1");
            $stmt->execute([$nummer]);
            if ($row = $stmt->fetch()) {
                $preis = $preisInput !== null ? $preisInput : (float)$row['preis'];
                $entry = [
                    'artikelnummer' => $row['artikelnummer'],
                    'kurzbez' => $row['kurzbez'],
                    'langbez' => $row['langbez'],
                    'rabatt' => 0.0,
                    'mwst' => (float)$row['mwst'],
                ];
                if ($raten && $ratenAnzahl > 0) {
                    $rate = $preis / $ratenAnzahl;
                    $entry['bemerkung'] = trim(($bemerkungAdd ? $bemerkungAdd . ' ' : '') . $ratenAnzahl . ' Raten à ' . number_format($rate, 2, ',', '.') . ' €');
                    $entry['einzelpreis'] = $rate;
                    $entry['menge'] = $ratenAnzahl;
                    $entry['ratenzahlung'] = 1;
                    $entry['raten_monate'] = $ratenMonate;
                    $entry['raten_preis'] = $preis;
                    $_SESSION['rate_schedules'][] = [
                        'months'  => $ratenMonate,
                        'preis'   => $preis,
                        'artikel' => $row['kurzbez']
                    ];
                    $_SESSION['invoice_data']['fusstext'] = buildRateFootText($_SESSION['invoice_data']['datum'] ?? date('Y-m-d'), $_SESSION['rate_schedules']);
                } else {
                    $entry['bemerkung'] = $bemerkungAdd;
                    $entry['einzelpreis'] = $preis;
                    $entry['menge'] = $menge;
                }
                $_SESSION['invoice_positions'][] = $entry;
                refreshRateFootText();
            }
        }
    } elseif ($action === 'remove') {
        $idx = (int)($_POST['index'] ?? -1);
        if (isset($_SESSION['invoice_positions'][$idx])) {
            array_splice($_SESSION['invoice_positions'], $idx, 1);
        }
        refreshRateFootText();
    } elseif ($action === 'save') {
        $empfaenger = trim($_POST['empfaenger'] ?? '');
        $empfaenger_id = (int)($_POST['empfaenger_id'] ?? 0);
        $datum = $_POST['datum'] ?? date('Y-m-d');
        $preise = $_POST['preise'] ?? [];
        $mengen = $_POST['mengen'] ?? [];
        $rabatte = $_POST['rabatte'] ?? [];
        $bemerkungen = $_POST['bemerkungen'] ?? [];
        $status = $_POST['status'] ?? 'angelegt';
        $bezahldatum = $_POST['bezahldatum'] ?? date('Y-m-d', strtotime($datum . ' +14 day'));
        $kopftext = trim($_POST['kopftext'] ?? '');
        if ($kopftext === KOPFTEXT_PLACEHOLDER) { $kopftext = ''; }
        $fusstext = trim($_POST['fusstext'] ?? '');
        if (!empty($_SESSION['rate_schedules'])) {
            $fusstext = buildRateFootText($datum, $_SESSION['rate_schedules']);
        }
        $kstmt = $pdo->prepare('SELECT sepa_zustimmung, sepamandatsreferenz FROM kunden WHERE id=?');
        $kstmt->execute([$empfaenger_id]);
        $kinfo = $kstmt->fetch(PDO::FETCH_ASSOC);
        $sepa_edit = (int)($kinfo['sepa_zustimmung'] ?? 0);
        $sepa_ref_edit = $sepa_edit ? ($kinfo['sepamandatsreferenz'] ?? '') : '';
        $edit = (int)($_POST['invoice_id'] ?? 0);
        if ($edit > 0) {
            $check = $pdo->prepare('SELECT 1 FROM rechnungen WHERE id=?');
            $check->execute([$edit]);
            if (!$check->fetchColumn()) {
                $edit = 0; // fallback to new invoice if id not found
            }
        }
        $empfaenger_edit = $empfaenger;
        $datum_edit = $datum;
        $status_edit = $status;
        $bezahldatum_edit = $bezahldatum;
        $kopftext_edit = $kopftext === KOPFTEXT_PLACEHOLDER ? '' : $kopftext;
        $fusstext_edit = $fusstext;
        $_SESSION['invoice_data'] = [
            'empfaenger' => $empfaenger_edit,
            'empfaenger_id' => $empfaenger_id,
            'datum'      => $datum_edit,
            'bezahldatum'=> $bezahldatum_edit,
            'status'     => $status_edit,
            'kopftext'   => $kopftext_edit,
            'fusstext'   => $fusstext_edit,
            'sepa'       => $sepa_edit,
            'sepa_ref'   => $sepa_ref_edit
        ];
        $hasRates = !empty($_SESSION['rate_schedules']) ? 1 : 0;
        if ($empfaenger === '' || $kopftext === '' || empty($_SESSION['invoice_positions'])) {
            $error = 'Bitte Empfänger, Kopftext und Positionen angeben.';
        } elseif ($sepa_edit && $sepa_ref_edit === '') {
            $error = 'Für SEPA-Zahlungen wird eine Mandatsreferenz benötigt.';
        } else {
            if ($edit > 0) {
                $pdo->prepare('UPDATE rechnungen SET empfaenger=?, empfaenger_id=?, erstellt_am=?, status=?, bezahldatum=?, kopftext=?, fusstext=?, sepa=?, sepa_ref=?, ratenzahlung=? WHERE id=?')->execute([
                    $empfaenger,
                    $empfaenger_id,
                    $datum,
                    $status,
                    $bezahldatum,
                    $kopftext,
                    $fusstext,
                    $sepa_edit,
                    $sepa_ref_edit,
                    $hasRates,
                    $edit
                ]);
                $pdo->prepare('DELETE FROM rechnungspositionen WHERE rechnung_id=?')->execute([$edit]);
                $rid = $edit;
                $nrStmt = $pdo->prepare('SELECT rechnungsnummer FROM rechnungen WHERE id=?');
                $nrStmt->execute([$edit]);
                $editNr = $nrStmt->fetchColumn() ?: '';
            } else {
                $nr = nextInvoiceNumber($pdo);
                $buchungRef = $_SESSION['invoice_buchung_id'] ?? null;
                $stmt = $pdo->prepare('INSERT INTO rechnungen (rechnungsnummer, empfaenger, empfaenger_id, erstellt_am, status, bezahldatum, kopftext, fusstext, sepa, sepa_ref, ratenzahlung, jahresrechnung, einzelmitgliedsrechnung, buchungen_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$nr, $empfaenger, $empfaenger_id, $datum, $status, $bezahldatum, $kopftext, $fusstext, $sepa_edit, $sepa_ref_edit, $hasRates, 0, 0, $buchungRef]);
                $rid = $pdo->lastInsertId();
            }
            $ins = $pdo->prepare('INSERT INTO rechnungspositionen (rechnung_id, artikelnummer, kurzbez, langbez, bemerkung, einzelpreis, menge, rabatt) VALUES (?,?,?,?,?,?,?,?)');
            foreach ($_SESSION['invoice_positions'] as $idx => $pos) {
                $preis = isset($preise[$idx]) ? (float)$preise[$idx] : (float)$pos['einzelpreis'];
                $menge = isset($mengen[$idx]) ? max(1, (int)$mengen[$idx]) : (int)$pos['menge'];
                $rabatt = isset($rabatte[$idx]) ? (float)$rabatte[$idx] : 0.0;
                $bem = isset($bemerkungen[$idx]) ? trim($bemerkungen[$idx]) : ($pos['bemerkung'] ?? '');
                $ins->execute([$rid, $pos['artikelnummer'], $pos['kurzbez'], $pos['langbez'], $bem, $preis, $menge, $rabatt]);
            }
            $_SESSION['invoice_positions'] = [];
            $_SESSION['invoice_data'] = [];
            $_SESSION['rate_schedules'] = [];
            $_SESSION['has_rates'] = 0;
            unset($_SESSION['invoice_buchung_id']);
            $status_edit = $status;
            $datum_edit = date('Y-m-d');
            if ($edit > 0) {
                $success = 'Rechnung aktualisiert.';
            $empfaenger_edit = '';
            $kopftext_edit = '';
            $fusstext_edit = '';
            $bezahldatum_edit = date('Y-m-d', strtotime($datum_edit . ' +14 day'));
            } else {
                $success = 'Rechnung ' . $nr . ' für ' . $empfaenger . ' erstellt. <a href="rechnung_pdf.php?id=' . $rid . '" class="pdf-link" target="_blank">PDF herunterladen</a>';
                $empfaenger_edit = '';
                $kopftext_edit = '';
                $fusstext_edit = '';
                $bezahldatum_edit = date('Y-m-d', strtotime($datum_edit . ' +14 day'));
                $editNr = '';
            }
        }
    } elseif (in_array($action, ['offer','offer_save','offer_convert'])) {
        $empfaenger = trim($_POST['empfaenger'] ?? '');
        $empfaenger_id = (int)($_POST['empfaenger_id'] ?? 0);
        $datum = $_POST['datum'] ?? date('Y-m-d');
        $preise = $_POST['preise'] ?? [];
        $mengen = $_POST['mengen'] ?? [];
        $rabatte = $_POST['rabatte'] ?? [];
        $bemerkungen = $_POST['bemerkungen'] ?? [];
        $bezahldatum = $_POST['bezahldatum'] ?? date('Y-m-d', strtotime($datum . ' +14 day'));
        $status = $_POST['status'] ?? 'angelegt';
        $kopftext = trim($_POST['kopftext'] ?? '');
        if ($kopftext === KOPFTEXT_PLACEHOLDER) { $kopftext = ''; }
        $fusstext = trim($_POST['fusstext'] ?? '');
        if (!empty($_SESSION['rate_schedules'])) {
            $fusstext = buildRateFootText($datum, $_SESSION['rate_schedules']);
        }
        $kstmt = $pdo->prepare('SELECT sepa_zustimmung, sepamandatsreferenz FROM kunden WHERE id=?');
        $kstmt->execute([$empfaenger_id]);
        $kinfo = $kstmt->fetch(PDO::FETCH_ASSOC);
        $sepa_edit = (int)($kinfo['sepa_zustimmung'] ?? 0);
        $sepa_ref_edit = $sepa_edit ? ($kinfo['sepamandatsreferenz'] ?? '') : '';
        if ($empfaenger === '' || $kopftext === '' || empty($_SESSION['invoice_positions'])) {
            $error = 'Bitte Empfänger, Kopftext und Positionen angeben.';
        } else {
            $_SESSION['offer_positions'] = [];
            foreach ($_SESSION['invoice_positions'] as $idx => $pos) {
                $preis = isset($preise[$idx]) ? (float)$preise[$idx] : (float)$pos['einzelpreis'];
                $menge = isset($mengen[$idx]) ? max(1, (int)$mengen[$idx]) : (int)$pos['menge'];
                $rabatt = isset($rabatte[$idx]) ? (float)$rabatte[$idx] : 0.0;
                $bem = isset($bemerkungen[$idx]) ? trim($bemerkungen[$idx]) : ($pos['bemerkung'] ?? '');
                $_SESSION['offer_positions'][] = [
                    'artikelnummer' => $pos['artikelnummer'],
                    'kurzbez'       => $pos['kurzbez'],
                    'langbez'       => $pos['langbez'],
                    'bemerkung'     => $bem,
                    'einzelpreis'   => $preis,
                    'menge'         => $menge,
                    'rabatt'        => $rabatt,
                    'mwst'          => (float)($pos['mwst'] ?? 0)
                ];
            }
            $offerId = 0;
            $offerNr = nextOfferNumber($pdo);
            if (in_array($action, ['offer_save','offer_convert'])) {
                $offerId = (int)($_POST['offer_id'] ?? $offerEditId);
                $chk = $pdo->prepare('SELECT angebotsnummer FROM angebote WHERE id=?');
                $chk->execute([$offerId]);
                if ($row = $chk->fetch()) {
                    $offerNr = $row['angebotsnummer'];
                } else {
                    $offerId = 0;
                }
            }
            $_SESSION['offer_data'] = [
                'nummer'     => $offerNr,
                'empfaenger' => $empfaenger,
                'empfaenger_id' => $empfaenger_id,
                'datum'      => $datum,
                'bezahldatum'=> $bezahldatum,
                'status'     => $status,
                'kopftext'   => $kopftext,
                'fusstext'   => $fusstext,
                'admin_name' => $admin_name
            ];

            if ($offerId > 0 && $action === 'offer_save') {
                $pdo->prepare('UPDATE angebote SET empfaenger=?, empfaenger_id=?, erstellt_am=?, bezahldatum=?, status=?, kopftext=?, fusstext=? WHERE id=?')->execute([
                    $empfaenger,
                    $empfaenger_id,
                    $datum,
                    $bezahldatum,
                    $status,
                    $kopftext,
                    $fusstext,
                    $offerId
                ]);
                $pdo->prepare('DELETE FROM angebotpositionen WHERE rechnung_id=?')->execute([$offerId]);
                $offId = $offerId;
            } elseif ($offerId > 0 && $action === 'offer_convert') {
                $offId = $offerId;
            } else {
                $offStmt = $pdo->prepare('INSERT INTO angebote (angebotsnummer, empfaenger, empfaenger_id, erstellt_am, bezahldatum, status, kopftext, fusstext) VALUES (?,?,?,?,?,?,?,?)');
                $offStmt->execute([$offerNr, $empfaenger, $empfaenger_id, $datum, $bezahldatum, 'angelegt', $kopftext, $fusstext]);
                $offId = $pdo->lastInsertId();
            }
            $posStmt = $pdo->prepare('INSERT INTO angebotpositionen (rechnung_id, artikelnummer, kurzbez, langbez, bemerkung, einzelpreis, menge, rabatt) VALUES (?,?,?,?,?,?,?,?)');
            foreach ($_SESSION['offer_positions'] as $p) {
                $posStmt->execute([$offId, $p['artikelnummer'], $p['kurzbez'], $p['langbez'], $p['bemerkung'], $p['einzelpreis'], $p['menge'], $p['rabatt']]);
            }
            if ($action === 'offer_convert') {
                $nr = nextInvoiceNumber($pdo);
                $buchungRef = $_SESSION['invoice_buchung_id'] ?? null;
                $stmt = $pdo->prepare('INSERT INTO rechnungen (rechnungsnummer, empfaenger, empfaenger_id, erstellt_am, status, bezahldatum, kopftext, fusstext, sepa, sepa_ref, ratenzahlung, jahresrechnung, einzelmitgliedsrechnung, buchungen_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                $stmt->execute([$nr, $empfaenger, $empfaenger_id, $datum, $status, $bezahldatum, $kopftext, $fusstext, 0, '', 0, 0, 0, $buchungRef]);
                $rid = $pdo->lastInsertId();
                $ins = $pdo->prepare('INSERT INTO rechnungspositionen (rechnung_id, artikelnummer, kurzbez, langbez, bemerkung, einzelpreis, menge, rabatt) VALUES (?,?,?,?,?,?,?,?)');
                foreach ($_SESSION['offer_positions'] as $p) {
                    $ins->execute([$rid, $p['artikelnummer'], $p['kurzbez'], $p['langbez'], $p['bemerkung'], $p['einzelpreis'], $p['menge'], $p['rabatt']]);
                }
                $pdo->prepare('UPDATE angebote SET rechnung_id=?, status=? WHERE id=?')->execute([$rid, 'versendet', $offId]);
                $_SESSION['invoice_positions'] = [];
                $_SESSION['invoice_data'] = [];
                $_SESSION['offer_positions'] = [];
                $_SESSION['offer_data'] = [];
                $offerEditId = 0;
                unset($_SESSION['invoice_buchung_id']);
                $success = 'Rechnung ' . $nr . ' für ' . $empfaenger . ' aus Angebot erstellt. <a href="rechnung_pdf.php?id=' . $rid . '" class="pdf-link" target="_blank">PDF herunterladen</a>';
                $empfaenger_edit = '';
                $datum_edit = date('Y-m-d');
                $status_edit = $status;
                $kopftext_edit = '';
                $fusstext_edit = '';
                $bezahldatum_edit = date('Y-m-d', strtotime($datum_edit . ' +14 day'));
                $editNr = '';
            } else {
                $success = $offerId > 0
                    ? 'Angebot aktualisiert.'
                    : 'Angebot erstellt. <a href="angebot_pdf.php?id=' . $offId . '" class="pdf-link" target="_blank">PDF herunterladen</a>';
                $_SESSION['invoice_positions'] = [];
                $_SESSION['invoice_data'] = [];
                $_SESSION['offer_positions'] = [];
                $_SESSION['offer_data'] = [];
                $offerEditId = 0;
                unset($_SESSION['invoice_buchung_id']);
                $empfaenger_edit = '';
                $kopftext_edit = '';
                $fusstext_edit = '';
                $datum_edit = date('Y-m-d');
                $bezahldatum_edit = date('Y-m-d', strtotime($datum_edit . ' +14 day'));
                $status_edit = $status;
            }
        }
    } elseif ($action === 'cancel') {
        $_SESSION['invoice_positions'] = [];
        $_SESSION['invoice_data'] = [];
        $_SESSION['rate_schedules'] = [];
        $_SESSION['has_rates'] = 0;
        unset($_SESSION['invoice_buchung_id']);
        header('Location: rechnungen.php');
        exit;
    }
}

// Angebot direkt in Rechnung umwandeln
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['convert_offer']) && ctype_digit($_GET['convert_offer'])) {
    $convId = (int)$_GET['convert_offer'];
    $stmt = $pdo->prepare('SELECT * FROM angebote WHERE id = ?');
    $stmt->execute([$convId]);
    if ($off = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($off['rechnung_id'])) {
            $error = 'Angebot hat bereits eine Rechnung! <a href="rechnungen.php?edit=' . $off['rechnung_id'] . '">Diese öffnen?</a>';
            $error_is_html = true;
        } else {
            $posStmt = $pdo->prepare('SELECT * FROM angebotpositionen WHERE rechnung_id=? ORDER BY id');
            $posStmt->execute([$convId]);
            $pos = $posStmt->fetchAll(PDO::FETCH_ASSOC);

            $nr = nextInvoiceNumber($pdo);
            $empId = (int)$off['empfaenger_id'];
            $empName = $off['empfaenger'];
            if ($empId > 0) {
                $kStmt = $pdo->prepare('SELECT vorname, nachname FROM kunden WHERE id=?');
                $kStmt->execute([$empId]);
                if ($krow = $kStmt->fetch(PDO::FETCH_ASSOC)) {
                    $empName = trim($krow['vorname'] . ' ' . $krow['nachname']);
                }
            }
            $buchungRef = $_SESSION['invoice_buchung_id'] ?? null;
            $ins = $pdo->prepare('INSERT INTO rechnungen (rechnungsnummer, empfaenger, empfaenger_id, erstellt_am, status, bezahldatum, kopftext, fusstext, sepa, sepa_ref, ratenzahlung, jahresrechnung, einzelmitgliedsrechnung, buchungen_id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $ins->execute([$nr, $empName, $empId, $off['erstellt_am'], 'angelegt', $off['bezahldatum'], $off['kopftext'], $off['fusstext'], 0, '', 0, 0, 0, $buchungRef]);
            $rid = $pdo->lastInsertId();
            $pIns = $pdo->prepare('INSERT INTO rechnungspositionen (rechnung_id, artikelnummer, kurzbez, langbez, bemerkung, einzelpreis, menge, rabatt) VALUES (?,?,?,?,?,?,?,?)');
            foreach ($pos as $p) {
                $pIns->execute([$rid, $p['artikelnummer'], $p['kurzbez'], $p['langbez'], $p['bemerkung'], $p['einzelpreis'], $p['menge'], $p['rabatt']]);
            }
            $upd = $pdo->prepare('UPDATE angebote SET rechnung_id=?, status=? WHERE id=?');
            $upd->execute([$rid, 'versendet', $convId]);
            unset($_SESSION['invoice_buchung_id']);
            $success = 'Rechnung ' . $nr . ' für ' . $empName . ' aus Angebot erstellt. <a href="rechnung_pdf.php?id=' . $rid . '" class="pdf-link" target="_blank">PDF herunterladen</a>';
            $empfaenger_edit = '';
            $datum_edit = date('Y-m-d');
            $status_edit = 'angelegt';
            $kopftext_edit = '';
            $fusstext_edit = '';
            $bezahldatum_edit = date('Y-m-d', strtotime($datum_edit . ' +14 day'));
            $editNr = '';
        }
    } else {
        $error = 'Angebot nicht gefunden.';
    }
}

$artikelStmt = $pdo->query("SELECT a.id,a.aid AS artikelnummer,a.kurzbez,a.mwst,(SELECT preis FROM preisliste p WHERE p.aid=a.id AND p.preis_ab<=CURDATE() AND (p.preis_bis IS NULL OR p.preis_bis>=CURDATE()) ORDER BY p.preis_ab DESC LIMIT 1) AS preis FROM artikel a WHERE a.aktiv=1 ORDER BY a.aid, a.kurzbez");
$artikel = $artikelStmt->fetchAll(PDO::FETCH_ASSOC);

// Kunden für Empfänger-Datalist laden
$kundenStmt = $pdo->query("SELECT id, vorname, nachname, email, sepa_zustimmung, sepamandatsreferenz FROM kunden WHERE aktiv=1 ORDER BY nachname, vorname");
$kunden = $kundenStmt->fetchAll(PDO::FETCH_ASSOC);

// Filterparameter für Anzeige bestehender Rechnungen
$jahr = isset($_GET['jahr']) && $_GET['jahr'] !== '' ? (int)$_GET['jahr'] : '';
$von  = $_GET['von'] ?? '';
$bis  = $_GET['bis'] ?? '';
if($jahr !== '' && $von === '' && $bis === ''){
    $von = $jahr . '-01-01';
    $bis = $jahr . '-12-31';
}

// Auswahl der vorhandenen Geschäftsjahre
$yearsStmt = $pdo->query('SELECT DISTINCT YEAR(erstellt_am) AS jahr FROM rechnungen ORDER BY jahr DESC');
$jahre = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

try {

    $sql = 'SELECT * FROM rechnungen';
    $conds = [];
    $params = [];
    if($von !== ''){ $conds[] = 'erstellt_am >= ?'; $params[] = $von; }
    if($bis !== ''){ $conds[] = 'erstellt_am <= ?'; $params[] = $bis; }
    if($conds){ $sql .= ' WHERE ' . implode(' AND ', $conds); }
    $sql .= ' ORDER BY id DESC';
    $rechStmt = $pdo->prepare($sql);
    $rechStmt->execute($params);
    $rechnungen = $rechStmt->fetchAll(PDO::FETCH_ASSOC);
    $nrById = [];
    foreach ($rechnungen as $r) {
        $nrById[$r['id']] = $r['rechnungsnummer'];
    }
    $corrections = [];
    $parentNums = [];
    $reminderById = [];
    foreach ($rechnungen as $r) {
        if (!empty($r['parent_id'])) {
            $pid = (int)$r['parent_id'];
            $parentNums[$r['id']] = $nrById[$pid] ?? '';
            $isReminder = stripos($r['kopftext'] ?? '', 'Mahnung zur Rechnung') !== false;
            $reminderById[$r['id']] = $isReminder;
            if (!$isReminder) {
                $corrections[$pid] = isset($corrections[$pid])
                    ? $corrections[$pid] . ', ' . $r['rechnungsnummer']
                    : $r['rechnungsnummer'];
            }
        }
    }

    $positionenByInvoice = [];
    $stmt = $pdo->prepare('SELECT * FROM rechnungspositionen WHERE rechnung_id = ? ORDER BY id');
    foreach ($rechnungen as $r) {
        $stmt->execute([$r['id']]);
        $positionenByInvoice[$r['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Gutschriften laden
    $sqlG = 'SELECT g.id, g.gutschriftnummer, g.empfaenger, g.empfaenger_id, g.erstellt_am, g.bezahldatum, g.status, g.kopftext, g.rechnung_id, r.rechnungsnummer AS parent_num, r.mahnstufe '
           . 'FROM gutschriften g LEFT JOIN rechnungen r ON g.rechnung_id = r.id';
    $condsG = [];
    $paramsG = [];
    if ($von !== '') { $condsG[] = 'g.erstellt_am >= ?'; $paramsG[] = $von; }
    if ($bis !== '') { $condsG[] = 'g.erstellt_am <= ?'; $paramsG[] = $bis; }
    if ($condsG) { $sqlG .= ' WHERE ' . implode(' AND ', $condsG); }
    $sqlG .= ' ORDER BY g.id DESC';
    $gStmt = $pdo->prepare($sqlG);
    $gStmt->execute($paramsG);
    $gutschriften = $gStmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapping: zu welcher Rechnung existieren bereits Gutschriften?
    $creditsByInvoice = [];
    foreach ($gutschriften as $g) {
        $pid = (int)($g['rechnung_id'] ?? 0);
        if ($pid > 0) {
            if (!isset($creditsByInvoice[$pid])) {
                $creditsByInvoice[$pid] = [];
            }
            $creditsByInvoice[$pid][] = $g['gutschriftnummer'];
        }
    }

    // Positionen der Gutschriften laden
    $positionenByCredit = [];
    $stmt = $pdo->prepare('SELECT * FROM gutschriftpositionen WHERE rechnung_id = ? ORDER BY id');
    foreach ($gutschriften as $g) {
        $stmt->execute([$g['id']]);
        $positionenByCredit[$g['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// Gesamtsummen und Artikelwerte pro Rechnung vorab berechnen (auf Basis Brutto-Preis)
// Gesondert für Mahngebühren-Rechnungen: Fehlt der Artikel und damit der MwSt-Satz,
// muss dieser als 0 % behandelt werden. Zudem wird der Bruttobetrag direkt
// mitberechnet, damit fehlende MwSt-Werte keine Auswirkung auf die Gesamtsumme haben.
$totalsStmt = $pdo->query("
    SELECT rp.rechnung_id,
        SUM(rp.einzelpreis * rp.menge * rp.rabatt / 100) AS rabatt,
        SUM(
            (rp.einzelpreis * rp.menge - rp.einzelpreis * rp.menge * rp.rabatt / 100) / (1 + COALESCE(a.mwst,0) / 100)
        ) AS netto,
        SUM(
            CASE WHEN a.mwst = 7 THEN
                (rp.einzelpreis * rp.menge - rp.einzelpreis * rp.menge * rp.rabatt / 100)
                - ((rp.einzelpreis * rp.menge - rp.einzelpreis * rp.menge * rp.rabatt / 100) / 1.07)
            ELSE 0 END
        ) AS mwst7,
        SUM(
            CASE WHEN a.mwst = 19 THEN
                (rp.einzelpreis * rp.menge - rp.einzelpreis * rp.menge * rp.rabatt / 100)
                - ((rp.einzelpreis * rp.menge - rp.einzelpreis * rp.menge * rp.rabatt / 100) / 1.19)
            ELSE 0 END
        ) AS mwst19,
        SUM(rp.einzelpreis * rp.menge - rp.einzelpreis * rp.menge * rp.rabatt / 100) AS brutto
    FROM rechnungspositionen rp
    LEFT JOIN artikel a ON rp.artikelnummer = a.aid
    GROUP BY rp.rechnung_id
");

$allInvoiceTotals = [];
foreach ($totalsStmt as $row) {
    $rid = (int)$row['rechnung_id'];
    $rab = (float)$row['rabatt'];
    $net = (float)$row['netto'];
    $mw7 = (float)$row['mwst7'];
    $mw19 = (float)$row['mwst19'];
    $brutto = (float)$row['brutto'];
    if (!$brutto) {
        $brutto = $net + $mw7 + $mw19;
    }
    $allInvoiceTotals[$rid] = [
        'rabatt' => $rab,
        'netto' => $net,
        'mwst7' => $mw7,
        'mwst19' => $mw19,
        'brutto' => $brutto
    ];
}


    // Gesamtsummen der Gutschriften berechnen
$creditStmt = $pdo->query("SELECT gp.rechnung_id,
    SUM(gp.einzelpreis * gp.menge * gp.rabatt / 100) AS rabatt,
    SUM((gp.einzelpreis * gp.menge - gp.einzelpreis * gp.menge * gp.rabatt / 100) / (1 + COALESCE(a.mwst, 0) / 100)) AS netto,
    SUM(
        CASE WHEN a.mwst = 7 THEN 
            (gp.einzelpreis * gp.menge - gp.einzelpreis * gp.menge * gp.rabatt / 100)
            - ((gp.einzelpreis * gp.menge - gp.einzelpreis * gp.menge * gp.rabatt / 100) / 1.07)
        ELSE 0 END
    ) AS mwst7,
    SUM(
        CASE WHEN a.mwst = 19 THEN 
            (gp.einzelpreis * gp.menge - gp.einzelpreis * gp.menge * gp.rabatt / 100)
            - ((gp.einzelpreis * gp.menge - gp.einzelpreis * gp.menge * gp.rabatt / 100) / 1.19)
        ELSE 0 END
    ) AS mwst19
    FROM gutschriftpositionen gp
    LEFT JOIN artikel a ON gp.artikelnummer = a.aid
    GROUP BY gp.rechnung_id");
    $allCreditTotals = [];
    foreach ($creditStmt as $row) {
        $rid = (int)$row['rechnung_id'];
        $rab = (float)$row['rabatt'];
        $net = (float)$row['netto'];
        $mw7 = (float)$row['mwst7'];
        $mw19 = (float)$row['mwst19'];
        $allCreditTotals[$rid] = [
            'rabatt' => $rab,
            'netto' => $net,
            'mwst7' => $mw7,
            'mwst19' => $mw19,
            'brutto' => $net + $mw7 + $mw19
        ];
    }

    // Summe aller Gutschriften pro Rechnung ermitteln
    $creditTotalsByInvoice = [];
    foreach ($gutschriften as $g) {
        $pid = (int)($g['rechnung_id'] ?? 0);
        if ($pid > 0) {
            $creditTotalsByInvoice[$pid] = ($creditTotalsByInvoice[$pid] ?? 0)
                + ($allCreditTotals[$g['id']]['brutto'] ?? 0);
        }
    }

    // Angebote laden inklusive verknüpfter Rechnungsnummer
    $sqlO = 'SELECT o.*, r.rechnungsnummer AS rechnungs_num, r.mahnstufe'
        . ' FROM angebote o'
        . ' LEFT JOIN rechnungen r ON o.rechnung_id = r.id';
    $condsO = [];
    $paramsO = [];
    if ($von !== '') { $condsO[] = 'o.erstellt_am >= ?'; $paramsO[] = $von; }
    if ($bis !== '') { $condsO[] = 'o.erstellt_am <= ?'; $paramsO[] = $bis; }
    if ($condsO) { $sqlO .= ' WHERE ' . implode(' AND ', $condsO); }
    $sqlO .= ' ORDER BY id DESC';
    $oStmt = $pdo->prepare($sqlO);
    $oStmt->execute($paramsO);
    $angebote = $oStmt->fetchAll(PDO::FETCH_ASSOC);

    // Mapping Angebot ID -> zugehörige Rechnungsnummer
    $invoiceNumByOffer = [];
    foreach ($angebote as $o) {
        $invoiceNumByOffer[$o['id']] = $o['rechnungs_num'] ?? '';
    }

    // Mapping Rechnungs-ID -> zugehörige Angebotsnummer
    $offerNumByInvoice = [];
    foreach ($angebote as $o) {
        if (!empty($o['rechnung_id'])) {
            $offerNumByInvoice[$o['rechnung_id']] = $o['angebotsnummer'];
        }
    }

    $positionenByOffer = [];
    $stmt = $pdo->prepare('SELECT * FROM angebotpositionen WHERE rechnung_id = ? ORDER BY id');
    foreach ($angebote as $o) {
        $stmt->execute([$o['id']]);
        $positionenByOffer[$o['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $offerStmt = $pdo->query("
        SELECT ap.rechnung_id,
            SUM(ap.einzelpreis * ap.menge * ap.rabatt / 100) AS rabatt,
            SUM((ap.einzelpreis * ap.menge - ap.einzelpreis * ap.menge * ap.rabatt / 100) / (1 + COALESCE(a.mwst, 0) / 100)) AS netto,
            SUM(
                CASE WHEN a.mwst = 7 THEN 
                    (ap.einzelpreis * ap.menge - ap.einzelpreis * ap.menge * ap.rabatt / 100) 
                    - ((ap.einzelpreis * ap.menge - ap.einzelpreis * ap.menge * ap.rabatt / 100) / 1.07)
                ELSE 0 END
            ) AS mwst7,
            SUM(
                CASE WHEN a.mwst = 19 THEN 
                    (ap.einzelpreis * ap.menge - ap.einzelpreis * ap.menge * ap.rabatt / 100) 
                    - ((ap.einzelpreis * ap.menge - ap.einzelpreis * ap.menge * ap.rabatt / 100) / 1.19)
                ELSE 0 END
            ) AS mwst19
        FROM angebotpositionen ap
        LEFT JOIN artikel a ON ap.artikelnummer = a.aid
        GROUP BY ap.rechnung_id
    ");

    $allOfferTotals = [];
    foreach ($offerStmt as $row) {
        $rid = (int)$row['rechnung_id'];
        $rab = (float)$row['rabatt'];
        $net = (float)$row['netto'];
        $mw7 = (float)$row['mwst7'];
        $mw19 = (float)$row['mwst19'];
        $allOfferTotals[$rid] = [
            'rabatt' => $rab,
            'netto' => $net,
            'mwst7' => $mw7,
            'mwst19' => $mw19,
            'brutto' => $net + $mw7 + $mw19
        ];
    }

    $statusLabels = [
        'angelegt'  => 'angelegt',
        'geprueft'  => 'geprüft',
        'versendet' => 'versendet',
        'bezahlt'   => 'bezahlt',
        'storniert' => 'storniert',
        'geloescht' => 'gelöscht'
    ];

    $mahnLabels = [
        0 => '',
        1 => 'Zahlungserinnerung',
        2 => '1. Mahnstufe',
        3 => '2. Mahnstufe',
        4 => '3. Mahnstufe'
    ];

    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename=rechnungen.csv');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM für korrekte Anzeige in Excel
        fputcsv(
            $out,
            ['Typ','Nummer','Gesamt','Raten','Sepa','Empfänger','Datum','Bezahlt am','Archiviert','Status','Kopftext','Korr.ver.','Gutschrift'],
            ';'
        );
        foreach ($rechnungen as $r) {
            $sum = number_format(invoiceTotalBrutto($pdo, $allInvoiceTotals, (int)$r['id']), 2, ',', '.') . ' €';
            $status = $statusLabels[$r['status']] ?? $r['status'];
            $corrText = '';
            $isReminderRow = $reminderById[$r['id']] ?? false;
            if ($r['status'] === 'geloescht') {
                $corrText = trim($r['geloescht_grund'] ?? '') !== ''
                    ? 'Löschgrund: ' . $r['geloescht_grund']
                    : 'gelöscht';
            } elseif (!empty($parentNums[$r['id']] ?? '') && !$isReminderRow) {
                $corrText = 'RK für RE: ' . ($parentNums[$r['id']] ?? '');
            } elseif (!empty($offerNumByInvoice[$r['id']] ?? '')) {
                $corrText = 'Rechnung aus Angebot: ' . $offerNumByInvoice[$r['id']];
            } else {
                $corrNr = $corrections[$r['id']] ?? null;
                if ($corrNr) {
                    $corrText = 'RK durch RE: ' . $corrNr;
                }
            }
            $credits = implode(', ', $creditsByInvoice[$r['id']] ?? []);
            fputcsv(
                $out,
                [
                    'Rechnung',
                    $r['rechnungsnummer'],
                    $sum,
                    $r['ratenzahlung'] ? 'Ja' : '',
                    $r['sepa'] ? 'Ja' : '',
                    $r['empfaenger'],
                    date('d.m.Y', strtotime($r['erstellt_am'])),
                    date('d.m.Y', strtotime($r['bezahldatum'])),
                    $r['archiviert_am'] ? date('d.m.Y', strtotime($r['archiviert_am'])) : '',
                    $status,
                    $r['kopftext'],
                    $corrText,
                    $credits
                ],
                ';'
            );
        }
        foreach ($gutschriften as $g) {
            $sum = '-' . number_format((float)($allCreditTotals[$g['id']]['brutto'] ?? 0), 2, ',', '.') . ' €';
            $status = $statusLabels[$g['status']] ?? $g['status'];
            $corrText = 'GS für RE: ' . ($g['parent_num'] ?? '');
            fputcsv(
                $out,
                [
                    'Gutschrift',
                    $g['gutschriftnummer'],
                    $sum,
                    '',
                    '',
                    $g['empfaenger'],
                    date('d.m.Y', strtotime($g['erstellt_am'])),
                    date('d.m.Y', strtotime($g['bezahldatum'])),
                    '',
                    $status,
                    $g['kopftext'],
                    $corrText,
                    ''
                ],
                ';'
            );
        }
        foreach ($angebote as $o) {
            $sum = number_format((float)($allOfferTotals[$o['id']]['brutto'] ?? 0), 2, ',', '.') . ' €';
            $corrText = '';
            if (!empty($invoiceNumByOffer[$o['id']])) {
                $corrText = 'Rechnung Nummer: ' . $invoiceNumByOffer[$o['id']];
            }
            fputcsv(
                $out,
                [
                    'Angebot',
                    $o['angebotsnummer'],
                    $sum,
                    '',
                    '',
                    $o['empfaenger'],
                    date('d.m.Y', strtotime($o['erstellt_am'])),
                    date('d.m.Y', strtotime($o['bezahldatum'])),
                    '',
                    $statusLabels[$o['status']] ?? $o['status'],
                    $o['kopftext'],
                    $corrText,
                    ''
                ],
                ';'
            );
        }
        fclose($out);
        exit;
    }

} catch (PDOException $e) {
    if ($e->getCode() === '42S02') {
        die("Fehlende Tabelle 'rechnungen'. Bitte die Datei verbandssoftware.sql importieren.");
    }
    throw $e;
}

if ($success === '' && isset($_GET['kunde_angelegt'])) {
    $success = 'Kunde angelegt';
}

// Daten für Kundenanlage-Modal
$einzelmitglieder = $pdo->query("SELECT id, vorname, nachname, email FROM kunden WHERE einzelmitglied=1 ORDER BY nachname, vorname")->fetchAll(PDO::FETCH_ASSOC);
$beitragArtikelStmt = $pdo->prepare("SELECT a.id, a.aid, a.kurzbez FROM artikel a JOIN artikel_artikelgruppen ag ON ag.artikel_id=a.id JOIN artikelgruppen g ON g.id=ag.artikelgruppe_id WHERE g.bezeichnung='Einzelmitglieder' AND a.aktiv=1 ORDER BY a.aid, a.kurzbez");
$beitragArtikelStmt->execute();
$beitragArtikel = $beitragArtikelStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>AN – Rechnungen – GS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.3.2/css/fixedHeader.bootstrap5.min.css">


<style>
  body {
    background-color: #f1f3f5;
    padding-top: 60px;
  }
  .card {
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  .card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  }
  .card .card-header {
    background-color: rgb(205, 207, 210);
    font-weight: 600;
    border-top-left-radius: 1rem;
    border-top-right-radius: 1rem;
  }
  .card-header.bg-light {
    background-color: rgb(205, 207, 210) !important;
  }
  .table-rounded {
    border-radius: 10px;
    overflow: hidden;
  }
  .table-rounded thead th:first-child { border-top-left-radius: 10px; }
  .table-rounded thead th:last-child { border-top-right-radius: 10px; }
  .table-rounded tbody tr:last-child td:first-child { border-bottom-left-radius: 10px; }
  .table-rounded tbody tr:last-child td:last-child { border-bottom-right-radius: 10px; }
  .table thead.table-light th {
    background-color: rgb(205, 207, 210) !important;
  }
  /* Schriftgröße der Übersichtstabelle etwas verkleinern */
  #rechnungstabelle th,
  #rechnungstabelle td {
    font-size: 0.8125rem;
  }

  .color-btn[data-scheme="F1"] {background-color: #81d4fa !important; } 
  .color-btn[data-scheme="F2"] {background-color: #ff8a65 !important; } 
  .color-btn[data-scheme="F3"] {background-color: #ce93d8 !important; } 

  /* farbliche Hinterlegung des Status in der Status-Spalte */
  .status-cell { font-weight: 600; }

  body.color-F1 tr.row-status-angelegt td  { background-color: #e0f7fa !important; }  /* Helles Türkis */
  body.color-F1 tr.row-status-geprueft td  { background-color: #b2ebf2 !important; }  /* Sanftes Blaugrün */
  body.color-F1 tr.row-status-versendet td { background-color: #81d4fa !important; }  /* Himmelblau */
  body.color-F1 tr.row-status-bezahlt td   { background-color: #4fc3f7 !important; }  /* Sattes Ozeanblau */
  body.color-F1 tr.row-status-storniert td { background-color: #ffcdd2 !important; }  /* Zartes Rot */
  body.color-F1 tr.row-status-geloescht td  { background-color: #ffb3b3 !important; }  /* Rot für gelöschte Rechnungen */

  body.color-F2 tr.row-status-angelegt td  { background-color: #fff3e0 !important; }  /* Zartes Apricot, Sonnenaufgang */
  body.color-F2 tr.row-status-geprueft td  { background-color: #ffccbc !important; }  /* Helles Korallenorange */
  body.color-F2 tr.row-status-versendet td { background-color: #ff8a65 !important; }  /* Sattes Sonnenuntergangsorange */
  body.color-F2 tr.row-status-bezahlt td   { background-color: #f06292 !important; }  /* Glühendes Pinkrot */
  body.color-F2 tr.row-status-storniert td { background-color: #ffab91 !important; }  /* Pastellorange-Rot */
  body.color-F2 tr.row-status-geloescht td  { background-color: #ffb3b3 !important; }  /* Rot für gelöschte Rechnungen */

  body.color-F3 tr.row-status-angelegt td  { background-color: #f3e5f5 !important; }  /* Zartes Lavendel */
  body.color-F3 tr.row-status-geprueft td  { background-color: #e1bee7 !important; }  /* Helles Fliederlila */
  body.color-F3 tr.row-status-versendet td { background-color: #ce93d8 !important; }  /* Mittel-Lila */
  body.color-F3 tr.row-status-bezahlt td   { background-color: #ba68c8 !important; }  /* Sattes Lila mit Tief*
  body.color-F3 tr.row-status-storniert td { background-color: #f8bbd0 !important; }  /* Zartes Pink */
  body.color-F3 tr.row-status-geloescht td  { background-color: #ffb3b3 !important; }  /* Rot für gelöschte Rechnungen */



    /* Nur die Verknüpfungs-Buttons orange einfärben */
  .btn-outline-success.link-btn {
      background-color: #fd7e14 !important; /* Orange */
      border-color: #fd7e14 !important;
      color: #fff;               /* Icon/Text weiß */
  }

  .btn-outline-success.link-btn:hover,
  .btn-outline-success.link-btn:focus {
      background-color: #e96b05 !important; /* dunkleres Orange beim Hover */
      border-color: #e96b05 !important;
      color: #fff;
  }

  .kopftext-cell {
  max-width: 15ch;       /* ca. 35 Zeichenbreite */
  white-space: normal;   /* Umbruch erlauben */
  word-break: break-word; /* Umbruch innerhalb von langen Wörtern */
}


  /* Hervorhebung korrigierter Rechnungen wurde deaktiviert. */


  @media (max-width: 575.98px) {
    #rechnung-cards .card {
      font-size: 0.75rem;
    }
  }
  datalist option {
    white-space: pre-line;
  }
  #positionsTable .mwst-cell {
    min-width: 105px;
  }
  /* adjust column widths in positions table */
  #positionsTable .gesamt-cell {
    min-width: 120px;
  }
  #positionsTable .qty-input {
    max-width: 70px;
  }
  #positionsTable .price-input {
    max-width: 90px;
  }
  .help-btn {
    width: 24px;
    height: 24px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background-color: #6db4ff;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }

  tr.shown td.toggle-row {
    background-color: #e9f5ff;
    font-weight: 600;
  }

  .toggle-row {
    cursor: pointer;
    white-space: nowrap;
  }
#rechnungstabelle tbody tr {
  cursor: pointer;
}
#rechnungstabelle td {
  vertical-align: top;
}

table.dataTable.fixedHeader-floating {
  z-index: 1055 !important;
  background-color: #f8f9fa !important;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1) !important;
  border-bottom-left-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
}

table.dataTable.fixedHeader-floating th {
  font-size: 0.8125rem !important;
  color: #212529 !important;
  background-color: #a1a8afff !important;
  font-weight: 700 !important;
  border-bottom-left-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
}

  .modal-dialog-scrollable .modal-content {
    max-height: 90vh;
    overflow: hidden;
  }

  .modal-dialog-scrollable .modal-body {
    overflow-y: auto;
    max-height: calc(90vh - 130px);
    padding-right: 1rem;
  }


</style>
</head>
<body class="color-<?= htmlspecialchars($colorScheme) ?>">
<nav class="navbar navbar-light bg-white border-bottom fixed-top shadow-sm px-3">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
      <img src="images/logo_lkvn.png" alt="LKVN Logo" style="height: 36px; margin-right: 12px;">
      <span class="text-muted"><i class="bi bi-file-earmark-text me-1"></i>AN – Rechnungen – GS – <?= htmlspecialchars($version) ?> – <?= htmlspecialchars($rolle_name) ?>  <?= htmlspecialchars($admin_name) ?></span>
    </div>
            <?php include "nav.php"; ?>
  </div>
</nav>
<div class="container mt-5 py-4">
<h1 class="page-title"><i class="bi bi-file-earmark-text me-2"></i>Angebote - Rechnungen - Gutschriften
  <button type="button" class="btn help-btn ms-2 p-0" data-bs-toggle="modal" data-bs-target="#hilfeRechnungenModal" aria-label="Hilfe">
    <i class="bi bi-info-lg"></i>
  </button>
</h1>
<?php if ($error): ?>
<div class="alert alert-danger"><?= $error_is_html ? $error : htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>
<div class="card mb-4">
<div class="card-body">
<div class="d-flex justify-content-between align-items-start mb-3">
  <h2 class="card-title mb-0">
    <?php if ($offerEditId): ?>Angebot bearbeiten [<?= htmlspecialchars($offerNrEdit) ?>]<?php elseif ($editId): ?>Rechnung bearbeiten [<?= htmlspecialchars($editNr) ?>]<?php else: ?>Neue Rechnung<?php endif; ?>
  </h2>
  <div class="d-flex align-items-center">
    <form method="post" action="rechnungen.php" class="m-0 me-2">
      <input type="hidden" name="action" value="cancel">
      <button type="submit" class="btn btn-sm btn-secondary" id="cancelButton">Abbrechen</button>
    </form>
    <button type="button" class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#createModal">Kunden anlegen</button>
    <a href="#" id="customerBtn" class="btn btn-sm btn-primary d-none">Zum Kunden</a>
  </div>
</div>
<div id="invoiceHeader">
<div class="row mb-3">
  <div class="col-md-3">
    <label class="form-label">Empfänger</label>
    <input type="text" name="empfaenger" list="kundenListe" class="form-control" value="<?= htmlspecialchars($empfaenger_edit) ?>" form="saveForm" required>
    <input type="hidden" name="empfaenger_id" id="empfaengerIdInput" form="saveForm" value="<?= htmlspecialchars($empfaenger_id_edit) ?>">
    <datalist id="kundenListe">
    <?php foreach ($kunden as $k): ?>
    <option value="<?= htmlspecialchars(trim($k['vorname'].' '.$k['nachname'].' - '.$k['email'])) ?>" data-id="<?= $k['id'] ?>" data-name="<?= htmlspecialchars(trim($k['vorname'].' '.$k['nachname'])) ?>" data-sepa="<?= (int)$k['sepa_zustimmung'] ?>" data-ref="<?= htmlspecialchars($k['sepamandatsreferenz'] ?? '', ENT_QUOTES) ?>">
        <?= htmlspecialchars(trim($k['vorname'].' '.$k['nachname'].' - '.$k['email'])) ?>
    </option>
    <?php endforeach; ?>
    </datalist>
  </div>
  <div class="col-md-3">
    <label class="form-label">Erstellungsdatum</label>
    <input type="date" name="datum" class="form-control" value="<?= htmlspecialchars($datum_edit) ?>" form="saveForm" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select" form="saveForm">
      <option value="angelegt" <?= $status_edit==='angelegt' ? 'selected' : '' ?>>angelegt</option>
      <option value="geprueft" <?= $status_edit==='geprueft' ? 'selected' : '' ?>>geprüft</option>
      <option value="versendet" <?= $status_edit==='versendet' ? 'selected' : '' ?>>versendet</option>
      <option value="bezahlt" <?= $status_edit==='bezahlt' ? 'selected' : '' ?>>bezahlt</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Zahlungsdatum</label>
    <input type="date" name="bezahldatum" class="form-control" value="<?= htmlspecialchars($bezahldatum_edit) ?>" form="saveForm" required>
  </div>
</div>
<div class="row mb-3 align-items-end">
  <div class="col-md-3">
    <label class="form-label d-block">SEPA-Lastschrift</label>
    <span id="sepaIcon" class="fs-4 <?= $sepa_edit ? 'text-success' : 'text-danger' ?>"><?= $sepa_edit ? '&#10004;' : '&#10006;' ?></span>
    <input type="hidden" name="sepa" id="sepaHidden" form="saveForm" value="<?= $sepa_edit ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">SEPA-Referenz</label>
    <input type="text" name="sepa_ref" id="sepaRefInput" class="form-control" value="<?= $sepa_edit ? htmlspecialchars($sepa_ref_edit) : '' ?>" form="saveForm" readonly>
  </div>
</div>
<div class="mb-3">
  <label class="form-label">Rechnungskopftext</label>
  <textarea name="kopftext" class="form-control" rows="2" form="saveForm" placeholder="<?= htmlspecialchars(KOPFTEXT_PLACEHOLDER) ?>" required><?= htmlspecialchars($kopftext_edit) ?></textarea>
</div>
</div>
<form method="post" class="mb-4" id="addForm">
<input type="hidden" name="action" value="add">
<?php if ($editId): ?>
<input type="hidden" name="invoice_id" value="<?= $editId ?>">
<?php endif; ?>
<input type="hidden" name="empfaenger" id="addFormEmpfaenger">
<input type="hidden" name="empfaenger_id" id="addFormEmpfaengerId">
<input type="hidden" name="datum" id="addFormDatum">
<input type="hidden" name="status" id="addFormStatus">
<input type="hidden" name="kopftext" id="addFormKopftext">
<input type="hidden" name="fusstext" id="addFormFusstext">
<input type="hidden" name="bezahldatum" id="addFormBezahldatum">
<input type="hidden" name="sepa" id="addFormSepa">
<input type="hidden" name="sepa_ref" id="addFormSepaRef">
<div class="row g-2 align-items-end">
<div class="col-md-2">
<label class="form-label small mb-0">Artikelnr.</label>
<input type="text" name="artikelnummer" list="artikelListe" class="form-control form-control-sm" placeholder="Artikel">
<datalist id="artikelListe">
<?php foreach ($artikel as $a): ?>
<option value="<?= htmlspecialchars($a['artikelnummer']) ?>" data-preis="<?= number_format((float)$a['preis'], 2, '.', '') ?>">
  <?= htmlspecialchars($a['kurzbez'] . "\n\n" . '( MwSt. ' . number_format((float)$a['mwst'], 0, ',', '.') . ' % )') ?>
</option>
<?php endforeach; ?>
</datalist>
</div>
<div class="col-md-1">
<label class="form-label small mb-0">Menge</label>
<input type="number" name="menge" class="form-control form-control-sm" min="1" value="1">
</div>
<div class="col-md-2">
<label class="form-label small mb-0">Preis</label>
<input type="number" step="0.01" name="einzelpreis" class="form-control form-control-sm" placeholder="Preis">
</div>
<div class="col-md-2">
<label class="form-label small mb-0">Bemerkung</label>
<input type="text" name="bemerkung" class="form-control form-control-sm" placeholder="Bemerkung">
</div>
<div class="col-md-1">
  <div class="form-check">
    <input class="form-check-input" type="checkbox" id="addRaten" name="ratenzahlung" value="1">
    <label class="form-check-label small" for="addRaten">Raten</label>
  </div>
</div>
<div class="col-12 raten-field d-none">
  <div class="form-label small mb-1">Monate</div>
  <div class="d-flex flex-wrap gap-1">
<?php
  $monNames=['Jan.','Feb.','Mrz.','Apr.','Mai','Jun.','Jul.','Aug.','Sep.','Okt.','Nov.','Dez.'];
  foreach($monNames as $idx=>$name):
?>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="checkbox" name="raten_monate[]" id="rateMon<?= $idx ?>" value="<?= $idx+1 ?>">
      <label class="form-check-label small" for="rateMon<?= $idx ?>"><?= $name ?></label>
    </div>
<?php endforeach; ?>
  </div>
</div>
<div class="col-md-1">
  <button type="submit" class="btn btn-primary btn-sm" aria-label="Position hinzufügen">
    <i class="bi bi-plus-lg"></i>
  </button>
</div>
</div>
</form>
<?php if (!empty($_SESSION['invoice_positions'])): ?>
<form method="post" id="saveForm">
<input type="hidden" name="action" value="<?= $offerEditId ? 'offer_save' : 'save' ?>">
<?php if ($editId): ?>
<input type="hidden" name="invoice_id" value="<?= $editId ?>">
<?php endif; ?>
<?php if ($offerEditId): ?>
<input type="hidden" name="offer_id" value="<?= $offerEditId ?>">
<?php endif; ?>
<table id="positionsTable" class="table table-bordered align-middle table-rounded">
<thead class="table-light">
<tr>
  <th>Pos.</th>
  <th>Artikelnummer</th>
  <th>Bezeichnung</th>
  <th>Bemerkung</th>
  <th class="text-end">Preis/Stück</th>
  <th class="text-end">Menge</th>
  <th class="text-end">Rabatt %</th>
  <th class="text-end">MwSt</th>
  <th class="text-end gesamt-cell">Gesamt</th>
  <th></th>
</tr>
</thead>
<tbody>
<?php foreach ($_SESSION['invoice_positions'] as $idx => $pos): ?>
<tr data-mwst="<?= number_format((float)($pos['mwst'] ?? 0),2,'.','') ?>">
  <td><?= $idx + 1 ?></td>
  <td><?= htmlspecialchars($pos['artikelnummer']) ?></td>
  <td><?= htmlspecialchars($pos['kurzbez']) ?><br><small class="text-muted"><?= htmlspecialchars($pos['langbez']) ?></small></td>
  <td>
    <input type="text" form="saveForm" name="bemerkungen[<?= $idx ?>]" value="<?= htmlspecialchars($pos['bemerkung'] ?? '') ?>" class="form-control form-control-sm">
  </td>
  <td>
    <input type="number" step="0.01" form="saveForm" name="preise[<?= $idx ?>]" value="<?= number_format((float)$pos['einzelpreis'],2,'.','') ?>" class="form-control form-control-sm text-end price-input">
  </td>
  <td>
    <input type="number" min="1" form="saveForm" name="mengen[<?= $idx ?>]" value="<?= (int)$pos['menge'] ?>" class="form-control form-control-sm text-end qty-input">
  </td>
  <td>
    <input type="number" min="0" max="100" step="0.01" form="saveForm" name="rabatte[<?= $idx ?>]" value="<?= number_format((float)($pos['rabatt'] ?? 0),2,'.','') ?>" class="form-control form-control-sm text-end rabatt-input">
  </td>
  <td class="text-end mwst-cell">
    <span class="mwst-wert"></span><br><small class="mwst-prozent text-muted"></small>
  </td>
  <td class="text-end gesamt-cell">
    <span class="gesamt"></span><br><small class="rabattwert text-muted"></small>
  </td>
    <td>
      <button type="submit" form="removeForm<?= $idx ?>" class="btn btn-sm btn-danger">✖</button>
    </td>
  </tr>
<?php endforeach; ?>
</tbody>
</table>
<div class="text-end fw-bold" id="invoiceTotals">
  <div>Rabatt gesamt: <span id="totalRabatt">0,00 €</span></div>
  <div>Zwischensumme: <span id="totalNetto">0,00 €</span></div>
  <div id="mwstRow" class="d-none">
    <span id="mwst7part" class="d-none">MwSt 7%: <span id="totalMwst7">0,00 €</span></span>
    <span id="mwst19part" class="d-none"> | MwSt 19%: <span id="totalMwst19">0,00 €</span></span>
    <span id="mwstSumPart" class="d-none"> | Summe MwSt: <span id="totalMwst">0,00 €</span></span>
  </div>
  <div>Gesamtsumme: <span id="totalBrutto">0,00 €</span></div>
</div>
<div class="mb-3 mt-3">
  <label class="form-label">Fußtext</label>
  <textarea name="fusstext" class="form-control" rows="2" form="saveForm"><?= htmlspecialchars($fusstext_edit) ?></textarea>
</div>
<?php if ($offerEditId): ?>
<button form="saveForm" type="submit" class="btn btn-success mt-2">Angebot speichern</button>
<!--<button form="offerForm" type="submit" class="btn btn-secondary mt-2 ms-2">Als Rechnung speichern</button>-->
<?php else: ?>
<button form="saveForm" type="submit" class="btn btn-success mt-2">Rechnung speichern</button>
<button form="offerForm" type="submit" class="btn btn-secondary mt-2 ms-2">Angebot erstellen</button>
<?php endif; ?>
</form>
<form method="post" id="offerForm" class="d-inline offerForm">
  <input type="hidden" name="action" value="<?= $offerEditId ? 'offer_convert' : 'offer' ?>">
  <?php if ($editId): ?>
  <input type="hidden" name="invoice_id" value="<?= $editId ?>">
  <?php endif; ?>
  <?php if ($offerEditId): ?>
  <input type="hidden" name="offer_id" value="<?= $offerEditId ?>">
  <?php endif; ?>
  <input type="hidden" name="empfaenger">
  <input type="hidden" name="empfaenger_id">
  <input type="hidden" name="datum">
  <input type="hidden" name="status">
  <input type="hidden" name="kopftext">
  <input type="hidden" name="fusstext">
  <input type="hidden" name="bezahldatum">
  <input type="hidden" name="sepa">
  <input type="hidden" name="sepa_ref">
</form>
<?php foreach ($_SESSION['invoice_positions'] as $idx => $_): ?>
<form method="post" id="removeForm<?= $idx ?>" class="d-inline removeForm">
  <input type="hidden" name="action" value="remove">
  <input type="hidden" name="index" value="<?= $idx ?>">
  <?php if ($editId): ?>
  <input type="hidden" name="invoice_id" value="<?= $editId ?>">
  <?php endif; ?>
  <?php if ($offerEditId): ?>
  <input type="hidden" name="offer_id" value="<?= $offerEditId ?>">
  <?php endif; ?>
  <input type="hidden" name="empfaenger">
  <input type="hidden" name="empfaenger_id">
  <input type="hidden" name="datum">
  <input type="hidden" name="status">
  <input type="hidden" name="kopftext">
  <input type="hidden" name="fusstext">
  <input type="hidden" name="bezahldatum">
  <input type="hidden" name="sepa">
  <input type="hidden" name="sepa_ref">
</form>
<?php endforeach; ?>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const dInput = document.querySelector('input[name="datum"]');
  const bInput = document.querySelector('input[name="bezahldatum"]');
  const empInput = document.querySelector('input[name="empfaenger"]');
  const sepaHidden = document.getElementById('sepaHidden');
  const sepaRef  = document.getElementById('sepaRefInput');
  const sepaIcon = document.getElementById('sepaIcon');
  const customerBtn = document.getElementById('customerBtn');
  const empIdInput = document.getElementById('empfaengerIdInput');
  const customerModalEl = document.getElementById('customerModal');
  const customerForm = document.getElementById('customerForm');
  const custId = document.getElementById('cust_id');
  const saveCustomerBtn = document.getElementById('saveCustomerBtn');
  const customerModal = customerModalEl ? new bootstrap.Modal(customerModalEl) : null;
  const custFieldNames = ['email','firmenname','vorname','nachname','geschlecht','strasse','hausnummer','plz','ort','mobilnummer','vereinsmitgliedschaft','beitrag_aid','parent_id','eintrittsdatum','austrittsdatum','geburtsdatum','notizen','sepamandatsreferenz','kreditinstitut','kontoinhaber','iban','bic','unterschrift_datum','unterschrift_ort'];
  const custCheckNames = ['einzelmitglied','familieneinzelmitglied','sepa_zustimmung','aktiv'];
  const custElems = {};
  [...custFieldNames, ...custCheckNames].forEach(f => { custElems[f] = document.getElementById('cust_' + f); });
  const artikelInput = document.querySelector('input[name="artikelnummer"]');
  const priceInput   = document.querySelector('input[name="einzelpreis"]');
  if(artikelInput && priceInput){
    artikelInput.addEventListener('change', () => {
      const opt = document.querySelector(`#artikelListe option[value="${artikelInput.value}"]`);
      if(opt && opt.dataset.preis){
        priceInput.value = parseFloat(opt.dataset.preis).toFixed(2);
      }
    });
  }
  if(dInput && !dInput.value){
    dInput.value = new Date().toISOString().split('T')[0];
  }
  if(bInput && !bInput.value){
    let dt = dInput ? new Date(dInput.value) : new Date();
    dt.setDate(dt.getDate() + 14);
    bInput.value = dt.toISOString().split('T')[0];
  }
  dInput?.addEventListener('change', () => {
    if(bInput){
      let dt = new Date(dInput.value);
      if(!isNaN(dt)){ dt.setDate(dt.getDate() + 14); bInput.value = dt.toISOString().split('T')[0]; }
    }
  });

  const getCustomerOption = () => {
    if(empIdInput && empIdInput.value){
      return document.querySelector(`#kundenListe option[data-id="${empIdInput.value}"]`);
    }
    return null;
  };

  const updateCustomerBtn = () => {
    if(!customerBtn || !empInput) return;
    const opt = getCustomerOption();
    if(opt && opt.dataset.id){
      customerBtn.classList.remove('d-none');
    }else{
      customerBtn.classList.add('d-none');
    }
  };

  const updateSepaState = () => {
    if(!empInput) return;
    const opt = getCustomerOption();
    const sepaVal = opt ? parseInt(opt.dataset.sepa || '0', 10) : 0;
    if(sepaHidden) sepaHidden.value = sepaVal;
    if(sepaRef){
      sepaRef.value = sepaVal === 1 && opt ? (opt.dataset.ref || '') : '';
    }
    if(sepaIcon){
      sepaIcon.textContent = sepaVal === 1 ? '✔' : '✖';
      sepaIcon.classList.toggle('text-success', sepaVal === 1);
      sepaIcon.classList.toggle('text-danger', sepaVal !== 1);
    }
  };

  const ratenChk = document.getElementById('addRaten');
  const ratenFields = document.querySelectorAll('.raten-field');
  const toggleRaten = () => {
    if(!ratenChk) return;
    ratenFields.forEach(f => f.classList.toggle('d-none', !ratenChk.checked));
  };
  toggleRaten();
  ratenChk?.addEventListener('change', toggleRaten);

  updateSepaState();
  updateCustomerBtn();
  empInput?.addEventListener('input', () => {
    const opt = document.querySelector(`#kundenListe option[value="${empInput.value}"]`);
    if(opt){
      empIdInput.value = opt.dataset.id || '';
      empInput.value = opt.dataset.name || empInput.value;
    } else {
      empIdInput.value = '';
    }
    updateSepaState();
    updateCustomerBtn();
  });
  customerBtn?.addEventListener('click', e => {
    e.preventDefault();
    if(!empIdInput || !customerModal || !custId) return;
    const kid = empIdInput.value;
    if(kid){
      fetch(`get_kunde.php?id=${kid}`)
        .then(res => res.json())
        .then(data => {
          if(data.success && data.kunde){
            custId.value = data.kunde.id;
            custFieldNames.forEach(f => { const el = custElems[f]; if(el) el.value = data.kunde[f] || ''; });
            custCheckNames.forEach(f => { const el = custElems[f]; if(el) el.checked = parseInt(data.kunde[f] || '0',10) === 1; });
            customerModal.show();
          }
        });
    }
  });

  saveCustomerBtn?.addEventListener('click', () => {
    if(!customerForm) return;
    const fd = new FormData(customerForm);
    fetch('update_kunde.php', {method:'POST', body: fd})
      .then(res => res.json())
      .then(data => {
        if(data.success){
          const kid = fd.get('id');
          const opt = document.querySelector(`#kundenListe option[data-id="${kid}"]`);
          if(opt){
            const name = `${fd.get('vorname')} ${fd.get('nachname')}`.trim();
            opt.dataset.name = name;
            opt.dataset.sepa = fd.get('sepa_zustimmung') ? '1' : '0';
            opt.dataset.ref = fd.get('sepamandatsreferenz') || '';
            opt.value = `${name} - ${fd.get('email')}`;
            if(empIdInput.value === kid){
              empInput.value = name;
              updateSepaState();
            }
          }
          customerModal.hide();
        }else{
          alert('Speichern fehlgeschlagen');
        }
      });
  });

});
function updateTotals() {
  const rabattEl = document.getElementById('totalRabatt');
  const nettoEl = document.getElementById('totalNetto');
  const mwst7El = document.getElementById('totalMwst7');
  const mwst19El = document.getElementById('totalMwst19');
  const bruttoEl = document.getElementById('totalBrutto');
  const mwstRow = document.getElementById('mwstRow');
  const mwst7Part = document.getElementById('mwst7part');
  const mwst19Part = document.getElementById('mwst19part');
  const mwstSumEl = document.getElementById('totalMwst');
  const mwstSumPart = document.getElementById('mwstSumPart');
  if(!rabattEl || !nettoEl || !mwst7El || !mwst19El || !bruttoEl || !mwstRow || !mwstSumEl) return;

  let sum = 0;
  let sumDisc = 0;
  let sumMwst = 0;
  let sumMwst7 = 0;
  let sumMwst19 = 0;
document.querySelectorAll('#positionsTable tbody tr').forEach(row => {
  const price = parseFloat(row.querySelector('.price-input').value) || 0;
  const qty = parseInt(row.querySelector('.qty-input').value) || 0;
  const disc = parseFloat(row.querySelector('.rabatt-input').value) || 0;
  const mwst = parseFloat(row.dataset.mwst || '0');

  const totalBrutto = price * qty;
  const discVal = totalBrutto * disc / 100;
  const finalBrutto = totalBrutto - discVal;

  // Netto = Brutto / (1 + MwSt%)
  const netto = finalBrutto / (1 + mwst / 100);
  const mwstVal = finalBrutto - netto;

  sumDisc += discVal;
  sum += netto;
  sumMwst += mwstVal;
  if (mwst === 7) sumMwst7 += mwstVal;
  if (mwst === 19) sumMwst19 += mwstVal;

  const mwstCell = row.querySelector('.mwst-wert');
  const mwstPct = row.querySelector('.mwst-prozent');
  if (mwstCell) mwstCell.textContent = mwstVal.toFixed(2).replace('.', ',') + ' €';
  if (mwstPct) mwstPct.textContent = mwst.toFixed(0) + ' %';

  row.querySelector('.rabattwert').textContent = '-' + discVal.toFixed(2).replace('.', ',') + ' €';
  row.querySelector('.gesamt').textContent = finalBrutto.toFixed(2).replace('.', ',') + ' €';
});

  document.getElementById('totalRabatt').textContent = '-' + sumDisc.toFixed(2).replace('.', ',') + ' €';
  document.getElementById('totalNetto').textContent = sum.toFixed(2).replace('.', ',') + ' €';
  if(sumMwst > 0){
    mwstRow.classList.remove('d-none');
    mwst7El.textContent = sumMwst7.toFixed(2).replace('.', ',') + ' €';
    mwst19El.textContent = sumMwst19.toFixed(2).replace('.', ',') + ' €';
    mwstSumEl.textContent = sumMwst.toFixed(2).replace('.', ',') + ' €';
    if(mwstSumPart) mwstSumPart.classList.remove('d-none');
    if(mwst7Part) mwst7Part.classList.toggle('d-none', sumMwst7 === 0);
    if(mwst19Part) mwst19Part.classList.toggle('d-none', sumMwst19 === 0);
    document.getElementById('totalBrutto').textContent = (sum + sumMwst).toFixed(2).replace('.', ',') + ' €';
  }else{
    mwstRow.classList.add('d-none');
    mwst7El.textContent = '0,00 €';
    mwst19El.textContent = '0,00 €';
    mwstSumEl.textContent = '0,00 €';
    if(mwstSumPart) mwstSumPart.classList.add('d-none');
    document.getElementById('totalBrutto').textContent = sum.toFixed(2).replace('.', ',') + ' €';
  }
}
document.addEventListener('input', e => {
  if(e.target.classList.contains('price-input') || e.target.classList.contains('qty-input') || e.target.classList.contains('rabatt-input')) {
    updateTotals();
  }
});
updateTotals();

function appendPositionFields(form){
  document.querySelectorAll('#positionsTable tbody tr').forEach(row => {
    ['preise','mengen','rabatte','bemerkungen'].forEach(name => {
      const inp = row.querySelector(`[name^="${name}["]`);
      if(inp){
        const m = inp.name.match(/\[(\d+)\]/);
        const idx = m ? m[1] : '';
        const hid = document.createElement('input');
        hid.type = 'hidden';
        hid.name = `${name}[${idx}]`;
        hid.value = inp.value;
        form.appendChild(hid);
      }
    });
  });
}
document.getElementById('addForm')?.addEventListener('submit', function(){
  document.getElementById('addFormEmpfaenger').value = document.querySelector('input[name="empfaenger"]').value;
  document.getElementById('addFormEmpfaengerId').value = document.getElementById('empfaengerIdInput').value;
  document.getElementById('addFormDatum').value = document.querySelector('input[name="datum"]').value;
  document.getElementById('addFormStatus').value = document.querySelector('select[name="status"]').value;
  document.getElementById('addFormKopftext').value = document.querySelector('textarea[name="kopftext"]').value;
  document.getElementById('addFormFusstext').value = document.querySelector('textarea[name="fusstext"]').value;
  document.getElementById('addFormBezahldatum').value = document.querySelector('input[name="bezahldatum"]').value;
  document.getElementById('addFormSepa').value = document.getElementById('sepaHidden').value;
  document.getElementById('addFormSepaRef').value = document.getElementById('sepaRefInput').value;
  appendPositionFields(this);
});
document.querySelectorAll('.removeForm').forEach(f => {
  f.addEventListener('submit', function(){
    this.querySelector('input[name="empfaenger"]').value = document.querySelector('input[name="empfaenger"]').value;
    this.querySelector('input[name="empfaenger_id"]').value = document.getElementById('empfaengerIdInput').value;
    this.querySelector('input[name="datum"]').value = document.querySelector('input[name="datum"]').value;
    this.querySelector('input[name="status"]').value = document.querySelector('select[name="status"]').value;
    this.querySelector('input[name="kopftext"]').value = document.querySelector('textarea[name="kopftext"]').value;
    this.querySelector('input[name="fusstext"]').value = document.querySelector('textarea[name="fusstext"]').value;
    this.querySelector('input[name="bezahldatum"]').value = document.querySelector('input[name="bezahldatum"]').value;
    this.querySelector('input[name="sepa"]').value = document.getElementById('sepaHidden').value;
    this.querySelector('input[name="sepa_ref"]').value = document.getElementById('sepaRefInput').value;
    appendPositionFields(this);
  });
});
document.getElementById('offerForm')?.addEventListener('submit', function(){
  this.querySelector('input[name="empfaenger"]').value = document.querySelector('input[name="empfaenger"]').value;
  this.querySelector('input[name="empfaenger_id"]').value = document.getElementById('empfaengerIdInput').value;
  this.querySelector('input[name="datum"]').value = document.querySelector('input[name="datum"]').value;
  this.querySelector('input[name="status"]').value = document.querySelector('select[name="status"]').value;
  this.querySelector('input[name="kopftext"]').value = document.querySelector('textarea[name="kopftext"]').value;
  this.querySelector('input[name="fusstext"]').value = document.querySelector('textarea[name="fusstext"]').value;
  this.querySelector('input[name="bezahldatum"]').value = document.querySelector('input[name="bezahldatum"]').value;
  this.querySelector('input[name="sepa"]').value = document.getElementById('sepaHidden').value;
  this.querySelector('input[name="sepa_ref"]').value = document.getElementById('sepaRefInput').value;
  appendPositionFields(this);
});
</script>
</div>
</div>
<div class="card mb-4" id="auswertungCard">
<div class="card-body">
<h2 class="mb-3">Auswertung sichtbare Rechnungen</h2>
<div class="d-flex flex-wrap gap-4 mb-3">
  <div>Gesamtsumme: <span id="auswertungGesamt">0,00 €</span></div>
  <div>Rabatt: <span id="auswertungRabatt">0,00 €</span></div>
  <div>MwSt 7%: <span id="auswertungMwst7">0,00 €</span></div>
  <div>MwSt 19%: <span id="auswertungMwst19">0,00 €</span></div>
</div>
</div>
</div>

<div class="card" id="existingInvoices">
<div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h2 class="mb-0">Bestehende Rechnungen &amp; Korrekturen</h2>
    <div class="d-flex gap-2">
        <div class="btn-group" role="group" aria-label="Farbschema">
            <button type="button" class="btn btn-outline-secondary btn-sm color-btn" data-scheme="F1">F1</button>
            <button type="button" class="btn btn-outline-secondary btn-sm color-btn" data-scheme="F2">F2</button>
            <button type="button" class="btn btn-outline-secondary btn-sm color-btn" data-scheme="F3">F3</button>
        </div>
        <a href="?export=csv" class="btn btn-outline-secondary btn-sm" title="CSV exportieren">
            <i class="bi bi-download"></i>
        </a>
        <button id="resetFilterBtn" type="button" class="btn btn-outline-secondary btn-sm" title="Filter zurücksetzen">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button id="toggleColumnFilter" type="button" class="btn btn-outline-secondary btn-sm" title="Spalten wählen">
            <i class="bi bi-filter"></i>
        </button>
    </div>
</div>

  <form id="filterzeile-rechnungen" method="get" class="d-flex flex-wrap align-items-end gap-1 mb-3">
    <div class="d-flex flex-column">
        <label for="empfaenger-filter" class="form-label small text-muted mb-1">Empfänger</label>
        <input type="text" id="empfaenger-filter" class="form-control form-control-sm" style="min-width:160px;" placeholder="Empfänger">
    </div>
    <div class="d-flex flex-column">
        <label for="status-filter" class="form-label small text-muted mb-1">Status</label>
        <select id="status-filter" class="form-select form-select-sm" style="min-width:160px;">
            <option value="">Alle</option>
            <option value="angelegt">angelegt</option>
            <option value="geprueft">geprüft</option>
            <option value="versendet">versendet</option>
            <option value="bezahlt">bezahlt</option>
            <option value="geloescht">gelöscht</option>
        </select>
    </div>
    <div class="d-flex flex-column">
        <label for="kopftext-filter" class="form-label small text-muted mb-1">Kopftext</label>
        <input type="text" id="kopftext-filter" class="form-control form-control-sm" style="min-width:160px;" placeholder="Kopftext">
    </div>
    <div class="d-flex flex-column">
        <label for="jahr-select" class="form-label small text-muted mb-1">Geschäftsjahr</label>
        <select name="jahr" id="jahr-select" class="form-select form-select-sm" style="min-width:120px;">
            <option value="">Alle</option>
            <?php foreach ($jahre as $j): ?>
            <option value="<?= $j ?>" <?= ($jahr !== '' && (int)$jahr === (int)$j) ? 'selected' : '' ?>><?= $j ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="d-flex flex-column">
        <label for="von-datum" class="form-label small text-muted mb-1">Von</label>
        <input type="date" name="von" id="von-datum" class="form-control form-control-sm" value="<?= htmlspecialchars($von) ?>">
    </div>
    <div class="d-flex flex-column">
        <label for="bis-datum" class="form-label small text-muted mb-1">Bis</label>
        <input type="date" name="bis" id="bis-datum" class="form-control form-control-sm" value="<?= htmlspecialchars($bis) ?>">
    </div>
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="korr-filter">
        <label class="form-check-label ms-2" for="korr-filter">Korrekturen einblenden</label>
    </div>
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="gutsch-filter">
        <label class="form-check-label ms-2" for="gutsch-filter">Gutschriften einblenden</label>
    </div>
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="angebot-filter">
        <label class="form-check-label ms-2" for="angebot-filter">Angebote einblenden</label>
    </div>
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="angebot-only-filter">
        <label class="form-check-label ms-2" for="angebot-only-filter">Nur Angebote</label>
    </div>

    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="open-filter" <?= $showOpen ? 'checked' : '' ?>>
        <label class="form-check-label ms-2" for="open-filter">Nur offene RE</label>
    </div>
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="deletedonly-filter" <?= $showDeletedOnly ? 'checked' : '' ?> />
        <label class="form-check-label ms-2" for="deletedonly-filter">Nur gelöschte</label>
    </div>
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="deleted-hide-filter" <?= !empty($hideDeleted) ? 'checked' : ''  ?> />
        <label class="form-check-label ms-2" for="deleted-hide-filter">Gelöschte ausblenden</label>
    </div>
    <div class="form-check d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="sepa-filter" <?= $showSepa ? 'checked' : '' ?> />
        <label class="form-check-label ms-2" for="sepa-filter">Sepa</label>
    </div>
    <!-- DataTables-Suchfeld und -Längenauswahl werden per JS hier eingefügt -->
</form>
<div class="card mb-3 d-none" id="columnFilterSection">
  <div class="card-header fw-bold text-muted small">
    <i class="bi bi-layout-three-columns me-1"></i>Spalten ein-/ausblenden
  </div>
  <div class="card-body">
    <div class="row row-cols-2 row-cols-md-4 g-2" id="columnFilterContent">
      <!-- Checkboxen werden dynamisch per JS eingefügt -->
    </div>
  </div>
</div>

<table class="table table-sm table-rounded align-middle" id="rechnungstabelle">

<thead class="table-light">
<tr>
  <th>Verkn.</th>
  <th><input type="checkbox" id="check-all"></th>
  <th>Nr.</th>
  <th class="text-end">Gesamt</th>
  <th>Raten</th>
  <th>Sepa</th>
  <th>Empfänger</th>
  <th>Erstelldatum</th>
  <th>Zahldatum</th>
  <th>Archiviert</th>
  <th>Status</th>
  <th>Mahnstufe</th>
  <th>Kopftext</th>
  <th>Korrekturver.</th>
  <th>Aktion</th>
  <th>Dok.</th>
  <th>E-Mail</th>
  <th>Bearb.</th>
  <th>Löschen</th>
  <th class="d-none">KorrFlag</th>
</tr>
</thead>
<tbody>
<?php foreach ($rechnungen as $r): ?>
<?php
    $rowClass = isset($corrections[$r['id']])
        ? 'corr-corrected'
        : ((isset($parentNums[$r['id']]) && !($reminderById[$r['id']] ?? false)) ? 'corr-correction' : '');
    $rowClass .= ' row-status-' . $r['status'];
    $tooltip = '';
    $deleteReason = '';
    if ($r['status'] === 'geloescht') {
        $delName = trim(($r['del_vorname'] ?? '') . ' ' . ($r['del_nachname'] ?? ''));
        $delDate = $r['geloescht_am'] ? date('d.m.Y', strtotime($r['geloescht_am'])) : '';
        $grund = $r['geloescht_grund'] ?? '';
        $deleteReason = $grund;
        $tooltip = 'Gelöscht';
        if ($delName !== '') $tooltip .= ' von ' . $delName;
        if ($delDate) $tooltip .= ' am ' . $delDate;
        if ($grund !== '') $tooltip .= ' – Grund: ' . $grund;
    }
    $tooltip .= ($tooltip ? ' – ' : '') . 'Doppelklick: Rechnung bearbeiten';
?>
  <tr class="<?= trim($rowClass) ?>" title="<?= htmlspecialchars($tooltip) ?>"
    data-nr="<?= htmlspecialchars($r['rechnungsnummer']) ?>"
    data-status="<?= htmlspecialchars($r['status']) ?>"
    data-sepa="<?= (int)$r['sepa'] ?>"
    data-mahnstufe="<?= (int)$r['mahnstufe'] ?>"
    <?php if (!empty($parentNums[$r['id']] ?? '')): ?>data-parent="<?= htmlspecialchars($parentNums[$r['id']]) ?>"<?php endif; ?>
    <?php if (!empty($corrections[$r['id']] ?? '')): ?>data-corrections="<?= htmlspecialchars($corrections[$r['id']]) ?>"<?php endif; ?>
    <?php if (!empty($offerNumByInvoice[$r['id']] ?? '')): ?>data-offer="<?= htmlspecialchars($offerNumByInvoice[$r['id']]) ?>"<?php endif; ?>
    <?php if ($canEdit && !in_array($r['status'], ['versendet','bezahlt','geloescht'])): ?>data-edit-link="rechnungen.php?edit=<?= $r['id'] ?>"<?php endif; ?>>
<?php $showLink = !empty($parentNums[$r['id']] ?? '') || !empty($corrections[$r['id']] ?? '') || !empty($creditsByInvoice[$r['id']] ?? []) || !empty($offerNumByInvoice[$r['id']] ?? ''); ?>
<td>
  <?php if ($showLink): ?>
  <button type="button" class="btn btn-sm btn-outline-success link-btn" title="Verknüpfte Belege anzeigen"><i class="bi bi-link-45deg"></i></button>

  <?php endif; ?>
</td>
<td><input type="checkbox" class="invoice-check" value="<?= $r['id'] ?>"></td>
<td class="toggle-row" data-id="<?= $r['id'] ?>">
  <i class="bi bi-chevron-right me-1"></i>
  <span class="toggle-label"><?= htmlspecialchars($r['rechnungsnummer']) ?></span>
</td>
<td class="text-end">
  <?= number_format(invoiceTotalBrutto($pdo, $allInvoiceTotals, (int)$r['id']), 2, ',', '.') ?> €
</td>
<td class="text-center"><?= $r['ratenzahlung'] ? '&#10004;' : '' ?></td>
<td class="text-center"><?= $r['sepa'] ? '&#10004;' : '' ?></td>



<td>
  <?= htmlspecialchars($r['empfaenger']) ?>
  <?php
    $email = '';
    foreach ($kunden as $k) {
        if ((int)$k['id'] === (int)$r['empfaenger_id']) {
            $email = $k['email'] ?? '';
            break;
        }
    }
    if ($email === '' && !empty($r['buchungen_id'])) {
        $q = $pdo->prepare('
            SELECT COALESCE(k.email, b.email) AS email
            FROM buchungen b
            LEFT JOIN kunden k ON k.id = b.kunde_id
            WHERE b.id = ?
            LIMIT 1
        ');
        $q->execute([$r['buchungen_id']]);
        $email = $q->fetchColumn() ?: '';
    }
    if ($email):
  ?>
    <br><small class="text-muted" style="font-size: 0.75em;"><?= htmlspecialchars($email) ?></small>
  <?php endif; ?>
</td>




<td><?= htmlspecialchars(date('d.m.Y', strtotime($r['erstellt_am']))) ?></td>
<td><?= htmlspecialchars(date('d.m.Y', strtotime($r['bezahldatum']))) ?></td>
<td><?= $r['archiviert_am'] ? htmlspecialchars(date('d.m.Y', strtotime($r['archiviert_am']))) : '' ?></td>
<td class="status-cell status-<?= htmlspecialchars($r['status']) ?>">
  <?= htmlspecialchars($statusLabels[$r['status']] ?? $r['status']) ?>
</td>
<?php
    $isReminderRow = $reminderById[$r['id']] ?? false;
    $mahnColText = $isReminderRow
        ? 'Mahnung für Rechnung ' . ($parentNums[$r['id']] ?? '')
        : ($mahnLabels[$r['mahnstufe']] ?? '');
?>
<td><?= htmlspecialchars($mahnColText) ?></td>
<td class="kopftext-cell"><?= htmlspecialchars($r['kopftext']) ?></td>
<td>
  <?php
    $remarks = [];
    if ($r['status'] === 'geloescht') {
        $reason = trim($r['geloescht_grund'] ?? '');
        $remarks[] = $reason !== '' ? 'Löschgrund: ' . htmlspecialchars($reason) : 'gelöscht';
    } elseif (!empty($parentNums[$r['id']] ?? '') && !$isReminderRow) {
        $remarks[] = 'RK für RE: ' . htmlspecialchars($parentNums[$r['id']]);
    } elseif (!empty($offerNumByInvoice[$r['id']] ?? '')) {
        $remarks[] = 'Rechnung aus Angebot: ' . htmlspecialchars($offerNumByInvoice[$r['id']]);
    } else {
        $corrNr = $corrections[$r['id']] ?? null;
        if ($corrNr) {
            $remarks[] = 'RK durch RE: ' . htmlspecialchars($corrNr);
        }
    }
    $credits = implode(', ', $creditsByInvoice[$r['id']] ?? []);
    if ($credits !== '') {
        $remarks[] = 'GS: ' . htmlspecialchars($credits);
    }
    echo implode('<br>', $remarks);
  ?>
</td>
<td>
  <?php
    $invoiceTotal = invoiceTotalBrutto($pdo, $allInvoiceTotals, (int)$r['id']);
    $credited     = $creditTotalsByInvoice[$r['id']] ?? 0;
  ?>
  <?php if ($credited < $invoiceTotal): ?>
    <a href="gutschriften_anlegen.php?parent=<?= $r['id'] ?>" class="btn btn-sm btn-success">GS</a>
  <?php endif; ?>
  <a href="rechnungskorrektur_anlegen.php?parent=<?= $r['id'] ?>" class="btn btn-sm btn-danger">RK</a>
</td>
<?php
  $pdfLink = 'rechnung_pdf.php?id=' . $r['id'];
  if (!empty($r['archiviert_am'])) {
      $archPath = 'archiv/rechnungen/' . rawurlencode($r['rechnungsnummer']) . '.pdf';
      if (file_exists(__DIR__ . '/' . $archPath)) {
          $pdfLink = $archPath;
      }
  } elseif (in_array($r['status'], ['versendet', 'bezahlt'])) {
      $sendPath = 'rechnungen/' . rawurlencode($r['rechnungsnummer']) . '.pdf';
      if (file_exists(__DIR__ . '/' . $sendPath)) {
          $pdfLink = $sendPath;
      }
  }
  $reminderLink = 'zahlungserinnerung_pdf.php?id=' . $r['id'];
  $reminderPath = 'mahnungen/' . rawurlencode($r['rechnungsnummer']) . '_mahnung1.pdf';
  if (file_exists(__DIR__ . '/' . $reminderPath)) {
      $reminderLink = $reminderPath;
  }
?>
<td>
  <a href="<?= htmlspecialchars($pdfLink) ?>" class="btn btn-sm btn-outline-secondary pdf-link" target="_blank">PDF</a>
  <?php if ($r['status'] === 'versendet'): ?>
    <a href="<?= htmlspecialchars($reminderLink) ?>" class="btn btn-sm btn-warning pdf-link" target="_blank">ZE</a>
  <?php endif; ?>
</td>
<td>
  <?php
  $kundenEmail = '';
  if (!empty($r['empfaenger_id'])) {
      foreach ($kunden as $k) {
          if ((int)$k['id'] === (int)$r['empfaenger_id']) {
              $kundenEmail = $k['email'] ?? '';
              break;
          }
      }
  }
  if ($kundenEmail === '' && !empty($r['buchungen_id'])) {
      $q = $pdo->prepare('
          SELECT COALESCE(k.email, b.email) AS email
          FROM buchungen b
          LEFT JOIN kunden k ON k.id = b.kunde_id
          WHERE b.id = ?
          LIMIT 1
      ');
      $q->execute([$r['buchungen_id']]);
      $kundenEmail = $q->fetchColumn() ?: '';
  }

  if ($kundenEmail):
    $mailto = 'mailto:' . rawurlencode($kundenEmail) .
              '?subject=' . rawurlencode("Ihre Rechnung " . $r['rechnungsnummer']) .
              '&body=' . rawurlencode("Sehr geehrte Damen und Herren,\n\nanbei erhalten Sie Ihre Rechnung " . $r['rechnungsnummer'] . ".");
  ?>
    <a href="<?= htmlspecialchars($mailto) ?>" class="btn btn-sm btn-outline-primary" title="E-Mail senden">
      <i class="bi bi-envelope-fill"></i>
    </a>
  <?php endif; ?>
</td>
<td>
  <?php if ($canEdit): ?>
    <?php if (!in_array($r['status'], ['versendet','bezahlt','geloescht'])): ?>
      <a href="rechnungen.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-primary" title="Bearbeiten"><i class="bi bi-pencil"></i></a>
    <?php else: ?>
      <span class="text-muted"><i class="bi bi-lock-fill"></i></span>
    <?php endif; ?>
  <?php endif; ?>
</td>
<td>
<?php if ($isSuperAdmin && in_array($r['status'], ['angelegt','geprueft'])): ?>
<button type="button" class="btn btn-sm btn-danger delete-rechnung-btn" data-id="<?= $r['id'] ?>"><i class="bi bi-trash"></i></button>
<?php endif; ?>
</td>
<td class="d-none"><?= isset($corrections[$r['id']]) ? '1' : '0' ?></td>
</tr>
<?php endforeach; ?>
<?php foreach ($gutschriften as $g): ?>
<?php $rowClass = 'credit-row row-status-' . $g['status']; ?>
<tr class="<?= $rowClass ?>" data-nr="<?= htmlspecialchars($g['gutschriftnummer']) ?>" data-parent="<?= htmlspecialchars($g['parent_num'] ?? '') ?>" data-sepa="0" data-status="<?= htmlspecialchars($g['status']) ?>">
  <td><button type="button" class="btn btn-sm btn-outline-success link-btn" title="Verknüpfte Belege anzeigen"><i class="bi bi-link-45deg"></i></button></td>
  <td><input type="checkbox" class="credit-check" value="<?= $g['id'] ?>"></td>
  <td class="toggle-row" data-id="<?= $g['id'] ?>">
    <i class="bi bi-chevron-right me-1"></i>
    <span class="toggle-label"><?= htmlspecialchars($g['gutschriftnummer']) ?></span>
  </td>
  <td class="text-end">-<?= number_format((float)($allCreditTotals[$g['id']]['brutto'] ?? 0), 2, ',', '.') ?> €</td>
  <td></td>
  <td></td>


<td>
  <?= htmlspecialchars($g['empfaenger']) ?>
  <?php
    $email = '';
    foreach ($kunden as $k) {
        if ((int)$k['id'] === (int)($g['empfaenger_id'] ?? 0)) {
            $email = $k['email'] ?? '';
            break;
        }
    }
    if ($email):
  ?>
    <br><small class="text-muted" style="font-size: 0.75em;"><?= htmlspecialchars($email) ?></small>
  <?php endif; ?>
</td>







  <td><?= htmlspecialchars(date('d.m.Y', strtotime($g['erstellt_am']))) ?></td>
  <td><?= htmlspecialchars(date('d.m.Y', strtotime($g['bezahldatum']))) ?></td>
  <td></td>
  <td class="status-cell status-<?= htmlspecialchars($g['status']) ?>">
    <?= htmlspecialchars($statusLabels[$g['status']] ?? $g['status']) ?>
  </td>
  <td></td>
  <td class="kopftext-cell"><?= htmlspecialchars($g['kopftext']) ?></td>
  <td><?php if (!empty($g['parent_num'])): ?>GS für RE: <?= htmlspecialchars($g['parent_num']) ?><?php endif; ?></td>
  <td>
    <a href="rechnungskorrektur_anlegen.php?<?= !empty($g['rechnung_id']) ? 'parent=' . $g['rechnung_id'] : 'gutschrift=' . $g['id'] ?>" class="btn btn-sm btn-danger">RK</a>
  </td>
  <td><a href="gutschrift_pdf.php?id=<?= $g['id'] ?>" class="btn btn-sm btn-outline-secondary pdf-link" target="_blank">PDF</a></td>
  <td>
    <?php
      $kundenEmail = '';
      if (!empty($g['empfaenger_id'])) {
          foreach ($kunden as $k) {
              if ((int)$k['id'] === (int)$g['empfaenger_id']) {
                  $kundenEmail = $k['email'] ?? '';
                  break;
              }
          }
      }
      if ($kundenEmail === '' && !empty($g['rechnung_id'])) {
          $q = $pdo->prepare('
              SELECT COALESCE(k.email, b.email) AS email
              FROM rechnungen r
              LEFT JOIN kunden k ON k.id = r.empfaenger_id
              LEFT JOIN buchungen b ON b.id = r.buchungen_id
              WHERE r.id = ?
              LIMIT 1
          ');
          $q->execute([$g['rechnung_id']]);
          $kundenEmail = $q->fetchColumn() ?: '';
      }
      if ($kundenEmail):
        $bodyText = "Sehr geehrte Damen und Herren,\n\nanbei erhalten Sie Ihre Gutschrift " . $g['gutschriftnummer'] . ".";
        $mailto = 'mailto:' . rawurlencode($kundenEmail) .
                  '?subject=' . rawurlencode("Ihre Gutschrift " . $g['gutschriftnummer']) .
                  '&body=' . rawurlencode($bodyText);
    ?>
      <a href="<?= htmlspecialchars($mailto) ?>" class="btn btn-sm btn-outline-primary" title="E-Mail senden">
        <i class="bi bi-envelope-fill"></i>
      </a>
    <?php endif; ?>
  </td>
  <td>
    <?php if (!in_array($g['status'], ['versendet','bezahlt'])): ?>
      <a href="gutschriften_anlegen.php?edit=<?= $g['id'] ?>" class="btn btn-sm btn-primary" title="Bearbeiten"><i class="bi bi-pencil"></i></a>
    <?php else: ?>
      <span class="text-muted"><i class="bi bi-lock-fill"></i></span>
    <?php endif; ?>
  </td>
  <td>
    <?php if ($isSuperAdmin && in_array($g['status'], ['angelegt','geprueft'])): ?>
      <button type="button" class="btn btn-sm btn-danger delete-gutschrift-btn" data-id="<?= $g['id'] ?>"><i class="bi bi-trash"></i></button>
    <?php endif; ?>
  </td>
  <td class="d-none">0</td>
</tr>
<?php endforeach; ?>
<?php foreach ($angebote as $o): ?>
<?php $rowClass = 'offer-row row-status-' . $o['status']; ?>
<?php $invNum = $invoiceNumByOffer[$o['id']] ?? ''; ?>
<tr class="<?= $rowClass ?>" data-nr="<?= htmlspecialchars($o['angebotsnummer']) ?>" data-status="<?= htmlspecialchars($o['status']) ?>" <?php if($invNum): ?>data-parent="<?= htmlspecialchars($invNum) ?>"<?php endif; ?>>
  <td>
    <?php if ($invNum): ?>
    <button type="button" class="btn btn-sm btn-outline-success link-btn" title="Verknüpfte Belege anzeigen"><i class="bi bi-link-45deg"></i></button>
    <?php endif; ?>
  </td>
  <td><input type="checkbox" class="offer-check" value="<?= $o['id'] ?>"></td>
  <td class="toggle-row" data-id="<?= $o['id'] ?>">
    <i class="bi bi-chevron-right me-1"></i>
    <span class="toggle-label"><?= htmlspecialchars($o['angebotsnummer']) ?></span>
  </td>
  <td class="text-end"><?= number_format((float)($allOfferTotals[$o['id']]['brutto'] ?? 0), 2, ',', '.') ?> €</td>
  <td></td>
  <td></td>
  
  
  <td>
    <?= htmlspecialchars($o['empfaenger']) ?>
    <?php
      $email = '';
      foreach ($kunden as $k) {
          if ((int)$k['id'] === (int)($o['empfaenger_id'] ?? 0)) {
              $email = $k['email'] ?? '';
              break;
          }
      }
      if ($email):
    ?>
      <br><small class="text-muted" style="font-size: 0.75em;"><?= htmlspecialchars($email) ?></small>
    <?php endif; ?>
  </td>





  <td><?= htmlspecialchars(date('d.m.Y', strtotime($o['erstellt_am']))) ?></td>
  <td><?= htmlspecialchars(date('d.m.Y', strtotime($o['bezahldatum']))) ?></td>
  <td></td>
  <td class="status-cell status-<?= htmlspecialchars($o['status']) ?>">
    <?= htmlspecialchars($statusLabels[$o['status']] ?? $o['status']) ?>
  </td>
  <td></td>
  <td class="kopftext-cell"><?= htmlspecialchars($o['kopftext']) ?></td>
  <td>
    <?php if ($invNum): ?>Rechnung Nummer: <?= htmlspecialchars($invNum) ?><?php endif; ?>
  </td>
  <td>
    <?php if (!$invNum): ?>
      <a href="rechnungen.php?convert_offer=<?= $o['id'] ?>" class="btn btn-sm btn-outline-primary">RE</a>
    <?php endif; ?>
  </td>
  <td><a href="angebot_pdf.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-secondary pdf-link" target="_blank">PDF</a></td>
  <td></td>
  <td>
    <?php if (!$invNum && !in_array($o['status'], ['versendet','bezahlt'])): ?>
      <a href="rechnungen.php?offer_edit=<?= $o['id'] ?>" class="btn btn-sm btn-primary" title="Bearbeiten"><i class="bi bi-pencil"></i></a>
    <?php else: ?>
      <span class="text-muted"><i class="bi bi-lock-fill"></i></span>
    <?php endif; ?>
  </td>
  <td></td>
  <td class="d-none">0</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<div id="rechnung-cards" class="d-none"></div>
<div class="mb-2">
  <select id="batch-status" class="form-select form-select-sm d-inline-block w-auto">
    <option value="">Status ändern...</option>
    <option value="versendet">versendet</option>
    <option value="bezahlt">bezahlt</option>
  </select>
  <button id="batch-apply" type="button" class="btn btn-primary btn-sm ms-2">Anwenden</button>
</div>
<div class="d-flex align-items-center gap-2">
  <button id="batch-rechnung" type="button" class="btn btn-primary btn-sm" title="Rechnung für Auswahl (RE)">RE</button>
  <button id="batch-gutschrift" type="button" class="btn btn-success btn-sm" title="Gutschrift für Auswahl (GS)">GS</button>
  <button id="batch-korrektur" type="button" class="btn btn-danger btn-sm" title="Rechnungskorrektur für Auswahl (RK)">RK</button>
  <?php if ($isSuperAdmin): ?>
  <button id="batch-delete" type="button" class="btn btn-danger btn-sm" title="Löschen">
    <i class="bi bi-trash"></i><span class="visually-hidden">Löschen</span>
  </button>
  <?php endif; ?>
  <button id="generate-multiple-pdfs" type="button" class="btn btn-outline-secondary btn-sm">PDFs erstellen</button>
  <button id="generate-reminders" type="button" class="btn btn-warning btn-sm d-none">ZE</button>
  <button id="advance-mahnstufe" type="button" class="btn btn-warning btn-sm d-none">Nächste Mahnstufe einleiten</button>
  <button id="sepa-export" type="button" class="btn btn-info btn-sm">SEPA XML</button>
  <div id="sepa-status" class="text-info small"></div>
</div>


<div class="mt-3 row">
  <div class="col-12 col-md-4 mb-2">
    <span class="me-3"><span class="badge bg-primary">RE</span> Rechnung aus Angebot erstellen</span>
  </div>
  <div class="col-12 col-md-4 mb-2">
    <span class="me-3"><span class="badge bg-success">GS</span> Gutschrift erstellen/anzeigen</span>
  </div>
  <div class="col-12 col-md-4 mb-2">
    <span class="me-3"><span class="badge bg-danger">RK</span> Rechnungskorrektur erstellen/anzeigen</span>
  </div>
  <div class="col-12 col-md-4 mb-2">
    <span class="me-3"><span class="badge bg-warning text-dark">ZE</span> Zahlungserinnerung erstellen/anzeigen</span>
  </div>
  <div class="col-12 col-md-4 mb-2">
    <span class="me-3"><span class="badge btn-sm btn-outline-success link-btn"><i class="bi bi-link-45deg"></i></span> Verknüpfungen anzeigen</span>
  </div>
</div>


  <small class="d-block mt-4 text-muted">Version <?= htmlspecialchars($version) ?> – © 2025 cw</small>
</div>

</div>
<div class="modal fade" id="deleteReasonModal" tabindex="-1" aria-labelledby="deleteReasonLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteReasonLabel">Löschgrund</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
      <div class="modal-body">
        <textarea id="deleteReasonInput" class="form-control" rows="3"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="deleteReasonConfirm">Löschen</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
      </div>
</div>
</div>
</div>
<?php include 'hilfe.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/colreorder/1.6.2/js/dataTables.colReorder.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.3.2/js/dataTables.fixedHeader.min.js"></script>
<script>
  const creditTotalsByInvoice = <?= json_encode($creditTotalsByInvoice, JSON_NUMERIC_CHECK) ?>;
</script>
<script>
const allInvoiceTotals = <?= json_encode($allInvoiceTotals, JSON_NUMERIC_CHECK) ?>;
const allCreditTotals  = <?= json_encode($allCreditTotals, JSON_NUMERIC_CHECK) ?>;
const IS_SUPERADMIN   = <?= json_encode($isSuperAdmin) ?>;
const CAN_EDIT       = <?= json_encode($canEdit) ?>;
const PREF_SHOWCORR   = <?= json_encode($showCorrections) ?>;
const PREF_SHOWCRED   = <?= json_encode($showCredits) ?>;
const PREF_SHOWOFFER  = <?= json_encode($showOffers) ?>;
const PREF_SHOWOFFERONLY = <?= json_encode($showOffersOnly) ?>;
const PREF_SHOWDEL    = <?= json_encode($showDelete) ?>;
const PREF_SHOWLINKS  = <?= json_encode($showLinks) ?>;
const PREF_SHOWOPEN  = <?= json_encode($showOpen) ?>;
const PREF_SHOWSEPA  = <?= json_encode($showSepa) ?>;
const PREF_SHOWDELONLY = <?= json_encode($showDeletedOnly) ?>;
const PREF_HIDEDEL  = <?= json_encode($hideDeleted) ?>;
const PREF_COLUMNS    = <?= json_encode($visibleColumns, JSON_NUMERIC_CHECK) ?>;
const PREF_ORDER     = <?= json_encode($filterState['order'] ?? null, JSON_NUMERIC_CHECK) ?>;
const PREF_LENGTH    = <?= json_encode($pageLength, JSON_NUMERIC_CHECK) ?>;
const PREF_COLOR     = <?= json_encode($colorScheme) ?>;
const invoicePositions = <?= json_encode($positionenByInvoice, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const creditPositions  = <?= json_encode($positionenByCredit, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
const offerPositions   = <?= json_encode($positionenByOffer, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
let linkFilterNr = null;
const urlParams = new URLSearchParams(window.location.search);
const paramNr = urlParams.get('filter_nr');
if(paramNr) linkFilterNr = paramNr;

function buildCards(){
  const container = $('#rechnung-cards').empty();
  $('#rechnungstabelle tbody tr').each(function(){
    const c = $(this).children();
    const card = $('<div class="card mb-2"></div>').addClass(this.className);
    if(this.title) card.attr('title', this.title);
    const body = $('<div class="card-body"></div>').appendTo(card);
    body.append('<div class="form-check mb-2">'+c.eq(1).html()+' <strong>Nr.:</strong> '+c.eq(2).text()+'</div>');
    body.append('<div><strong>Ges.:</strong> '+c.eq(3).text()+'</div>');
    body.append('<div><strong>Raten:</strong> '+c.eq(4).html()+'</div>');
    body.append('<div><strong>Sepa:</strong> '+c.eq(5).html()+'</div>');
    body.append('<div><strong>Empf.:</strong> '+c.eq(6).text()+'</div>');
    body.append('<div><strong>Dat.:</strong> '+c.eq(7).text()+'</div>');
    body.append('<div><strong>Bez.:</strong> '+c.eq(8).text()+'</div>');
    body.append('<div><strong>Arch.:</strong> '+c.eq(9).text()+'</div>');
    const statusClass = c.eq(10).attr('class');
    body.append('<div><strong>Stat.:</strong> <span class="'+statusClass+'">'+c.eq(10).html()+'</span></div>');
    body.append('<div><strong>Mahnstufe:</strong> '+c.eq(11).text()+'</div>');
    body.append('<div><strong>Kopf:</strong> '+c.eq(12).text()+'</div>');
    body.append('<div class="mb-2">'+c.eq(13).html()+'</div>');
    const actions = $('<div class="d-flex gap-1"></div>');
    actions.append(c.eq(14).html());
    actions.append(c.eq(15).html());
    actions.append(c.eq(16).html());
    if(CAN_EDIT) actions.append(c.eq(17).html());
    body.append(actions);
    container.append(card);
  });
}

function updateEvaluation(){
  const table = $('#rechnungstabelle').DataTable();
  new $.fn.dataTable.ColReorder(table); 
  const rows = $(table.rows({search:'applied', page:'all'}).nodes());
  let sum = 0, rab = 0, mw7 = 0, mw19 = 0;
  rows.each(function(){
    const invCb = $(this).find('.invoice-check');
    const credCb = $(this).find('.credit-check');
    if(invCb.length){
      const id = parseInt(invCb.val());
      const t = allInvoiceTotals[id];
      if(t){
        // Alle Rechnungen werden positiv gewertet. Korrektur-Rechnungen
        // sollen nicht negativ einfließen, nur Gutschriften werden
        // weiter unten abgezogen.
        sum  += t.brutto;
        rab  += t.rabatt;
        mw7  += t.mwst7;
        mw19 += t.mwst19;
      }
    }else if(credCb.length){
      const id = parseInt(credCb.val());
      const t = allCreditTotals[id];
      if(t){
        sum  -= t.brutto;
        rab  -= t.rabatt;
        mw7  -= t.mwst7;
        mw19 -= t.mwst19;
      }
    }
  });
  document.getElementById('auswertungGesamt').textContent = sum.toFixed(2).replace('.',',')+' \u20ac';
  document.getElementById('auswertungRabatt').textContent = rab.toFixed(2).replace('.',',')+' \u20ac';
  document.getElementById('auswertungMwst7').textContent = mw7.toFixed(2).replace('.',',')+' \u20ac';
  document.getElementById('auswertungMwst19').textContent = mw19.toFixed(2).replace('.',',')+' \u20ac';
}

function updateLayout(){
  const isMobile = window.innerWidth < 576;
  const wrapper = $('#rechnungstabelle_wrapper');
  const cards = $('#rechnung-cards');
  if(isMobile){
    wrapper.addClass('d-none');
    cards.removeClass('d-none');
    if(!cards.children().length){ buildCards(); }
  }else{
    cards.addClass('d-none');
    wrapper.removeClass('d-none');
  }
}

function updateAdvanceButton(){
  const show = Array.from(document.querySelectorAll('.invoice-check:checked')).some(cb => {
    const tr = cb.closest('tr');
    if(!tr) return false;
    const stufe = parseInt(tr.dataset.mahnstufe || '0', 10);
    return stufe >= 1 && stufe < 4;
  });
  document.getElementById('advance-mahnstufe').classList.toggle('d-none', !show);
}

function updateSepaButton(){
  const btn = document.getElementById('sepa-export');
  if(!btn) return;
  const selected = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)'));
  const hasPaid = selected.some(cb => cb.closest('tr').dataset.status === 'bezahlt');
  const hasNoSepa = selected.some(cb => cb.closest('tr').dataset.sepa !== '1');
  btn.disabled = selected.length === 0 || hasPaid || hasNoSepa;
  const statusMsg = document.getElementById('sepa-status');
if(selected.length === 0){
  btn.disabled = true;
  statusMsg.textContent = 'Keine Rechnung ausgewählt.';
} else if(hasPaid){
  btn.disabled = true;
  statusMsg.textContent = 'Eine der ausgewählten Rechnungen ist bereits bezahlt.';
} else if(hasNoSepa){
  btn.disabled = true;
  statusMsg.textContent = 'Nicht alle ausgewählten Rechnungen sind für SEPA geeignet.';
} else {
  btn.disabled = false;
  statusMsg.textContent = '';
}

}

document.getElementById('check-all').addEventListener('change', function(){
  document.querySelectorAll('.invoice-check:not(:disabled), .credit-check:not(:disabled), .offer-check:not(:disabled)')
    .forEach(cb => cb.checked = this.checked);
  updateAdvanceButton();
  toggleReminderVisibility();
  updateSepaButton();
});
document.addEventListener('change', function(e){
  if(e.target.classList.contains('invoice-check')){
    updateAdvanceButton();
    toggleReminderVisibility();
    updateSepaButton();
  }
});
updateSepaButton();
document.getElementById('batch-apply').addEventListener('click', function(event){
  event.preventDefault();
  const status = document.getElementById('batch-status').value;
  const invoiceIds = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)')).map(c => c.value).join(',');
  const creditIds  = Array.from(document.querySelectorAll('.credit-check:checked')).map(c => c.value).join(',');
  const offerIds   = Array.from(document.querySelectorAll('.offer-check:checked')).map(c => c.value).join(',');
  if(!status || (!invoiceIds && !creditIds && !offerIds)) return;
  const tasks = [];
  if(invoiceIds){
    const fd = new URLSearchParams();
    fd.append('ids', invoiceIds);
    fd.append('status', status);
    tasks.push(fetch('update_rechnung_status.php', {method:'POST', body: fd}).then(r=>r.json()));
  }
  if(creditIds){
    const fd = new URLSearchParams();
    fd.append('ids', creditIds);
    fd.append('status', status);
    tasks.push(fetch('update_gutschrift_status.php', {method:'POST', body: fd}).then(r=>r.json()));
  }
  if(offerIds){
    const fd = new URLSearchParams();
    fd.append('ids', offerIds);
    fd.append('status', status);
    tasks.push(fetch('update_angebot_status.php', {method:'POST', body: fd}).then(r=>r.json()));
  }
  Promise.all(tasks).then(()=>location.reload());
});
const sepaBtn = document.getElementById('sepa-export');
if(sepaBtn){
  sepaBtn.addEventListener('click', function(event){
    event.preventDefault();
    const selected = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)'));
    if(!selected.length) return;
    const invalidSepa = selected.some(cb => cb.closest('tr').dataset.sepa !== '1');
    if(invalidSepa){
      alert('SEPA XML nicht möglich. Prüfe die angegebenen Rechnungen.');
      return;
    }
    const hasPaid = selected.some(cb => cb.closest('tr').dataset.status === 'bezahlt');
    if(hasPaid) return;
    const ids = selected.map(c => c.value).join(',');
    const fd = new URLSearchParams();
    fd.append('ids', ids);
    fetch('sepa_export.php', {method:'POST', body: fd})
      .then(async r => {
        if(!r.ok) throw new Error('SEPA XML konnte nicht erstellt werden');
        const disp = r.headers.get('Content-Disposition') || '';
        const match = disp.match(/filename="?([^";]+)"?/);
        const filename = match ? match[1] : 'sepa.xml';
        const blob = await r.blob();
        return {blob, filename};
      })
      .then(({blob, filename}) => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
      })
      .then(() => fetch('sepa_overview_pdf.php', {method:'POST', body: fd}))
      .then(async r => {
        if(!r.ok) throw new Error('Bankeinzugsliste konnte nicht erstellt werden');
        const disp = r.headers.get('Content-Disposition') || '';
        const match = disp.match(/filename="?([^";]+)"?/);
        const filename = match ? match[1] : 'bankeinzugsliste.pdf';
        const blob = await r.blob();
        return {blob, filename};
      })
      .then(({blob, filename}) => {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
      })
      .then(() => {
        const sfd = new URLSearchParams();
        sfd.append('ids', ids);
        sfd.append('status', 'bezahlt');
        return fetch('update_rechnung_status.php', {method:'POST', body: sfd}).then(r=>r.json());
      })
      .then(res => {
        if(res.success){
          alert('SEPA-Dateien erstellt und Rechnungen als bezahlt markiert.');
          location.reload();
        }else{
          alert('SEPA-Dateien erstellt, aber Status konnte nicht aktualisiert werden.');
        }
      })
      .catch(err => {
        alert(err.message || 'Fehler beim Erstellen der SEPA-Dateien.');
      });
  });
}
document.getElementById('advance-mahnstufe').addEventListener('click', function(){
  const invoiceIds = Array.from(document.querySelectorAll('.invoice-check:checked')).map(cb => cb.value);
  if(!invoiceIds.length) return;
  fetch('advance_mahnstufe.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ ids: invoiceIds })
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message || 'Mahnstufen aktualisiert.');
    if(data.success) location.reload();
  })
  .catch(err => alert('Fehler: ' + err.message));
});
const deleteModalEl = document.getElementById('deleteReasonModal');
const deleteModal = new bootstrap.Modal(deleteModalEl);
const deleteInput = document.getElementById('deleteReasonInput');
let deleteUrl = '';
let deleteIds = [];
const batchDeleteBtn = document.getElementById('batch-delete');
if(batchDeleteBtn){
  batchDeleteBtn.addEventListener('click', function(event){
    event.preventDefault();
    const ids = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)')).map(c => c.value);
    if(!ids.length) return;
    deleteIds = ids;
    deleteUrl = 'delete_rechnungen.php';
    deleteInput.value='';
    deleteModal.show();
  });
}

const batchREBtn = document.getElementById('batch-rechnung');
if (batchREBtn) {
  batchREBtn.addEventListener('click', () => {
    const ids = [...document.querySelectorAll('.offer-check:checked:not(:disabled)')]
      .map(cb => cb.value);

    if (!ids.length) return alert('Bitte mindestens ein Angebot markieren.');

    ids.forEach(id => window.open(`rechnungen.php?convert_offer=${id}`, '_blank'));
  });
}

const batchGSBtn = document.getElementById('batch-gutschrift');
if (batchGSBtn) {
  batchGSBtn.addEventListener('click', () => {
    const ids = [...document.querySelectorAll('.invoice-check:checked:not(:disabled)')]
      .map(cb => parseInt(cb.value, 10));

    if (!ids.length) return alert('Bitte mindestens eine Rechnung markieren.');

    // Skip vollständig gutgeschriebene Rechnungen
    const runnable = ids.filter(id => {
      const inv = allInvoiceTotals[id]?.brutto ?? 0;
      const cred = creditTotalsByInvoice?.[id] ?? 0;
      return inv > 0 && cred < inv;
    });

    if (!runnable.length) return alert('Auswahl ist bereits vollständig gutgeschrieben.');

    runnable.forEach(id => window.open(`gutschriften_anlegen.php?parent=${id}`, '_blank'));
  });
}

const batchRKBtn = document.getElementById('batch-korrektur');
if (batchRKBtn) {
  batchRKBtn.addEventListener('click', () => {
    const ids = [...document.querySelectorAll('.invoice-check:checked:not(:disabled)')]
      .map(cb => cb.value);

    if (!ids.length) return alert('Bitte mindestens eine Rechnung markieren.');

    ids.forEach(id => window.open(`rechnungskorrektur_anlegen.php?parent=${id}`, '_blank'));
  });
}

function deleteRechnung(btn){
  deleteIds = [btn.dataset.id];
  deleteUrl = 'delete_rechnung.php';
  deleteInput.value='';
  deleteModal.show();
}

document.getElementById('deleteReasonConfirm').addEventListener('click',()=>{
  const reason = deleteInput.value;
  const fd = new URLSearchParams();
  if(deleteUrl === 'delete_rechnungen.php'){
    fd.append('ids', deleteIds.join(','));
  }else{
    fd.append('id', deleteIds[0]);
  }
  fd.append('reason', reason);
  fetch(deleteUrl,{method:'POST',body:fd,credentials:'same-origin'})
    .then(r=>r.json())
    .then(res=>{
      if(res.success){
        deleteModal.hide();
        alert('Rechnung gelöscht.');
        location.reload();
      }else{
        deleteModal.hide();
        alert(res.msg||'Fehler beim Löschen.');
      }
    })
    .catch(()=>{deleteModal.hide();alert('Fehler beim Löschen.');});
});

function deleteGutschrift(btn){
  if(!confirm('Gutschrift löschen?')) return;
  const id = btn.dataset.id;
  const fd = new URLSearchParams();
  fd.append('id', id);
  fetch('delete_gutschrift.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
    })
    .then(r => r.json())
    .then(res => {
      if(res.success){
        const dt = $('#rechnungstabelle').DataTable();
        dt.row($(btn).closest('tr')).remove().draw(false);
        alert('Gutschrift gelöscht.');
      }else{
        alert(res.msg || 'Fehler beim Löschen.');
      }
    })
    .catch(() => alert('Fehler beim Löschen.'));
}

function saveFilterState(){
  const dt = $('#rechnungstabelle').DataTable();
  const columns = [];
  document.querySelectorAll('.column-checkbox').forEach(cb => {
    if(cb.checked) columns.push(parseInt(cb.dataset.column));
  });
  const order = dt.colReorder?.order?.() || [];
  fetch('update_invoice_filters.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      version: 4,
      showCorrections: document.getElementById('korr-filter')?.checked || false,
      showCredits: document.getElementById('gutsch-filter')?.checked || false,
      showDelete: document.getElementById('delete-filter')?.checked || false,
      showDeletedOnly: document.getElementById('deletedonly-filter')?.checked || false,
      hideDeleted: document.getElementById('deleted-hide-filter')?.checked || false,
      showOffers: document.getElementById('angebot-filter')?.checked || false,
      showOffersOnly: document.getElementById('angebot-only-filter')?.checked || false,
      showOpen: document.getElementById('open-filter')?.checked || false,
      showSepa: document.getElementById('sepa-filter')?.checked || false,
      columns: columns,
      order: order,
      length: dt.page.len()
    })
  });
}

const FILTER_STORAGE_KEY = 'rechnungFilters';

function saveUIFilters(dt){
  let data = {};
  try{ data = JSON.parse(localStorage.getItem(FILTER_STORAGE_KEY) || '{}'); }catch(e){}
  data.empfaenger = document.getElementById('empfaenger-filter')?.value || '';
  data.status     = document.getElementById('status-filter')?.value || '';
  data.kopftext   = document.getElementById('kopftext-filter')?.value || '';
  data.search     = document.getElementById('dt-search')?.value || '';
  data.jahr       = document.getElementById('jahr-select')?.value || '';
  data.von        = document.getElementById('von-datum')?.value || '';
  data.bis        = document.getElementById('bis-datum')?.value || '';
  data.hideDeleted= document.getElementById('deleted-hide-filter')?.checked || false;
  data.showOffers = document.getElementById('angebot-filter')?.checked || false;
  data.showOffersOnly = document.getElementById('angebot-only-filter')?.checked || false;
  if(dt){
    try{ data.page = dt.page(); }catch(e){}
    try{ data.length = dt.page.len(); }catch(e){}
  }
  try{ localStorage.setItem(FILTER_STORAGE_KEY, JSON.stringify(data)); }catch(e){}
}

function loadUIFilters(table){
  try{
    const f = JSON.parse(localStorage.getItem(FILTER_STORAGE_KEY) || '{}');
    if(f.search){
      const raw = f.search.trim();
      $('#dt-search').val(raw);
      if(raw.includes('*')){
        const esc = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = raw.split('*').map(esc).join('.*');
        table.search(regex, true, false);
      }else{
        table.search(raw);
      }
    }
    if(f.empfaenger){
      $('#empfaenger-filter').val(f.empfaenger);
      table.column(6).search(f.empfaenger);
    }
    if(f.status){
      $('#status-filter').val(f.status);
      const label = $('#status-filter option:selected').text();
      table.column(10).search(label);
    }
    if(f.kopftext){
      $('#kopftext-filter').val(f.kopftext);
      table.column(11).search(f.kopftext);
    }
    if(f.jahr!==undefined) $('#jahr-select').val(f.jahr);
    if(f.von!==undefined) $('#von-datum').val(f.von);
    if(f.bis!==undefined) $('#bis-datum').val(f.bis);
    if(f.hideDeleted!==undefined) $('#deleted-hide-filter').prop('checked', !!f.hideDeleted);
    if(f.showOffers!==undefined) $('#angebot-filter').prop('checked', !!f.showOffers);
    if(f.showOffersOnly!==undefined) $('#angebot-only-filter').prop('checked', !!f.showOffersOnly);
    if(f.length!==undefined){
      const l = parseInt(f.length);
      if(!isNaN(l)) table.page.len(l);
    }
    if(f.page!==undefined){
      const p = parseInt(f.page);
      if(!isNaN(p)) table.page(p);
    }
  }catch(e){}
}

function resetUIFilters(table){
  localStorage.removeItem(FILTER_STORAGE_KEY);
  $('#empfaenger-filter').val('');
  $('#status-filter').val('');
  $('#kopftext-filter').val('');
  $('#jahr-select').val('');
  $('#von-datum').val('');
  $('#bis-datum').val('');
  table.search('').columns().search('');
  table.page.len(PREF_LENGTH || 10);
  table.draw();
  const kf=document.getElementById('korr-filter');
  if(kf){ kf.checked=false; kf.dispatchEvent(new Event('change')); }
  const gf=document.getElementById('gutsch-filter');
  if(gf){ gf.checked=false; gf.dispatchEvent(new Event('change')); }
  const df=document.getElementById('delete-filter');
  if(df){ df.checked=false; df.dispatchEvent(new Event('change')); }
  const dof=document.getElementById('deletedonly-filter');
  if(dof){ dof.checked=false; dof.dispatchEvent(new Event('change')); }
  const dhf=document.getElementById('deleted-hide-filter');
  if(dhf){ dhf.checked=false; dhf.dispatchEvent(new Event('change')); }
  const of=document.getElementById('open-filter');
  if(of){ of.checked=true; of.dispatchEvent(new Event('change')); }
  const lf=document.getElementById('links-filter');
  if(lf){ lf.checked=false; lf.dispatchEvent(new Event('change')); }
  const off=document.getElementById('angebot-filter');
  if(off){ off.checked=false; off.dispatchEvent(new Event('change')); }
  const ofo=document.getElementById('angebot-only-filter');
  if(ofo){ ofo.checked=false; ofo.dispatchEvent(new Event('change')); }
  const sf=document.getElementById('sepa-filter');
  if(sf){ sf.checked=false; sf.dispatchEvent(new Event('change')); }
  saveFilterState();
}

$(document).ready(function(){
  const colorBtns = document.querySelectorAll('.color-btn');
  let currentScheme = PREF_COLOR;
  const applyScheme = scheme => {
    document.body.classList.remove('color-F1','color-F2','color-F3');
    document.body.classList.add('color-'+scheme);
    colorBtns.forEach(b=>b.classList.toggle('active', b.dataset.scheme===scheme));
    currentScheme = scheme;
  };
  applyScheme(currentScheme);
  colorBtns.forEach(btn=>{
    btn.addEventListener('click',()=>{
      const scheme = btn.dataset.scheme;
      if(scheme===currentScheme) return;
      applyScheme(scheme);
      fetch('update_color_scheme.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({scheme})
      });
    });
  });
  const ALL_COLS = Array.from({length:20}, (_, i) => i);
  const defaultHidden = CAN_EDIT ? [13,14,18,19] : [13,14,17,18,19];
  let visibleCols;
  if(Array.isArray(PREF_COLUMNS)){
    const mandatory = [1];
    if(!CAN_EDIT) mandatory.push(16);
    visibleCols = Array.from(new Set(mandatory.concat(PREF_COLUMNS.map(Number).filter(c=>c!==18&&c!==19))));
  }
  if(!visibleCols){
    visibleCols = ALL_COLS.filter(i => !defaultHidden.includes(i));
  }
  visibleCols = visibleCols.filter(i => i!==18 && i!==19);
  const hiddenCols = ALL_COLS.filter(i => !visibleCols.includes(i));
  const headerOffset = $('.navbar').outerHeight() || 0;
  const table = $('#rechnungstabelle').DataTable({
    language:{url:'//cdn.datatables.net/plug-ins/1.13.6/i18n/de-DE.json'},
    autoWidth:false,
    pageLength: PREF_LENGTH || 10,
    columnDefs:[
      {targets:[0,1,4,5,13,14,15,16,17], orderable:false, searchable:false},
      {targets:hiddenCols, visible:false}
    ],
    order:[[2,'desc']],
    colReorder:{ order: PREF_ORDER || null },
    fixedHeader:{header:true,headerOffset:headerOffset},
    initComplete:function(){
      const wrapper = $('#rechnungstabelle_wrapper');
      const filterRow = $('#filterzeile-rechnungen');

      const searchContainer = wrapper.find('.dataTables_filter');
      const searchInput = searchContainer.find('input');
      searchInput.addClass('form-control form-control-sm').css('min-width','160px').removeClass('ms-2');
      const searchWrapper = $('<div class="d-flex flex-column"></div>');
      searchWrapper.append('<label for="dt-search" class="form-label small text-muted mb-1">Suche:</label>');
      searchWrapper.append(searchInput.attr('id','dt-search'));
      searchContainer.addClass('text-start').css('margin-right','gap-1').html(searchWrapper);
      // Replace DataTables' default search handler so we can support
      // simple wildcard queries using "*". The "*" will match any
      // number of arbitrary characters.
      searchInput.off('.DT').on('input', function(){
        const raw = this.value.trim();
        if(raw.includes('*')){
          const esc = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
          const regex = raw.split('*').map(esc).join('.*');
          table.search(regex, true, false).draw();
        }else{
          table.search(raw).draw();
        }
        saveUIFilters(table);
      });
      filterRow.prepend(searchContainer);

      // Zeilenanzahl mit Eingabefeld + Vorschlagsliste
      const lengthContainer = wrapper.find('.dataTables_length');

      // Datalist nur einmal anhängen
      if (!document.getElementById('pageLengths')) {
        $('body').append(`
          <datalist id="pageLengths">
            <option value="10">
            <option value="25">
            <option value="50">
            <option value="100">
          </datalist>
        `);
      }

      // Eingabefeld mit Vorschlägen + Pfeiltastensteuerung
      const input = $(`
        <input type="text"
              list="pageLengths"
              class="form-control form-control-sm"
              style="width: 100px;"
              placeholder="Zeilen">`);

      input.val(table.page.len());

      // Eingabe / Auswahl aus Datalist
      input.on('change', function () {
        const val = parseInt(this.value, 10);
        if (!isNaN(val) && val > 0) {
          table.page.len(val).draw();
        }
      });

      // ↑ ↓ Tastatursteuerung
      input.on('keydown', function (e) {
        let val = parseInt(this.value, 10);
        if (isNaN(val)) val = 0;

        if (e.key === 'ArrowUp') {
          e.preventDefault();
          val += 1;
          this.value = val;
          table.page.len(val).draw();
        }

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          val = Math.max(1, val - 1);
          this.value = val;
          table.page.len(val).draw();
        }
      });

      // Label und Layout einbauen
      const lengthWrapper = $('<div class="d-flex flex-column"></div>');
      lengthWrapper.append('<label class="form-label small text-muted mb-1">Zeilen:</label>');
      lengthWrapper.append(input);
      lengthContainer.html(lengthWrapper);
      filterRow.prepend(lengthContainer);


      const columnFilter = $('#columnFilterSection');
      const toggleBtn = $('#toggleColumnFilter');
      const columnFilterHeader = columnFilter.find('.card-header');
      columnFilterHeader.css('cursor','pointer');
      toggleBtn.add(columnFilterHeader).on('click',()=>columnFilter.toggleClass('d-none'));
      table.columns().every(function(index){
        if(index===1 || index===18 || index===19 || (!CAN_EDIT && index===17)) return;
        const title = $(this.header()).text().trim();
        const id = 'colChk'+index;
        const wrapper = $('<div class="col form-check"></div>');
        const visible = visibleCols.includes(index);
        table.column(index).visible(visible);
        const cb = $('<input type="checkbox" class="form-check-input column-checkbox">')
          .attr('id',id).attr('data-column',index)
          .prop('checked', visible);
        const label = $('<label class="form-check-label ms-1"></label>')
          .attr('for',id).text(title);
        wrapper.append(cb).append(label);
        $('#columnFilterContent').append(wrapper);
      });
columnFilter.on('change', '.column-checkbox', function () {
  const idx = parseInt(this.dataset.column); // Original-Spaltenindex
  table.column(idx, {order: 'index'}).visible(this.checked);
  table.columns.adjust();
  saveFilterState();
});

      updateLayout();
      table.fixedHeader.adjust();
      updateEvaluation();
      saveFilterState();
      loadUIFilters(table);
      table.draw();
    }
  });
  table.on('draw', () => { updateEvaluation(); updateSepaButton(); });
  table.on('page.dt length.dt', () => saveUIFilters(table));
  $(window).on('resize', () => {
    updateLayout();
    table.fixedHeader.adjust();
  });
  document.getElementById('empfaenger-filter').addEventListener('keyup',e=>{
    table.column(6).search(e.target.value).draw();
    saveUIFilters(table);
  });
    document.getElementById('status-filter').addEventListener('change',e=>{
      const value = e.target.value;
      if(value===''){
        table.column(10).search('').draw();
      }else{
        const label = e.target.options[e.target.selectedIndex].text;
        table.column(10).search(label).draw();
      }
      saveUIFilters(table);
    });
    document.getElementById('kopftext-filter').addEventListener('keyup',e=>{
      table.column(11).search(e.target.value).draw();
      saveUIFilters(table);
    });
    $('#jahr-select,#von-datum,#bis-datum').on('change',function(){
      saveUIFilters(table);
      $('#filterzeile-rechnungen')[0].submit();
    });
    const korrFilter=document.getElementById('korr-filter');
    const gutschFilter=document.getElementById('gutsch-filter');
    const angebotFilter=document.getElementById('angebot-filter');
    const angebotOnlyFilter=document.getElementById('angebot-only-filter');
    const openFilter=document.getElementById('open-filter');
    const deletedOnlyFilter=document.getElementById('deletedonly-filter');
    const hideDeletedFilter=document.getElementById('deleted-hide-filter');
    const linksFilter=document.getElementById('links-filter');
    const sepaFilter=document.getElementById('sepa-filter');
    const deleteFilter=document.getElementById('delete-filter');
    if(korrFilter) korrFilter.checked = PREF_SHOWCORR;
    if(gutschFilter) gutschFilter.checked = PREF_SHOWCRED;
    if(angebotFilter) angebotFilter.checked = PREF_SHOWOFFER;
    if(angebotOnlyFilter) angebotOnlyFilter.checked = PREF_SHOWOFFERONLY;
    if(openFilter) openFilter.checked = PREF_SHOWOPEN;
    if(deletedOnlyFilter) deletedOnlyFilter.checked = PREF_SHOWDELONLY;
    if(hideDeletedFilter) hideDeletedFilter.checked = PREF_HIDEDEL;
    if(linksFilter) linksFilter.checked = PREF_SHOWLINKS;
    if(sepaFilter) sepaFilter.checked = PREF_SHOWSEPA;
    if(deleteFilter) deleteFilter.checked = PREF_SHOWDEL;
    const body=document.body;

    $.fn.dataTable.ext.search.push((settings,data,dataIndex)=>{
      if(settings.nTable.id!=='rechnungstabelle') return true;
      const row = table.row(dataIndex).node();
      const status = row.dataset.status || '';
      const isDeleted = status === 'geloescht';
      if(linkFilterNr){
        const nr = row.dataset.nr || '';
        const parent = row.dataset.parent || '';
        const offer = row.dataset.offer || '';
        const corrList = (row.dataset.corrections || '').split(',').map(s=>s.trim()).filter(Boolean);
        if(nr===linkFilterNr || parent===linkFilterNr || offer===linkFilterNr || corrList.includes(linkFilterNr)) return true;
        return false;
      }
      const sepaFlag = parseInt(row.dataset.sepa ?? '0', 10);
      if(sepaFilter && sepaFilter.checked && sepaFlag !== 1) return false;
      if(angebotOnlyFilter && angebotOnlyFilter.checked && !row.classList.contains('offer-row')) return false;
      if(openFilter && openFilter.checked){
        if(isDeleted) return false;
        if(!['angelegt','geprueft','versendet'].includes(status)) return false;
      } else if(deletedOnlyFilter && deletedOnlyFilter.checked){
        return isDeleted;
      }
      const hideDeleted = hideDeletedFilter && hideDeletedFilter.checked;
      if(hideDeleted && status === 'geloescht') return false;
      if(gutschFilter && !gutschFilter.checked && row.classList.contains('credit-row')) return false;
      if(angebotFilter && !angebotFilter.checked && row.classList.contains('offer-row')) return false;
      // Correction invoices should always be visible irrespective of the
      // filter state. Only hide original invoices that have been corrected
      // when the filter is not active.
      if(row.classList.contains('corr-correction')) return true;
      if(korrFilter.checked) return true;
      return !row.classList.contains('corr-corrected');
    });

    body.classList.toggle('show-corr',korrFilter.checked);
    body.classList.toggle('show-gutsch',gutschFilter.checked);
    body.classList.toggle('show-offer',angebotFilter && angebotFilter.checked);
    if(linksFilter) table.column(0).visible(linksFilter.checked);
    table.draw();
    if(deleteFilter){
      deleteFilter.checked = table.column(15).visible();
      table.column(15).visible(deleteFilter.checked);
    }
    saveFilterState();
    korrFilter.addEventListener('change',e=>{
      body.classList.toggle('show-corr',e.target.checked);
      table.draw();
      saveFilterState();
    });
    gutschFilter.addEventListener('change',e=>{
      body.classList.toggle('show-gutsch',e.target.checked);
      saveFilterState();
    });
    if(angebotFilter){
      angebotFilter.addEventListener('change',e=>{
        body.classList.toggle('show-offer',e.target.checked);
        table.draw();
        saveFilterState();
      });
    }
    if(angebotOnlyFilter){
      angebotOnlyFilter.addEventListener('change',()=>{
        table.draw();
        saveFilterState();
      });
    }
    if(openFilter){
      openFilter.addEventListener('change',()=>{
        table.draw();
        saveFilterState();
      });
    }
    if(deletedOnlyFilter){
      deletedOnlyFilter.addEventListener('change',()=>{
        table.draw();
        saveFilterState();
      });
    }
    if(hideDeletedFilter){
      hideDeletedFilter.addEventListener('change',()=>{
        table.draw();
        saveFilterState();
      });
    }
    if(linksFilter){
      linksFilter.addEventListener('change',e=>{
        table.column(0).visible(e.target.checked);
        table.columns.adjust();
        saveFilterState();
      });
    }
    if(sepaFilter){
      sepaFilter.addEventListener('change',()=>{
        table.draw();
        saveFilterState();
      });
    }
    if(deleteFilter){
      deleteFilter.addEventListener('change',e=>{
        table.column(15).visible(e.target.checked);
        table.columns.adjust();
        saveFilterState();
      });
    }
  $(document).on('click','.delete-rechnung-btn',function(){
    deleteRechnung(this);
  });
  $(document).on('click','.delete-gutschrift-btn',function(){
    deleteGutschrift(this);
  });
  $(document).on('click','.link-btn',function(){
    const row = this.closest('tr');
    const nr = row?.dataset.parent || row?.dataset.nr || null;
    linkFilterNr = (linkFilterNr === nr) ? null : nr;
    table.draw();
  });
  $(document).on('click','.pdf-link',function(){
    saveUIFilters(table);
    setTimeout(()=>{
      window.location.href = window.location.pathname + window.location.search;
    }, 1000);
  });
  $('#resetFilterBtn').on('click',function(){
    resetUIFilters(table);
    linkFilterNr = null;
    $('#filterzeile-rechnungen')[0].submit();
  });
  $(document).on('change','.invoice-check',function(){
    updateEvaluation();
  });

  $('#rechnungstabelle tbody').on('click','td.toggle-row',function(){
    const tr = $(this).closest('tr');
    const row = table.row(tr);
    const id = $(this).data('id');
    const icon = $(this).find('i');
    const isCredit = tr.hasClass('credit-row');
    const isOffer = tr.hasClass('offer-row');
    if(row.child.isShown()){
      row.child.hide();
      tr.removeClass('shown');
      icon.removeClass('bi-chevron-down').addClass('bi-chevron-right');
    }else{
      let data = invoicePositions[id] || [];
      if(isCredit) data = creditPositions[id] || [];
      if(isOffer) data = offerPositions[id] || [];
      const html = buildPositionTable(data);
      row.child(html).show();
      tr.addClass('shown');
      icon.removeClass('bi-chevron-right').addClass('bi-chevron-down');
    }
  });

  $('#rechnungstabelle tbody').on('dblclick','tr',function(e){
    const target = $(e.target);
    if(target.closest('td').hasClass('toggle-row')) return;
    if(target.closest('a,button,input,label').length) return;
    const editHref = $(this).data('edit-link') || $(this).find('a.btn-primary[title="Bearbeiten"]').attr('href');
    if(editHref){
      window.location.href = editHref;
    } else {
      alert('Bearbeiten der Rechnung nicht mehr möglich.');
    }
  });

  function buildPositionTable(positionen){
    if(!positionen.length) return '<div class="text-muted">Keine Positionen gefunden.</div>';
    let html = '<table class="table table-sm mb-0"><thead><tr>';
    html += '<th>Artikelnummer</th><th>Bezeichnung</th><th>Menge</th><th>Einzelpreis</th><th>Rabatt %</th><th>Gesamt</th>';
    html += '</tr></thead><tbody>';
    positionen.forEach(p=>{
      const gesamt = (p.einzelpreis * p.menge * (1 - (p.rabatt / 100))).toFixed(2).replace('.',',');
      html += `<tr>
        <td>${p.artikelnummer}</td>
        <td>${p.kurzbez}<br><small>${p.langbez || ''}</small></td>
        <td>${p.menge}</td>
        <td>${Number(p.einzelpreis).toFixed(2).replace('.',',')} €</td>
        <td>${Number(p.rabatt).toFixed(2).replace('.',',')}</td>
        <td>${gesamt} €</td>
      </tr>`;
    });

    html += '</tbody></table>';
    return html;
  }
});
const archiveBtn = document.getElementById('archive-pdfs');
if(archiveBtn){
  archiveBtn.addEventListener('click', function () {
    const invoiceIds = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)')).map(cb => cb.value);
    if (invoiceIds.length === 0) {
      alert('Bitte markiere mindestens eine Rechnung zum Archivieren.');
      return;
    }
    if (!confirm('Ausgewählte Rechnungen wirklich archivieren?')) return;
    fetch('rechnungen_archivieren.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ids: invoiceIds })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        location.reload();
      } else {
        alert('Fehler: ' + (data.error || 'Unbekannter Fehler'));
      }
    })
    .catch(err => {
      alert('Fehler beim Archivieren: ' + err.message);
    });
  });
}
const reminderBtn = document.getElementById('generate-reminders');
function toggleReminderVisibility(){
  if(!reminderBtn) return;
  const selected = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)'));
  const hasSent = selected.some(cb => cb.closest('tr').dataset.status === 'versendet');
  reminderBtn.classList.toggle('d-none', !hasSent);
}

if(reminderBtn){
  reminderBtn.addEventListener('click', function () {
    const selected = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)'))
      .filter(cb => cb.closest('tr').dataset.status === 'versendet');
    if (selected.length === 0) {
      alert('Bitte markiere mindestens eine versendete Rechnung.');
      return;
    }
    selected.forEach(cb => {
      window.open('zahlungserinnerung_pdf.php?id=' + cb.value, '_blank');
    });
  });

  toggleReminderVisibility();
}
const pdfBtn = document.getElementById('generate-multiple-pdfs');
if(pdfBtn){
  pdfBtn.addEventListener('click', function () {
    const invoiceIds = Array.from(document.querySelectorAll('.invoice-check:checked:not(:disabled)'))
      .map(cb => cb.value);
    const offerIds = Array.from(document.querySelectorAll('.offer-check:checked:not(:disabled)'))
      .map(cb => cb.value);
    const creditIds = Array.from(document.querySelectorAll('.credit-check:checked:not(:disabled)'))
      .map(cb => cb.value);
    if (invoiceIds.length + offerIds.length + creditIds.length === 0) {
      alert('Bitte markiere mindestens einen Beleg.');
      return;
    }
    function generate(overwrite=false){
      const token = document.getElementById('csrf-token').value;
      fetch('generate_multiple_pdfs.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': token
        },
        body: JSON.stringify({ invoices: invoiceIds, offers: offerIds, credits: creditIds, overwrite })
      })
      .then(res => res.json())
      .then(data => {
        if(data.exists && !overwrite){
          if(confirm(data.message)){
            generate(true);
          }
        }else{
          const modalBody = document.getElementById('pdfDownloadModalBody');
          modalBody.innerHTML = '';

          if(data.message){
            const msg = document.createElement('p');
            msg.textContent = data.message;
            if(!data.success){
              msg.classList.add('text-danger');
            }
            modalBody.appendChild(msg);
          }

          const successLinks = [];
          if(data.generated){
            (data.generated.invoices || []).forEach(nr => {
              successLinks.push({label: 'Rechnung ' + nr, url: 'rechnungen/' + nr + '.pdf'});
            });
            (data.generated.offers || []).forEach(nr => {
              successLinks.push({label: 'Angebot ' + nr, url: 'angebote/angebot_' + nr + '.pdf'});
            });
            (data.generated.credits || []).forEach(nr => {
              successLinks.push({label: 'Gutschrift ' + nr, url: 'gutschriften/gutschrift_' + nr + '.pdf'});
            });
          }

          const failedItems = [];
          if(data.failed){
            (data.failed.invoices || []).forEach(nr => failedItems.push('Rechnung ' + nr));
            (data.failed.offers || []).forEach(nr => failedItems.push('Angebot ' + nr));
            (data.failed.credits || []).forEach(nr => failedItems.push('Gutschrift ' + nr));
          }

          const existingItems = [];
          if(data.exists){
            (data.exists.invoices || []).forEach(nr => existingItems.push('Rechnung ' + nr));
            (data.exists.offers || []).forEach(nr => existingItems.push('Angebot ' + nr));
            (data.exists.credits || []).forEach(nr => existingItems.push('Gutschrift ' + nr));
          }

          if(successLinks.length){
            const h = document.createElement('h6');
            h.textContent = 'Erstellt';
            modalBody.appendChild(h);
            const ul = document.createElement('ul');
            ul.className = 'list-group mb-3';
            successLinks.forEach(f => {
              const li = document.createElement('li');
              li.className = 'list-group-item';
              const a = document.createElement('a');
              a.href = f.url;
              a.textContent = f.label;
              a.target = '_blank';
              li.appendChild(a);
              ul.appendChild(li);
            });
            modalBody.appendChild(ul);
          }

          if(failedItems.length){
            const h = document.createElement('h6');
            h.textContent = 'Fehlgeschlagen';
            modalBody.appendChild(h);
            const ul = document.createElement('ul');
            ul.className = 'list-group mb-3';
            failedItems.forEach(label => {
              const li = document.createElement('li');
              li.className = 'list-group-item list-group-item-danger';
              li.textContent = label;
              ul.appendChild(li);
            });
            modalBody.appendChild(ul);
          }

          if(existingItems.length){
            const h = document.createElement('h6');
            h.textContent = 'Bereits vorhanden';
            modalBody.appendChild(h);
            const ul = document.createElement('ul');
            ul.className = 'list-group';
            existingItems.forEach(label => {
              const li = document.createElement('li');
              li.className = 'list-group-item';
              li.textContent = label;
              ul.appendChild(li);
            });
            modalBody.appendChild(ul);
          }

          if(!successLinks.length && !failedItems.length && !existingItems.length && !data.success){
            modalBody.textContent = data.message || 'Fehler beim Erstellen der PDFs.';
          }

          const modal = new bootstrap.Modal(document.getElementById('pdfDownloadModal'));
          modal.show();
        }
      })
      .catch(err => {
        alert('Fehler beim Erstellen der PDFs: ' + err.message);
      });
    }
    generate();
  });
}
</script>
<input type="hidden" id="csrf-token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
<div class="modal fade" id="pdfDownloadModal" tabindex="-1" aria-labelledby="pdfDownloadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pdfDownloadModalLabel">PDF-Downloads</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
      </div>
<div class="modal-body" id="pdfDownloadModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Schließen</button>
      </div>
    </div>
  </div>
</div>
<?php include 'kunden_edit_modal.php'; ?>
<?php $kundenRedirect = 'rechnungen.php'; include 'kunden_neu_modal.php'; ?>
<script src="dropdown_nav.js"></script>
<script>
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' && !document.querySelector('.modal.show')) {
    document.getElementById('cancelButton')?.click();
  }
});
</script>
</body>
</html>