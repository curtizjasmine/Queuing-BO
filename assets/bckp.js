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
            polling: null,

            // =========== MODAL DATA ===========
            showModal: false,
            modalAction: '',      // 'skip' or 'cancel'
            modalRemarks: '',
            statusModal: '',

            // =========== WEBSOCKET ===========
            socket: null
        }
    },

    // ... [Your existing Computed properties remain the same] ...
    computed: {
        /* ------------------ USER FILTER ------------------ */
        filteredUsers() {
            if (!this.searchQuery) return this.users;
            const query = this.searchQuery.toLowerCase();
            return this.users.filter(user => String(user.user_id).toLowerCase().includes(query));
        },
        // ... other computed properties
    },

    methods: {
        // =========== WEBSOCKET ===========
        initWebSocket() {
            this.socket = new WebSocket('ws://172.16.15.235:8080');
            this.socket.onopen = () => console.log("✅ Connected to WebSocket");
            this.socket.onerror = (error) => console.error("WebSocket Error:", error);
        },

        // =========== MODAL LOGIC ===========
        openModal(action) {
            if (!this.selectedTicket) {
                alert("No ticket selected!");
                return;
            }
            this.modalAction = action; // Set action ('skip' or 'cancel')
            this.modalRemarks = '';    // Clear previous text
            this.showModal = true;     // Show modal
        },

        closeModal() {
            this.showModal = false;
        },

        // =========== 🆕 CONFIRM ACTION (Skip/Cancel) ===========
        confirmModalAction() {
            // 1. Validation: Ensure remarks are not empty
            if (!this.modalRemarks || this.modalRemarks.trim() === '') {
                alert("Please enter a remark or reason.");
                return;
            }

            // 2. Prepare Data for PHP
            const data = new FormData();
            
            // Determine which PHP case to run
            if (this.modalAction === 'skip') {
                data.append("choice", "skip_ticket");
                this.statusModal = "Skipped"
            } else {
                data.append("choice", "skip_ticket");
                 this.statusModal = "Cancelled"
            }

            data.append("patient_id", this.selectedTicket.patient_id);
            data.append("remarks", this.modalRemarks);
            data.append("status",this.statusModal)

            // 3. Send to Backend
            axios.post('./middleware/routes.php', data)
                .then(res => {
                    // Update the list with the new data from server
                    this.pendingTickets = Array.isArray(res.data) ? res.data : [];
                    
                    // Close Modal & Reset
                    this.closeModal();
                    this.selectedTicket = null; // Deselect
                    
                    alert(`Ticket successfully ${this.modalAction}ed.`);
                })
                .catch(err => {
                    console.error("Error processing action:", err);
                    alert("An error occurred while saving.");
                });
        },

        // =========== EXISTING API CALLS ===========
        display() {
            const data = new FormData();
            data.append("choice", "get_pendingTickets");

            axios.post('./middleware/routes.php', data)
                .then(res => {
                    this.pendingTickets = Array.isArray(res.data) ? res.data : [];
                    console.log(res.data);
                    
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
                    // WebSocket Send
                    if (this.socket && this.socket.readyState === WebSocket.OPEN) {
                        const payload = JSON.stringify({
                            type: 'call',
                            ticket: ticket,
                            patient_id: id,
                            station: window.currentStation
                        });
                        this.socket.send(payload);
                    }
                })
                .catch(err => { console.error("Error:", err); });
        },
         skipnumber(){
           
         },


        completed(id){
            const data = new FormData();
            data.append("choice", "completed_tickets");
            data.append("patient_id", id);
            axios.post('./middleware/routes.php', data)
                .then(res => {
                    this.pendingTickets = Array.isArray(res.data) ? res.data : [];
                    this.selectedTicket = null; // Deselect after done
                })
                .catch(err => { console.error("Error:", err); });
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
        this.display();
        this.polling = setInterval(() => this.display(), 2000);
    },

    beforeUnmount() {
        clearInterval(this.polling);
        if (this.socket) this.socket.close();
    }
});

app.mount('#app');