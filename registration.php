<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "guest_db"; // change to your DB name
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle AJAX search
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM tbl_guest WHERE Name LIKE ? LIMIT 10");
    $searchTerm = "%".$search."%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $guests = [];
    while ($row = $result->fetch_assoc()) {
        $guests[] = $row;
    }
    echo json_encode($guests);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guest Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Search Guest</h2>

    <!-- Search Input -->
    <input type="text" id="search" onkeyup="searchGuest()" class="form-control" placeholder="Type guest name...">

    <!-- Suggestions -->
    <ul id="suggestions" class="list-group mt-2"></ul>
</div>

<!-- Modal -->
<div class="modal fade" id="guestModal" tabindex="-1" aria-labelledby="guestModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="guestModalLabel">Guest Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="guestInfo">
        <!-- Guest info will appear here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
function searchGuest() {
    let keyword = document.getElementById("search").value;

    if(keyword.length < 1){
        document.getElementById("suggestions").innerHTML = "";
        return;
    }

    fetch("?search=" + keyword)
        .then(response => response.json())
        .then(data => {
            let suggestions = document.getElementById("suggestions");
            suggestions.innerHTML = "";

            data.forEach(g => {
                let li = document.createElement("li");
                li.classList.add("list-group-item", "list-group-item-action");
                li.textContent = g.Name;
                li.style.cursor = "pointer";

                li.onclick = function() {
                    showGuestInfo(g);
                }

                suggestions.appendChild(li);
            });
        });
}

function showGuestInfo(guest){
    let info = `
        <p><strong>Guest ID:</strong> ${guest.guest_id}</p>
        <p><strong>Name:</strong> ${guest.Name}</p>
        <p><strong>Designation:</strong> ${guest.Designation}</p>
        <p><strong>Company:</strong> ${guest.Company}</p>
        <p><strong>Address:</strong> ${guest.Address}</p>
        <p><strong>Contacts:</strong> ${guest.Contacts}</p>
        <p><strong>Status:</strong> ${guest.status}</p>
        <p><strong>Image:</strong><br> <img src="uploads/${guest.image}" width="150" class="rounded"></p>
    `;
    document.getElementById("guestInfo").innerHTML = info;

    // Show modal
    var myModal = new bootstrap.Modal(document.getElementById('guestModal'));
    myModal.show();
}
</script>

</body>
</html>
