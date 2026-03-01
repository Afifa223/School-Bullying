<?php
require_once "teacher_ui.php";
teacher_require_login();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

teacher_header("urgent", "Urgent Cases | SBMS");

try {
    // If your severity values are "High/Medium/Low", this works.
    // If your project stores different values, change the condition here.
    $stmt = $conn->prepare("
      SELECT
        br.case_id, br.incident_type, br.severity, br.location, br.status, br.submitted_at,
        CASE WHEN br.is_anonymous = 1 THEN 'Anonymous'
             ELSE CONCAT(s.first_name, ' ', s.last_name) END AS student_name
      FROM bullying_reports br
      LEFT JOIN student s ON s.student_id = br.owner_student_id
      WHERE LOWER(br.severity) = 'high'
      ORDER BY br.submitted_at DESC
    ");
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

} catch (mysqli_sql_exception $e) {
    echo '<div class="hero"><div class="container">';
    echo '<div class="alert error"><b>Urgent page error:</b> ' . e($e->getMessage()) . '</div>';
    echo '</div></div>';
    teacher_footer();
    exit;
}
?>

<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Urgent Cases</h1>
      <p>High severity cases needing quick action.</p>
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
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($res->num_rows === 0): ?>
          <tr><td colspan="7" style="color:#64748b;">No high severity cases found.</td></tr>
        <?php else: ?>
          <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
              <td><?php echo e($row["case_id"]); ?></td>
              <td><?php echo e($row["student_name"]); ?></td>
              <td><?php echo e($row["incident_type"]); ?></td>
              <td><?php echo e($row["severity"]); ?></td>
              <td><?php echo e($row["location"]); ?></td>
              <td><?php echo e($row["status"]); ?></td>
              <td>
                <a class="btn small outline" href="teacher_case_view.php?case_id=<?php echo urlencode($row["case_id"]); ?>">View</a>
                <a class="btn small outline" href="teacher_update_status.php?case_id=<?php echo urlencode($row["case_id"]); ?>">Update</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php teacher_footer(); ?>