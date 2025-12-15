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
            --accent: #0ea5e9;  /* Sky Blue */
            --bg: #f0f9ff;
            --surface: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --border: #cbd5e1;
            --step-inactive: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: var(--bg);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background-color: var(--primary);
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .brand {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            color: #ccfbf1;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 5px;
            transition: 0.3s;
            cursor: pointer;
            user-select: none;
        }

        .nav-item:hover, .nav-item.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }

        /* DROPDOWN STYLES */
        .dropdown-toggle { justify-content: space-between; }
        .dropdown-menu { display: none; flex-direction: column; background-color: rgba(0, 0, 0, 0.2); border-radius: 8px; margin-bottom: 5px; overflow: hidden; }
        .dropdown-menu.show { display: flex; }
        .nav-sub-item { padding: 12px 15px 12px 50px; color: #ccfbf1; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; transition: 0.2s; cursor: pointer; }
        .nav-sub-item:hover { background-color: rgba(255, 255, 255, 0.1); color: white; }
        .chevron { transition: transform 0.3s; font-size: 0.8rem; }
        .chevron.rotate { transform: rotate(180deg); }

        /* MAIN CONTENT */
        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        header {
            background: var(--surface);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .station-indicator { background: #e0f2fe; color: #0369a1; padding: 8px 15px; border-radius: 20px; font-weight: bold; font-size: 0.9rem; }
        .teller-info { display: flex; align-items: center; gap: 15px; }
        .teller-avatar { width: 40px; height: 40px; background: #f43f5e; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }

        /* VIEW CONTAINERS */
        #view-dashboard { display: block; height: 100%; }
        #view-table { display: none; padding: 30px; height: calc(100vh - 80px); overflow-y: auto; }

        /* GRID SYSTEM */
        .dashboard-grid {
            padding: 30px;
            display: grid;
            grid-template-columns: 2.5fr 1fr;
            gap: 30px;
            height: calc(100vh - 80px);
        }

        /* LEFT COLUMN (FORM) */
        .left-col { overflow-y: auto; padding-right: 10px; }
        .card { background: var(--surface); border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); margin-bottom: 20px; }

        /* RIGHT COLUMN (QUEUE) */
        .right-col { display: flex; flex-direction: column; height: 100%; }
        .queue-header { padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--border); }
        .queue-list { flex: 1; overflow-y: auto; }
        .queue-footer { padding: 15px; background: white; border-top: 1px solid var(--border); }
        
        /* QUEUE BUTTONS */
        .queue-btn-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .q-btn { padding: 12px; border: none; border-radius: 8px; font-size: 0.9rem; font-weight: bold; cursor: pointer; color: white; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
        .q-btn:active { transform: scale(0.98); }
        .btn-next { background-color: var(--primary); grid-column: span 2; font-size: 1.1rem; }
        .btn-call { background-color: var(--accent); }
        .btn-skip { background-color: var(--warning); color: white; }
        .btn-cancel { background-color: var(--danger); color: white; }

        /* FORM STYLES */
        .section-title { color: var(--primary); font-size: 1.1rem; font-weight: 700; margin-bottom: 15px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-top: 10px; }
        .subsection-header { background-color: #e2e8f0; padding: 5px 10px; font-size: 0.85rem; font-weight: bold; color: var(--text-dark); margin-top: 10px; border-radius: 4px; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-dark); font-size: 0.85rem; }
        .form-input { width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.95rem; background: #f8fafc; }
        .form-input:focus { border-color: var(--accent); background: white; outline: none; }

        /* CHECKLIST STYLES */
        .checklist-container { background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid var(--border); }
        .checklist-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .checklist-item:last-child { border-bottom: none; }
        .checklist-question { font-size: 0.9rem; font-weight: 600; color: var(--text-dark); flex: 1; }
        .radio-group { display: flex; gap: 5px; }
        .radio-label { cursor: pointer; font-size: 0.85rem; padding: 4px 10px; border-radius: 4px; border: 1px solid var(--border); background: white; display: flex; align-items: center; }
        .radio-label:hover { background: #e2e8f0; }
        input[type="radio"], input[type="checkbox"] { margin-right: 5px; }

        /* QUEUE LIST ITEMS */
        .queue-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid var(--border); cursor: pointer; }
        .queue-item:hover { background: #f0f9ff; }
        .queue-item.active { border-left: 4px solid var(--primary); background: #f0f9ff; }

        .btn-submit { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem; margin-top: 10px; color: white; transition: 0.2s; background: var(--success); }
        .btn-submit:hover { background: #059669; }
        .inline-input { border: none; border-bottom: 1px solid #94a3b8; background: transparent; padding: 2px 5px; font-size: 0.9rem; width: 200px; }

        /* --- TABLE STYLES --- */
        .med-table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); }
        .med-table thead { background-color: var(--primary); color: white; }
        .med-table th, .med-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--border); font-size: 0.9rem; vertical-align: middle; }
        .med-table tbody tr:hover { background-color: #f1f5f9; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; display: inline-block; }
        .badge-completed { background: #d1fae5; color: #047857; }
        .badge-pending { background: #fee2e2; color: #b91c1c; }
        .badge-skipped { background: #fff7ed; color: #c2410c; }
        .badge-cancelled { background: #f3f4f6; color: #374151; }
        
        /* Remarks text style */
        .remarks-text { font-style: italic; color: var(--text-light); font-size: 0.85rem; }

        /* --- INTERACTIVE TABS & MODAL --- */
        .queue-tab {
            cursor: pointer;
            transition: all 0.2s;
            opacity: 0.5;
            border: 2px solid transparent;
        }
        .queue-tab.active-tab {
            opacity: 1;
            transform: scale(1.05);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-color: rgba(0,0,0,0.1);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(2px);
        }
        
        .modal-card {
            background: white;
            width: 400px;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header { font-size: 1.2rem; font-weight: bold; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
        .modal-body { margin-bottom: 20px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; }

        .btn-modal-cancel { padding: 8px 16px; border: 1px solid var(--border); background: white; border-radius: 6px; cursor: pointer; }
        .btn-modal-confirm { padding: 8px 16px; border: none; color: white; border-radius: 6px; cursor: pointer; font-weight: bold; }


        /* --- PROGRESS STEPPER (VISUAL DESIGN) --- */
        .progressbar-wrapper {
            margin: 20px 0 30px 0;
            position: relative;
        }

        .progressbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .progress-line-bg {
            position: absolute;
            top: 20px; /* Half of circle height (40px) */
            left: 0;
            width: 100%;
            height: 4px;
            background-color: var(--step-inactive);
            z-index: 0;
            border-radius: 2px;
        }

        .progress-line-fill {
            position: absolute;
            top: 20px;
            left: 0;
            width: 0%; /* JS will change this */
            height: 4px;
            background-color: var(--primary);
            z-index: 0;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .step-item {
            text-align: center;
            position: relative;
            z-index: 1;
            cursor: pointer;
            width: 80px; /* Ensure click area */
        }

        .step-circle {
            width: 40px;
            height: 40px;
            background-color: var(--step-inactive);
            color: var(--text-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px auto;
            font-size: 1.1rem;
            border: 3px solid white; /* Creates gap around line */
            transition: 0.3s;
        }

        .step-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-light);
            transition: 0.3s;
        }

        /* Active State (The current step) */
        .step-item.active .step-circle {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 0 0 3px #ccfbf1; /* Outer Glow */
        }
        .step-item.active .step-label {
            color: var(--primary);
            font-weight: bold;
        }

        /* Completed State (Past steps) */
        .step-item.completed .step-circle {
            background-color: var(--primary);
            color: white;
        }
        .step-item.completed .step-label {
            color: var(--primary);
        }

        /* --- STEP PANES --- */
        .step-pane { display: none; animation: fadeIn 0.4s ease; }
        .step-pane.active-pane { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

        .btn-nav { padding: 10px 20px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; transition: 0.2s; }
        .btn-prev { background: #e2e8f0; color: var(--text-dark); }
        .btn-prev:hover { background: #cbd5e1; }
        .btn-next-step { background: var(--primary); color: white; }
        .btn-next-step:hover { background: #0d9488; }
        .form-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--border); }

    </style>
</head>
<body>

    <!-- MODAL -->
    <div id="actionModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header" id="modalTitle">
                <!-- Title Injected via JS -->
            </div>
            <div class="modal-body">
                <div style="background: #f1f5f9; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-weight: bold; text-align: center; border: 1px dashed #cbd5e1;">
                    Ticket: <span id="modalTicketId" style="font-size: 1.2rem; color: var(--primary);">C-XXX</span>
                </div>
                <label class="form-label">Remarks / Reason</label>
                <textarea id="modalRemarks" class="form-input" style="height: 100px; resize: none;" placeholder="Enter details here..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-cancel" onclick="closeModal()">Back</button>
                <button id="modalConfirmBtn" class="btn-modal-confirm" onclick="confirmModalAction()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-user-nurse"></i> MedFlow</div>
        <nav>
            <a onclick="showDashboard()" class="nav-item active" id="nav-triage">
                <i class="fas fa-notes-medical"></i> Clinical Workflow
            </a>
            
            <div id="managedTicketsWrapper">
                <div class="nav-item dropdown-toggle" onclick="toggleDropdown()">
                    <div style="display:flex; align-items:center; gap:15px;">
                        <i class="fas fa-tasks"></i> Managed Tickets
                    </div>
                    <i class="fas fa-chevron-down chevron" id="ticketChevron"></i>
                </div>
                
                <div class="dropdown-menu" id="ticketDropdown">
                    <a onclick="loadTable('completed')" class="nav-sub-item"><i class="fas fa-check-circle"></i> Completed Tickets</a>
                    <a onclick="loadTable('pending')" class="nav-sub-item"><i class="fas fa-clock"></i> Pending Tickets</a>
                    <a onclick="loadTable('skipped')" class="nav-sub-item"><i class="fas fa-forward"></i> Skipped Tickets</a>
                    <a onclick="loadTable('cancelled')" class="nav-sub-item"><i class="fas fa-ban"></i> Cancelled Tickets</a>
                </div>
            </div>

            <a href="#" class="nav-item"><i class="fas fa-search"></i> Patient Search</a>
        </nav>
    </aside>

    <!-- MAIN AREA -->
    <main class="main">
        <header>
            <div class="station-indicator"><i class="fas fa-clinic-medical"></i> General Station A</div>
            
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
                
                <!-- LEFT: MULTI-STEP FORM -->
                <div class="left-col">
                    <div class="card">
                        
                        <!-- Header Info -->
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                            <h2 style="margin:0; color:var(--primary);">Patient Evaluation</h2>
                            <div style="display:flex; gap: 10px;">
                                <span style="background:#e0f2fe; color:#0284c7; padding:6px 12px; border-radius:20px; font-size:0.85rem; font-weight:bold; display:flex; align-items:center; gap:5px;">
                                    <i class="fas fa-ticket-alt"></i> Ticket: <span id="currentTicket">C-104</span>
                                </span>
                            </div>
                        </div>

                        <!-- PROGRESS BAR (VISUAL) -->
                        <div class="progressbar-wrapper">
                            <div class="progress-line-bg"></div>
                            <div class="progress-line-fill" id="progressFill"></div>

                            <div class="progressbar">
                                <!-- Step 1: Triage -->
                                <div class="step-item active" id="step-node-1" onclick="goToStep(1)">
                                    <div class="step-circle"><i class="fas fa-notes-medical"></i></div>
                                    <div class="step-label">Triage</div>
                                </div>
                                <!-- Step 2: Registration -->
                                <div class="step-item" id="step-node-2" onclick="goToStep(2)">
                                    <div class="step-circle"><i class="fas fa-user"></i></div>
                                    <div class="step-label">Registration</div>
                                </div>
                                <!-- Step 3: Payment -->
                                <div class="step-item" id="step-node-3" onclick="goToStep(3)">
                                    <div class="step-circle"><i class="fas fa-credit-card"></i></div>
                                    <div class="step-label">Payment</div>
                                </div>
                                <!-- Step 4: Consultation -->
                                <div class="step-item" id="step-node-4" onclick="goToStep(4)">
                                    <div class="step-circle"><i class="fas fa-user-md"></i></div>
                                    <div class="step-label">Consultation</div>
                                </div>
                            </div>
                        </div>

                        <!-- --- FORM CONTENT PANES --- -->

                        <!-- PANE 1: TRIAGE -->
                        <div id="pane-1" class="step-pane active-pane">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                    <h2 style="margin:0; color:var(--primary);">Triage Screening Form</h2>
                    <div style="display:flex; gap: 10px;">
                        <span style="background:#e0f2fe; color:#0284c7; padding:6px 12px; border-radius:20px; font-size:0.85rem; font-weight:bold; display:flex; align-items:center; gap:5px;">
                            <i class="fas fa-ticket-alt"></i> Ticket: <span id="currentTicket">C-104</span>
                        </span>
                        <span style="background:#d1fae5; color:#047857; padding:6px 12px; border-radius:20px; font-size:0.85rem; font-weight:bold; display:flex; align-items:center; gap:5px;">
                            <i class="fas fa-user-tag"></i> <span>New Patient</span>
                        </span>
                    </div>
                </div>
  <div class="section-title"><i class="fas fa-user"></i> Patient Demographics</div>
                        <div class="form-grid-3">
                            <div><label class="form-label">First Name</label><input type="text" class="form-input" id="fname" placeholder="Ex: Juan"></div>
                            <div><label class="form-label">Middle Initial</label><input type="text" class="form-input" id="mname" placeholder="Ex: D."></div>
                            <div><label class="form-label">Last Name</label><input type="text" class="form-input" id="lname" placeholder="Ex: Dela Cruz"></div>
                        </div>

                        <div class="form-grid-3">
                            <div><label class="form-label">Date of Birth</label><input type="date" class="form-input" id="dob" onchange="calculateAge()"></div>
                            <div><label class="form-label">Age (Auto)</label><input type="text" class="form-input" id="age" readonly style="background:#e2e8f0;"></div>
                            <div><label class="form-label">Sex</label><select class="form-input"><option>Male</option><option>Female</option></select></div>
                        </div>

                        <div class="form-grid-2">
                            <div><label class="form-label">Civil Status</label><select class="form-input"><option>Single</option><option>Married</option><option>Widowed</option></select></div>
                            <div><label class="form-label">Contact No.</label><input type="text" class="form-input" placeholder="0912-345-6789"></div>
                        </div>

                        <div style="margin-bottom:15px;">
                            <label class="form-label">Complete Address</label>
                            <input type="text" class="form-input" placeholder="House No, Street, Barangay, City/Municipality">
                        </div>
                            <div class="section-title"><i class="fas fa-heartbeat"></i> Vital Signs</div>
                            <div class="form-grid-3">

                                <div><label class="form-label">Blood Pressure</label><input type="text" class="form-input" placeholder="120/80"></div>
                                <div><label class="form-label">Temperature (°C)</label><input type="text" class="form-input" placeholder="36.5"></div>
                                <div><label class="form-label">Weight (kg)</label><input type="text" class="form-input" placeholder="65"></div>
                            </div>

                           <div class="section-title"><i class="fas fa-clipboard-check"></i> Screening: In the past 1 Week...</div>
                        <div class="checklist-container">
                            <div class="subsection-header">A. Symptoms</div>
                            <div class="checklist-item"><span class="checklist-question">1. Fever (Temperature > 38°C)?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="sym_fever" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="sym_fever" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">2. Cough and/or Colds?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="sym_cough" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="sym_cough" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">3. Sore Throat / Throat Pain?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="sym_throat" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="sym_throat" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">4. Shortness of Breath / Difficulty Breathing?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="sym_sob" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="sym_sob" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">5. Influenza-like symptoms (Headache, Muscle pain, Diarrhea, Loss of Taste/Smell)?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="sym_flu" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="sym_flu" value="no" checked> No</label></div></div>

                            <div class="subsection-header">B. Exposure History</div>
                            <div class="checklist-item"><span class="checklist-question">1. History of confirmed COVID-19 Infection?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="hist_covid" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="hist_covid" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">2. Travel/Residence in area with local transmission?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="hist_travel" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="hist_travel" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">3. Contact with someone with recent travel to infected areas?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="hist_contact" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="hist_contact" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">4. Are they any influenza-like illness during the weekn in your workplace?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="hist_travel" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="hist_travel" value="no" checked> No</label></div></div>
                            <div class="checklist-item"><span class="checklist-question">5. Did you have direct contact with suspected or confirmed case?</span><div class="radio-group"><label class="radio-label"><input type="radio" name="hist_contact" value="yes"> Yes</label><label class="radio-label"><input type="radio" name="hist_contact" value="no" checked> No</label></div></div>
                            
                            <div class="subsection-header">C. Medication Intake (Prior to consultation)</div>
                            <div class="checklist-item" style="flex-direction: column; align-items: flex-start;">
                                <span class="checklist-question" style="margin-bottom: 8px;">Did you take any Antipyretics/Pain Killers? (Check all that apply)</span>
                                <div class="radio-group" style="flex-wrap: wrap;">
                                    <label class="radio-label"><input type="checkbox" name="meds" value="paracetamol"> Paracetamol</label>
                                    <label class="radio-label"><input type="checkbox" name="meds" value="ibuprofen"> Ibuprofen</label>
                                    <label class="radio-label"><input type="checkbox" name="meds" value="naproxen"> Naproxen</label>
                                    <input type="text" class="inline-input" placeholder="Others (Specify)">
                                </div>
                            </div>

                            <div class="subsection-header">D. Medical History</div>
                               <div class="checklist-item" style="flex-direction: column; align-items: flex-start;">
                            <span class="checklist-question" style="margin-bottom: 5px;">1. List Pre-existing conditions (Comorbidities):</span>
                            <input type="text" class="form-input" placeholder="e.g., Hypertension, Diabetes, Asthma...">
                        </div>
                            <div class="checklist-item" style="flex-direction: column; align-items: flex-start;">
                            <span class="checklist-question" style="margin-bottom: 5px;">2. if yes, please specify date of admission:</span>
                            <input type="text" class="form-input" placeholder="e.g., Hypertension, Diabetes, Asthma...">
                        </div>
                        <div class="checklist-item">
                            <span class="checklist-question">3. Were you admitted recently due to these conditions?</span>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="hist_admit" value="yes"> Yes</label>
                                <label class="radio-label"><input type="radio" name="hist_admit" value="no" checked> No</label>
                            </div>
                        </div>

                        <div class="checklist-item">
                            <span class="checklist-question">4. History of ICU Admission?</span>
                            <div class="radio-group">
                                <label class="radio-label"><input type="radio" name="hist_icu" value="yes"> Yes</label>
                                <label class="radio-label"><input type="radio" name="hist_icu" value="no" checked> No</label>
                            </div>
                        </div>
                        </div>

                        <div style="margin-top:15px;">
                            <label class="form-label">Additional Triage Notes / Chief Complaint</label>
                            <textarea class="form-input" style="height:80px;" placeholder="Patient complains of..."></textarea>
                        </div>

                     
                            <div class="form-footer">
                                <button class="btn-nav btn-next-step" onclick="goToStep(2)">Next Step <i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <!-- PANE 2: REGISTRATION -->
                        <div id="pane-2" class="step-pane">
                            <div class="section-title"><i class="fas fa-user"></i> Patient Registration</div>
                            <div class="form-grid-3">
                                <div><label class="form-label">First Name</label><input type="text" class="form-input" id="fname" placeholder="Ex: Juan"></div>
                                <div><label class="form-label">Middle Initial</label><input type="text" class="form-input" id="mname" placeholder="Ex: D."></div>
                                <div><label class="form-label">Last Name</label><input type="text" class="form-input" id="lname" placeholder="Ex: Dela Cruz"></div>
                            </div>

                            <div class="form-grid-3">
                                <div><label class="form-label">Date of Birth</label><input type="date" class="form-input" id="dob" onchange="calculateAge()"></div>
                                <div><label class="form-label">Age</label><input type="text" class="form-input" id="age" readonly style="background:#e2e8f0;"></div>
                                <div><label class="form-label">Sex</label><select class="form-input"><option>Male</option><option>Female</option></select></div>
                            </div>

                            <div class="form-grid-2">
                                <div><label class="form-label">Contact Number</label><input type="text" class="form-input" placeholder="0912-345-6789"></div>
                                <div><label class="form-label">Civil Status</label><select class="form-input"><option>Single</option><option>Married</option></select></div>
                            </div>

                            <div class="form-footer">
                                <button class="btn-nav btn-prev" onclick="goToStep(1)">Back</button>
                                <button class="btn-nav btn-next-step" onclick="goToStep(3)">Next Step <i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <!-- PANE 3: PAYMENT -->
                        <div id="pane-3" class="step-pane">
                            <div class="section-title"><i class="fas fa-file-invoice-dollar"></i> Billing & Payment</div>
                            
                            <div class="form-grid-2">
                                <div>
                                    <label class="form-label">Payment Category</label>
                                    <select class="form-input">
                                        <option value="self">Self-Pay / Cash</option>
                                        <option value="hmo">HMO / Insurance</option>
                                    </select>
                                </div>
                                <div><label class="form-label">HMO Provider (If applicable)</label><input type="text" class="form-input" placeholder="Maxicare, Philhealth..."></div>
                            </div>

                            <div style="background:#f0fdf4; border:1px dashed #16a34a; padding:15px; border-radius:8px; margin: 15px 0;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                    <span>Consultation Fee</span>
                                    <b>₱ 500.00</b>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                    <span>Triage Supplies</span>
                                    <b>₱ 50.00</b>
                                </div>
                                <hr style="border-top:1px solid #bbf7d0; margin:10px 0;">
                                <div style="display:flex; justify-content:space-between; font-size:1.1rem; color:#15803d; font-weight:bold;">
                                    <span>TOTAL DUE</span>
                                    <span>₱ 550.00</span>
                                </div>
                            </div>

                            <div class="form-footer">
                                <button class="btn-nav btn-prev" onclick="goToStep(2)">Back</button>
                                <button class="btn-nav btn-next-step" onclick="goToStep(4)">Next Step <i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <!-- PANE 4: CONSULTATION -->
                        <div id="pane-4" class="step-pane">
                            <div class="section-title"><i class="fas fa-user-md"></i> Doctor's Consultation</div>
                            
                            <div class="form-grid-2">
                                <div>
                                    <label class="form-label">Subjective (History)</label>
                                    <textarea class="form-input" style="height:100px; resize:none;"></textarea>
                                </div>
                                <div>
                                    <label class="form-label">Objective (Findings)</label>
                                    <textarea class="form-input" style="height:100px; resize:none;"></textarea>
                                </div>
                            </div>

                            <div style="margin-bottom:15px;">
                                <label class="form-label">Assessment / Diagnosis</label>
                                <input type="text" class="form-input" placeholder="ICD-10 Code or Description">
                            </div>

                            <div style="margin-bottom:15px;">
                                <label class="form-label">Plan / Prescription</label>
                                <textarea class="form-input" style="height:80px; resize:none;"></textarea>
                            </div>

                            <div class="form-footer">
                                <button class="btn-nav btn-prev" onclick="goToStep(3)">Back</button>
                                <button class="btn-nav btn-submit" onclick="submitCompleteWorkflow()" style="margin-top:0;">Complete Cycle <i class="fas fa-check"></i></button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT: INCOMING QUEUE -->
                <div class="right-col">
                    <div class="card" style="padding:0; overflow:hidden; height:100%;">
                        <div style="padding:20px; background:#f8fafc; border-bottom:1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h3 style="color:var(--text-dark); margin: 0;">Incoming Patients</h3>
                                <p style="font-size:0.8rem; color:var(--text-light); margin: 0;">Select to Triage</p>
                            </div>

                            <div style="display: flex; gap: 8px;">
                                <div id="tab-priority" onclick="switchQueueTab('priority')" class="queue-tab" style="background: #fee2e2; color: #b91c1c; font-weight: bold; padding: 5px 12px; border-radius: 8px; font-size: 0.85rem; display:flex; align-items:center; gap:5px;">
                                    <i class="fas fa-exclamation-circle"></i> 4 Priorities
                                </div>

                                <div id="tab-pending" onclick="switchQueueTab('pending')" class="queue-tab active-tab" style="background: #e0f2fe; color: #0369a1; font-weight: bold; padding: 5px 12px; border-radius: 8px; font-size: 0.85rem; display:flex; align-items:center; gap:5px;">
                                    <i class="fas fa-user-clock"></i> 12 Pending
                                </div>
                            </div>
                        </div>
                        
                        <div id="incomingList" style="overflow-y:auto; height:calc(100% - 80px);">
                            <div id="list-pending">
                                <div class="queue-item active" onclick="selectPatient(this, 'C-104', 'Maria Clara')">
                                    <div><div style="font-weight:bold;">C-104 (Maria Clara)</div><div style="font-size:0.85rem; color:#64748b;">Waiting: 5m</div></div>
                                    <div style="text-align:right;"><div style="font-size:0.85rem; font-weight:bold; color:var(--primary);">Walk-In</div></div>
                                </div>
                                <div class="queue-item" onclick="selectPatient(this, 'C-105', 'Jose Rizal')">
                                    <div><div style="font-weight:bold;">C-105 (Jose Rizal)</div><div style="font-size:0.85rem; color:#64748b;">Waiting: 2m</div></div>
                                    <div style="text-align:right;"><div style="font-size:0.85rem; font-weight:bold; color:var(--primary);">Walk-In</div></div>
                                </div>
                                <div class="queue-item" onclick="selectPatient(this, 'C-106', 'Andres B.')">
                                    <div><div style="font-weight:bold;">C-106 (Andres B.)</div><div style="font-size:0.85rem; color:#64748b;">Waiting: 1m</div></div>
                                    <div style="text-align:right;"><div style="font-size:0.85rem; font-weight:bold; color:var(--primary);">Walk-In</div></div>
                                </div>
                            </div>

                            <div id="list-priority" style="display: none;">
                                <div class="queue-item" style="border-left: 4px solid var(--danger); background: #fef2f2;" onclick="selectPatient(this, 'P-001', 'Elderly Patient')">
                                    <div><div style="font-weight:bold; color:#b91c1c;">P-001 (Senior)</div><div style="font-size:0.85rem; color:#64748b;">Waiting: 15m</div></div>
                                    <div style="text-align:right;"><div style="font-size:0.85rem; font-weight:bold; color:var(--danger);">Priority</div></div>
                                </div>
                            </div>

                            <div class="queue-footer">
                                <div class="queue-btn-grid">
                                    <button class="q-btn btn-call" onclick="queueAction('call')"><i class="fas fa-bullhorn"></i> Call</button>
                                    <button class="q-btn btn-skip" onclick="openActionModal('skip')"><i class="fas fa-forward"></i> Skip</button>
                                    <button class="q-btn btn-cancel" onclick="openActionModal('cancel')"><i class="fas fa-ban"></i> Cancel</button>
                                    <button class="q-btn btn-next" onclick="queueAction('next')">Next <i class="fas fa-chevron-right"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- VIEW 2: MANAGED TICKETS TABLE -->
        <div id="view-table">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 id="tableTitle" style="color:var(--text-dark);">Managed Tickets</h2>
                <div style="color:var(--text-light); font-size:0.9rem;">Real-time updates</div>
            </div>
            
            <table class="med-table">
                <thead id="tableHead">
                    <!-- HEADERS INJECTED VIA JS -->
                </thead>
                <tbody id="tableBody">
                    <!-- ROWS INJECTED VIA JS -->
                </tbody>
            </table>
        </div>

    </main>

    <script>
        // === STEPPER LOGIC ===
        const totalSteps = 4;
        
        function goToStep(step) {
            // 1. Hide all panes
            document.querySelectorAll('.step-pane').forEach(el => el.classList.remove('active-pane'));
            // 2. Show target pane
            document.getElementById('pane-' + step).classList.add('active-pane');

            // 3. Update Visual Stepper
            updateStepperVisuals(step);
        }

        function updateStepperVisuals(currentStep) {
            // Update line fill width (Steps: 1=0%, 2=33%, 3=66%, 4=100%)
            const percent = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressFill').style.width = percent + '%';

            for(let i = 1; i <= totalSteps; i++) {
                const node = document.getElementById('step-node-' + i);
                const circle = node.querySelector('.step-circle');
                
                // Reset classes
                node.classList.remove('active', 'completed');
                
                if (i < currentStep) {
                    // Previous steps -> Completed (Checkmark)
                    node.classList.add('completed');
                    circle.innerHTML = '<i class="fas fa-check"></i>';
                } else if (i === currentStep) {
                    // Current step -> Active
                    node.classList.add('active');
                    // Restore original icon
                    const icons = ['fa-notes-medical', 'fa-user', 'fa-credit-card', 'fa-user-md'];
                    circle.innerHTML = `<i class="fas ${icons[i-1]}"></i>`;
                } else {
                    // Future steps -> Inactive
                    const icons = ['fa-notes-medical', 'fa-user', 'fa-credit-card', 'fa-user-md'];
                    circle.innerHTML = `<i class="fas ${icons[i-1]}"></i>`;
                }
            }
        }

        // === UPDATED DATA ===
        const database = {
            'completed': [
                { token: 'C-099', name: 'Emilio Aguinaldo', address: 'Kawit, Cavite', timeGen: '09:50 AM', timeFin: '10:10 AM', status: 'Completed', bp: '120/80', temp: '36.5', weight: '70' },
                { token: 'C-100', name: 'Apolinario Mabini', address: 'Tanauan, Batangas', timeGen: '10:05 AM', timeFin: '10:25 AM', status: 'Completed', bp: '130/85', temp: '36.8', weight: '62' },
            ],
            'pending': [
                { token: 'C-104', name: 'Maria Clara', address: 'San Diego, Laguna', timeGen: '10:55 AM', timeFin: '-', status: 'Pending' },
                { token: 'C-105', name: 'Jose Rizal', address: 'Calamba, Laguna', timeGen: '11:03 AM', timeFin: '-', status: 'Pending' },
                { token: 'C-106', name: 'Andres Bonifacio', address: 'Tondo, Manila', timeGen: '11:09 AM', timeFin: '-', status: 'Pending' },
            ],
            'skipped': [
                { token: 'C-102', name: 'Melchora Aquino', address: 'Caloocan City', timeGen: '10:30 AM', timeFin: '10:45 AM', status: 'Skipped', bp: '140/90', temp: '37.2', weight: '60', remarks: 'Patient not responding' },
            ],
            'cancelled': [
                { token: 'C-098', name: 'Gabriela Silang', address: 'Santa, Ilocos Sur', timeGen: '09:30 AM', timeFin: '09:50 AM', status: 'Cancelled', bp: '120/80', temp: '36.6', weight: '55', remarks: 'Duplicate entry' },
            ]
        };

        // === TAB SWITCHING LOGIC (QUEUE) ===
        function switchQueueTab(type) {
            const listPending = document.getElementById('list-pending');
            const listPriority = document.getElementById('list-priority');
            const tabPending = document.getElementById('tab-pending');
            const tabPriority = document.getElementById('tab-priority');

            if(type === 'pending') {
                listPending.style.display = 'block';
                listPriority.style.display = 'none';
                tabPending.classList.add('active-tab');
                tabPriority.classList.remove('active-tab');
            } else {
                listPending.style.display = 'none';
                listPriority.style.display = 'block';
                tabPriority.classList.add('active-tab');
                tabPending.classList.remove('active-tab');
            }
        }

        // === MODAL LOGIC ===
        let currentModalAction = '';
        function openActionModal(action) {
            const ticket = document.getElementById('currentTicket').innerText;
            const modal = document.getElementById('actionModal');
            const titleEl = document.getElementById('modalTitle');
            const btn = document.getElementById('modalConfirmBtn');
            const remarks = document.getElementById('modalRemarks');
            
            remarks.value = '';
            document.getElementById('modalTicketId').innerText = ticket;
            currentModalAction = action;

            if(action === 'skip') {
                titleEl.innerHTML = '<i class="fas fa-forward" style="color:var(--warning)"></i> Skip Patient';
                btn.style.backgroundColor = 'var(--warning)';
                btn.innerText = 'Confirm Skip';
            } else if (action === 'cancel') {
                titleEl.innerHTML = '<i class="fas fa-ban" style="color:var(--danger)"></i> Cancel Ticket';
                btn.style.backgroundColor = 'var(--danger)';
                btn.innerText = 'Confirm Cancel';
            }
            modal.style.display = 'flex';
        }

        function closeModal() { document.getElementById('actionModal').style.display = 'none'; }

        function confirmModalAction() {
            const remarks = document.getElementById('modalRemarks').value;
            if(remarks.trim() === "") { alert("Please enter a remark or reason."); return; }
            alert(`Confirmed: ${currentModalAction.toUpperCase()}`);
            closeModal();
            const activeItem = document.querySelector('.queue-item.active');
            if(activeItem) activeItem.remove();
        }

        // === TABLE LOGIC ===
        function loadTable(category) {
            document.getElementById('view-dashboard').style.display = 'none';
            document.getElementById('view-table').style.display = 'block';
            document.getElementById('nav-triage').classList.remove('active');
            
            const titles = { 'completed': 'Completed Tickets', 'pending': 'Pending Queue', 'skipped': 'Skipped Patients', 'cancelled': 'Cancelled Tickets' };
            document.getElementById('tableTitle').innerText = titles[category];

            const thead = document.getElementById('tableHead');
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';
            
            let headers = category === 'pending' ? 
                `<tr><th>Token</th><th>Name</th><th>Address</th><th>Time</th><th>Status</th></tr>` :
                `<tr><th>Token</th><th>Name</th><th>Address</th><th>Time</th><th>Details</th><th>Status</th></tr>`;
            thead.innerHTML = headers;

            const data = database[category] || [];
            if(data.length === 0) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No records.</td></tr>'; return; }

            data.forEach(row => {
                let tr = `<tr><td><strong>${row.token}</strong></td><td>${row.name}</td><td>${row.address}</td><td>${row.timeGen}</td>`;
                if(category !== 'pending') tr += `<td>${row.bp ? row.bp : '-'}</td>`;
                tr += `<td><span class="status-badge badge-${category}">${row.status}</span></td></tr>`;
                tbody.innerHTML += tr;
            });
        }

        // === GENERAL FUNCTIONS ===
        function showDashboard() {
            document.getElementById('view-dashboard').style.display = 'block';
            document.getElementById('view-table').style.display = 'none';
            document.getElementById('nav-triage').classList.add('active');
        }

        function toggleDropdown() { document.getElementById('ticketDropdown').classList.toggle('show'); }

        function calculateAge() {
            const dob = new Date(document.getElementById('dob').value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            if (today.getMonth() < dob.getMonth() || (today.getMonth() === dob.getMonth() && today.getDate() < dob.getDate())) age--;
            document.getElementById('age').value = age;
        }

        function selectPatient(element, ticketId, name) {
            document.querySelectorAll('.queue-item').forEach(i => i.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('currentTicket').innerText = ticketId;
            // Clear inputs and reset to Step 1
            document.querySelectorAll('input, textarea').forEach(i => i.value = '');
            goToStep(1);
        }

        function submitCompleteWorkflow() {
            const ticket = document.getElementById('currentTicket').innerText;
            if(confirm(`Complete entire workflow for ticket ${ticket}?`)) {
                alert("Patient cycle completed successfully.");
                const activeItem = document.querySelector('.queue-item.active');
                if(activeItem) activeItem.remove();
                goToStep(1);
            }
        }
        
        function queueAction(type) { if(type) alert(type + " action triggered."); }
    </script>
</body>
</html>