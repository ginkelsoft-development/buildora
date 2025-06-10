console.log("Buildora.js loaded!");

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

console.log("Alpine.js loaded!");

function updateRepeatableFieldNames(form) {
    form.querySelectorAll('[data-repeatable-index]').forEach((row) => {
        const index = row.dataset.repeatableIndex;
        row.querySelectorAll('[name]').forEach((el) => {
            if (el.name && el.name.includes('${index}')) {
                el.name = el.name.replaceAll('${index}', index);
            }
        });
    });
}

window.updateRepeatableFieldNames = updateRepeatableFieldNames;

document.addEventListener('submit', (e) => {
    const form = e.target.closest('form');
    if (form) {
        updateRepeatableFieldNames(form);
    }
}, true);
