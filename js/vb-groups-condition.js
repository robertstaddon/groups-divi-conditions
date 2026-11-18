( function() {
	'use strict';

	var config = window.groupsDiviConditions || {};
	var i18n = config.i18n || {};
	var React = window.vendor && window.vendor.React;
	var apiFetch = window.wp && window.wp.apiFetch;
	var hooks = window.vendor && window.vendor.wp && window.vendor.wp.hooks;

	if ( ! React || ! apiFetch || ! hooks ) {
		return;
	}

	var createElement = React.createElement;
	var useEffect = React.useEffect;
	var useState = React.useState;
	var CONDITION = 'groupsMembership';

	function text( key, fallback ) {
		return i18n[ key ] || fallback;
	}

	function plain( value ) {
		if ( value && typeof value.toJS === 'function' ) {
			return value.toJS();
		}
		return value;
	}

	function settingsOf( condition ) {
		var source = plain( condition );
		var settings = source && source.conditionSettings;
		return settings && typeof settings === 'object' ? plain( settings ) : {};
	}

	function selectedGroups( settings ) {
		return Array.isArray( settings.groups ) ? settings.groups : [];
	}

	function groupLabelList( groups ) {
		var names = [];
		var i;

		for ( i = 0; i < groups.length; i++ ) {
			if ( groups[ i ] && groups[ i ].label ) {
				names.push( String( groups[ i ].label ) );
			}
		}

		return names.join( ', ' );
	}

	function GroupsSettings( props ) {
		var condition = props.condition;
		var setCondition = props.setCondition;
		var settings = settingsOf( condition );
		var selected = selectedGroups( settings );
		var rule = settings.displayRule === 'isNot' ? 'isNot' : 'is';
		var loaded = useState( null );
		var groups = loaded[0];
		var setGroups = loaded[1];
		var failed = useState( false );
		var loadFailed = failed[0];
		var setLoadFailed = failed[1];

		useEffect( function() {
			var active = true;

			apiFetch( { path: config.restPath || 'groups-divi-conditions/v1/groups' } ).then( function( response ) {
				if ( ! active ) {
					return;
				}
				setGroups( response && Array.isArray( response.groups ) ? response.groups : [] );
			} ).catch( function() {
				if ( active ) {
					setLoadFailed( true );
					setGroups( [] );
				}
			} );

			return function() {
				active = false;
			};
		}, [] );

		function update( patch ) {
			setCondition( function( current ) {
				var base = plain( current );
				if ( ! base || typeof base !== 'object' ) {
					base = plain( condition ) || {};
				}
				var nextSettings = {};
				var key;

				for ( key in ( base.conditionSettings || {} ) ) {
					if ( Object.prototype.hasOwnProperty.call( base.conditionSettings, key ) ) {
						nextSettings[ key ] = base.conditionSettings[ key ];
					}
				}

				for ( key in patch ) {
					if ( Object.prototype.hasOwnProperty.call( patch, key ) ) {
						nextSettings[ key ] = patch[ key ];
					}
				}

				var next = {};
				for ( key in base ) {
					if ( Object.prototype.hasOwnProperty.call( base, key ) ) {
						next[ key ] = base[ key ];
					}
				}
				next.conditionSettings = nextSettings;
				return next;
			} );
		}

		function toggleGroup( group, checked ) {
			var next = [];
			var i;
			var value = String( group.id );

			for ( i = 0; i < selected.length; i++ ) {
				if ( ! selected[ i ] || String( selected[ i ].value ) === value ) {
					continue;
				}
				next.push( selected[ i ] );
			}

			if ( checked ) {
				next.push( { label: group.name, value: value } );
			}

			update( { groups: next } );
		}

		function isChecked( id ) {
			var i;
			for ( i = 0; i < selected.length; i++ ) {
				if ( selected[ i ] && String( selected[ i ].value ) === String( id ) ) {
					return true;
				}
			}
			return false;
		}

		var known = {};
		var options = [];
		var i;

		if ( Array.isArray( groups ) ) {
			for ( i = 0; i < groups.length; i++ ) {
				known[ String( groups[ i ].id ) ] = true;
				options.push( groups[ i ] );
			}
		}

		var missing = [];
		for ( i = 0; i < selected.length; i++ ) {
			if ( selected[ i ] && ! known[ String( selected[ i ].value ) ] && groups !== null ) {
				missing.push( selected[ i ] );
			}
		}

		var list;
		if ( groups === null && ! loadFailed ) {
			list = createElement( 'p', { style: hintStyle }, text( 'loading', 'Loading groups…' ) );
		} else if ( loadFailed ) {
			list = createElement( 'p', { style: hintStyle }, text( 'loadError', 'Groups could not be loaded.' ) );
		} else if ( options.length === 0 && missing.length === 0 ) {
			list = createElement( 'p', { style: hintStyle }, text( 'none', 'No groups found.' ) );
		} else {
			var boxes = [];
			for ( i = 0; i < options.length; i++ ) {
				boxes.push( checkbox( options[ i ].id, options[ i ].name, isChecked( options[ i ].id ), toggleGroup.bind( null, options[ i ] ) ) );
			}
			for ( i = 0; i < missing.length; i++ ) {
				boxes.push( checkbox(
					missing[ i ].value,
					( missing[ i ].label || ( '#' + missing[ i ].value ) ) + ' — ' + text( 'missing', 'This group no longer exists.' ),
					true,
					toggleGroup.bind( null, { id: missing[ i ].value, name: missing[ i ].label || ( '#' + missing[ i ].value ) } )
				) );
			}
			list = createElement( 'div', null, boxes );
		}

		return createElement(
			'div',
			null,
			createElement( 'label', { style: labelStyle }, text( 'displayRule', 'Display rule' ) ),
			createElement(
				'select',
				{
					value: rule,
					onChange: function( event ) {
						update( { displayRule: event.target.value === 'isNot' ? 'isNot' : 'is' } );
					},
					style: selectStyle
				},
				createElement( 'option', { value: 'is' }, text( 'isMember', 'Is a member of' ) ),
				createElement( 'option', { value: 'isNot' }, text( 'isNotMember', 'Is not a member of' ) )
			),
			createElement( 'label', { style: labelStyle }, text( 'groups', 'Groups' ) ),
			list,
			createElement( 'p', { style: hintStyle }, text( 'anyOf', 'The visitor matches if they belong to any selected group.' ) )
		);
	}

	function checkbox( id, label, checked, onChange ) {
		return createElement(
			'label',
			{ key: String( id ), style: checkStyle },
			createElement( 'input', {
				type: 'checkbox',
				checked: checked,
				onChange: function( event ) {
					onChange( event.target.checked );
				}
			} ),
			createElement( 'span', null, label )
		);
	}

	var labelStyle = { display: 'block', margin: '8px 0 4px', fontSize: '12px', fontWeight: 600 };
	var selectStyle = { width: '100%', marginBottom: '8px' };
	var checkStyle = { display: 'flex', gap: '8px', alignItems: 'flex-start', margin: '4px 0', fontSize: '13px' };
	var hintStyle = { margin: '6px 0 0', fontSize: '12px', opacity: 0.75 };

	hooks.addFilter(
		'divi.fieldLibrary.conditionalDisplay.conditionsStore',
		'groups-divi-conditions/store',
		function( conditions ) {
			var next = Array.isArray( conditions ) ? conditions.slice() : [];
			var i;

			for ( i = 0; i < next.length; i++ ) {
				if ( next[ i ] && next[ i ].name === CONDITION ) {
					return next;
				}
			}

			next.push( {
				name: CONDITION,
				label: text( 'label', 'Groups Membership' ),
				category: 'user'
			} );

			return next;
		}
	);

	hooks.addFilter(
		'divi.fieldLibrary.conditionalDisplay.initialCustomItemEdit',
		'groups-divi-conditions/initial',
		function( previous, name, id, operator ) {
			if ( name !== CONDITION ) {
				return previous;
			}

			return {
				id: id,
				conditionName: CONDITION,
				conditionSettings: {
					displayRule: 'is',
					groups: [],
					enableCondition: 'on'
				},
				operator: operator || 'OR'
			};
		}
	);

	hooks.addFilter(
		'divi.fieldLibrary.conditionalDisplay.customSettingsComponent',
		'groups-divi-conditions/settings',
		function( previous, condition, setCondition ) {
			if ( ! condition || condition.conditionName !== CONDITION ) {
				return previous;
			}

			return createElement( GroupsSettings, {
				condition: condition,
				setCondition: setCondition
			} );
		}
	);

	hooks.addFilter(
		'divi.fieldLibrary.conditionalDisplay.customTitle',
		'groups-divi-conditions/title',
		function( previous, condition ) {
			if ( ! condition || condition.conditionName !== CONDITION ) {
				return previous;
			}

			var names = groupLabelList( selectedGroups( settingsOf( condition ) ) );
			return names ? text( 'label', 'Groups Membership' ) + ': ' + names : text( 'label', 'Groups Membership' );
		}
	);

	hooks.addFilter(
		'divi.fieldLibrary.conditionalDisplay.tooltips.customTooltip',
		'groups-divi-conditions/tooltip',
		function( previous, name, settings ) {
			if ( name !== CONDITION ) {
				return previous;
			}

			var groups = selectedGroups( settings || {} );
			var names = groupLabelList( groups );

			if ( ! names ) {
				return text( 'tooltipEmpty', 'No groups selected.' );
			}

			var template = settings && settings.displayRule === 'isNot'
				? text( 'tooltipIsNot', 'Display when the visitor is not a member of %s.' )
				: text( 'tooltipIs', 'Display when the visitor is a member of %s.' );

			return template.replace( '%s', names );
		}
	);
}() );
