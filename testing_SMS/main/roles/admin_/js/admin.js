$(document).ready(function() {
    // Intercept clicks on links with the class 'ajax-link'
    $('.ajax-link').on('click', function(e) {
        e.preventDefault(); // Prevent default anchor behavior (page refresh)

        const url = $(this).attr('href') + '&ajax=true'; // Add an 'ajax=true' query parameter

        // Fetch the content dynamically
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                $('#content').html(response); // Replace the #content container with the new content
                window.history.pushState(null, '', $(this).attr('href')); // Update the URL in the browser
            },
            error: function() {
                $('#content').html('<p>Error loading content. Please try again later.</p>');
            }
        });
    });

    // Handle browser navigation (back/forward buttons)
    $(window).on('popstate', function() {
        const url = window.location.href + '&ajax=true';
        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                $('#content').html(response);
            },
            error: function() {
                $('#content').html('<p>Error loading content. Please try again later.</p>');
            }
        });
    });
});