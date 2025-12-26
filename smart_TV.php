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

        /* --- OVERLAY FOR AUTOPLAY POLICY (HIDDEN BY DEFAULT) --- */
        #start-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 9999;
            display: none; /* Hidden by default */
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

    <!-- CLICK TO START OVERLAY (Hidden by default, shows only if autostart fails) -->
    <div id="start-overlay" onclick="forceStartSystem()">
        <h1>Queue Display</h1>
        <p>Audio permission required</p>
        <button>CLICK TO START</button>
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
        <video id="queueVideo" autoplay muted playsinline loop></video>
    </div>

    <script>
        // --- 1. SYSTEM STARTUP ---
        async function startSystem() {
            console.log("Starting system automatically...");
            
            // Hide overlay if it's visible
            document.getElementById('start-overlay').style.display = 'none';
            
            try {
                // Try to unlock audio by playing silent audio
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                // Create a silent audio buffer
                const buffer = audioContext.createBuffer(1, 1, 22050);
                const source = audioContext.createBufferSource();
                source.buffer = buffer;
                source.connect(audioContext.destination);
                
                // Play the silent sound
                if (audioContext.state === 'suspended') {
                    await audioContext.resume();
                }
                source.start(0);
                
                // Try to unmute and play video
                const video = document.getElementById('queueVideo');
                if (video.muted) {
                    video.muted = false;
                }
                
                // Try to play video with sound
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(error => {
                        console.log("Video autoplay with sound prevented, keeping muted");
                        video.muted = true;
                        video.play();
                    });
                }
                
                console.log("Audio context unlocked successfully");
                audioContext.close();
                
            } catch (error) {
                console.warn("Audio unlock failed, showing overlay:", error);
                // If audio unlock fails, show the overlay for manual start
                document.getElementById('start-overlay').style.display = 'flex';
                document.getElementById('start-overlay').querySelector('p').textContent = 
                    "Audio permission required. Click to enable sound.";
            }
        }

        // Manual start fallback
        function forceStartSystem() {
            document.getElementById('start-overlay').style.display = 'none';
            // Force video play
            const video = document.getElementById('queueVideo');
            video.play().catch(e => {
                video.muted = true;
                video.play();
            });
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
        
        // Initialize video
        videoPlayer.src = playlist[0];
        videoPlayer.loop = false; // We'll handle looping through playlist
        
        videoPlayer.addEventListener('ended', () => {
            currentVideoIndex = (currentVideoIndex + 1) % playlist.length;
            videoPlayer.src = playlist[currentVideoIndex];
            videoPlayer.play().catch(e => {
                console.log("Video autoplay prevented, retrying muted");
                videoPlayer.muted = true;
                videoPlayer.play();
            });
        });

        // --- 3. WEBSOCKET LOGIC ---
        const announcementAudio = new Audio('Announcement sound effect.mp3'); 
        announcementAudio.preload = 'auto';
        let conn;

        function connectSocket() {
            // Replace with your WebSocket server URL
            conn = new WebSocket('ws://172.16.15.235:8080');

            conn.onopen = function(e) { 
                console.log("✅ TV Connected to WebSocket"); 
            };
            
            conn.onmessage = function(e) {
                try {
                    const data = JSON.parse(e.data);
                    console.log("Received WebSocket data:", data);
                    
                    if (data.type === 'call') {
                        updateDisplay(data);
                    }
                    
                    // Update all counter displays if available
                    if (data.triage) document.getElementById('triage-display').innerText = data.triage;
                    if (data.registration) document.getElementById('reg-display').innerText = data.registration;
                    if (data.consultation) document.getElementById('consult-display').innerText = data.consultation;
                } catch (err) { 
                    console.error("Invalid JSON:", err); 
                }
            };

            conn.onclose = function(e) { 
                console.log("WebSocket closed, reconnecting in 5 seconds...");
                setTimeout(connectSocket, 5000); 
            };
            
            conn.onerror = function(err) { 
                console.error("WebSocket error:", err); 
                conn.close(); 
            };
        }

        function updateDisplay(data) {
            const ticketNumber = data.ticket;
            const stationName = data.station || "Consultation"; 
            
            // 1. Update Visuals
            const displayElement = document.getElementById('main-ticket-display');
            if(displayElement) displayElement.innerText = ticketNumber;
            
            // Update station title
            const stationTitle = document.getElementById('station-title');
            if(stationTitle) stationTitle.innerText = stationName.toUpperCase();

            // 2. Play Audio, THEN Speak
            announcementAudio.currentTime = 0;
            
            // Try to play announcement sound
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
                    console.warn("Announcement audio blocked. Trying to speak directly.");
                    // If audio fails, speak immediately
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
                void card.offsetWidth; // Trigger reflow
                card.classList.add('blink-active');
                setTimeout(() => card.classList.remove('blink-active'), 5000);
            }
        }

        // --- 4. ROBUST SPEECH LOGIC (FULLY KIOSK OPTIMIZED) ---
        function speakTicket(ticket, station) {
            const sentence = `Ticket number ${ticket}, please proceed to ${station}`;
            
            console.log("Speaking:", sentence);

            // CHECK: Are we running in Fully Kiosk Browser?
            if (typeof fully !== "undefined") {
                // Use Fully Kiosk Native TTS (More reliable on Android)
                try {
                    fully.textToSpeech(sentence);
                    console.log("Speaking via Fully Kiosk Native API");
                    return;
                } catch (e) {
                    console.error("Fully Kiosk TTS failed, falling back to Web API");
                }
            }
            
            // Fallback to Web Speech API
            fallbackSpeak(sentence);
        }

        function fallbackSpeak(text) {
            if (!window.speechSynthesis) {
                console.error("Web Speech API not supported");
                return;
            }
            
            window.speechSynthesis.cancel(); // Stop previous
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9;
            utterance.volume = 1.0;
            utterance.lang = 'en-US';
            
            // Try to find a good voice
            const voices = window.speechSynthesis.getVoices();
            if (voices.length > 0) {
                // Prefer English voices
                const englishVoice = voices.find(v => v.lang.startsWith('en-'));
                if (englishVoice) utterance.voice = englishVoice;
            }
            
            window.speechSynthesis.speak(utterance);
        }

        // --- 5. INITIALIZATION ---
        
        // When page loads, auto-start everything
        window.addEventListener('DOMContentLoaded', () => {
            console.log("DOM loaded, starting system...");
            
            // Connect WebSocket immediately
            connectSocket();
            
            // Try to start video immediately
            const video = document.getElementById('queueVideo');
            video.play().catch(e => {
                console.log("Video autoplay prevented initially:", e);
                video.muted = true;
                video.play();
            });
            
            // Auto-start the system after a brief delay
            setTimeout(() => {
                startSystem();
            }, 500);
        });

        // Also try on window load
        window.addEventListener('load', () => {
            console.log("Window fully loaded");
        });

        // Initialize voices for TTS
        if (window.speechSynthesis) {
            // Wait for voices to load
            speechSynthesis.onvoiceschanged = function() {
                console.log("Voices loaded:", speechSynthesis.getVoices().length);
            };
            
            // Trigger voices loading
            setTimeout(() => {
                const voices = speechSynthesis.getVoices();
                if (voices.length === 0) {
                    console.log("No voices loaded yet, will retry...");
                    // Some browsers need this
                    speechSynthesis.getVoices();
                }
            }, 1000);
        }

        // Emergency fallback: if audio still doesn't work after 3 seconds, show overlay
        setTimeout(() => {
            // Check if we can play audio
            const testAudio = new Audio();
            testAudio.muted = true;
            testAudio.play().then(() => {
                console.log("Audio test passed");
            }).catch(() => {
                console.log("Audio test failed, showing overlay");
                document.getElementById('start-overlay').style.display = 'flex';
            });
        }, 3000);
    </script>
</body>
</html>