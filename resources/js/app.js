import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import flatpickr from 'flatpickr';
import { Vietnamese } from 'flatpickr/dist/l10n/vn.js';
import Chart from 'chart.js/auto';

// Alpine
window.Alpine = Alpine;
Alpine.start();

// TomSelect default
window.TomSelect = TomSelect;

// Flatpickr default locale
flatpickr.setDefaults({ locale: Vietnamese, dateFormat: 'd/m/Y' });
window.flatpickr = flatpickr;

// Chart.js
window.Chart = Chart;
