<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cyan Queue - TV Display</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Segoe UI', sans-serif; 
            display: flex; 
            height: 100vh; 
            background-color: #000; 
            overflow: hidden; 
            flex-direction: row; 
        }

        :root { 
            --theme-color: #00acc1; 
            --text-color: #ffffff; 
            --sidebar-width: max(300px, 30vw); 
        }

        /* --- OVERLAY FOR AUTOPLAY POLICY --- */
        #start-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
            cursor: pointer;
        }
        #start-overlay h1 { font-size: 3rem; margin-bottom: 20px; }
        #start-overlay p { font-size: 1.5rem; color: #ccc; }
        #start-overlay button {
            padding: 15px 40px;
            font-size: 1.5rem;
            background: var(--theme-color);
            color: white;
            border: none;
            border-radius: 8px;
            margin-top: 20px;
            cursor: pointer;
        }

        /* --- SIDEBAR (Counters) --- */
        .sidebar { 
            width: var(--sidebar-width); 
            background-color: #f4f4f4; 
            padding: 15px; 
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
            z-index: 10; 
            height: 100%;
            overflow-y: auto; 
        }

        .counter-card { 
            border: 3px solid var(--theme-color); 
            border-radius: 12px; 
            overflow: hidden; 
            background-color: white; 
            display: flex; 
            flex-direction: column; 
            transition: transform 0.3s ease; 
            min-height: 80px; 
            flex: 1; 
        }

        #station-card {
            flex: 1.5; 
            border-width: 5px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            order: -1; 
        }

        .card-header { 
            background-color: var(--theme-color); 
            color: var(--text-color); 
            font-size: clamp(1rem, 2vw, 1.5rem); 
            font-weight: 800; 
            text-align: center; 
            padding: 8px 0; 
            text-transform: uppercase; 
            white-space: nowrap;
        }

        .card-body { 
            flex-grow: 1; 
            background-color: #ffffff; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            font-size: clamp(2.5rem, 5vw, 4rem); 
            font-weight: bold; 
            color: #333; 
        }
        
        /* Highlight animation */
        .blink-active { animation: blink 1s infinite alternate; border-color: #ff3d00; }
        @keyframes blink { 
            from { box-shadow: 0 0 10px #ff3d00; } 
            to { box-shadow: 0 0 30px #ff3d00; transform: scale(1.02); } 
        }

        /* --- VIDEO DISPLAY --- */
        .main-display { 
            flex-grow: 1; 
            background-color: #000; 
            position: relative; 
            height: 100%;
            width: calc(100% - var(--sidebar-width));
        }

        video { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        @media screen and (max-width: 900px) {
            body { flex-direction: column-reverse; height: 100vh; }
            .main-display { width: 100%; height: 40vh; flex-grow: 0; }
            .sidebar { width: 100%; height: 60vh; flex-direction: row; flex-wrap: wrap; align-content: flex-start; gap: 10px; padding: 10px; }
            .counter-card { min-height: 100px; flex: 1 1 45%; }
            #station-card { flex: 1 1 100%; min-height: 140px; }
        }
    </style>
</head>
<body>

    <!-- CLICK TO START OVERLAY (Required for Audio/TTS to work reliably) -->
    <div id="start-overlay" onclick="startSystem()">
        <h1>Queue Display</h1>
        <p>Tap anywhere to enable Audio & Speech</p>
        <button>START SYSTEM</button>
    </div>

    <!-- LEFT SIDE: Counters -->
    <div class="sidebar">
        <div class="counter-card" id="station-card">
            <div class="card-header" id="station-title">NOW SERVING</div>
            <div class="card-body" id="main-ticket-display">--</div>
        </div>
        
        <div class="counter-card">
            <div class="card-header">TRIAGE</div>
            <div class="card-body" id="triage-display">--</div>
        </div>
        <div class="counter-card">
            <div class="card-header">REGISTRATION</div>
            <div class="card-body" id="reg-display">--</div>
        </div>
      
        <div class="counter-card">
            <div class="card-header">CONSULTATION</div>
            <div class="card-body" id="consult-display">--</div>
        </div>
    </div>

    <!-- RIGHT SIDE: Video Player -->
    <div class="main-display">
        <!-- Added muted initially, will unmute if needed logic applied later -->
        <video id="queueVideo" autoplay muted playsinline></video>
    </div>

    <script>
        // --- 1. SYSTEM STARTUP ---
        function startSystem() {
            // Remove overlay
            document.getElementById('start-overlay').style.display = 'none';
            
            // Try to play silent audio to unlock iOS/Android audio engine
            const audio = new Audio('Announcement sound effect.mp3');
            audio.play().then(() => {
                audio.pause();
                audio.currentTime = 0;
            }).catch(e => console.log("Audio unlock failed", e));

            // Start Video Sound if needed (optional)
            // document.getElementById('queueVideo').muted = false;
        }

        // --- 2. VIDEO LOGIC ---
        const playlist = [
            "https://videos.pexels.com/video-files/856973/856973-hd_1920_1080_25fps.mp4",
            "https://videos.pexels.com/video-files/3209045/3209045-hd_1920_1080_25fps.mp4", 
            "https://videos.pexels.com/video-files/1409899/1409899-hd_1920_1080_25fps.mp4",
            "https://videos.pexels.com/video-files/3249935/3249935-hd_1920_1080_25fps.mp4"
        ];
        const videoPlayer = document.getElementById('queueVideo');
        let currentVideoIndex = 0;
        
        videoPlayer.src = playlist[0];
        videoPlayer.addEventListener('ended', () => {
            currentVideoIndex = (currentVideoIndex + 1) % playlist.length;
            videoPlayer.src = playlist[currentVideoIndex];
            videoPlayer.play().catch(e => console.log("Video Autoplay prevented:", e));
        });

        // --- 3. WEBSOCKET LOGIC ---
        const announcementAudio = new Audio('Announcement sound effect.mp3'); 
        let conn;

        function connectSocket() {
            conn = new WebSocket('ws://172.16.15.235:8080');

            conn.onopen = function(e) { console.log("✅ TV Connected"); };
            
            conn.onmessage = function(e) {
                try {
                    const data = JSON.parse(e.data);
                    if (data.type === 'call') {
                        updateDisplay(data);
                    }
                } catch (err) { console.error("Invalid JSON:", err); }
            };

            conn.onclose = function(e) { setTimeout(connectSocket, 5000); };
            conn.onerror = function(err) { conn.close(); };
        }

        function updateDisplay(data) {
            const ticketNumber = data.ticket;
            const stationName = data.station || "Consultation"; 
            
            // 1. Update Visuals
            const displayElement = document.getElementById('main-ticket-display');
            if(displayElement) displayElement.innerText = ticketNumber;

            // 2. Play Audio, THEN Speak
            announcementAudio.currentTime = 0;
            const playPromise = announcementAudio.play();

            if (playPromise !== undefined) {
                playPromise
                .then(() => {
                    // Audio started. Wait for it to end.
                    announcementAudio.onended = function() {
                        speakTicket(ticketNumber, stationName);
                    };
                })
                .catch(error => {
                    console.warn("Audio blocked. Trying to speak immediately.");
                    // If audio fails (blocked), speak anyway
                    speakTicket(ticketNumber, stationName);
                });
            } else {
                 // Fallback for older browsers
                 announcementAudio.onended = function() {
                    speakTicket(ticketNumber, stationName);
                 };
            }

            // 3. Animation
            const card = document.getElementById('station-card');
            if(card) {
                card.classList.remove('blink-active'); 
                void card.offsetWidth; 
                card.classList.add('blink-active');
                setTimeout(() => card.classList.remove('blink-active'), 5000);
            }
        }

        // --- 4. ROBUST SPEECH LOGIC (FULLY KIOSK OPTIMIZED) ---
        function speakTicket(ticket, station) {
            const sentence = `Ticket number ${ticket}, please proceed to ${station}`;

            // CHECK: Are we running in Fully Kiosk Browser?
            if (typeof fully !== "undefined") {
                // Use Fully Kiosk Native TTS (More reliable on Android)
                // fully.textToSpeech(text, engine, queue)
                try {
                    fully.textToSpeech(sentence);
                    console.log("Speaking via Fully Kiosk Native API");
                } catch (e) {
                    console.error("Fully Kiosk TTS failed, falling back to Web API");
                    fallbackSpeak(sentence);
                }
            } else {
                // Not Fully Kiosk, use Standard Web API
                fallbackSpeak(sentence);
            }
        }

        function fallbackSpeak(text) {
            window.speechSynthesis.cancel(); // Stop previous
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9;
            utterance.lang = 'en-US';
            window.speechSynthesis.speak(utterance);
        }

        connectSocket();

    </script>
</body>
</html>