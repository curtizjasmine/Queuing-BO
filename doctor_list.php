<?php
session_start();
// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // For testing purposes, dummy fallback
    $admin_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Admin User";
} else {
    $admin_name = $_SESSION['full_name'];
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "opd_db";

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}

$action_status = "";
$error_message = "";

// CRUD operations
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed");
    }

    /* ============================
       ADD DOCTOR
       ============================ */
    if (isset($_POST['add_doctor'])) {
        
        if (empty($_POST['username']) || empty($_POST['full_name']) || 
            empty($_POST['password']) || empty($_POST['department_id']) ||
            empty($_POST['schedule_day']) || empty($_POST['schedule_time'])) {
            
            $action_status = "error";
            $error_message = "All fields are required.";

        } else {
            $username = trim($_POST['username']);
            $full_name = trim($_POST['full_name']); 
            $password = $_POST['password'];
            $department_id = intval($_POST['department_id']);
            $schedule_day = trim($_POST['schedule_day']);
            $schedule_time = trim($_POST['schedule_time']);
            $role = "doctor";

            $check = $conn->prepare("SELECT id FROM tbl_users WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                $action_status = "error";
                $error_message = "Username already exists. Please choose another.";
                $check->close();
            } else {
                $check->close();
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO tbl_users (username, password, role, full_name, department_id, schedule_date, schedule_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");

                if ($stmt) {
                    $stmt->bind_param("ssssiss", $username, $password_hash, $role, $full_name, $department_id, $schedule_day, $schedule_time);
                    try {
                        if ($stmt->execute()) {
                            $action_status = "added";
                        } else {
                            $action_status = "error";
                            $error_message = "Database Error: " . $stmt->error;
                        }
                    } catch (Exception $e) {
                        $action_status = "error";
                        $error_message = "System Error: " . $e->getMessage();
                    }
                    $stmt->close();
                } else {
                    $action_status = "error";
                    $error_message = "Query Preparation Failed: " . $conn->error;
                }
            }
        }
    }

    /* ============================
       EDIT DOCTOR
       ============================ */
    if (isset($_POST['edit_doctor'])) {
        $id = intval($_POST['id']);
        $username = trim($_POST['username']);
        $full_name = trim($_POST['full_name']);
        $department_id = intval($_POST['department_id']);

        $stmt = $conn->prepare("UPDATE tbl_users SET username=?, full_name=?, department_id=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("ssii", $username, $full_name, $department_id, $id);
            if ($stmt->execute()) {
                $action_status = "updated";
            } else {
                $action_status = "error";
                $error_message = "Update failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    /* ============================
       DELETE DOCTOR
       ============================ */
    if (isset($_POST['delete_doctor'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM tbl_users WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $action_status = "deleted";
            } else {
                $action_status = "error";
                $error_message = "Delete failed: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch department list
$deptResult = $conn->query("SELECT id, name FROM tbl_department ORDER BY name");
$departments = $deptResult ? $deptResult->fetch_all(MYSQLI_ASSOC) : [];

/* ==================================================
   PAGINATION LOGIC (LIMIT 3)
   ================================================== */
$limit = 3; // Items per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// 1. Get Total Count
$countSql = "SELECT COUNT(*) as total FROM tbl_users WHERE role='doctor'";
$countResult = $conn->query($countSql);
$total_records = $countResult->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

// Adjust page if it exceeds total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

// 2. Calculate Offset
$offset = ($page - 1) * $limit;

// 3. Fetch Records with LIMIT and OFFSET
$sql = "
    SELECT d.*, dep.name AS department_name 
    FROM tbl_users d
    LEFT JOIN tbl_department dep ON dep.id = d.department_id
    WHERE role='doctor'
    ORDER BY d.id DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$doctors = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* ================================= */
    /* EXISTING LAYOUT STYLES           */
    /* ================================= */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%); font-family: 'Inter', 'Segoe UI', sans-serif; min-height: 100vh; }
    .header { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15); position: sticky; top: 0; z-index: 100; }
    .logo { font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px; }
    .header-right { display: flex; align-items: center; gap: 20px; font-size: 14px; }
    .header-right a { color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; transition: all 0.3s ease; }
    .header-right a:hover { background: rgba(255, 255, 255, 0.2); }
    .main-container { display: flex; min-height: calc(100vh - 70px); }
    .sidebar { width: 250px; background: white; padding: 30px 0; border-right: 1px solid #e5e7eb; overflow-y: auto; box-shadow: 2px 0 8px rgba(0, 0, 0, 0.04); }
    .sidebar .nav-link { color: #6b7280; padding: 12px 20px; border-left: 3px solid transparent; transition: all 0.3s ease; font-size: 14px; font-weight: 500; }
    .sidebar .nav-link:hover { color: #16a34a; background: #f3f4f6; border-left-color: #16a34a; }
    .sidebar .nav-link.active { color: #16a34a; background: #f0fdf4; border-left-color: #16a34a; }
    .sidebar-title { color: #9ca3af; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 12px 20px; letter-spacing: 0.5px; }
    .content { padding: 40px; flex: 1; overflow-y: auto; }
    .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .content h2 { font-size: 28px; font-weight: 700; color: #1f2937; margin: 0; }
    .btn-success { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); border: none; padding: 10px 24px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(22, 163, 74, 0.2); }
    .btn-success:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3); }
    .table-container { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); overflow: hidden; padding-bottom: 1px; }
    .table { margin: 0; }
    .table thead { background: #f9fafb; border-bottom: 2px solid #e5e7eb; }
    .table th { color: #6b7280; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; padding: 16px; border: none; }
    .table td { padding: 16px; color: #374151; border-color: #e5e7eb; vertical-align: middle; }
    .table tbody tr { transition: all 0.2s ease; }
    .table tbody tr:hover { background: #f9fafb; }
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
    .status-active { background: #dbeafe; color: #0c4a6e; }
    .btn-primary, .btn-danger { padding: 6px 12px; font-size: 13px; font-weight: 600; border: none; border-radius: 6px; transition: all 0.3s ease; }
    .btn-primary { background: #3b82f6; }
    .btn-primary:hover { background: #2563eb; transform: translateY(-1px); }
    .btn-danger { background: #ef4444; }
    .btn-danger:hover { background: #dc2626; transform: translateY(-1px); }
    .btn-sm { margin: 0 4px; }
    
    /* ================================= */
    /* NEW PAGINATION STYLES             */
    /* ================================= */
    .pagination-container {
        padding: 15px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
    }
    .page-link {
        color: #16a34a;
        border: 1px solid #e5e7eb;
        margin: 0 3px;
        border-radius: 6px;
    }
    .page-link:hover {
        background-color: #f0fdf4;
        color: #15803d;
        border-color: #16a34a;
    }
    .page-item.active .page-link {
        background-color: #16a34a;
        border-color: #16a34a;
        color: white;
    }
    .page-item.disabled .page-link {
        color: #9ca3af;
        background-color: #f9fafb;
        border-color: #e5e7eb;
    }

    /* ================================= */
    /* MODAL STYLES (UNCHANGED)          */
    /* ================================= */
    .modal-content { border: none; border-radius: 16px; box-shadow: 0 15px 50px rgba(0,0,0,0.15); overflow: hidden; }
    .modal-header { background: linear-gradient(135deg, #16a34a, #14532d); color: white; padding: 20px 25px; border-bottom: none; }
    .modal-title { font-weight: 700; letter-spacing: 0.5px; font-size: 1.2rem; }
    .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }
    .modal-body { padding: 30px 25px; background-color: #fff; }
    .input-group-text { background-color: #f0fdf4; border: 1px solid #d1d5db; border-right: none; color: #16a34a; border-radius: 8px 0 0 8px; min-width: 45px; justify-content: center; }
    .form-control, .form-select { border: 1px solid #d1d5db; border-left: none; border-radius: 0 8px 8px 0; padding: 10px 12px; font-size: 0.95rem; }
    .form-control:focus, .form-select:focus { border-color: #16a34a; box-shadow: none; border-left: 1px solid #16a34a; }
    .form-label { font-size: 0.85rem; font-weight: 600; color: #4b5563; margin-bottom: 6px; margin-left: 2px; }
    .modal-footer { background-color: #f9fafb; border-top: 1px solid #eee; padding: 15px 25px; }
    .btn-save { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; padding: 8px 24px; border-radius: 8px; font-weight: 600; }
    .btn-save:hover { color: white; transform: translateY(-1px); }

    @media (max-width: 768px) {
        .main-container { flex-direction: column; }
        .sidebar { width: 100%; padding: 0; border-right: none; border-bottom: 1px solid #e5e7eb; }
        .content { padding: 20px; }
        .table { font-size: 12px; }
        .content-header { flex-direction: column; gap: 15px; align-items: flex-start; }
    }
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="logo">
        <i class="bi bi-hospital"></i>
        OPD Admin Panel
    </div>
    <div class="header-right">
        <span><i class="bi bi-person-circle"></i> <?= htmlspecialchars($admin_name) ?></span>
        <a href="logout.php" title="Logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="main-container">

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-title">Menu</div>
    <ul class="nav flex-column">
        <li><a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    </ul>
    
    <div class="sidebar-title mt-4">Manage Users</div>
    <ul class="nav flex-column">
        <li><a href="doctor_list.php" class="nav-link active"><i class="bi bi-person-badge"></i> Doctors</a></li>
        <li><a href="users_list.php" class="nav-link"><i class="bi bi-people"></i> Users</a></li>
        <li><a href="department_list.php" class="nav-link"><i class="bi bi-diagram-3"></i> Departments</a></li>
    </ul>
</div>

<!-- CONTENT -->
<div class="content">
    <div class="content-header">
        <h2><i class="bi bi-person-badge"></i> Doctor Management</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
            <i class="bi bi-plus-lg"></i> Add Doctor
        </button>
    </div>

    <div class="table-container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-person"></i> Username</th>
                    <th><i class="bi bi-person-fill"></i> Full Name</th>
                    <th><i class="bi bi-diagram-3"></i> Department</th>
                    <th><i class="bi bi-calendar"></i> Schedule Day</th>
                    <th><i class="bi bi-clock"></i> Schedule Time</th>
                    <th><i class="bi bi-circle-fill"></i> Status</th>
                    <th><i class="bi bi-gear"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($doctors)): ?>
                <tr><td colspan="8" class="text-center py-4">No doctors found.</td></tr>
            <?php else: ?>
                <?php foreach ($doctors as $doc): ?>
                    <tr>
                        <td><strong><?= $doc['id'] ?></strong></td>
                        <td><?= htmlspecialchars($doc['username']) ?></td>
                        <td><?= htmlspecialchars($doc['full_name']) ?></td>
                        <td><?= htmlspecialchars($doc['department_name'] ?? "N/A") ?></td>
                        <td><?= htmlspecialchars($doc['schedule_date'] ?? "N/A") ?></td>
                        <td><?= htmlspecialchars($doc['schedule_time'] ?? "N/A") ?></td>
                        <td>
                            <span class="status-badge status-active">
                                <?= htmlspecialchars($doc['status']) ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm editBtn"
                                data-id="<?= $doc['id'] ?>"
                                data-username="<?= htmlspecialchars($doc['username']) ?>"
                                data-full_name="<?= htmlspecialchars($doc['full_name']) ?>"
                                data-department_id="<?= $doc['department_id'] ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>

                            <button class="btn btn-danger btn-sm" onclick="deleteDoctor(<?= $doc['id'] ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        
        <!-- PAGINATION CONTROLS -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <!-- PREVIOUS BUTTON -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- PAGE NUMBERS -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- NEXT BUTTON -->
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
    </div>
</div>
</div>

<!-- ========================================== -->
<!-- MODERN ADD DOCTOR MODAL                    -->
<!-- ========================================== -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" class="modal-content" accept-charset="UTF-8">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i> Add New Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="row g-3">
                    <!-- Username -->
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input name="username" class="form-control" placeholder="Login username" required>
                        </div>
                    </div>

                    <!-- Full Name -->
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                            <input name="full_name" class="form-control" placeholder="Dr. John Doe" required>
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="col-12">
                        <label class="form-label">Department</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hospital"></i></span>
                            <select name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Schedule Day -->
                    <div class="col-md-6">
                        <label class="form-label">Schedule Day</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-calendar-week"></i></span>
                            <select name="schedule_day" class="form-select" required>
                                <option value="">Select Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                    </div>

                    <!-- Schedule Time -->
                    <div class="col-md-6">
                        <label class="form-label">Schedule Time</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            <input name="schedule_time" type="time" class="form-control" required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="col-12">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input name="password" type="password" class="form-control" placeholder="Set password" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button name="add_doctor" class="btn btn-save">
                    <i class="bi bi-check-lg"></i> Add Doctor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODERN EDIT DOCTOR MODAL                   -->
<!-- ========================================== -->
<div class="modal fade" id="editDoctorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i> Edit Doctor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" id="edit_id" name="id">

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input id="edit_username" name="username" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-heading"></i></span>
                            <input id="edit_full_name" name="full_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Department</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-hospital"></i></span>
                            <select id="edit_department_id" name="department_id" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button name="edit_doctor" class="btn btn-save">
                    <i class="bi bi-check-lg"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Delete Function
function deleteDoctor(id) {
    Swal.fire({
        title: "Delete Doctor?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#ef4444",
        cancelButtonColor: "#6b7280",
        confirmButtonText: "Delete"
    }).then(res => {
        if (res.isConfirmed) {
            const form = document.createElement("form");
            form.method = "POST";
            form.innerHTML = `
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="delete_doctor">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Populate Edit Modal
document.querySelectorAll(".editBtn").forEach(btn => {
    btn.addEventListener("click", () => {
        document.getElementById("edit_id").value = btn.dataset.id;
        document.getElementById("edit_username").value = btn.dataset.username;
        document.getElementById("edit_full_name").value = btn.dataset.full_name;
        document.getElementById("edit_department_id").value = btn.dataset.department_id;

        new bootstrap.Modal(document.getElementById("editDoctorModal")).show();
    });
});

// SweetAlert Messages
<?php if ($action_status === "added") : ?>
    Swal.fire({
        title: "Success!",
        text: "Doctor added successfully!",
        icon: "success",
        timer: 2000
    });
<?php elseif ($action_status === "updated") : ?>
    Swal.fire({
        title: "Updated!",
        text: "Doctor updated successfully!",
        icon: "success",
        timer: 2000
    });
<?php elseif ($action_status === "deleted") : ?>
    Swal.fire({
        title: "Deleted!",
        text: "Doctor deleted successfully!",
        icon: "success",
        timer: 2000
    });
<?php elseif ($action_status === "error") : ?>
    Swal.fire({
        title: "Error!",
        text: "<?= addslashes($error_message) ?>",
        icon: "error"
    });
<?php endif; ?>
</script>

</body>
</html>