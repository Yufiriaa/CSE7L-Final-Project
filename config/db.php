<?php
// ============================================================================
// Database Configuration & Connection
// ============================================================================

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'UMStudyGroupDB');
define('DB_PORT', 3306);

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    die('Database Connection Failed: ' . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset('utf8mb4');

// Enable error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// ─── HELPER FUNCTIONS FOR QUERIES ───────────────────────────────────────────

// Execute query with optional parameters and return result
function query($sql, $params = []) {
    global $conn;
    
    if ($params) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return null;
        
        // Build type string
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) $types .= 'i';
            elseif (is_float($param)) $types .= 'd';
            else $types .= 's';
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        return $conn->query($sql);
    }
}

// Fetch all results as associative array
function fetchAll($sql, $params = []) {
    $result = query($sql, $params);
    if (!$result) return [];
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fetch single row
function fetchOne($sql, $params = []) {
    $result = query($sql, $params);
    if (!$result) return null;
    return $result->fetch_assoc();
}

// Get last insert ID
function lastInsertId() {
    global $conn;
    return $conn->insert_id;
}

// Get affected rows
function affectedRows() {
    global $conn;
    return $conn->affected_rows;
}

// ─── STORED PROCEDURE FUNCTIONS: LOCKING & CONCURRENCY ──────────────────────

/**
 * sp_JoinGroupSafely - Safe join with FOR UPDATE locking
 * 
 * Implements: Transaction Locking + Concurrency Control
 * Uses SELECT FOR UPDATE to atomically check capacity and insert member
 * 
 * @param int $studentId Student ID attempting to join
 * @param int $groupId Group ID to join
 * @return array ['success' => bool, 'message' => string]
 */
function spJoinGroupSafely($studentId, $groupId) {
    global $conn;
    
    // Prepare stored procedure call
    $stmt = $conn->prepare('CALL sp_JoinGroupSafely(?, ?, @success, @message)');
    $stmt->bind_param('ii', $studentId, $groupId);
    $stmt->execute();
    $stmt->close();
    
    // Get output parameters
    $result = $conn->query('SELECT @success as success, @message as message');
    $row = $result->fetch_assoc();
    
    return [
        'success' => (bool)$row['success'],
        'message' => $row['message']
    ];
}

/**
 * sp_CreateScheduleAtomic - Atomic schedule creation with logging
 * 
 * Implements: Transaction Logging + Concurrency Control
 * Ensures schedule creation and audit logging happen atomically
 * 
 * @param int $groupId Study group ID
 * @param string $studyDate Date in Y-m-d format
 * @param string $startTime Time in H:i format
 * @param string $endTime Time in H:i format
 * @param string $studyMode Online/In-Person/Hybrid
 * @return array ['success' => bool, 'message' => string, 'scheduleId' => int|null]
 */
function spCreateScheduleAtomic($groupId, $studyDate, $startTime, $endTime, $studyMode) {
    global $conn;
    
    // Prepare stored procedure call
    $stmt = $conn->prepare('CALL sp_CreateScheduleAtomic(?, ?, ?, ?, ?, @success, @message, @scheduleId)');
    $stmt->bind_param('issss', $groupId, $studyDate, $startTime, $endTime, $studyMode);
    $stmt->execute();
    $stmt->close();
    
    // Get output parameters
    $result = $conn->query('SELECT @success as success, @message as message, @scheduleId as scheduleId');
    $row = $result->fetch_assoc();
    
    return [
        'success' => (bool)$row['success'],
        'message' => $row['message'],
        'scheduleId' => $row['scheduleId'] ? (int)$row['scheduleId'] : null
    ];
}

// ─── AUDIT LOG HELPER ────────────────────────────────────────────────────────

/**
 * logAuditAction - Manually log an action to AuditLog
 * (Most logging is automatic via triggers, but this allows manual entries)
 * 
 * @param string $actionType Type of action (e.g., 'LOGIN', 'UPDATE_PROFILE')
 * @param string $tableAffected Table name affected
 * @param int $recordId ID of affected record
 * @param int $userId Student ID performing action
 * @param string $details Additional details
 */
function logAuditAction($actionType, $tableAffected, $recordId, $userId, $details = '') {
    query('INSERT INTO AuditLog (ActionType, TableAffected, RecordID, UserID, Details) VALUES (?, ?, ?, ?, ?)',
        [$actionType, $tableAffected, $recordId, $userId, $details]);
}
?>
