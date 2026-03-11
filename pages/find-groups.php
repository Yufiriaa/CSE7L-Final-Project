<?php
// ─── FIND GROUPS ──────────────────────────────────────────────────────────────
$search = strtolower(trim($_GET['q'] ?? ''));
$myPref = fetchOne('SELECT * FROM StudyPreference WHERE StudentID = ?', [$user['StudentID']]);
$prefTime = $myPref['PreferredTime'] ?? 'Evening';
$prefSize = $myPref['PreferredGroupSize'] ?? '4-6';

// Get all groups with course info
$filtered = fetchAll('SELECT sg.*, c.CourseCode, c.CourseTitle FROM StudyGroup sg 
                      LEFT JOIN Course c ON sg.CourseID = c.CourseID
                      ORDER BY sg.GroupName ASC');

// Filter by search query
if ($search) {
    $filtered = array_filter($filtered, function($g) use ($search) {
        return str_contains(strtolower($g['GroupName']), $search) || 
               str_contains(strtolower($g['CourseTitle'] ?? ''), $search) || 
               str_contains(strtolower($g['CourseCode'] ?? ''), $search);
    });
}
?>
<div style="display:grid;grid-template-columns:1fr 264px;gap:18px;align-items:start">
  <div>
    <h2 class="page-title">Find Groups</h2>
    <form method="GET" style="margin-bottom:14px">
      <input type="hidden" name="page" value="find">
      <input class="form-control" type="text" name="q" placeholder="🔍 Search groups or courses…" value="<?= h($_GET['q']??'') ?>">
    </form>
    <?php foreach ($filtered as $g):
      $scheds = fetchAll('SELECT * FROM Schedule WHERE GroupID = ? ORDER BY StudyDate ASC LIMIT 1', [$g['GroupID']]);
      $sc = !empty($scheds) ? $scheds[0] : null;
      $cnt = fetchOne('SELECT COUNT(*) as cnt FROM GroupMembership WHERE GroupID = ?', [$g['GroupID']])['cnt'];
      $isMem = in_array($g['GroupID'], $myGIds);
      $isFull = $cnt >= $g['MaxMembers'];
      $borderColor = $isMem ? '#16a34a' : 'var(--m)';
    ?>
    <div class="card" style="border-left:4px solid <?= $borderColor ?>;margin-bottom:11px">
      <div style="display:flex;justify-content:space-between;align-items:flex-start">
        <div style="flex:1">
          <div style="display:flex;gap:6px;margin-bottom:6px;flex-wrap:wrap">
            <span class="group-tag">Study Group</span>
            <?php if ($isMem): ?><span class="badge badge-green">Joined ✓</span><?php endif; ?>
            <?php if ($isFull && !$isMem): ?><span class="badge badge-red">Full</span><?php endif; ?>
          </div>
          <div class="group-name"><?= h($g['GroupName']) ?></div>
          <div class="group-meta"><?= h($g['CourseCode']??'') ?> · <?= h($sc['StudyMode']??'Online') ?> · <?= $cnt ?>/<?= $g['MaxMembers'] ?> members</div>
          <?php if ($sc): ?><div style="font-size:12px;color:#666">📅 <?= fmtDate($sc['StudyDate']) ?> · <?= fmt12($sc['StartTime']) ?>–<?= fmt12($sc['EndTime']) ?></div><?php endif; ?>
        </div>
        <div style="margin-left:14px;margin-top:2px">
          <?php if ($isMem): ?>
          <span class="badge badge-green">Member ✓</span>
          <?php elseif ($isFull): ?>
          <button class="btn btn-primary btn-sm" disabled style="opacity:.5">Full</button>
          <?php else: ?>
          <form method="POST" style="display:inline">
            <input type="hidden" name="action" value="join_group">
            <input type="hidden" name="group_id" value="<?= $g['GroupID'] ?>">
            <button class="btn btn-primary btn-sm" type="submit">Join Group</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($filtered)): ?><div style="text-align:center;padding:40px;color:#ccc">No groups found.</div><?php endif; ?>
  </div>

  <!-- Preferences Panel -->
  <div class="card sticky-top">
    <div class="section-title" style="color:var(--m)">User Preferences</div>
    <form method="POST">
      <input type="hidden" name="action" value="save_prefs">
      <div class="form-group">
        <label class="form-label">Preferred Study Time</label>
        <select class="form-control" name="pref_time">
          <?php foreach (['Morning','Afternoon','Evening','Night'] as $t): ?>
          <option <?= $prefTime===$t?'selected':'' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Preferred Group Size</label>
        <select class="form-control" name="pref_size">
          <?php foreach (['2-3','4-6','7-10'] as $s): ?>
          <option <?= $prefSize===$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" style="width:100%" type="submit">Update Preferences</button>
    </form>
  </div>
</div>
