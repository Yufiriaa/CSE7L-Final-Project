<?php
// ─── ADMIN ────────────────────────────────────────────────────────────────────
$tab = $_GET['tab'] ?? 'students';
$showAddGroup = isset($_GET['add_group']);
?>
<h2 class="page-title">Admin Panel</h2>
<div class="tab-bar">
  <?php foreach (['students','groups','system'] as $t): ?>
  <a href="?page=admin&tab=<?= $t ?>" class="tab-btn <?= $tab===$t?'active':'' ?>"><?= ucfirst($t) ?></a>
  <?php endforeach; ?>
</div>

<?php if ($tab === 'students'): ?>
<?php $students = fetchAll('SELECT * FROM Student ORDER BY FullName ASC'); ?>
<div class="card">
  <div class="section-title">All Students (<?= count($students) ?>)</div>
  <table class="data-table">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Program</th><th>Year</th></tr></thead>
    <tbody>
    <?php foreach ($students as $s): ?>
    <tr>
      <td><span class="badge"><?= $s['StudentID'] ?></span></td>
      <td style="font-weight:600"><?= h($s['FullName']) ?></td>
      <td style="color:#888;font-size:12px"><?= h($s['Email']) ?></td>
      <td><?= h($s['Program']) ?></td>
      <td style="text-align:center"><?= $s['YearLevel'] ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif ($tab === 'groups'): ?>
<?php $groups = fetchAll('SELECT sg.*, c.CourseCode FROM StudyGroup sg LEFT JOIN Course c ON sg.CourseID = c.CourseID ORDER BY sg.GroupName ASC'); ?>
<div style="display:flex;justify-content:flex-end;margin-bottom:12px">
  <a href="?page=admin&tab=groups&add_group=1" class="btn btn-primary">+ Create Group</a>
</div>
<div class="card">
  <table class="data-table">
    <thead><tr><th>Group</th><th>Course</th><th>Members</th><th>Max</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($groups as $g):
      $cnt = fetchOne('SELECT COUNT(*) as cnt FROM GroupMembership WHERE GroupID = ?', [$g['GroupID']])['cnt'];
    ?>
    <tr>
      <td style="font-weight:600"><?= h($g['GroupName']) ?></td>
      <td style="color:#888"><?= h($g['CourseCode']??'') ?></td>
      <td style="text-align:center"><?= $cnt ?></td>
      <td style="text-align:center"><?= $g['MaxMembers'] ?></td>
      <td>
        <form method="POST" style="display:inline" onsubmit="return confirm('Delete this group?')">
          <input type="hidden" name="action" value="delete_group">
          <input type="hidden" name="group_id" value="<?= $g['GroupID'] ?>">
          <button class="btn btn-danger btn-sm" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php if ($showAddGroup): ?>
<?php $courses = fetchAll('SELECT * FROM Course ORDER BY CourseTitle ASC'); ?>
<div class="modal-overlay open">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Create Study Group</span>
      <a href="?page=admin&tab=groups" class="modal-close">×</a>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_group">
      <input type="hidden" name="redirect" value="page=admin&tab=groups">
      <div class="form-group">
        <label class="form-label">Course *</label>
        <select class="form-control" name="course_id" required>
          <option value="">Select course…</option>
          <?php foreach ($courses as $c): ?><option value="<?= $c['CourseID'] ?>"><?= h($c['CourseCode'].' - '.$c['CourseTitle']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group"><label class="form-label">Group Name *</label><input class="form-control" name="name" placeholder="e.g. Database Study Group A" required></div>
      <div class="form-group">
        <label class="form-label">Max Members</label>
        <select class="form-control" name="max_members"><?php foreach ([3,4,5,6,7,8,10] as $n): ?><option value="<?=$n?>" <?=$n==6?'selected':''?>><?=$n?></option><?php endforeach; ?></select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
        <a href="?page=admin&tab=groups" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">Create</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php elseif ($tab === 'system'): ?>
<?php
$totalStudents = fetchOne('SELECT COUNT(*) as cnt FROM Student')['cnt'];
$totalGroups = fetchOne('SELECT COUNT(*) as cnt FROM StudyGroup')['cnt'];
$totalMemberships = fetchOne('SELECT COUNT(*) as cnt FROM GroupMembership')['cnt'];
$totalSchedules = fetchOne('SELECT COUNT(*) as cnt FROM Schedule')['cnt'];
$totalCourses = fetchOne('SELECT COUNT(*) as cnt FROM Course')['cnt'];
$avgGroupSize = $totalGroups > 0 ? number_format($totalMemberships / $totalGroups, 1) : '0';
?>
<div class="card-grid grid-2 gap-14">
  <?php
  $sysStats = [
    ['Total Students',     $totalStudents,         '🎓', 'var(--m)'],
    ['Study Groups',       $totalGroups,           '👥', '#1d4ed8'],
    ['Total Memberships',  $totalMemberships,      '🔗', '#16a34a'],
    ['Scheduled Sessions', $totalSchedules,        '📅', '#9333ea'],
    ['Available Courses',  $totalCourses,          '📚', '#ea580c'],
    ['Avg Group Size',     $avgGroupSize,          '📊', '#0891b2'],
  ];
  foreach ($sysStats as [$l,$v,$i,$c]):
  ?>
  <div class="card" style="display:flex;align-items:center;gap:13px;border-left:4px solid <?= $c ?>">
    <span style="font-size:28px"><?= $i ?></span>
    <div>
      <div style="font-size:26px;font-weight:900;color:<?= $c ?>"><?= $v ?></div>
      <div style="font-size:10px;color:#aaa;font-weight:700;text-transform:uppercase;letter-spacing:.5px"><?= $l ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php endif; // admin tabs ?>
