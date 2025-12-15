<?php
session_start();

// Session protection: only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$admin_name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

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
        background: linear-gradient(90deg, #16a34a, #22c55e); /* green gradient */
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
        background: #fff; /* white sidebar */
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
        background: #e6f4ea; /* light green hover */
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
    <div class="logo"><i class="bi bi-hospital"></i> OPD Admin Panel</div>
    <div class="admin-info">
        Welcome, <?php echo htmlspecialchars($admin_name); ?>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<!-- Main layout -->
<div class="main-layout">

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a href="admin_dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i> Dashboard</a>
            </li>

            <li class="nav-item mb-2">
                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#manageUsersDropdown" aria-expanded="false">
                    <i class="bi bi-people"></i> Manage Users
                </a>
                <div class="collapse" id="manageUsersDropdown">
                    <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                        <li><a href="doctor_list.php" class="nav-link"><i class="bi bi-person-badge"></i> Doctor</a></li>
                        <li><a href="users_list.php" class="nav-link"><i class="bi bi-person"></i> Users</a></li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Admin Dashboard</h2>
        <p>Welcome to the Admin Dashboard. Use the sidebar to manage users and navigate the system.</p>
    </div>

</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
