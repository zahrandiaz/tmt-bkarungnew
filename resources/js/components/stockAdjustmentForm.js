import TomSelect from 'tom-select';

// Komponen Alpine.js untuk form penyesuaian stok
export default () => ({
    // Properti untuk menyimpan nama produk (untuk menangani error validasi)
    selectedProductName: window.oldData?.product_name_display || '',
    
    // Fungsi inisialisasi yang akan dipanggil oleh Alpine.js
    init() {
        const tomSelect = new TomSelect(this.$refs.selectProduct, {
            valueField: 'id',
            labelField: 'name',
            searchField: ['name', 'sku'],
            create: false,
            // Mengambil opsi dari API
            load: (query, callback) => {
                const url = `/api/products/search?q=${encodeURIComponent(query)}`;
                fetch(url)
                    .then(res => res.json())
                    .then(json => {
                        // API Anda mengembalikan array, jadi kita langsung teruskan
                        callback(json);
                    })
                    .catch(() => callback());
            },
            // Mengatur bagaimana setiap opsi di dropdown ditampilkan
            render: {
                option: (data, escape) => `<div>
                                              <span class="font-semibold">${escape(data.name)}</span>
                                              <span class="block text-sm text-gray-500">SKU: ${escape(data.sku)} | Stok: ${escape(data.stock)}</span>
                                           </div>`,
                item: (item, escape) => `<div>${escape(item.name)}</div>`
            },
            // Saat pilihan berubah, simpan nama produk ke hidden input
            onChange: (value) => {
                const selectedOption = tomSelect.options[value];
                this.selectedProductName = selectedOption ? selectedOption.name : '';
            }
        });
    }
});