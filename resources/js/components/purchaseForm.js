export default () => ({
    items: window.oldItems || [{ id: Date.now(), product_id: '', quantity: 1, purchase_price: 0, subtotal: 0, product_data: null }],
    total_amount: window.oldTotalAmount || 0,
    
    gallery: {
        isOpen: false,
        products: [],
        pagination: {},
        isLoading: false,
    },

    init() {
        window.addEventListener('subtotal-updated', () => this.calculateTotal());
        this.calculateTotal();
    },

    addItem() {
        this.items.push({ id: Date.now(), product_id: '', quantity: 1, purchase_price: 0, subtotal: 0, product_data: null });
    },

    calculateTotal() {
        this.total_amount = this.items.reduce((total, item) => total + (item.subtotal || 0), 0);
    },

    formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
    },

    toggleGallery() {
        this.gallery.isOpen = !this.gallery.isOpen;
        if (this.gallery.isOpen && this.gallery.products.length === 0) {
            this.fetchGalleryProducts();
        }
    },

    fetchGalleryProducts(url = '/api/products/gallery') {
        if (this.gallery.isLoading) return;
        this.gallery.isLoading = true;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                this.gallery.products.push(...data.data);
                this.gallery.pagination = data;
                this.gallery.isLoading = false;
            });
    },

    selectFromGallery(product) {
        let targetIndex = this.items.findIndex(item => !item.product_id);
        if (targetIndex === -1) {
            this.addItem();
            targetIndex = this.items.length - 1;
        }

        this.items[targetIndex].product_id = product.id;
        // Harga beli tidak diisi otomatis, biarkan pengguna yang input
        this.items[targetIndex].product_data = product; 

        this.gallery.isOpen = false;
    }
});