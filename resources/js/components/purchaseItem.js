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
                    // Di sini kita tidak mengisi harga beli, karena harga beli bisa bervariasi
                } else {
                    this.item.product_id = null;
                }
                this.calculateSubtotal();
            }
        });

        // Watcher untuk sinkronisasi dari galeri
        this.$watch('item.product_id', (newValue) => {
            if (newValue && this.tomselect.getValue() !== newValue) {
                if (this.item.product_data) {
                    this.tomselect.addOption(this.item.product_data);
                }
                this.tomselect.setValue(newValue);
            }
        });

        // Inisialisasi awal
        if (this.item.product_id && this.item.product_data) {
             this.tomselect.addOption(this.item.product_data);
             this.tomselect.setValue(this.item.product_id);
        }
    },

    calculateSubtotal() {
        this.item.subtotal = (this.item.quantity || 0) * (this.item.purchase_price || 0);
        this.$dispatch('subtotal-updated');
    }
});