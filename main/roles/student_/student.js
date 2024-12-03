$('#registrationForm').on('submit', function(e) {
    e.preventDefault(); // Prevent the default form submission

    var formData = new FormData(this);
    formData.append('ajax', true); // Flag to indicate this is an AJAX request

    $.ajax({
    url: 'sports_stud.php',
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function(response) {
        console.log(response); // Log the raw response
        var data = JSON.parse(response);
        if (data.status === 'success') {
            alert('Registration successful!');
            $('#registrationForm')[0].reset();
        } else {
            alert('Registration failed: ' + data.message);
        }
    },
    error: function(xhr, status, error) {
        console.error(xhr.responseText);
        alert('Error submitting the form: ' + error);
    }
});
});