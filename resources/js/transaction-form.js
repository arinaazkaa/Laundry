export default function transactionForm(config) {
    return {
        services: config.services,
        packages: config.packages ?? [],
        promos: config.promos ?? [],
        stackingEnabled: !!config.stackingEnabled,
        memberDiscountPercent: Number(config.memberDiscountPercent ?? 0),
        pickupRequestId: config.pickupRequestId ?? null,
        tab: 'kiloan',
        cart: [],
        customerQuery: '',
        customerResults: [],
        selectedCustomer: null,
        selectedPromoIds: [],
        promoResults: {},
        usePickup: false,
        useDelivery: false,
        pickupEnabled: config.pickupEnabled,
        deliveryEnabled: config.deliveryEnabled,
        paidAmount: 0,
        paymentMethod: config.defaultPaymentMethod ?? 'cash',
        subtotal: 0,
        discount: 0,
        deliveryFee: 0,
        total: 0,
        freeOngkirApplied: false,
        memberDiscountApplied: false,
        cartError: null,
        submitting: false,
        previewing: false,
        previewSeq: 0,

        showQuickAddModal: false,
        newCustomer: { name: '', phone: '', address: '' },
        savingCustomer: false,

        init() {
            // Isi awal dari "Salin ke transaksi baru" atau dari permintaan jemput pelanggan
            if (config.prefill) {
                this.cart = config.prefill.items ?? [];
                this.selectedCustomer = config.prefill.customer ?? null;
                if (config.prefill.use_pickup && this.pickupEnabled) {
                    this.usePickup = true;
                }
            }
            this.recalculate();
        },

        addItem(service) {
            this.cart.push({
                service_id: service.id,
                package_id: null,
                name: service.name,
                type: service.type,
                price: parseFloat(service.price),
                qty: 1,
            });
            this.recalculate();
        },

        addPackage(pkg) {
            const existing = this.cart.find(i => i.package_id === pkg.id);

            if (existing) {
                existing.qty = Number(existing.qty) + 1;
            } else {
                this.cart.push({
                    service_id: null,
                    package_id: pkg.id,
                    name: 'Paket ' + pkg.name,
                    type: 'paket',
                    price: Number(pkg.package_price),
                    weight_kg: pkg.weight_kg,
                    qty: 1,
                });
            }

            this.recalculate();
        },

        removeItem(index) {
            this.cart.splice(index, 1);
            this.recalculate();
        },

        // Jumlah paket harus bilangan bulat minimal 1
        normalizeQty(item) {
            if (item.type === 'paket') {
                item.qty = Math.max(1, Math.round(Number(item.qty) || 1));
            }
        },

        // Pilih atau lepas promo. Promo yang boleh digabung bisa dipilih bersamaan (maksimal 2)
        selectPromo(promoId) {
            if (this.selectedPromoIds.includes(promoId)) {
                this.selectedPromoIds = this.selectedPromoIds.filter(id => id !== promoId);
            } else {
                const promo = this.promos.find(p => p.id === promoId);

                if (!this.stackingEnabled || !promo?.stackable) {
                    this.selectedPromoIds = [promoId];
                } else {
                    const keep = this.selectedPromoIds.filter(id => this.promos.find(p => p.id === id)?.stackable);
                    this.selectedPromoIds = [...keep, promoId].slice(-2);
                }
            }

            this.recalculate();
        },

        // Hanya promo yang benar-benar berlaku yang dikirim saat menyimpan
        get appliedPromoIds() {
            return this.selectedPromoIds.filter(id => this.promoResults[id]?.applied);
        },

        // Teks syarat promo, misalnya "Min. Rp 50.000 · Min. 5 kg"
        syarat(promo) {
            const parts = [];
            if (Number(promo.min_transaction) > 0) {
                parts.push('Min. Rp ' + Number(promo.min_transaction).toLocaleString('id-ID'));
            }
            if (Number(promo.min_weight) > 0) {
                parts.push('Min. ' + Number(promo.min_weight) + ' kg');
            }
            return parts.join(' · ');
        },

        // Angka dihitung server supaya aturan promo, paket, dan member selalu sama dengan saat disimpan
        async recalculate() {
            this.subtotal = this.cart.reduce((sum, i) => sum + (Number(i.qty) * i.price), 0);

            if (this.cart.length === 0) {
                this.previewSeq++;
                this.discount = 0;
                this.deliveryFee = 0;
                this.total = 0;
                this.freeOngkirApplied = false;
                this.memberDiscountApplied = false;
                this.promoResults = {};
                this.cartError = null;
                this.previewing = false;
                return;
            }

            // Hanya jawaban terbaru yang dipakai kalau kasir mengetik cepat
            const seq = ++this.previewSeq;
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
                        items: this.cart.map(i => ({
                            service_id: i.service_id,
                            package_id: i.package_id ?? null,
                            qty: Number(i.qty) || 0,
                        })),
                        promo_ids: this.selectedPromoIds,
                        use_pickup: this.usePickup,
                        use_delivery: this.useDelivery,
                        customer_id: this.selectedCustomer?.id ?? null,
                    }),
                });

                if (seq !== this.previewSeq) return;

                if (res.ok) {
                    const data = await res.json();
                    this.subtotal = data.subtotal;
                    this.discount = data.discount;
                    this.deliveryFee = data.delivery_fee;
                    this.total = data.total;
                    this.freeOngkirApplied = data.free_ongkir_applied;
                    this.memberDiscountApplied = data.member_discount_applied;
                    this.promoResults = Object.fromEntries((data.promo_results ?? []).map(r => [r.id, r]));
                    this.cartError = null;
                } else if (res.status === 422) {
                    const err = await res.json();
                    this.cartError = Object.values(err.errors ?? {})[0]?.[0] ?? null;
                }
            } catch (err) {
                // Kalau preview gagal (misalnya offline), angka lama tetap tampil. Server tetap menghitung ulang saat disimpan
            } finally {
                if (seq === this.previewSeq) this.previewing = false;
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
            return Math.max(0, (Number(this.paidAmount) || 0) - this.total);
        },

        async submit() {
            if (this.cart.length === 0) {
                alert('Pilih minimal satu layanan.');
                return;
            }

            if (this.cart.some(i => !(Number(i.qty) > 0))) {
                alert('Berat atau jumlah laundry wajib diisi.');
                return;
            }

            this.submitting = true;

            const payload = {
                customer_id: this.selectedCustomer?.id ?? null,
                items: this.cart.map(i => ({
                    service_id: i.service_id,
                    package_id: i.package_id ?? null,
                    qty: Number(i.qty),
                })),
                promo_ids: this.appliedPromoIds,
                use_pickup: this.usePickup,
                use_delivery: this.useDelivery,
                pickup_request_id: this.pickupRequestId,
                payment_method: this.paymentMethod,
                paid_amount: Number(this.paidAmount) || 0,
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

                // 422 = ditolak validasi server (misalnya promo, paket, atau permintaan jemput sudah tidak berlaku)
                if (res.status === 422) {
                    const err = await res.json();
                    const first = Object.values(err.errors ?? {})[0]?.[0];
                    alert((first ?? err.message ?? 'Data transaksi tidak valid.') + '\n\nTotal akan dihitung ulang. Periksa lagi sebelum menyimpan.');
                    this.submitting = false;
                    await this.recalculate();
                    return;
                }

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