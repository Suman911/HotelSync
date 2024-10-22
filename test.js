document.getElementById('fetchButton').addEventListener('click', function() {
    fetch("https://hotelsync.000.pe/api/dashboardcounts", {
        method: "GET"
    })
    .then(response => response.json()) // Parse JSON response
    .then(data => {
        // Display the result in the div
        document.getElementById('resultDiv').innerHTML = JSON.stringify(data, null, 2);
    })
    .catch(error => {
        // Handle any errors
        document.getElementById('resultDiv').innerHTML = "Error: " + error;
    });
});
