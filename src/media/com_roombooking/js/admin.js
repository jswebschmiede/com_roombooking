import EasyMDE from 'easymde';

import 'easymde/dist/easymde.min.css';
import '../css/admin.css';

document.addEventListener('DOMContentLoaded', function () {
    const placeholders = Joomla.getOptions('com_roombooking').placeholders;

    // Convert placeholders object to array
    const placeholderItems = Object.entries(placeholders).map(([key, value]) => ({
        name: key,
        action: (editor) => {
            editor.codemirror.replaceSelection(key);
        },
        title: key,
        text: value,
    }));

    const easyMDE = new EasyMDE({
        element: document.getElementById('jform_body'),
        hideIcons: ['fullscreen', 'side-by-side', 'image', 'upload-image', 'heading'],
        showIcons: [
            'heading-1',
            'heading-2',
            'heading-3',
            'undo',
            'redo',
            'horizontal-rule',
            'guide',
        ],
        spellChecker: false,
        status: false,
        promptURLs: true,
        toolbar: [
            'heading-1',
            'heading-2',
            'heading-3',
            'bold',
            'italic',
            '|',
            'quote',
            'unordered-list',
            'ordered-list',
            '|',
            'link',
            'horizontal-rule',
            '|',
            'undo',
            'redo',
            '|',
            'preview',
            'guide',
            '|',
            {
                name: 'custom',
                className: 'fa fa-plus',
                title: 'Placeholder',
                children: placeholderItems,
            },
        ],
    });
});
