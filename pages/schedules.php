<?php
// ─── SCHEDULES ────────────────────────────────────────────────────────────────
$myScheds = [];
if (!empty($myGIds)) {
    $groupPlaceholders = implode(',', array_fill(0, count($myGIds), '?'));
    $myScheds = fetchAll("SELECT s.*, sg.GroupName FROM Schedule s 
                         LEFT JOIN StudyGroup sg ON s.GroupID = sg.GroupID
                         WHERE s.GroupID IN ($groupPlaceholders)
                         ORDER BY s.StudyDate ASC", $myGIds);
}
$showAddModal = isset($_GET['add']);

// Build dynamic calendar based on current month
$today = new DateTime();
$currentMonth = (int)($_GET['month'] ?? $today->format('m'));
$currentYear = (int)($_GET['year'] ?? $today->format('Y'));

// Create calendar start date
$calStart = new DateTime("$currentYear-$currentMonth-01");
$startDow = (int)$calStart->format('w'); // 0=Sun
$daysInMonth = (int)$calStart->format('t'); // Days in month

// Month/year display
$monthYear = $calStart->format('F, Y');
$prevMonth = $currentMonth - 1;
$prevYear = $currentYear;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $currentMonth + 1;
$nextYear = $currentYear;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>
<div class="page-header">
  <h2 class="page-title mb-0">Schedules</h2>
  <a href="?page=sched&add=1" class="btn btn-primary">+ Schedule Session</a>
</div>

<!-- Calendar -->
<div class="card" style="margin-bottom:18px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <a href="?page=sched&month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-ghost btn-sm">&laquo; Previous</a>
    <div class="section-title mb-0"><?= $monthYear ?></div>
    <a href="?page=sched&month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-ghost btn-sm">Next &raquo;</a>
  </div>
  <div class="cal-grid" style="margin-bottom:5px">
    <?php foreach (['SUN','MON','TUE','WED','THU','FRI','SAT'] as $d): ?>
    <div class="cal-header"><?= $d ?></div>
    <?php endforeach; ?>
  </div>
  <?php
  $rows = ceil(($daysInMonth + $startDow) / 7);
  for ($row = 0; $row < $rows; $row++):
  ?>
  <div class="cal-grid">
    <?php for ($col = 0; $col < 7; $col++):
      $dayNum = $row * 7 + $col - $startDow + 1;
      $inMonth = $dayNum >= 1 && $dayNum <= $daysInMonth;
      $dateStr = $inMonth ? sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $dayNum) : '';
      $isToday = $inMonth && $dateStr === $today->format('Y-m-d');
      $sesGroup = null;
      if ($dateStr) { 
        foreach ($myScheds as $s) { 
          if ($s['StudyDate'] === $dateStr) { 
            $sesGroup = $s['GroupName']; 
            break; 
          } 
        } 
      }
      $cls = 'cal-day';
      if (!$inMonth) $cls .= ' other-month';
      if ($isToday) $cls .= ' today';
      if ($sesGroup) $cls .= ' has-session';
    ?>
    <div class="<?= $cls ?>">
      <?php if ($inMonth): ?>
      <div class="cal-day-num"><?= $dayNum ?></div>
      <?php if ($sesGroup): ?><div class="cal-session-label"><?= h(implode(' ', array_slice(explode(' ', $sesGroup), 0, 3))) ?></div><?php endif; ?>
      <?php endif; ?>
    </div>
    <?php endfor; ?>
  </div>
  <?php endfor; ?>
</div>

<!-- Upcoming Sessions -->
<div class="card">
  <div class="section-title">Upcoming Sessions</div>
  <?php if (empty($myScheds)): ?>
  <div style="color:#ccc;text-align:center;padding:20px">No sessions scheduled.</div>
  <?php else: foreach ($myScheds as $s): ?>
  <div class="session-row">
    <div>
      <div class="session-name"><?= h($s['GroupName']??'') ?></div>
      <div class="session-meta"><?= h($s['StudyMode']) ?> · <?= fmt12($s['StartTime']) ?>–<?= fmt12($s['EndTime']) ?></div>
    </div>
    <div class="col-right">
      <div class="session-date"><?= fmtDate($s['StudyDate']) ?></div>
      <div class="session-when"><?= daysUntil($s['StudyDate']) ?></div>
    </div>
  </div>
  <?php endforeach; endif; ?>
</div>

<!-- Add Schedule Modal -->
<?php if ($showAddModal): ?>
<div class="modal-overlay open">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Schedule a Study Session</span>
      <a href="?page=sched" class="modal-close">×</a>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_schedule">
      <div class="form-group">
        <label class="form-label">Study Group *</label>
        <select class="form-control" name="group_id" required>
          <option value="">Select a group…</option>
          <?php foreach ($myGroups as $g): ?>
          <option value="<?= $g['GroupID'] ?>"><?= h($g['GroupName']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Date *</label>
        <input class="form-control" type="date" name="study_date" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div class="form-group">
          <label class="form-label">Start Time</label>
          <input class="form-control" type="time" name="start_time" value="18:00">
        </div>
        <div class="form-group">
          <label class="form-label">End Time</label>
          <input class="form-control" type="time" name="end_time" value="20:00">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Mode</label>
        <select class="form-control" name="mode">
          <option>Online</option><option>In-Person</option><option>Hybrid</option>
        </select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
        <a href="?page=sched" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">Schedule</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
