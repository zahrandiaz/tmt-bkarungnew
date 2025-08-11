import './bootstrap';
import Alpine from 'alpinejs';

import saleForm from './components/saleForm';
import saleItem from './components/saleItem';
import purchaseForm from './components/purchaseForm';
import purchaseItem from './components/purchaseItem'; // <-- [BARU]

Alpine.data('saleForm', saleForm);
Alpine.data('saleItem', saleItem);
Alpine.data('purchaseForm', purchaseForm);
Alpine.data('purchaseItem', purchaseItem); // <-- [BARU]

window.Alpine = Alpine;
Alpine.start();