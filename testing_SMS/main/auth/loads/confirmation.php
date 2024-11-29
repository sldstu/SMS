<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Modal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmActionBtn" class="btn btn-danger">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let actionCallback = null; // Holds the callback for the confirm action
        let modal = document.getElementById("confirmationModal");
        let confirmButton = document.getElementById("confirmActionBtn");

        // Function to show the modal
        function showConfirmationModal(message, callback, confirmButtonClass = "btn-danger", confirmButtonText = "Confirm") {
            // Set the message
            document.getElementById("confirmationMessage").innerText = message;

            // Set callback
            actionCallback = callback;

            // Customize the confirm button (optional)
            confirmButton.className = `btn ${confirmButtonClass}`;
            confirmButton.innerText = confirmButtonText;

            // Show the modal
            let confirmationModal = new bootstrap.Modal(modal);
            confirmationModal.show();
        }

        // When the confirm button is clicked
        confirmButton.addEventListener("click", () => {
            if (actionCallback) {
                actionCallback(); // Execute the callback
            }

            // Hide the modal after confirmation
            let confirmationModal = bootstrap.Modal.getInstance(modal);
            confirmationModal.hide();

            // Clear the callback to avoid memory leaks or stale references
            actionCallback = null;
        });
    </script>
</body>

</html>
