<?php
require_once "teacher_ui.php";
teacher_require_login();
teacher_header("submitted", "Submitted Reports | SBMS");

$sql = "
  SELECT
    br.report_id, br.case_id, br.incident_type, br.severity, br.location,
    br.occurrence_datetime, br.status, br.submitted_at, br.is_anonymous,
    CASE WHEN br.is_anonymous = 1 THEN 'Anonymous'
         ELSE CONCAT(s.first_name, ' ', s.last_name) END AS student_name,
    (SELECT COUNT(*) FROM report_evidence re WHERE re.report_id = br.report_id) AS evidence_count
  FROM bullying_reports br
  LEFT JOIN student s ON s.student_id = br.owner_student_id
  ORDER BY br.submitted_at DESC
";
$res = $conn->query($sql);
?>
<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Submitted Reports</h1>
      <p>All reports submitted by students.</p>
    </div>

    <div class="card wide">
      <table class="table">
        <thead>
          <tr>
            <th>Case ID</th>
            <th>Student</th>
            <th>Type</th>
            <th>Severity</th>
            <th>Location</th>
            <th>Status</th>
            <th>Evidence</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?php echo e($row["case_id"]); ?></td>
            <td><?php echo e($row["student_name"]); ?></td>
            <td><?php echo e($row["incident_type"]); ?></td>
            <td><?php echo e($row["severity"]); ?></td>
            <td><?php echo e($row["location"]); ?></td>
            <td><?php echo e($row["status"]); ?></td>
            <td><?php echo (int)$row["evidence_count"]; ?></td>
            <td>
              <a class="btn small outline" href="teacher_case_view.php?case_id=<?php echo urlencode($row["case_id"]); ?>">View</a>
              <a class="btn small outline" href="teacher_update_status.php?case_id=<?php echo urlencode($row["case_id"]); ?>">Update</a>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php teacher_footer(); ?>