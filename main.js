import {
	ClassicEditor,
	Autosave,
	Bold,
	Essentials,
	FindAndReplace,
	Italic,
	Paragraph,
	PasteFromOffice,
	PlainTableOutput,
	Subscript,
	Superscript,
	Table,
	TableCaption,
	TableLayout,
	TableToolbar,
	Underline,
	BlockQuote,
	Undo,
} from 'ckeditor5';

const LICENSE_KEY = 'GPL'; // Free license placeholder

const editorConfig = {
	licenseKey: LICENSE_KEY,

	toolbar: {
		items: [
			'undo',
			'redo',
			'|',
			'findAndReplace',
			'|',
			'bold',
			'italic',
			'underline',
			'subscript',
			'superscript',
			'|',
			'insertTable',
			'blockquote'
		],
		shouldNotGroupWhenFull: false
	},

	plugins: [
		Essentials,
		Paragraph,
		Autosave,
		Bold,
		Italic,
		Underline,
		Subscript,
		Superscript,
		FindAndReplace,
		PasteFromOffice,
		PlainTableOutput,
		Table,
		TableCaption,
		TableLayout,
		TableToolbar,
		BlockQuote,
		Undo,
	],

	table: {
		contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
	}

	// ✅ Removed: initialData
};

document.querySelectorAll('#question, #answer').forEach(element => {
	ClassicEditor.create(element, editorConfig)
		.catch(error => {
			console.error(error);
		});
});
