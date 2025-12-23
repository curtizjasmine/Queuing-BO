
<?php
// start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: for debugging, uncomment to see session contents
// echo '<pre>'; print_r($_SESSION); echo '</pre>';

// Safe getters with defaults
$full_name = isset($_SESSION['full_name']) && is_string($_SESSION['full_name']) && trim($_SESSION['full_name']) !== ''
    ? trim($_SESSION['full_name'])
    : 'Guest User';

$role = isset($_SESSION['role']) && is_string($_SESSION['role']) && trim($_SESSION['role']) !== ''
    ? trim($_SESSION['role'])
    : 'Guest';

// Compute initials safely (first letter of first + first letter of last if available)
$initials = '';
if ($full_name !== 'Guest User') {
    $parts = preg_split('/\s+/', $full_name, -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) >= 1) {
        $first = $parts[0];
        $second = $parts[1] ?? '';
        $initials = strtoupper(($first[0] ?? '') . ($second[0] ?? ''));
        // if initials empty (very unlikely), fallback to first two letters of name
        if ($initials === '') {
            $initials = strtoupper(substr($first, 0, 2));
        }
    } else {
        $initials = strtoupper(substr($full_name, 0, 2));
    }
} else {
    $initials = 'GU'; // Guest
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow | Clinical Workflow</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0f766e; /* Teal */
            --accent: #0ea5e9;  /* Call Blue */
            --warning: #f59e0b; /* Skip Orange */
            --danger: #ef4444;  /* Cancel/Done Red */
            --success: #10b981; /* Saved to Doctor Green */
            --bg: #f0f9ff;
            --surface: #ffffff;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border: #cbd5e1;
            --step-inactive: #e2e8f0;
            --ticket-bg: #e0f2fe;
            --ticket-text: #0369a1;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, sans-serif; }
        body { background-color: var(--bg); display: flex; height: 100vh; overflow: hidden; }

        /* --- SIDEBAR --- */
        .sidebar { width: 260px; background-color: var(--primary); color: white; display: flex; flex-direction: column; padding: 20px; transition: 0.3s; flex-shrink: 0; }
        .brand { font-size: 1.5rem; font-weight: bold; margin-bottom: 40px; display: flex; align-items: center; gap: 10px; }
        .nav-item { display: flex; align-items: center; gap: 15px; padding: 15px; color: #ccfbf1; text-decoration: none; border-radius: 8px; margin-bottom: 5px; cursor: pointer; transition: 0.3s; }
        .nav-item:hover, .nav-item.active { background-color: rgba(255,255,255,0.2); color: white; }
        .dropdown-menu { display: none; flex-direction: column; background-color: rgba(0, 0, 0, 0.2); border-radius: 8px; margin-bottom: 5px; overflow: hidden; }
        .dropdown-menu.show { display: flex; }
        .nav-sub-item { padding: 12px 15px 12px 50px; color: #ccfbf1; font-size: 0.9rem; cursor: pointer; transition: 0.2s; }
        .nav-sub-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .chevron { transition: 0.3s; margin-left: auto; }
        .chevron.rotate { transform: rotate(180deg); }

        /* --- MAIN LAYOUT --- */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        header { background: var(--surface); padding: 15px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; height: 90px; flex-shrink: 0;  }
        .station-indicator { background: var(--ticket-bg); color: var(--ticket-text); padding: 8px 15px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; }
        .teller-avatar { width: 40px; height: 40px; background: #f43f5e; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }

        /* --- DASHBOARD GRID --- */
        #view-dashboard { display: block; height: calc(100vh - 70px); }
        .dashboard-grid { padding: 20px; display: grid; grid-template-columns: 2.8fr 1fr; gap: 20px; height: 100%; }
        .left-col { overflow-y: auto; padding-right: 10px; padding-bottom: 50px; }
        .card { background: var(--surface); border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

        /* --- STEPPER --- */
        .progressbar-wrapper { margin: 20px 0 30px 0; position: relative; padding: 0 40px; }
        .progress-line-bg { position: absolute; top: 20px; left: 40px; right: 40px; height: 3px; background: var(--step-inactive); z-index: 0; }
        .progress-line-fill { position: absolute; top: 20px; left: 40px; height: 3px; background: var(--primary); z-index: 0; transition: width 0.3s ease; }
        .progressbar { display: flex; justify-content: space-between; position: relative; z-index: 1; }
        .step-item { text-align: center; width: 80px; cursor: pointer; }
        .step-circle { width: 40px; height: 40px; background: white; color: var(--text-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto; border: 2px solid var(--step-inactive); transition: 0.3s; }
        .step-item.completed .step-circle { background: var(--primary); color: white; border-color: var(--primary); }
        .step-item.active .step-circle { background: white; color: var(--primary); border: 2px solid var(--primary); box-shadow: 0 0 0 5px #ccfbf1; }
        .step-label { font-size: 0.75rem; font-weight: bold; color: var(--text-light); }
        .step-item.active .step-label { color: var(--primary); }

        /* --- FORM STYLING --- */
        .step-pane { display: none; padding: 0 30px 30px 30px; }
        .step-pane.active-pane { display: block; }
        .section-title { color: var(--primary); font-size: 1.1rem; font-weight: 700; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; display: flex; align-items: center; gap: 10px; margin-top: 20px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.85rem; color: var(--text-dark); }
        .form-input { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; background: #f8fafc; font-size: 0.9rem; outline: none; }
        .form-input:focus { border-color: var(--primary); }
        
        /* --- SCREENING SPECIFICS --- */
        .triage-section-header { background: #f1f5f9; padding: 8px 15px; font-weight: bold; font-size: 0.85rem; color: #475569; border-radius: 4px; margin: 15px 0 10px 0; }
        .screening-question { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; }
        .radio-group { display: flex; gap: 15px; }
        .radio-group label { font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .btn-new-patient { background: #dcfce7; color: #166534; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; border: none; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .btn-save-doctor { width: 100%; background: var(--success); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 25px; display: flex; justify-content: center; align-items: center; gap: 10px; font-size: 1rem; }

        /* --- QUEUE COLUMN --- */
        .right-col { display: flex; flex-direction: column; height: 100%; overflow: hidden; }
        .queue-header { padding: 20px; border-bottom: 1px solid var(--border); }
        .badge { padding: 4px 10px; border-radius: 8px; font-weight: bold; font-size: 0.8rem; display: flex; align-items: center; gap: 5px; }
        .badge-red { background: #fee2e2; color: #b91c1c; }
        .badge-blue { background: var(--ticket-bg); color: var(--ticket-text); }
        .queue-footer { padding: 15px; background: white; border-top: 1px solid var(--border); flex-shrink: 0; }
        .btn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .q-btn { border: none; border-radius: 8px; padding: 12px; color: white; font-weight: bold; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-call { background: var(--accent); }
        .btn-skip { background: var(--warning); }
        .btn-cancel, .btn-done { background: var(--danger); }
        .btn-next-full { background: var(--primary); grid-column: span 2; padding: 15px; font-size: 1.1rem; margin-top: 5px; }

        /* --- MODAL --- */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-card { background: white; width: 400px; padding: 25px; border-radius: 12px; }

        /* --- TABLE VIEW --- */
        #view-table { display: none; padding: 30px; height: calc(100vh - 70px); overflow-y: auto; }
        .med-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; border: 1px solid var(--border); }
        .med-table th { background: var(--primary); color: white; padding: 15px; text-align: left; }
        .med-table td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
    </style>
</head>
<body>

    <!-- MODAL -->
     <aside class="sidebar">
        <div class="brand"><i class="fas fa-user-nurse"></i> MedFlow</div>
        <nav>
            <a onclick="showDashboard()" class="nav-item active" id="nav-workflow"><i class="fas fa-notes-medical"></i> Clinical Workflow</a>
            <div class="nav-item" onclick="toggleDropdown()"><i class="fas fa-tasks"></i> Managed Tickets <i class="fas fa-chevron-down chevron" id="tickChevron"></i></div>
            <div class="dropdown-menu" id="tickDropdown">
                <div class="nav-sub-item" onclick="loadTable('completed')"><i class="fas fa-check-circle"></i> Completed</div>
                <div class="nav-sub-item" onclick="loadTable('skipped')"><i class="fas fa-forward"></i> Skipped</div>
            </div>
            <a class="nav-item"><i class="fas fa-search"></i> Patient Search</a>
        </nav>
    </aside>

    <main class="main" id="app">
                <div id="actionModal" class="modal-overlay">
        <div class="modal-card">
            <h2 id="modalTitle" style="margin-bottom: 15px;">Action Required</h2>
            <div style="background: #f1f5f9; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center;">Ticket: <span id="modalTicketId" style="font-weight: bold; color: var(--primary);">---</span></div>
            <textarea class="form-input" style="height: 100px; resize: none;" placeholder="Remarks..."></textarea>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button onclick="closeModal()" style="padding: 8px 15px; border: 1px solid var(--border); background: white; border-radius: 6px;">Cancel</button>
                <button onclick="confirmModalAction()" style="padding: 8px 15px; background: var(--primary); color: white; border: none; border-radius: 6px;">Confirm</button>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->

        <header>
            <div class="station-indicator" ><i class="fas fa-clinic-medical"></i> Triage Station A</div>
            
            <div class="teller-info" >
                <div style="text-align: right;">
                    <div style="font-weight: bold; color: var(--text-dark);">
                         <?= htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-light);">Triage Officer</div>
                </div>
                <div class="teller-avatar" style="margin-left: 70px;">
                    <?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        </header>

        <div id="view-dashboard">
            <div class="dashboard-grid">
                
                <!-- MAIN FORM SECTION -->
                <div class="left-col">
                    <div class="card" style="min-height: 100%;">
                        <div style="padding: 25px 25px 0 25px; display: flex; justify-content: space-between; align-items: center;">
                            <h1 style="color: var(--primary); font-size: 1.6rem;" id="paneTitle">Patient Registration</h1>
                            <div style="display: flex; gap: 10px;">
                                <span style="background: var(--ticket-bg); color: var(--ticket-text); padding: 6px 15px; border-radius: 20px; font-weight: bold; font-size: 0.85rem;">
                                    <i class="fas fa-ticket-alt"></i> Ticket: <span id="currentTicket">C-104</span>
                                </span>
                                <button class="btn-new-patient"><i class="fas fa-user-plus"></i> New Patient</button>
                            </div>
                        </div>

                        <!-- Stepper -->
                        <div class="progressbar-wrapper">
                            <div class="progress-line-bg"></div>
                            <div class="progress-line-fill" id="progressFill" style="width: 33.33%;"></div>
                            <div class="progressbar">
                                <div class="step-item completed" onclick="goToStep(1)">
                                    <div class="step-circle"><i class="fas fa-check"></i></div>
                                    <div class="step-label">Triage</div>
                                </div>
                                <div class="step-item active" onclick="goToStep(2)">
                                    <div class="step-circle"><i class="fas fa-user"></i></div>
                                    <div class="step-label">Registration</div>
                                </div>
                                <div class="step-item" onclick="goToStep(3)"   disabled>
                                    <div class="step-circle"><i class="fas fa-credit-card"></i></div>
                                    <div class="step-label">Payment</div>
                                </div>
                                <div class="step-item" onclick="goToStep(4)"  disabled>
                                    <div class="step-circle"><i class="fas fa-user-md"></i></div>
                                    <div class="step-label">Consultation</div>
                                </div>
                            </div>
                        </div>

                        <!-- PANE 1: TRIAGE FORM (Unchanged) -->
                        <div id="pane-1" class="step-pane">
                            <div class="section-title"><i class="fas fa-user"></i> Patient Demographics</div>
                            <div class="form-grid-3">
                                <div><label class="form-label">First Name</label><input type="text" class="form-input" placeholder="Ex: Juan" v-model="fname"></div>
                                <div><label class="form-label">Middle Initial</label><input type="text" class="form-input" placeholder="Ex: D." v-model="mname"></div>
                                <div><label class="form-label">Last Name</label><input type="text" class="form-input" placeholder="Ex: Dela Cruz" v-model="lname"></div>
                            </div>
                            <div class="form-grid-3">
                                <div><label class="form-label">Date of Birth</label><input type="text" class="form-input" v-model="BOD"></div>
                                <div><label class="form-label">Age</label><input type="text" class="form-input" placeholder="Age" v-model="age"></div>
                                <div><label class="form-label">Sex</label><input type="text" class="form-input" v-model="gender"></div>
                            </div>
                            <div class="form-grid-2">
                                <div><label class="form-label">Civil Status</label><input type="text" class="form-input" v-model="civil_Status"></div>
                                <div><label class="form-label">Contact No.</label><input type="text" class="form-input" placeholder="0912-345-6789" v-model="contact_no"></div>
                            </div>
                            <div style="margin-bottom: 20px;"><label class="form-label">Complete Address</label><input type="text" class="form-input" placeholder="House No, Street, Barangay, City/Municipality" v-model="home_address"></div>

                            <div class="section-title"><i class="fas fa-heartbeat"></i> Vital Signs</div>
                            <div class="form-grid-3">
                                <div><label class="form-label">Blood Pressure</label><input type="text" class="form-input" placeholder="120/80" v-model="BP"></div>
                                <div><label class="form-label">Temperature (°C)</label><input type="text" class="form-input" placeholder="36.5" v-model="Pressure"></div>
                                <div><label class="form-label">Weight (kg)</label><input type="text" class="form-input" placeholder="65" v-model="Weight"></div>
                            </div>

                            <div class="section-title"><i class="fas fa-clipboard-list"></i> Screening: In the past 1 Week...</div>
                            <div class="triage-section-header">A. Symptoms</div>
                            <div class="screening-question">1. Fever (Temperature > 38°C)? <div class="radio-group"><input type="text" class="form-input" v-model="sym_fever"></div></div>
                            <div class="screening-question">2. Cough and/or Colds? <div class="radio-group"><input type="text" class="form-input" v-model="has_cough"></div></div>
                            <div class="screening-question">3. Sore Throat / Throat Pain? <div class="radio-group"><input type="text" class="form-input" v-model="has_sorethroat"></div></div>
                             <div class="screening-question">4. Cough and/or Colds? <div class="radio-group"><input type="text" class="form-input" v-model="has_shortnessBreath"></div></div>
                            <div class="screening-question">5. Sore Throat / Throat Pain? <div class="radio-group"><input type="text" class="form-input" v-model="has_sorethroat"></div></div>
                            
                            <div class="triage-section-header">B. Exposure History</div>
                            <div class="screening-question">1. History of confirmed COVID-19 Infection? <div class="radio-group"><input type="text" class="form-input" v-model="has_history_Covid"></div></div>
                            <div class="screening-question">2. Travel/Residence in area with local transmission? <div class="radio-group"><input type="text" class="form-input" v-model="have_localTransimission"></div></div>
                            <div class="screening-question">3. History of confirmed COVID-19 Infection? <div class="radio-group"><input type="text" class="form-input" v-model="have_contact_recentTravel"></div></div>
                            <div class="screening-question">4. Travel/Residence in area with local transmission? <div class="radio-group"><input type="text" class="form-input"  v-model="has_inluenza_illness"></div></div>
                            <div class="screening-question">5. History of confirmed COVID-19 Infection? <div class="radio-group"><input type="text" class="form-input" v-model="has_contactConfirm"></div></div>
             
                            <div class="triage-section-header">C. Medication Intake</div>
                            <div style="padding: 10px;"><label class="form-label">Did you take any Antipyretics/Pain Killers?</label>
                                <div class="radio-group" style="margin-top: 10px;"><input type="text" class="form-input" v-model="medicine"></div>
                            </div>

                            <div class="triage-section-header">D. Medical History</div>
                            <div style="margin-bottom: 15px;"><label class="form-label">1. List Pre-existing conditions:</label><input type="text" class="form-input" placeholder="e.g., Hypertension, Diabetes..." v-model="existingConditions"></div>
                            <div style="margin-bottom: 15px;"><label class="form-label">2. List Pre-existing conditions:</label><input type="text" class="form-input" placeholder="e.g., Hypertension, Diabetes..." v-model="admissionDate"></div>
                            <div class="screening-question">1. Were you admitted recently? <div class="radio-group"><input type="text" class="form-input" v-model="admitted_conditions"></div></div>
                             <div class="screening-question">2. Were you admitted recently? <div class="radio-group"><input type="text" class="form-input"  v-model="historyICU"></div></div>

                            <div style="margin-top: 20px;"><label class="form-label">Additional Triage Notes / Chief Complaint</label><textarea class="form-input" style="height: 100px; resize: none;" placeholder="Patient complains of..."></textarea></div>

                            <button class="btn-save-doctor" onclick="goToStep(2)"><i class="fas fa-paper-plane"></i> Saved to Doctor</button>
                        </div>

                        <!-- PANE 2: REGISTRATION (Active) -->
                        <div id="pane-2" class="step-pane active-pane">
                            <div class="section-title">Patient Registration Data</div>
                            <p style="color: var(--text-light); margin-bottom: 20px;">Review and complete demographic records.</p>
                              <div class="form-grid-3">
                                <div><label class="form-label">First Name</label><input type="text" class="form-input" placeholder="Ex: Juan" v-model="fname"></div>
                                <div><label class="form-label">Middle Initial</label><input type="text" class="form-input" placeholder="Ex: D." v-model="lname"></div>
                                <div><label class="form-label">Last Name</label><input type="text" class="form-input" placeholder="Ex: Dela Cruz" v-model="mname"></div>
                            </div>
                            <div class="form-grid-3">
                                <div><label class="form-label">Date of Birth</label><input type="text" class="form-input" v-model="BOD"></div>
                                <div><label class="form-label">Age</label><input type="text" class="form-input" placeholder="Age" v-model="age"></div>
                                <div><label class="form-label">Sex</label><input type="text" class="form-input" placeholder="Sex" v-model="gender"></div>
                            </div>
                            <div class="form-grid-2">
                                <div><label class="form-label">Civil Status</label><input type="text" class="form-input" placeholder="Civil-Status" v-model="civil_Status"></div>
                                <div><label class="form-label">Contact No.</label><input type="text" class="form-input" placeholder="0912-345-6789"  v-model="contact_no"></div>
                            </div>
                            <div style="margin-bottom: 20px;"><label class="form-label">Complete Address</label><input type="text" class="form-input" placeholder="House No, Street, Barangay, City/Municipality"  v-model="home_address"></div>

                            <div class="section-title"><i class="fas fa-heartbeat"></i> Vital Signs</div>
                            <div class="form-grid-3">
                                <div><label class="form-label">Blood Pressure</label><input type="text" class="form-input" placeholder="120/80" v-model="BP"></div>
                                <div><label class="form-label">Temperature (°C)</label><input type="text" class="form-input" placeholder="36.5" v-model="Pressure"></div>
                                <div><label class="form-label">Weight (kg)</label><input type="text" class="form-input" placeholder="65" v-model = "Weight"></div>
    </div>
                            
                        </div>

                        
                    </div>
                </div>

                <!-- QUEUE SIDEBAR -->
                <div class="right-col">
                    <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                        <div class="queue-header">
                            <div style="display: flex; justify-content: space-between;">
                                <div><h3 style="font-size: 1rem;">Incoming Patients</h3><p style="font-size: 0.75rem; color: var(--text-light);">Waitlist for Triage</p></div>
                                <div style="display: flex; gap: 5px;"><div class="badge badge-red">1</div><div class="badge badge-blue">12</div></div>
                            </div>
                        </div>
                            <div class="queue-footer">
                            <div class="btn-grid">
                                <button class="q-btn btn-call"   @click="callnumber(selectedTicket?.patient_id,selectedTicket?.ticket)"><i class="fas fa-bullhorn"></i> Call</button>
                                <button class="q-btn btn-skip" onclick="openModal('Skip')"><i class="fas fa-forward"></i> Skip</button>
                                <button class="q-btn btn-cancel" onclick="openModal('Cancel')"><i class="fas fa-ban"></i> Cancel</button>
                                <button class="q-btn btn-done" onclick="alert('Triage Done')"><i class="fas fa-check"></i> Done</button>
                                <button class="q-btn btn-next-full" onclick="alert('Serving Next')">Next <i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
              <div v-for="(ticket, index) in pendingTickets" 
     :key="ticket.ticket_id || index"
     class="queue-item"
     @click="selectTicket(ticket)" 
     :style="{
        display: 'flex',
        alignItems: 'center', /* Vertically centers all children */
        justifyContent: 'flex-start', 
        padding: '0 15px',
        marginBottom: '12px',
        borderRadius: '12px',
        cursor: 'pointer',
        width: '220px',
        marginLeft: '10px',
        marginTop: '10px',
        height: '75px',
        transition: 'all 0.2s ease-in-out',
        border: '1px solid',
        backgroundColor: (selectedTicket && selectedTicket.patient_id === ticket.patient_id) ? '#f0f7ff' : '#ffffff',
        borderColor: (selectedTicket && selectedTicket.patient_id === ticket.patient_id) ? '#007bff' : '#e0e0e0',
        boxShadow: (selectedTicket && selectedTicket.patient_id === ticket.patient_id) ? '0 4px 12px rgba(0,123,255,0.15)' : '0 2px 4px rgba(0,0,0,0.05)'
     }">
     
    <!-- Left Accent Indicator (Bar) - Perfectly Centered Vertically -->
    <div :style="{
        width: '5px',
        height: '40px', /* Fixed height for a cleaner look */
        borderRadius: '10px',
       
          
        flexShrink: 0, /* Prevents the bar from squishing */
        backgroundColor: (selectedTicket && selectedTicket.patient_id === ticket.patient_id) ? '#007bff' : '#a4c6f8ff'
    }"  ></div>

    <!-- Text Content Wrapper -->
    <div style="display: flex; flex-direction: column; justify-content: center;   margin-left: 30px;">
        <!-- Ticket Number -->
        <div style="
            font-size: 1.1rem; 
            font-weight: 800; 
            color: #1a1a1a; 
            line-height: 1;
            margin-bottom: 4px;
        " >
            {{ ticket.ticket }}
        </div>

        <!-- Patient Status -->
        <div style="
            font-size: 0.75rem; 
            font-weight: 600; 
            color: #666; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        ">
            {{ ticket.status_patients }}
        </div>
    </div>
</div>          

                    </div>
                </div>
            </div>
        </div>

        <!-- TABLE VIEW -->
        <div id="view-table">
            <h2 id="tableTitle" style="margin-bottom: 20px; color: var(--primary);">COMPLETED TICKETS</h2>
            <table class="med-table">
                <thead><tr><th>Ticket</th><th>Patient Name</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
        
    </main>


    
<script src="./assets/vue.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<script src="./assets/app.js"></script>

    <script>
        function goToStep(step) {
            document.querySelectorAll('.step-pane').forEach(p => p.classList.remove('active-pane'));
            document.getElementById('pane-' + step).classList.add('active-pane');
            
            const fill = ((step - 1) / 3) * 100;
            document.getElementById('progressFill').style.width = fill + '%';
            
            const steps = document.querySelectorAll('.step-item');
            const titles = ["Triage Screening Form", "Patient Registration", "Payment & Billing", "Medical Consultation"];
            document.getElementById('paneTitle').innerText = titles[step-1];

            steps.forEach((s, i) => {
                s.classList.remove('active', 'completed');
                const icon = s.querySelector('i');
                if(i + 1 < step) {
                    s.classList.add('completed');
                    icon.className = "fas fa-check";
                }
                if(i + 1 === step) {
                    s.classList.add('active');
                    const icons = ["fa-notes-medical", "fa-user", "fa-credit-card", "fa-user-md"];
                    icon.className = "fas " + icons[i];
                }
            });
            document.querySelector('.left-col').scrollTop = 0;
        }

        function toggleDropdown() {
            document.getElementById('tickDropdown').classList.toggle('show');
            document.getElementById('tickChevron').classList.toggle('rotate');
        }

        function showDashboard() {
            document.getElementById('view-dashboard').style.display = 'block';
            document.getElementById('view-table').style.display = 'none';
            document.getElementById('nav-workflow').classList.add('active');
        }

        function loadTable(category) {
            document.getElementById('view-dashboard').style.display = 'none';
            document.getElementById('view-table').style.display = 'block';
            document.getElementById('nav-workflow').classList.remove('active');
            document.getElementById('tableTitle').innerText = category.toUpperCase() + " TICKETS";
            document.getElementById('tableBody').innerHTML = `<tr><td>C-102</td><td>Sample Patient</td><td>09:45 AM</td><td><span style="padding:4px 10px; border-radius:15px; background:#e0f2fe; color:#0369a1;">${category}</span></td><td><button style="border:none; background:none; color:var(--accent); cursor:pointer;"><i class="fas fa-eye"></i> View</button></td></tr>`;
        }

        function selectPatient(tid) {
            document.getElementById('currentTicket').innerText = tid;
            document.getElementById('servingTicket').innerText = tid;
            goToStep(1); 
        }

        function openModal(action) {
            document.getElementById('modalTitle').innerText = action + " Patient";
            document.getElementById('modalTicketId').innerText = document.getElementById('servingTicket').innerText;
            document.getElementById('actionModal').style.display = 'flex';
        }
        function closeModal() { document.getElementById('actionModal').style.display = 'none'; }
        function confirmModalAction() { alert("Action confirmed!"); closeModal(); }
    </script>

</body>
</html>
