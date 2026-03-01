<?php
require_once "teacher_ui.php";
teacher_require_login();

teacher_header("dashboard", "Teacher Dashboard | SBMS");
?>
<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Teacher / Administrative Functionalities</h1>
      <p>
        Welcome, <b><?php echo e(teacher_name()); ?></b>.
        Review cases, view evidence, update status, and filter by severity/type/location.
      </p>
    </div>

    <div class="grid">
      <div class="card">
        <h3>Submitted Reports</h3>
        <p>All reports submitted by students.</p>
        <div class="card-actions">
          <a class="btn outline" href="teacher_submitted.php">Open</a>
        </div>
      </div>

      <div class="card">
        <h3>Urgent Cases</h3>
        <p>High severity cases needing quick action.</p>
        <div class="card-actions">
          <a class="btn outline" href="teacher_urgent.php">Open</a>
        </div>
      </div>

      <div class="card">
        <h3>Follow-up Reminders</h3>
        <p>Cases waiting for follow-up updates.</p>
        <div class="card-actions">
          <a class="btn outline" href="teacher_reminders.php">Open</a>
        </div>
      </div>
    </div>

    <div class="card wide">
      <h3>Case Review &amp; Management</h3>
      <p>View incident details, severity and location, evidence (images), and update case status.</p>
      <div class="card-actions">
        <a class="btn primary" href="teacher_case_view.php">View Detailed Case</a>
        <a class="btn outline" href="teacher_update_status.php">Update Status</a>
        <a class="btn outline" href="teacher_filters.php">Filter Cases</a>
      </div>
    </div>

    <div class="footer">Teacher tools · Severity / Type / Location / Status Filters · Privacy & Terms</div>
  </div>
</div>
<?php teacher_footer(); ?>