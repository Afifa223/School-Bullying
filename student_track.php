<?php
require_once "auth.php";
require_role("student");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require "db.php";

$student_id = current_user_id();

/**
 * Status pipeline (teacher will update later)
 * Keep these keys stable so teacher dashboard can update them.
 */
function status_steps(): array {
  return [
    "submitted"   => "Submitted",
    "seen"        => "Seen by Teacher",
    "under_review"=> "Under Review",
    "resolved"    => "Resolved",
  ];
}

/**
 * Make a human friendly title from report fields
 */
function generate_title(array $r): string {
  $incident = ucfirst(str_replace("_", " ", $r["incident_type"] ?? "incident"));
  $sev = ucfirst($r["severity"] ?? "");
  $loc = trim($r["location"] ?? "");
  $base = trim("$sev $incident");
  if ($loc !== "") $base .= " • " . $loc;
  return $base !== "" ? $base : "Bullying Report";
}

/**
 * "X days ago" from submitted_at
 */
function days_ago_label(?string $submitted_at): string {
  if (!$submitted_at) return "—";
  try {
    $sub = new DateTime($submitted_at);
    $now = new DateTime();
    $diff = $now->diff($sub);

    if ($diff->days === 0) return "Today";
    if ($diff->days === 1) return "1 day ago";
    return $diff->days . " days ago";
  } catch (Exception $e) {
    return "—";
  }
}

/**
 * Progress percent based on step index
 */
function progress_percent(string $status): int {
  $keys = array_keys(status_steps());
  $idx = array_search($status, $keys, true);
  if ($idx === false) $idx = 0;
  $max = max(count($keys) - 1, 1);
  return (int)round(($idx / $max) * 100);
}

/**
 * AJAX: return report details JSON for modal
 */
if (isset($_GET["ajax"]) && $_GET["ajax"] === "1") {
  header("Content-Type: application/json; charset=utf-8");

  $case_id = trim($_GET["case_id"] ?? "");
  if ($case_id === "") {
    echo json_encode(["ok" => false, "error" => "Missing Case ID"]);
    exit;
  }

  // Only allow the owner_student_id (the logged-in student) to view the report
  $stmt = $conn->prepare("SELECT report_id, case_id, incident_type, severity, occurrence_datetime, location, description, status, submitted_at
                          FROM bullying_reports
                          WHERE case_id = ? AND owner_student_id = ?
                          LIMIT 1");
  $stmt->bind_param("si", $case_id, $student_id);
  $stmt->execute();
  $case = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$case) {
    echo json_encode(["ok" => false, "error" => "Report not found"]);
    exit;
  }

  $ev = $conn->prepare("SELECT file_name, original_name, uploaded_at
                        FROM report_evidence
                        WHERE report_id = ?
                        ORDER BY evidence_id DESC");
  $ev->bind_param("i", $case["report_id"]);
  $ev->execute();
  $evidence = $ev->get_result()->fetch_all(MYSQLI_ASSOC);
  $ev->close();

  $status = $case["status"] ?: "submitted";

  echo json_encode([
    "ok" => true,
    "data" => [
      "title" => generate_title($case),
      "case_id" => $case["case_id"],
      "status" => $status,
      "status_label" => status_steps()[$status] ?? "Submitted",
      "progress_percent" => progress_percent($status),
      "incident_type" => $case["incident_type"],
      "severity" => $case["severity"],
      "occurrence_datetime" => $case["occurrence_datetime"],
      "location" => $case["location"],
      "description" => $case["description"],
      "submitted_at" => $case["submitted_at"],
      "days_ago" => days_ago_label($case["submitted_at"]),
      "evidence" => array_map(function($e){
        return [
          "original_name" => $e["original_name"],
          "uploaded_at" => $e["uploaded_at"],
          "url" => "uploads/" . rawurlencode($e["file_name"]),
        ];
      }, $evidence),
      "steps" => status_steps(),
    ]
  ]);
  exit;
}

/**
 * Normal page: load ALL reports for this student (owner_student_id)
 */
$stmt = $conn->prepare("SELECT report_id, case_id, incident_type, severity, occurrence_datetime, location, description, status, submitted_at
                        FROM bullying_reports
                        WHERE owner_student_id = ?
                        ORDER BY submitted_at DESC, report_id DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Track Reports | SBMS</title>
  <link rel="stylesheet" href="css/student_track.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
</head>
<body>

<!-- NAV -->
<div class="nav">
  <div class="container nav-inner">
    <div class="brand">
      <span class="badge"></span>
      <span>SBMS Student Panel</span>
    </div>
    <div class="links">
      <a href="student_dashboard.php">Home</a>
      <a href="student_report.php">Start Report</a>
      <a href="student_track.php" class="active">Track</a>
      <a href="my_report.php">My Reports</a>
      <a href="help_resources.php">Help & Resources</a>
      <a class="btn outline" href="logout.php">Logout</a>
    </div>
  </div>
</div>

<!-- BODY -->
<div class="hero">
  <div class="container">
    <div class="page-head">
      <div>
        <h1>Your Submitted Reports</h1>
        <p class="muted">All your reports are listed here. Click <b>Track</b> to view details and progress.</p>
      </div>
      
    </div>

    <?php if (!$reports): ?>
      <div class="empty">
        <h3>No reports yet</h3>
        <p class="muted">When you submit a bullying report, it will appear here with a Case ID.</p>
        <a class="btn outline" href="student_report.php">Start a Report</a>
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($reports as $r): 
          $title = generate_title($r);
          $status = $r["status"] ?: "submitted";
          $status_label = status_steps()[$status] ?? "Submitted";
          $days = days_ago_label($r["submitted_at"]);
        ?>
          <div class="report-card">
            <div class="card-top">
              <div class="title-wrap">
                <div class="report-title"><?php echo htmlspecialchars($title); ?></div>
                <div class="meta">
                  <span class="pill">Case ID: <b><?php echo htmlspecialchars($r["case_id"]); ?></b></span>
                  <span class="pill"><?php echo htmlspecialchars($days); ?></span>
                </div>
              </div>

              <div class="status-badge status-<?php echo htmlspecialchars($status); ?>">
                <?php echo htmlspecialchars($status_label); ?>
              </div>
            </div>

            <div class="card-mid">
              <div class="mini">
                <div class="k">Type</div>
                <div class="v"><?php echo htmlspecialchars($r["incident_type"]); ?></div>
              </div>
              <div class="mini">
                <div class="k">Severity</div>
                <div class="v"><?php echo htmlspecialchars($r["severity"]); ?></div>
              </div>
              <div class="mini">
                <div class="k">Location</div>
                <div class="v"><?php echo htmlspecialchars($r["location"]); ?></div>
              </div>
            </div>

            <div class="card-actions">
              <button class="btn outline track-btn"
                      data-case-id="<?php echo htmlspecialchars($r["case_id"]); ?>">
                Track
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="footer">Privacy & Safety · SBMS</div>
  </div>
</div>

<!-- MODAL -->
<div class="modal" id="trackModal" aria-hidden="true">
  <div class="modal-backdrop" data-close="1"></div>

  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-head">
      <div>
        <div class="modal-title" id="modalTitle">Loading…</div>
        <div class="modal-sub muted" id="modalSub"></div>
      </div>
      <button class="icon-btn" id="modalClose" aria-label="Close">✕</button>
    </div>

    <div class="modal-body">
      <div class="two-col">
        <div class="panel">
          <h3>Progress</h3>

          <div class="progress-row">
            <div class="progress-bar">
              <div class="progress-fill" id="progressFill" style="width:0%"></div>
            </div>
            <div class="progress-pct" id="progressPct">0%</div>
          </div>

          <div class="timeline" id="timeline"></div>
        </div>

        <div class="panel">
          <h3>Case Details</h3>
          <div class="detail" id="detailBox">
            <div class="muted">Loading case details…</div>
          </div>

          <h3 style="margin-top:14px;">Evidence</h3>
          <div class="evidence" id="evidenceBox">
            <div class="muted">—</div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-foot">
      <button class="btn outline" id="closeBottom">Close</button>
    </div>
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('trackModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalSub = document.getElementById('modalSub');
  const detailBox = document.getElementById('detailBox');
  const evidenceBox = document.getElementById('evidenceBox');
  const timeline = document.getElementById('timeline');
  const progressFill = document.getElementById('progressFill');
  const progressPct = document.getElementById('progressPct');

  function openModal(){
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('no-scroll');
  }
  function closeModal(){
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('no-scroll');
  }

  document.addEventListener('click', (e) => {
    const t = e.target;

    if (t && t.matches('.track-btn')) {
      const caseId = t.getAttribute('data-case-id');
      loadCase(caseId);
      openModal();
    }

    if (t && t.getAttribute('data-close') === '1') closeModal();
  });

  document.getElementById('modalClose').addEventListener('click', closeModal);
  document.getElementById('closeBottom').addEventListener('click', closeModal);

  async function loadCase(caseId){
    modalTitle.textContent = 'Loading…';
    modalSub.textContent = '';
    detailBox.innerHTML = '<div class="muted">Loading case details…</div>';
    evidenceBox.innerHTML = '<div class="muted">Loading…</div>';
    timeline.innerHTML = '';
    progressFill.style.width = '0%';
    progressPct.textContent = '0%';

    try{
      const res = await fetch(`student_track.php?ajax=1&case_id=${encodeURIComponent(caseId)}`, {
        credentials: 'same-origin'
      });
      const json = await res.json();

      if(!json.ok){
        modalTitle.textContent = 'Error';
        detailBox.innerHTML = `<div class="alert error">${escapeHtml(json.error || 'Something went wrong')}</div>`;
        evidenceBox.innerHTML = `<div class="muted">—</div>`;
        return;
      }

      const d = json.data;
      modalTitle.textContent = d.title;
      modalSub.textContent = `Case ID: ${d.case_id} • ${d.days_ago} • Status: ${d.status_label}`;

      // Details
      detailBox.innerHTML = `
        <div class="kv"><div class="k">Incident Type</div><div class="v">${escapeHtml(d.incident_type)}</div></div>
        <div class="kv"><div class="k">Severity</div><div class="v">${escapeHtml(d.severity)}</div></div>
        <div class="kv"><div class="k">Occurrence</div><div class="v">${escapeHtml(d.occurrence_datetime || '—')}</div></div>
        <div class="kv"><div class="k">Location</div><div class="v">${escapeHtml(d.location || '—')}</div></div>
        <div class="kv"><div class="k">Submitted At</div><div class="v">${escapeHtml(d.submitted_at || '—')}</div></div>
        <div class="kv"><div class="k">Description</div><div class="v pre">${escapeHtml(d.description || '—')}</div></div>
      `;

      // Evidence
      if(!d.evidence || d.evidence.length === 0){
        evidenceBox.innerHTML = `<div class="muted">No evidence uploaded.</div>`;
      } else {
        evidenceBox.innerHTML = d.evidence.map(ev => `
          <div class="ev-item">
            <div class="ev-name">${escapeHtml(ev.original_name)}</div>
            <div class="ev-sub muted">Uploaded: ${escapeHtml(ev.uploaded_at || '—')}</div>
            <a class="btn tiny outline" href="${ev.url}" target="_blank" rel="noopener">View</a>
          </div>
        `).join('');
      }

      // Progress + timeline
      progressFill.style.width = `${d.progress_percent}%`;
      progressPct.textContent = `${d.progress_percent}%`;

      const keys = Object.keys(d.steps);
      const currentIndex = keys.indexOf(d.status);
      timeline.innerHTML = keys.map((k, i) => {
        const label = d.steps[k];
        const state =
          i < currentIndex ? 'done' :
          i === currentIndex ? 'active' : 'todo';

        return `
          <div class="t-row ${state}">
            <div class="t-dot"></div>
            <div class="t-content">
              <div class="t-title">${escapeHtml(label)}</div>
              <div class="t-sub muted">${timelineHint(k, d)}</div>
            </div>
          </div>
        `;
      }).join('');

    } catch(err){
      modalTitle.textContent = 'Error';
      detailBox.innerHTML = `<div class="alert error">Failed to load. Please try again.</div>`;
      evidenceBox.innerHTML = `<div class="muted">—</div>`;
    }
  }

  function timelineHint(statusKey, d){
    // simple hints (you can refine later)
    if(statusKey === 'submitted') return `Submitted • ${d.submitted_at || '—'}`;
    if(statusKey === 'seen') return `Waiting for teacher to view`;
    if(statusKey === 'under_review') return `Investigation in progress`;
    if(statusKey === 'resolved') return `Case closed`;
    return '';
  }

  function escapeHtml(str){
    return String(str ?? '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }
})();
</script>

</body>
</html>