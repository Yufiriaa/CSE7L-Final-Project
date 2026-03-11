<?php
// ─── LOGIN / REGISTER PAGES ───────────────────────────────────────────────────
?>
<div class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <div style="display:inline-flex;align-items:center;justify-content:center;width:62px;height:62px;border-radius:50%;background:radial-gradient(circle at 35% 35%,#c41e20,#7B1113);border:2px solid #F5C518;margin:0 auto">
        <span style="color:#F5C518;font-weight:900;font-size:22px">UM</span>
      </div>
      <h1 class="login-title">UM SMART STUDY GROUP</h1>
      <p class="login-sub">Formation System</p>
    </div>

    <?php if ($page === 'login'): ?>
    <h2 class="login-h2">LOGIN</h2>
    <?php if (!empty($loginError)): ?><div class="error-box"><?= h($loginError) ?></div><?php endif; ?>
    <?php if ($toast): ?><div class="alert-info"><?= h($toast) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" placeholder="Enter email" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" placeholder="Enter password" required>
      </div>
      <button class="btn btn-primary" style="width:100%;margin-top:8px">Sign In</button>
    </form>
    <p class="text-muted text-center">No account? <a href="?page=register" class="text-link">Register</a></p>

    <?php else: ?>
    <h2 class="login-h2">REGISTRATION</h2>
    <form method="POST">
      <input type="hidden" name="action" value="register">
      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input class="form-control" type="text" name="full_name" placeholder="Enter your full name" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email *</label>
        <input class="form-control" type="email" name="email" placeholder="Enter UMindanao Email" required>
      </div>
      <div class="form-group">
        <label class="form-label">College/Department *</label>
        <div id="collegeGrid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:10px;margin-bottom:10px">
        </div>
        <select class="form-control" id="departmentSelect" name="department" required style="display:none">
          <option value="">Select College...</option>
          <option value="cae">College of Accounting Education</option>
          <option value="cafae">College of Architecture and Fine Arts Education</option>
          <option value="case">College of Arts and Sciences Education</option>
          <option value="cbae">College of Business Administration Education</option>
          <option value="cce">College of Computing Education</option>
          <option value="ccje">College of Criminal Justice Education</option>
          <option value="cee">College of Engineering Education</option>
          <option value="che">College of Hospitality Education</option>
          <option value="chse">College of Health Sciences Education</option>
          <option value="cte">College of Teacher Education</option>
        </select>
        <div id="selectedCollege" style="margin-top:10px;padding:8px;background:#f0f0f0;border-radius:4px;text-align:center;display:none">
          <small id="selectedCollegueName" style="color:#666"></small>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Program *</label>
        <select class="form-control" id="programSelect" name="program" required>
          <option value="">Select Department First</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Year Level</label>
        <select class="form-control" name="year_level">
          <option>1st Year</option><option>2nd Year</option><option>3rd Year</option><option>4th Year</option><option>5th Year+</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Password *</label>
        <input class="form-control" type="password" name="password" required>
      </div>
      <button class="btn btn-primary" style="width:100%;margin-top:8px">Sign Up</button>
    </form>
    <p class="text-muted text-center">Already have an account? <a href="?page=login" class="text-link">Sign in</a></p>
    <?php endif; ?>
  </div>
</div>

<script>
const collegeData = {
  'cae': { name: 'College of Accounting Education', fullName: 'College of Accounting Education' },
  'cafae': { name: 'College of Architecture and Fine Arts Education', fullName: 'College of Architecture and Fine Arts Education' },
  'case': { name: 'College of Arts and Sciences Education', fullName: 'College of Arts and Sciences Education' },
  'cbae': { name: 'College of Business Administration Education', fullName: 'College of Business Administration Education' },
  'cce': { name: 'College of Computing Education', fullName: 'College of Computing Education' },
  'ccje': { name: 'College of Criminal Justice Education', fullName: 'College of Criminal Justice Education' },
  'cee': { name: 'College of Engineering Education', fullName: 'College of Engineering Education' },
  'che': { name: 'College of Hospitality Education', fullName: 'College of Hospitality Education' },
  'chse': { name: 'College of Health Sciences Education', fullName: 'College of Health Sciences Education' },
  'cte': { name: 'College of Teacher Education', fullName: 'College of Teacher Education' }
};

const departmentPrograms = {
  'cae': [
    'Bachelor of Science in Accountancy',
    'Bachelor of Science in Accounting Information System',
    'Bachelor of Science in Management Accounting'
  ],
  'cafae': [
    'Bachelor of Science in Architecture',
    'Bachelor of Fine Arts and Design Major in Painting',
    'Bachelor of Science in Interior Design'
  ],
  'case': [
    'Bachelor of Arts in Communication',
    'Bachelor of Arts in English Language',
    'Bachelor of Arts in Political Science',
    'Bachelor of Science in Agroforestry',
    'Bachelor of Science in Biology with Specializations in Ecology',
    'Bachelor of Science in Environmental Science',
    'Bachelor of Science in Forestry',
    'Bachelor of Science in Psychology',
    'Bachelor of Science in Social Work'
  ],
  'cbae': [
    'Bachelor of Science in Business Administration Major in Business Economics',
    'Bachelor of Science in Business Administration Major in Financial Management',
    'Bachelor of Science in Business Administration Major in Human Resource Management',
    'Bachelor of Science in Business Administration Major in Marketing Management',
    'Bachelor of Science in Customs Administration',
    'Bachelor of Science in Entrepreneurship',
    'Bachelor of Science in Legal Management',
    'Bachelor of Science in Real Estate Management'
  ],
  'cce': [
    'Bachelor of Science in Computer Science',
    'Bachelor of Science in Entertainment and Multimedia Computing Major in Game Development',
    'Bachelor of Science in Information Technology',
    'Bachelor of Library and Information Science',
    'Bachelor of Multimedia Arts'
  ],
  'ccje': [
    'Bachelor of Science in Criminology'
  ],
  'cee': [
    'Bachelor of Science in Chemical Engineering',
    'Bachelor of Science in Civil Engineering Major in Geotechnical',
    'Bachelor of Science in Civil Engineering Major in Structural',
    'Bachelor of Science in Civil Engineering Major in Transportation',
    'Bachelor of Science in Computer Engineering',
    'Bachelor of Science in Electrical Engineering',
    'Bachelor of Science in Electronics Engineering',
    'Bachelor of Science in Materials Engineering',
    'Bachelor of Science in Mechanical Engineering'
  ],
  'che': [
    'Bachelor of Science in Hospitality Management',
    'Bachelor of Science in Tourism Management'
  ],
  'chse': [
    'Bachelor of Science in Medical Technology',
    'Bachelor of Science in Nursing',
    'Bachelor of Science in Nutrition and Dietetics',
    'Bachelor of Science in Pharmacy'
  ],
  'cte': [
    'Bachelor of Elementary Education',
    'Bachelor of Physical Education',
    'Bachelor of Secondary Education Major in English',
    'Bachelor of Secondary Education Major in Filipino',
    'Bachelor of Secondary Education Major in Mathematics',
    'Bachelor of Secondary Education Major in Science',
    'Bachelor of Secondary Education Major in Social Studies',
    'Bachelor of Special Needs Education Major in Elementary School Teaching'
  ]
};

// Render college icons on page load
function renderCollegeGrid() {
  const grid = document.getElementById('collegeGrid');
  Object.entries(collegeData).forEach(([code, data]) => {
    const box = document.createElement('div');
    box.className = 'college-box';
    box.style.cssText = 'cursor:pointer;padding:10px;border:2px solid #ddd;border-radius:8px;text-align:center;transition:all 0.3s;background:#fafafa';
    box.innerHTML = `
      <img src="./assets/images/${code}.png" alt="${code}" style="width:60px;height:60px;margin-bottom:8px">
      <div style="font-size:11px;font-weight:600;color:#333">${code.toUpperCase()}</div>
    `;
    box.addEventListener('click', () => selectCollege(code));
    grid.appendChild(box);
  });
}

function selectCollege(code) {
  const deptSelect = document.getElementById('departmentSelect');
  const selectedDiv = document.getElementById('selectedCollege');
  const nameDiv = document.getElementById('selectedCollegueName');
  
  // Update hidden select
  deptSelect.value = code;
  
  // Show selected college info
  nameDiv.textContent = collegeData[code].fullName;
  selectedDiv.style.display = 'block';
  
  // Highlight selected box
  document.querySelectorAll('.college-box').forEach(box => {
    box.style.borderColor = '#ddd';
    box.style.background = '#fafafa';
  });
  event.currentTarget.style.borderColor = 'var(--m)';
  event.currentTarget.style.background = 'rgba(var(--m-rgb), 0.1)';
  
  // Update program dropdown
  const progSelect = document.getElementById('programSelect');
  progSelect.innerHTML = '<option value="">Select a Program</option>';
  
  if (departmentPrograms[code]) {
    departmentPrograms[code].forEach(program => {
      const option = document.createElement('option');
      option.value = program;
      option.textContent = program;
      progSelect.appendChild(option);
    });
  }
}

const deptSelect = document.getElementById('departmentSelect');
const progSelect = document.getElementById('programSelect');

deptSelect.addEventListener('change', function() {
  const selectedDept = this.value;
  progSelect.innerHTML = '<option value="">Select a Program</option>';
  
  if (selectedDept && departmentPrograms[selectedDept]) {
    departmentPrograms[selectedDept].forEach(program => {
      const option = document.createElement('option');
      option.value = program;
      option.textContent = program;
      progSelect.appendChild(option);
    });
  }
});

// Initialize on page load
renderCollegeGrid();
</script>
