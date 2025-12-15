<?php
// ==========================================
// 1. BACKEND: DATABASE & LOGIC
// ==========================================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "opd_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- HANDLE FORM SUBMISSION (Save Triage) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $ticket = $_POST['ticket_id'];
    $notes = $_POST['triage_notes'];
    $actionType = $_POST['action']; // 'complete', 'skip', 'cancel'

    $newStatus = 'Pending';
    if ($actionType == 'complete') $newStatus = 'Completed';
    if ($actionType == 'skip') $newStatus = 'Skipped';
    if ($actionType == 'cancel') $newStatus = 'Cancelled';

    $stmt = $conn->prepare("UPDATE tbl_patients SET status = ?, triage_notes = ? WHERE ticket = ?");
    $stmt->bind_param("sss", $newStatus, $notes, $ticket);
    $stmt->execute();
    
    // Refresh page to update queue
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- FETCH LISTS ---
// 1. Priority Patients
$sql_priority = "SELECT * FROM tbl_patients WHERE status = 'Pending' AND is_priority = 1 ORDER BY id ASC";
$result_priority = $conn->query($sql_priority);

// 2. Normal Pending Patients
$sql_pending = "SELECT * FROM tbl_patients WHERE status = 'Pending' AND is_priority = 0 ORDER BY id ASC";
$result_pending = $conn->query($sql_pending);

// Determine initial ticket for display
$current_ticket = "---";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow | Triage Station</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* CSS STYLES (Kept exactly as requested) */
        :root { --primary: #0f766e; --accent: #0ea5e9; --bg: #f0f9ff; --surface: #ffffff; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; --text-dark: #1e293b; --text-light: #64748b; --border: #cbd5e1; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--bg); display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background-color: var(--primary); color: white; display: flex; flex-direction: column; padding: 20px; }
        .brand { font-size: 1.5rem; font-weight: bold; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .nav-item { display: flex; align-items: center; gap: 15px; padding: 15px; color: #ccfbf1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; cursor: pointer; user-select: none; }
        .nav-item:hover, .nav-item.active { background-color: rgba(255,255,255,0.2); color: white; }
        .main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        header { background: var(--surface); padding: 15px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .station-indicator { background: #e0f2fe; color: #0369a1; padding: 8px 15px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; }
        .teller-info { display: flex; align-items: center; gap: 15px; }
        .teller-avatar { width: 40px; height: 40px; background: #f43f5e; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .dashboard-grid { padding: 30px; display: grid; grid-template-columns: 2.5fr 1fr; gap: 30px; height: calc(100vh - 80px); }
        .left-col { overflow-y: auto; padding-right: 10px; }
        .card { background: var(--surface); border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); margin-bottom: 20px; }
        .right-col { display: flex; flex-direction: column; height: 100%; }
        .queue-footer { padding: 15px; background: white; border-top: 1px solid var(--border); }
        .queue-btn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .q-btn { padding: 12px; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: bold; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
        .q-btn:active { transform: scale(0.98); }
        .btn-next { background-color: var(--primary); grid-column: span 2; font-size: 1.1rem; }
        .btn-call { background-color: var(--accent); }
        .btn-skip { background-color: var(--warning); color: white; }
        .btn-cancel { background-color: var(--danger); color: white; }
        .section-title { color: var(--primary); font-size: 1.1rem; font-weight: 700; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-top: 10px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-dark); font-size: 0.85rem; }
        .form-input { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.95rem; background: #f8fafc; }
        .queue-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid var(--border); cursor: pointer; }
        .queue-item:hover { background: #f0f9ff; }
        .queue-item.active { border-left: 4px solid var(--primary); background: #f0f9ff; }
        .btn-submit { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem; margin-top: 10px; color: white; transition: 0.2s; background: var(--success); }
        .btn-submit:hover { background: #059669; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-user-nurse"></i> MedFlow</div>
        <nav>
            <a class="nav-item active"><i class="fas fa-notes-medical"></i> Triage Duty</a>
            <a href="#" class="nav-item"><i class="fas fa-tasks"></i> Managed Tickets</a>
        </nav>
    </aside>

    <!-- MAIN AREA -->
    <main class="main">
        <header>
            <div class="station-indicator"><i class="fas fa-clinic-medical"></i> Triage Station A</div>
            <div class="teller-info">
                <div style="text-align: right;">
                    <div style="font-weight: bold; color: var(--text-dark);">Nurse Joy</div>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Triage Officer</div>
                </div>
                <div class="teller-avatar">NJ</div>
            </div>
        </header>

        <!-- DASHBOARD VIEW -->
        <div id="view-dashboard">
            <div class="dashboard-grid">
                
                <!-- LEFT: TRIAGE FORM (WRAPPED IN FORM FOR SUBMISSION) -->
                <div class="left-col">
                    <form method="POST" action="">
                        <input type="hidden" name="action" id="formAction" value="complete">
                        <!-- Hidden Input to store the Current Ticket -->
                        <input type="hidden" name="ticket_id" id="inputTicketId" value="">

                        <div class="card">
                            <!-- === THIS IS THE HEADER YOU WANT TO UPDATE === -->
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                                <h2 style="margin:0; color:var(--primary);">Triage Screening Form</h2>
                                <div style="display:flex; gap: 10px;">
                                    <!-- DYNAMIC TICKET ID DISPLAY -->
                                    <span style="background:#e0f2fe; color:#0284c7; padding:6px 12px; border-radius:20px; font-size:0.85rem; font-weight:bold; display:flex; align-items:center; gap:5px; transition: transform 0.2s;">
                                        <i class="fas fa-ticket-alt"></i> Ticket: 
                                        <span id="currentTicket"><?php echo htmlspecialchars($current_ticket); ?></span>
                                    </span>
                                    <span style="background:#d1fae5; color:#047857; padding:6px 12px; border-radius:20px; font-size:0.85rem; font-weight:bold; display:flex; align-items:center; gap:5px;">
                                        <i class="fas fa-user-tag"></i> <span>New Patient</span>
                                    </span>
                                </div>
                            </div>
                            <!-- ============================================= -->

                            <div class="section-title"><i class="fas fa-user"></i> Patient Demographics</div>
                            <div class="form-grid-3">
                                <div><label class="form-label">First Name</label><input type="text" class="form-input" id="fname" placeholder="Ex: Juan"></div>
                                <div><label class="form-label">Middle Initial</label><input type="text" class="form-input" placeholder="Ex: D."></div>
                                <div><label class="form-label">Last Name</label><input type="text" class="form-input" id="lname" placeholder="Ex: Dela Cruz"></div>
                            </div>

                            <div class="form-grid-3">
                                <div><label class="form-label">Date of Birth</label><input type="date" class="form-input" id="dob"></div>
                                <div><label class="form-label">Age</label><input type="text" class="form-input" id="age" readonly style="background:#e2e8f0;"></div>
                            </div>

                            <div style="margin-top:15px;">
                                <label class="form-label">Additional Triage Notes / Chief Complaint</label>
                                <textarea name="triage_notes" class="form-input" style="height:80px;" placeholder="Patient complains of..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-submit" onclick="return validateSubmit()">
                                <i class="fas fa-paper-plane"></i> Saved to Doctor
                            </button>
                        </div>
                    </form>
                </div>

                <!-- RIGHT: INCOMING QUEUE -->
                <div class="right-col">
                    <div id="incomingList" style="overflow-y:auto; flex:1; background:white; border-left:1px solid var(--border);">
                        <div class="queue-header" style="font-weight:bold; color:var(--text-dark);">Waiting List</div>
                        
                        <!-- LIST: PRIORITY -->
                        <div id="list-priority">
                            <?php 
                            if ($result_priority && $result_priority->num_rows > 0) {
                                while($row = $result_priority->fetch_assoc()) {
                                    echo '<div class="queue-item" style="border-left: 4px solid var(--danger); background: #fef2f2;" 
                                            onclick="selectPatient(this, \''.$row['ticket'].'\')">
                                            <div>
                                                <div style="font-weight:bold; color:#b91c1c;">'.$row['ticket'].' <i class="fas fa-exclamation-circle"></i></div>
                                                <div style="font-size:0.8rem; color:var(--text-light);">'.$row['patient_name'].'</div>
                                            </div>
                                            <div style="font-size:0.8rem; font-weight:bold; color:var(--danger);">Priority</div>
                                          </div>';
                                }
                            }
                            ?>
                        </div>

                        <!-- LIST: PENDING (NORMAL) -->
                        <div id="list-pending">
                            <?php 
                            if ($result_pending && $result_pending->num_rows > 0) {
                                while($row = $result_pending->fetch_assoc()) {
                                    echo '<div class="queue-item" onclick="selectPatient(this, \''.$row['ticket'].'\')">
                                            <div>
                                                <div style="font-weight:bold;">'.$row['ticket'].'</div>
                                                <div style="font-size:0.8rem; color:var(--text-light);">'.$row['patient_name'].'</div>
                                            </div>
                                            <div style="font-size:0.8rem; font-weight:bold; color:var(--primary);">Regular</div>
                                          </div>';
                                }
                            } else {
                                echo '<div style="padding:20px; text-align:center; color:#ccc;">No pending patients</div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- QUEUE ACTION BUTTONS -->
                    <div class="queue-footer">
                        <div class="queue-btn-grid">
                            <button type="button" class="q-btn btn-call" onclick="queueAction('call')"><i class="fas fa-bullhorn"></i> Call</button>
                            <button type="button" class="q-btn btn-skip" onclick="queueAction('skip')"><i class="fas fa-forward"></i> Skip</button>
                            <button type="button" class="q-btn btn-cancel" onclick="queueAction('cancel')"><i class="fas fa-ban"></i> Cancel</button>
                            
                            <!-- === THIS IS THE BUTTON YOU ASKED FOR === -->
                            <button type="button" class="q-btn btn-next" onclick="queueAction('next')">
                                Next Patient <i class="fas fa-chevron-right"></i>
                            </button>
                            <!-- ======================================== -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // 1. FUNCTION: Select Patient & Update Header (The specific request)
        function selectPatient(element, ticketNumber) {
            // Remove 'active' class from all queue items
            document.querySelectorAll('.queue-item').forEach(item => {
                item.classList.remove('active');
            });

            // Add 'active' class to the clicked/selected item
            element.classList.add('active');

            // --- KEY LOGIC: Update the Header Ticket ID ---
            const ticketDisplay = document.getElementById('currentTicket');
            ticketDisplay.innerText = ticketNumber;

            // Update the hidden input for the backend form
            document.getElementById('inputTicketId').value = ticketNumber;

            // Add a small visual pop animation
            ticketDisplay.parentElement.style.transform = "scale(1.1)";
            setTimeout(() => {
                ticketDisplay.parentElement.style.transform = "scale(1)";
            }, 200);

            // Optional: Reset form fields for new patient
            document.getElementById('fname').value = "";
            document.getElementById('lname').value = "";
            document.querySelector('textarea').value = "";
            
            console.log("Selected: " + ticketNumber);
        }

        // 2. FUNCTION: Handle Queue Actions (Next, Call, etc.)
        function queueAction(action) {
            if (action === 'next') {
                const currentItem = document.querySelector('.queue-item.active');
                let nextItem = null;

                if (currentItem) {
                    // Try to find the immediate next sibling
                    nextItem = currentItem.nextElementSibling;
                    
                    // IF we are at the end of Priority List, jump to Pending List
                    if (!nextItem && currentItem.parentElement.id === 'list-priority') {
                        const pendingList = document.getElementById('list-pending');
                        nextItem = pendingList.querySelector('.queue-item');
                    }
                } else {
                    // IF nothing selected, pick the very first available patient
                    // Check Priority first
                    const priorityList = document.getElementById('list-priority');
                    nextItem = priorityList.querySelector('.queue-item');
                    
                    // If no priority, check pending
                    if(!nextItem) {
                        const pendingList = document.getElementById('list-pending');
                        nextItem = pendingList.querySelector('.queue-item');
                    }
                }

                // EXECUTE SELECTION
                if (nextItem && nextItem.classList.contains('queue-item')) {
                    // This triggers the click event, running selectPatient() automatically
                    nextItem.click(); 
                    nextItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    alert("No more patients in the queue!");
                }
            } 
            else if (action === 'call') {
                const t = document.getElementById('currentTicket').innerText;
                if(t !== '---') alert("📢 Paging Ticket Number: " + t + " to Triage A");
            }
            else if (action === 'skip' || action === 'cancel') {
                const t = document.getElementById('inputTicketId').value;
                if(!t) { alert("Select a patient first"); return; }
                
                if(confirm("Are you sure you want to " + action + " this ticket?")) {
                    document.getElementById('formAction').value = action;
                    document.querySelector('form').submit();
                }
            }
        }

        function validateSubmit() {
            const t = document.getElementById('inputTicketId').value;
            if(!t || t === '---') {
                alert("Please select a patient from the queue first.");
                return false;
            }
            return confirm("Save triage details and complete this patient?");
        }

        // Auto-select first patient on load (Optional)
        window.onload = function() {
            // queueAction('next'); 
        };
    </script>
</body>
</html>