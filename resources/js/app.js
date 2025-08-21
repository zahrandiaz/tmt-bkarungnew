import './bootstrap';
import Alpine from 'alpinejs';

// [BARU] 1. Impor CSS Tom Select
import 'tom-select/dist/css/tom-select.default.css';

// [BARU] 2. Impor JavaScript Tom Select
import TomSelect from 'tom-select/dist/js/tom-select.complete.min.js';

import saleForm from './components/saleForm';
import saleItem from './components/saleItem';
import purchaseForm from './components/purchaseForm';
import purchaseItem from './components/purchaseItem';
import stockAdjustmentForm from './components/stockAdjustmentForm.js';

Alpine.data('saleForm', saleForm);
Alpine.data('saleItem', saleItem);
Alpine.data('purchaseForm', purchaseForm);
Alpine.data('purchaseItem', purchaseItem);
Alpine.data('stockAdjustmentForm', stockAdjustmentForm);

// [BARU] 3. Jadikan TomSelect global agar bisa dipanggil di mana saja
window.TomSelect = TomSelect;
window.Alpine = Alpine;

Alpine.start();