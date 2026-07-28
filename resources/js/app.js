import Alpine from 'alpinejs';
window.Alpine = Alpine;

Alpine.data('appLayout', (initialData = {}) => ({
    mobileMenuOpen: false,
    toastMessage: '',
    showToast: false,
    triggerToast(msg) {
        this.toastMessage = msg;
        this.showToast = true;
        setTimeout(() => {
            this.showToast = false;
        }, 3500);
    },
    darkMode: localStorage.getItem('dark_mode') === 'true',
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('dark_mode', this.darkMode ? 'true' : 'false');
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        this.triggerToast(this.darkMode ? 'Dark Mode enabled.' : 'Light Mode enabled.');
    },
    activeRightPanel: '',
    chatRecipient: '',
    chatInput: '',
    chatMessages: {
        'Emily Cooper': [
            { sender: 'Emily Cooper', text: 'Hey, did you review the PO for the fertilizer seeds?', time: '10:30 AM' },
            { sender: 'You', text: 'Yes, looking at it now.', time: '10:32 AM' }
        ],
        'Alex Morgan': [
            { sender: 'Alex Morgan', text: 'Can we expedite the logistics review for AgriCorp?', time: 'Yesterday' }
        ],
        'Marcus Davis': [
            { sender: 'Marcus Davis', text: 'The budget approval step is pending on your end.', time: '2 hours ago' }
        ]
    },
    notificationsList: [
        { id: 1, text: 'Requisition #0024 approved by Johny Papa', time: '1 hour ago', read: false },
        { id: 2, text: 'Purchase Order #1042 successfully transmitted to AgriSeed Corp', time: '3 hours ago', read: false },
        { id: 3, text: 'New Supplier profile pending verification: BioGrow Farms', time: '5 hours ago', read: true }
    ],
    systemSettings: {
        emailAlerts: true,
        desktopAlerts: false,
        defaultUrgency: 'Medium'
    },
    openChat(recipient) {
        this.chatRecipient = recipient;
        this.activeRightPanel = 'chat';
    },
    sendChatMessage() {
        if (!this.chatInput.trim()) return;
        const msgText = this.chatInput;
        this.chatMessages[this.chatRecipient].push({
            sender: 'You',
            text: msgText,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
        const recipient = this.chatRecipient;
        this.chatInput = '';
        
        // Simulate automatic reply after 1.2s
        setTimeout(() => {
            const replies = [
                "Sure, I will look into it shortly.",
                "Sounds good, keep me posted!",
                "Thanks for the update.",
                "I'm on a call right now, will check this later."
            ];
            const randomReply = replies[Math.floor(Math.random() * replies.length)];
            this.chatMessages[recipient].push({
                sender: recipient,
                text: randomReply,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });
            setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 20);
        }, 1200);
    },
    init() {
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        }
    },
    showRequisitionModal: false,
    showPoModal: false,
    supplierId: '',
    expectedDelivery: '',
    neededBy: '',
    urgency: 'Medium',
    department: 'Farm Operations',
    requestorName: initialData.requestorName || '',
    suppliersList: initialData.suppliersList || [],
    catalogProducts: initialData.catalogProducts || [],
    reqItems: initialData.reqItems || [{ sku: '', name: '', unit: '', qty: 1, cost: 0, justification: '' }],
    poItems: initialData.poItems || [{ sku: '', name: '', unit: '', qty: 1, cost: 0 }],
    selectCatalogItem(itemIndex, productId) {
        if (!productId) {
            if (this.reqItems[itemIndex]) {
                this.reqItems[itemIndex].unit = '';
            }
            return;
        }
        const prod = this.catalogProducts.find(p => p.id == productId);
        if (prod && this.reqItems[itemIndex]) {
            this.reqItems[itemIndex].sku = prod.sku || '';
            this.reqItems[itemIndex].name = prod.name || '';
            this.reqItems[itemIndex].cost = Number(prod.base_price || 0);

            let uomVal = '';
            if (prod.uom) {
                uomVal = prod.uom.uom_name || prod.uom.uom_code || '';
            }

            if (!uomVal) {
                this.reqItems[itemIndex].unit = 'No UOM Assigned';
            } else {
                this.reqItems[itemIndex].unit = uomVal;
            }
        }
    },
    addReqItem() {
        this.reqItems.push({ sku: '', name: '', unit: '', qty: 1, cost: 0, justification: '' });
    },
    removeReqItem(i) {
        this.reqItems.splice(i, 1);
    },
    addPoItem() {
        this.poItems.push({ sku: '', name: '', unit: 'Unit', qty: 1, cost: 0 });
    },
    removePoItem(i) {
        this.poItems.splice(i, 1);
    },
    reqTotal() {
        return this.reqItems.reduce((sum, item) => sum + (Number(item.qty || 0) * Number(item.cost || 0)), 0);
    },
    poSubtotal() {
        return this.poItems.reduce((sum, item) => sum + (Number(item.qty || 0) * Number(item.cost || 0)), 0);
    },
    poVat() {
        return this.poSubtotal() * 0.12;
    },
    poTotal() {
        return this.poSubtotal() + this.poVat();
    },
    getSupplierName() {
        const s = this.suppliersList.find(x => x.id == this.supplierId);
        return s ? s.name : '—';
    }
}));

Alpine.start();
