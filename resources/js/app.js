import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import TomSelect from 'tom-select';
import Swal from 'sweetalert2';

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();

window.TomSelect = TomSelect;
window.Swal = Swal;
