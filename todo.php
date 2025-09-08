<?php
session_name('user_session');
session_start([
    'cookie_secure'   => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Enforce app access via roles mapping; fallback allows roles 4,3,2,1
requireRole([4,3,2,1]);

// Current user id needed for early category queries
$userId = (int)($_SESSION['user_id'] ?? 0);

// CSRF: Alle POST-Operationen (inkl. Uploads) prüfen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'get_csrf') {
        header('Content-Type: application/json');
        if (function_exists('csrf_get_tokens')) {
            $t = csrf_get_tokens();
            echo json_encode(['ok' => true, 'id' => $t['id'] ?? '', 'token' => $t['token'] ?? '']);
        } else {
            echo json_encode(['ok' => false]);
        }
        exit;
    }
    $isReorder = ($_POST['action'] ?? '') === 'reorder';
    if (!csrf_verify_post()) {
        http_response_code(403);
        if ($isReorder) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'csrf']);
        } else {
            echo 'CSRF verification failed';
        }
        exit;
    }
}

// Ensure base table exists for user-linked weekly todos
$conn->query(
    "CREATE TABLE IF NOT EXISTS todos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        description TEXT NOT NULL,
        week_start DATE NOT NULL,
        todo_date DATE NOT NULL,
        priority ENUM('hoch','mittel','niedrig') NOT NULL DEFAULT 'mittel',
        start_time TIME DEFAULT NULL,
        due_date DATE DEFAULT NULL,
        due_time TIME DEFAULT NULL,
        repeat_freq ENUM('none','daily','weekly','monthly') NOT NULL DEFAULT 'none',
        repeat_until DATE DEFAULT NULL,
        archived TINYINT(1) NOT NULL DEFAULT 0,
        archived_at DATETIME DEFAULT NULL,
        sort_order INT DEFAULT NULL,
        created_by INT DEFAULT NULL,
        in_progress_by INT DEFAULT NULL,
        in_progress_at DATETIME DEFAULT NULL,
        is_done TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=offen,1=erledigt,2=in_bearbeitung',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME DEFAULT NULL,
        completed_by INT DEFAULT NULL,
        sent_scope ENUM('single','users','all') NOT NULL DEFAULT 'single',
        dispatch_group VARCHAR(32) DEFAULT NULL,
        title VARCHAR(100) DEFAULT NULL,
        INDEX idx_user_week (user_id, week_start),
        INDEX idx_user_date (user_id, todo_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
);

// Add missing column on older installs
$hasTodoDate = false;
$res = $conn->query("SHOW COLUMNS FROM todos LIKE 'todo_date'");
if ($res) { $hasTodoDate = $res->num_rows > 0; $res->close(); }
if (!$hasTodoDate) {
    $conn->query("ALTER TABLE todos ADD COLUMN todo_date DATE NOT NULL AFTER week_start");
    $conn->query("ALTER TABLE todos ADD INDEX idx_user_date (user_id, todo_date)");
    $conn->query("UPDATE todos SET todo_date = week_start WHERE todo_date IS NULL OR todo_date = '0000-00-00'");
}
// Add priority column for older installs
$hasPriority = false;
$res2 = $conn->query("SHOW COLUMNS FROM todos LIKE 'priority'");
if ($res2) { $hasPriority = $res2->num_rows > 0; $res2->close(); }
if (!$hasPriority) {
    $conn->query("ALTER TABLE todos ADD COLUMN priority ENUM('hoch','mittel','niedrig') NOT NULL DEFAULT 'mittel' AFTER todo_date");
}
// Add timing fields for older installs
$res3 = $conn->query("SHOW COLUMNS FROM todos LIKE 'start_time'");
if ($res3 && $res3->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN start_time TIME NULL AFTER priority"); }
if ($res3) { $res3->close(); }
$res4 = $conn->query("SHOW COLUMNS FROM todos LIKE 'due_date'");
if ($res4 && $res4->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN due_date DATE NULL AFTER start_time"); }
if ($res4) { $res4->close(); }
$res5 = $conn->query("SHOW COLUMNS FROM todos LIKE 'due_time'");
if ($res5 && $res5->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN due_time TIME NULL AFTER due_date"); }
if ($res5) { $res5->close(); }
// sort_order for manual ordering
$res6 = $conn->query("SHOW COLUMNS FROM todos LIKE 'sort_order'");
if ($res6 && $res6->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN sort_order INT NULL AFTER due_time"); }
if ($res6) { $res6->close(); }
// created_by for audit
$res7 = $conn->query("SHOW COLUMNS FROM todos LIKE 'created_by'");
if ($res7 && $res7->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN created_by INT NULL AFTER sort_order"); }
if ($res7) { $res7->close(); }
// Track who set a task to in-progress
$resIP = $conn->query("SHOW COLUMNS FROM todos LIKE 'in_progress_by'");
if ($resIP && $resIP->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN in_progress_by INT NULL AFTER created_by"); }
if ($resIP) { $resIP->close(); }
// Track when a task was set to in-progress
$resIPat = $conn->query("SHOW COLUMNS FROM todos LIKE 'in_progress_at'");
if ($resIPat && $resIPat->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN in_progress_at DATETIME NULL AFTER in_progress_by"); }
if ($resIPat) { $resIPat->close(); }
// Add title column for subject
$resTitle = $conn->query("SHOW COLUMNS FROM todos LIKE 'title'");
if ($resTitle && $resTitle->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN title VARCHAR(100) NULL AFTER user_id"); }
if ($resTitle) { $resTitle->close(); }
// Add sent_scope/dispatch_group for grouping sent-to-all
$resScope = $conn->query("SHOW COLUMNS FROM todos LIKE 'sent_scope'");
if ($resScope && $resScope->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN sent_scope ENUM('single','users','all') NOT NULL DEFAULT 'single' AFTER completed_at"); }
if ($resScope) { $resScope->close(); }
$resGroup = $conn->query("SHOW COLUMNS FROM todos LIKE 'dispatch_group'");
if ($resGroup && $resGroup->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN dispatch_group VARCHAR(32) NULL AFTER sent_scope"); }
if ($resGroup) { $resGroup->close(); }
// Track who completed a task
$resCompBy = $conn->query("SHOW COLUMNS FROM todos LIKE 'completed_by'");
if ($resCompBy && $resCompBy->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN completed_by INT NULL AFTER completed_at"); }
if ($resCompBy) { $resCompBy->close(); }
// Ensure is_done can represent "in Bearbeitung"
$resDoneCol = $conn->query("SHOW FULL COLUMNS FROM todos LIKE 'is_done'");
if ($resDoneCol) {
    $rowDone = $resDoneCol->fetch_assoc();
    if (strpos((string)($rowDone['Comment'] ?? ''), 'in_bearbeitung') === false) {
        $conn->query("ALTER TABLE todos MODIFY is_done TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=offen,1=erledigt,2=in_bearbeitung'");
    }
    $resDoneCol->close();
}
// Categories support
// Create categories table if missing
$conn->query(
    "CREATE TABLE IF NOT EXISTS todo_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        sort_order INT NULL,
        owner_id INT NULL,
        created_by INT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_owner (owner_id),
        KEY idx_created_by (created_by)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
);
// Per-user category sharing table
$conn->query(
    "CREATE TABLE IF NOT EXISTS todo_category_shares (
        category_id INT NOT NULL,
        user_id INT NOT NULL,
        PRIMARY KEY(category_id, user_id),
        KEY idx_share_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
);

// Table for many-to-many assignees of todos
$conn->query(
    "CREATE TABLE IF NOT EXISTS todo_assignees (
        todo_id INT NOT NULL,
        user_id INT NOT NULL,
        PRIMARY KEY(todo_id, user_id),
        KEY idx_ta_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
);

// Forward log table (who forwarded a To-Do to whom)
// (Weiterleiten-Log wurde entfernt)
// Migrate older installs: add owner_id, adjust indexes
$hasOwner = false;
$resOwner = $conn->query("SHOW COLUMNS FROM todo_categories LIKE 'owner_id'");
if ($resOwner) { $hasOwner = $resOwner->num_rows > 0; $resOwner->close(); }
if (!$hasOwner) {
    $conn->query("ALTER TABLE todo_categories ADD COLUMN owner_id INT NULL AFTER sort_order");
    $conn->query("ALTER TABLE todo_categories ADD INDEX idx_owner (owner_id)");
}
// Ensure created_by exists
$hasCreatedBy = false;
$resCreatedBy = $conn->query("SHOW COLUMNS FROM todo_categories LIKE 'created_by'");
if ($resCreatedBy) { $hasCreatedBy = $resCreatedBy->num_rows > 0; $resCreatedBy->close(); }
if (!$hasCreatedBy) {
    $conn->query("ALTER TABLE todo_categories ADD COLUMN created_by INT NULL AFTER owner_id");
    $conn->query("ALTER TABLE todo_categories ADD INDEX idx_created_by (created_by)");
}
// Drop any unique indexes on category names to allow duplicates
$hasUniqName = false;
$resIdx = $conn->query("SHOW INDEX FROM todo_categories WHERE Key_name='uniq_name'");
if ($resIdx) { $hasUniqName = $resIdx->num_rows > 0; $resIdx->close(); }
if ($hasUniqName) { $conn->query("ALTER TABLE todo_categories DROP INDEX uniq_name"); }

$hasUniqOwnerName = false;
$resIdx2 = $conn->query("SHOW INDEX FROM todo_categories WHERE Key_name='uniq_owner_name'");
if ($resIdx2) { $hasUniqOwnerName = $resIdx2->num_rows > 0; $resIdx2->close(); }
if ($hasUniqOwnerName) { $conn->query("ALTER TABLE todo_categories DROP INDEX uniq_owner_name"); }
// Add category_id on todos for single-category assignment
$resCatCol = $conn->query("SHOW COLUMNS FROM todos LIKE 'category_id'");
if ($resCatCol && $resCatCol->num_rows === 0) {
    $conn->query("ALTER TABLE todos ADD COLUMN category_id INT NULL AFTER created_by");
    $conn->query("ALTER TABLE todos ADD INDEX idx_category (category_id)");
}
if ($resCatCol) { $resCatCol->close(); }
// (reminder columns removed)

// Ensure default category "Eigene Aufgaben" exists and fetch its id
$defaultCatId = null;
$defaultCatName = 'Eigene Aufgaben';
$stmtDefSel = $conn->prepare('SELECT id FROM todo_categories WHERE name = ? AND owner_id IS NULL LIMIT 1');
if ($stmtDefSel) {
    $stmtDefSel->bind_param('s', $defaultCatName);
    $stmtDefSel->execute();
    $stmtDefSel->bind_result($cid);
    if ($stmtDefSel->fetch()) { $defaultCatId = (int)$cid; }
    $stmtDefSel->close();
}
if (!$defaultCatId) {
    $stmtDefIns = $conn->prepare('INSERT IGNORE INTO todo_categories (name, sort_order, owner_id, created_by) VALUES (?, 1, NULL, ?)');
    if ($stmtDefIns) { $stmtDefIns->bind_param('si', $defaultCatName, $userId); $stmtDefIns->execute(); $stmtDefIns->close(); }
    // re-read id
    $stmtDefSel2 = $conn->prepare('SELECT id FROM todo_categories WHERE name = ? AND owner_id IS NULL LIMIT 1');
    if ($stmtDefSel2) {
        $stmtDefSel2->bind_param('s', $defaultCatName);
        $stmtDefSel2->execute();
        $stmtDefSel2->bind_result($cid2);
        if ($stmtDefSel2->fetch()) { $defaultCatId = (int)$cid2; }
        $stmtDefSel2->close();
    }
}

// Build category id->name map for display
$catMap = [];
// Map categories visible to the selected user context (for display)
// Note: $selectedUserId might not be initialized yet at this point; fallback to current $userId
$catViewerId = isset($selectedUserId) ? (int)$selectedUserId : (int)$userId;
$resCatMap = $conn->query(
    "SELECT id, name FROM todo_categories WHERE owner_id IS NULL OR owner_id = " . $catViewerId .
    " OR id IN (SELECT category_id FROM todo_category_shares WHERE user_id = " . $catViewerId . ")"
);
if ($resCatMap) {
    while ($r = $resCatMap->fetch_assoc()) { $catMap[(int)$r['id']] = (string)$r['name']; }
    $resCatMap->close();
}

// Repeat columns
$resR1 = $conn->query("SHOW COLUMNS FROM todos LIKE 'repeat_freq'");
if ($resR1 && $resR1->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN repeat_freq ENUM('none','daily','weekly','monthly') NOT NULL DEFAULT 'none' AFTER due_time"); }
if ($resR1) { $resR1->close(); }
$resR2 = $conn->query("SHOW COLUMNS FROM todos LIKE 'repeat_until'");
if ($resR2 && $resR2->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN repeat_until DATE NULL AFTER repeat_freq"); }
if ($resR2) { $resR2->close(); }

// Archive columns
$resA1 = $conn->query("SHOW COLUMNS FROM todos LIKE 'archived'");
if ($resA1 && $resA1->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN archived TINYINT(1) NOT NULL DEFAULT 0 AFTER repeat_until"); }
if ($resA1) { $resA1->close(); }
$resA2 = $conn->query("SHOW COLUMNS FROM todos LIKE 'archived_at'");
if ($resA2 && $resA2->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN archived_at DATETIME NULL AFTER archived"); }
if ($resA2) { $resA2->close(); }

// Forward flag
$resFwd = $conn->query("SHOW COLUMNS FROM todos LIKE 'is_forwarded'");
if ($resFwd && $resFwd->num_rows === 0) { $conn->query("ALTER TABLE todos ADD COLUMN is_forwarded TINYINT(1) NOT NULL DEFAULT 0 AFTER dispatch_group"); }
if ($resFwd) { $resFwd->close(); }

// Helpers
function isoWeekToMonday(string $weekStr): string {
    // Accept HTML week input format: YYYY-Www
    if (!preg_match('/^(\d{4})-W(\d{2})$/', $weekStr, $m)) {
        $dt = new DateTime();
        $year = (int)$dt->format('o');
        $week = (int)$dt->format('W');
    } else {
        $year = (int)$m[1];
        $week = (int)$m[2];
    }
    $d = new DateTime();
    $d->setISODate($year, $week, 1); // Monday
    return $d->format('Y-m-d');
}

// Upload helpers for To-Do modal (store only on disk, not DB)
function ttEnsureUploadDir(?int $todoId = null): string {
    // Flat storage: all files directly under uploads_todo (no subfolders)
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads_todo';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $dir;
}

function ttSanitizeBase(string $name): string {
    $name = preg_replace('/[\\\/:*?"<>|]+/u', '_', $name);
    $name = preg_replace('/\s+/', '_', $name);
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '', $name);
    $name = trim($name, '._-');
    return $name !== '' ? $name : 'datei';
}

function ttHandleUploads(?array $files, int $uploaderId, ?int $todoId = null): void {
    if (!$files || !isset($files['name']) || !is_array($files['name'])) return;
    $dir = ttEnsureUploadDir($todoId);
    $allowedExt = ['pdf','jpg','jpeg','png','doc','docx','eml','msg'];
    $maxSize = 10 * 1024 * 1024; // 10 MB pro Datei
    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
    $allowedMime = [
        'application/pdf','image/jpeg','image/png','application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'message/rfc822','application/vnd.ms-outlook'
    ];
    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        $err = (int)($files['error'][$i] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) continue;
        $tmp = $files['tmp_name'][$i] ?? '';
        if (!is_uploaded_file($tmp)) continue;
        $orig = (string)($files['name'][$i] ?? '');
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) { @unlink($tmp); continue; }
        $size = (int)($files['size'][$i] ?? 0);
        if ($size <= 0 || $size > $maxSize) { @unlink($tmp); continue; }
        $mime = $finfo ? @finfo_file($finfo, $tmp) : ($files['type'][$i] ?? '');
        if ($mime && !in_array($mime, $allowedMime, true)) { @unlink($tmp); continue; }
        $base = ttSanitizeBase(pathinfo($orig, PATHINFO_FILENAME));
        $stamp = date('Ymd_His');
        $rand = bin2hex(random_bytes(3));
        // Prefix with todo id for flat storage to associate files to a task
        $tidPrefix = ($todoId && $todoId > 0) ? ('t' . (int)$todoId . '_') : '';
        $destName = $tidPrefix . $stamp . '_u' . $uploaderId . '_' . $rand . '_' . $base . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $destName;
        @move_uploaded_file($tmp, $dest);
        // no DB storage on purpose
    }
    if ($finfo) { @finfo_close($finfo); }
}

// Copy existing uploaded files from one To-Do folder to another (used for multi-empfänger)
function ttCopyUploads(int $fromTodoId, int $toTodoId): void {
    if ($fromTodoId <= 0 || $toTodoId <= 0) return;
    $dir = ttEnsureUploadDir(null);
    // Copy flat files that belong to fromTodoId (prefix t<id>_)
    $prefixFrom = 't' . (int)$fromTodoId . '_';
    $prefixTo   = 't' . (int)$toTodoId   . '_';
    $files = @scandir($dir) ?: [];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        if (strpos($f, $prefixFrom) !== 0) continue;
        $src = $dir . DIRECTORY_SEPARATOR . $f;
        if (!is_file($src)) continue;
        $destName = $prefixTo . substr($f, strlen($prefixFrom));
        $dest = $dir . DIRECTORY_SEPARATOR . $destName;
        // Ensure unique
        if (file_exists($dest)) {
            $pi = pathinfo($destName);
            $base = $pi['filename'] ?? 'file';
            $ext = isset($pi['extension']) && $pi['extension'] !== '' ? ('.' . $pi['extension']) : '';
            $i = 1;
            do { $dest = $dir . DIRECTORY_SEPARATOR . $base . '_' . $i . $ext; $i++; } while (file_exists($dest));
        }
        @copy($src, $dest);
    }
    // Also copy legacy nested files if present
    $legacy = $dir . DIRECTORY_SEPARATOR . (string)$fromTodoId;
    if (is_dir($legacy)) {
        $files = @scandir($legacy) ?: [];
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $src = $legacy . DIRECTORY_SEPARATOR . $f;
            if (!is_file($src)) continue;
            $destName = $prefixTo . $f;
            $dest = $dir . DIRECTORY_SEPARATOR . $destName;
            if (file_exists($dest)) {
                $pi = pathinfo($destName);
                $base = $pi['filename'] ?? 'file';
                $ext = isset($pi['extension']) && $pi['extension'] !== '' ? ('.' . $pi['extension']) : '';
                $i = 1;
                do { $dest = $dir . DIRECTORY_SEPARATOR . $base . '_' . $i . $ext; $i++; } while (file_exists($dest));
            }
            @copy($src, $dest);
        }
    }
}

function ttListAttachments(int $todoId): array {
    $out = [];
    if ($todoId <= 0) return $out;
    $dir = ttEnsureUploadDir(null);
    if (!is_dir($dir)) return $out;
    $prefix = 't' . (int)$todoId . '_';
    // Scan flat storage
    $files = @scandir($dir) ?: [];
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        if (strpos($f, $prefix) !== 0) continue;
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        if (is_file($path)) {
            $out[] = [
                'name' => $f,
                'size' => filesize($path) ?: 0,
                'url'  => 'todo_file.php?id=' . urlencode((string)$todoId) . '&file=' . urlencode($f),
            ];
        }
    }
    // Also support legacy nested folder for existing files
    $legacy = $dir . DIRECTORY_SEPARATOR . (string)$todoId;
    if (is_dir($legacy)) {
        $dh = @opendir($legacy);
        if ($dh) {
            while (($f = readdir($dh)) !== false) {
                if ($f === '.' || $f === '..') continue;
                $path = $legacy . DIRECTORY_SEPARATOR . $f;
                if (is_file($path)) {
                    $out[] = [
                        'name' => $f,
                        'size' => filesize($path) ?: 0,
                        'url'  => 'todo_file.php?id=' . urlencode((string)$todoId) . '&file=' . urlencode($f),
                    ];
                }
            }
            closedir($dh);
        }
    }
    // Sort newest first
    usort($out, function($a,$b){ return strcmp($b['name'], $a['name']); });
    return $out;
}

function mondayToIsoWeek(string $mondayDate): string {
    $d = DateTime::createFromFormat('Y-m-d', $mondayDate) ?: new DateTime();
    return $d->format('o-\WW');
}

// Build 7 day objects for one ISO week starting on Monday
function buildWeekDays(string $mondayYmd, int $daysCount = 7): array {
    $days = [];
    $names = ['Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag','Sonntag'];
    $d = DateTime::createFromFormat('Y-m-d', $mondayYmd) ?: new DateTime();
    for ($i = 0; $i < $daysCount; $i++) {
        $di = clone $d; $di->modify("+{$i} day");
        $days[] = [
            'label' => $names[$i % 7],
            'date'  => $di->format('Y-m-d'),
            'short' => $di->format('d.m.'),
        ];
    }
    return $days;
}

// Compute due date based on priority rules relative to a start date (todo_date)
function computeDueByPriority(?string $startYmd, string $prio): ?string {
    if (!$startYmd || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startYmd)) return null;
    try {
        $d = new DateTime($startYmd);
    } catch (Exception $e) { return null; }
    $p = strtolower($prio);
    // Mapping per requirement: hoch=+1 Tag, mittel=+2 Tage, niedrig=+7 Tage
    if ($p === 'hoch')        { $d->modify('+1 day'); }
    elseif ($p === 'mittel')  { $d->modify('+2 days'); }
    elseif ($p === 'niedrig') { $d->modify('+7 days'); }
    else { /* unknown: keep */ }
    return $d->format('Y-m-d');
}

$userId = (int)$_SESSION['user_id'];
$roleId = currentRole();
$canSelectUser = in_array((int)$roleId, [1,2], true); // controls header user filter + destructive rights

// Load user list for modal assignment (everyone may assign To-Dos to others)
$allUsers = [];
$resU = $conn->query("SELECT id, COALESCE(NULLIF(TRIM(CONCAT(vorname, ' ', nachname)), ''), email) AS name FROM user WHERE aktiv = 1 ORDER BY name ASC");
if ($resU) {
    while ($row = $resU->fetch_assoc()) { $allUsers[] = $row; }
    $resU->close();
}

// Determine week in scope (default current week)
$selectedWeek = $_GET['week'] ?? '';
if ($selectedWeek === '') {
    $selectedWeek = (new DateTime())->format('o-\WW');
}
$weekStart = isoWeekToMonday($selectedWeek);
// Selected user for viewing (admins can select)
$selectedUserId = $userId;
if ($canSelectUser) {
    $tmp = (int)($_GET['user'] ?? $userId);
    if ($tmp > 0) { $selectedUserId = $tmp; }
}
// Compute end of the selected week (7 days)
$weekEndObj = DateTime::createFromFormat('Y-m-d', $weekStart) ?: new DateTime();
$weekEndObj->modify('+6 days');
$rangeEnd = $weekEndObj->format('Y-m-d');
// Heute-Ansicht: aktuelles Datum
$todayYmd = date('Y-m-d');

// Status-Filter: 'alle' | 'offen' | 'bearbeitung' | 'erledigt'
$selectedStatus = strtolower(trim($_GET['status'] ?? 'alle'));
if (!in_array($selectedStatus, ['alle','offen','bearbeitung','erledigt'], true)) { $selectedStatus = 'alle'; }

// Priority filter: 'alle' | 'hoch' | 'mittel' | 'niedrig'
$selectedPrio = strtolower(trim($_GET['prio'] ?? 'alle'));
if (!in_array($selectedPrio, ['alle','hoch','mittel','niedrig'], true)) { $selectedPrio = 'alle'; }

// Repeat filter: 'alle' | 'none' | 'daily' | 'weekly' | 'monthly'
$selectedRepeat = strtolower(trim($_GET['repeat'] ?? 'alle'));
if (!in_array($selectedRepeat, ['alle','none','daily','weekly','monthly'], true)) { $selectedRepeat = 'alle'; }

// Archive view toggle
$showArchive = isset($_GET['archive']) && $_GET['archive'] == '1';

// Category to open in inline edit mode after adding
$editCatId = (int)($_GET['edit_cat'] ?? 0);

// Archive sort: created/completed asc/desc (only relevant in archive view)
$selectedSort = strtolower(trim($_GET['sort'] ?? ''));
if (!in_array($selectedSort, ['created_desc','created_asc','completed_desc','completed_asc'], true)) {
    $selectedSort = '';
}

// Additional archive filters
$archiveLimit = (int)($_GET['limit'] ?? 50);
if ($archiveLimit < 1) { $archiveLimit = 50; }
$archiveSearch = trim($_GET['search'] ?? '');
$archiveYear = (int)($_GET['year'] ?? 0);
$archiveFrom = $_GET['from'] ?? '';
$archiveTo = $_GET['to'] ?? '';
if ($archiveFrom !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $archiveFrom)) { $archiveFrom = ''; }
if ($archiveTo !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $archiveTo)) { $archiveTo = ''; }
if ($archiveYear < 1970 || $archiveYear > 2100) { $archiveYear = 0; }

// Auto-archive: mark done tasks older than 5 days as archived
$conn->query("UPDATE todos SET archived = 1, archived_at = IFNULL(archived_at, NOW()) WHERE archived = 0 AND is_done = 1 AND completed_at IS NOT NULL AND completed_at <= DATE_SUB(NOW(), INTERVAL 5 DAY)");

// Handle POST actions: add todo, toggle done, move between days
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $postedStatus = strtolower(trim($_POST['status'] ?? $selectedStatus));
    if (!in_array($postedStatus, ['alle','offen','bearbeitung','erledigt'], true)) { $postedStatus = 'alle'; }
    $postedSort = strtolower(trim($_POST['sort'] ?? $selectedSort));
    if (!in_array($postedSort, ['','created_desc','created_asc','completed_desc','completed_asc'], true)) { $postedSort = ''; }

    // Manual archive button: archive all done tasks for the current scope
    if (isset($_POST['archive_now']) && $_POST['archive_now'] == '1') {
        $scopeUser = (int)($_POST['view_user'] ?? $selectedUserId);
        if ($canSelectUser) {
            $stmt = $conn->prepare("UPDATE todos SET archived = 1, archived_at = IFNULL(archived_at, NOW()) WHERE user_id = ? AND is_done = 1 AND archived = 0");
            if ($stmt) { $stmt->bind_param('i', $scopeUser); $stmt->execute(); $stmt->close(); }
        } else {
            // Only own tasks
            $stmt = $conn->prepare("UPDATE todos SET archived = 1, archived_at = IFNULL(archived_at, NOW()) WHERE user_id = ? AND is_done = 1 AND archived = 0");
            if ($stmt) { $stmt->bind_param('i', $userId); $stmt->execute(); $stmt->close(); }
        }
        // Redirect back preserving current GET filters
        $qs = $_GET; $url = 'todo.php'; if (!empty($qs)) { $url .= '?' . http_build_query($qs); }
        header('Location: ' . $url);
        exit;
    }

    if ($action === 'add_category') {
        $name = trim($_POST['cat_name'] ?? '');
        $ajax = isset($_POST['ajax']) && $_POST['ajax'] == '1';
        $newId = 0;
        if ($name !== '') {
            $next = 1;
            $resSO = $conn->query("SELECT COALESCE(MAX(sort_order),0) AS m FROM todo_categories WHERE owner_id = " . (int)$userId);
            if ($resSO && ($r = $resSO->fetch_assoc())) { $next = ((int)$r['m']) + 1; }
            if ($resSO) { $resSO->close(); }
            $stmt = $conn->prepare('INSERT IGNORE INTO todo_categories (name, sort_order, owner_id, created_by) VALUES (?, ?, ?, ?)');
            if ($stmt) { $stmt->bind_param('siii', $name, $next, $userId, $userId); $stmt->execute(); $stmt->close(); }
            $stmt2 = $conn->prepare('SELECT id FROM todo_categories WHERE name = ? AND owner_id = ?');
            if ($stmt2) { $stmt2->bind_param('si', $name, $userId); $stmt2->execute(); $res2 = $stmt2->get_result(); if ($row2 = $res2->fetch_assoc()) { $newId = (int)$row2['id']; } $stmt2->close(); }
        }
        if ($ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=> ($newId>0), 'id'=>$newId, 'name'=>$name]);
            exit;
        }
        // After adding via form, redirect and open inline rename
        $qs = $_GET;
        if ($newId > 0) { $qs['edit_cat'] = $newId; }
        $url = 'todo.php'; if (!empty($qs)) { $url .= '?' . http_build_query($qs); }
        header('Location: ' . $url);
        exit;
    }

  if ($action === 'rename_category') {
        $catId = (int)($_POST['cat_id'] ?? 0);
        $newName = trim($_POST['new_name'] ?? '');
        if ($catId > 0 && $newName !== '') {
            // Check owner of category
            $owner = null;
            $stmtS = $conn->prepare('SELECT owner_id FROM todo_categories WHERE id = ?');
            if ($stmtS) { $stmtS->bind_param('i', $catId); $stmtS->execute(); $stmtS->bind_result($owner); $stmtS->fetch(); $stmtS->close(); }
            $isGlobal = ($owner === null);
            $allowed = ($owner == $userId) || ($isGlobal && $canSelectUser);
            if ($allowed) {
                $stmt = $conn->prepare('UPDATE todo_categories SET name = ? WHERE id = ?');
                if ($stmt) { $stmt->bind_param('si', $newName, $catId); $stmt->execute(); $stmt->close(); }
  }

  
        }
        $qs = $_GET; $url = 'todo.php'; if (!empty($qs)) { $url .= '?' . http_build_query($qs); }
        header('Location: ' . $url);
        exit;
    }

    if ($action === 'delete_category') {
        $catId = (int)($_POST['cat_id'] ?? 0);
        if ($catId > 0) {
            // Allow deleting own categories or global categories for admins ($canSelectUser)
            $owner = null;
            $stmtChk = $conn->prepare('SELECT owner_id FROM todo_categories WHERE id = ?');
            if ($stmtChk) {
                $stmtChk->bind_param('i', $catId);
                $stmtChk->execute();
                $stmtChk->bind_result($owner);
                if ($stmtChk->fetch()) {
                    $isOwn = ($owner == $userId);
                    $isGlobal = ($owner === null);
                    $allowed = $isOwn || ($isGlobal && $canSelectUser);
                } else { $allowed = false; }
                $stmtChk->close();
                if ($allowed) {
                    // Detach todos from this category, then delete category
                    $stmtU = $conn->prepare('UPDATE todos SET category_id = NULL WHERE category_id = ?');
                    if ($stmtU) { $stmtU->bind_param('i', $catId); $stmtU->execute(); $stmtU->close(); }
                    $stmtD = $conn->prepare('DELETE FROM todo_categories WHERE id = ?');
                    if ($stmtD) { $stmtD->bind_param('i', $catId); $stmtD->execute(); $stmtD->close(); }
                }
            }
        }
        $qs = $_GET; $url = 'todo.php'; if (!empty($qs)) { $url .= '?' . http_build_query($qs); }
        header('Location: ' . $url);
        exit;
    }

    if ($action === 'edit_category') {
        $catId    = (int)($_POST['cat_id'] ?? 0);
        $newName  = trim($_POST['new_name'] ?? '');
        $shareIds = array_map('intval', $_POST['share_user_ids'] ?? []);
        if ($catId > 0) {
            // Ensure current user owns the category before editing
            $ownerId = null;
            if ($stmtOwn = $conn->prepare('SELECT owner_id FROM todo_categories WHERE id = ?')) {
                $stmtOwn->bind_param('i', $catId);
                $stmtOwn->execute();
                $stmtOwn->bind_result($ownerId);
                $stmtOwn->fetch();
                $stmtOwn->close();
            }
            if ($ownerId == $userId) {
                if ($newName !== '') {
                    $stmt = $conn->prepare('UPDATE todo_categories SET name = ? WHERE id = ? AND owner_id = ?');
                    if ($stmt) { $stmt->bind_param('sii', $newName, $catId, $userId); $stmt->execute(); $stmt->close(); }
                }
                // Update share mappings
                if ($stmtDel = $conn->prepare('DELETE FROM todo_category_shares WHERE category_id = ?')) {
                    $stmtDel->bind_param('i', $catId);
                    $stmtDel->execute();
                    $stmtDel->close();
                }
                if (!empty($shareIds)) {
                    if ($stmtIns = $conn->prepare('INSERT INTO todo_category_shares (category_id, user_id) VALUES (?, ?)')) {
                        foreach ($shareIds as $sid) {
                            if ($sid === (int)$userId) continue; // owner need not be in share table
                            $stmtIns->bind_param('ii', $catId, $sid);
                            $stmtIns->execute();
                        }
                        $stmtIns->close();
                    }
                }
            }
        }
        $qs = $_GET; $url = 'todo.php'; if (!empty($qs)) { $url .= '?' . http_build_query($qs); }
        header('Location: ' . $url);
        exit;
    }

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        if (function_exists('mb_substr')) { $title = mb_substr($title, 0, 100); } else { $title = substr($title, 0, 100); }
        $desc = trim($_POST['description'] ?? '');
        if ($title === '') { header('Location: todo.php'); exit; }
        $targetUserIds = [];
        $postedCategoryId = (int)($_POST['category_id'] ?? 0);
        $ids = [];
        $catOwnerId = null;
        if ($postedCategoryId > 0) {
            if ($stmt = $conn->prepare('SELECT owner_id FROM todo_categories WHERE id = ?')) {
                $stmt->bind_param('i', $postedCategoryId);
                $stmt->execute();
                $stmt->bind_result($catOwnerId);
                $stmt->fetch();
                $stmt->close();
            }
            if ($catOwnerId === null) {
                if (!empty($defaultCatId) && $postedCategoryId === (int)$defaultCatId) {
                    $ids[(int)$userId] = true;
                } else {
                    foreach ($allUsers as $u) { $ids[(int)$u['id']] = true; }
                }
            } else {
                $ids[(int)$catOwnerId] = true;
            }
            $resSh = $conn->query('SELECT user_id FROM todo_category_shares WHERE category_id = ' . $postedCategoryId);
            if ($resSh) { while ($r = $resSh->fetch_assoc()) { $ids[(int)$r['user_id']] = true; } $resSh->close(); }
        }
        if (empty($ids)) { $ids[(int)$userId] = true; }
        $targetUserIds = array_keys($ids);
        $weekIn = $_POST['week'] ?? $selectedWeek;
        $ws = isoWeekToMonday($weekIn);
        $td = $_POST['todo_date'] ?? '';
        // Fallback: kein festes Datum nötig in Listenansicht – intern heutiges Datum speichern
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$td)) { $td = date('Y-m-d'); }
        $prioInput = $_POST['priority'] ?? ($selectedPrio !== 'alle' ? $selectedPrio : 'mittel');
        $prio = strtolower(trim($prioInput));
        if (!in_array($prio, ['hoch','mittel','niedrig'], true)) { $prio = 'niedrig'; }
        $start_time = null; // Startzeit entfällt
        $due_date = $_POST['due_date'] ?? '';
        $due_time = null; // Fällige Uhrzeit entfällt
        // Basic validation for date/time inputs
        // start_time nicht mehr genutzt
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date ?? '')) { $due_date = null; }
        // due_time nicht mehr genutzt
        // normalize: ensure week_start matches the picked date
        $tdObj = DateTime::createFromFormat('Y-m-d', $td) ?: new DateTime();
        $ws = (clone $tdObj)->modify('monday this week')->format('Y-m-d');
        // Automatisches Fälligkeitsdatum, falls nicht gesetzt
        if (!$due_date) { $due_date = computeDueByPriority($td, $prio); }
        // Wiederholungen aus Formular
        $repeatFreq = $_POST['repeat_freq'] ?? 'none';
        if (!in_array($repeatFreq, ['none','daily','weekly','monthly'], true)) $repeatFreq = 'none';
        $repeatUntil = $_POST['repeat_until'] ?? null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$repeatUntil)) { $repeatUntil = null; }
        // Insert once title is provided; description may be empty
        if ($title !== '') {
            $creatorId = (int)($_SESSION['user_id'] ?? 0);
            $ownerId = $targetUserIds[0] ?? $creatorId;
            // Determine category for owner (others share same category)
            $categoryId = (int)$postedCategoryId;
            if ($categoryId > 0) {
                $okCat = false;
                if ($defaultCatId && $categoryId === (int)$defaultCatId) { $okCat = true; }
                else {
                    $stmtC = $conn->prepare('SELECT id FROM todo_categories WHERE id = ? AND (owner_id = ? OR owner_id IS NULL OR id IN (SELECT category_id FROM todo_category_shares WHERE user_id = ?))');
                    if ($stmtC) { $stmtC->bind_param('iii', $categoryId, $ownerId, $ownerId); $stmtC->execute(); $resC = $stmtC->get_result(); $okCat = ($resC && $resC->num_rows>0); $stmtC->close(); }
                }
                if (!$okCat) { $categoryId = (int)($defaultCatId ?? 0); }
            } else {
                $categoryId = (int)($defaultCatId ?? 0);
            }
            // Determine next sort order for owner
            $nextOrder = 1;
            $stmtMax = $conn->prepare('SELECT COALESCE(MAX(t.sort_order), 0) AS maxo FROM todos t JOIN todo_assignees ta ON ta.todo_id = t.id WHERE ta.user_id = ? AND t.todo_date = ?');
            if ($stmtMax) { $stmtMax->bind_param('is', $ownerId, $td); $stmtMax->execute(); $resMax = $stmtMax->get_result(); if ($rowMax = $resMax->fetch_assoc()) { $nextOrder = ((int)$rowMax['maxo']) + 1; } $stmtMax->close(); }
            $stmtIns = $conn->prepare('INSERT INTO todos (user_id, title, description, week_start, todo_date, priority, start_time, due_date, due_time, repeat_freq, repeat_until, sort_order, created_by, category_id, sent_scope, dispatch_group) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $dispatchGroup = null; $sentScope = 'single';
            if ($stmtIns) {
                $stmtIns->bind_param('issssssssssiiiss', $ownerId, $title, $desc, $ws, $td, $prio, $start_time, $due_date, $due_time, $repeatFreq, $repeatUntil, $nextOrder, $creatorId, $categoryId, $sentScope, $dispatchGroup);
                $stmtIns->execute();
                $newId = (int)$conn->insert_id;
                $stmtIns->close();
                // Insert assignees
                if ($newId > 0) {
                    if ($stmtA = $conn->prepare('INSERT INTO todo_assignees (todo_id, user_id) VALUES (?, ?)')) {
                        foreach ($targetUserIds as $targetUserId) {
                            $stmtA->bind_param('ii', $newId, $targetUserId);
                            $stmtA->execute();
                        }
                        $stmtA->close();
                    }
                    if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
                        ttHandleUploads($_FILES['attachments'], (int)($_SESSION['user_id'] ?? 0), $newId);
                    }
                }
            }
        }
        $redir = 'todo.php?status=' . urlencode($postedStatus) . '&prio=' . urlencode($selectedPrio) . '&sort=' . urlencode($postedSort) . '&user=' . (int)$selectedUserId;
        if (!empty($postedCategoryId)) { $redir .= '&open_cat=' . (int)$postedCategoryId; }
        header('Location: ' . $redir);
        exit;
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $done = (int)($_POST['done'] ?? 0);
        // permission: owner, creator or admin
        $allowed = false; $ownerId = null; $creatorId = null; $currDone = null; $inProgBy = null; $assigned = 0;
        $stmtC = $conn->prepare('SELECT t.user_id, t.created_by, t.is_done, t.in_progress_by, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t WHERE t.id = ?');
        if ($stmtC) {
            $stmtC->bind_param('ii', $userId, $id);
            $stmtC->execute();
            $stmtC->bind_result($ownerId, $creatorId, $currDone, $inProgBy, $assigned);
            if ($stmtC->fetch()) {
                $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser;
            }
            $stmtC->close();
        }
        if ($allowed && (int)$currDone === 2 && $done !== 2 && (int)$inProgBy !== (int)$userId) {
            $allowed = false;
        }
        if ($allowed) {
            $stmt = $conn->prepare('UPDATE todos SET is_done = ?, completed_at = CASE WHEN ? = 1 THEN NOW() ELSE NULL END, completed_by = CASE WHEN ? = 1 THEN ? ELSE NULL END, in_progress_by = CASE WHEN ? = 2 THEN ? ELSE NULL END, in_progress_at = CASE WHEN ? = 2 THEN NOW() ELSE NULL END WHERE id = ?');
            if ($stmt) { $stmt->bind_param('iiiiiiii', $done, $done, $done, $userId, $done, $userId, $done, $id); $stmt->execute(); $stmt->close(); }
        }
        $redirUser = (int)($_POST['view_user'] ?? $selectedUserId);
        header('Location: todo.php?status=' . urlencode($postedStatus) . '&prio=' . urlencode($selectedPrio) . '&sort=' . urlencode($postedSort) . '&user=' . $redirUser);
        exit;
    }

    if ($action === 'move') {
        $id = (int)($_POST['id'] ?? 0);
        $newDate = $_POST['todo_date'] ?? '';
        $dObj = DateTime::createFromFormat('Y-m-d', $newDate);
        if ($id > 0 && $dObj) {
            $newWeek = (clone $dObj)->modify('monday this week')->format('Y-m-d');
            // permission check
            $allowed = false; $creatorId = null; $isDone = null; $inProgBy = null; $inProgName = ''; $assigned = 0;
            $stmtC = $conn->prepare('SELECT t.created_by, t.is_done, t.in_progress_by, COALESCE(NULLIF(TRIM(CONCAT(u.vorname, " ", u.nachname)), ""), u.email) AS in_progress_name, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t LEFT JOIN user u ON u.id = t.in_progress_by WHERE t.id = ?');
            if ($stmtC) { $stmtC->bind_param('ii', $userId, $id); $stmtC->execute(); $stmtC->bind_result($creatorId, $isDone, $inProgBy, $inProgName, $assigned); if ($stmtC->fetch()) { $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser; } $stmtC->close(); }
            if ((int)$isDone === 2 && (int)$inProgBy !== (int)$userId) { http_response_code(409); echo 'Aufgabe von ' . htmlspecialchars($inProgName, ENT_QUOTES) . ' gesperrt'; exit; }
            if ($allowed) {
                $stmt = $conn->prepare('UPDATE todos SET todo_date = ?, week_start = ? WHERE id = ?');
                if ($stmt) { $stmt->bind_param('ssi', $newDate, $newWeek, $id); $stmt->execute(); $stmt->close(); }
            }
        }
        $redirUser = (int)($_POST['view_user'] ?? $selectedUserId);
        header('Location: todo.php?status=' . urlencode($postedStatus) . '&prio=' . urlencode($selectedPrio) . '&sort=' . urlencode($postedSort) . '&user=' . $redirUser);
        exit;
    }

    if ($action === 'reorder') {
        // expects: date=YYYY-MM-DD, ids=comma-separated
        $date = $_POST['date'] ?? '';
        $idsStr = $_POST['ids'] ?? '';
        $newPrio = $_POST['priority'] ?? '';
        if (!in_array($newPrio, ['hoch','mittel','niedrig'], true)) { $newPrio = null; }
        $hasCat = array_key_exists('category', $_POST);
        $newCat = $hasCat ? (int)($_POST['category'] ?? 0) : null; // 0 => clear
        if ($newCat !== null) {
            if ($newCat === 0) { /* clear allowed */ }
            else {
                $okCat2 = false;
                if ($defaultCatId && $newCat === (int)$defaultCatId) { $okCat2 = true; }
                else {
                    $stmtVC = $conn->prepare('SELECT id FROM todo_categories WHERE id = ? AND (owner_id = ? OR owner_id IS NULL OR id IN (SELECT category_id FROM todo_category_shares WHERE user_id = ?))');
                    if ($stmtVC) { $stmtVC->bind_param('iii', $newCat, $userId, $userId); $stmtVC->execute(); $resVC = $stmtVC->get_result(); $okCat2 = ($resVC && $resVC->num_rows>0); $stmtVC->close(); }
                }
                if (!$okCat2) { $newCat = 0; }
            }
        }
        $dObj = $date ? DateTime::createFromFormat('Y-m-d', $date) : null;
        if ($idsStr === '') {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'missing_ids']);
            exit;
        }
        $ids = array_filter(array_map('intval', explode(',', $idsStr)));
        if (empty($ids)) {
            header('Content-Type: application/json');
            echo json_encode(['ok'=>false,'error'=>'invalid_ids']);
            exit;
        }
        $newWeek = $dObj ? (clone $dObj)->modify('monday this week')->format('Y-m-d') : null;
        $conn->begin_transaction();
        $pos = 1;
        foreach ($ids as $tid) {
            // permission per item
            $allowed = false; $creatorId = null; $isDone = null; $inProgBy = null; $inProgName = ''; $assigned = 0;
            $stmtC = $conn->prepare('SELECT t.created_by, t.is_done, t.in_progress_by, COALESCE(NULLIF(TRIM(CONCAT(u.vorname, " ", u.nachname)), ""), u.email) AS in_progress_name, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t LEFT JOIN user u ON u.id = t.in_progress_by WHERE t.id = ?');
            if ($stmtC) { $stmtC->bind_param('ii', $userId, $tid); $stmtC->execute(); $stmtC->bind_result($creatorId, $isDone, $inProgBy, $inProgName, $assigned); if ($stmtC->fetch()) { $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser; } $stmtC->close(); }
            if ((int)$isDone === 2 && (int)$inProgBy !== (int)$userId) { $conn->rollback(); header('Content-Type: application/json'); echo json_encode(['ok'=>false,'error'=>'locked','user'=>$inProgName]); exit; }
            if (!$allowed) continue;
            // Persist reordering. If priority changes, also recompute and persist due_date.
            $newDueForThis = null;
            if ($newPrio) {
                // Determine effective start date: prefer posted $date, else current todo_date from DB
                $effectiveStart = $date ?: null;
                if (!$effectiveStart) {
                    $q = $conn->prepare('SELECT todo_date FROM todos WHERE id = ?');
                    if ($q) { $q->bind_param('i', $tid); $q->execute(); $q->bind_result($effectiveStart); $q->fetch(); $q->close(); }
                }
                $newDueForThis = computeDueByPriority($effectiveStart, $newPrio);
            }
            if ($date && $newWeek) {
                if ($newPrio && $hasCat) {
                    $catParam = ($newCat && $newCat > 0) ? $newCat : null; // null clears
                    if ($catParam !== null) {
                        $stmtUp = $conn->prepare('UPDATE todos SET todo_date = ?, week_start = ?, priority = ?, due_date = ?, category_id = ?, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('ssssiii', $date, $newWeek, $newPrio, $newDueForThis, $catParam, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    } else {
                        $stmtUp = $conn->prepare('UPDATE todos SET todo_date = ?, week_start = ?, priority = ?, due_date = ?, category_id = NULL, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('sssiii', $date, $newWeek, $newPrio, $newDueForThis, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    }
                } elseif ($newPrio) {
                    $stmtUp = $conn->prepare('UPDATE todos SET todo_date = ?, week_start = ?, priority = ?, due_date = ?, sort_order = ? WHERE id = ?');
                    if ($stmtUp) { $stmtUp->bind_param('ssssii', $date, $newWeek, $newPrio, $newDueForThis, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                } elseif ($hasCat) {
                    $catParam = ($newCat && $newCat > 0) ? $newCat : null;
                    if ($catParam !== null) {
                        $stmtUp = $conn->prepare('UPDATE todos SET todo_date = ?, week_start = ?, category_id = ?, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('ssiii', $date, $newWeek, $catParam, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    } else {
                        $stmtUp = $conn->prepare('UPDATE todos SET todo_date = ?, week_start = ?, category_id = NULL, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('ssii', $date, $newWeek, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    }
                } else {
                    $stmtUp = $conn->prepare('UPDATE todos SET todo_date = ?, week_start = ?, sort_order = ? WHERE id = ?');
                    if ($stmtUp) { $stmtUp->bind_param('ssii', $date, $newWeek, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                }
            } else {
                if ($newPrio && $hasCat) {
                    $catParam = ($newCat && $newCat > 0) ? $newCat : null;
                    if ($catParam !== null) {
                        $stmtUp = $conn->prepare('UPDATE todos SET priority = ?, due_date = ?, category_id = ?, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('ssiii', $newPrio, $newDueForThis, $catParam, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    } else {
                        $stmtUp = $conn->prepare('UPDATE todos SET priority = ?, due_date = ?, category_id = NULL, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('ssii', $newPrio, $newDueForThis, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    }
                } elseif ($newPrio) {
                    $stmtUp = $conn->prepare('UPDATE todos SET priority = ?, due_date = ?, sort_order = ? WHERE id = ?');
                    if ($stmtUp) { $stmtUp->bind_param('ssii', $newPrio, $newDueForThis, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                } elseif ($hasCat) {
                    $catParam = ($newCat && $newCat > 0) ? $newCat : null;
                    if ($catParam !== null) {
                        $stmtUp = $conn->prepare('UPDATE todos SET category_id = ?, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('iii', $catParam, $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    } else {
                        $stmtUp = $conn->prepare('UPDATE todos SET category_id = NULL, sort_order = ? WHERE id = ?');
                        if ($stmtUp) { $stmtUp->bind_param('ii', $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                    }
                } else {
                    $stmtUp = $conn->prepare('UPDATE todos SET sort_order = ? WHERE id = ?');
                    if ($stmtUp) { $stmtUp->bind_param('ii', $pos, $tid); $stmtUp->execute(); $stmtUp->close(); }
                }
            }
            $pos++;
        }
        $processed = $pos - 1;
        $conn->commit();
        header('Content-Type: application/json');
        if ($processed === count($ids)) {
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok'=>false,'error'=>'not_all_processed']);
        }
        exit;
    }

    if ($action === 'update') {
        $tid = (int)($_POST['todo_id'] ?? 0);
        $title = trim($_POST['title'] ?? ''); if (function_exists('mb_substr')) { $title = mb_substr($title, 0, 100); } else { $title = substr($title, 0, 100); }
        $desc = trim($_POST['description'] ?? '');
        $prio = strtolower(trim($_POST['priority'] ?? 'mittel'));
        if (!in_array($prio, ['hoch','mittel','niedrig'], true)) { $prio = 'mittel'; }
        $start_time = null; // Startzeit entfällt
        $due_date = $_POST['due_date'] ?? null; // Spätestens am (optional)
        $due_time = null; // Uhrzeit entfällt
        $categoryId = (int)($_POST['category_id'] ?? 0);
        if ($categoryId > 0) {
            $okCat = false;
            if ($defaultCatId && $categoryId === (int)$defaultCatId) { $okCat = true; }
            else {
                $stmtC = $conn->prepare('SELECT id FROM todo_categories WHERE id = ? AND (owner_id = ? OR owner_id IS NULL OR id IN (SELECT category_id FROM todo_category_shares WHERE user_id = ?))');
                if ($stmtC) { $stmtC->bind_param('iii', $categoryId, $userId, $userId); $stmtC->execute(); $resC = $stmtC->get_result(); $okCat = ($resC && $resC->num_rows>0); $stmtC->close(); }
            }
            if (!$okCat) { $categoryId = (int)($defaultCatId ?? 0); }
        } else {
            $categoryId = (int)($defaultCatId ?? 0);
        }
        // Validierungen
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$due_date)) { $due_date = null; }
        // Mögliches neues Startdatum (todo_date) aus Formular
        $newStart = $_POST['todo_date'] ?? null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$newStart)) { $newStart = null; }
        if ($tid > 0) {
            // permission: owner, assigned user, creator oder admin (1,2)
            $allowed = false; $ownerId = null; $creatorId = null; $assigned = 0;
            $stmtChk = $conn->prepare('SELECT t.user_id, t.created_by, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t WHERE t.id = ?');
            if ($stmtChk) { $stmtChk->bind_param('ii', $userId, $tid); $stmtChk->execute(); $stmtChk->bind_result($ownerId, $creatorId, $assigned); if ($stmtChk->fetch()) { $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser; } $stmtChk->close(); }
            if ($allowed) {
                // Aktuelles Startdatum laden, falls nicht im Formular
                $effectiveStart = $newStart;
                if ($effectiveStart === null) {
                    $q = $conn->prepare('SELECT todo_date FROM todos WHERE id = ?');
                    if ($q) { $q->bind_param('i', $tid); $q->execute(); $q->bind_result($effectiveStart); $q->fetch(); $q->close(); }
                }
                // Automatisches Fälligkeitsdatum: nur setzen, wenn im Formular keins angegeben wurde
                if ($due_date === null) {
                    $due_date = computeDueByPriority($effectiveStart, $prio);
                }
                // Update Grunddaten (inkl. optionalem Startdatum)
                if ($newStart !== null) {
                    $stmt = $conn->prepare('UPDATE todos SET title = ?, description = ?, priority = ?, todo_date = ?, start_time = NULL, due_date = ?, due_time = NULL, category_id = ? WHERE id = ?');
                    if ($stmt) { $stmt->bind_param('sssssii', $title, $desc, $prio, $newStart, $due_date, $categoryId, $tid); }
                } else {
                    $stmt = $conn->prepare('UPDATE todos SET title = ?, description = ?, priority = ?, start_time = NULL, due_date = ?, due_time = NULL, category_id = ? WHERE id = ?');
                    if ($stmt) { $stmt->bind_param('ssssii', $title, $desc, $prio, $due_date, $categoryId, $tid); }
                }
                if ($stmt) { $stmt->execute(); $stmt->close(); }
                // Empfänger anhand Kategorie bestimmen
                $wantIds = [];
                $catOwnerId2 = null;
                if ($categoryId > 0) {
                    $stmtCat2 = $conn->prepare('SELECT owner_id FROM todo_categories WHERE id = ?');
                    if ($stmtCat2) { $stmtCat2->bind_param('i', $categoryId); $stmtCat2->execute(); $stmtCat2->bind_result($catOwnerId2); $stmtCat2->fetch(); $stmtCat2->close(); }
                    if ($catOwnerId2 === null) {
                        if (!empty($defaultCatId) && $categoryId === (int)$defaultCatId) {
                            $wantIds[(int)$userId] = true;
                        } else {
                            foreach ($allUsers as $u) { $wantIds[(int)$u['id']] = true; }
                        }
                    } else {
                        $wantIds[(int)$catOwnerId2] = true;
                    }
                    $resSh = $conn->query('SELECT user_id FROM todo_category_shares WHERE category_id = ' . $categoryId);
                    if ($resSh) { while ($r = $resSh->fetch_assoc()) { $wantIds[(int)$r['user_id']] = true; } $resSh->close(); }
                }
                $forwardUserId = (int)($_POST['forward_user'] ?? 0);
                if ($forwardUserId > 0 && $forwardUserId !== (int)$ownerId) {
                    $wantIds = [$forwardUserId];
                } else {
                    if (empty($wantIds)) { $wantIds[(int)$ownerId] = true; }
                    $wantIds = array_keys($wantIds);
                }
                // Update assignee table
                $current = [];
                $stmtCur = $conn->prepare('SELECT user_id FROM todo_assignees WHERE todo_id = ?');
                if ($stmtCur) { $stmtCur->bind_param('i', $tid); $stmtCur->execute(); $stmtCur->bind_result($uid); while ($stmtCur->fetch()) { $current[] = (int)$uid; } $stmtCur->close(); }
                if ($stmtAdd = $conn->prepare('INSERT INTO todo_assignees (todo_id, user_id) VALUES (?, ?)')) {
                    foreach ($wantIds as $uid) {
                        if (!in_array($uid, $current, true)) {
                            $stmtAdd->bind_param('ii', $tid, $uid);
                            $stmtAdd->execute();
                        }
                    }
                    $stmtAdd->close();
                }
                if ($stmtDel = $conn->prepare('DELETE FROM todo_assignees WHERE todo_id = ? AND user_id = ?')) {
                    foreach ($current as $uid) {
                        if (!in_array($uid, $wantIds, true)) {
                            $stmtDel->bind_param('ii', $tid, $uid);
                            $stmtDel->execute();
                        }
                    }
                    $stmtDel->close();
                }
                // Besitzer auf ersten Empfänger setzen
                $newOwner = $wantIds[0] ?? $ownerId;
                $stmtOwner = $conn->prepare('UPDATE todos SET user_id = ? WHERE id = ?');
                if ($stmtOwner) { $stmtOwner->bind_param('ii', $newOwner, $tid); $stmtOwner->execute(); $stmtOwner->close(); }
            }
        }
        // Handle uploads (store on disk only) into todo subfolder
        if (!empty($_FILES['attachments']) && is_array($_FILES['attachments']['name'])) {
            ttHandleUploads($_FILES['attachments'], (int)($_SESSION['user_id'] ?? 0), $tid);
        }
        $redirUser = (int)($_POST['view_user'] ?? $selectedUserId);
        header('Location: todo.php?status=' . urlencode($postedStatus) . '&prio=' . urlencode($selectedPrio) . '&sort=' . urlencode($postedSort) . '&user=' . $redirUser);
        exit;
    }

    if ($action === 'archive_item') {
        $tid = (int)($_POST['id'] ?? 0);
        if ($tid > 0) {
            // permission: assigned user, creator oder admin/HR
            $allowed = false; $creatorId = null; $isDone = null; $inProgBy = null; $inProgName = ''; $assigned = 0;
            $stmtChk = $conn->prepare('SELECT t.created_by, t.is_done, t.in_progress_by, COALESCE(NULLIF(TRIM(CONCAT(u.vorname, " ", u.nachname)), ""), u.email) AS in_progress_name, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t LEFT JOIN user u ON u.id = t.in_progress_by WHERE t.id = ?');
            if ($stmtChk) { $stmtChk->bind_param('ii', $userId, $tid); $stmtChk->execute(); $stmtChk->bind_result($creatorId, $isDone, $inProgBy, $inProgName, $assigned); if ($stmtChk->fetch()) { $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser; } $stmtChk->close(); }
            if ((int)$isDone === 2 && (int)$inProgBy !== (int)$userId) { http_response_code(409); echo 'Aufgabe von ' . htmlspecialchars($inProgName, ENT_QUOTES) . ' gesperrt'; exit; }
            if ($allowed) {
                // nur erledigte Aufgaben archivieren
                $stmt = $conn->prepare('UPDATE todos SET archived = 1, archived_at = NOW() WHERE id = ? AND is_done = 1');
                if ($stmt) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
            }
        }
        $qs = $_GET; $url = 'todo.php'; if (!empty($qs)) { $url .= '?' . http_build_query($qs); }
        header('Location: ' . $url);
        exit;
    }

    if (in_array($action, ['bulk_archive','bulk_unarchive','bulk_reopen','bulk_delete'], true)) {
        $idsStr = $_POST['ids'] ?? '';
        $ids = array_filter(
            array_map('intval', preg_split('/[\s,;]+/', (string)$idsStr)),
            function($v){ return $v > 0; }
        );
        if (!empty($ids)) {
            $lockedBy = null;
            foreach ($ids as $tid) {
                $allowed = false; $ownerId = null; $creatorId = null; $isDone = null; $inProgBy = null; $inProgName = ''; $assigned = 0;
                $stmtChk = $conn->prepare('SELECT t.user_id, t.created_by, t.is_done, t.in_progress_by, COALESCE(NULLIF(TRIM(CONCAT(u.vorname, " ", u.nachname)), ""), u.email) AS in_progress_name, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t LEFT JOIN user u ON u.id = t.in_progress_by WHERE t.id = ?');
                if ($stmtChk) { $stmtChk->bind_param('ii', $userId, $tid); $stmtChk->execute(); $stmtChk->bind_result($ownerId, $creatorId, $isDone, $inProgBy, $inProgName, $assigned); if ($stmtChk->fetch()) { $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser; } $stmtChk->close(); }
                if ((int)$isDone === 2 && (int)$inProgBy !== (int)$userId) { $lockedBy = $inProgName; break; }
                if (!$allowed) { continue; }
                if ($action === 'bulk_archive') {
                    // Gruppe/Scope ermitteln für mögliches Massen-Archivieren (z. B. "Gesendet an alle")
                    $dgVal = null; $scopeVal = 'single';
                    $stmtDG = $conn->prepare('SELECT COALESCE(dispatch_group, \'\'), COALESCE(sent_scope, \'single\') FROM todos WHERE id = ?');
                    if ($stmtDG) { $stmtDG->bind_param('i', $tid); $stmtDG->execute(); $stmtDG->bind_result($dgVal, $scopeVal); $stmtDG->fetch(); $stmtDG->close(); }
                    $dgVal = trim((string)$dgVal);
                    // Für gesendete Aufgaben (erstellt von mir, Besitzer ist jemand anderes) erlauben wir Archivieren auch wenn nicht erledigt
                    if ((int)$creatorId === (int)$userId && (int)$ownerId !== (int)$userId) {
                        if ($dgVal !== '' && ($scopeVal === 'all' || $scopeVal === 'users')) {
                            // Ganze Gruppe archivieren
                            $stmt = $conn->prepare('UPDATE todos SET archived = 1, archived_at = NOW() WHERE dispatch_group = ? AND created_by = ? AND archived = 0');
                            if ($stmt) { $stmt->bind_param('si', $dgVal, $userId); $stmt->execute(); $stmt->close(); }
                        } else {
                            $stmt = $conn->prepare('UPDATE todos SET archived = 1, archived_at = NOW() WHERE id = ?');
                            if ($stmt) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
                        }
                    } else {
                        $stmt = $conn->prepare('UPDATE todos SET archived = 1, archived_at = NOW() WHERE id = ? AND is_done = 1');
                        if ($stmt) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
                    }
                } elseif ($action === 'bulk_unarchive') {
                    $stmt = $conn->prepare('UPDATE todos SET archived = 0, archived_at = NULL WHERE id = ?');
                    if ($stmt) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
                } elseif ($action === 'bulk_reopen') {
                    $stmt = $conn->prepare('UPDATE todos SET is_done = 0, completed_at = NULL, completed_by = NULL, in_progress_by = NULL, in_progress_at = NULL WHERE id = ? AND (is_done <> 2 OR in_progress_by = ?)');
                    if ($stmt) { $stmt->bind_param('ii', $tid, $userId); $stmt->execute(); $stmt->close(); }
                } elseif ($action === 'bulk_delete') {
                    // Only delete archived items and clean up assignees
                    if ($stmtDelA = $conn->prepare('DELETE FROM todo_assignees WHERE todo_id = ?')) { $stmtDelA->bind_param('i', $tid); $stmtDelA->execute(); $stmtDelA->close(); }
                    if ($stmt = $conn->prepare('DELETE FROM todos WHERE id = ? AND archived = 1')) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
                }
            }
            if ($lockedBy !== null) { http_response_code(409); echo 'Aufgabe von ' . htmlspecialchars($lockedBy, ENT_QUOTES) . ' gesperrt'; exit; }
        }
        $redirUser = (int)($_POST['view_user'] ?? $selectedUserId);
        header('Location: todo.php?archive=' . ($showArchive ? '1' : '0') . '&status=' . urlencode($postedStatus) . '&prio=' . urlencode($selectedPrio) . '&sort=' . urlencode($postedSort) . '&user=' . $redirUser . '&limit=' . (int)$archiveLimit . '&search=' . urlencode($archiveSearch) . '&year=' . (int)$archiveYear . '&from=' . urlencode($archiveFrom) . '&to=' . urlencode($archiveTo));
        exit;
    }

    if ($action === 'unarchive_item') {
        $tid = (int)($_POST['id'] ?? 0);
        if ($tid > 0) {
            // permission: assigned user, creator oder admin/HR
            $allowed = false; $creatorId = null; $assigned = 0;
            $stmtChk = $conn->prepare('SELECT t.created_by, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t WHERE t.id = ?');
            if ($stmtChk) { $stmtChk->bind_param('ii', $userId, $tid); $stmtChk->execute(); $stmtChk->bind_result($creatorId, $assigned); if ($stmtChk->fetch()) { $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser; } $stmtChk->close(); }
            if ($allowed) {
                $stmt = $conn->prepare('UPDATE todos SET archived = 0, archived_at = NULL WHERE id = ?');
                if ($stmt) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
            }
        }
        // Redirect to active view so the item appears under Erledigt
        $qs = $_GET; $qs['archive'] = '0';
        $url = 'todo.php?' . http_build_query($qs);
        header('Location: ' . $url);
        exit;
    }

    if ($action === 'delete') {
        // only superadmin (role 1)
        if ((int)$roleId === 1) {
            $tid = (int)($_POST['todo_id'] ?? 0);
            if ($tid > 0) {
                if ($stmt = $conn->prepare('DELETE FROM todo_assignees WHERE todo_id = ?')) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
                $stmt = $conn->prepare('DELETE FROM todos WHERE id = ?');
                if ($stmt) { $stmt->bind_param('i', $tid); $stmt->execute(); $stmt->close(); }
            }
        }
        $redirUser = (int)($_POST['view_user'] ?? $selectedUserId);
        header('Location: todo.php?week=' . urlencode($selectedWeek) . '&status=' . urlencode($postedStatus) . '&prio=' . urlencode($selectedPrio) . '&sort=' . urlencode($postedSort) . '&user=' . $redirUser);
        exit;
    }

    if ($action === 'delete_attachment') {
        $tid = (int)($_POST['todo_id'] ?? 0);
        $filename = basename((string)($_POST['file'] ?? ''));
        header('Content-Type: application/json');
        if ($tid <= 0 || $filename === '') { echo json_encode(['ok'=>false,'error'=>'bad_request']); exit; }
        // permission: assigned user, creator oder admin/HR
        $allowed = false; $creatorId = null; $assigned = 0;
        $stmtChk = $conn->prepare('SELECT t.created_by, EXISTS(SELECT 1 FROM todo_assignees ta WHERE ta.todo_id = t.id AND ta.user_id = ?) AS assigned FROM todos t WHERE t.id = ?');
        if ($stmtChk) { $stmtChk->bind_param('ii', $userId, $tid); $stmtChk->execute(); $stmtChk->bind_result($creatorId, $assigned); if ($stmtChk->fetch()) { $allowed = ($assigned == 1) || ($creatorId == $userId) || $canSelectUser; } $stmtChk->close(); }
        if (!$allowed) { echo json_encode(['ok'=>false,'error'=>'forbidden']); exit; }
        $root = __DIR__ . DIRECTORY_SEPARATOR . 'uploads_todo';
        $pathRoot = $root . DIRECTORY_SEPARATOR . $filename;
        $pathNested = $root . DIRECTORY_SEPARATOR . $tid . DIRECTORY_SEPARATOR . $filename;
        $path = is_file($pathRoot) ? $pathRoot : $pathNested;
        if (!is_file($path)) { echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
        $ok = @unlink($path);
        echo json_encode(['ok'=>(bool)$ok]);
        exit;
    }

}

// Load todos for selected week and user
$todos = [];
// Alle Aufgaben ohne Datumseinschränkung
if ($canSelectUser) {
    // Admin/HR: Zeige Aufgaben des ausgewählten Benutzers – sowohl zugewiesene als auch von ihm erstellte
    $baseSql = 'SELECT t.id, t.user_id, t.title, t.description, t.is_done, t.archived, t.created_at, t.completed_at, t.completed_by, t.todo_date, t.priority, t.start_time, t.due_date, t.due_time, t.repeat_freq, t.repeat_until, t.sort_order, t.created_by, t.category_id, t.sent_scope, t.dispatch_group, t.is_forwarded, t.in_progress_by, t.in_progress_at,
                       COALESCE(NULLIF(TRIM(CONCAT(uc.vorname, " ", uc.nachname)), ""), uc.email) AS creator_name,
                       COALESCE(NULLIF(TRIM(CONCAT(ur.vorname, " ", ur.nachname)), ""), ur.email) AS recipient_name,
                       COALESCE(NULLIF(TRIM(CONCAT(up.vorname, " ", up.nachname)), ""), up.email) AS in_progress_name,
                       COALESCE(NULLIF(TRIM(CONCAT(ud.vorname, " ", ud.nachname)), ""), ud.email) AS completed_name
                FROM todos t
                LEFT JOIN todo_assignees ta ON ta.todo_id = t.id AND ta.user_id = ?
                LEFT JOIN user uc ON uc.id = t.created_by
                LEFT JOIN user ur ON ur.id = ta.user_id
                LEFT JOIN user up ON up.id = t.in_progress_by
                LEFT JOIN user ud ON ud.id = t.completed_by
                WHERE (ta.user_id IS NOT NULL OR t.created_by = ?) AND t.archived = ?';
} else {
    // Für normale Benutzer: Nur Aufgaben, denen der Benutzer zugewiesen ist
    $baseSql = 'SELECT t.id, t.user_id, t.title, t.description, t.is_done, t.archived, t.created_at, t.completed_at, t.completed_by, t.todo_date, t.priority, t.start_time, t.due_date, t.due_time, t.repeat_freq, t.repeat_until, t.sort_order, t.created_by, t.category_id, t.sent_scope, t.dispatch_group, t.is_forwarded, t.in_progress_by, t.in_progress_at,
                       COALESCE(NULLIF(TRIM(CONCAT(uc.vorname, " ", uc.nachname)), ""), uc.email) AS creator_name,
                       COALESCE(NULLIF(TRIM(CONCAT(ur.vorname, " ", ur.nachname)), ""), ur.email) AS recipient_name,
                       COALESCE(NULLIF(TRIM(CONCAT(up.vorname, " ", up.nachname)), ""), up.email) AS in_progress_name,
                       COALESCE(NULLIF(TRIM(CONCAT(ud.vorname, " ", ud.nachname)), ""), ud.email) AS completed_name
                FROM todos t
                JOIN todo_assignees ta ON ta.todo_id = t.id
                LEFT JOIN user uc ON uc.id = t.created_by
                LEFT JOIN user ur ON ur.id = ta.user_id
                LEFT JOIN user up ON up.id = t.in_progress_by
                LEFT JOIN user ud ON ud.id = t.completed_by
                WHERE ta.user_id = ? AND t.archived = ?';
}
if ($selectedStatus === 'offen') {
    $baseSql .= ' AND t.is_done = 0';
} elseif ($selectedStatus === 'bearbeitung') {
    $baseSql .= ' AND t.is_done = 2';
} elseif ($selectedStatus === 'erledigt') {
    $baseSql .= ' AND t.is_done = 1';
}
if ($selectedPrio !== 'alle') {
    $baseSql .= ' AND t.priority = ?';
}
if ($selectedRepeat !== 'alle') {
    $baseSql .= ' AND t.repeat_freq = ?';
}
// Ordering: In archive view allow custom sort by created/completed; otherwise keep default
if ($showArchive && $selectedSort !== '') {
    if ($selectedSort === 'created_desc') {
        $baseSql .= " ORDER BY t.created_at DESC, t.id DESC";
    } elseif ($selectedSort === 'created_asc') {
        $baseSql .= " ORDER BY t.created_at ASC, t.id ASC";
    } elseif ($selectedSort === 'completed_desc') {
        // Put rows without completed_at at the end
        $baseSql .= " ORDER BY (t.completed_at IS NULL) ASC, t.completed_at DESC, t.id DESC";
    } elseif ($selectedSort === 'completed_asc') {
        $baseSql .= " ORDER BY (t.completed_at IS NULL) ASC, t.completed_at ASC, t.id ASC";
    }
} else {
    $baseSql .= " ORDER BY FIELD(t.is_done,0,2,1), FIELD(t.priority,'hoch','mittel','niedrig'), COALESCE(t.sort_order, 999999) ASC, t.created_at ASC, t.id ASC";
}
$stmt = $conn->prepare($baseSql);
if ($stmt) {
    $arch = $showArchive ? 1 : 0;
    if ($canSelectUser) {
        if ($selectedPrio !== 'alle' && $selectedRepeat !== 'alle') {
            $stmt->bind_param('iiiss', $selectedUserId, $selectedUserId, $arch, $selectedPrio, $selectedRepeat);
        } elseif ($selectedPrio !== 'alle') {
            $stmt->bind_param('iiis', $selectedUserId, $selectedUserId, $arch, $selectedPrio);
        } elseif ($selectedRepeat !== 'alle') {
            $stmt->bind_param('iiis', $selectedUserId, $selectedUserId, $arch, $selectedRepeat);
        } else {
            $stmt->bind_param('iii', $selectedUserId, $selectedUserId, $arch);
        }
    } else {
        if ($selectedPrio !== 'alle' && $selectedRepeat !== 'alle') {
            $stmt->bind_param('iiss', $userId, $arch, $selectedPrio, $selectedRepeat);
        } elseif ($selectedPrio !== 'alle') {
            $stmt->bind_param('iis', $userId, $arch, $selectedPrio);
        } elseif ($selectedRepeat !== 'alle') {
            $stmt->bind_param('iis', $userId, $arch, $selectedRepeat);
        } else {
            $stmt->bind_param('ii', $userId, $arch);
        }
    }
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $todos[] = $row; }
    $stmt->close();
}

// Group by date for columns
// In eigener Ansicht (auch für Admin, wenn eigener Benutzer ausgewählt) gesendete Aufgaben
// nicht in den regulären Listen (Prioritäten/Kategorien) anzeigen
if (!$showArchive && ($canSelectUser ? ((int)$selectedUserId === (int)$userId) : true)) {
    $filtered = [];
    foreach ($todos as $t) {
        $isSentByMe = ((int)($t['created_by'] ?? 0) === (int)$userId) && ((int)($t['user_id'] ?? 0) !== (int)$userId);
        if ($isSentByMe) { continue; }
        $filtered[] = $t;
    }
    $todos = $filtered;
}


// Gemeinsame Filterfunktion für Archiv und aktive Aufgaben
$filterFn = function(array $t) use ($archiveSearch, $archiveYear, $archiveFrom, $archiveTo) {
    $date = $t['todo_date'] ?? '';
    if ($archiveSearch !== '') {
        $hay = mb_strtolower(($t['title'] ?? '') . ' ' . ($t['description'] ?? ''));
        if (mb_strpos($hay, mb_strtolower($archiveSearch)) === false) { return false; }
    }
    if ($archiveYear > 0 && substr((string)$date, 0, 4) != (string)$archiveYear) { return false; }
    if ($archiveFrom !== '' && $date < $archiveFrom) { return false; }
    if ($archiveTo !== '' && $date > $archiveTo) { return false; }
    return true;
};

// Für Archivansicht zugewiesene und gesendete Aufgaben trennen
$archAssigned = [];
$archSent = [];
if ($showArchive) {
    foreach ($todos as $t) {
        if ((int)($t['user_id'] ?? 0) === (int)$selectedUserId) {
            $archAssigned[] = $t;
        }
        if ((int)($t['created_by'] ?? 0) === (int)$selectedUserId && (int)($t['user_id'] ?? 0) !== (int)$selectedUserId) {
            $archSent[] = $t;
        }
    }
    $archAssigned = array_values(array_filter($archAssigned, $filterFn));
    $archSent = array_values(array_filter($archSent, $filterFn));
    $archAssigned = array_slice($archAssigned, 0, $archiveLimit);
    $archSent = array_slice($archSent, 0, $archiveLimit);
} else {
    // Filter auf aktive Aufgaben anwenden
    $todos = array_values(array_filter($todos, $filterFn));
}

// Nach Datum gruppieren
$byDate = [];
foreach ($todos as $t) {
    $key = $t['todo_date'] ?? $weekStart;
    $byDate[$key][] = $t;
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="custom.css">
    <style>
      /* Tage sollen so breit wie die Seite/Filter sein: 1 Spalte = volle Breite */
      .kanban{ display:grid; grid-template-columns: 1fr; gap:12px; }
      .kanban-col{ background:#fff; border-radius:10px; padding:10px; height:auto; display:flex; flex-direction:column; }
      .kanban-col .col-header{ display:flex; align-items:center; justify-content:flex-start; gap:6px; margin-bottom:6px; }
      .kanban-col .items{ padding-right:0; }
      /* Drei Prioritätsfelder immer nebeneinander */
      .prio-grid{ display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; }
      .prio-col{ background:#f8f9fa; border:1px solid #e9ecef; border-radius:8px; padding:8px; display:flex; flex-direction:column; }
      .prio-col h6{ font-size:.9rem; margin:0 0 6px 0; color:#6c757d; display:flex; align-items:center; gap:.35rem; }
      .prio-col.low h6{ color:#198754; }
      .prio-col.mid h6{ color:#fd7e14; }
      .prio-col.high h6{ color:#dc3545; }
      #addTodoPriority{ flex-wrap:nowrap; }
      #addTodoPriority label{ flex:1 1 0; }
      .todo-card{ border:1px solid #e5e7eb; border-radius:8px; padding:10px; background:#fff; margin-bottom:8px; position:relative; word-break:break-word; }
      .todo-card.done{ opacity:.6; }
      /* Deutliches Signal in Gesendete Aufgaben, wenn erledigt */
      .todo-card.sent-done{ border-color:#198754; background:#eaf7e9; opacity:1; }
      .todo-card.overdue{ border-color:#dc3545; background:#fff5f5; }
      /* Heute fällig: blau */
      .todo-card.due-today{ border-color:#0d6efd; background:#eef6ff; }
      /* Morgen fällig (1 Tag vorher): gelb/orange */
      .todo-card.due-soon{ border-color:#fd7e14; background:#fff8ef; }
      /* Icon nun inline neben dem Titel */
      .status-icon-inline{ font-size:1rem; }
      .dropzone{ min-height: 44px; }
      .dropzone.drag-over{ outline: 2px dashed #0d6efd; outline-offset: 2px; background: #f8fbff; }
      .todo-card.dragging{ opacity:.5; }
      .card-content{ flex:1 1 0; min-width:0; }
      .add-inline input[type=text]{ height:32px; }
      /* kleiner Plus-Button */
      .btn-icon-xs{ padding:.125rem .35rem; line-height:1; font-size:.75rem; display:inline-flex; align-items:center; justify-content:center; }
      .btn-icon-xs i{ font-size:.8rem; }
      /* Extra small icon button */
      .btn-icon-xxs{ padding:.05rem .3rem; line-height:1; font-size:.7rem; display:inline-flex; align-items:center; justify-content:center; }
      .btn-icon-xxs i{ font-size:.9em; }
      /* Smaller attachments list inside To-Do modal */
      #addTodoModal .attach-list .attach-item{ font-size:.75rem; padding:2px 4px; }
      #addTodoModal .attach-list .attach-item i{ font-size:.9em; }
      /* To-Do Modal: Blaue Buttons einheitlich schmal */
      #addTodoModal .btn.btn-primary,
      #addTodoModal .btn.btn-outline-primary{
        padding: .25rem .55rem;
        line-height: 1.2;
        font-size: .9rem;
        border-radius: .4rem;
        width: auto;
      }
      /* Save button in footer: use default size like Abbrechen */
      #addTodoModal .modal-footer .btn.btn-primary{
        padding: .375rem .75rem; /* Bootstrap default */
        line-height: 1.5;        /* Bootstrap default */
        font-size: 1rem;         /* Bootstrap default */
      }
      /* Mehr... Toggle Chevron */
      #moreToggle .chev{ transition: transform .2s ease; }
      #moreToggle[aria-expanded="true"] .chev{ transform: rotate(180deg); }
      /* Labels näher an die Eingabefelder im To-Do-Modal */
      #addTodoModal .form-label{ margin-bottom: .25rem; }
      /* Beschreibung im Add-Modal: volle Breite */
      #addTodoDesc{ width: 100%; }
      .btn-main-add{ padding:.45rem .75rem; font-size:1.1rem; display:inline-flex; align-items:center; justify-content:center; }
      .btn-main-add i{ font-size:1.2rem; }
      /* Kleine Icon-Buttons für Kategorie-Aktionen */
      .btn-icon-xs{ padding:.125rem .35rem; line-height:1; font-size:.8rem; display:inline-flex; align-items:center; justify-content:center; }
      .btn-icon-xs i{ font-size:.8rem; }
      /* Kategorie-Aktionsbuttons: gleiche runde Ecken wie große Buttons */
      #col-categories .btn-icon-xs{ border-radius:.5rem; }
      
      /* Vertikale Ausrichtung und kompaktere Abstände in Kategorie-Header */
      #col-categories .col-header{ align-items:center; gap:.25rem !important; flex-wrap: nowrap !important; }
      #col-categories .col-header .cat-title{ display:inline-flex; align-items:center; }
      #col-categories .col-header form{ display:inline-flex; align-items:center; gap:.2rem; }
      #col-categories .col-header .gap-2{ gap:.25rem !important; }
      /* Dezent: Kategorien voneinander abheben, aber nicht eingerückt */
      #col-categories .category-section{ border:0; border-top:1px solid #e9ecef; padding:8px 0; margin:12px 0; background: transparent; }
      #col-categories .category-section:first-of-type{ border-top:0; margin-top:0; }
      #col-categories .category-section .col-header{ margin-bottom:8px; }

      #col-categories .col-header .cat-count{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background:#6c757d;
        color:#fff;
        border-radius:50%;
        width:2rem;
        height:2rem;
        font-size:.8rem;
      }
      

      /* Keine Umsortierung pro Breakpoint – immer volle Breite */
      @media (max-width: 1600px){ .kanban{ grid-template-columns: 1fr; } }
      @media (max-width: 992px){ .kanban{ grid-template-columns: 1fr; } }
      @media (max-width: 768px){ .kanban{ grid-template-columns: 1fr; } }

      /* Mobile Optimierungen */
      @media (max-width: 768px){
        .prio-grid{ grid-template-columns: 1fr; gap:10px; }
        .kanban-col{ padding:8px; }
        .prio-col{ padding:8px; }
        .dropzone{ min-height: 52px; }
        .status-icon-inline{ font-size:1.1rem; }
        .todo-card{ padding:10px; }
      }
      @media (max-width: 576px){
        .filters{ width:100%; gap:.35rem; }
        .filters .filter-item{ flex:1 1 48%; min-width:0; }
        .filters .form-control,
        .filters .form-select{ min-width:0 !important; flex:1 1 auto; padding:.25rem .5rem; height:32px; font-size:.85rem; }
        .filters .form-label{ font-size:.8rem; }
        .filters .btn-archive{ padding:.25rem .5rem; height:32px; font-size:.85rem; }
        .btn-main-add{ padding:.35rem .6rem; font-size:.95rem; }
        .kanban-col .col-header small{ font-size:.85rem; }
        .prio-col h6{ font-size:.85rem; }
        .text-muted{ font-size:.78rem !important; }
      }

      /* Meta-Reihe für Start/Spätestens nebeneinander */
      .meta-row{ display:flex; gap:.5rem; flex-wrap:wrap; align-items:center; margin:.25rem 0 .35rem; }
      .meta-row .badge{ font-weight:500; }
      .legend{ font-size:.85rem; gap:.75rem !important; }
      .legend .bi{ font-size:.95rem; }
      .attach-list{ display:flex; flex-wrap:wrap; gap:6px; margin-top:4px; }
      .attach-item{ display:inline-flex; align-items:center; gap:6px; background:#f8f9fa; border:1px solid #e9ecef; border-radius:6px; padding:2px 6px; font-size:.8rem; }
      .attach-item i{ color:#6c757d; }
      .attach-item a{ color:inherit; }
      .attach-item .dl{ color:#495057; }
      /* Auffällige Kategorie-Icons vor dem Titel (kompakter) */
      .cat-icon{ display:inline-flex; align-items:center; justify-content:center; width:18px; height:18px; border-radius:50%; color:#fff; font-size:.85rem; }
      .cat-icon.global{ background:#0d6efd; }
      .cat-icon.private{ background:#6c757d; }
      .cat-icon.shared{ background:#6610f2; }
      /* Zeigefinger-Cursor auf interaktiven Bereichen */
      .todo-card,
      .dropzone,
      .prio-col,
      .btn,
      .btn-main-add { cursor: pointer; }
      /* Prevent background scroll when a modal is open */
      html.modal-open, body.modal-open { overflow: hidden !important; }
      /* Ensure the modal itself can scroll if content is long */
      .modal { overflow-y: auto; }
      /* Chevron indicator for Wiederholen-Collapse */
      .repeat-btn{ padding:.2rem .45rem; line-height:1.2; font-size:.9rem; }
      .repeat-btn .chev { transition: transform .2s ease; transform: rotate(0deg); }
      .repeat-btn[aria-expanded="true"] .chev { transform: rotate(180deg); }
      /* (keine Sonderstile für Wiederholen-Collapse; nutzt Bootstrap-Buttons) */
      /* Filterzeile: standardmäßig einzeilig, bei schmalen Viewports umbrechen */
      .filters { flex-wrap: nowrap; }
      .filters .filter-item{ display:flex; flex-direction:column; gap:.05rem; }
      .filters .filter-item .form-label{ margin-bottom:0; font-size:.95rem; color:#495057; }
      .filters .filter-item{ flex: 1 1 150px; min-width:150px; }
      /* Benutzer-Filter breiter darstellen (gleiche Höhe wie andere) */
      .filters .filter-item.filter-user{ flex: 1 1 260px; min-width:260px; }
      /* Archiv-Button auf gleiche Höhe wie Inputs */
      .filters .btn-archive{ height: calc(1.5em + .75rem + 2px); display:inline-flex; align-items:center; }
      .filters .clear-input{ display:none; }
      /* Neue Kategorie: Feld kompakter */
      #col-categories form .form-control[name="cat_name"]{ max-width: 200px; }
      
      @media (max-width: 1000px) {
        .filters { flex-wrap: wrap; }
      }
    </style>
</head>
<body>
<div class="sticky-top bg-light position-relative">
  <?php include 'nav.php'; ?>
  <?php if (function_exists('csrf_get_tokens')): 
        $t = csrf_get_tokens(); ?>
  <script>
    window._csrf = { id: <?= json_encode($t['id'] ?? '') ?>, token: <?= json_encode($t['token'] ?? '') ?> };
  </script>
  <?php endif; ?>
</div>

<div class="container mt-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <div class="d-flex align-items-center gap-3">
          <h1 class="m-0">To-Do</h1>
          <?php
            $todayY = (new DateTime())->format('Y-m-d');
            $defaultAddDate = ($todayY >= $weekStart && $todayY <= $rangeEnd) ? $todayY : $weekStart;
          ?>
          <button type="button" class="btn btn-outline-primary btn-main-add" title="Neues To-Do"
                  data-bs-toggle="modal" data-bs-target="#addTodoModal" data-date="<?= htmlspecialchars($defaultAddDate, ENT_QUOTES) ?>">
            <i class="bi bi-plus-lg"></i>
          </button>
          <button type="button" class="btn btn-sm btn-primary" title="Neue Kategorie"
                  data-bs-toggle="modal" data-bs-target="#newCategoryModal">
            Neue Kategorie
          </button>
          <?php
            // Toggle zwischen aktiv und Archiv, aktuelle Filter beibehalten
            $qs = $_GET; $qs['archive'] = $showArchive ? '0' : '1';
            $archiveUrl = 'todo.php?' . http_build_query($qs);
          ?>
          <a href="<?= htmlspecialchars($archiveUrl, ENT_QUOTES) ?>" class="btn btn-sm btn-outline-secondary">
            <?= $showArchive ? 'Aktive Aufgaben' : 'Archiv' ?>
          </a>
        </div>

    </div>
    <form method="get" class="filters d-flex mb-3 align-items-end gap-2 flex-wrap">
      <?php if ($showArchive): ?><input type="hidden" name="archive" value="1"><?php endif; ?>
      <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
      <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
      <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
      <input type="hidden" name="user" value="<?= (int)$selectedUserId ?>">
      <div class="filter-item">
        <label class="form-label">Menge</label>
        <div class="position-relative">
          <input type="number" name="limit" value="<?= (int)$archiveLimit ?>" min="1" class="form-control pe-4">
          <button type="button" class="btn-close clear-input position-absolute top-50 end-0 translate-middle-y me-2"></button>
        </div>
      </div>
      <div class="filter-item">
        <label class="form-label">Suche</label>
        <div class="position-relative">
          <input type="text" name="search" value="<?= htmlspecialchars($archiveSearch, ENT_QUOTES) ?>" class="form-control pe-4">
          <button type="button" class="btn-close clear-input position-absolute top-50 end-0 translate-middle-y me-2"></button>
        </div>
      </div>
      <div class="filter-item">
        <label class="form-label">Jahr</label>
        <div class="position-relative">
          <input type="number" name="year" value="<?= $archiveYear ? (int)$archiveYear : '' ?>" class="form-control pe-4" min="1970" max="2100">
          <button type="button" class="btn-close clear-input position-absolute top-50 end-0 translate-middle-y me-2"></button>
        </div>
      </div>
      <div class="filter-item">
        <label class="form-label">Von</label>
        <div class="position-relative">
          <input type="date" name="from" value="<?= htmlspecialchars($archiveFrom, ENT_QUOTES) ?>" class="form-control pe-4">
          <button type="button" class="btn-close clear-input position-absolute top-50 end-0 translate-middle-y me-2"></button>
        </div>
      </div>
      <div class="filter-item">
        <label class="form-label">Bis</label>
        <div class="position-relative">
          <input type="date" name="to" value="<?= htmlspecialchars($archiveTo, ENT_QUOTES) ?>" class="form-control pe-4">
          <button type="button" class="btn-close clear-input position-absolute top-50 end-0 translate-middle-y me-2"></button>
        </div>
      </div>
      <div class="filter-item">
        <label class="form-label">&nbsp;</label>
        <button type="submit" class="btn btn-secondary btn-archive w-100">Filtern</button>
      </div>
    </form>

    <div class="card">
      <div class="card-body">
        <div class="kanban">

    <?php if (!$showArchive): ?>
        <?php
        // Eigene Aufgaben oberhalb der Kategorien anzeigen
        $visibleCatIds = array_fill_keys(array_map('intval', array_keys($catMap)), true);
        $ownGrouped = ['niedrig'=>[], 'mittel'=>[], 'hoch'=>[]];
        $doneItems = [];
        foreach ($todos as $it) {
          if ((int)($it['archived'] ?? 0) === 1) continue;
          $isSentByMe = (!$canSelectUser || ((int)$selectedUserId === (int)$userId))
                        && ((int)($it['created_by'] ?? 0) === (int)$userId)
                        && ((int)($it['user_id'] ?? 0) !== (int)$userId);
          if ($isSentByMe) continue;
          if ((int)($it['is_done'] ?? 0) === 1) {
            $doneItems[] = $it;
            continue;
          }
          $catVal = (int)($it['category_id'] ?? 0);
          if ($catVal === (int)($defaultCatId ?? 0) || $catVal === 0 || !isset($visibleCatIds[$catVal])) {
            $ownGrouped[strtolower($it['priority'] ?? 'mittel')][] = $it;
          }
        }

        // Gesendete Aufgaben laden (werden später angezeigt)
        $sentItems = [];
        $sqlSent = 'SELECT t.id, t.user_id, t.title, t.description, t.is_done, t.created_at, t.completed_at, t.todo_date, t.priority, t.due_date, t.due_time, t.sort_order, t.category_id, t.sent_scope, t.dispatch_group, t.in_progress_by, t.in_progress_at,' .
                   ' COALESCE(NULLIF(TRIM(CONCAT(ur.vorname, " ", ur.nachname)), ""), ur.email) AS recipient_name,' .
                   ' COALESCE(NULLIF(TRIM(CONCAT(uc.vorname, " ", uc.nachname)), ""), uc.email) AS creator_name,' .
                   ' COALESCE(NULLIF(TRIM(CONCAT(up.vorname, " ", up.nachname)), ""), up.email) AS in_progress_name' .
                   ' FROM todos t' .
                   ' LEFT JOIN user ur ON ur.id = t.user_id' .
                   ' LEFT JOIN user uc ON uc.id = t.created_by' .
                   ' LEFT JOIN user up ON up.id = t.in_progress_by' .
                   ' WHERE t.created_by = ? AND t.user_id <> ? AND t.archived = 0';
        if ($selectedStatus === 'offen') { $sqlSent .= ' AND t.is_done = 0'; }
        elseif ($selectedStatus === 'bearbeitung') { $sqlSent .= ' AND t.is_done = 2'; }
        elseif ($selectedStatus === 'erledigt') { $sqlSent .= ' AND t.is_done = 1'; }
        if ($selectedPrio !== 'alle') { $sqlSent .= ' AND t.priority = ?'; }
        $sqlSent .= " ORDER BY t.is_done ASC, FIELD(t.priority,'niedrig','mittel','hoch'), COALESCE(t.sort_order, 999999) ASC, t.created_at ASC, t.id ASC";
        $stmtSent = $conn->prepare($sqlSent);
        if ($stmtSent) {
          if ($selectedPrio !== 'alle') { $stmtSent->bind_param('iis', $userId, $userId, $selectedPrio); }
          else { $stmtSent->bind_param('ii', $userId, $userId); }
          $stmtSent->execute();
          $resSent = $stmtSent->get_result();
          while ($row = $resSent->fetch_assoc()) { $sentItems[] = $row; }
          $stmtSent->close();
        }
        $sentPending = [];
        ?>

        <div class="mt-3">
          <strong>Eigene Aufgaben</strong>
        </div>
        <div class="mt-2" id="col-own">
          <div class="prio-grid">
            <?php foreach (['niedrig'=>'Niedrig','mittel'=>'Mittel','hoch'=>'Hoch'] as $pKey=>$pLabel): $colItems = $ownGrouped[$pKey] ?? []; ?>
            <div class="prio-col <?= $pKey==='niedrig'?'low':($pKey==='mittel'?'mid':'high') ?>">
              <h6><i class="bi bi-sliders"></i> <?= $pLabel ?></h6>
              <div class="items dropzone" data-date="" data-priority="<?= $pKey ?>" data-category="<?= (int)($defaultCatId ?? 0) ?>">
                <?php foreach ($colItems as $t): ?>
                  <?php
                    $now = new DateTime();
                    if (!empty($t['due_date'])) {
                        $baseTime = !empty($t['due_time']) ? $t['due_time'] : '23:59';
                        $dueDT = new DateTime($t['due_date'] . ' ' . $baseTime);
                    } else { $dueDT = null; }
                    $dueClass = '';
                    $statusIcon = '';
                    $statusTitle = '';
                    if (in_array((int)($t['is_done'] ?? 0), [0,2], true) && $dueDT instanceof DateTime) {
                        $todayStr = (new DateTime('today'))->format('Y-m-d');
                        $tomorrowStr = (new DateTime('tomorrow'))->format('Y-m-d');
                        $dueDateStr = $dueDT->format('Y-m-d');
                        if ($now > $dueDT) {
                            $dueClass = 'overdue';
                            $statusIcon = 'bi-exclamation-octagon-fill text-danger';
                            $statusTitle = 'Überfällig seit ' . $dueDT->format('d.m.');
                        } elseif ($dueDateStr === $todayStr) {
                            $dueClass = 'due-today';
                            $statusIcon = 'bi-calendar-day text-primary';
                            $statusTitle = 'Heute spätestens am ' . $dueDT->format('d.m.');
                        } elseif ($dueDateStr === $tomorrowStr) {
                            $dueClass = 'due-soon';
                            $statusIcon = 'bi-bell-fill text-warning';
                            $statusTitle = 'Spätestens morgen (' . $dueDT->format('d.m.') . ')';
                        }
                    }
                  ?>
                  <div class="todo-card <?= ((int)$t['is_done'] === 1 ? 'done' : '') ?> <?= $dueClass ?>" data-id="<?= (int)$t['id'] ?>" draggable="true"
                        data-sent-scope="<?= htmlspecialchars($t['sent_scope'] ?? 'single', ENT_QUOTES) ?>"
                        data-title="<?= htmlspecialchars($t['title'] ?? '', ENT_QUOTES) ?>"
                        data-desc="<?= htmlspecialchars($t['description'], ENT_QUOTES) ?>"
                        data-priority="<?= htmlspecialchars(strtolower($t['priority'] ?? 'mittel'), ENT_QUOTES) ?>"
                        data-start=""
                        data-user-id="<?= (int)($t['user_id'] ?? 0) ?>"
                        data-todo-date="<?= htmlspecialchars($t['todo_date'] ?? '', ENT_QUOTES) ?>"
                        data-due-date="<?= htmlspecialchars($t['due_date'] ?? '', ENT_QUOTES) ?>"
                        data-due-time="<?= htmlspecialchars($t['due_time'] ?? '', ENT_QUOTES) ?>"
                        data-repeat-freq="<?= htmlspecialchars($t['repeat_freq'] ?? 'none', ENT_QUOTES) ?>"
                        data-repeat-until="<?= htmlspecialchars($t['repeat_until'] ?? '', ENT_QUOTES) ?>"
                        data-category-id="<?= (int)($t['category_id'] ?? 0) ?>"
                        data-created-by="<?= (int)($t['created_by'] ?? 0) ?>"
                        data-created-by-name="<?= htmlspecialchars($t['creator_name'] ?? '', ENT_QUOTES) ?>"
                        data-is-done="<?= (int)($t['is_done'] ?? 0) ?>"
                        data-in-progress-by="<?= (int)($t['in_progress_by'] ?? 0) ?>"
                        data-in-progress-name="<?= htmlspecialchars($t['in_progress_name'] ?? '', ENT_QUOTES) ?>">
                    <div class="d-flex align-items-start justify-content-between">
                      <div class="me-2 card-content">
                        <div class="d-flex align-items-center gap-2">
                          <?php if ($statusIcon): ?><i class="bi <?= $statusIcon ?> status-icon-inline" title="<?= htmlspecialchars($statusTitle, ENT_QUOTES) ?>"></i><?php endif; ?>
                          <?php $__tt = trim((string)($t['title'] ?? '')); if ($__tt !== ''): ?>
                            <span class="fw-semibold"><?= htmlspecialchars($__tt, ENT_QUOTES) ?></span>
                          <?php endif; ?>
                          <?php if (!empty($t['repeat_freq']) && $t['repeat_freq'] !== 'none'): ?>
                            <span class="badge rounded-pill bg-secondary">
                              <?= $t['repeat_freq']==='daily'?'Täglich':($t['repeat_freq']==='weekly'?'Wöchentlich':'Monatlich') ?>
                            </span>
                          <?php endif; ?>
                          <?php if ((int)$t['is_done'] === 2): ?>
                            <span class="badge rounded-pill bg-warning text-dark">In Bearbeitung</span>
                          <?php endif; ?>
                        </div>
                        <div class="mt-1">
                          <div><?= nl2br(htmlspecialchars($t['description'], ENT_QUOTES)) ?></div>
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                          <?php if (!empty($t['creator_name'])): ?>
                            <div><span>Erstellt von: <?= htmlspecialchars($t['creator_name'], ENT_QUOTES) ?></span></div>
                          <?php else: ?>
                            <div><span>Erstellt von: –</span></div>
                          <?php endif; ?>
                          <?php if ((int)$t['is_done'] === 2 && !empty($t['in_progress_name'])): ?>
                            <div><span>In Bearbeitung von: <?= htmlspecialchars($t['in_progress_name'], ENT_QUOTES) ?></span><?php if (!empty($t['in_progress_at'])): ?> · <span><?= htmlspecialchars((new DateTime($t['in_progress_at']))->format('d.m.'), ENT_QUOTES) ?></span><?php endif; ?></div>
                          <?php elseif ((int)$t['is_done'] === 1 && !empty($t['completed_name'])): ?>
                            <div><span>Fertiggestellt von: <?= htmlspecialchars($t['completed_name'], ENT_QUOTES) ?></span><?php if (!empty($t['completed_at'])): ?> · <span><?= htmlspecialchars((new DateTime($t['completed_at']))->format('d.m.'), ENT_QUOTES) ?></span><?php endif; ?></div>
                          <?php endif; ?>
                          <div>
                            <span>am <?= htmlspecialchars((new DateTime($t['created_at']))->format('d.m.'), ENT_QUOTES) ?></span>
                            <?php if (!empty($t['due_date'])): ?>
                              · <span>Spätestens: <?= htmlspecialchars((new DateTime($t['due_date']))->format('d.m.'), ENT_QUOTES) ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <?php $atts = ttListAttachments((int)$t['id']); if (!empty($atts)): ?>
                          <div class="attach-list">
                            <?php foreach ($atts as $a): $fn = $a['name']; $url = $a['url']; $dl = $url . '&dl=1'; $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION)); $icon = 'bi-file-earmark'; if (in_array($ext,['jpg','jpeg','png'])) { $icon = 'bi-file-earmark-image'; } elseif ($ext==='pdf') { $icon = 'bi-file-earmark-pdf'; } elseif (in_array($ext,['doc','docx'])) { $icon = 'bi-file-earmark-word'; } elseif (in_array($ext,['eml','msg'])) { $icon = 'bi-envelope'; } ?>
                            <div class="attach-item">
                              <i class="bi <?= $icon ?>"></i>
                              <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="text-decoration-none"><span><?= htmlspecialchars($fn, ENT_QUOTES) ?></span></a>
                              <a href="<?= htmlspecialchars($dl, ENT_QUOTES) ?>" class="dl ms-1" title="Download"><i class="bi bi-download"></i></a>
                            </div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                      <?php if ((int)$t['is_done'] === 2): ?>
                        <form method="post" class="ms-2 d-inline-block">
                          <input type="hidden" name="action" value="toggle">
                          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                          <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                          <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                          <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                          <input type="hidden" name="done" value="0">
                          <button type="submit" class="btn btn-sm btn-warning" title="Zurück zu offen">
                            <i class="bi bi-check2-circle"></i>
                          </button>
                        </form>
                      <?php else: ?>
                        <form method="post" class="ms-2 d-inline-block">
                          <input type="hidden" name="action" value="toggle">
                          <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                          <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                          <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                          <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                          <input type="hidden" name="done" value="2">
                          <button type="submit" class="btn btn-sm btn-outline-warning" title="Als in Bearbeitung markieren">
                            <i class="bi bi-check2-circle"></i>
                          </button>
                        </form>
                      <?php endif; ?>
                      <form method="post" class="ms-2 d-inline-block">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                        <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                        <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                        <input type="hidden" name="done" value="1">
                        <button type="submit" class="btn btn-sm btn-success" title="Als erledigt markieren">
                          <i class="bi bi-check2-circle"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

          <?php
            $todoCategories = [];
            $resCats = $conn->query(
              "SELECT id, name, owner_id, created_by FROM todo_categories " .
              "WHERE (owner_id IS NULL OR owner_id = " . (int)$selectedUserId . ") " .
              "   OR id IN (SELECT category_id FROM todo_category_shares WHERE user_id = " . (int)$selectedUserId . ") " .
              "ORDER BY COALESCE(sort_order,999999), name"
            );
            if ($resCats) { while($r = $resCats->fetch_assoc()){ $todoCategories[] = $r; } $resCats->close(); }
            $ownCats = []; $sharedCats = []; $globCats = [];
            foreach ($todoCategories as $c) {
              $cid = (int)$c['id']; if (!empty($defaultCatId) && $cid === (int)$defaultCatId) continue;
              if (!isset($c['owner_id']) || $c['owner_id'] === null) { $globCats[] = $c; }
              elseif ((int)$c['owner_id'] === (int)$selectedUserId) { $ownCats[] = $c; }
              else { $sharedCats[] = $c; }
            }
          $allCats = array_merge($ownCats, $sharedCats, $globCats);
          ?>
            <div class="mt-3">
              <strong>Aufgaben Kategorien</strong>
            </div>
            <div class="mt-2" id="col-categories">
            <?php if (empty($allCats)): ?>
              <div class="items"><div class="text-muted" style="font-size:.9rem;">Keine Kategorien.</div></div>
            <?php else: ?>
              <div class="d-flex justify-content-end mb-2">
                <button type="button" id="btnToggleAllCats" class="btn btn-sm btn-outline-primary" data-state="collapsed">
                  <i class="bi bi-chevron-double-down me-1"></i>
                  Alle öffnen
                </button>
              </div>
              <?php foreach ($allCats as $cat): ?>
            <?php
              $catOpen = ['niedrig'=>[], 'mittel'=>[], 'hoch'=>[]];
              $catTotal = 0; $catDone = 0;
              foreach ($todos as $it) {
                if ((int)($it['archived'] ?? 0) === 1) continue;
                // Nicht in Kategorien anzeigen: Aufgaben, die ICH an andere gesendet habe
                $isSentByMe = (!$canSelectUser || ((int)$selectedUserId === (int)$userId))
                               && ((int)($it['created_by'] ?? 0) === (int)$userId)
                               && ((int)($it['user_id'] ?? 0) !== (int)$userId);
                if ($isSentByMe) continue;
                if ((int)($it['category_id'] ?? 0) !== (int)$cat['id']) continue;
                $catTotal++;
                if ((int)($it['is_done'] ?? 0) !== 1) {
                  $catOpen[strtolower($it['priority'] ?? 'mittel')][] = $it;
                } else {
                  $catDone++;
                }
              }
              
            ?>
            <?php $wrapId = 'catwrap-' . (int)$cat['id']; ?>
            <div class="mt-2 category-section">
              <div class="col-header d-flex align-items-center justify-content-between gap-2 flex-wrap" data-bs-target="#<?= $wrapId ?>" aria-controls="<?= $wrapId ?>" aria-expanded="false">
                <div class="d-flex align-items-center gap-2">
                  <span class="cat-count"><?= max(0, $catTotal - $catDone) ?></span>
                  <?php
                    $isOwn = isset($cat['owner_id']) && (int)$cat['owner_id'] === (int)$userId;
                    $isGlobal = !isset($cat['owner_id']) || $cat['owner_id'] === null;
                    // Shares für Icon-Ermittlung laden
                    $shareIds = [];
                    $resSHicon = $conn->query('SELECT user_id FROM todo_category_shares WHERE category_id = ' . (int)$cat['id']);
                    if ($resSHicon) { while($srI = $resSHicon->fetch_assoc()){ $shareIds[] = (int)$srI['user_id']; } $resSHicon->close(); }
                    $hasOtherShares = false;
                    if (!empty($shareIds) && isset($cat['owner_id'])) {
                      foreach ($shareIds as $sid) { if ($sid !== (int)$cat['owner_id']) { $hasOtherShares = true; break; } }
                    }
                    $isSharedCat = (!$isGlobal && $hasOtherShares);
                    if (!isset($userMap)) { $userMap = []; foreach ($allUsers as $u0){ $userMap[(int)$u0['id']] = $u0['name']; } }
                    // Für globale Kategorien den Ersteller anzeigen (created_by), sonst den Besitzer (owner_id)
                    $ownerNameInline = $isGlobal
                      ? htmlspecialchars($userMap[(int)($cat['created_by'] ?? 0)] ?? 'Unbekannt', ENT_QUOTES)
                      : htmlspecialchars($userMap[(int)$cat['owner_id']] ?? ('User '.(int)$cat['owner_id']), ENT_QUOTES);
                  ?>
                  <div class="d-flex flex-column">
                    <div class="d-flex align-items-center gap-2">
                      <?php if ($isGlobal): ?>
                        <span class="cat-icon global" title="Kategorie für alle"><i class="bi bi-globe"></i></span>
                      <?php elseif ($isSharedCat): ?>
                        <span class="cat-icon shared" title="Mit ausgewählten Benutzern geteilt"><i class="bi bi-people"></i></span>
                      <?php else: ?>
                        <span class="cat-icon private" title="Privat"><i class="bi bi-lock"></i></span>
                      <?php endif; ?>
                      <span class="cat-title fw-semibold" data-cat-id="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES) ?></span>
                      <?php if ($isOwn): ?>
                        <?php
                          $ownerName = $isGlobal
                            ? htmlspecialchars($userMap[(int)($cat['created_by'] ?? 0)] ?? 'Unbekannt', ENT_QUOTES)
                            : htmlspecialchars($userMap[(int)$cat['owner_id']] ?? ('User '.(int)$cat['owner_id']), ENT_QUOTES);
                        ?>
                        <button type="button" class="btn btn-outline-secondary btn-icon-xs cat-edit ms-1" title="Kategorie bearbeiten"
                                data-cat-id="<?= (int)$cat['id'] ?>" data-cat-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                data-cat-owner="<?= $ownerName ?>"
                                data-cat-shares="<?= htmlspecialchars(implode(',', $shareIds), ENT_QUOTES) ?>">
                          <i class="bi bi-pencil"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                    <?php if ($ownerNameInline): ?>
                      <small class="text-muted">Erstellt von: <?= $ownerNameInline ?></small>
                    <?php endif; ?>
                  </div>
                </div>

              </div>
              <div id="<?= $wrapId ?>" class="collapse mt-2">
              <div class="prio-grid">
                <?php foreach (['niedrig'=>'Niedrig','mittel'=>'Mittel','hoch'=>'Hoch'] as $pKey=>$pLabel): $colItems = $catOpen[$pKey] ?? []; ?>
                <div class="prio-col <?= $pKey==='niedrig'?'low':($pKey==='mittel'?'mid':'high') ?>">
                  <h6><i class="bi bi-sliders"></i> <?= $pLabel ?></h6>
                  <div class="items dropzone" data-date="" data-priority="<?= $pKey ?>" data-category="<?= (int)$cat['id'] ?>">
                    <?php foreach ($colItems as $t): ?>
                      <?php
                        $now = new DateTime();
                        if (!empty($t['due_date'])) {
                            $baseTime = !empty($t['due_time']) ? $t['due_time'] : '23:59';
                            $dueDT = new DateTime($t['due_date'] . ' ' . $baseTime);
                        } else {
                            $dueDT = null;
                        }
                        $dueClass = '';
                        $statusIcon = '';
                        $statusTitle = '';
                        if (in_array((int)($t['is_done'] ?? 0), [0,2], true) && $dueDT instanceof DateTime) {
                            $todayStr = (new DateTime('today'))->format('Y-m-d');
                            $tomorrowStr = (new DateTime('tomorrow'))->format('Y-m-d');
                            $dueDateStr = $dueDT->format('Y-m-d');
                            if ($now > $dueDT) {
                                $dueClass = 'overdue';
                                $statusIcon = 'bi-exclamation-octagon-fill text-danger';
                                $statusTitle = 'Überfällig seit ' . $dueDT->format('d.m.');
                            } elseif ($dueDateStr === $todayStr) {
                                $dueClass = 'due-today';
                                $statusIcon = 'bi-calendar-day text-primary';
                                $statusTitle = 'Heute spätestens am ' . $dueDT->format('d.m.');
                            } elseif ($dueDateStr === $tomorrowStr) {
                                $dueClass = 'due-soon';
                                $statusIcon = 'bi-bell-fill text-warning';
                                $statusTitle = 'Spätestens morgen (' . $dueDT->format('d.m.') . ')';
                            }
                        }
                      ?>
                      <div class="todo-card <?= ((int)$t['is_done'] === 1 ? 'done' : '') ?> <?= $dueClass ?>" data-id="<?= (int)$t['id'] ?>" draggable="true"
                            data-sent-scope="<?= htmlspecialchars($t['sent_scope'] ?? 'single', ENT_QUOTES) ?>"
                            data-title="<?= htmlspecialchars($t['title'] ?? '', ENT_QUOTES) ?>"
                            data-desc="<?= htmlspecialchars($t['description'], ENT_QUOTES) ?>"
                           data-priority="<?= htmlspecialchars(strtolower($t['priority'] ?? 'mittel'), ENT_QUOTES) ?>"
                           data-start=""
                           data-user-id="<?= (int)($t['user_id'] ?? 0) ?>"
                           data-todo-date="<?= htmlspecialchars($t['todo_date'] ?? '', ENT_QUOTES) ?>"
                           data-due-date="<?= htmlspecialchars($t['due_date'] ?? '', ENT_QUOTES) ?>"
                           data-due-time="<?= htmlspecialchars($t['due_time'] ?? '', ENT_QUOTES) ?>"
                           data-repeat-freq="<?= htmlspecialchars($t['repeat_freq'] ?? 'none', ENT_QUOTES) ?>"
                           data-repeat-until="<?= htmlspecialchars($t['repeat_until'] ?? '', ENT_QUOTES) ?>"
                           data-category-id="<?= (int)($t['category_id'] ?? 0) ?>"
                           data-created-by="<?= (int)($t['created_by'] ?? 0) ?>"
                           data-created-by-name="<?= htmlspecialchars($t['creator_name'] ?? '', ENT_QUOTES) ?>"
                           data-is-done="<?= (int)($t['is_done'] ?? 0) ?>"
                           data-in-progress-by="<?= (int)($t['in_progress_by'] ?? 0) ?>"
                           data-in-progress-name="<?= htmlspecialchars($t['in_progress_name'] ?? '', ENT_QUOTES) ?>">
                        <div class="d-flex align-items-start justify-content-between">
                          <div class="me-2 card-content">
                            <div class="d-flex align-items-center gap-2">
                              <?php if ($statusIcon): ?><i class="bi <?= $statusIcon ?> status-icon-inline" title="<?= htmlspecialchars($statusTitle, ENT_QUOTES) ?>"></i><?php endif; ?>
                              <?php $__tt = trim((string)($t['title'] ?? '')); if ($__tt !== ''): ?>
                                <span class="fw-semibold"><?= htmlspecialchars($__tt, ENT_QUOTES) ?></span>
                              <?php endif; ?>
                                <?php if (!empty($t['repeat_freq']) && $t['repeat_freq'] !== 'none'): ?>
                                  <span class="badge rounded-pill bg-secondary">
                                    <?= $t['repeat_freq']==='daily'?'Täglich':($t['repeat_freq']==='weekly'?'Wöchentlich':'Monatlich') ?>
                                  </span>
                                <?php endif; ?>
                                <?php if ((int)$t['is_done'] === 2): ?>
                                  <span class="badge rounded-pill bg-warning text-dark">In Bearbeitung</span>
                                <?php endif; ?>
                              </div>
                              <div class="mt-1">
                                <div><?= nl2br(htmlspecialchars($t['description'], ENT_QUOTES)) ?></div>
                              </div>
                              <div class="text-muted" style="font-size:.8rem;">
                              <?php if (!empty($t['creator_name'])): ?>
                                <div><span>Erstellt von: <?= htmlspecialchars($t['creator_name'], ENT_QUOTES) ?></span></div>
                              <?php else: ?>
                                <div><span>Erstellt von: –</span></div>
                              <?php endif; ?>
                              <?php if ((int)$t['is_done'] === 2 && !empty($t['in_progress_name'])): ?>
                                <div><span>In Bearbeitung von: <?= htmlspecialchars($t['in_progress_name'], ENT_QUOTES) ?></span><?php if (!empty($t['in_progress_at'])): ?> · <span><?= htmlspecialchars((new DateTime($t['in_progress_at']))->format('d.m.'), ENT_QUOTES) ?></span><?php endif; ?></div>
                              <?php elseif ((int)$t['is_done'] === 1 && !empty($t['completed_name'])): ?>
                                <div><span>Fertiggestellt von: <?= htmlspecialchars($t['completed_name'], ENT_QUOTES) ?></span><?php if (!empty($t['completed_at'])): ?> · <span><?= htmlspecialchars((new DateTime($t['completed_at']))->format('d.m.'), ENT_QUOTES) ?></span><?php endif; ?></div>
                              <?php endif; ?>
                              <div>
                                <span>am <?= htmlspecialchars((new DateTime($t['created_at']))->format('d.m.'), ENT_QUOTES) ?></span>
                                <?php if (!empty($t['due_date'])): ?>
                                  · <span>Spätestens: <?= htmlspecialchars((new DateTime($t['due_date']))->format('d.m.'), ENT_QUOTES) ?></span>
                                <?php endif; ?>
                              </div>

                              </div>
                              <?php $atts = ttListAttachments((int)$t['id']); if (!empty($atts)): ?>
                              <div class="attach-list">
                                <?php foreach ($atts as $a): $fn = $a['name']; $url = $a['url']; $dl = $url . '&dl=1'; $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION)); $icon = 'bi-file-earmark'; if (in_array($ext,['jpg','jpeg','png'])) { $icon = 'bi-file-earmark-image'; } elseif ($ext==='pdf') { $icon = 'bi-file-earmark-pdf'; } elseif (in_array($ext,['doc','docx'])) { $icon = 'bi-file-earmark-word'; } elseif (in_array($ext,['eml','msg'])) { $icon = 'bi-envelope'; } ?>
                                <div class="attach-item">
                                  <i class="bi <?= $icon ?>"></i>
                                  <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="text-decoration-none"><span><?= htmlspecialchars($fn, ENT_QUOTES) ?></span></a>
                                  <a href="<?= htmlspecialchars($dl, ENT_QUOTES) ?>" class="dl ms-1" title="Download"><i class="bi bi-download"></i></a>
                                </div>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                          </div>
                          <?php if ((int)$t['is_done'] === 2): ?>
                            <form method="post" class="ms-2 d-inline-block">
                              <input type="hidden" name="action" value="toggle">
                              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                              <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                              <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                              <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                              <input type="hidden" name="done" value="0">
                              <button type="submit" class="btn btn-sm btn-warning" title="Zurück zu offen">
                                <i class="bi bi-check2-circle"></i>
                              </button>
                            </form>
                          <?php else: ?>
                            <form method="post" class="ms-2 d-inline-block">
                              <input type="hidden" name="action" value="toggle">
                              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                              <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                              <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                              <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                              <input type="hidden" name="done" value="2">
                              <button type="submit" class="btn btn-sm btn-outline-warning" title="Als in Bearbeitung markieren">
                                <i class="bi bi-check2-circle"></i>
                              </button>
                            </form>
                          <?php endif; ?>
                          <form method="post" class="ms-2 d-inline-block">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                            <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                            <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                            <input type="hidden" name="done" value="1">
                            <button type="submit" class="btn btn-sm btn-success" title="Als erledigt markieren">
                              <i class="bi bi-check2-circle"></i>
                            </button>
                          </form>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
                <?php endforeach; ?>
              </div>
            </div>
              </div>
              <?php endforeach; endif; ?>
        </div>
    <?php endif; ?>

        <div class="mt-3">
          <strong>Aufgabenstatus:</strong>
        </div>
        <?php if (!$showArchive): ?>
          <?php if (!empty($sentPending)): ?>
            <div class="mb-3">
              <strong class="d-block mb-1">Gesendete Aufgaben (wartend)</strong>
              <ul class="list-unstyled mb-0">
                <?php foreach ($sentPending as $s): ?>
                  <li class="small" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    <?= htmlspecialchars($s['title'] ?? '', ENT_QUOTES) ?> &rarr;
                    <?= htmlspecialchars($s['recipient_name'], ENT_QUOTES) ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
          <div class="kanban-col mt-3" id="col-sent">
            <div class="col-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
              <strong class="me-2 mb-1 mb-sm-0">Gesendete Aufgaben</strong>
              <div class="d-flex align-items-center gap-2">
                <div class="form-check m-0">
                  <input class="form-check-input" type="checkbox" id="selectAllSent">
                  <label class="form-check-label small" for="selectAllSent">Alle auswählen</label>
                </div>
                <form id="bulkSentForm" method="post" class="m-0 d-flex align-items-center gap-2">
                  <input type="hidden" name="action" value="">
                  <input type="hidden" name="ids" value="">
                  <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                  <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                  <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                  <input type="hidden" name="view_user" value="<?= (int)$selectedUserId ?>">
                  <button type="button" id="bulkArchiveSentBtn" class="btn btn-sm btn-outline-primary" disabled title="Ausgewählte ins Archiv">
                    <i class="bi bi-archive"></i>
                  </button>
                </form>
              </div>
            </div>
            <div>
              <?php
                // group sent-to-all by dispatch_group
                $sentGrouped = [];
                $seenDG = [];
                foreach (($sentItems ?? []) as $it) {
                  if (($it['sent_scope'] ?? 'single') === 'all' && !empty($it['dispatch_group'])) {
                    $dg = $it['dispatch_group'];
                    if (!isset($seenDG[$dg])) { $sentGrouped[] = $it; $seenDG[$dg] = true; }
                  } else {
                    $sentGrouped[] = $it;
                  }
                }
              ?>
              <?php if (empty($sentGrouped)): ?>
                <div class="text-muted" style="font-size:.9rem;">Keine gesendeten Aufgaben.</div>
              <?php else: ?>
                <div class="items">
                  <?php foreach ($sentGrouped as $t): ?>
                    <div class="todo-card <?= ((int)$t['is_done'] === 1 ? 'sent-done' : '') ?> d-flex align-items-start" data-id="<?= (int)$t['id'] ?>"
                        data-sent-scope="<?= htmlspecialchars($t['sent_scope'] ?? 'single', ENT_QUOTES) ?>"
                        data-title="<?= htmlspecialchars($t['title'] ?? '', ENT_QUOTES) ?>"
                        data-desc="<?= htmlspecialchars($t['description'], ENT_QUOTES) ?>"
                       data-priority="<?= htmlspecialchars(strtolower($t['priority'] ?? 'mittel'), ENT_QUOTES) ?>"
                       data-user-id="<?= (int)($t['user_id'] ?? 0) ?>"
                       data-todo-date="<?= htmlspecialchars($t['todo_date'] ?? '', ENT_QUOTES) ?>"
                       data-due-date="<?= htmlspecialchars($t['due_date'] ?? '', ENT_QUOTES) ?>"
                       data-due-time="<?= htmlspecialchars($t['due_time'] ?? '', ENT_QUOTES) ?>"
                       data-repeat-freq="<?= htmlspecialchars($t['repeat_freq'] ?? 'none', ENT_QUOTES) ?>"
                      data-repeat-until="<?= htmlspecialchars($t['repeat_until'] ?? '', ENT_QUOTES) ?>"
                       data-category-id="<?= (int)($t['category_id'] ?? 0) ?>"
                       data-is-done="<?= (int)($t['is_done'] ?? 0) ?>"
                       data-in-progress-by="<?= (int)($t['in_progress_by'] ?? 0) ?>"
                       data-in-progress-name="<?= htmlspecialchars($t['in_progress_name'] ?? '', ENT_QUOTES) ?>">
                      <div class="form-check me-2 mt-1">
                        <input class="form-check-input bulk-select-sent" type="checkbox" value="<?= (int)$t['id'] ?>">
                      </div>
                      <div class="d-flex align-items-start justify-content-between" style="flex:1;">
                        <div class="me-2 card-content">
                          <?php $__tt = trim((string)($t['title'] ?? '')); if ($__tt !== ''): ?>
                            <div class="fw-semibold mb-1"><?= htmlspecialchars($__tt, ENT_QUOTES) ?></div>
                          <?php endif; ?>
                          <div class="mb-1"><?= nl2br(htmlspecialchars($t['description'], ENT_QUOTES)) ?></div>
                          <?php if (!empty($t['repeat_freq']) && $t['repeat_freq'] !== 'none'): ?>
                            <div class="mb-1">
                              <span class="badge rounded-pill bg-secondary">
                                <?= $t['repeat_freq']==='daily'?'Täglich':($t['repeat_freq']==='weekly'?'Wöchentlich':'Monatlich') ?>
                              </span>
                            </div>
                          <?php endif; ?>
                          <div class="text-muted" style="font-size:.8rem;">
                            <div>Erstellt von: <?= htmlspecialchars($t['creator_name'] ?? '', ENT_QUOTES) ?></div>
                            <?php $isFwd = (int)($t['is_forwarded'] ?? 0) === 1; $sentScope = $t['sent_scope'] ?? 'single'; $recName = trim((string)($t['recipient_name'] ?? '')); ?>
                            <div>Gesendet an: <span class="<?= $isFwd ? 'text-primary' : '' ?>"><?= ($sentScope === 'all') ? 'alle' : htmlspecialchars($recName, ENT_QUOTES) ?></span></div>
                            <div>
                              am <?= htmlspecialchars((new DateTime($t['created_at']))->format('d.m.'), ENT_QUOTES) ?>
                              <?php if (!empty($t['due_date'])): ?>
                                · <span data-role="due-label">Spätestens: <?= htmlspecialchars((new DateTime($t['due_date']))->format('d.m.'), ENT_QUOTES) ?></span>
                              <?php endif; ?>
                              <?php if (!empty($t['completed_at'])): ?>
                                · <span class="text-success fw-semibold">Erledigt: <?= htmlspecialchars((new DateTime($t['completed_at']))->format('d.m. H:i'), ENT_QUOTES) ?></span>
                              <?php endif; ?>
                              <?php $cid = (int)($t['category_id'] ?? 0); $cname = $cid>0 ? ($catMap[$cid] ?? ('Kategorie #'.$cid)) : ($defaultCatName ?? 'Eigene Aufgaben'); ?>
                              <?php if ($cid > 0 && $cid !== (int)($defaultCatId ?? 0)): ?>
                                · <span class="text-danger">Kategorie: <?= htmlspecialchars($cname, ENT_QUOTES) ?></span>
                              <?php endif; ?>
                            </div>
                            <div>Status:
                              <?php if ((int)$t['is_done'] === 2): ?>
                                <span class="text-warning">In Bearbeitung</span>
                              <?php elseif ((int)$t['is_done'] === 1): ?>
                                <span class="text-success">Fertiggestellt</span>
                              <?php else: ?>
                                <span class="text-muted">Offen</span>
                              <?php endif; ?>
                            </div>
                          </div>
                          <?php $atts = ttListAttachments((int)$t['id']); if (!empty($atts)): ?>
                            <div class="attach-list">
                              <?php foreach ($atts as $a): $fn = $a['name']; $url = $a['url']; $dl = $url . '&dl=1'; $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION)); $icon = 'bi-file-earmark'; if (in_array($ext,['jpg','jpeg','png'])) { $icon = 'bi-file-earmark-image'; } elseif ($ext==='pdf') { $icon = 'bi-file-earmark-pdf'; } elseif (in_array($ext,['doc','docx'])) { $icon = 'bi-file-earmark-word'; } elseif (in_array($ext,['eml','msg'])) { $icon = 'bi-envelope'; } ?>
                                <div class="attach-item">
                                  <i class="bi <?= $icon ?>"></i>
                                  <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="text-decoration-none"><span><?= htmlspecialchars($fn, ENT_QUOTES) ?></span></a>
                                  <a href="<?= htmlspecialchars($dl, ENT_QUOTES) ?>" class="dl ms-1" title="Download"><i class="bi bi-download"></i></a>
                                </div>
                              <?php endforeach; ?>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

          </div>
        <?php endif; ?>
        <div class="kanban-col mt-3" id="<?= $showArchive ? 'col-archive' : 'col-done' ?>">
          <div class="col-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <strong class="me-2 mb-1 mb-sm-0"><?= $showArchive ? 'Archiviert' : 'Erledigte Aufgaben' ?></strong>
            <div class="d-flex align-items-center gap-2">
              <?php if ($showArchive): ?>
                <div class="form-check m-0">
                  <input class="form-check-input" type="checkbox" id="selectAllArchive">
                  <label class="form-check-label small" for="selectAllArchive">Alle auswählen</label>
                </div>
                <form id="bulkArchiveForm" method="post" class="m-0 d-flex align-items-center gap-2">
                  <input type="hidden" name="action" value="">
                  <input type="hidden" name="ids" value="">
                  <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                  <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                  <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                  <input type="hidden" name="view_user" value="<?= (int)$selectedUserId ?>">
                  <input type="hidden" name="limit" value="<?= (int)$archiveLimit ?>">
                  <input type="hidden" name="search" value="<?= htmlspecialchars($archiveSearch, ENT_QUOTES) ?>">
                  <input type="hidden" name="year" value="<?= $archiveYear ? (int)$archiveYear : '' ?>">
                  <input type="hidden" name="from" value="<?= htmlspecialchars($archiveFrom, ENT_QUOTES) ?>">
                  <input type="hidden" name="to" value="<?= htmlspecialchars($archiveTo, ENT_QUOTES) ?>">
                  <button type="button" id="bulkUnarchiveBtn" class="btn btn-sm btn-primary" disabled>
                    <i class="bi bi-box-arrow-up"></i>
                  </button>
                  <button type="button" id="bulkDeleteArchiveBtn" class="btn btn-sm btn-outline-danger" disabled title="Ausgewählte dauerhaft löschen">
                    <i class="bi bi-trash3"></i>
                  </button>
                </form>
              <?php else: ?>
                <div class="form-check m-0">
                  <input class="form-check-input" type="checkbox" id="selectAllDone">
                  <label class="form-check-label small" for="selectAllDone">Alle auswählen</label>
                </div>
                <form id="bulkDoneForm" method="post" class="m-0 d-flex align-items-center gap-2">
                  <input type="hidden" name="action" value="">
                  <input type="hidden" name="ids" value="">
                  <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                  <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                  <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                  <input type="hidden" name="view_user" value="<?= (int)$selectedUserId ?>">
                  <button type="button" id="bulkReopenBtn" class="btn btn-sm btn-outline-secondary" disabled>
                    <i class="bi bi-arrow-counterclockwise"></i>
                  </button>
                  <button type="button" id="bulkArchiveBtn" class="btn btn-sm btn-outline-primary" disabled>
                    <i class="bi bi-archive"></i>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <div>
            <?php
              // In Archive view, only show items assigned to the selected user here
              $listItems = $showArchive ? ($archAssigned ?? []) : ($doneItems ?? []);
            ?>
            <?php if (empty($listItems)): ?>
              <div class="text-muted" style="font-size:.9rem;"><?= $showArchive ? 'Keine archivierten Aufgaben.' : 'Keine erledigten Aufgaben.' ?></div>
            <?php else: ?>
              <div class="items">
                <?php foreach ($listItems as $t): ?>
                  <div class="todo-card done d-flex align-items-start" data-id="<?= (int)$t['id'] ?>"
                        data-sent-scope="<?= htmlspecialchars($t['sent_scope'] ?? 'single', ENT_QUOTES) ?>"
                        data-title="<?= htmlspecialchars($t['title'] ?? '', ENT_QUOTES) ?>"
                        data-desc="<?= htmlspecialchars($t['description'], ENT_QUOTES) ?>"
                       data-priority="<?= htmlspecialchars(strtolower($t['priority'] ?? 'mittel'), ENT_QUOTES) ?>"
                       data-user-id="<?= (int)($t['user_id'] ?? 0) ?>"
                       data-todo-date="<?= htmlspecialchars($t['todo_date'] ?? '', ENT_QUOTES) ?>"
                       data-due-date="<?= htmlspecialchars($t['due_date'] ?? '', ENT_QUOTES) ?>"
                       data-due-time="<?= htmlspecialchars($t['due_time'] ?? '', ENT_QUOTES) ?>"
                       data-repeat-freq="<?= htmlspecialchars($t['repeat_freq'] ?? 'none', ENT_QUOTES) ?>"
                       data-repeat-until="<?= htmlspecialchars($t['repeat_until'] ?? '', ENT_QUOTES) ?>"
                       data-category-id="<?= (int)($t['category_id'] ?? 0) ?>"
                       data-is-done="<?= (int)($t['is_done'] ?? 0) ?>"
                       data-in-progress-by="<?= (int)($t['in_progress_by'] ?? 0) ?>"
                       data-in-progress-name="<?= htmlspecialchars($t['in_progress_name'] ?? '', ENT_QUOTES) ?>">
                    <div class="form-check me-2 mt-1">
                      <input class="form-check-input <?= $showArchive ? 'bulk-select-archive' : 'bulk-select-done' ?>" type="checkbox" value="<?= (int)$t['id'] ?>">
                    </div>
                    <div class="d-flex align-items-start justify-content-between" style="flex:1;">
                      <div class="me-2 card-content">
                        <div class="d-flex align-items-center gap-2">
                          <?php $__tt = trim((string)($t['title'] ?? '')); if ($__tt !== ''): ?>
                            <span class="fw-semibold"><?= htmlspecialchars($__tt, ENT_QUOTES) ?></span>
                          <?php endif; ?>
                          <?php if (!empty($t['repeat_freq']) && $t['repeat_freq'] !== 'none'): ?>
                            <span class="badge rounded-pill bg-secondary">
                              <?= $t['repeat_freq']==='daily'?'Täglich':($t['repeat_freq']==='weekly'?'Wöchentlich':'Monatlich') ?>
                            </span>
                          <?php endif; ?>
                        </div>
                        <div class="mt-1">
                          <div><?= nl2br(htmlspecialchars($t['description'], ENT_QUOTES)) ?></div>
                        </div>
                          <div class="text-muted" style="font-size:.8rem;">
                            <?php if (!empty($t['creator_name'])): ?>
                              <div><span>Erstellt von: <?= htmlspecialchars($t['creator_name'], ENT_QUOTES) ?></span><?php if ((int)($t['is_forwarded'] ?? 0) === 1): ?> · <span class="text-primary">Weitergeleitet</span><?php endif; ?></div>
                            <?php else: ?>
                              <div><span>Erstellt von: –</span></div>
                            <?php endif; ?>
                            <?php if ((int)$t['is_done'] === 2 && !empty($t['in_progress_name'])): ?>
                              <div><span>In Bearbeitung von: <?= htmlspecialchars($t['in_progress_name'], ENT_QUOTES) ?></span><?php if (!empty($t['in_progress_at'])): ?> · <span><?= htmlspecialchars((new DateTime($t['in_progress_at']))->format('d.m.'), ENT_QUOTES) ?></span><?php endif; ?></div>
                            <?php elseif ((int)$t['is_done'] === 1 && !empty($t['completed_name'])): ?>
                              <div><span>Fertiggestellt von: <?= htmlspecialchars($t['completed_name'], ENT_QUOTES) ?></span><?php if (!empty($t['completed_at'])): ?> · <span class="text-success fw-semibold"><?= htmlspecialchars((new DateTime($t['completed_at']))->format('d.m. H:i'), ENT_QUOTES) ?></span><?php endif; ?></div>
                            <?php endif; ?>
                            <div>
                              <span>am <?= htmlspecialchars((new DateTime($t['created_at']))->format('d.m.'), ENT_QUOTES) ?></span>
                              <?php if (!empty($t['due_date'])): ?>
                                · <span data-role="due-label">Spätestens: <?= htmlspecialchars((new DateTime($t['due_date']))->format('d.m.'), ENT_QUOTES) ?></span>
                              <?php endif; ?>
                              <?php $cid = (int)($t['category_id'] ?? 0); $cname = $cid>0 ? ($catMap[$cid] ?? ('Kategorie #'.$cid)) : ($defaultCatName ?? 'Eigene Aufgaben'); ?>
                              <?php if ($cid > 0 && $cid !== (int)($defaultCatId ?? 0)): ?>
                                · <span class="text-danger">Kategorie: <?= htmlspecialchars($cname, ENT_QUOTES) ?></span>
                              <?php endif; ?>
                            </div>
                          </div>
                          
                        </div>
                        <?php $atts = ttListAttachments((int)$t['id']); if (!empty($atts)): ?>
                          <div class="attach-list">
                            <?php foreach ($atts as $a): $fn = $a['name']; $url = $a['url']; $dl = $url . '&dl=1'; $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION)); $icon = 'bi-file-earmark'; if (in_array($ext,['jpg','jpeg','png'])) { $icon = 'bi-file-earmark-image'; } elseif ($ext==='pdf') { $icon = 'bi-file-earmark-pdf'; } elseif (in_array($ext,['doc','docx'])) { $icon = 'bi-file-earmark-word'; } elseif (in_array($ext,['eml','msg'])) { $icon = 'bi-envelope'; } ?>
                              <div class="attach-item">
                                <i class="bi <?= $icon ?>"></i>
                                <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="text-decoration-none"><span><?= htmlspecialchars($fn, ENT_QUOTES) ?></span></a>
                                <a href="<?= htmlspecialchars($dl, ENT_QUOTES) ?>" class="dl ms-1" title="Download"><i class="bi bi-download"></i></a>
                              </div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($showArchive): ?>
        <div class="kanban-col mt-3" id="col-archive-sent">
          <div class="col-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <strong class="me-2 mb-1 mb-sm-0">Archivierte gesendete Aufgaben</strong>
            <div class="d-flex align-items-center gap-2">
              <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" id="selectAllArchiveSent">
                <label class="form-check-label small" for="selectAllArchiveSent">Alle auswählen</label>
              </div>
              <form id="bulkArchiveSentForm" method="post" class="m-0 d-flex align-items-center gap-2">
                <input type="hidden" name="action" value="">
                <input type="hidden" name="ids" value="">
                <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
                <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
                <input type="hidden" name="view_user" value="<?= (int)$selectedUserId ?>">
                <input type="hidden" name="limit" value="<?= (int)$archiveLimit ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($archiveSearch, ENT_QUOTES) ?>">
                <input type="hidden" name="year" value="<?= $archiveYear ? (int)$archiveYear : '' ?>">
                <input type="hidden" name="from" value="<?= htmlspecialchars($archiveFrom, ENT_QUOTES) ?>">
                <input type="hidden" name="to" value="<?= htmlspecialchars($archiveTo, ENT_QUOTES) ?>">
                <button type="button" id="bulkUnarchiveSentBtn" class="btn btn-sm btn-primary" disabled title="Ausgewählte wiederherstellen">
                  <i class="bi bi-box-arrow-up"></i>
                </button>
              </form>
            </div>
          </div>
          <div>
            <?php if (empty($archSent)): ?>
              <div class="text-muted" style="font-size:.9rem;">Keine archivierten gesendeten Aufgaben.</div>
            <?php else: ?>
              <div class="items">
                <?php foreach ($archSent as $t): ?>
                        <div class="todo-card done d-flex align-items-start" data-id="<?= (int)$t['id'] ?>"
                           data-sent-scope="<?= htmlspecialchars($t['sent_scope'] ?? 'single', ENT_QUOTES) ?>"
                           data-title="<?= htmlspecialchars($t['title'] ?? '', ENT_QUOTES) ?>"
                           data-desc="<?= htmlspecialchars($t['description'], ENT_QUOTES) ?>"
                     data-priority="<?= htmlspecialchars(strtolower($t['priority'] ?? 'mittel'), ENT_QUOTES) ?>"
                     data-user-id="<?= (int)($t['user_id'] ?? 0) ?>"
                     data-todo-date="<?= htmlspecialchars($t['todo_date'] ?? '', ENT_QUOTES) ?>"
                     data-due-date="<?= htmlspecialchars($t['due_date'] ?? '', ENT_QUOTES) ?>"
                     data-due-time="<?= htmlspecialchars($t['due_time'] ?? '', ENT_QUOTES) ?>"
                     data-repeat-freq="<?= htmlspecialchars($t['repeat_freq'] ?? 'none', ENT_QUOTES) ?>"
                     data-repeat-until="<?= htmlspecialchars($t['repeat_until'] ?? '', ENT_QUOTES) ?>"
                    data-category-id="<?= (int)($t['category_id'] ?? 0) ?>"
                    data-is-done="<?= (int)($t['is_done'] ?? 0) ?>"
                    data-in-progress-by="<?= (int)($t['in_progress_by'] ?? 0) ?>"
                    data-in-progress-name="<?= htmlspecialchars($t['in_progress_name'] ?? '', ENT_QUOTES) ?>">
                    <div class="form-check me-2 mt-1">
                      <input class="form-check-input bulk-select-archive-sent" type="checkbox" value="<?= (int)$t['id'] ?>">
                    </div>
                    <div class="d-flex align-items-start justify-content-between" style="flex:1;">
                      <div class="me-2 card-content">
                        <?php $__tt = trim((string)($t['title'] ?? '')); if ($__tt !== ''): ?>
                          <div class="fw-semibold mb-1"><?= htmlspecialchars($__tt, ENT_QUOTES) ?></div>
                        <?php endif; ?>
                        <div class="d-flex align-items-center gap-2">
                          <span><?= nl2br(htmlspecialchars($t['description'], ENT_QUOTES)) ?></span>
                          <?php if (!empty($t['repeat_freq']) && $t['repeat_freq'] !== 'none'): ?>
                            <span class="badge rounded-pill bg-secondary">
                              <?= $t['repeat_freq']==='daily'?'Täglich':($t['repeat_freq']==='weekly'?'Wöchentlich':'Monatlich') ?>
                            </span>
                          <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:.8rem;">
                          <div>
                            <span>am <?= htmlspecialchars((new DateTime($t['created_at']))->format('d.m.'), ENT_QUOTES) ?></span>
                            <?php if (!empty($t['due_date'])): ?>
                              · <span data-role="due-label">Spätestens: <?= htmlspecialchars((new DateTime($t['due_date']))->format('d.m.'), ENT_QUOTES) ?></span>
                            <?php endif; ?>
                            <?php
                              $sentScope = $t['sent_scope'] ?? 'single';
                              $recName = trim((string)($t['recipient_name'] ?? ''));
                            ?>
                            · <span class="text-primary">
                              Weitergeleitet <?= ($sentScope === 'all') ? 'an alle' : ('an ' . htmlspecialchars($recName, ENT_QUOTES)) ?>
                            </span>
                            <?php if (!empty($t['completed_at'])): ?>
                              · <span class="text-success fw-semibold">Erledigt: <?= htmlspecialchars((new DateTime($t['completed_at']))->format('d.m. H:i'), ENT_QUOTES) ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                        <?php $atts = ttListAttachments((int)$t['id']); if (!empty($atts)): ?>
                          <div class="attach-list">
                            <?php foreach ($atts as $a): $fn = $a['name']; $url = $a['url']; $dl = $url . '&dl=1'; $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION)); $icon = 'bi-file-earmark'; if (in_array($ext,['jpg','jpeg','png'])) { $icon = 'bi-file-earmark-image'; } elseif ($ext==='pdf') { $icon = 'bi-file-earmark-pdf'; } elseif (in_array($ext,['doc','docx'])) { $icon = 'bi-file-earmark-word'; } elseif (in_array($ext,['eml','msg'])) { $icon = 'bi-envelope'; } ?>
                            <div class="attach-item">
                              <i class="bi <?= $icon ?>"></i>
                              <a href="<?= htmlspecialchars($url, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="text-decoration-none"><span><?= htmlspecialchars($fn, ENT_QUOTES) ?></span></a>
                              <a href="<?= htmlspecialchars($dl, ENT_QUOTES) ?>" class="dl ms-1" title="Download"><i class="bi bi-download"></i></a>
                            </div>
                            <?php endforeach; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
        </div>

      <div class="kanban-col mt-3">
        <div class="legend d-flex flex-wrap align-items-center gap-3">
          <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-exclamation-octagon-fill text-danger"></i> <span>Überfällig (Datum überschritten)</span></span>
          <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-calendar-day text-primary"></i> <span>Heute spätestens</span></span>
          <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-bell-fill" style="color:#fd7e14"></i> <span>Erinnerung: 1&nbsp;Tag vorher</span></span>
          <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-check2-circle text-success"></i> <span>Als erledigt markieren</span></span>
          <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-arrow-counterclockwise text-secondary"></i> <span>Wiederherstellen</span></span>
          <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-archive text-primary"></i> <span>Archivieren</span></span>
          <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-trash3 text-danger"></i> <span>Löschen (nur Archiv)</span></span>
        </div>
        <div class="legend d-flex flex-wrap align-items-center gap-3 mt-2">
          <span class="d-inline-flex align-items-center gap-2"><span class="cat-icon global"><i class="bi bi-globe"></i></span> <span>Kategorie: Für alle</span></span>
          <span class="d-inline-flex align-items-center gap-2"><span class="cat-icon shared"><i class="bi bi-people"></i></span> <span>Kategorie: Geteilt</span></span>
          <span class="d-inline-flex align-items-center gap-2"><span class="cat-icon private"><i class="bi bi-lock"></i></span> <span>Kategorie: Privat</span></span>
        </div>
      </div>

        </div> <!-- kanban -->
      </div> <!-- card-body -->
    </div> <!-- card -->
  </div> <!-- container -->


    <!-- Add To-Do Modal -->
    <div class="modal fade" id="addTodoModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="post" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="addTodoTitle">Neues To-Do <span id="addTodoHeaderDay" class="text-muted"></span></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
  <div class="modal-body">
              <input type="hidden" name="action" value="add">
              <input type="hidden" name="todo_id" value="">
              
              <input type="hidden" name="status" value="<?= htmlspecialchars($selectedStatus, ENT_QUOTES) ?>">
              <input type="hidden" name="prio" value="<?= htmlspecialchars($selectedPrio, ENT_QUOTES) ?>">
              <input type="hidden" name="sort" value="<?= htmlspecialchars($selectedSort, ENT_QUOTES) ?>">
              <div class="mb-3">
                <label for="addTodoSubject" class="form-label">Titel</label>
                <input type="text" id="addTodoSubject" name="title" class="form-control" maxlength="100" placeholder="Kurzer Titel" required>
              </div>
              <div class="mb-3">
                <label for="addTodoDesc" class="form-label">Beschreibung</label>
                <textarea id="addTodoDesc" name="description" class="form-control" rows="3" placeholder="Aufgabe eingeben…"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label" for="addTodoCategory">Kategorie wählen</label>
                <select id="addTodoCategory" name="category_id" class="form-select form-select-sm">
                  <?php
                    // List categories in fixed order: private -> shared -> global
                    $seenCatIds = [];
                    if (!empty($defaultCatId)) {
                      echo '<option value="'.(int)$defaultCatId.'" data-type="private" selected>'.htmlspecialchars($defaultCatName, ENT_QUOTES).'</option>';
                      $seenCatIds[(int)$defaultCatId] = true;
                    }
                    $ownRes = $conn->query("SELECT id, name FROM todo_categories WHERE owner_id = " . (int)$selectedUserId . " ORDER BY COALESCE(sort_order, 999999), name");
                    if ($ownRes) {
                      while($c = $ownRes->fetch_assoc()){
                        $cid = (int)$c['id'];
                        if (!empty($defaultCatId) && $cid === (int)$defaultCatId) continue;
                        $nm  = htmlspecialchars($c['name'], ENT_QUOTES);
                        if (empty($seenCatIds[$cid])) { echo '<option value="'.$cid.'" data-type="private">'.$nm.'</option>'; $seenCatIds[$cid] = true; }
                      }
                      $ownRes->close();
                    }
                    $shrRes = $conn->query("SELECT c.id, c.name FROM todo_categories c JOIN todo_category_shares s ON s.category_id = c.id WHERE s.user_id = " . (int)$selectedUserId . " ORDER BY COALESCE(c.sort_order, 999999), c.name");
                    if ($shrRes) {
                      while($c = $shrRes->fetch_assoc()){
                        $cid = (int)$c['id'];
                        if (!empty($defaultCatId) && $cid === (int)$defaultCatId) continue;
                        $nm  = htmlspecialchars($c['name'], ENT_QUOTES);
                        if (empty($seenCatIds[$cid])) { echo '<option value="'.$cid.'" data-type="shared">'.$nm.'</option>'; $seenCatIds[$cid] = true; }
                      }
                      $shrRes->close();
                    }
                    $globRes = $conn->query("SELECT id, name FROM todo_categories WHERE owner_id IS NULL ORDER BY COALESCE(sort_order, 999999), name");
                    if ($globRes) {
                      while($c = $globRes->fetch_assoc()){
                        $cid = (int)$c['id'];
                        if (!empty($defaultCatId) && $cid === (int)$defaultCatId) continue;
                        $nm  = htmlspecialchars($c['name'], ENT_QUOTES);
                        if (empty($seenCatIds[$cid])) { echo '<option value="'.$cid.'" data-type="global">'.$nm.'</option>'; $seenCatIds[$cid] = true; }
                      }
                      $globRes->close();
                    }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Priorität</label>
                <div class="btn-group w-100" role="group" id="addTodoPriority">
                  <input type="radio" class="btn-check" name="priority" id="prioLow" value="niedrig" <?= ($selectedPrio==='niedrig') ? 'checked' : '' ?>>
                  <label class="btn btn-outline-success" for="prioLow">Niedrig</label>
                  <input type="radio" class="btn-check" name="priority" id="prioMid" value="mittel" <?= ($selectedPrio==='mittel' || $selectedPrio==='alle') ? 'checked' : '' ?>>
                  <label class="btn btn-outline-warning" for="prioMid">Mittel</label>
                  <input type="radio" class="btn-check" name="priority" id="prioHigh" value="hoch" <?= $selectedPrio==='hoch' ? 'checked' : '' ?>>
                  <label class="btn btn-outline-danger" for="prioHigh">Hoch</label>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-sm-6">
                  <label for="addTodoStartDate" class="form-label">Startdatum</label>
                  <input type="date" id="addTodoStartDate" name="todo_date" class="form-control">
                </div>
                <div class="col-sm-6">
                  <label for="addTodoDueDate" class="form-label">Spätestens am</label>
                  <input type="date" id="addTodoDueDate" name="due_date" class="form-control">
                </div>
              </div>

              <div id="modalExistingAttachments" class="mb-3 d-none">
                <label class="form-label">Anhänge</label>
                <div id="modalAttachList" class="attach-list"></div>
              </div>
              <div class="mb-3">
                <label for="todoAttachments" class="form-label">Dateien hochladen</label>
                <input type="file" id="todoAttachments" name="attachments[]" class="form-control" multiple
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.eml,.msg">
              </div>

              <!-- Mehr... Toggle -->
              <div class="d-flex justify-content-end mb-2">
                <button type="button"
                        id="moreToggle"
                        class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="collapse"
                        data-bs-target="#editMoreWrap"
                        aria-expanded="false"
                        aria-controls="editMoreWrap">
                  <i class="bi bi-chevron-down chev me-1"></i> mehr…
                </button>
              </div>

              <!-- Wrapper beginnt: Wiederholen etc. -->
              <div id="editMoreWrap" class="collapse">

              <!-- Wiederholen -->
              <h6 class="mt-3 mb-2">Wiederholen</h6>
              <div class="row g-3">
                <div class="col-sm-4">
                  <label for="repeatFreq" class="form-label">Wiederholen</label>
                  <select id="repeatFreq" name="repeat_freq" class="form-select">
                    <option value="none" selected>Keine</option>
                    <option value="daily">Täglich</option>
                    <option value="weekly">Wöchentlich</option>
                    <option value="monthly">Monatlich</option>
                  </select>
                </div>
                <div class="col-sm-4">
                  <label for="repeatUntil" class="form-label">Bis (inkl.)</label>
                  <input type="date" id="repeatUntil" name="repeat_until" class="form-control" placeholder="YYYY-MM-DD">
                </div>
              <div class="col-sm-4 d-flex align-items-end">
                <small class="text-muted">Mehrere Aufgaben ab Startdatum.</small>
              </div>
              </div>
              <input type="hidden" name="forward_action" id="forwardAction" value="0">

              <!-- Aufgabe weitergeben an -->
              <div id="forwardSection" class="mt-3">
                <h6 class="mb-2">Aufgabe weitergeben an:</h6>
                <div class="mb-3">
                  <select id="forwardUser" name="forward_user" class="form-select">
                    <option value="">Nicht weitergeben</option>
                    <?php foreach ($allUsers as $u): $uid=(int)$u['id']; if ($uid === (int)$userId) continue; $nm=htmlspecialchars($u['name'] ?? ('User '.$u['id']), ENT_QUOTES); ?>
                      <option value="<?= $uid ?>"><?= $nm ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              </div> <!-- /#editMoreWrap -->
            <div class="modal-footer d-flex justify-content-between">
              <?php if ((int)$roleId === 1): ?>
                <button type="button" id="deleteTodoBtn" class="btn btn-outline-danger">Löschen</button>
              <?php endif; ?>
              <div>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
                <button type="submit" class="btn btn-primary">Speichern</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>

<!-- Neues Kategorie-Modal -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="todo.php">
        <div class="modal-header">
          <h5 class="modal-title">Neue Kategorie</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add_category">
          <div class="mb-3">
            <label for="newCatName" class="form-label">Name</label>
            <input type="text" id="newCatName" name="cat_name" class="form-control" placeholder="Kategorie-Name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
          <button type="submit" class="btn btn-primary">Anlegen</button>
        </div>
      </form>
    </div>
  </div>
</div>

 

<!-- Kategorie bearbeiten Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="todo.php">
        <div class="modal-header">
          <h5 class="modal-title">Kategorie bearbeiten</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="edit_category">
          <input type="hidden" name="cat_id" id="editCatId" value="">
          <div class="mb-2">
            <span class="text-muted">Erstellt von:</span>
            <span id="editCatOwner" class="fw-semibold"></span>
          </div>
          <div class="mb-3">
            <label for="editCatName" class="form-label">Titel</label>
            <input type="text" id="editCatName" name="new_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <span class="form-label d-block">Teilen mit</span>
            <div class="d-flex flex-column gap-1">
              <?php foreach ($allUsers as $u): $uid=(int)$u['id']; if ($uid === (int)$userId) continue; $nm = htmlspecialchars($u['name'] ?? ('User '.$uid), ENT_QUOTES); ?>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="share_user_ids[]" value="<?= $uid ?>" id="catShare<?= $uid ?>">
                  <label class="form-check-label" for="catShare<?= $uid ?>"><?= $nm ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" id="editCatDeleteBtn" class="btn btn-outline-danger">Löschen</button>
          <div>
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abbrechen</button>
            <button type="submit" class="btn btn-primary">Speichern</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Hinweis Modal für gesperrte Aufgaben -->
<div class="modal fade" id="inProgressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Hinweis</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="inProgressMsg" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Default category id available for JS
  const DEFAULT_CAT_ID = <?php echo (int)($defaultCatId ?? 0); ?>;
  const CURRENT_USER_ID = <?php echo (int)$userId; ?>;
  function showInProgressModal(name){
    var msgEl = document.getElementById('inProgressMsg');
    if (msgEl){ msgEl.textContent = 'Aufgabe bereits in Bearbeitung von: ' + name; }
    var el = document.getElementById('inProgressModal');
    if (el){ var m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }
  }
  // Live submit archive filters on input
  (function(){
    var form = document.querySelector('form.filters');
    if (!form) return;
    var inputs = form.querySelectorAll('input:not([type="hidden"])');
    var timer;
    inputs.forEach(function(inp){
      inp.addEventListener('input', function(){
        clearTimeout(timer);
        timer = setTimeout(function(){ form.submit(); }, 300);
      });
    });
  })();
  // Clear buttons for filter inputs
  (function(){
    var form = document.querySelector('form.filters');
    if (!form) return;
    var buttons = form.querySelectorAll('.clear-input');
    buttons.forEach(function(btn){
      var inp = btn.previousElementSibling;
      if (!inp) return;
      function toggle(){ btn.style.display = inp.value ? 'block' : 'none'; }
      btn.addEventListener('click', function(){
        inp.value = '';
        inp.dispatchEvent(new Event('input'));
        toggle();
      });
      inp.addEventListener('input', toggle);
      toggle();
    });
  })();
  // Empfänger werden serverseitig anhand der Kategorie bestimmt
  (function(){
    var modal = document.getElementById('addTodoModal');
    if (!modal) return;
    var modalForm = modal.querySelector('form');
    var forwardSelect = document.getElementById('forwardUser');
    var forwardAction = document.getElementById('forwardAction');
    if (forwardSelect && forwardAction) {
      forwardSelect.addEventListener('change', function(){
        forwardAction.value = forwardSelect.value ? '1' : '0';
      });
    }
    // Ensure selected priority button remains highlighted and dim others
    var prioRadios = modal.querySelectorAll('#addTodoPriority input[type="radio"]');
    function updatePriorityStyles(){
      var labels = modal.querySelectorAll('#addTodoPriority label');
      labels.forEach(function(lbl){
        lbl.classList.remove('active');
        lbl.style.opacity = '0.15';
      });
      prioRadios.forEach(function(radio){
        if(radio.checked){
          var lbl = modal.querySelector('#addTodoPriority label[for="'+radio.id+'"]');
          if(lbl){
            lbl.classList.add('active');
            lbl.style.opacity = '1';
          }
        }
      });
    }
    prioRadios.forEach(function(radio){
      radio.addEventListener('change', updatePriorityStyles);
    });
    updatePriorityStyles();
    // Suppress reminder toasts when viewing another user's list
    var SUPPRESS_REMINDER_TOASTS = <?php echo ($canSelectUser && (int)$selectedUserId !== (int)$userId) ? 'true' : 'false'; ?>;
    function weekdayName(dateStr){
      try {
        const d = new Date(dateStr);
        return ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'][d.getDay()] || '';
      } catch(_) { return ''; }
    }
    function toShort(dateStr){
      if(!dateStr) return '';
      const p = dateStr.split('-');
      return p.length===3 ? (p[2]+'.'+p[1]+'.') : '';
    }
    // Compute due date in JS based on priority and start date; expose globally so DnD can use it
    window.computeDueByPriorityJS = function(startYmd, prio){
      if(!startYmd) return '';
      var d = new Date(startYmd);
      if (isNaN(d)) return '';
      var p = (prio||'').toLowerCase();
      // Mapping: hoch=+1 Tag, mittel=+2 Tage, niedrig=+7 Tage
      if (p === 'hoch') {
        d.setDate(d.getDate()+1);
      } else if (p === 'mittel') {
        d.setDate(d.getDate()+2);
      } else if (p === 'niedrig') {
        d.setDate(d.getDate()+7);
      }
      var y = d.getFullYear();
      var m = String(d.getMonth()+1).padStart(2,'0');
      var da = String(d.getDate()).padStart(2,'0');
      return y+'-'+m+'-'+da;
    }

    var userEditedDue = false;

    function fillModalFrom(trigger){
      var date = trigger.getAttribute('data-date') || '';
      var dayLabel = trigger.getAttribute('data-daylabel') || weekdayName(date);
      var dayShort = trigger.getAttribute('data-dayshort') || toShort(date);
      var startDateInput = document.getElementById('addTodoStartDate');
      var headerDay = document.getElementById('addTodoHeaderDay');
      var titleEl = document.getElementById('addTodoTitle');
      var desc = document.getElementById('addTodoDesc');
        var titleInput = document.getElementById('addTodoSubject');
        var dueDate = document.getElementById('addTodoDueDate');
        var actionInput = modal.querySelector('input[name="action"]');
        var idInput = modal.querySelector('input[name="todo_id"]');
        var catSelect = document.getElementById('addTodoCategory');
        var forwardSelect = document.getElementById('forwardUser');
        var forwardAction = document.getElementById('forwardAction');
        if (forwardSelect) forwardSelect.value = '';
        if (forwardAction) forwardAction.value = '0';
      function getPriority(){ var el=document.querySelector('input[name="priority"]:checked'); return el?el.value:'mittel'; }
      function setPriority(v){
        var el=document.querySelector('input[name="priority"][value="'+v+'"]');
        if(el){
          el.checked=true;
          el.dispatchEvent(new Event('change'));
        }
      }
      var startInput = document.getElementById('addTodoStartTime');
      var dueTime = document.getElementById('addTodoDueTime');
      var delId = document.getElementById('deleteTodoId');
      if (!date) date = new Date().toISOString().slice(0,10);
      if (startDateInput) startDateInput.value = date;
      // Keine Tages-/Datumsanzeige im Titel
      if (headerDay) { headerDay.textContent = ''; }
      if (trigger.classList.contains('todo-card')){
        if (actionInput) actionInput.value = 'update';
        var tid = trigger.getAttribute('data-id') || '';
        if (idInput) idInput.value = tid;
        if (delId) delId.value = tid;
        if (titleInput) titleInput.value = trigger.getAttribute('data-title') || '';
        if (desc) desc.value = (trigger.getAttribute('data-desc') || '');
        setPriority(trigger.getAttribute('data-priority') || 'mittel');
        if (startDateInput) startDateInput.value = (trigger.getAttribute('data-todo-date') || date);
        if (dueDate) {
          var existingDue = trigger.getAttribute('data-due-date') || '';
          if (existingDue) { dueDate.value = existingDue; }
          else {
            // auto-compute when editing and no due date set
            var s = (startDateInput && startDateInput.value) ? startDateInput.value : date;
            dueDate.value = computeDueByPriorityJS(s, getPriority());
          }
        }
        if (titleEl) titleEl.firstChild.nodeValue = 'To-Do bearbeiten ';
        if (catSelect) catSelect.value = trigger.getAttribute('data-category-id') || String(DEFAULT_CAT_ID);
        // No special scope preselection when editing
        // Existing attachments from card into modal
        var modalAttWrap = document.getElementById('modalExistingAttachments');
        var modalAttList = document.getElementById('modalAttachList');
        if (modalAttWrap && modalAttList){
          var cardList = trigger.querySelector('.attach-list');
          if (cardList && cardList.children.length){
            modalAttList.innerHTML = '';
            // Rebuild list with delete buttons
            var items = cardList.querySelectorAll('.attach-item a.text-decoration-none');
            items.forEach(function(a){
              var href = a.getAttribute('href')||'';
              try{
                var u = new URL(href, window.location.origin);
                var file = u.searchParams.get('file')||'';
                var wrap = document.createElement('div'); wrap.className = 'attach-item';
                var icon = a.parentElement ? a.parentElement.querySelector('i.bi') : null;
                var ic = document.createElement('i'); ic.className = icon ? icon.className : 'bi bi-file-earmark';
                var link = document.createElement('a'); link.href = href; link.target = '_blank'; link.rel = 'noopener'; link.className = 'text-decoration-none'; link.innerHTML = '<span>'+a.textContent+'</span>';
                var dl = document.createElement('a'); dl.href = href + (href.includes('?')?'&':'?') + 'dl=1'; dl.className = 'dl ms-1'; dl.title = 'Download'; dl.innerHTML = '<i class="bi bi-download"></i>';
                var del = document.createElement('button'); del.type = 'button'; del.className = 'btn btn-danger btn-icon-xxs ms-1 btn-del-att'; del.title = 'Löschen'; del.setAttribute('data-file', file);
                del.innerHTML = '<i class="bi bi-x text-white"></i>';
                wrap.appendChild(ic); wrap.appendChild(link); wrap.appendChild(dl); wrap.appendChild(del);
                modalAttList.appendChild(wrap);
              }catch(_){ /* ignore */ }
            });
            modalAttWrap.classList.remove('d-none');
          } else {
            modalAttList.innerHTML = '';
            modalAttWrap.classList.add('d-none');
          }
        }
        // Save current editing card for later DOM updates (attachment deletions)
        window.CURRENT_EDIT_CARD = trigger;
        // Clear any unsaved new-file selections when switching to edit existing
        try{
          var fileInput = document.getElementById('todoAttachments');
          if (fileInput) fileInput.value = '';
          var preview = document.getElementById('newAttachPreview');
          if (preview) preview.innerHTML = '';
        }catch(_){ }
        
      } else {
        if (actionInput) actionInput.value = 'add';
        if (idInput) idInput.value = '';
        if (delId) delId.value = '';
        if (desc) { desc.value = ''; }
        if (titleInput) titleInput.value = '';
        setPriority(trigger.getAttribute('data-priority') || 'mittel');
        if (startDateInput) startDateInput.value = date;
        if (dueDate) dueDate.value = computeDueByPriorityJS(startDateInput.value, getPriority());
        if (titleEl) titleEl.firstChild.nodeValue = 'Neues To-Do ';
        // If opened from a category column/dropzone, prefer that category; otherwise default
        var zoneCat = trigger.getAttribute('data-category') || '';
        if (catSelect) catSelect.value = zoneCat ? zoneCat : String(DEFAULT_CAT_ID);
        // No default scope changes here (handled on modal show)
        // Hide existing attachments section in add mode
        var modalAttWrap2 = document.getElementById('modalExistingAttachments');
        var modalAttList2 = document.getElementById('modalAttachList');
        if (modalAttWrap2 && modalAttList2) { modalAttList2.innerHTML = ''; modalAttWrap2.classList.add('d-none'); }
        window.CURRENT_EDIT_CARD = null;
        // Clear any unsaved new-file selections when opening add mode
        try{
          var fileInput2 = document.getElementById('todoAttachments');
          if (fileInput2) fileInput2.value = '';
          var preview2 = document.getElementById('newAttachPreview');
          if (preview2) preview2.innerHTML = '';
        }catch(_){ }

      }
      if (desc) desc.focus();
    }

    // Enforce max length for title in client
    (function(){
      var title = document.getElementById('addTodoSubject');
      var MAX_TITLE = 100;
      function clamp(el, max){ if (!el) return; if (el.value.length > max) el.value = el.value.slice(0, max); }
      if (title) title.addEventListener('input', function(){ clamp(title, MAX_TITLE); });
      if (modalForm){
        modalForm.addEventListener('submit', function(){ clamp(title, MAX_TITLE); });
      }
    })();
    modal.addEventListener('show.bs.modal', function (event) {
      var trigger = event.relatedTarget;
      if (trigger) { fillModalFrom(trigger); }
      try {
        const wrap = document.getElementById('editMoreWrap');
        const tog  = document.getElementById('moreToggle');
        if (wrap && tog) {
          const col = bootstrap.Collapse.getOrCreateInstance(wrap, { toggle: false });

          const repSel = document.getElementById('repeatFreq');
          const repUntil = document.getElementById('repeatUntil');
          const shouldOpen =
            (repSel && repSel.value && repSel.value !== 'none') ||
            (repUntil && repUntil.value);

          if (shouldOpen) {
            col.show(); tog.setAttribute('aria-expanded','true');
          } else {
            col.hide(); tog.setAttribute('aria-expanded','false');
          }
        }
      } catch(e) {}
    });

    

      if (modalForm){
        modalForm.addEventListener('submit', function(){
          try {
            var catSel = document.getElementById('addTodoCategory');
            var cid = catSel ? parseInt(catSel.value, 10) : 0;
            if (cid) {
              var KEY = 'ttp_cat_open';
              var current = [];
              try { current = JSON.parse(localStorage.getItem(KEY) || '[]'); if (!Array.isArray(current)) current = []; } catch(_) { current = []; }
              if (current.indexOf(String(cid)) === -1) { current.push(String(cid)); localStorage.setItem(KEY, JSON.stringify(current)); }
            }
          } catch(_) { /* ignore storage issues */ }
        });
      }

    // Quick-add category inside To-Do modal removed

    // Multi-assign per Toggle entfernt – Checkbox-Liste wird im Hinzufügen-Modus angezeigt
    // Delete within the same form (Superadmin)
    var deleteBtn = document.getElementById('deleteTodoBtn');
    if (deleteBtn && modalForm){
      deleteBtn.addEventListener('click', function(){
        if (!confirm('To-Do wirklich löschen?')) return;
        var actionInput = modalForm.querySelector('input[name="action"]');
        var idInput = modalForm.querySelector('input[name="todo_id"]');
        if (actionInput) actionInput.value = 'delete';
        // Fallback: ensure id present
        if (!idInput || !idInput.value) return;
        modalForm.submit();
      });
    }

    // Auto-update due date when priority or start date changes (unless user edited due manually)
    function handleAutoDue(ev){
      if (!modal.classList.contains('show')) return;
      var target = ev.target;
      if (!target) return;
      var dueInput = document.getElementById('addTodoDueDate');
      if (target.id === 'addTodoDueDate') { userEditedDue = true; return; }
      if (target.name === 'priority' || target.id === 'addTodoStartDate'){
        if (dueInput && !userEditedDue) {
          var sEl = document.getElementById('addTodoStartDate');
          var pEl = document.querySelector('input[name="priority"]:checked');
          var sd = (sEl && sEl.value) ? sEl.value : '';
          var pr = pEl ? pEl.value : 'niedrig';
          dueInput.value = computeDueByPriorityJS(sd, pr);
        }
      }
    }
    document.addEventListener('input', handleAutoDue);
    document.addEventListener('change', handleAutoDue);
    // Reset manual flag when modal opens
    modal.addEventListener('show.bs.modal', function(){ userEditedDue = false; });
    // Click to open edit modal when a card is clicked (except on controls)
    document.addEventListener('click', function(ev){
      var card = ev.target.closest('.todo-card');
      if (!card) return;
      // Ignore interactive controls only, not the whole form container
      if (ev.target.closest('button, input, select, textarea, .form-check-input, .btn, a')) return;
      var isDone = parseInt(card.dataset.isDone || '0', 10);
      var inBy = parseInt(card.dataset.inProgressBy || '0', 10);
      var inName = card.dataset.inProgressName || '';
      if (isDone === 2 && inBy && inBy !== CURRENT_USER_ID){
        showInProgressModal(inName);
        return;
      }
      fillModalFrom(card);
      var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
      bsModal.show();
    });
    // Intercept toggle actions if another user is editing the task
    document.addEventListener('submit', function(ev){
      var form = ev.target;
      if (!form || !form.querySelector('input[name="action"][value="toggle"]')) return;
      var doneInput = form.querySelector('input[name="done"]');
      if (!doneInput) return;
      var doneVal = parseInt(doneInput.value || '0', 10);
      if (doneVal === 2) return;
      var card = form.closest('.todo-card');
      if (!card) return;
      var isDone = parseInt(card.dataset.isDone || '0', 10);
      var inBy = parseInt(card.dataset.inProgressBy || '0', 10);
      var inName = card.dataset.inProgressName || '';
      if (isDone === 2 && inBy && inBy !== CURRENT_USER_ID){
        ev.preventDefault();
        showInProgressModal(inName);
      }
    });
    // Click on empty area of a day (dropzone) opens modal for adding on that date
    document.addEventListener('click', function(ev){
      if (modal.classList.contains('show')) return; // don't re-open while a modal is visible
      var zone = ev.target.closest('.dropzone');
      if (!zone) return;
      if (ev.target.closest('.todo-card') || ev.target.closest('form')) return;
      // Ensure date present
      if (!zone.getAttribute('data-date')) return;
      // Provide computed labels if not set
      if (!zone.getAttribute('data-daylabel')) zone.setAttribute('data-daylabel','');
      if (!zone.getAttribute('data-dayshort')) zone.setAttribute('data-dayshort','');
      fillModalFrom(zone);
      var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
      bsModal.show();
    });

    // Click anywhere in a priority column (except on cards/forms) also opens add modal for that date
    document.addEventListener('click', function(ev){
      if (modal.classList.contains('show')) return;
      var prioCol = ev.target.closest('.prio-col');
      if (!prioCol) return;
      if (ev.target.closest('.todo-card') || ev.target.closest('form')) return;
      var zone = prioCol.querySelector('.dropzone');
      if (!zone) return;
      fillModalFrom(zone);
      var bsModal = new bootstrap.Modal(modal);
      bsModal.show(zone);
    });

    // Removed handler that opened the add modal when clicking blank column areas

    // Intentionally no handler for blank space between columns to avoid accidental creation
      // Ensure backdrops are cleaned up on hide to avoid stacking and clear unsaved attachments
      modal.addEventListener('hidden.bs.modal', function(){
        document.body.classList.remove('modal-open');
        var backs = document.querySelectorAll('.modal-backdrop');
        backs.forEach(function(b){ b.parentNode && b.parentNode.removeChild(b); });
        try{
          var fileInput = document.getElementById('todoAttachments');
          if (fileInput) fileInput.value = '';
          var preview = document.getElementById('newAttachPreview');
          if (preview) preview.innerHTML = '';
          window.CURRENT_EDIT_CARD = null;
        }catch(_){ }
      });
  })();

  // Delete attachment inside Edit modal
  (function(){
    var modal = document.getElementById('editCategoryModal');
    // Not this modal; attachment deletion belongs to addTodoModal; but list resides in addTodoModal
  })();

  (function(){
    var todoModal = document.getElementById('addTodoModal');
    if (!todoModal) return;
    todoModal.addEventListener('click', function(ev){
      var btn = ev.target.closest('.btn-del-att');
      if (!btn) return;
      ev.preventDefault();
      var file = btn.getAttribute('data-file')||'';
      var idInput = todoModal.querySelector('input[name="todo_id"]');
      var tid = idInput ? (idInput.value||'') : '';
      if (!file || !tid) return;
      if (!confirm('Anhang wirklich löschen?')) return;
      var fd = new FormData();
      fd.append('action','delete_attachment');
      fd.append('todo_id', tid);
      fd.append('file', file);
      // CSRF for fetch POST
      try{ if (window._csrf){ fd.append('csrf_id', window._csrf.id||''); fd.append('csrf_token', window._csrf.token||''); } }catch(_){ }
      fetch('todo.php', { method:'POST', body: fd })
        .then(r=>r.json()).then(function(j){
          if (j && j.ok){
            // Remove from modal list
            var item = btn.closest('.attach-item');
            if (item) {
              var wrap = item.parentElement; item.remove();
              if (wrap && wrap.classList.contains('attach-list') && wrap.children.length === 0){
                var sec = document.getElementById('modalExistingAttachments');
                if (sec) sec.classList.add('d-none');
              }
            }
            // Remove from underlying card on the page
            try{
              var card = window.CURRENT_EDIT_CARD;
              if (card){
                var list = card.querySelector('.attach-list');
                if (list){
                  var links = list.querySelectorAll('a.text-decoration-none');
                  links.forEach(function(a){ if (a.href.includes(encodeURIComponent(file))) { var parent = a.closest('.attach-item'); if (parent) parent.remove(); } });
                }
              }
            }catch(_){ }
          } else {
            alert('Löschen fehlgeschlagen');
          }
        })
        .catch(function(){ alert('Netzwerkfehler beim Löschen'); });
    });
  })();

  // Allow removing selected files in Add modal (before upload)
  (function(){
    var todoModal = document.getElementById('addTodoModal');
    if (!todoModal) return;
    var fileInput = document.getElementById('todoAttachments');
    if (!fileInput) return;
    // Create preview with remove buttons for selected files (only saved on form submit)
    var preview = document.createElement('div');
    preview.className = 'attach-list';
    preview.id = 'newAttachPreview';
    fileInput.parentNode.appendChild(preview);
    function renderPreview(){
      preview.innerHTML = '';
      var files = fileInput.files;
      if (!files || files.length === 0) return;
      for (let i=0; i<files.length; i++){
        var f = files[i];
        var wrap = document.createElement('div'); wrap.className = 'attach-item';
        var ic = document.createElement('i'); ic.className = 'bi bi-paperclip';
        var name = document.createElement('span'); name.textContent = f.name;
        var del = document.createElement('button'); del.type = 'button'; del.className = 'btn btn-danger btn-icon-xxs ms-1'; del.title = 'Entfernen'; del.innerHTML = '<i class="bi bi-x text-white"></i>';
        del.addEventListener('click', function(){
          // Remove file from FileList via DataTransfer
          var dt = new DataTransfer();
          for (let j=0; j<files.length; j++){ if (j !== i) dt.items.add(files[j]); }
          fileInput.files = dt.files; renderPreview();
        });
        wrap.appendChild(ic); wrap.appendChild(name); wrap.appendChild(del);
        preview.appendChild(wrap);
      }
    }
    fileInput.addEventListener('change', renderPreview);
  })();

  // Kategorie bearbeiten Modal öffnen
  (function(){
    var container = document.getElementById('col-categories');
    if (!container) return;
    var modal = document.getElementById('editCategoryModal');
    if (!modal) return;
    var nameInput = document.getElementById('editCatName');
    var idInput = document.getElementById('editCatId');
    var ownerSpan = document.getElementById('editCatOwner');
    container.addEventListener('click', function(ev){
      var btn = ev.target.closest('.cat-edit');
      if (!btn) return;
      ev.preventDefault();
      var catId = btn.getAttribute('data-cat-id') || '';
      var catName = btn.getAttribute('data-cat-name') || '';
      var ownerName = btn.getAttribute('data-cat-owner') || '';
      var shareStr = btn.getAttribute('data-cat-shares') || '';
      var shareIds = shareStr ? shareStr.split(',').filter(Boolean) : [];
      var shareInputs = modal.querySelectorAll('input[name="share_user_ids[]"]');
      shareInputs.forEach(function(inp){ inp.checked = shareIds.includes(inp.value); });
      if (nameInput) nameInput.value = catName;
      if (idInput) idInput.value = catId;
      if (ownerSpan) ownerSpan.textContent = ownerName;
      var bs = bootstrap.Modal.getOrCreateInstance(modal);
      bs.show();
    });
    var delBtn = document.getElementById('editCatDeleteBtn');
    if (delBtn) {
      delBtn.addEventListener('click', function(){
        var catId = (idInput && idInput.value) ? idInput.value : '';
        if (!catId) return;
        if (!confirm('Kategorie wirklich löschen? Zugeordnete Aufgaben behalten ihre Daten, verlieren aber die Kategorie.')) return;
        var form = document.createElement('form');
        form.method = 'post'; form.action = 'todo.php';
        var a = document.createElement('input'); a.name = 'action'; a.value = 'delete_category';
        var b = document.createElement('input'); b.name = 'cat_id'; b.value = catId;
        form.appendChild(a); form.appendChild(b);
        document.body.appendChild(form);
        form.submit();
      });
    }
  })();

  // Bulk-Auswahl und -Aktionen für Erledigt-/Archiv-Listen
  (function(){
    function setupBulk(colEl, selectAllId, itemClass, formId, actionButtons){
      if (!colEl) return;
      const selectAll = document.getElementById(selectAllId);
      const form = document.getElementById(formId);
      const actionInput = form ? form.querySelector('input[name="action"]') : null;
      const idsInput = form ? form.querySelector('input[name="ids"]') : null;
      const getChecks = ()=> Array.from(colEl.querySelectorAll('input.'+itemClass));
      const btns = (actionButtons||[]).map(cfg => ({ cfg, btn: document.getElementById(cfg.btnId) }));

      function updateState(){
        const checks = getChecks();
        const total = checks.length;
        const selected = checks.filter(c=>c.checked).length;
        btns.forEach(({btn})=>{ if (btn) btn.disabled = selected === 0; });
        if (selectAll){
          selectAll.indeterminate = selected > 0 && selected < total;
          selectAll.checked = total > 0 && selected === total;
        }
      }

      if (selectAll){
        selectAll.addEventListener('change', function(){
          const checks = getChecks();
          checks.forEach(c => { c.checked = selectAll.checked; });
          updateState();
        });
      }
      colEl.addEventListener('change', function(ev){
        const el = ev.target;
        if (!el || !el.classList) return;
        if (el.classList.contains(itemClass)) updateState();
      });

      btns.forEach(({cfg, btn})=>{
        if (!btn || !form || !actionInput || !idsInput) return;
        btn.addEventListener('click', function(){
          const ids = getChecks().filter(c=>c.checked).map(c=>c.value);
          if (ids.length === 0) return;
          actionInput.value = cfg.action;
          idsInput.value = ids.join(',');
          form.submit();
        });
      });

      updateState();
    }

    setupBulk(
      document.getElementById('col-done'),
      'selectAllDone',
      'bulk-select-done',
      'bulkDoneForm',
      [ { btnId: 'bulkReopenBtn', action: 'bulk_reopen' }, { btnId: 'bulkArchiveBtn', action: 'bulk_archive' } ]
    );
    setupBulk(
      document.getElementById('col-archive'),
      'selectAllArchive',
      'bulk-select-archive',
      'bulkArchiveForm',
      [ { btnId: 'bulkUnarchiveBtn', action: 'bulk_unarchive' }, { btnId: 'bulkDeleteArchiveBtn', action: 'bulk_delete' } ]
    );
    setupBulk(
      document.getElementById('col-archive-sent'),
      'selectAllArchiveSent',
      'bulk-select-archive-sent',
      'bulkArchiveSentForm',
      [ { btnId: 'bulkUnarchiveSentBtn', action: 'bulk_unarchive' } ]
    );
    setupBulk(
      document.getElementById('col-sent'),
      'selectAllSent',
      'bulk-select-sent',
      'bulkSentForm',
      [ { btnId: 'bulkArchiveSentBtn', action: 'bulk_archive' } ]
    );
  })();

  // Drag & Drop ordering across days with due-date constraint + toast feedback
  (function(){
    const zones = document.querySelectorAll('.dropzone');
    let draggingCard = null;
    let origin = { parent: null, next: null, data: {} };

    function showToast(message, type){
      const wrapId = 'tt-toast-wrap';
      let wrap = document.getElementById(wrapId);
      if (!wrap){
        wrap = document.createElement('div');
        wrap.id = wrapId;
        wrap.style.position = 'fixed';
        wrap.style.top = '1rem';
        wrap.style.left = '50%';
        wrap.style.transform = 'translateX(-50%)';
        wrap.style.zIndex = 1080;
        wrap.style.maxWidth = '90%';
        wrap.style.display = 'flex';
        wrap.style.flexDirection = 'column';
        wrap.style.alignItems = 'center';
        wrap.style.gap = '8px';
        document.body.appendChild(wrap);
      }
      const el = document.createElement('div');
      const bg = type === 'danger' ? 'text-bg-danger' : (type === 'info' ? 'text-bg-info' : 'text-bg-warning');
      // Icons passend zu Erinnerungen: rot=überfällig, blau=heute (Kalender), gelb=1 Tag vorher (Glocke)
      const iconName = type === 'danger' ? 'bi-exclamation-octagon-fill' : (type === 'info' ? 'bi-calendar-day' : 'bi-bell-fill');
      const iconStyle = type === 'warning' ? 'color:#fd7e14' : (type === 'info' ? 'color:#0d6efd' : '');
      el.className = 'toast align-items-center ' + bg + ' border-0 shadow';
      el.setAttribute('role', 'alert');
      el.innerHTML = '<div class="d-flex align-items-center gap-2 px-2">'
        + '<i class="bi '+iconName+'" style="'+iconStyle+'"></i>'
        + '<div class="toast-body"><strong>'+ message +'</strong></div>'
        + '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>'
        + '</div>';
      wrap.appendChild(el);
      const t = new bootstrap.Toast(el, { delay: 3000 });
      t.show();
      el.addEventListener('hidden.bs.toast', ()=> el.remove());
    }

    function onDragStart(e){
      // Prevent drag when starting on the "Erledigt" button/form
      try {
        const isOnDoneButton = e.target && (
          e.target.closest('form') && e.target.closest('form').querySelector('input[name="action"][value="toggle"]') ||
          e.target.closest('.btn-success')
        );
        if (isOnDoneButton) {
          e.preventDefault();
          e.stopPropagation();
          return;
        }
      } catch(_) { /* no-op */ }

      draggingCard = this;
      this.classList.add('dragging');
      origin.parent = this.parentNode;
      origin.next = this.nextSibling;
      origin.data = {
        date: this.getAttribute('data-date') || '',
        priority: this.getAttribute('data-priority') || '',
        category: this.getAttribute('data-category-id') || '',
        due: this.getAttribute('data-due-date') || ''
      };
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', this.dataset.id || '');
    }
    function onDragEnd(){
      this.classList.remove('dragging');
      draggingCard = null;
    }
    function getAfterElement(container, y){
      const els = [...container.querySelectorAll('.todo-card:not(.dragging)')];
      let closest = { offset: Number.NEGATIVE_INFINITY, element: null };
      for (const el of els){
        const box = el.getBoundingClientRect();
        const offset = y - box.top - box.height/2;
        if (offset < 0 && offset > closest.offset){ closest = { offset, element: el }; }
      }
      return closest.element;
    }
    function fetchWithCsrfRetry(form, retryOnce = true){
      return fetch('todo.php', { method: 'POST', body: form, credentials: 'same-origin', keepalive: true })
        .then(async r => {
          if (r.status === 403 && retryOnce){
            const fd = new FormData(); fd.append('action','get_csrf');
            return fetch('todo.php', { method:'POST', body: fd, credentials:'same-origin' })
              .then(r2 => r2.json())
              .then(tok => {
                if (!tok || !tok.ok) throw new Error('csrf');
                window._csrf = { id: tok.id, token: tok.token };
                form.set('csrf_id', tok.id);
                form.set('csrf_token', tok.token);
                return fetchWithCsrfRetry(form, false);
              });
          }
          if (!r.ok) throw new Error('net');
          return r.json().catch(() => ({ ok:true }));
        });
    }
    async function persist(container, card){
      const date = container.getAttribute('data-date') || '';
      const prio = container.getAttribute('data-priority') || '';
      const cat  = container.getAttribute('data-category') || '';
      const ids = [...container.querySelectorAll('.todo-card')].map(el => el.dataset.id).join(',');
      if (!ids) return;
      const form = new FormData();
      form.append('action','reorder');
      if (date) form.append('date', date);
      form.append('ids', ids);
      if (prio) form.append('priority', prio);
      // Always send category param; use 0 to clear when dropping back to "Alle Aufgaben"
      form.append('category', cat ? cat : '0');
      form.append('status', new URLSearchParams(window.location.search).get('status') || 'alle');
      form.append('prio', new URLSearchParams(window.location.search).get('prio') || 'alle');
      // CSRF anhängen
      try {
        if (window._csrf) {
          form.append('csrf_id', window._csrf.id || '');
          form.append('csrf_token', window._csrf.token || '');
        }
      } catch(_) {}
      return fetchWithCsrfRetry(form)
        .then(data => {
          if (card && data && data.error === 'locked') {
            if (origin.parent) {
              if (origin.next) origin.parent.insertBefore(card, origin.next);
              else origin.parent.appendChild(card);
            }
            if (origin.data){
              if (origin.data.date) card.setAttribute('data-date', origin.data.date); else card.removeAttribute('data-date');
              if (origin.data.priority) card.setAttribute('data-priority', origin.data.priority);
              if (origin.data.category) card.setAttribute('data-category-id', origin.data.category); else card.removeAttribute('data-category-id');
              if (origin.data.due) card.setAttribute('data-due-date', origin.data.due); else card.removeAttribute('data-due-date');
            }
            showInProgressModal(data.user || '');
          }
        })
        .catch(err => {
          if (card) {
            if (origin.parent) {
              if (origin.next) origin.parent.insertBefore(card, origin.next);
              else origin.parent.appendChild(card);
            }
            if (origin.data){
              if (origin.data.date) card.setAttribute('data-date', origin.data.date); else card.removeAttribute('data-date');
              if (origin.data.priority) card.setAttribute('data-priority', origin.data.priority);
              if (origin.data.category) card.setAttribute('data-category-id', origin.data.category); else card.removeAttribute('data-category-id');
              if (origin.data.due) card.setAttribute('data-due-date', origin.data.due); else card.removeAttribute('data-due-date');
            }
          }
          showToast(
            err.message === 'csrf'
              ? 'Aktion blockiert (Sicherheitsprüfung). Bitte erneut versuchen.'
              : 'Netzwerkfehler. Nicht gespeichert.',
            'danger'
          );
        });
    }

    zones.forEach(zone => {
      zone.addEventListener('dragover', (e)=>{
        e.preventDefault();
        zone.classList.add('drag-over');
        const after = getAfterElement(zone, e.clientY);
        if (!draggingCard) return;
        if (after == null) zone.appendChild(draggingCard);
        else zone.insertBefore(draggingCard, after);
      });
      zone.addEventListener('dragleave', ()=> zone.classList.remove('drag-over'));
      zone.addEventListener('drop', async (e)=>{
        e.preventDefault();
        zone.classList.remove('drag-over');
        // After dropping, update the card's dataset so the edit modal reflects the new state
        try{
          const newDate = zone.getAttribute('data-date') || '';
          const newPrio = zone.getAttribute('data-priority') || '';
          if (draggingCard){
            // Enforce due-date constraint: cannot move beyond due_date
            const due = draggingCard.getAttribute('data-due-date') || '';
            if (due && newDate && newDate > due){
              // revert to origin position
              if (origin.parent){
                if (origin.next) origin.parent.insertBefore(draggingCard, origin.next);
                else origin.parent.appendChild(draggingCard);
              }
              const d = due.split('-');
              const dueShort = d.length===3 ? (d[2]+'.'+d[1]+'.'+d[0]) : due;
              showToast('Verschieben nicht möglich: Spätestens bis '+ dueShort);
              return; // do not persist
            }
            if (newDate) draggingCard.setAttribute('data-date', newDate);
            if (newPrio) draggingCard.setAttribute('data-priority', newPrio);
            const newCat = zone.getAttribute('data-category') || '';
            if (newCat) draggingCard.setAttribute('data-category-id', newCat);
            else draggingCard.removeAttribute('data-category-id');
            // UI-only: update "Spätestens" text according to new priority
            // Fallback to today's date if no start date is available
            let startForDue = newDate || draggingCard.getAttribute('data-todo-date') || '';
            if (!startForDue) {
              const t = new Date();
              const ty = t.getFullYear();
              const tm = String(t.getMonth()+1).padStart(2,'0');
              const td = String(t.getDate()).padStart(2,'0');
              startForDue = `${ty}-${tm}-${td}`;
            }
            if (newPrio && startForDue){
              const newDue = computeDueByPriorityJS(startForDue, newPrio);
              if (newDue){
                draggingCard.setAttribute('data-due-date', newDue);
                const parts = newDue.split('-');
                const dueShort = parts.length===3 ? (parts[2]+'.'+parts[1]+'.') : '';
                if (dueShort){
                  const info = draggingCard.querySelector('.text-muted');
                  if (info){
                    // Prefer dedicated due label marker if present
                    let dueEl = draggingCard.querySelector('[data-role="due-label"]');
                    if (!dueEl){
                      // Fallback to the second line (the one containing "am ...")
                      const lines = info.querySelectorAll('div');
                      const metaLine = lines.length > 1 ? lines[1] : info;
                      let found = false;
                      metaLine.querySelectorAll('span').forEach(function(s){
                        const t = (s.textContent||'').trim();
                        if (t.startsWith('Spätestens:')){ dueEl = s; found = true; }
                      });
                      if (!found){
                        const sep = document.createTextNode(' · ');
                        dueEl = document.createElement('span');
                        metaLine.appendChild(sep);
                        metaLine.appendChild(dueEl);
                      }
                    }
                    if (dueEl){ dueEl.setAttribute('data-role','due-label'); dueEl.textContent = 'Spätestens: ' + dueShort; }
                  }
                }
              }
            }
            // Update visible date label inside the card
            const dparts = (newDate||'').split('-');
            const short = dparts.length===3 ? (dparts[2]+'.'+dparts[1]+'.') : '';
            const dateEl = draggingCard.querySelector('.todo-date');
            if (dateEl && short) dateEl.textContent = 'Tag: ' + short;
          }
        }catch(_){}
        await persist(zone, draggingCard);
        if (origin.parent && origin.parent !== zone){
          await persist(origin.parent, null);
        }
      });
    });

    document.querySelectorAll('.todo-card').forEach(card => {
      card.addEventListener('dragstart', onDragStart);
      card.addEventListener('dragend', onDragEnd);
    });

    // Reminder notifications: overdue, today due, tomorrow due (only for own view)
    try{
      const cards = document.querySelectorAll('.todo-card:not(.done)');
      let cntOver = 0, cntToday = 0, cntTomorrow = 0;
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth()+1).padStart(2,'0');
      const dd = String(today.getDate()).padStart(2,'0');
      const todayStr = `${yyyy}-${mm}-${dd}`;
      const tmr = new Date(today.getTime() + 24*60*60*1000);
      const tmm = String(tmr.getMonth()+1).padStart(2,'0');
      const tdd = String(tmr.getDate()).padStart(2,'0');
      const tomorrowStr = `${tmr.getFullYear()}-${tmm}-${tdd}`;
      // helper for monthly same-day check
      const isSameDayOfMonth = (iso, startIso)=>{
        const a = new Date(iso), b = new Date(startIso);
        return a.getDate() === b.getDate();
      };
      let cntRepeatToday = 0;
      cards.forEach(c => {
        const due = c.getAttribute('data-due-date') || '';
        if (!due) return;
        if (due < todayStr) cntOver++;
        else if (due === todayStr) cntToday++;
        else if (due === tomorrowStr) cntTomorrow++;
        // recurrence due today
        const rf = (c.getAttribute('data-repeat-freq')||'none').toLowerCase();
        if (rf !== 'none'){
          const startIso = c.getAttribute('data-todo-date')||'';
          const untilIso = c.getAttribute('data-repeat-until')||'';
          if (startIso){
            // within until (if set)
            if (!untilIso || todayStr <= untilIso){
              if (rf === 'daily') cntRepeatToday++;
              else if (rf === 'weekly') {
                const start = new Date(startIso);
                if (start.getDay() === today.getDay()) cntRepeatToday++;
              } else if (rf === 'monthly') {
                if (isSameDayOfMonth(todayStr, startIso)) cntRepeatToday++;
              }
            }
          }
        }
      });
      if (!SUPPRESS_REMINDER_TOASTS){
        if (cntOver > 0) showToast(`${cntOver} Aufgabe(n) überfällig`, 'danger');
        if (cntToday > 0) showToast(`${cntToday} Aufgabe(n) heute spätestens`, 'info');
        if (cntTomorrow > 0) showToast(`${cntTomorrow} Aufgabe(n) spätestens morgen`, 'warning');
        if (cntRepeatToday > 0) showToast(`${cntRepeatToday} wiederkehrende Aufgabe(n) heute`, 'info');
      }
    } catch(_){ /* noop */ }

    // Kategorien: Ganze Zeile klickbar machen
    (function(){
      const container = document.getElementById('col-categories');
      if (!container) return;
      container.addEventListener('click', function(ev){
        const header = ev.target.closest('.category-section .col-header');
        if (!header) return;
        // Interaktive Elemente nicht abfangen
        if (ev.target.closest('button, a, input, select, textarea')) return;
        // Edit-Button ignorieren
        if (ev.target.closest('.cat-edit')) return;
        const sel = header.getAttribute('data-bs-target');
        if (!sel) return;
        const target = document.querySelector(sel);
        if (!target) return;
        const col = bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });
        const expanded = (header.getAttribute('aria-expanded') === 'true');
        if (expanded) { col.hide(); header.setAttribute('aria-expanded','false'); }
        else { col.show(); header.setAttribute('aria-expanded','true'); }
      });
    })();

    // Toggle all categories (expand/collapse)
    (function(){
      const container = document.getElementById('col-categories');
      if (!container) return;
      const btn = document.getElementById('btnToggleAllCats');
      if (!btn) return;
      btn.addEventListener('click', function(){
        const expand = btn.getAttribute('data-state') !== 'expanded';
        const nodes = container.querySelectorAll('.collapse');
        nodes.forEach(function(el){
          const col = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });
          if (expand) col.show(); else col.hide();
        });
        // Set state + label
        btn.setAttribute('data-state', expand ? 'expanded' : 'collapsed');
        btn.innerHTML = expand
          ? '<i class="bi bi-chevron-double-up me-1"></i> Alle schließen'
          : '<i class="bi bi-chevron-double-down me-1"></i> Alle öffnen';
        // Align aria states on headers
        container.querySelectorAll('.category-section .col-header').forEach(function(t){ t.setAttribute('aria-expanded', expand ? 'true' : 'false'); });
      });
    })();

      // Persist opened/closed categories across reloads and new entries
      (function(){
        const KEY_OPEN = 'ttp_cat_open';
      const KEY_CLOSED = 'ttp_cat_closed';
      const container = document.getElementById('col-categories');
      if (!container) return;
      const allBtn = document.getElementById('btnToggleAllCats');
      const syncAllBtn = () => {
        if (!allBtn) return;
        const total = container.querySelectorAll('.category-section .collapse').length;
        const opened = container.querySelectorAll('.category-section .collapse.show').length;
        const allOpen = total > 0 && opened === total;
        allBtn.setAttribute('data-state', allOpen ? 'expanded' : 'collapsed');
        allBtn.innerHTML = allOpen
          ? '<i class="bi bi-chevron-double-up me-1"></i> Alle schließen'
          : '<i class="bi bi-chevron-double-down me-1"></i> Alle öffnen';
      };
      const parseId = (sel)=>{ if (!sel) return null; const m = sel.match(/#catwrap-(\d+)/); return m ? String(m[1]) : null; };
      const loadSet = (k)=>{ try { const a = JSON.parse(localStorage.getItem(k) || '[]'); return new Set((Array.isArray(a)?a:[]).map(String)); } catch(_) { return new Set(); } };
      const saveSet = (k,set)=>{ try { localStorage.setItem(k, JSON.stringify(Array.from(set))); } catch(_) {} };
      const openSet = loadSet(KEY_OPEN);
      const closedSet = loadSet(KEY_CLOSED);
      // Closed wins over open: if something is explicitly closed, ensure it's not reopened
      closedSet.forEach(id => { if (openSet.has(id)) openSet.delete(id); });
      // Also honor URL param ?open_cat=<id> after adding a new task
      try {
        const qp = new URLSearchParams(window.location.search);
        const oc = qp.get('open_cat');
        if (oc) {
          const id = String(parseInt(oc,10));
          // Do not override an explicit closed state
          if (!closedSet.has(id)) { openSet.add(id); }
          saveSet(KEY_OPEN, openSet);
        }
      } catch(_) {}
      // Bind to each collapse to track state changes and restore
      container.querySelectorAll('.category-section .col-header').forEach(function(header){
        const sel = header.getAttribute('data-bs-target') || '';
        const id = parseId(sel);
        const target = document.querySelector(sel);
        if (!target || !id) return;
        target.addEventListener('shown.bs.collapse', function(){
          openSet.add(id);
          if (closedSet.has(id)) closedSet.delete(id);
          saveSet(KEY_OPEN, openSet); saveSet(KEY_CLOSED, closedSet);
          header.setAttribute('aria-expanded','true');
          syncAllBtn();
        });
        target.addEventListener('hidden.bs.collapse', function(){
          openSet.delete(id);
          closedSet.add(id);
          saveSet(KEY_OPEN, openSet); saveSet(KEY_CLOSED, closedSet);
          header.setAttribute('aria-expanded','false');
          syncAllBtn();
        });
        const col = bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });
        // Show categories by default unless explicitly closed
        if (!closedSet.has(id)) {
          col.show();
          header.setAttribute('aria-expanded','true');
        } else {
          header.setAttribute('aria-expanded','false');
        }
      });
      syncAllBtn();
      // Integrate with "toggle all"
      if (allBtn){
        allBtn.addEventListener('click', function(){
          const expand = allBtn.getAttribute('data-state') !== 'expanded';
          const ids = Array.from(container.querySelectorAll('.category-section .col-header')).map(function(b){ return parseId(b.getAttribute('data-bs-target')||''); }).filter(Boolean);
          if (expand){
            ids.forEach(function(id){ openSet.add(id); closedSet.delete(id); });
          } else {
            openSet.clear();
            ids.forEach(function(id){ closedSet.add(id); });
          }
          saveSet(KEY_OPEN, openSet);
          saveSet(KEY_CLOSED, closedSet);
          syncAllBtn();
        });
      }
    })();
  })();
</script>
</body>
</html>