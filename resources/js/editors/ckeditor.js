import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

export function initCkeditors() {
    document.querySelectorAll('textarea.editorfield:not(.ck-initialized)').forEach((el) => {
        ClassicEditor
            .create(el)
            .then(() => {
                el.classList.add('ck-initialized');
            })
            .catch((err) => console.error('CKEditor init error:', err));
    });
}

export default initCkeditors; // ← voeg deze toe
