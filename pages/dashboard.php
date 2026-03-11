<?php
// ─── DASHBOARD ────────────────────────────────────────────────────────────────
// Get user's groups
$myGroups = $myGroups ?? fetchAll('SELECT sg.* FROM vw_StudentGroups sg WHERE sg.StudentID = ?', [$user['StudentID']]);

// Get upcoming schedules for user's groups
$upcomingScheds = [];
$totalStudyHours = 0;
if (!empty($myGroups)) {
    $groupIds = array_column($myGroups, 'GroupID');
    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
    $upcomingScheds = fetchAll("SELECT * FROM Schedule WHERE GroupID IN ($placeholders) ORDER BY StudyDate ASC", $groupIds);
    
    // Calculate total study hours from all scheduled sessions
    foreach ($upcomingScheds as $sched) {
        $startTime = new DateTime($sched['StudyDate'] . ' ' . $sched['StartTime']);
        $endTime = new DateTime($sched['StudyDate'] . ' ' . $sched['EndTime']);
        $hoursPerSession = $startTime->diff($endTime)->h + ($startTime->diff($endTime)->i / 60);
        $totalStudyHours += $hoursPerSession;
    }
}
$upcomingCount = count(array_filter($upcomingScheds, fn($s) => daysUntil($s['StudyDate']) !== 'Past'));

// Get stats
$totalStudents = fetchOne('SELECT COUNT(*) as cnt FROM Student')['cnt'];
$totalGroups = fetchOne('SELECT COUNT(*) as cnt FROM StudyGroup')['cnt'];

// Calculate progress percentage (goal: 20 hours per semester)
$studyGoal = 20;
$progressPercentage = min(100, (int)(($totalStudyHours / $studyGoal) * 100));
?>
<div style="display:flex;align-items:center;gap:16px;margin-bottom:20px">
  <div>
    <h1 style="color:var(--m);font-size:27px;font-weight:900;margin-bottom:4px">Welcome, <?= h($user['FullName']) ?></h1>
    <p style="color:#aaa;font-style:italic;font-size:14px"><?= h($user['Program']) ?></p>
  </div>
  <?php if ($user['College'] ?? false): ?>
  <img src="./assets/images/<?= h($user['College']) ?>.png" alt="College" style="width:80px;height:80px;margin-left:auto">
  <?php endif; ?>
</div>

<!-- Stats -->
<div class="card-grid grid-4 gap-14" style="margin-bottom:22px">
  <?php
  $stats = [
    ['My Groups', count($myGroups), '👥', 'var(--m)'],
    ['Upcoming Sessions', $upcomingCount, '📅', '#1d4ed8'],
    ['Total Students', $totalStudents, '🎓', '#16a34a'],
    ['Active Groups', $totalGroups, '📌', '#9333ea'],
  ];
  foreach ($stats as [$l,$v,$i,$c]):
  ?>
  <div class="card stat-card" style="--c:<?= $c ?>">
    <div class="stat-icon"><?= $i ?></div>
    <div class="stat-val"><?= $v ?></div>
    <div class="stat-label"><?= $l ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card-grid grid-2 gap-18">
  <!-- My Groups Overview -->
  <div class="card">
    <div class="row-between" style="margin-bottom:13px">
      <div class="section-title mb-0">My Active Groups Overview</div>
      <a href="?page=myg" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <?php if (empty($myGroups)): ?>
    <div style="text-align:center;padding:22px 0;color:#ccc;font-size:13px">No groups yet. <a href="?page=find" style="color:var(--m);font-weight:700">Find one →</a></div>
    <?php else: foreach ($myGroups as $g):
      $scheds = array_filter($upcomingScheds, fn($s)=>$s['GroupID']==$g['GroupID']);
      usort($scheds, fn($a,$b)=>strcmp($a['StudyDate'],$b['StudyDate']));
      $nextSched = reset($scheds);
      $memberCount = fetchOne('SELECT COUNT(*) as cnt FROM GroupMembership WHERE GroupID = ?', [$g['GroupID']])['cnt'];
    ?>
    <div class="row-between border-bottom-row">
      <div>
        <div style="font-weight:700;font-size:14px"><?= h($g['GroupName']) ?></div>
        <div style="font-size:12px;color:#aaa"><?= $nextSched ? 'Next session '.daysUntil($nextSched['StudyDate']) : 'No session scheduled' ?></div>
      </div>
      <span class="badge"><?= $memberCount ?> members</span>
    </div>
    <?php endforeach; endif; ?>
  </div>

  <div style="display:flex;flex-direction:column;gap:18px">
    <!-- Activity -->
    <div class="card">
      <div class="section-title">Recent Activity</div>
      <?php
      $acts = [];
      foreach ($myGroups as $g) $acts[] = ['✅', "You joined {$g['GroupName']}"];
      foreach (array_slice(array_values($upcomingScheds),0,3) as $s) {
        $g = fetchOne('SELECT GroupName FROM StudyGroup WHERE GroupID = ?', [$s['GroupID']]);
        if ($g) $acts[] = ['📅', "{$g['GroupName']} — ".fmtDate($s['StudyDate'])];
      }
      $acts = array_slice($acts, 0, 6);
      if (empty($acts)): ?><div style="color:#ccc;font-size:13px">No activity yet.</div>
      <?php else: foreach ($acts as [$icon,$text]): ?>
      <div class="activity-item"><span><?= $icon ?></span><span><?= h($text) ?></span></div>
      <?php endforeach; endif; ?>
    </div>
    <!-- Progress -->
    <div class="card">
      <div class="section-title">Personal Study Progress</div>
      <div style="font-size:12px;color:#aaa;margin-bottom:6px">Total Study Hours This Semester</div>
      <div class="progress-wrap"><div class="progress-bar" style="width:<?= $progressPercentage ?>%"></div></div>
      <div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;margin-top:5px">
        <span><?= number_format($totalStudyHours, 1) ?>h completed</span><span style="color:var(--m);font-weight:700"><?= $progressPercentage ?>%</span><span><?= (int)$studyGoal ?>h goal</span>
      </div>
      <a href="?page=sched" class="btn btn-ghost btn-sm" style="width:100%;text-align:center;margin-top:10px;display:block">View Schedule</a>
    </div>
  </div>
</div>
