export default () => ({
    // [DIUBAH] Mengambil semua data awal dari window.oldData
    items: window.oldData?.items || [{ id: Date.now(), product_id: '', quantity: 1, sale_price: 0, subtotal: 0, product_data: null }],
    total_amount: window.oldData?.total_amount || 0,
    payment_method: window.oldData?.payment_method || 'tunai',
    payment_status: window.oldData?.payment_status || 'lunas',
    down_payment: window.oldData?.down_payment || 0,
    
    // Properti untuk galeri
    gallery: {
        isOpen: false,
        products: [],
        pagination: {},
        isLoading: false,
    },

    init() {
        // Listener ini akan menghitung ulang total setiap kali subtotal di baris anak berubah
        window.addEventListener('subtotal-updated', () => this.calculateTotal());
        this.calculateTotal();
    },

    addItem() {
        this.items.push({ id: Date.now(), product_id: '', quantity: 1, sale_price: 0, subtotal: 0, product_data: null });
    },

    removeItem(index) {
        this.items.splice(index, 1);
        this.calculateTotal(); // Hitung ulang total setelah menghapus
    },

    calculateTotal() {
        this.total_amount = this.items.reduce((total, item) => total + (item.subtotal || 0), 0);
    },

    formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value);
    },

    // Fungsi-fungsi untuk galeri
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
        // Cari baris kosong pertama
        let targetIndex = this.items.findIndex(item => !item.product_id);
        
        // Jika tidak ada baris kosong, buat yang baru
        if (targetIndex === -1) {
            this.addItem();
            targetIndex = this.items.length - 1;
        }

        // Isi data baris tersebut
        this.items[targetIndex].product_id = product.id;
        this.items[targetIndex].sale_price = product.selling_price;
        this.items[targetIndex].subtotal = product.selling_price * this.items[targetIndex].quantity;
        this.items[targetIndex].product_data = product; // Data lengkap untuk inisialisasi TomSelect

        this.calculateTotal();
        this.gallery.isOpen = false;
    }
});