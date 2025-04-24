/**
 * This configuration was generated using the CKEditor 5 Builder. You can modify it anytime using this link:
 * https://ckeditor.com/ckeditor-5/builder/#installation/NodgNARATAdArDADBSBGRAWOBmDAOdRbKPKbATgrwDYRVtrEQNyQpzzF1O6Q9zqKCAFMAdikRhgqMJMkz5AXUgBjCk2pQIioA===
 */

import {
	ClassicEditor,
	Alignment,
	AutoLink,
	Autosave,
	Bold,
	Essentials,
	FontBackgroundColor,
	FontColor,
	FontFamily,
	FontSize,
	Heading,
	Indent,
	IndentBlock,
	Italic,
	Link,
	Paragraph,
	PlainTableOutput,
	Subscript,
	Superscript,
	Table,
	TableCaption,
	TableCellProperties,
	TableColumnResize,
	TableLayout,
	TableProperties,
	TableToolbar,
	Underline
} from 'ckeditor5';

const LICENSE_KEY =
	'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3NDY1NzU5OTksImp0aSI6ImFmM2FiZDI4LTYyOTEtNDQ4Zi04ZTNlLWQyMGY1ZjI1NDg4ZiIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6ImYyNDc5MjllIn0.7OQ4pZfZPYg-xAxV1zxWGDoc11seMvH9x9fteoxDN58VGUQ_mcMXRXLKWSnGQ-l40DjJElqML6TuHCJvyL49Lw';

const editorConfig = {
	toolbar: {
		items: [
			'heading',
			'|',
			'fontSize',
			'fontFamily',
			'fontColor',
			'fontBackgroundColor',
			'|',
			'bold',
			'italic',
			'underline',
			'subscript',
			'superscript',
			'|',
			'link',
			'insertTable',
			'insertTableLayout',
			'|',
			'alignment',
			'|',
			'outdent',
			'indent'
		],
		shouldNotGroupWhenFull: false
	},
	plugins: [
		Alignment,
		AutoLink,
		Autosave,
		Bold,
		Essentials,
		FontBackgroundColor,
		FontColor,
		FontFamily,
		FontSize,
		Heading,
		Indent,
		IndentBlock,
		Italic,
		Link,
		Paragraph,
		PlainTableOutput,
		Subscript,
		Superscript,
		Table,
		TableCaption,
		TableCellProperties,
		TableColumnResize,
		TableLayout,
		TableProperties,
		TableToolbar,
		Underline
	],
	fontFamily: {
		supportAllValues: true
	},
	fontSize: {
		options: [10, 12, 14, 'default', 18, 20, 22],
		supportAllValues: true
	},
	heading: {
		options: [
			{
				model: 'paragraph',
				title: 'Paragraph',
				class: 'ck-heading_paragraph'
			},
			{
				model: 'heading1',
				view: 'h1',
				title: 'Heading 1',
				class: 'ck-heading_heading1'
			},
			{
				model: 'heading2',
				view: 'h2',
				title: 'Heading 2',
				class: 'ck-heading_heading2'
			},
			{
				model: 'heading3',
				view: 'h3',
				title: 'Heading 3',
				class: 'ck-heading_heading3'
			},
			{
				model: 'heading4',
				view: 'h4',
				title: 'Heading 4',
				class: 'ck-heading_heading4'
			},
			{
				model: 'heading5',
				view: 'h5',
				title: 'Heading 5',
				class: 'ck-heading_heading5'
			},
			{
				model: 'heading6',
				view: 'h6',
				title: 'Heading 6',
				class: 'ck-heading_heading6'
			}
		]
	},
	initialData:
		'<h2>Hi<h2>',
	licenseKey: LICENSE_KEY,
	link: {
		addTargetToExternalLinks: true,
		defaultProtocol: 'https://',
		decorators: {
			toggleDownloadable: {
				mode: 'manual',
				label: 'Downloadable',
				attributes: {
					download: 'file'
				}
			}
		}
	},
	placeholder: 'Type or paste your content here!',
	table: {
		contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
	}
};

document.querySelectorAll('#question, #answer').forEach(element => {
    ClassicEditor.create(element, editorConfig)
        .catch(error => {
            console.error(error);
        });
});

