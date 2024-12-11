const canvas = document.getElementById('myCanvas'); // Replace with your canvas ID
if (canvas) {
    const ctx = canvas.getContext('2d');
    // Your drawing code here
} else {
    console.error('Canvas element not found.');
}

try {
    localStorage.setItem('key', 'value');
} catch (error) {
    console.error('Error accessing storage:', error);
}


const sidebarToggle = document.querySelector("#sidebar-toggle");
sidebarToggle.addEventListener("click",function(){
    document.querySelector("#sidebar").classList.toggle("collapsed");
});

// document.querySelector(".theme-toggle").addEventListener("click",() => {
//     toggleLocalStorage();
//     toggleRootClass();
// });

// function toggleRootClass(){
//     const current = document.documentElement.getAttribute('data-bs-theme');
//     const inverted = current == 'dark' ? 'light' : 'dark';
//     document.documentElement.setAttribute('data-bs-theme',inverted);
// }

// function toggleLocalStorage(){
//     if(isLight()){
//         localStorage.removeItem("light");
//     }else{
//         localStorage.setItem("light","set");
//     }
// }

// function isLight(){
//     return localStorage.getItem("light");
// }

// if(isLight()){
//     toggleRootClass();
// }


$(document).ready(function () {
  function loadPageContent(url) {
      $.ajax({
          url: url + '&ajax=true',
          method: 'GET',
          beforeSend: function () {
              $('#content').html('<div class="text-center">Loading...</div>');
          },
          success: function (response) {
              $('#content').html(response);
              reinitializeScripts();
          },
          error: function () {
              alert('Error loading content.');
          }
      });
  }

  function reinitializeScripts() {
      // Reinitialize any necessary scripts for loaded content
  }

  $('.ajax-link').on('click', function (e) {
      e.preventDefault();
      const url = $(this).attr('href');
      history.pushState(null, '', url);
      loadPageContent(url);
  });

  window.onpopstate = function () {
      loadPageContent(location.href);
  };

  reinitializeScripts(); // Initialize on first load
});



  // Function to load analytics view
  function viewAnalytics() {
    $.ajax({
      type: "GET", // Use GET request
      url: "../main/roles/admin_/dashboard.php", // URL for the analytics view
      dataType: "html", // Expect HTML response
      success: function (response) {
        $(".content-page").html(response); // Load the response into the content area
        loadChart(); // Call function to load the chart
      },
    });
  }

  // Function to load a sales chart using Chart.js
  function loadChart() {
    const ctx = document.getElementById("salesChart").getContext("2d"); // Get context of the chart element
    const salesChart = new Chart(ctx, {
      type: "bar", // Set chart type to bar
      data: {
        labels: [
          "Jan",
          "Feb",
          "Mar",
          "Apr",
          "May",
          "Jun",
          "Jul",
          "Aug",
          "Sept",
          "Oct",
          "Nov",
          "Dec",
        ], // Monthly labels
        datasets: [
          {
            label: "Sales", // Label for the dataset
            data: [
              7000, 5500, 5000, 4000, 4500, 6500, 8200, 8500, 9200, 9600, 10000,
              9800,
            ], // Sales data
            backgroundColor: "#EE4C51", // Bar color
            borderColor: "#EE4C51", // Border color
            borderWidth: 1, // Border width
          },
        ],
      },
      options: {
        responsive: true, // Make chart responsive
        scales: {
          y: {
            beginAtZero: true, // Start y-axis at 0
            max: 10000, // Maximum value for y-axis
            ticks: {
              stepSize: 2000, // Set step size for y-axis ticks
            },
          },
        },
      },
    });
  }


