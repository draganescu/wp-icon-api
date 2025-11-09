import htm from './htm.js';

export default function IconControl( props ) {
	const el = window.wp.element.createElement;
	const html = htm.bind( el );
	const PanelBody = window.wp.components.PanelBody;
	const PanelRow = window.wp.components.PanelRow;
	const TextControl = window.wp.components.TextControl;
	const Spinner = window.wp.components.Spinner;
	const apiFetch = window.wp.apiFetch;
	const addQueryArgs = window.wp.url.addQueryArgs;
	const useState = window.wp.element.useState;
	const useEffect = window.wp.element.useEffect;
	const useRef = window.wp.element.useRef;
	const __ = window.wp.i18n.__;

	const [ icons, setIcons ] = useState( [] );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ error, setError ] = useState( null );
	const debounceTimer = useRef( null );

	/**
	 * Debounced search function.
	 */
	const debouncedSearch = ( searchTerm ) => {
		if ( debounceTimer.current ) {
			clearTimeout( debounceTimer.current );
		}

		setIsLoading( true );
		setError( null );

		debounceTimer.current = setTimeout( async () => {
			try {
				const queryParams = { search: searchTerm };
				const response = await apiFetch( { 
					path: addQueryArgs( '/wp-icon-api/v1/icons/search', queryParams ),
					method: 'GET'
				} );
				setIcons( Array.isArray( response ) ? response : [] );
			} catch ( err ) {
				console.error( 'Error fetching icons:', err );
				setError( __( 'Failed to load icons. Please try again.', 'wp-icon-api' ) );
				setIcons( [] );
			} finally {
				setIsLoading( false );
			}
		}, 300 );
	};

	/**
	 * Handle icon selection with proper error handling.
	 */
	const setIcon = async ( iconSrc ) => {
		if ( ! iconSrc ) {
			return;
		}

		try {
			const response = await fetch( iconSrc );
			if ( ! response.ok ) {
				throw new Error( `HTTP error! status: ${response.status}` );
			}
			const svg = await response.text();
			
			// Basic SVG validation
			if ( ! svg.includes( '<svg' ) || ! svg.includes( '</svg>' ) ) {
				throw new Error( 'Invalid SVG content' );
			}
			
			props.setAttributes( { icon: svg } );
		} catch ( err ) {
			console.error( 'Error loading icon:', err );
			setError( __( 'Failed to load icon. Please try another one.', 'wp-icon-api' ) );
		}
	};

	/**
	 * Clean up debounce timer on unmount.
	 */
	useEffect( () => {
		return () => {
			if ( debounceTimer.current ) {
				clearTimeout( debounceTimer.current );
			}
		};
	}, [] );

	/**
	 * Render icon grid.
	 */
	const iconsGrid = icons.map( ( icon, index ) => {
		return html`
			<button 
				key=${index}
				onClick=${() => setIcon( icon.src )}
				style=${{
					border: '1px solid #ddd',
					background: '#fff',
					padding: '8px',
					cursor: 'pointer',
					borderRadius: '4px'
				}}
				title=${icon.label || icon.name}
			>
				<img 
					width=24 
					height=24 
					src="${icon.src}" 
					alt="${icon.label || icon.name}"
					style=${{ display: 'block' }}
				/>
			</button>
		`;
	});

	return html`
		<${PanelBody} title=${__( 'Icon Selection', 'wp-icon-api' )}>
			<${PanelRow}>
				<${TextControl}
					label=${__( 'Search Icons', 'wp-icon-api' )}
					placeholder=${__( 'Type to search...', 'wp-icon-api' )}
					onChange=${( value ) => {
						debouncedSearch( value );
					}}
				/>
			</${PanelRow}>
			<${PanelRow}>
				${isLoading && html`
					<div style=${{ display: 'flex', alignItems: 'center', gap: '10px' }}>
						<${Spinner} />
						<span>${__( 'Loading icons...', 'wp-icon-api' )}</span>
					</div>
				`}
				${error && html`
					<div style=${{ color: '#d63638', padding: '10px', background: '#fcf0f1', borderRadius: '4px' }}>
						${error}
					</div>
				`}
				${!isLoading && !error && icons.length === 0 && html`
					<p>${__( 'No icons found. Try a different search term.', 'wp-icon-api' )}</p>
				`}
				${!isLoading && !error && icons.length > 0 && html`
					<div style=${{
						display: 'grid',
						gridTemplateColumns: 'repeat(4, 1fr)',
						gap: '8px',
						marginTop: '10px'
					}}>
						${iconsGrid}
					</div>
				`}
			</${PanelRow}>
		</${PanelBody}>
	`;
}