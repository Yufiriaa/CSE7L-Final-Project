<?php
// ─── MY GROUPS ────────────────────────────────────────────────────────────────
$myGroups = $myGroups ?? fetchAll('SELECT sg.* FROM vw_StudentGroups sg WHERE sg.StudentID = ?', [$user['StudentID']]);
$detailId = (int)($_GET['detail'] ?? 0);
$detailGroup = $detailId ? fetchOne('SELECT * FROM StudyGroup WHERE GroupID = ?', [$detailId]) : null;
$showCreateGroup = isset($_GET['create_group']);
?>
<div class="page-header">
  <h2 class="page-title mb-0">My Groups</h2>
  <a href="?page=myg&create_group=1" class="btn btn-primary">+ Create Group</a>
</div>

<?php if (empty($myGroups)): ?>
<div class="card" style="text-align:center;padding:48px">
  <div style="font-size:52px;margin-bottom:12px">📚</div>
  <div style="color:#ccc;font-size:15px">You haven't joined any groups yet. <a href="?page=find" style="color:var(--m);font-weight:700">Find one →</a></div>
</div>
<?php else: ?>
<div class="card-grid grid-2 gap-14">
  <?php foreach ($myGroups as $g):
    $course = fetchOne('SELECT * FROM Course WHERE CourseID = ?', [$g['CourseID']]);
    $scheds = fetchAll('SELECT * FROM Schedule WHERE GroupID = ? ORDER BY StudyDate ASC', [$g['GroupID']]);
    $nextSched = !empty($scheds) ? $scheds[0] : null;
    $cnt = fetchOne('SELECT COUNT(*) as cnt FROM GroupMembership WHERE GroupID = ?', [$g['GroupID']])['cnt'];
  ?>
  <div class="card" style="border-left:4px solid var(--m)">
    <span class="group-tag">Study Group</span>
    <div class="group-name"><?= h($g['GroupName']) ?></div>
    <div class="group-meta"><?= h($course['CourseCode'] ?? '') ?> · <?= h($nextSched['StudyMode'] ?? 'Online') ?> · <?= $cnt ?>/<?= $g['MaxMembers'] ?> members</div>
    <?php if ($nextSched): ?>
    <div class="schedule-box">
      📅 <strong><?= fmtDate($nextSched['StudyDate']) ?></strong> · <?= fmt12($nextSched['StartTime']) ?>–<?= fmt12($nextSched['EndTime']) ?>
      <span class="schedule-days"><?= daysUntil($nextSched['StudyDate']) ?></span>
    </div>
    <?php endif; ?>
    <div class="flex-actions">
      <a href="?page=myg&detail=<?= $g['GroupID'] ?>" class="btn btn-primary btn-sm">Details</a>
      <form method="POST" style="display:inline" onsubmit="return confirm('Leave this group?')">
        <input type="hidden" name="action" value="leave_group">
        <input type="hidden" name="group_id" value="<?= $g['GroupID'] ?>">
        <button class="btn btn-secondary btn-sm" type="submit">Leave</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Detail Modal -->
<?php if ($detailGroup): ?>
<div class="modal-overlay open" id="detailModal">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title"><?= h($detailGroup['name']) ?></span>
      <a href="?page=myg" class="modal-close">×</a>
    </div>
    <?php $course = findById($db['courses'], $detailGroup['courseId']); ?>
    <p style="color:#777;font-size:13px;margin-bottom:14px"><?= h($course['title'] ?? '') ?> · Max <?= $detailGroup['maxMembers'] ?> members</p>
    <div style="font-size:13px;color:var(--m);font-weight:700;margin-bottom:10px">Members</div>
    <?php foreach (filterBy($db['memberships'],'groupId',$detailGroup['id']) as $m):
      $st = findById($db['students'], $m['studentId']);
      if (!$st) continue;
    ?>
    <div class="member-row">
      <div class="member-avatar"><?= h(mb_substr($st['fullName'],0,1)) ?></div>
      <div style="flex:1">
        <div class="member-name"><?= h($st['fullName']) ?></div>
        <div class="member-sub"><?= h($st['program']) ?> · Year <?= $st['yearLevel'] ?></div>
      </div>
      <span style="font-size:11px;color:#ccc">Joined <?= fmtDate($m['dateJoined']) ?></span>
    </div>
    <?php endforeach; ?>
    <div style="font-size:13px;color:var(--m);font-weight:700;margin:14px 0 10px">Schedules</div>
    <?php $gScheds = filterBy($db['schedules'],'groupId',$detailGroup['id']);
    if (empty($gScheds)): ?><div style="color:#ccc;font-size:13px">No sessions scheduled.</div>
    <?php else: foreach ($gScheds as $s): ?>
    <div class="schedule-box">📅 <?= fmtDate($s['studyDate']) ?> · <?= fmt12($s['startTime']) ?>–<?= fmt12($s['endTime']) ?> · <?= h($s['mode']) ?></div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- Create Group Modal -->
<?php if ($showCreateGroup): ?>
<?php $courses = fetchAll('SELECT * FROM Course ORDER BY CourseTitle ASC'); ?>
<div class="modal-overlay open">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Create Study Group</span>
      <a href="?page=myg" class="modal-close">×</a>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_group">
      <input type="hidden" name="redirect" value="page=myg">
      <div class="form-group">
        <label class="form-label">Course *</label>
        <select class="form-control" name="course_id" required>
          <option value="">Select course…</option>
          <?php foreach ($courses as $c): ?><option value="<?= $c['CourseID'] ?>"><?= h($c['CourseCode'].' - '.$c['CourseTitle']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Group Name *</label>
        <input class="form-control" name="name" placeholder="e.g. Database Study Group A" required>
      </div>
      <div class="form-group">
        <label class="form-label">Max Members</label>
        <select class="form-control" name="max_members">
          <?php foreach ([3,4,5,6,7,8,10] as $n): ?><option value="<?=$n?>" <?=$n==6?'selected':''?>><?=$n?></option><?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
        <a href="?page=myg" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">Create</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
