import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Ignore benign AlpineJS transition cancellation rejections
window.addEventListener('unhandledrejection', function (event) {
    if (event.reason && event.reason.isFromCancelledTransition) {
        event.preventDefault();
    }
});
import TomSelect from 'tom-select';
import Swal from 'sweetalert2';

Alpine.plugin(collapse);
window.Alpine = Alpine;

/**
 * Bulk ICS/PAR document download component.
 * Server-side values are injected via data-* attributes on the host element:
 *   data-action  — form POST URL (route)
 *   data-csrf    — CSRF token
 *   data-assets  — JSON-encoded assets array
 */
Alpine.data('bulkDocDownload', (assets) => ({
    docType: 'ICS',
    selectedAssets: [],
    initError: null,
    assetsData: [],

    init() {
        try {
            const rootEl = this.$root || this.$el.closest('[data-assets]') || this.$el;
            this.assetsData = Array.isArray(assets) ? assets : JSON.parse(rootEl.dataset.assets || '[]');
        } catch (e) {
            console.error('bulkDocDownload: failed to parse assets data', e);
            this.initError = 'Unable to load assets for this employee. Please refresh the page.';
            this.assetsData = [];
        }
    },

    get filteredAssets() {
        return this.assetsData.filter(asset => {
            const cost = parseFloat(asset.unit_cost) || 0;
            return this.docType === 'ICS' ? cost <= 49999 : cost > 49999;
        });
    },

    toggleSelectAll() {
        const visible = this.filteredAssets;
        const allSelected = visible.every(a => this.selectedAssets.includes(a.id));
        if (allSelected) {
            this.selectedAssets = this.selectedAssets.filter(id => !visible.some(v => v.id === id));
        } else {
            visible.forEach(a => {
                if (!this.selectedAssets.includes(a.id)) {
                    this.selectedAssets.push(a.id);
                }
            });
        }
    },

    isAllSelected() {
        const visible = this.filteredAssets;
        if (visible.length === 0) return false;
        return visible.every(a => this.selectedAssets.includes(a.id));
    },

    downloadDocuments() {
        const rootEl = this.$root || this.$el.closest('[data-action]') || this.$el;
        const action = rootEl.dataset.action;
        const csrf   = rootEl.dataset.csrf;

        if (!action || action === 'undefined' || action === '') {
            console.error('bulkDocDownload: data-action is missing or undefined — aborting download request. Check that the host element has a valid data-action attribute rendered by Blade.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrf;
        form.appendChild(csrfInput);

        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'doc_type';
        typeInput.value = this.docType;
        form.appendChild(typeInput);

        this.selectedAssets.forEach(id => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'selected_ids[]';
            idInput.value = id;
            form.appendChild(idInput);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    },
}));

Alpine.start();

window.TomSelect = TomSelect;
window.Swal = Swal;
