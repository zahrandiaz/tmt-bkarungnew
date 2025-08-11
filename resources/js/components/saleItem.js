import TomSelect from 'tom-select';

export default (item, index) => ({
    item: item,
    tomselect: null,

    init() {
        this.tomselect = new TomSelect(this.$refs.select, {
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'sku'],
            create: false,
            render: {
                option: (data, escape) => `<div><span class="font-semibold">${escape(data.name)}</span><span class="text-sm text-gray-500 ml-2">(Stok: ${escape(data.stock)})</span></div>`,
                item: (item, escape) => `<div>${escape(item.name)}</div>`
            },
            load: (query, callback) => {
                const url = `/api/products/search?q=${encodeURIComponent(query)}`;
                fetch(url).then(res => res.json()).then(json => callback(json)).catch(() => callback());
            },
            onChange: (value) => {
                const product = this.tomselect.options[value];
                if (product) {
                    this.item.product_id = product.id;
                    this.item.sale_price = product.selling_price;
                } else {
                    this.item.product_id = null;
                    this.item.sale_price = 0;
                }
                this.calculateSubtotal();
            }
        });
        
        // [PERBAIKAN UTAMA] Awasi perubahan pada product_id
        this.$watch('item.product_id', (newValue) => {
            if (newValue && this.tomselect.getValue() !== newValue) {
                // Cek jika data produknya ada (dari galeri)
                if (this.item.product_data) {
                    this.tomselect.addOption(this.item.product_data);
                }
                this.tomselect.setValue(newValue);
            }
        });

        // Inisialisasi awal jika data sudah ada
        if (this.item.product_id && this.item.product_data) {
             this.tomselect.addOption(this.item.product_data);
             this.tomselect.setValue(this.item.product_id);
        }
    },

    calculateSubtotal() {
        this.item.subtotal = (this.item.quantity || 0) * (this.item.sale_price || 0);
        // Kirim event bahwa subtotal berubah
        this.$dispatch('subtotal-updated');
    }
});