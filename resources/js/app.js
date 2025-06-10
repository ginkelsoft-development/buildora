import './buildora';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

window.initCkeditors = function () {
    document.querySelectorAll('textarea.editorfield:not(.ck-initialized)').forEach((el) => {
        ClassicEditor
            .create(el)
            .then(() => el.classList.add('ck-initialized'))
            .catch(console.error);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    window.initCkeditors();
});

