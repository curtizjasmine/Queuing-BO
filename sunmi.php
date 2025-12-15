<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Women's Health Center - OPD Queuing System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .service-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .current-number {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(79, 70, 229, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
        }
        
        .print-ticket {
            animation: printAnim 0.5s ease-out;
        }
        
        @keyframes printAnim {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4">
    <!-- Header Section -->
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">CEBU VELEZ GENERAL HOSPITAL</h1>
            <p class="text-xl text-white opacity-90">Outpatient Department Queuing System</p>
        </div>

        <!-- Current Serving Numbers Display -->
        <!-- <div class="bg-white rounded-2xl shadow-2xl p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Currently Serving</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="current-number bg-blue-50 rounded-xl p-4 text-center border-2 border-blue-200">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">PRENATAL</h3>
                    <div class="text-3xl font-bold text-blue-600">P-015</div>
                    <p class="text-xs text-blue-600 mt-1">Next: P-016</p>
                </div>
                <div class="current-number bg-purple-50 rounded-xl p-4 text-center border-2 border-purple-200">
                    <h3 class="text-sm font-medium text-purple-800 mb-2">GYNECOLOGY</h3>
                    <div class="text-3xl font-bold text-purple-600">G-008</div>
                    <p class="text-xs text-purple-600 mt-1">Next: G-009</p>
                </div>
                <div class="current-number bg-pink-50 rounded-xl p-4 text-center border-2 border-pink-200">
                    <h3 class="text-sm font-medium text-pink-800 mb-2">TEEN CLINIC</h3>
                    <div class="text-3xl font-bold text-pink-600">T-012</div>
                    <p class="text-xs text-pink-600 mt-1">Next: T-013</p>
                </div>
                <div class="current-number bg-green-50 rounded-xl p-4 text-center border-2 border-green-200">
                    <h3 class="text-sm font-medium text-green-800 mb-2">WELL WOMAN</h3>
                    <div class="text-3xl font-bold text-green-600">W-006</div>
                    <p class="text-xs text-green-600 mt-1">Next: W-007</p>
                </div>
            </div>
        </div> -->

        <!-- Service Selection Section -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Select Your Service</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Prenatal Service -->
                <div class="service-card bg-blue-100 rounded-xl p-5 text-center cursor-pointer" onclick="selectService('prenatal')">
                    <div class="w-16 h-16 mx-auto mb-3 bg-blue-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-blue-800 mb-1">PRENATAL</h3>
                    <p class="text-sm text-blue-600">Pregnancy care and consultation</p>
                </div>

                <!-- Menopause Service -->
                <div class="service-card bg-purple-100 rounded-xl p-5 text-center cursor-pointer" onclick="selectService('menopause')">
                    <div class="w-16 h-16 mx-auto mb-3 bg-purple-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-purple-800 mb-1">MENOPAUSE</h3>
                    <p class="text-sm text-purple-600">Menopause management</p>
                </div>

                <!-- Gynecology Service -->
                <div class="service-card bg-pink-100 rounded-xl p-5 text-center cursor-pointer" onclick="selectService('gynecology')">
                    <div class="w-16 h-16 mx-auto mb-3 bg-pink-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-8m-8 0H3m2 0h8M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-pink-800 mb-1">GYNECOLOGY</h3>
                    <p class="text-sm text-pink-600">Gynecological services</p>
                </div>

                <!-- Teen Clinic Service -->
                <div class="service-card bg-red-100 rounded-xl p-5 text-center cursor-pointer" onclick="selectService('teen_clinic')">
                    <div class="w-16 h-16 mx-auto mb-3 bg-red-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-red-800 mb-1">TEEN CLINIC</h3>
                    <p class="text-sm text-red-600">Adolescent health services</p>
                </div>

                <!-- Family Planning Service -->
                <div class="service-card bg-green-100 rounded-xl p-5 text-center cursor-pointer" onclick="selectService('family_planning')">
                    <div class="w-16 h-16 mx-auto mb-3 bg-green-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-green-800 mb-1">FAMILY PLANNING</h3>
                    <p class="text-sm text-green-600">Contraception counseling</p>
                </div>

                <!-- VAWC Desk Service -->
                <div class="service-card bg-orange-100 rounded-xl p-5 text-center cursor-pointer" onclick="selectService('vawc_desk')">
                    <div class="w-16 h-16 mx-auto mb-3 bg-orange-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-orange-800 mb-1">VAWC DESK</h3>
                    <p class="text-sm text-orange-600">Violence against women support</p>
                </div>

                <!-- Well Woman Check Up -->
                <div class="service-card bg-teal-100 rounded-xl p-5 text-center cursor-pointer" onclick="selectService('well_woman')">
                    <div class="w-16 h-16 mx-auto mb-3 bg-teal-200 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-teal-800 mb-1">WELL WOMAN</h3>
                    <p class="text-sm text-teal-600">Comprehensive health check</p>
                </div>
            </div>
        </div>

        <!-- Queue Number Display -->
        <div id="queueDisplay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 print-ticket">
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Your Queue Number</h2>
                    <div id="queueNumber" class="text-6xl font-bold mb-4"></div>
                    <div id="serviceName" class="text-xl font-semibold mb-6"></div>
                    <div class="bg-gray-100 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-600 mb-2">Estimated wait time: <span class="font-semibold">15-25 minutes</span></p>
                        <p class="text-sm text-gray-600">Please wait in the designated area</p>
                    </div>
                    <button onclick="printTicket()" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors mr-2">
                        Print Ticket
                    </button>
                    <button onclick="closeQueueDisplay()" class="bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-600 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white opacity-80 mt-12">
            <p>© 2025 VELEZ QUEUING SYSTEM  All rights reserved.</p>
            <p class="text-sm mt-2">For assistance, please approach our information desk.</p>
        </div>
    </div>

    <script>
        // Queue counters for each service
        const queueCounters = {
            prenatal: 15,
            menopause: 7,
            gynecology: 8,
            teen_clinic: 12,
            family_planning: 9,
            vawc_desk: 5,
            well_woman: 6
        };

        // Service prefixes
        const servicePrefixes = {
            prenatal: 'P',
            menopause: 'M',
            gynecology: 'G',
            teen_clinic: 'T',
            family_planning: 'FP',
            vawc_desk: 'V',
            well_woman: 'W'
        };

        // Service colors for styling
        const serviceColors = {
            prenatal: 'blue',
            menopause: 'purple',
            gynecology: 'pink',
            teen_clinic: 'red',
            family_planning: 'green',
            vawc_desk: 'orange',
            well_woman: 'teal'
        };

        function selectService(service) {
            // Increment queue counter
            queueCounters[service]++;
            
            // Generate queue number
            const queueNumber = `${servicePrefixes[service]}-${queueCounters[service].toString().padStart(3, '0')}`;
            
            // Get service display name
            const serviceNames = {
                prenatal: 'Prenatal Care',
                menopause: 'Menopause Management',
                gynecology: 'Gynecology Services',
                teen_clinic: 'Teen Clinic',
                family_planning: 'Family Planning',
                vawc_desk: 'VAWC Desk',
                well_woman: 'Well Woman Check Up'
            };
            
            // Update display
            document.getElementById('queueNumber').textContent = queueNumber;
            document.getElementById('queueNumber').className = `text-6xl font-bold mb-4 text-${serviceColors[service]}-600`;
            document.getElementById('serviceName').textContent = serviceNames[service];
            document.getElementById('serviceName').className = `text-xl font-semibold mb-6 text-${serviceColors[service]}-600`;
            
            // Show queue display
            document.getElementById('queueDisplay').classList.remove('hidden');
        }

        function closeQueueDisplay() {
            document.getElementById('queueDisplay').classList.add('hidden');
        }

        function printTicket() {
            window.print();
        }

        // Simulate live updates to current numbers (every 30 seconds)
        function simulateQueueUpdates() {
            setInterval(() => {
                // Randomly increment one of the current numbers
                const services = Object.keys(queueCounters);
                const randomService = services[Math.floor(Math.random() * services.length)];
                queueCounters[randomService]++;
                
                // Update the display (you would typically get this from a server)
                console.log(`Simulated update: ${servicePrefixes[randomService]} counter increased`);
            }, 30000);
        }

        // Start simulation
        simulateQueueUpdates();
    </script>
</body>
</html>

