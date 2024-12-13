<?php
require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

$type = $_GET['type'] ?? 'facilitator';
$role = $type === 'coach' ? 'coach' : 'facilitator';

// Fetch users based on role
$query = $conn->prepare("SELECT user_id, username, first_name, last_name, role FROM users WHERE role = :role");
$query->bindParam(':role', $role);
$query->execute();
$users = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-header">
  <h5 class="modal-title">Select <?= ucfirst($type) ?>s</h5>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
  <input type="text" id="<?= $type ?>_search" class="form-control mb-3" placeholder="Search <?= $type ?>s...">
  <div class="table-responsive">
    <table id="<?= $type ?>s_table" class="table table-hover">
      <thead>
        <tr>
          <th>Select</th>
          <th>Username</th>
          <th>Name</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td>
              <input type="checkbox" class="<?= $type ?>-checkbox" value="<?= $user['user_id'] ?>"
                data-name="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>">
            </td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
  <button type="button" class="btn btn-primary" id="confirm_facilitators">Confirm Selection</button>
</div>

<!-- Add this script at the bottom of select_facilitators.php -->
<script>
  // Search functionality
  document.getElementById('<?= $type ?>_search').addEventListener('input', function(e) {
    const searchText = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#<?= $type ?>s_table tbody tr');

    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(searchText) ? '' : 'none';
    });
  });


  document.getElementById('confirm_facilitators').addEventListener('click', function() {
    const selectedFacilitators = Array.from(document.querySelectorAll('.facilitator-checkbox:checked')).map(checkbox => ({
      id: checkbox.value,
      name: checkbox.dataset.name
    }));

    // Create or update badges container
    let badgesContainer = document.getElementById('selected-facilitators-badges');
    if (!badgesContainer) {
      badgesContainer = document.createElement('div');
      badgesContainer.id = 'selected-facilitators-badges';
      badgesContainer.className = 'mb-3';
      document.querySelector('#addSportForm .modal-body').appendChild(badgesContainer);
    }

    // Update badges display
    const badgesHtml = selectedFacilitators.map(facilitator => `
        <span class="badge bg-primary me-2 mb-1">
            ${facilitator.name}
        </span>
    `).join('');

    badgesContainer.innerHTML = badgesHtml || '<span class="text-muted">No facilitators selected</span>';

    // Store selected facilitators data
    document.getElementById('addSportForm').dataset.selectedFacilitators = JSON.stringify(selectedFacilitators);

    // Close the facilitators selection modal using the parent modal element
    const facilitatorsModal = this.closest('.modal');
    const bsModal = bootstrap.Modal.getInstance(facilitatorsModal);
    bsModal.hide();
  });
</script>