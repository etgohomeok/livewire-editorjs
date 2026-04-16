import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import ImageTool from '@editorjs/image';
import Delimiter from '@editorjs/delimiter';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import Warning from '@editorjs/warning';
import Table from '@editorjs/table';

const STATIC_TOOLS = {
    header: Header,
    delimiter: Delimiter,
    list: { class: List, inlineToolbar: true },
    quote: { class: Quote, inlineToolbar: true },
    warning: { class: Warning, inlineToolbar: true },
    table: { class: Table, inlineToolbar: true },
};

window.LivewireEditorjs = {
    init({ holder, component, tools, placeholder, readOnly, initialData }) {
        const enabled = Array.isArray(tools) && tools.length > 0
            ? tools
            : ['header', 'paragraph', 'image', 'delimiter', 'list', 'quote', 'warning', 'table'];

        const toolsConfig = {};

        for (const name of enabled) {
            if (name === 'paragraph') {
                continue;
            }

            if (name === 'image') {
                toolsConfig.image = {
                    class: ImageTool,
                    config: {
                        uploader: {
                            uploadByFile: (file) => uploadByFile(component, file),
                            uploadByUrl: (url) => uploadByUrl(component, url),
                        },
                    },
                };
                continue;
            }

            if (STATIC_TOOLS[name]) {
                toolsConfig[name] = STATIC_TOOLS[name];
            }
        }

        const editor = new EditorJS({
            holder,
            placeholder: placeholder || undefined,
            readOnly: readOnly === true,
            data: initialData || { blocks: [] },
            tools: toolsConfig,
            onChange: async () => {
                const saved = await editor.save();
                component.set('value', saved);
            },
        });
    },
};

function uploadByFile(component, file) {
    return new Promise((resolve, reject) => {
        component.upload(
            'photo',
            file,
            () => {
                component.storeUploadedImage().then((url) => {
                    resolve({ success: 1, file: { url } });
                }).catch(() => reject('Upload failed'));
            },
            () => reject('Upload failed')
        );
    });
}

function uploadByUrl(component, url) {
    return component.uploadImageByUrl(url).then((storedUrl) => ({
        success: 1,
        file: { url: storedUrl },
    }));
}
