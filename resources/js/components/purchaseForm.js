import TomSelect from 'tom-select';

export default () => ({
    items: window.oldItems || [{ product_id: '', quantity: 1, purchase_price: 0, subtotal: 0 }],
    total_amount: window.oldTotalAmount || 0,

    init() {
        this.calculateTotal();
    },

    addItem() {
        this.items.push({ product_id: '', quantity: 1, purchase_price: 0, subtotal: 0 });
    },

    removeItem(index) {
        this.items.splice(index, 1);
        this.calculateTotal();
    },

    calculateSubtotal(index) {
        const item = this.items[index];
        item.subtotal = (item.quantity || 0) * (item.purchase_price || 0);
        this.calculateTotal();
    },

    calculateTotal() {
        this.total_amount = this.items.reduce((total, item) => total + (item.subtotal || 0), 0);
    },

    formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
    },

    initTomSelect(element, index) {
        const tomselect = new TomSelect(element, {
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
                fetch(url)
                    .then(response => response.json())
                    .then(json => callback(json))
                    .catch(() => callback());
            },
            onChange: (value) => {
                const product = tomselect.options[value];
                if (product) {
                    this.items[index].product_id = product.id;
                    // Logika ini bisa ditambahkan jika ingin harga beli terakhir otomatis terisi
                    // this.items[index].purchase_price = product.purchase_price;
                    this.calculateSubtotal(index);
                }
            }
        });
    }
});