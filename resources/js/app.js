import './bootstrap';
import Alpine from 'alpinejs';

// Impor komponen
import saleForm from './components/saleForm';
import purchaseForm from './components/purchaseForm'; // <-- [BARU]

// Daftarkan komponen
Alpine.data('saleForm', saleForm);
Alpine.data('purchaseForm', purchaseForm); // <-- [BARU]

window.Alpine = Alpine;
Alpine.start();