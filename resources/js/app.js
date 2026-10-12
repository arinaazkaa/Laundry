import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import transactionForm from './transaction-form';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.data('transactionForm', transactionForm);
Alpine.start();