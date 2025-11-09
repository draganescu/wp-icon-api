import htm from './htm.js';
import IconControl from './IconControl.js';

/**
 * Icon Block for WP Icon API.
 *
 * @package WP_Icon_API
 * @since 0.1.0
 */

( function ( blocks, editor, i18n, element, blockEditor ) {
	const el = element.createElement;
	const html = htm.bind( el );
	const __ = i18n.__;
	const useBlockProps = blockEditor.useBlockProps;
	const InspectorControls = blockEditor.InspectorControls;

	/**
	 * Default star icon SVG.
	 */
	const DEFAULT_ICON = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.568 8.332 1.151-6.064 5.828 1.48 8.279-7.416-3.967-7.417 3.967 1.481-8.279-6.064-5.828 8.332-1.151z"/></svg>';

	/**
	 * Safely render SVG content.
	 */
	const renderSVG = ( svgContent ) => {
		if ( ! svgContent || typeof svgContent !== 'string' ) {
			return null;
		}

		// Basic validation
		if ( ! svgContent.includes( '<svg' ) || ! svgContent.includes( '</svg>' ) ) {
			return null;
		}

		// Create a temporary div to parse SVG
		const tempDiv = document.createElement( 'div' );
		tempDiv.innerHTML = svgContent;
		const svg = tempDiv.querySelector( 'svg' );

		if ( ! svg ) {
			return null;
		}

		// Ensure SVG has proper attributes
		if ( ! svg.getAttribute( 'xmlns' ) ) {
			svg.setAttribute( 'xmlns', 'http://www.w3.org/2000/svg' );
		}
		if ( ! svg.getAttribute( 'width' ) ) {
			svg.setAttribute( 'width', '24' );
		}
		if ( ! svg.getAttribute( 'height' ) ) {
			svg.setAttribute( 'height', '24' );
		}
		if ( ! svg.getAttribute( 'viewBox' ) ) {
			svg.setAttribute( 'viewBox', '0 0 24 24' );
		}

		// Set default styling
		svg.style.display = 'block';
		svg.style.width = '24px';
		svg.style.height = '24px';

		return html`<div dangerouslySetInnerHTML=${{ __html: svg.outerHTML }} />`;
	};

	blocks.registerBlockType( 'wp-icon-api/icon-block', {
		title: __( 'Icon Block', 'wp-icon-api' ),
		icon: 'star-filled',
		category: 'widgets',
		description: __( 'Display an icon from the WP Icon API.', 'wp-icon-api' ),

		attributes: {
			icon: {
				type: 'string',
				default: DEFAULT_ICON,
			},
		},

		example: {
			attributes: {
				icon: DEFAULT_ICON,
			},
		},

		edit: function ( props ) {
			const icon = props.attributes.icon;
			const setAttributes = props.setAttributes;

			const blockProps = useBlockProps( {
				className: 'wp-icon-api-icon-block',
			} );

			const renderedIcon = renderSVG( icon );

			return html`
				<figure ...${blockProps}>
					${renderedIcon || html`
						<div style=${{
							width: '24px',
							height: '24px',
							border: '1px dashed #ccc',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							color: '#666'
						}}>
							${__( 'No icon', 'wp-icon-api' )}
						</div>
					`}
				</figure>
				<${InspectorControls}>
					<${IconControl} 
						icon=${icon}
						setAttributes=${setAttributes}
					/>
				</${InspectorControls}>
			`;
		},

		save: function ( props ) {
			const icon = props.attributes.icon;
			const renderedIcon = renderSVG( icon );

			return html`
				<figure class=${props.className}>
					${renderedIcon || html`
						<div style=${{
							width: '24px',
							height: '24px',
							border: '1px dashed #ccc',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							color: '#666'
						}}>
							${__( 'No icon', 'wp-icon-api' )}
						</div>
					`}
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