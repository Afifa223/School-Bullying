<?php
require_once "teacher_ui.php";
teacher_require_login();
teacher_header("filters", "Filter Cases | SBMS");

$severity = trim($_GET["severity"] ?? "");
$type     = trim($_GET["type"] ?? "");
$status   = trim($_GET["status"] ?? "");
$location = trim($_GET["location"] ?? "");

$where = [];
$params = [];
$types = "";

if ($severity !== "") { $where[] = "br.severity = ?"; $params[] = $severity; $types .= "s"; }
if ($type !== "")     { $where[] = "br.incident_type = ?"; $params[] = $type; $types .= "s"; }
if ($status !== "")   { $where[] = "br.status = ?"; $params[] = $status; $types .= "s"; }
if ($location !== "") { $where[] = "br.location LIKE ?"; $params[] = "%".$location."%"; $types .= "s"; }

$sql = "
  SELECT br.case_id, br.incident_type, br.severity, br.location, br.status, br.submitted_at
  FROM bullying_reports br
";
if (count($where) > 0) {
  $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY br.submitted_at DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
?>
<div class="hero">
  <div class="container">
    <div class="hero-card">
      <h1>Filter Cases</h1>
      <p>Filter by severity, incident type, location, and case status.</p>

      <form method="GET">
        <div class="field">
          <label>Severity</label>
          <select name="severity">
            <option value="">All</option>
            <?php foreach (["Low","Medium","High"] as $v): ?>
              <option value="<?php echo e($v); ?>" <?php echo $severity===$v ? "selected" : ""; ?>><?php echo e($v); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Incident Type</label>
          <select name="type">
            <option value="">All</option>
            <?php foreach (["Physical","Verbal","Cyberbullying","Social","Other"] as $v): ?>
              <option value="<?php echo e($v); ?>" <?php echo $type===$v ? "selected" : ""; ?>><?php echo e($v); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Status</label>
          <select name="status">
            <option value="">All</option>
            <?php foreach (["submitted","under-review","action-taken","resolved"] as $v): ?>
              <option value="<?php echo e($v); ?>" <?php echo $status===$v ? "selected" : ""; ?>><?php echo e($v); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="field">
          <label>Location (contains)</label>
          <input name="location" value="<?php echo e($location); ?>" placeholder="Example: cafeteria / hallway / class">
        </div>

        <div class="card-actions">
          <button class="btn primary" type="submit">Apply Filters</button>
          <a class="btn outline" href="teacher_filters.php">Reset</a>
        </div>
      </form>
    </div>

    <div class="card wide">
      <table class="table">
        <thead>
          <tr>
            <th>Case ID</th>
            <th>Type</th>
            <th>Severity</th>
            <th>Location</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php while ($row = $res->fetch_assoc()): ?>
          <tr>
            <td><?php echo e($row["case_id"]); ?></td>
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
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php teacher_footer(); ?>