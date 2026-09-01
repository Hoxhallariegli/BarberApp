import './bootstrap';
import './dark';
import 'flatpickr';
import "flatpickr/dist/flatpickr.css";
import ui from '@alpinejs/ui';
import Swal from 'sweetalert2';

window.Swal = Swal;

// Toast Configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Global Toast Listener
window.addEventListener('toast', event => {
    const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
    Toast.fire({
        icon: data.type || 'success',
        title: data.message
    });
});

import * as FilePond from 'filepond';
window.FilePond = FilePond;

Alpine.plugin(ui);

import Prism from 'prismjs';
import 'prismjs/plugins/normalize-whitespace/prism-normalize-whitespace';
import 'prismjs/themes/prism-tomorrow.css';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-css';
import 'prismjs/components/prism-javascript';

Prism.plugins.NormalizeWhitespace.setDefaults({
	'remove-trailing': true,
	'remove-indent': true,
	'left-trim': true,
	'right-trim': true
});

Prism.highlightAll();
