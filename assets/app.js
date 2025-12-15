const app = Vue.createApp({
    data() {
        return {
            // =========== DATA ===========
            pendingTickets: [],
            selectedTicket: null,
            currentTab: 'pending',
            users: [],
            searchQuery: "",
            DepartmentData: [],
            currentPage: 1,
            itemsPerPage: 10,
            ticketid : null,
            showModal: false,
            modalAction: '',      
            modalRemarks: '',
            statusModal: '',
            socket: null,
            fname: null,
            lname: null,
            mname: null,
            BOD: null,
            age:  null,
          
            civil_Status:  null,
            contact_no:  null,
            home_address: null,
            BP: null,
            Pressure:  null,
            Weight:  null,
            has_fever: null,
 
              savedData: {
            sym_fever: null,
            has_cough: null,
            has_sorethroat: null,
            has_shortnessBreath: null,
            has_influenza_Symptoms: null,
            has_history_Covid: null,
            have_localTransimission:  null,
            have_contact_recentTravel: null,
            has_inluenza_illness: null,
            has_contactConfirm: null,
              gender: null,
              medicine:  null,
             existingConditions: null,
             admissionDate: null,
             admitted_conditions: null,
             historyICU: null,
             took_antipyretics: null,
    }
        }
    },
    computed: {
        filteredUsers() {
            if (!this.searchQuery) return this.users;
            const query = this.searchQuery.toLowerCase();
            return this.users.filter(user => String(user.user_id).toLowerCase().includes(query));
        },
    },

    methods: {
        // =========== WEBSOCKET ===========
        initWebSocket() {
            this.socket = new WebSocket('ws://172.16.15.235:8080');
            this.socket.onopen = () => console.log("✅ Connected to WebSocket");
            this.socket.onerror = (error) => console.error("WebSocket Error:", error);
            this.socket.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    if (data.type === 'update_queue' || data.type === 'call') {
                        console.log("WebSocket Trigger: Refreshing Data...");
                        this.display(); 
                    }
                } catch (e) {
                    console.error("Error parsing WebSocket message:", e);
                }
            };
        },
        broadcastUpdate(type = 'update_queue', extraData = {}) {
            if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                const payload = JSON.stringify({
                    type: type,
                    ...extraData
                });
                this.socket.send(payload);
            }
        },

        // =========== MODAL LOGIC ===========
        openModal(action) {
            if (!this.selectedTicket) {
                alert("No ticket selected!");
                return;
            }
            this.modalAction = action; 
            this.modalRemarks = '';    
            this.showModal = true;     
        },

        closeModal() {
            this.showModal = false;
        },

        // =========== CONFIRM ACTION (Skip/Cancel) ===========
        confirmModalAction() {
            if (!this.modalRemarks || this.modalRemarks.trim() === '') {
                alert("Please enter a remark or reason.");
                return;
            }

            const data = new FormData();
            
            if (this.modalAction === 'skip') {
                data.append("choice", "skip_ticket");
                this.statusModal = "Skipped"
            } else {
                data.append("choice", "skip_ticket"); // Assuming PHP handles logic based on status
                this.statusModal = "Cancelled"
            }

            data.append("patient_id", this.selectedTicket.patient_id);
            data.append("remarks", this.modalRemarks);
            data.append("status", this.statusModal)

            axios.post('./middleware/routes.php', data)
                .then(res => {
                    // Update Local Data immediately (optional, display() handles it too)
                    this.pendingTickets = Array.isArray(res.data) ? res.data : [];
                    
                    // 🟢 REALTIME: Broadcast to other screens
                    this.broadcastUpdate('update_queue');

                    this.closeModal();
                    this.selectedTicket = null; 
                    alert(`Ticket successfully ${this.modalAction}ed.`);
                })
                .catch(err => {
                    console.error("Error processing action:", err);
                    alert("An error occurred while saving.");
                });
        },

        // =========== API CALLS ===========
        display() {
            // This is now called by onmessage (WebSocket) or initial load
            const data = new FormData();
            data.append("choice", "get_pendingTickets");

            axios.post('./middleware/routes.php', data)
                .then(res => {
                    this.pendingTickets = Array.isArray(res.data) ? res.data : [];
                    console.log("Data refreshed via", res.data.length, "items");
                })
                .catch(err => { console.error("Error:", err); });
               
        },
       
        callnumber(id, ticket) {
            let x = this;
            x.ticketid = ticket; 
            const data = new FormData();
            data.append("choice", "do_displayTv");
            data.append("patient_id", id);
            
            axios.post('./middleware/routes.php', data)
                .then(res => {
                    this.pendingTickets = Array.isArray(res.data) ? res.data : [];
                    
                    // 🟢 REALTIME: Send Call Signal
                    this.broadcastUpdate('call', {
                        ticket: ticket,
                        patient_id: id,
                        station: window.currentStation || 'Counter' // Ensure currentStation exists
                    });
                })
                .catch(err => { console.error("Error:", err); });
        },
        
        savedsData(){
             console.log(this.savedData.civil_Status);
             
        },
completed(id) {
    // Safety check to ensure savedData exists before accessing properties
    if (!this.savedData) {
        console.error("Error: savedData is missing");
        return;
    }

    const data = new FormData();
    data.append("choice", "completed_tickets");
    data.append("patient_id", id);
    data.append("fname", this.fname);
    data.append("lname", this.lname);
    data.append("mname", this.mname);
    data.append("BOD", this.BOD);
    data.append("age", this.age);
    data.append("civil_Status", this.savedData.civil_Status || "");
    data.append("contact_no", this.contact_no);
    data.append("home_address", this.home_address);
    data.append("BP", this.BP);
    data.append("Pressure", this.Pressure);
    data.append("Weight", this.Weight);
    data.append("sym_fever", this.savedData.sym_fever || "");
    data.append("has_cough", this.savedData.has_cough || "");
    data.append("has_sorethroat", this.savedData.has_sorethroat || "");
    data.append("has_shortnessBreath", this.savedData.has_shortnessBreath || "");
    data.append("has_influenza_Symptoms", this.savedData.has_influenza_Symptoms || "");
    data.append("has_history_Covid", this.savedData.has_history_Covid || "");
    data.append("have_localTransimission", this.savedData.have_localTransimission || "");
    data.append("have_contact_recentTravel", this.savedData.have_contact_recentTravel || "");
    data.append("has_inluenza_illness", this.savedData.has_inluenza_illness || "");
    data.append("has_contactConfirm", this.savedData.has_contactConfirm || "");
     data.append("took_antipyretics", this.savedData.took_antipyretics || "");
    data.append("gender", this.savedData.gender || "");
    data.append("medicine", this.savedData.medicine || "");
    data.append("existingConditions", this.savedData.existingConditions || "");
    data.append("admissionDate", this.savedData.admissionDate || "");
    data.append("admitted_conditions", this.savedData.admitted_conditions || "");
    data.append("historyICU", this.savedData.historyICU || "");

    axios.post('./middleware/routes.php', data)
        .then(res => {
            console.log("Server Response:", res.data); // Debugging
            if(res.data === "connected"){
                // Logic for success
            }
            this.pendingTickets = Array.isArray(res.data) ? res.data : [];
            this.selectedTicket = null;
            this.broadcastUpdate('update_queue');
        })
        .catch(err => {
            console.error("Error:", err);
        });
},

        selectTicket(ticket) {
            this.selectedTicket = ticket;
        },
        switchTab(tab) {
            this.currentTab = tab;
        }
    },

    mounted() {
        this.initWebSocket();
        this.display(); // Initial load only
        
        // 🔴 REMOVED: No more Polling
        // this.polling = setInterval(() => this.display(), 2000);
    },

    beforeUnmount() {
        // clearInterval(this.polling);
        if (this.socket) this.socket.close();
    }
});

app.mount('#app');