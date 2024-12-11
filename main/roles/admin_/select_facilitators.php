<?php
require_once __DIR__ . '/../../database/database.class.php';
$conn = (new Database())->connect();

// Fetch users who can be facilitators (you might want to filter by role)
$query = $conn->prepare("SELECT user_id, username, first_name, last_name, role FROM users");
$query->execute();
$users = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="modal-content">
  <div class="modal-header">
    <h5 class="modal-title">Select Sport Facilitators</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>
  <div class="modal-body">
    <input type="text" id="facilitator_search" class="form-control mb-3" placeholder="Search facilitators...">

    <div class="table-responsive">
      <table id="facilitators_table" class="table table-hover">
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
                <input type="checkbox" class="facilitator-checkbox" value="<?= $user['user_id'] ?>"
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
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="bootstrap.Modal.getInstance(this.closest('.modal')).hide()">Close</button>
    <button type="button" class="btn btn-primary" id="confirm_facilitators">Confirm Selection</button>
</div>
</div>

<script>
  document.getElementById('facilitator_search').addEventListener('input', function(e) {
    const searchText = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#facilitators_table tbody tr');

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

    // Update visual display
    document.getElementById('selected_facilitators_list').innerHTML = 
        selectedFacilitators.map(f => `
            <span class="badge bg-primary me-2 mb-1">
                <i class="bi bi-person-fill me-1"></i>${f.name}
            </span>
        `).join('');

    // Add to form data
    const facilitatorInput = document.createElement('input');
    facilitatorInput.type = 'hidden';
    facilitatorInput.name = 'facilitators';
    facilitatorInput.value = JSON.stringify(selectedFacilitators.map(f => f.id));

    // Update form
    const form = document.getElementById('addSportForm');
    const existingInput = form.querySelector('input[name="facilitators"]');
    if (existingInput) existingInput.remove();
    form.appendChild(facilitatorInput);

    // Hide the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('facilitatorModal'));
    modal.hide();
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('select_facilitators_btn').addEventListener('click', function() {
        const facilitatorModal = new bootstrap.Modal(document.getElementById('facilitatorModal'));

        fetch('../main/roles/admin_/select_facilitators.php')
            .then(response => response.text())
            .then(html => {
                document.querySelector('#facilitatorModal .modal-content').innerHTML = html;
                facilitatorModal.show();

                // Add event listener after modal content is loaded
                document.getElementById('confirm_facilitators').addEventListener('click', function() {
                    const selectedFacilitators = [];
                    document.querySelectorAll('.facilitator-checkbox:checked').forEach(checkbox => {
                        selectedFacilitators.push({
                            id: checkbox.value,
                            name: checkbox.closest('tr').querySelector('td:nth-child(3)').textContent
                        });
                    });

                    const facilitatorsList = document.getElementById('selected_facilitators_list');
                    facilitatorsList.innerHTML = selectedFacilitators.map(f =>
                        `<span class="badge bg-primary me-1">${f.name}</span>`
                    ).join('');

                    // Add to form data
                    const facilitatorInput = document.createElement('input');
                    facilitatorInput.type = 'hidden';
                    facilitatorInput.name = 'facilitators';
                    facilitatorInput.value = JSON.stringify(selectedFacilitators.map(f => f.id));

                    // Update form
                    const form = document.getElementById('addSportForm');
                    const existingInput = form.querySelector('input[name="facilitators"]');
                    if (existingInput) existingInput.remove();
                    form.appendChild(facilitatorInput);

                    facilitatorModal.hide();
                });
            });
    });

    document.getElementById('addSportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../main/roles/admin_/sports.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addSportModal'));
                    modal.hide();
                    window.location.reload();
                }
            });
    });
});
</script>