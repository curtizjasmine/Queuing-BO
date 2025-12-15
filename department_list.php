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

/* =========================================================
   ADD DEPARTMENT
========================================================= */
if (isset($_POST['add_department'])) {

    $name = $_POST['name'];
    $schedule_day = $_POST['schedule_day'];
    $schedule_time = $_POST['schedule_time'];

    // Generate department_id format: YYYYNnn (ex: 2025001)
    $year = date("Y");

    $res = $conn->query("SELECT department_id FROM tbl_department ORDER BY id DESC LIMIT 1");
    $last = $res->fetch_assoc();

    if ($last) {
        $seq = intval(substr($last['department_id'], 4)) + 1;
    } else {
        $seq = 1;
    }

    $department_id = $year . str_pad($seq, 3, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO tbl_department (department_id, Name, schedule_day, schedule_time)
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $department_id, $name, $schedule_day, $schedule_time);
    if ($stmt->execute()) {
        $_SESSION['action_status'] = "added";
        header("Location: department_list.php");
        exit;
    }
    $stmt->close();
}

/* =========================================================
   EDIT DEPARTMENT
========================================================= */
if (isset($_POST['edit_department'])) {

    $id = $_POST['id'];
    $name = $_POST['name'];
    $schedule_day = $_POST['schedule_day'];
    $schedule_time = $_POST['schedule_time'];

    $stmt = $conn->prepare("UPDATE tbl_department SET Name=?, schedule_day=?, schedule_time=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $schedule_day, $schedule_time, $id);
    if ($stmt->execute()) {
        $_SESSION['action_status'] = "updated";
        header("Location: department_list.php");
        exit;
    }
    $stmt->close();
}

/* =========================================================
   DELETE DEPARTMENT
========================================================= */
if (isset($_POST['delete_department'])) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM tbl_department WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['action_status'] = "deleted";
        header("Location: department_list.php");
        exit;
    }
    $stmt->close();
}

// Get action status from session (if exists)
if (isset($_SESSION['action_status'])) {
    $action_status = $_SESSION['action_status'];
    unset($_SESSION['action_status']); // Clear it after retrieving
}

/* =========================================================
   FETCH DATA
========================================================= */
$result = $conn->query("SELECT * FROM tbl_department ORDER BY id DESC");
$departments = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Department Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f9;
}

/* Header */
.header {
    height: 70px;
    background: linear-gradient(90deg, #16a34a, #22c55e);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 30px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.header .logo {
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: 1px;
}
.header .admin-info a {
    color: #fff;
    text-decoration: none;
    margin-left: 10px;
}
.header .admin-info a:hover {
    text-decoration: underline;
}

/* Main layout */
.main-layout {
    display: flex;
    flex: 1;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background: #fff;
    color: #333;
    min-height: calc(100vh - 70px);
    position: sticky;
    top: 70px;
    padding-top: 20px;
    border-right: 1px solid #dee2e6;
}
.sidebar .nav-link {
    color: #333;
    font-weight: 500;
    transition: all 0.2s;
}
.sidebar .nav-link:hover, .sidebar .nav-link.active {
    background: #e6f4ea;
    border-radius: 5px;
    color: #16a34a;
}
.sidebar .nav-link i {
    margin-right: 8px;
}
.sidebar .collapse .nav-link {
    padding-left: 30px;
    font-size: 0.95rem;
}

/* Content */
.content {
    flex: 1;
    padding: 30px;
}
</style>
</head>
<body>

<!-- Header -->
<div class="header">
    <h4><i class="bi bi-hospital"></i> OPD Admin Panel</h4>
    <div>
        Welcome, <?= htmlspecialchars($admin_name) ?>
        <a href="logout.php" class="text-white ms-3"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="main-layout">
    <!-- Sidebar -->
  <div class="sidebar d-flex flex-column">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="collapse" data-bs-target="#manageUsersDropdown" aria-expanded="true">
                    <i class="bi bi-people"></i> Manage Users
                </a>
                <div class="collapse show" id="manageUsersDropdown">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="doctor_list.php" class="nav-link active"><i class="bi bi-person-badge"></i> Doctor</a></li>
                        <li><a href="users_list.php" class="nav-link"><i class="bi bi-person"></i> Users</a></li>
                         <li><a href="department_list.php" class="nav-link active"><i class="bi bi-building"></i> Department</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Department Management</h2>

        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
            <i class="bi bi-plus"></i> Add Department
        </button>

        <table class="table table-bordered table-striped bg-white">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department ID</th>
                    <th>Name</th>
                    <th>Schedule Day</th>
                    <th>Schedule Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $dep): ?>
                <tr>
                    <td><?= $dep['id'] ?></td>
                    <td><?= $dep['department_id'] ?></td>
                    <td><?= htmlspecialchars($dep['Name']) ?></td>
                    <td><?= htmlspecialchars($dep['schedule_day']) ?></td>
                    <td><?= htmlspecialchars($dep['schedule_time']) ?></td>
                    <td>
                        <button class="btn btn-primary btn-sm editBtn"
                            data-id="<?= $dep['id'] ?>"
                            data-department_id="<?= $dep['department_id'] ?>"
                            data-name="<?= htmlspecialchars($dep['Name']) ?>"
                            data-day="<?= htmlspecialchars($dep['schedule_day']) ?>"
                            data-time="<?= htmlspecialchars($dep['schedule_time']) ?>">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn btn-danger btn-sm" onclick="deleteDepartment(<?= $dep['id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addDepartmentModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Add Department</h5></div>
            <div class="modal-body">
                <label>Name</label>
                <input name="name" class="form-control mb-2" required>

                <label>Schedule Day</label>
                <input name="schedule_day" class="form-control mb-2" placeholder="MWF" required>

                <label>Schedule Time</label>
                <input type="text" name="schedule_time" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button name="add_department" class="btn btn-success">Add</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editDepartmentModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header"><h5>Edit Department</h5></div>

            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">

                <label>Department ID</label>
                <input id="edit_department_id" class="form-control mb-2" readonly>

                <label>Name</label>
                <input name="name" id="edit_name" class="form-control mb-2" required>

                <label>Schedule Day</label>
                <input name="schedule_day" id="edit_day" class="form-control mb-2" required>

                <label>Schedule Time</label>
                <input type="text" name="schedule_time" id="edit_time" class="form-control" required>
            </div>

            <div class="modal-footer">
                <button name="edit_department" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Delete
function deleteDepartment(id) {
    Swal.fire({
        title: "Are you sure?",
        icon: "warning",
        showCancelButton: true,
    }).then(res => {
        if (res.isConfirmed) {
            const f = document.createElement("form");
            f.method = "POST";
            f.innerHTML = `<input type="hidden" name="id" value="${id}">
                           <input type="hidden" name="delete_department">`;
            document.body.appendChild(f);
            f.submit();
        }
    });
}

// Edit button
document.querySelectorAll(".editBtn").forEach(btn => {  
    btn.addEventListener("click", () => {
        document.getElementById("edit_id").value = btn.dataset.id;
        document.getElementById("edit_department_id").value = btn.dataset.department_id;
        document.getElementById("edit_name").value = btn.dataset.name;
        document.getElementById("edit_day").value = btn.dataset.day;
        document.getElementById("edit_time").value = btn.dataset.time;

        new bootstrap.Modal(document.getElementById("editDepartmentModal")).show();
    });
});

// Alerts
<?php if ($action_status): ?>
Swal.fire("Success", "Action completed successfully!", "success");
<?php endif; ?>
</script>

</body>
</html>