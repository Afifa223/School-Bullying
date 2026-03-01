<?php
require_once "teacher_ui.php";
teacher_require_login();

$case_id = trim($_GET["case_id"] ?? "");
$message = "";
$message_type = "";

$allowed_status = ["submitted", "under-review", "action-taken", "resolved"];

/**
 * Convert HTML datetime-local (YYYY-MM-DDTHH:MM or YYYY-MM-DDTHH:MM:SS)
 * to MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
 */
function dt_local_to_mysql(string $dt): string {
  $dt = trim($dt);
  if ($dt === "") return "";

  $dt = str_replace("T", " ", $dt);

  if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $dt)) {
    $dt .= ":00";
  }

  return $dt;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST["action"] ?? "";

  // ---------- UPDATE STATUS ----------
  if ($action === "update_status") {
    $case_id = trim($_POST["case_id"] ?? "");
    $new_status = trim($_POST["status"] ?? "");
    $teacher_note = trim($_POST["teacher_note"] ?? ""); // ✅ NEW

    if ($case_id === "" || !in_array($new_status, $allowed_status, true)) {
      $message = "Invalid Case ID or Status.";
      $message_type = "error";
    } else {
      // ✅ resolved_mark should be "resolved" only if status is resolved, otherwise NULL
      $resolved_status = ($new_status === "resolved") ? "resolved" : null;

      $stmt = $conn->prepare("
        UPDATE bullying_reports
        SET status = ?, teacher_note = ?, resolved_status = ?
        WHERE case_id = ?
      ");
      $stmt->bind_param("ssss", $new_status, $teacher_note, $resolved_status, $case_id);
      $stmt->execute();
      $stmt->close();

      $message = "Status updated successfully.";
      $message_type = "success";
    }
  }

  // ---------- SAVE REMINDER ----------
  if ($action === "save_reminder") {
    $case_id = trim($_POST["case_id"] ?? "");
    $followup_local = trim($_POST["followup_datetime"] ?? "");
    $note = trim($_POST["note"] ?? "");

    $followup_mysql = dt_local_to_mysql($followup_local);

    if ($case_id === "") {
      $message = "Case ID is required to save a reminder.";
      $message_type = "error";
    } elseif ($followup_mysql === "") {
      $message = "Please pick a follow-up date/time and press OK before saving.";
      $message_type = "error";
    } else {
      $stmt = $conn->prepare("SELECT report_id FROM bullying_reports WHERE case_id = ? LIMIT 1");
      $stmt->bind_param("s", $case_id);
      $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if (!$row) {
        $message = "Case not found. Please check the Case ID.";
        $message_type = "error";
      } else {
        $report_id = (int)$row["report_id"];
        $tid = teacher_id();

        $stmt = $conn->prepare("
          INSERT INTO followups (report_id, teacher_id, followup_datetime, note)
          VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $report_id, $tid, $followup_mysql, $note);
        $stmt->execute();
        $stmt->close();

        $message = "Reminder saved successfully. Check Reminders tab.";
        $message_type = "success";
      }
    }
  }
}

teacher_header("", "Update Status | SBMS");
?>

<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Update Case Status</h1>
      <p>Update status and optionally schedule a follow-up reminder.</p>

      <?php if ($message): ?>
        <div class="alert <?php echo e($message_type); ?>"><?php echo e($message); ?></div>
      <?php endif; ?>

      <!-- ===================== STATUS FORM ===================== -->
      <div class="card wide" style="margin-top:14px;">
        <h3>Update Status</h3>

        <form method="POST">
          <input type="hidden" name="action" value="update_status">

          <div class="field">
            <label>Case ID</label>
            <input name="case_id" value="<?php echo e($case_id); ?>" placeholder="Example: SBMS-ABC123" required>
          </div>

          <div class="field">
            <label>Status</label>
            <select name="status" required>
              <?php foreach ($allowed_status as $st): ?>
                <option value="<?php echo e($st); ?>"><?php echo e($st); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- ✅ NEW: Teacher note for status update -->
          <div class="field">
            <label>Teacher Note (optional)</label>
            <textarea name="teacher_note" rows="3"
              placeholder="Write notes for the student (shown in My Reports)..."></textarea>
          </div>

          <div class="card-actions">
            <button class="btn primary" type="submit">Update Status</button>
            <a class="btn outline" href="teacher_submitted.php">Back to List</a>
          </div>
        </form>
      </div>

      <!-- ===================== REMINDER FORM ===================== -->
      <div class="card wide" style="margin-top:14px;">
        <h3>Add Follow-up Reminder</h3>

        <form method="POST" id="reminderForm">
          <input type="hidden" name="action" value="save_reminder">

          <div class="field">
            <label>Case ID</label>
            <input name="case_id" value="<?php echo e($case_id); ?>" placeholder="Example: SBMS-ABC123" required>
          </div>

          <div class="field">
            <label>Follow-up Date/Time</label>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
              <input type="datetime-local" name="followup_datetime" id="followup_datetime">
              <button type="button" class="btn outline" id="okBtn">OK</button>
              <span id="okMsg" style="color:#64748b; font-weight:800;"></span>
            </div>
          </div>

          <div class="field">
            <label>Note (optional)</label>
            <input name="note" id="note" placeholder="Example: Check if student needs counselling / ask for evidence." disabled>
          </div>

          <div class="card-actions">
            <button class="btn primary" type="submit" id="saveReminderBtn" disabled>Save Reminder</button>
            <a class="btn outline" href="teacher_reminders.php">Open Reminders</a>
          </div>

          <p style="color:#64748b; margin-top:10px;">
            Tip: Select date/time, press <b>OK</b>, then write a note and click <b>Save Reminder</b>.
          </p>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
  const okBtn = document.getElementById("okBtn");
  const dt = document.getElementById("followup_datetime");
  const note = document.getElementById("note");
  const saveBtn = document.getElementById("saveReminderBtn");
  const okMsg = document.getElementById("okMsg");

  okBtn.addEventListener("click", () => {
    if (!dt.value) {
      okMsg.textContent = "Please select a date/time first.";
      okMsg.style.color = "#b91c1c";
      note.disabled = true;
      saveBtn.disabled = true;
      return;
    }

    okMsg.textContent = "Date/time confirmed ✅";
    okMsg.style.color = "#16a34a";
    note.disabled = false;
    saveBtn.disabled = false;
    note.focus();
  });
</script>

<?php teacher_footer(); ?>