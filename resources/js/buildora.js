console.log("Buildora.js loaded!");

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

console.log("Alpine.js loaded!");

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import Sortable from 'sortablejs';
window.Sortable = Sortable;

window.initCkeditors = function () {
    const promises = [];

    document.querySelectorAll('textarea.editorfield:not(.ck-initialized)').forEach((el) => {
        const promise = ClassicEditor
            .create(el)
            .then((editor) => {
                el.classList.add('ck-initialized');
                el._ckeditorInstance = editor;
            })
            .catch(console.error);

        promises.push(promise);
    });

    return Promise.all(promises);
};

document.addEventListener('DOMContentLoaded', () => {
    window.initCkeditors();
});
