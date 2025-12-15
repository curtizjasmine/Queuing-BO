<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedFlow | Queue Display</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f172a;
            color: white;
            height: 100vh;
            overflow: hidden;
            display: grid;
            grid-template-rows: 80px 1fr 100px;
        }

        /* HEADER */
        header {
            background: #0f766e;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .brand { font-size: 2rem; font-weight: bold; letter-spacing: 1px; }
        .clock { font-size: 1.5rem; font-family: monospace; }

        /* MAIN CONTENT */
        .display-area {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            padding: 40px;
            gap: 40px;
        }

        /* LEFT: NOW SERVING */
        .now-serving {
            background: white;
            color: #1e293b;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }

        .now-serving-label { font-size: 2.5rem; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 20px; }
        .ticket-number { font-size: 10rem; font-weight: 800; color: #0f766e; line-height: 1; }
        .station-name { font-size: 3rem; font-weight: 700; color: #0ea5e9; margin-top: 20px; }

        /* FLASH ANIMATION FOR "CALL AGAIN" */
        @keyframes flash {
            0% { background-color: white; }
            50% { background-color: #fef08a; } /* Yellow flash */
            100% { background-color: white; }
        }
        .blink { animation: flash 0.5s 3; }

        /* RIGHT: UP NEXT */
        .up-next-container {
            background: #1e293b;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #334155;
        }
        .next-title { font-size: 1.5rem; color: #94a3b8; border-bottom: 2px solid #334155; padding-bottom: 15px; margin-bottom: 20px; }
        
        .next-item {
            display: flex;
            justify-content: space-between;
            font-size: 2rem;
            padding: 15px 0;
            border-bottom: 1px solid #334155;
            color: #e2e8f0;
        }

        /* FOOTER */
        footer {
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #94a3b8;
        }
        .scrolling-text {
            width: 100%;
            overflow: hidden;
            white-space: nowrap;
        }
        .scrolling-text span {
            display: inline-block;
            padding-left: 100%;
            animation: scroll 20s linear infinite;
        }
        @keyframes scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }

    </style>
</head>
<body>

    <header>
        <div class="brand"><i class="fas fa-hospital-user"></i> MedFlow Queue</div>
        <div class="clock" id="clock">00:00:00</div>
    </header>

    <div class="display-area">
        <!-- NOW SERVING CARD -->
        <div class="now-serving" id="nowServingCard">
            <div class="now-serving-label">Now Serving</div>
            <div class="ticket-number" id="displayTicket">---</div>
            <div class="station-name" id="displayStation">Please Wait</div>
        </div>

        <!-- UP NEXT LIST -->
        <div class="up-next-container">
            <div class="next-title">Up Next</div>
            <div class="next-item">
                <span>C-105</span> <span>Preparing...</span>
            </div>
            <div class="next-item">
                <span>C-106</span> <span>Waiting</span>
            </div>
            <div class="next-item">
                <span>C-107</span> <span>Waiting</span>
            </div>
        </div>
    </div>

    <footer>
        <div class="scrolling-text">
            <span>Welcome to MedFlow. Please observe silence. Please prepare your ID and Health Card. If you are experiencing difficulty breathing, please inform the staff immediately.</span>
        </div>
    </footer>

    <script>
        // === CLOCK ===
        setInterval(() => {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString();
        }, 1000);

        // === LISTENER FOR TRIAGE STATION ===
        window.addEventListener('storage', (event) => {
            if (event.key === 'medflow_current_ticket') {
                const data = JSON.parse(event.newValue);
                updateDisplay(data);
            }
        });

        function updateDisplay(data) {
            const ticketEl = document.getElementById('displayTicket');
            const stationEl = document.getElementById('displayStation');
            const card = document.getElementById('nowServingCard');

            // Update Text
            ticketEl.innerText = data.ticket;
            stationEl.innerText = data.station;

            // Visual Cue for "Call Again"
            if (data.action === 'call') {
                // Remove class to reset animation if already running
                card.classList.remove('blink');
                void card.offsetWidth; // Trigger reflow
                card.classList.add('blink');
                
                // Optional: Add a simple beep sound
                playDing();
            }
        }

        // Simple Beep (Optional - browsers might block auto-audio without interaction)
        function playDing() {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.value = 500;
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 1);
            setTimeout(() => osc.stop(), 1000);
        }

        // Check for existing data on load
        const existing = localStorage.getItem('medflow_current_ticket');
        if(existing) {
            updateDisplay(JSON.parse(existing));
        }
    </script>
</body>
</html>