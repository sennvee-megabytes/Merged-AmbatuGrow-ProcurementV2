<?php
require_once __DIR__ . '/db.php';

$db = get_db();
$user = current_user();

$pr_id = (int)($_GET['pr_id'] ?? 0);
if ($pr_id <= 0) {
    header('Location: create_requisition.php');
    exit;
}

// Load the requisition + requestor
$stmt = $db->prepare(
    'SELECT r.*, u.name AS requestor_name
     FROM requisitions r
     JOIN users u ON u.id = r.requestor_id
     WHERE r.id = ?'
);
$stmt->bind_param('i', $pr_id);
$stmt->execute();
$requisition = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$requisition) {
    http_response_code(404);
    die('Requisition not found.');
}

// Only valid approver workers
$approverOptions = $db->query(
    "SELECT id, name, role FROM users WHERE username IN ('sarah.jenkins', 'finance.manager', 'johny.papa') OR name IN ('Sarah Jenkins', 'Michael Finn', 'Johny Papa') ORDER BY CASE WHEN username='sarah.jenkins' OR name='Sarah Jenkins' THEN 1 WHEN username='finance.manager' OR name='Michael Finn' THEN 2 ELSE 3 END"
)->fetch_all(MYSQLI_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_route'])) {

    $approvalType = ($_POST['approval_type'] ?? 'sequential') === 'parallel' ? 'parallel' : 'sequential';
    $stepApprovers = $_POST['step_approver'] ?? []; // array of user ids, in step order
    $stepRoles     = $_POST['step_role'] ?? [];

    $steps = [];
    foreach ($stepApprovers as $i => $approverId) {
        $approverId = (int)$approverId;
        if ($approverId <= 0) continue;
        $steps[] = [
            'approver_id' => $approverId,
            'role_label'  => trim($stepRoles[$i] ?? ''),
        ];
    }

    if (empty($steps)) {
        $errors[] = 'Add at least one approver.';
    }

    if (empty($errors)) {
        $db->begin_transaction();
        try {
            // Clear any previously-defined steps for this PR (e.g. re-routing)
            $del = $db->prepare('DELETE FROM approval_steps WHERE requisition_id = ?');
            $del->bind_param('i', $pr_id);
            $del->execute();
            $del->close();

            $stepStmt = $db->prepare(
                'INSERT INTO approval_steps (requisition_id, step_number, approver_id, role_label, status)
                 VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($steps as $index => $step) {
                $stepNumber = $index + 1;
                // In sequential mode, only step 1 starts "active"; others wait their turn.
                // In parallel mode, everyone is "active" immediately.
                $status = ($approvalType === 'parallel' || $stepNumber === 1) ? 'active' : 'pending';

                $stepStmt->bind_param(
                    'iiiss',
                    $pr_id, $stepNumber, $step['approver_id'], $step['role_label'], $status
                );
                $stepStmt->execute();
            }
            $stepStmt->close();

            $update = $db->prepare(
                "UPDATE requisitions SET approval_type = ?, status = 'pending_approval' WHERE id = ?"
            );
            $update->bind_param('si', $approvalType, $pr_id);
            $update->execute();
            $update->close();

            $db->commit();

            header('Location: approval_queue.php?routed=' . $pr_id);
            exit;

        } catch (Throwable $e) {
            $db->rollback();
            $errors[] = 'Could not save workflow: ' . $e->getMessage();
        }
    }
}

// Default 3 blank steps in the UI if this is a first-time routing
$existingSteps = $db->prepare('SELECT * FROM approval_steps WHERE requisition_id = ? ORDER BY step_number');
$existingSteps->bind_param('i', $pr_id);
$existingSteps->execute();
$existingSteps = $existingSteps->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($existingSteps)) {
    $existingSteps = [
        ['step_number' => 1, 'approver_id' => '', 'role_label' => 'Manager approval'],
        ['step_number' => 2, 'approver_id' => '', 'role_label' => 'Department Head Approval'],
        ['step_number' => 3, 'approver_id' => '', 'role_label' => 'Finance Approval'],
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AMBATUGROW - Route for Approval</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    background: #eef0f3;
    color: #1f2430;
    display: flex;
    min-height: 100vh;
  }
  .sidebar {
    width: 210px; background: #2f5233; color: #fff;
    display: flex; flex-direction: column; padding: 24px 16px; flex-shrink: 0;
  }
  .logo-box { width: 56px; height: 56px; background: #fff; border-radius: 8px; margin: 0 auto 12px auto; }
  .brand-name { text-align: center; font-weight: 700; font-size: 14px; letter-spacing: 0.5px; }
  .brand-sub { text-align: center; font-size: 10px; letter-spacing: 1px; color: #cfe0cf; margin-bottom: 28px; }
  .nav { display: flex; flex-direction: column; gap: 4px; }
  .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 6px; font-size: 13px; color: #e3ebe3; cursor: pointer; }
  .nav-item.active { background: #24402a; color: #fff; font-weight: 600; }
  .nav-icon { width: 16px; height: 16px; display: inline-block; opacity: 0.9; }
  .nav-bottom { display: flex; flex-direction: column; gap: 4px; margin-top: auto; }
  .dots { text-align: center; color: #9db89d; margin: 16px 0; letter-spacing: 3px; }

  .main { flex: 1; padding: 28px 32px; display: flex; gap: 28px; }
  .content-col { flex: 1; max-width: 700px; }
  .breadcrumb { font-size: 13px; color: #6b7280; margin-bottom: 16px; }
  .breadcrumb a { color: #2563eb; text-decoration: none; }
  .breadcrumb .sep { margin: 0 6px; color: #9ca3af; }
  .page-title { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
  .page-sub { font-size: 13px; color: #6b7280; margin-bottom: 28px; }

  .section-title { font-size: 15px; font-weight: 700; margin-bottom: 2px; }
  .section-sub { font-size: 12px; color: #6b7280; margin-bottom: 20px; }

  .approval-flow { display: flex; gap: 40px; }
  .steps { flex: 1; }
  .step-block {
    border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px 16px; margin-bottom: 14px; background: #fff;
  }
  .step { display: flex; gap: 12px; margin-bottom: 10px; }
  .step-num {
    width: 26px; height: 26px; border-radius: 50%; background: #2563eb; color: #fff;
    font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .step-fields { flex: 1; display: flex; gap: 10px; }
  .step-fields select, .step-fields input {
    flex: 1; border: 1px solid #dcdfe4; border-radius: 6px; padding: 8px 10px; font-size: 12.5px;
  }
  .required-badge {
    background: #dbeafe; color: #2563eb; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px;
  }
  .remove-step { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 12px; }

  .add-approval-step { color: #2563eb; font-size: 13px; font-weight: 600; margin-top: 6px; cursor: pointer; background: none; border: none; }

  .additional-options { margin-top: 34px; }
  .additional-title { font-size: 14px; font-weight: 700; margin-bottom: 14px; }
  .radio-option { display: flex; gap: 10px; margin-bottom: 14px; align-items: flex-start; }
  .radio-option input { margin-top: 3px; }
  .radio-title { font-size: 13px; font-weight: 700; }
  .radio-desc { font-size: 12px; color: #9ca3af; }

  .side-panel { width: 300px; flex-shrink: 0; }
  .panel-card { background: #fff; border-radius: 10px; padding: 22px; margin-bottom: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
  .panel-title { font-size: 15px; font-weight: 700; margin-bottom: 22px; }

  .summary-title { font-size: 15px; font-weight: 700; margin-bottom: 18px; }
  .summary-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 14px; color: #374151; }
  .summary-row .label { color: #6b7280; }
  .summary-row.total { margin-top: 20px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-weight: 700; font-size: 14px; }
  .summary-row.total .value { font-size: 16px; }

  .footer-actions { display: flex; justify-content: flex-end; align-items: center; gap: 20px; margin-top: 8px; }
  .cancel-btn { font-size: 13px; color: #374151; font-weight: 600; cursor: pointer; text-decoration: none; }
  .submit-btn {
    background: #2563eb; color: #fff; font-size: 13px; font-weight: 600; padding: 10px 20px;
    border-radius: 20px; cursor: pointer; border: none;
  }
  .error-box { background: #fee2e2; color: #b91c1c; border-radius: 8px; padding: 12px 16px; font-size: 13px; margin-bottom: 16px; }
</style>
</head>
<body>

  <div class="sidebar">
    <div class="logo-box"></div>
    <div class="brand-name">AMBATUGROW</div>
    <div class="brand-sub">ERP SYSTEM</div>
    <div class="nav">
      <div class="nav-item active"><span class="nav-icon">▦</span> Order Management</div>
      <div class="nav-item"><span class="nav-icon">＋</span> Create PO</div>
      <div class="nav-item"><span class="nav-icon">▮▮</span> Tracking</div>
      <div class="nav-item"><span class="nav-icon">☑</span> Approvals</div>
    </div>
    <div class="dots">•••</div>
    <div class="nav-bottom">
      <div class="nav-item">⚙ Settings</div>
      <div class="nav-item">❓ Support</div>
    </div>
  </div>

  <div class="main">
    <div class="content-col">
      <div class="breadcrumb">
        <a href="create_requisition.php">Create requisiton</a>
        <span class="sep">></span>
        <span><?= htmlspecialchars($requisition['pr_code']) ?></span>
        <span class="sep">></span>
        <span>Route for Approval</span>
      </div>

      <div class="page-title">Route Requisition for Approval</div>
      <div class="page-sub">Select approvers and define the approval flow for this requisition</div>

      <?php if (!empty($errors)): ?>
        <div class="error-box">
          <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="section-title">Approval workflow</div>
      <div class="section-sub">Define the people who need to review and approve this requisition</div>

      <form method="POST" action="route.php?pr_id=<?= $pr_id ?>" id="routeForm">
        <div class="approval-flow">
          <div class="steps">
            <div id="stepsContainer">
              <?php foreach ($existingSteps as $i => $step): ?>
                <div class="step-block">
                  <div class="step">
                    <div class="step-num"><?= $i + 1 ?></div>
                    <div class="step-fields">
                      <input type="text" name="step_role[]" placeholder="Step label (e.g. Manager Approval)"
                             value="<?= htmlspecialchars($step['role_label'] ?? '') ?>">
                      <select name="step_approver[]">
                        <option value="">Select approver</option>
                        <?php foreach ($approverOptions as $opt): ?>
                          <option value="<?= $opt['id'] ?>" <?= (($step['approver_id'] ?? '') == $opt['id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($opt['name'] . ' — ' . $opt['role']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <button type="button" class="remove-step" onclick="removeStep(this)">✕</button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="add-approval-step" onclick="addStep()">+ Add Approval Step</button>

            <div class="additional-options">
              <div class="additional-title">Additional Options</div>
              <div class="radio-option">
                <input type="radio" name="approval_type" value="sequential"
                       <?= ($requisition['approval_type'] === 'sequential' ? 'checked' : '') ?> id="seqType">
                <label for="seqType">
                  <div class="radio-title">Sequential Type</div>
                  <div class="radio-desc">Approvers review one at a time in order</div>
                </label>
              </div>
              <div class="radio-option">
                <input type="radio" name="approval_type" value="parallel"
                       <?= ($requisition['approval_type'] === 'parallel' ? 'checked' : '') ?> id="parType">
                <label for="parType">
                  <div class="radio-title">Parallel</div>
                  <div class="radio-desc">Approvers review simultaneously</div>
                </label>
              </div>
            </div>
          </div>

          <div class="side-panel">
            <div class="panel-card">
              <div class="summary-title">Requisiton summary</div>
              <div class="summary-row"><span class="label">Requisiton ID</span><span><?= htmlspecialchars($requisition['pr_code']) ?></span></div>
              <div class="summary-row"><span class="label">Requestor</span><span><?= htmlspecialchars($requisition['requestor_name']) ?></span></div>
              <div class="summary-row"><span class="label">Department</span><span><?= htmlspecialchars($requisition['department']) ?></span></div>
              <div class="summary-row"><span class="label">Date created</span><span><?= date('F j, Y', strtotime($requisition['created_at'])) ?></span></div>
              <div class="summary-row total"><span class="label">Total Amount</span><span class="value"><?= format_money((float)$requisition['total']) ?></span></div>
            </div>

            <div class="footer-actions">
              <a href="create_requisition.php" class="cancel-btn">Cancel</a>
              <button type="submit" name="submit_route" class="submit-btn">Submit for approval</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

<script>
let stepCount = <?= count($existingSteps) ?>;
const approverOptionsHTML = `<?php
  echo '<option value="">Select approver</option>';
  foreach ($approverOptions as $opt) {
      echo '<option value="' . $opt['id'] . '">' . htmlspecialchars($opt['name'] . ' — ' . $opt['role']) . '</option>';
  }
?>`;

function addStep() {
  stepCount++;
  const container = document.getElementById('stepsContainer');
  const div = document.createElement('div');
  div.className = 'step-block';
  div.innerHTML = `
    <div class="step">
      <div class="step-num">${stepCount}</div>
      <div class="step-fields">
        <input type="text" name="step_role[]" placeholder="Step label (e.g. Finance Approval)">
        <select name="step_approver[]">${approverOptionsHTML}</select>
      </div>
      <button type="button" class="remove-step" onclick="removeStep(this)">✕</button>
    </div>
  `;
  container.appendChild(div);
}

function removeStep(btn) {
  btn.closest('.step-block').remove();
}
</script>
</body>
</html>
