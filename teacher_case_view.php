<?php
require_once "teacher_ui.php";
teacher_require_login();

$case_id = trim($_GET["case_id"] ?? "");

teacher_header("", "View Case | SBMS");

$case = null;
$evidence = [];

if ($case_id !== "") {
  $stmt = $conn->prepare("
    SELECT
      br.report_id, br.case_id, br.incident_type, br.severity, br.location,
      br.occurrence_datetime, br.description, br.status, br.submitted_at, br.is_anonymous,
      CASE WHEN br.is_anonymous = 1 THEN 'Anonymous'
           ELSE CONCAT(s.first_name,' ',s.last_name) END AS student_name
    FROM bullying_reports br
    LEFT JOIN student s ON s.student_id = br.owner_student_id
    WHERE br.case_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("s", $case_id);
  $stmt->execute();
  $case = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($case) {
    $stmt = $conn->prepare("SELECT file_name, original_name FROM report_evidence WHERE report_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $case["report_id"]);
    $stmt->execute();
    $evidence = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
  }
}
?>
<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>View Detailed Case</h1>
      <p>Enter a Case ID to view incident details and evidence.</p>

      <form method="GET" class="field">
        <label>Case ID</label>
        <input name="case_id" value="<?php echo e($case_id); ?>" placeholder="Example: SBMS-ABC123" />
        <div class="card-actions">
          <button class="btn primary" type="submit">Search</button>
          <a class="btn outline" href="teacher_submitted.php">Back to List</a>
        </div>
      </form>
    </div>

    <?php if ($case_id !== "" && !$case): ?>
      <div class="alert error" style="margin-top:14px;">Case not found.</div>
    <?php endif; ?>

    <?php if ($case): ?>
      <div class="card wide">
        <h3>Case Summary</h3>
        <p><b>Case ID:</b> <?php echo e($case["case_id"]); ?></p>
        <p><b>Student:</b> <?php echo e($case["student_name"]); ?></p>
        <p><b>Incident Type:</b> <?php echo e($case["incident_type"]); ?></p>
        <p><b>Severity:</b> <?php echo e($case["severity"]); ?></p>
        <p><b>Location:</b> <?php echo e($case["location"]); ?></p>
        <p><b>Date/Time:</b> <?php echo e($case["occurrence_datetime"]); ?></p>
        <p><b>Status:</b> <?php echo e($case["status"]); ?></p>

        <div class="field">
          <label>Description</label>
          <textarea readonly><?php echo e($case["description"]); ?></textarea>
        </div>

        <div class="card-actions">
          <a class="btn outline" href="teacher_update_status.php?case_id=<?php echo urlencode($case["case_id"]); ?>">Update Status</a>
          <a class="btn outline" href="teacher_reminders.php?case_id=<?php echo urlencode($case["case_id"]); ?>">Add Reminder</a>
        </div>
      </div>

      <div class="card wide">
        <h3>Evidence</h3>
        <?php if (count($evidence) === 0): ?>
          <p style="color:#64748b; margin-top:6px;">No evidence uploaded for this case.</p>
        <?php else: ?>
          <div class="evidence-grid">
            <?php foreach ($evidence as $ev): ?>
              <a href="<?php echo "uploads/" . e($ev["file_name"]); ?>" target="_blank">
                <img src="<?php echo "uploads/" . e($ev["file_name"]); ?>" alt="<?php echo e($ev["original_name"]); ?>">
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php teacher_footer(); ?>