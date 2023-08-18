import htm  from './htm.js';
import IconControl from './IconControl.js';
/**
 * Hello World: Step 4
 *
 * Adding extra controls: built-in alignment toolbar.
 */

( function ( blocks, editor, i18n, element, blockEditor ) {
	const el = element.createElement;
	const html = htm.bind(el);
	const __ = i18n.__;
	const RichText = editor.RichText;
	const useBlockProps = blockEditor.useBlockProps;
	const InspectorControls = blockEditor.InspectorControls; 
	const BlockControls = blockEditor.BlockControls;

	blocks.registerBlockType( 'wp-icon-api/icon-block', {
		title: __( 'Example block that consumes icon API' ),
		icon: 'star-filled',
		category: 'widgets',

		attributes: {
			icon: {
				type: 'string',
				default: "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'><path d='M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z'/></svg>"
			},
		},

		example: {
			attributes: {
				icon: "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24'><path d='M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z'/></svg>"
			},
		},

		edit: function ( props ) {
			const icon = props.attributes.icon;

			const blockProps = useBlockProps( {
				key: 'icon',
				className: props.className,
			} );

			return html`
				<figure ...${blockProps}>
					<img src="data:image/svg+xml;utf8,${encodeURIComponent(icon)}" />
				</figure>
				<${InspectorControls}>
					<${IconControl} ...${props}/>
				</${InspectorControls}>
			`;
		},

		save: function ( props ) {
			const icon = props.attributes.icon;
			return html`
				<figure class=${props.className}>
					<img src="data:image/svg+xml;utf8,${encodeURIComponent(icon)}" />
				</figure>
			`;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.editor,
	window.wp.i18n,
	window.wp.element,
	window.wp.blockEditor
);