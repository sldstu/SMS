<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadSection(section) {
            const content = document.getElementById('content');
            content.innerHTML = '<div class="text-center my-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            let endpoint = '';
            switch (section) {
                case 'registered_sports':
                    endpoint = 'register.php?action=registered_sports';
                    break;
                case 'sports':
                    endpoint = 'register.php?action=sports';
                    break;
                case 'events':
                    endpoint = 'register.php?action=events';
                    break;
            }

            fetch(endpoint)
                .then(response => response.json())
                .then(data => {
                    if (section === 'registered_sports') {
                        content.innerHTML = renderRegisteredSports(data);
                    } else if (section === 'sports') {
                        content.innerHTML = renderSports(data);
                    } else if (section === 'events') {
                        content.innerHTML = renderEvents(data);
                    }
                })
                .catch(err => {
                    content.innerHTML = '<div class="alert alert-danger">Failed to load content.</div>';
                    console.error(err);
                });
        }

        function renderRegisteredSports(data) {
            let html = '<h2>Registered Sports</h2><table class="table table-bordered"><thead><tr><th>Sport</th><th>Status</th></tr></thead><tbody>';
            data.forEach(item => {
                html += `<tr><td>${item.sport_name}</td><td>${item.status}</td></tr>`;
            });
            html += '</tbody></table>';
            return html;
        }

        function renderSports(data) {
            let html = '<h2>Available Sports</h2><table class="table table-bordered"><thead><tr><th>Sport Name</th><th>Action</th></tr></thead><tbody>';
            data.forEach(item => {
                html += `<tr><td>${item.sport_name}</td><td><button class="btn btn-primary" onclick="showRegistrationModal(${item.sport_id}, '${item.sport_name}')">Register</button></td></tr>`;
            });
            html += '</tbody></table>';
            return html;
        }

        function renderEvents(data) {
            let html = '<h2>Upcoming Events</h2><table class="table table-bordered"><thead><tr><th>Event Name</th><th>Date</th></tr></thead><tbody>';
            data.forEach(item => {
                html += `<tr><td>${item.event_name}</td><td>${item.event_date}</td></tr>`;
            });
            html += '</tbody></table>';
            return html;
        }

        function showRegistrationModal(sport_id, sport_name) {
            document.getElementById('sport_id').value = sport_id;
            document.getElementById('modalSportName').textContent = sport_name;
            const modal = new bootstrap.Modal(document.getElementById('registrationModal'));
            modal.show();
        }

        function registerSport() {
            const form = new FormData(document.getElementById('register_form'));
            fetch('api.php?action=register_sport', {
                method: 'POST',
                body: form,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Registration successful!');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('registrationModal'));
                        modal.hide();
                        loadSection('registered_sports');
                    } else {
                        alert('Registration failed!');
                    }
                })
                .catch(error => {
                    alert('An error occurred while registering.');
                    console.error(error);
                });
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1>Student Dashboard</h1>
        <div class="btn-group mb-3">
            <button class="btn btn-outline-primary" onclick="loadSection('registered_sports')">Registered Sports</button>
            <button class="btn btn-outline-primary" onclick="loadSection('sports')">Sports</button>
            <button class="btn btn-outline-primary" onclick="loadSection('events')">Upcoming Events</button>
        </div>
        <div id="content"></div>
    </div>

    <!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrationModalLabel">Register for <span id="modalSportName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="register_form" enctype="multipart/form-data">
                    <input type="hidden" id="sport_id" name="sport_id">
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="contact_info" class="form-label">Contact Info</label>
                        <input type="text" class="form-control" id="contact_info" name="contact_info" required>
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" required>
                    </div>
                    <div class="mb-3">
                        <label for="height" class="form-label">Height (cm)</label>
                        <input type="number" class="form-control" id="height" name="height" required>
                    </div>
                    <div class="mb-3">
                        <label for="weight" class="form-label">Weight (kg)</label>
                        <input type="number" class="form-control" id="weight" name="weight" required>
                    </div>
                    <div class="mb-3">
                        <label for="sex" class="form-label">Sex</label>
                        <select class="form-select" id="sex" name="sex" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="course" class="form-label">Course</label>
                        <select class="form-select" id="course" name="course" required>
                            <option value="CS">CS</option>
                            <option value="IT">IT</option>
                            <option value="ACT">ACT</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="section" class="form-label">Section</label>
                        <input type="text" class="form-control" id="section" name="section">
                    </div>
                    <div class="mb-3">
                        <label for="medcert" class="form-label">Medical Certificate</label>
                        <input type="file" class="form-control" id="medcert" name="medcert" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <div class="mb-3">
                        <label for="photo_2x2" class="form-label">2x2 Photo</label>
                        <input type="file" class="form-control" id="photo_2x2" name="photo_2x2" accept=".jpg,.jpeg,.png" required>
                    </div>
                    <div class="mb-3">
                        <label for="cor_pic" class="form-label">Certificate of Registration (COR)</label>
                        <input type="file" class="form-control" id="cor_pic" name="cor_pic" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="registerSport()">Submit</button>
            </div>
        </div>
    </div>
</div>

<div>
    <a href="logout.php">LABAS </a>
</div>
</body>
</html>
zzz