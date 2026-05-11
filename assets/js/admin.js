( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var picker = document.querySelector( '.fsfew-page-picker' );

		if ( ! picker ) {
			return;
		}

		var searchInput = picker.querySelector( '.fsfew-page-search' );
		var options = Array.prototype.slice.call( picker.querySelectorAll( '.fsfew-page-option' ) );
		var selectedContainer = picker.querySelector( '.fsfew-selected-pages' );
		var emptyLabel = picker.getAttribute( 'data-empty-label' ) || 'No pages selected yet.';

		function renderSelectedPills() {
			var checkedInputs = options
				.map( function ( option ) {
					return option.querySelector( 'input[type="checkbox"]' );
				} )
				.filter( function ( input ) {
					return input && input.checked;
				} );

			selectedContainer.innerHTML = '';

			if ( ! checkedInputs.length ) {
				var emptyNode = document.createElement( 'span' );
				emptyNode.className = 'fsfew-selected-empty';
				emptyNode.textContent = emptyLabel;
				selectedContainer.appendChild( emptyNode );
				return;
			}

			checkedInputs.forEach( function ( input ) {
				var title = input.getAttribute( 'data-page-title' ) || input.value;
				var pill = document.createElement( 'button' );
				var text = document.createElement( 'span' );
				var close = document.createElement( 'span' );

				pill.type = 'button';
				pill.className = 'fsfew-page-pill';
				pill.setAttribute( 'data-page-id', input.value );
				text.textContent = title;
				close.setAttribute( 'aria-hidden', 'true' );
				close.textContent = '×';
				pill.appendChild( text );
				pill.appendChild( close );
				selectedContainer.appendChild( pill );
			} );
		}

		function filterOptions() {
			var query = searchInput.value.trim().toLowerCase();

			options.forEach( function ( option ) {
				var title = ( option.getAttribute( 'data-page-title' ) || '' ).toLowerCase();
				option.hidden = query && -1 === title.indexOf( query );
			} );
		}

		options.forEach( function ( option ) {
			var input = option.querySelector( 'input[type="checkbox"]' );

			if ( input ) {
				input.addEventListener( 'change', renderSelectedPills );
			}
		} );

		searchInput.addEventListener( 'input', filterOptions );

		selectedContainer.addEventListener( 'click', function ( event ) {
			var pill = event.target.closest( '.fsfew-page-pill' );

			if ( ! pill ) {
				return;
			}

			var pageId = pill.getAttribute( 'data-page-id' );
			var input = picker.querySelector( 'input[type="checkbox"][value="' + pageId + '"]' );

			if ( input ) {
				input.checked = false;
				renderSelectedPills();
			}
		} );

		renderSelectedPills();
		filterOptions();
	} );
}() );
