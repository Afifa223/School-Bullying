<?php
require_once "teacher_ui.php";
teacher_require_login();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = "";
$message_type = "";

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $followup_id = (int)($_POST["followup_id"] ?? 0);
        if ($followup_id > 0) {
            $stmt = $conn->prepare("UPDATE followups SET is_done = 1 WHERE followup_id = ? AND teacher_id = ?");
            $tid = teacher_id();
            $stmt->bind_param("ii", $followup_id, $tid);
            $stmt->execute();
            $stmt->close();
            $message = "Reminder marked as done.";
            $message_type = "success";
        }
    }

    $tid = teacher_id();
    $stmt = $conn->prepare("
      SELECT
        f.followup_id, f.followup_datetime, f.note,
        br.case_id, br.incident_type, br.severity, br.status
      FROM followups f
      JOIN bullying_reports br ON br.report_id = f.report_id
      WHERE f.teacher_id = ? AND f.is_done = 0
      ORDER BY f.followup_datetime ASC
    ");
    $stmt->bind_param("i", $tid);
    $stmt->execute();
    $res = $stmt->get_result();
    $stmt->close();

} catch (mysqli_sql_exception $e) {
    teacher_header("reminders", "Reminders | SBMS");
    echo '<div class="hero"><div class="container">';
    echo '<div class="alert error"><b>Reminders page error:</b> ' . e($e->getMessage()) . '</div>';
    echo '</div></div>';
    teacher_footer();
    exit;
}

teacher_header("reminders", "Reminders | SBMS");
?>

<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Follow-up Reminders</h1>
      <p>Upcoming reminders assigned to you.</p>

      <?php if ($message): ?>
        <div class="alert <?php echo e($message_type); ?>"><?php echo e($message); ?></div>
      <?php endif; ?>
    </div>

    <div class="card wide">
      <table class="table">
        <thead>
          <tr>
            <th>Due</th>
            <th>Case ID</th>
            <th>Type</th>
            <th>Severity</th>
            <th>Status</th>
            <th>Note</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($res->num_rows === 0): ?>
          <tr><td colspan="7" style="color:#64748b;">No pending reminders.</td></tr>
        <?php else: ?>
          <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
              <td><?php echo e($row["followup_datetime"]); ?></td>
              <td><?php echo e($row["case_id"]); ?></td>
              <td><?php echo e($row["incident_type"]); ?></td>
              <td><?php echo e($row["severity"]); ?></td>
              <td><?php echo e($row["status"]); ?></td>
              <td><?php echo e($row["note"] ?? ""); ?></td>
              <td style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn small outline" href="teacher_case_view.php?case_id=<?php echo urlencode($row["case_id"]); ?>">View</a>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="followup_id" value="<?php echo (int)$row["followup_id"]; ?>">
                  <button class="btn small primary" type="submit">Done</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>

      <p style="color:#64748b; margin-top:10px;">
        Add reminders from “Update Status” page (optional reminder section).
      </p>
    </div>
  </div>
</div>

<?php teacher_footer(); ?>