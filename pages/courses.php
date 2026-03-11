<?php
// ─── COURSES ──────────────────────────────────────────────────────────────────
$showAdd = isset($_GET['add']);
$courses = fetchAll('SELECT * FROM Course ORDER BY CourseTitle ASC');
?>
<div class="page-header">
  <h2 class="page-title mb-0">Courses</h2>
  <?php if ($user['role']==='admin'): ?><a href="?page=courses&add=1" class="btn btn-primary">+ Add Course</a><?php endif; ?>
</div>
<div class="card-grid grid-2 gap-14">
  <?php foreach ($courses as $c):
    $gcnt = fetchOne('SELECT COUNT(*) as cnt FROM StudyGroup WHERE CourseID = ?', [$c['CourseID']])['cnt'];
  ?>
  <div class="card course-card">
    <div class="row-between">
      <div>
        <span class="badge course-code-badge"><?= h($c['CourseCode']) ?></span>
        <div class="course-title"><?= h($c['CourseTitle']) ?></div>
        <div class="course-meta"><?= $c['Units'] ?> units</div>
      </div>
      <span class="badge badge-blue"><?= $gcnt ?> group<?= $gcnt!==1?'s':'' ?></span>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php if ($showAdd): ?>
<div class="modal-overlay open">
  <div class="modal-box">
    <div class="modal-header">
      <span class="modal-title">Add Course</span>
      <a href="?page=courses" class="modal-close">×</a>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_course">
      <div class="form-group"><label class="form-label">Course Code *</label><input class="form-control" name="code" placeholder="e.g. CS101" required></div>
      <div class="form-group"><label class="form-label">Course Title *</label><input class="form-control" name="title" placeholder="e.g. Algorithms" required></div>
      <div class="form-group">
        <label class="form-label">Units</label>
        <select class="form-control" name="units"><?php for($i=1;$i<=6;$i++): ?><option value="<?=$i?>"><?=$i?> unit<?=$i>1?'s':''?></option><?php endfor; ?></select>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
        <a href="?page=courses" class="btn btn-secondary">Cancel</a>
        <button class="btn btn-primary" type="submit">Add Course</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
