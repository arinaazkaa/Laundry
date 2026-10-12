export default function transactionForm(config) {
    return {
        services: config.services,
        promos: config.promos,
        tab: 'kiloan',
        cart: [],
        customerQuery: '',
        customerResults: [],
        selectedCustomer: null,
        selectedPromoId: null,
        usePickup: false,
        useDelivery: false,
        pickupEnabled: config.pickupEnabled,
        deliveryEnabled: config.deliveryEnabled,
        paidAmount: 0,
        paymentMethod: 'cash',
        subtotal: 0,
        discount: 0,
        deliveryFee: 0,
        total: 0,
        freeOngkirApplied: false,
        memberDiscountApplied: false,
        promoUsable: null,
        promoReason: null,
        submitting: false,
        previewing: false,

        showQuickAddModal: false,
        newCustomer: { name: '', phone: '', address: '' },
        savingCustomer: false,

        init() {
            this.recalculate();
        },

        addItem(service) {
            this.cart.push({
                service_id: service.id,
                name: service.name,
                type: service.type,
                price: parseFloat(service.price),
                qty: 1,
            });
            this.recalculate();
        },

        removeItem(index) {
            this.cart.splice(index, 1);
            this.recalculate();
        },

        get totalWeight() {
            return this.cart
                .filter(i => i.type === 'kiloan')
                .reduce((sum, i) => sum + Number(i.qty || 0), 0);
        },

        selectPromo(promoId) {
            this.selectedPromoId = this.selectedPromoId === promoId ? null : promoId;
            this.recalculate();
        },

        // Sekarang recalculate() memanggil server (preview), bukan hitung manual di JS,
        // supaya diskon promo & member selalu akurat sesuai aturan yang sama dengan saat disimpan.
        async recalculate() {
            this.subtotal = this.cart.reduce((sum, i) => sum + (i.qty * i.price), 0);

            if (this.cart.length === 0) {
                this.discount = 0;
                this.deliveryFee = 0;
                this.total = 0;
                this.freeOngkirApplied = false;
                this.memberDiscountApplied = false;
                this.promoUsable = null;
                this.promoReason = null;
                return;
            }

            this.previewing = true;
            try {
                const res = await fetch('/transactions/preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        items: this.cart.map(i => ({ qty: i.qty, price: i.price, type: i.type })),
                        promo_id: this.selectedPromoId,
                        use_pickup: this.usePickup,
                        use_delivery: this.useDelivery,
                        customer_id: this.selectedCustomer?.id ?? null,
                    }),
                });

                if (res.ok) {
                    const data = await res.json();
                    this.subtotal = data.subtotal;
                    this.discount = data.discount;
                    this.deliveryFee = data.delivery_fee;
                    this.total = data.total;
                    this.freeOngkirApplied = data.free_ongkir_applied;
                    this.memberDiscountApplied = data.member_discount_applied;
                    this.promoUsable = data.promo_usable;
                    this.promoReason = data.promo_reason;
                }
            } catch (err) {
                // Kalau preview gagal (offline dsb), biarkan angka lama tampil, submit tetap divalidasi server
            } finally {
                this.previewing = false;
            }
        },

        async searchCustomer(q) {
            this.customerQuery = q;
            if (!q) { this.customerResults = []; return; }
            const res = await fetch(`/customers/search?q=${encodeURIComponent(q)}`);
            this.customerResults = await res.json();
        },

        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerResults = [];
            this.customerQuery = '';
            this.recalculate();
        },

        openQuickAdd() {
            this.newCustomer = { name: this.customerQuery, phone: '', address: '' };
            this.showQuickAddModal = true;
            this.customerResults = [];
        },

        async saveNewCustomer() {
            if (!this.newCustomer.name) {
                alert('Nama pelanggan wajib diisi.');
                return;
            }
            this.savingCustomer = true;
            try {
                const res = await fetch('/customers', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.newCustomer),
                });

                if (!res.ok) throw new Error('Gagal menyimpan pelanggan');

                const customer = await res.json();
                this.selectCustomer(customer);
                this.showQuickAddModal = false;
                this.newCustomer = { name: '', phone: '', address: '' };
            } catch (err) {
                alert('Gagal menyimpan pelanggan, coba lagi.');
            } finally {
                this.savingCustomer = false;
            }
        },

        get change() {
            return Math.max(0, this.paidAmount - this.total);
        },

        async submit() {
            if (this.cart.length === 0) {
                alert('Pilih minimal satu layanan.');
                return;
            }
            this.submitting = true;

            const payload = {
                customer_id: this.selectedCustomer?.id ?? null,
                items: this.cart.map(i => ({
                    service_id: i.service_id,
                    name: i.name,
                    type: i.type,
                    price: i.price,
                    qty: i.qty,
                })),
                promo_id: this.promoUsable ? this.selectedPromoId : null,
                use_pickup: this.usePickup,
                use_delivery: this.useDelivery,
                payment_method: this.paymentMethod,
                paid_amount: this.paidAmount,
            };

            try {
                const res = await fetch('/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });

                if (!res.ok) throw new Error('Gagal menyimpan transaksi');

                const data = await res.json();
                window.location.href = `/transactions/${data.id}`;
            } catch (err) {
                alert('Terjadi kesalahan, coba lagi.');
                this.submitting = false;
            }
        },
    };
}