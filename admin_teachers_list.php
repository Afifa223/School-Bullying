<?php
require_once "auth.php";
require_role("admin");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$teacher_id = (int)($_GET["teacher_id"] ?? 0);

/* Total teachers count */
$totalTeachers = 0;
$resCount = $conn->query("SELECT COUNT(*) AS total FROM teachers");
if ($countRow = $resCount->fetch_assoc()) {
    $totalTeachers = (int)$countRow["total"];
}

/* Fetch all teachers for dropdown */
$dropdown = [];
$resDrop = $conn->query("SELECT teacher_id, full_name, email FROM teachers ORDER BY teacher_id DESC");
while ($row = $resDrop->fetch_assoc()) {
    $dropdown[] = $row;
}

/* Filter table if selected */
if ($teacher_id > 0) {
    $stmt = $conn->prepare("SELECT teacher_id, full_name, email, created_at
                            FROM teachers
                            WHERE teacher_id = ?
                            ORDER BY teacher_id DESC");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT teacher_id, full_name, email, created_at
                            FROM teachers
                            ORDER BY teacher_id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Teachers List | SBMS</title>

<link rel="stylesheet" href="css/admin.css">
<link rel="stylesheet" href="css/admin_teachers_list.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
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
            <a href="admin_teachers_list.php" class="active">Teachers</a>
            <a class="btn outline" href="logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="hero">
    <div class="container">

        <div class="card teachers-card">
            <div class="form-head">
                <div>
                    <h2>Teachers List</h2>
                    <p class="muted">Browse all teacher accounts and quickly filter by teacher.</p>
                </div>
            </div>

            <div class="top-actions">
                <div class="count-box">
                    <span class="count-label">Total Teachers</span>
                    <span class="count-value"><?php echo $totalTeachers; ?></span>
                </div>

                <a href="add_teacher.php" class="add-btn">+ Add New Teacher</a>
            </div>

            <form method="GET" id="filterForm" class="search-row">
                <select name="teacher_id" id="teacherSelect">
                    <option value="0">All Teachers</option>
                    <?php foreach ($dropdown as $d): ?>
                        <option value="<?php echo (int)$d["teacher_id"]; ?>"
                            <?php echo ($teacher_id === (int)$d["teacher_id"]) ? "selected" : ""; ?>>
                            <?php echo htmlspecialchars($d["full_name"] . " - " . $d["email"]); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="helper-text">Search teacher name or email from the dropdown.</p>
            </form>

            <?php if ($result->num_rows > 0): ?>
                <div class="table-wrap">
                    <table class="teachers-table">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Teacher ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $serial = 1; ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $serial++; ?></td>
                                    <td><?php echo htmlspecialchars($row["teacher_id"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                                    <td><?php echo htmlspecialchars($row["created_at"]); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="add_teacher.php" class="table-btn">Add More</a>
                                            <a href="remove_teacher.php?teacher_id=<?php echo (int)$row["teacher_id"]; ?>"
                                               class="table-btn danger-btn"
                                               onclick="return confirm('Are you sure you want to remove this teacher?');">
                                               Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    No teachers found for the selected filter.
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">Admin tools · Teachers Directory</div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(function () {
    $("#teacherSelect").select2({
        placeholder: "Search teacher...",
        allowClear: true
    });

    $("#teacherSelect").on("change", function () {
        $("#filterForm").submit();
    });
});
</script>

</body>
</html>