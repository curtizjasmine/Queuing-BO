<?php
session_start();

// Initialize message variable
$message = "";

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "opd_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection error: " . $conn->connect_error);
}

// Login check
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['password'])) {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare SQL query
    $sql = "SELECT * FROM tbl_users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL prepare error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password hash
 if (password_verify($password, $row['password'])) {
    // Successful login, store session variables
    $_SESSION['user_id'] = $row['id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];
    $_SESSION['full_name'] = $row['full_name'];
    $_SESSION['pwd'] = $row['password'];
    
    // Store job_assign from database to jobassign in session
    $_SESSION['jobassign'] = isset($row['job_assign']) ? $row['job_assign'] : '';
    
    // Redirect based on role and job assignment
    if ($row['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit;
    } elseif ($row['role'] === 'doctor') {
        header("Location: doctor_dashboard.php");
        exit;
    } elseif ($row['role'] === 'user') {
        $job_assign = isset($row['job_assign']) ? $row['job_assign'] : '';
        
        if ($job_assign === 'Triage') {
            header("Location: test.php");
            exit;
        } elseif ($job_assign === 'Registration') {
            header("Location: registration_dashboard.php");
            exit;
        } else {
            // Handle case where user role is 'user' but job assignment is not recognized
            $message = "<div class='alert alert-warning text-center'>No valid job assignment found.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning text-center'>Role not recognized.</div>";
    }
} else {
            $message = "<div class='alert alert-danger text-center'>Incorrect password.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger text-center'>User not found.</div>";
    }

    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPD Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f0f4f8;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-card {
            width: 100%;
            max-width: 380px;
            padding: 30px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .login-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
            color: #0c4b8f;
        }
        .opd-logo {
            width: 80px;
            display: block;
            margin: 0 auto 10px auto;
        }
        .form-control { height: 45px; }
        .btn-login { height: 45px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="login-card">

        <img src="https://cdn-icons-png.flaticon.com/512/2966/2966486.png" class="opd-logo">

        <h3 class="login-title">OPD Login</h3>

        <!-- PHP MESSAGE -->
        <?php if(!empty($message)) echo $message; ?>

        <!-- LOGIN FORM -->
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" maxlength="10" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 btn-login">Login</button>
        </form>

        <div class="text-center mt-3">
            <a href="#" style="font-size:14px;">Forgot Password?</a>
        </div>
    </div>

</body>
</html>
