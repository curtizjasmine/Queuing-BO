<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_name = $_SESSION['full_name'];

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "opd_db";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Connection error: " . $conn->connect_error);

$action_status = "";

/* -----------------------------------
    FETCH DEPARTMENTS
-------------------------------------- */
$dept_query = $conn->query("SELECT * FROM tbl_department ORDER BY Name ASC");
$departments = $dept_query->fetch_all(MYSQLI_ASSOC);

/* -----------------------------------
    CRUD OPERATIONS
-------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* Add User */
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $department_id = $_POST['department_id'];
        // Capture the new Section input
        $section = $_POST['section']; 
        $role = 'user';

        // Updated Query to include 'section'
        $stmt = $conn->prepare("INSERT INTO tbl_users (username, password, role, full_name, department_id, job_assign, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("ssssis", $username, $password, $role, $full_name, $department_id, $section);
        
        if ($stmt->execute()) $action_status = "added";
        $stmt->close();
    }

    /* Edit User */
    if (isset($_POST['edit_user'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $department_id = $_POST['department_id'];
        // Capture the new Section input
        $section = $_POST['section'];

        // Updated Query to include 'section'
        $stmt = $conn->prepare("UPDATE tbl_users SET username=?, full_name=?, department_id=?, job_assign=? WHERE id=?");
        $stmt->bind_param("ssisi", $username, $full_name, $department_id, $section, $id);

        if ($stmt->execute()) $action_status = "updated";
        $stmt->close();
    }

    /* Delete User */
    if (isset($_POST['delete_user'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM tbl_users WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) $action_status = "deleted";
        $stmt->close();
    }
}

/* -----------------------------------
    FETCH ALL USERS
-------------------------------------- */
$result = $conn->query("
    SELECT u.*, d.Name AS dept_name
    FROM tbl_users u
    LEFT JOIN tbl_department d ON u.department_id = d.id
    WHERE u.role = 'user'
    ORDER BY u.id DESC
");
$users = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: #f4f6f9;
    font-family: 'Segoe UI';
}
.header {
    height: 70px;
    background: linear-gradient(90deg, #16a34a, #22c55e);
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.main-layout { display: flex; flex: 1; }

.sidebar {
    width: 250px;
    background: #ffffff;
    border-right: 1px solid #dee2e6;
    padding-top: 20px;
}
.sidebar .nav-link {
    font-weight: 500;
    color: #333;
}
.sidebar .nav-link:hover, .sidebar .nav-link.active {
    background: #e6f4ea;
    color: #16a34a;
    border-radius: 5px;
}
.content {
    flex: 1;
    padding: 30px;
}
</style>
</head>

<body>

<!-- Header -->
<div class="header">
    <div class="logo"><i class="bi bi-hospital"></i> OPD Admin Panel</div>
    <div class="admin-info">
        Welcome, <?= htmlspecialchars($admin_name) ?>
        <a href="logout.php" style="color:white; text-decoration:none;">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<!-- Layout -->
<div class="main-layout">

    <!-- Sidebar -->
    <div class="sidebar">
        <ul class="nav nav-pills flex-column mb-auto">
            <li><a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>

            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="collapse" href="#manageUsersDropdown">
                    <i class="bi bi-people"></i> Manage Users
                </a>
                <div class="collapse show" id="manageUsersDropdown">
                    <ul class="list-unstyled ms-3">
                        <li><a href="users_list.php" class="nav-link active"><i class="bi bi-person"></i> Users</a></li>
                        <li><a href="doctor_list.php" class="nav-link"><i class="bi bi-person-badge"></i> Doctors</a></li>
                        <li><a href="department_list.php" class="nav-link"><i class="bi bi-building"></i> Department</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content">
        <h2>User Management</h2>

        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-plus"></i> Add User
        </button>

        <table class="table table-bordered bg-white table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Section</th> <!-- Added Column -->
                    <th>Status</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= $user['dept_name'] ?: 'N/A' ?></td>
                    <td><span class="badge bg-info"><?= htmlspecialchars($user['job_assign'] ?? 'N/A') ?></span></td> <!-- Display Section -->
                    <td><?= $user['status'] ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm editBtn"
                            data-id="<?= $user['id'] ?>"
                            data-username="<?= htmlspecialchars($user['username']) ?>"
                            data-full_name="<?= htmlspecialchars($user['full_name']) ?>"
                            data-department="<?= $user['department_id'] ?>"
                            data-section="<?= htmlspecialchars($user['job_assign']) ?>"> <!-- Pass Section data -->
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $user['id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Department</label>
                    <select name="department_id" class="form-control" required>
                        <option disabled selected>Select Department</option>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- NEW INPUT DROPDOWN ADDED HERE -->
                <div class="mb-3">
                    <label>Section</label>
                    <select name="section" class="form-control" required>
                        <option value="" disabled selected>Select Section</option>
                        <option value="Triage">Triage</option>
                        <option value="Registration">Registration</option>
                        <option value="Payment">Payment</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" name="add_user" class="btn btn-success">Add User</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>

        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="id" id="edit_id">

                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Department</label>
                    <select name="department_id" id="edit_department_id" class="form-control" required>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- NEW INPUT DROPDOWN ADDED TO EDIT MODAL AS WELL -->
                <div class="mb-3">
                    <label>Section</label>
                    <select name="section" id="edit_section" class="form-control" required>
                        <option value="" disabled>Select Section</option>
                        <option value="Triage">Triage</option>
                        <option value="Registration">Registration</option>
                        <option value="Payment">Payment</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
/* Delete User */
function deleteUser(id) {
    Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, Delete"
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement("form");
            form.method = "POST";
            form.innerHTML = `<input type="hidden" name="id" value="${id}">
                              <input type="hidden" name="delete_user">`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/* Populate Edit Modal */
document.querySelectorAll(".editBtn").forEach(btn => {
    btn.addEventListener("click", function() {
        document.getElementById("edit_id").value = this.dataset.id;
        document.getElementById("edit_username").value = this.dataset.username;
        document.getElementById("edit_full_name").value = this.dataset.full_name;
        document.getElementById("edit_department_id").value = this.dataset.department;
        
        // Populate the Section dropdown
        document.getElementById("edit_section").value = this.dataset.section;

        new bootstrap.Modal(document.getElementById("editUserModal")).show();
    });
});

/* SweetAlert Notifications */
<?php if ($action_status === "added"): ?>
    Swal.fire("Success", "User added successfully!", "success");
<?php elseif ($action_status === "updated"): ?>
    Swal.fire("Success", "User updated successfully!", "success");
<?php elseif ($action_status === "deleted"): ?>
    Swal.fire("Deleted", "User deleted successfully!", "success");
<?php endif; ?>
</script>

</body>
</html>