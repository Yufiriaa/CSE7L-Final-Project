<?php
session_start();

// ─── DATABASE CONNECTION ─────────────────────────────────────────────────────
require 'config/db.php';

// ─── HELPER FUNCTIONS ─────────────────────────────────────────────────────────
function fmt12($t) {
    if (!$t) return '';
    list($h, $m) = explode(':', $t);
    $hr = (int)$h;
    $ampm = $hr >= 12 ? 'pm' : 'am';
    $hr12 = $hr > 12 ? $hr - 12 : ($hr == 0 ? 12 : $hr);
    return "{$hr12}:{$m}{$ampm}";
}
function fmtDate($d) {
    if (!$d) return '';
    return date('M j, Y', strtotime($d));
}
function daysUntil($d) {
    $today = new DateTime();
    $target = new DateTime($d);
    $diff = (int)$today->diff($target)->days * ($target >= $today ? 1 : -1);
    if ($diff < 0) return 'Past';
    if ($diff == 0) return 'Today';
    if ($diff == 1) return 'Tomorrow';
    return "In {$diff} days";
}
function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// ─── AUTH ─────────────────────────────────────────────────────────────────────
$user = $_SESSION['user'] ?? null;
$toast = $_SESSION['toast'] ?? null;
$_SESSION['toast'] = null;
$loginError = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        
        $student = fetchOne('SELECT * FROM Student WHERE Email = ?', [$email]);
        if ($student && password_verify($pass, $student['Password'])) {
            $_SESSION['user'] = array_merge($student, ['role'=>'student']);
            header('Location: ?page=dash'); exit;
        }
        $loginError = 'Invalid email or password.';
    }

    if ($action === 'register') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $college = trim($_POST['department'] ?? '');
        $program = trim($_POST['program'] ?? '');
        $yearLevelText = trim($_POST['year_level'] ?? '1st Year');
        $yearLevel = (int)substr($yearLevelText, 0, 1); // Extract "1", "2", "3", or "4" from "1st Year", etc.
        $password = $_POST['password'] ?? '';
        
        // Validate email uniqueness
        $existingEmail = fetchOne('SELECT StudentID FROM Student WHERE Email = ?', [$email]);
        
        if ($fullName && $email && $college && $program && $password) {
            if ($existingEmail) {
                $_SESSION['toast'] = '❌ Email already registered. Please use a different email.';
            } else {
                // StudentID is AUTO_INCREMENT, so we don't insert it manually
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                query('INSERT INTO Student (FullName, Email, College, Program, YearLevel, Password) VALUES (?, ?, ?, ?, ?, ?)',
                    [$fullName, $email, $college, $program, $yearLevel, $hashedPassword]);
                $_SESSION['toast'] = '✅ Account created! You can now sign in.';
                header('Location: ?page=login'); exit;
            }
        } else {
            $_SESSION['toast'] = '❌ Please fill in all required fields.';
        }
        header('Location: ?page=register'); exit;
    }

    if ($action === 'logout') {
        session_destroy();
        header('Location: ?page=login'); exit;
    }

    if ($user && $action === 'join_group') {
        $groupId = (int)($_POST['group_id'] ?? 0);
        $studentId = $user['StudentID'];
        
        // Use stored procedure with FOR UPDATE locking and concurrency control
        // This ensures atomic capacity check + insertion with proper transaction handling
        $result = spJoinGroupSafely($studentId, $groupId);
        
        if ($result['success']) {
            $_SESSION['toast'] = "✅ Joined successfully!";
        } else {
            $_SESSION['toast'] = '❌ ' . $result['message'];
        }
        header('Location: ?page=find'); exit;
    }

    if ($user && $action === 'leave_group') {
        $groupId = (int)($_POST['group_id'] ?? 0);
        $studentId = $user['StudentID'];
        query('DELETE FROM GroupMembership WHERE StudentID = ? AND GroupID = ?', [$studentId, $groupId]);
        $_SESSION['toast'] = '👋 Left the group.';
        header('Location: ?page=myg'); exit;
    }

    if ($user && $action === 'add_schedule') {
        $groupId = (int)($_POST['group_id'] ?? 0);
        $studyDate = $_POST['study_date'] ?? '';
        $startTime = $_POST['start_time'] ?? '18:00';
        $endTime = $_POST['end_time'] ?? '20:00';
        $mode = $_POST['mode'] ?? 'Online';
        
        if ($groupId && $studyDate) {
            // Use stored procedure with atomic transaction + audit logging
            // Ensures schedule and audit log both created or both rolled back
            $result = spCreateScheduleAtomic($groupId, $studyDate, $startTime, $endTime, $mode);
            
            if ($result['success']) {
                $_SESSION['toast'] = '✅ Session scheduled!';
            } else {
                $_SESSION['toast'] = '❌ ' . $result['message'];
            }
        } else {
            $_SESSION['toast'] = '❌ Missing required fields';
        }
        header('Location: ?page=sched'); exit;
    }

    if ($user && $action === 'save_prefs') {
        $studentId = $user['StudentID'];
        $prefTime = $_POST['pref_time'] ?? 'Evening';
        $prefSize = $_POST['pref_size'] ?? '4-6';
        
        $existing = fetchOne('SELECT PreferenceID FROM StudyPreference WHERE StudentID = ?', [$studentId]);
        if ($existing) {
            query('UPDATE StudyPreference SET PreferredTime = ?, PreferredGroupSize = ? WHERE StudentID = ?',
                [$prefTime, $prefSize, $studentId]);
        } else {
            query('INSERT INTO StudyPreference (StudentID, PreferredTime, PreferredGroupSize) VALUES (?, ?, ?)',
                [$studentId, $prefTime, $prefSize]);
        }
        $_SESSION['toast'] = '✅ Preferences saved!';
        header('Location: ?page=find'); exit;
    }

    if ($user && $action === 'add_course') {
        $code = trim($_POST['code'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $units = (int)($_POST['units'] ?? 3);
        
        if ($code && $title) {
            query('INSERT INTO Course (CourseCode, CourseTitle, Units) VALUES (?, ?, ?)',
                [$code, $title, $units]);
            $_SESSION['toast'] = '✅ Course added!';
        }
        header('Location: ?page=courses'); exit;
    }

    if ($user && $action === 'add_group') {
        $courseId = (int)($_POST['course_id'] ?? 0);
        $groupName = trim($_POST['name'] ?? '');
        $maxMembers = (int)($_POST['max_members'] ?? 6);
        $redirectPage = $_POST['redirect'] ?? 'admin&tab=groups';
        
        if ($courseId && $groupName) {
            query('INSERT INTO StudyGroup (CourseID, GroupName, CreatedDate, MaxMembers) VALUES (?, ?, ?, ?)',
                [$courseId, $groupName, date('Y-m-d'), $maxMembers]);
            $_SESSION['toast'] = '✅ Group created!';
        }
        header('Location: ?' . $redirectPage); exit;
    }

    if ($user && $action === 'delete_group') {
        $groupId = (int)($_POST['group_id'] ?? 0);
        query('DELETE FROM StudyGroup WHERE GroupID = ?', [$groupId]);
        $_SESSION['toast'] = 'Group deleted.';
        header('Location: ?page=admin&tab=groups'); exit;
    }
}

$page = $_GET['page'] ?? ($user ? 'dash' : 'login');
if (!$user && !in_array($page, ['login','register'])) { header('Location: ?page=login'); exit; }

// Precompute current user's group IDs from database
$myGIds = [];
$myGroups = [];
if ($user && $user['role'] !== 'admin') {
    $myGroups = fetchAll('SELECT sg.* FROM vw_StudentGroups sg WHERE sg.StudentID = ?', [$user['StudentID']]);
    foreach ($myGroups as $g) { $myGIds[] = $g['GroupID']; }
}

// ─── HTML OUTPUT ──────────────────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UM Smart Study Group Formation System</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if ($toast): ?>
<div class="toast" id="toast"><?= h($toast) ?></div>
<?php endif; ?>

<?php
// ─── LOGIN / REGISTER PAGES ───────────────────────────────────────────────────
if ($page === 'login' || $page === 'register'):
  require 'pages/auth.php';

else: ?>

<!-- ─── MAIN APP LAYOUT ───────────────────────────────────────────────────── -->
<div class="layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sb-header">
      <div class="sb-logo"><span>UM</span></div>
      <div>
        <div class="sb-brand-main">UM SMART</div>
        <div class="sb-brand-sub">University of Mindanao</div>
      </div>
    </div>
    <nav class="sb-nav">
      <?php
      $navItems = [
        ['dash',    '⊞', 'Dashboard'],
        ['myg',     '👥', 'My Groups'],
        ['find',    '🔍', 'Find Groups'],
        ['sched',   '📅', 'Schedules'],
        ['courses', '📚', 'Courses'],
      ];
      if ($user['role'] === 'admin') $navItems[] = ['admin', '⚙️', 'Admin Panel'];
      foreach ($navItems as [$pg, $ic, $lb]):
        $active = ($page === $pg) ? ' active' : '';
      ?>
      <a href="?page=<?= $pg ?>" class="sb-link<?= $active ?>"><?= $ic ?> <?= $lb ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="sb-footer">
      <div class="sb-user">
        <div class="sb-avatar"><?= h(mb_substr($user['FullName'],0,1)) ?></div>
        <div>
          <div class="sb-name"><?= h($user['FullName']) ?></div>
          <div class="sb-prog"><?= h($user['Program']) ?></div>
        </div>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="logout">
        <button class="sb-logout" type="submit">Sign Out</button>
      </form>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main">

<?php

// ─── DASHBOARD ────────────────────────────────────────────────────────────────
if ($page === 'dash'):
  require 'pages/dashboard.php';

// ─── MY GROUPS ────────────────────────────────────────────────────────────────
elseif ($page === 'myg'):
  require 'pages/my-groups.php';

// ─── FIND GROUPS ──────────────────────────────────────────────────────────────
elseif ($page === 'find'):
  require 'pages/find-groups.php';

// ─── SCHEDULES ────────────────────────────────────────────────────────────────
elseif ($page === 'sched'):
  require 'pages/schedules.php';

// ─── COURSES ──────────────────────────────────────────────────────────────────
elseif ($page === 'courses'):
  require 'pages/courses.php';

// ─── ADMIN ────────────────────────────────────────────────────────────────────
elseif ($page === 'admin' && $user['role']==='admin'):
  require 'pages/admin.php';

else: ?>
<!-- Fallback: redirect to dashboard -->
<script>window.location='?page=dash'</script>
<?php endif; // page routing ?>

  </main>
</div>

<?php endif; // app vs login ?>

<script src="assets/js/script.js"></script>
</body>
</html>
