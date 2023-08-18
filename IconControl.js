import htm  from './htm.js';

export default function IconControl( props ) {
	const el = window.wp.element.createElement;
	const html = htm.bind(el);
	const PanelBody = window.wp.components.PanelBody;
	const PanelRow = window.wp.components.PanelRow;
	const TextControl = window.wp.components.TextControl;
	const apiFetch = window.wp.apiFetch;
	const fetch = window.fetch;
	const addQueryArgs = window.wp.url.addQueryArgs;
	const useState = window.wp.element.useState;
	const Grid = window.wp.components.__experimentalGrid;

	const [ icons, setIcons ] = useState( [] );

	
	const getIcons = async ( seachItem ) => {
		const queryParams = { search: seachItem }; 
		return await apiFetch( { path: addQueryArgs( '/wp/v2/icons/search', queryParams ) } );;
	};

	const setIcon = ( icon ) => {
		fetch( icon )
			.then(response => response.text())
			.then(svg => {
				props.setAttributes( { icon: svg } );
			} );
	};

	console.log( icons )

	const iconsGrid = icons.map( ( icon ) => {
		return html`
			<button onClick=${() => setIcon( icon.src )}>
				<img src="${icon.src}" />
			</button>
		`;
	});

	return html`
		<${PanelBody} title="Icon">
			<${PanelRow}>
				<${TextControl}
					label="Icon"
					onChange=${async ( value ) => {
						setIcons( await getIcons( value ) );
					}}
				/>
			</${PanelRow}>
			<${PanelRow}>
				${icons.length === 0 && html`<p>No icons found</p>`}
				<${Grid} columns="4">
					${icons.length > 0 && iconsGrid}
				</${Grid}>
			</${PanelRow}>
		</${PanelBody}>
	`;
}