<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$message = "";
$message_type = "";

/* Remove Teacher Action */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();

    $teacher_id = (int)($_POST["teacher_id"] ?? 0);

    if ($teacher_id <= 0) {
        $message = "Invalid teacher selected.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM teachers WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $message = "Teacher removed successfully!";
            $message_type = "success";
        } else {
            $message = "Teacher not found.";
            $message_type = "error";
        }
        $stmt->close();
    }
}

/* Fetch Teachers */
$teachers = [];
$res = $conn->query("SELECT teacher_id, full_name, email, created_at FROM teachers ORDER BY teacher_id DESC");
while ($row = $res->fetch_assoc()) {
    $teachers[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Remove Teacher | SBMS</title>
  <link rel="stylesheet" href="css/admin.css">

  <style>
    select{
      width:100%;
      padding:12px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,0.14);
      background:#eef4ff;
      color:#0b1220;
      outline:none;
      font-weight:700;
    }
  </style>
</head>

<body class="admin-ui">

<div class="nav">
  <div class="container nav-inner">
    <div class="brand">
      <span class="badge admin"></span>
      <span>SBMS Admin Panel</span>
    </div>

    <div class="links">
      <a href="admin_dashboard.php">Dashboard</a>
      <a href="add_teacher.php">Add Teacher</a>
      <a href="admin_teachers_list.php">Teachers</a>
      <a href="admin_teacher_progress.php">Progress</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="hero">
  <div class="container">

    <div class="card admin-form-card">
      <div class="form-head">
        <div>
          <h2>Remove Teacher</h2>
          <p class="muted">Select a teacher and permanently delete the account.</p>
        </div>
      </div>

      <?php if ($message): ?>
        <div class="alert <?php echo htmlspecialchars($message_type); ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <?php if (count($teachers) === 0): ?>
        <div class="alert">No teachers found.</div>
      <?php else: ?>
        <form method="POST" class="form-grid" onsubmit="return confirm('Are you sure you want to remove this teacher?');">
          <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

          <div class="field">
            <label>Search Teacher</label>
            <input
              type="text"
              id="teacherSearch"
              placeholder="Type a name or email to filter..."
              autocomplete="off"
            >
            <small class="hint">Start typing to filter the dropdown list below.</small>
          </div>

          <div class="field">
            <label>Select Teacher</label>
            <select name="teacher_id" id="teacherSelect" required>
              <?php foreach ($teachers as $t): ?>
                <option value="<?php echo (int)$t["teacher_id"]; ?>">
                  <?php echo htmlspecialchars($t["teacher_id"] . " - " . $t["full_name"] . " (" . $t["email"] . ")"); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <small class="hint">This action will permanently delete the selected teacher.</small>
          </div>

          <div class="form-actions">
            <button class="btn primary" type="submit">Remove Teacher</button>
            <a class="btn outline" href="admin_dashboard.php">Back to Dashboard</a>
          </div>
        </form>
      <?php endif; ?>

    </div>

    <div class="footer">Admin Tools · System Management · Security & Access Control</div>

  </div>
</div>

<script>
(function () {
  const searchInput = document.getElementById("teacherSearch");
  const selectEl = document.getElementById("teacherSelect");
  if (!searchInput || !selectEl) return;

  // Save original options
  const originalOptions = Array.from(selectEl.options).map(opt => ({
    value: opt.value,
    text: opt.text
  }));

  function renderOptions(filtered) {
    const currentValue = selectEl.value;

    selectEl.innerHTML = "";
    filtered.forEach(item => {
      const opt = document.createElement("option");
      opt.value = item.value;
      opt.textContent = item.text;
      selectEl.appendChild(opt);
    });

    // Try to keep previous selection if still exists
    const stillExists = Array.from(selectEl.options).some(o => o.value === currentValue);
    if (stillExists) selectEl.value = currentValue;
  }

  function filterOptions() {
    const q = searchInput.value.trim().toLowerCase();

    if (q === "") {
      renderOptions(originalOptions);
      return;
    }

    const filtered = originalOptions.filter(item =>
      item.text.toLowerCase().includes(q)
    );

    // If nothing matches, show an empty placeholder option
    if (filtered.length === 0) {
      selectEl.innerHTML = "";
      const opt = document.createElement("option");
      opt.value = "";
      opt.textContent = "No matches found";
      opt.disabled = true;
      opt.selected = true;
      selectEl.appendChild(opt);
      return;
    }

    renderOptions(filtered);
  }

  searchInput.addEventListener("input", filterOptions);
})();
</script>

</body>
</html>