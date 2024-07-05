import {Plugin} from 'ckeditor5/src/core';
import {ButtonView, ContextualBalloon, clickOutsideHandler} from 'ckeditor5/src/ui';
import UcsfCalloutboxView from './ucsfcalloutboxview';

export default class UcsfCalloutboxUI extends Plugin {
    static get requires() {
		return [ ContextualBalloon ];
	}

	init() {
		const editor = this.editor;
		
        // Create the balloon and the form view.
		this._balloon = this.editor.plugins.get( ContextualBalloon );
		const viewDocument = this.editor.editing.view.document;

		// Handle click on view document and show panel when selection is placed inside the link element.
		// Keep panel open until selection will be inside the same link element.
		this.listenTo( viewDocument, 'click', () => {
			
			const isCallout = this._getSelectedElement();

			if ( isCallout ) {
				this.formView = this._createFormView();
				this._showUI();
			}
		} );

		editor.ui.componentFactory.add( 'ucsfCalloutbox', (locale) => {
			const command = editor.commands.get('insertCalloutBox');
			const button = new ButtonView(locale);
			button.set({
                label: 'UcsfCalloutbox',
                withText: true,
                tooltip: true,
            });

			button.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');
			// Show the UI on button click.
			this.listenTo( button, 'execute', () => {
				// editor.execute('insertCalloutBox')
				editor.execute('insertCalloutBox')
				this.formView = this._createFormView();
				this._showUI();
			} );

			return button;
		} );
	}
	_createFormView() {
		const editor = this.editor;
		const formView = new UcsfCalloutboxView( editor);

		// Execute the command after clicking the "Save" button.
		this.listenTo( formView, 'submit', () => {
			// Grab values from the abbreviation and title input fields.
			const align = formView.element.querySelector('#align-dropdown').value;
			const image = formView.element.querySelector('input[name="corner"]:checked').value;
			const formValues = { align, image}
			editor.model.change( writer => {
				const isCallout = this._getSelectedElement()
				writer.setAttribute('data-align', align, isCallout)
				writer.setAttribute('data-image', image, isCallout)
			} );

            // Hide the form view after submit.
			this._hideUI();
		} );

		// Hide the form view after clicking the "Cancel" button.
		this.listenTo( formView, 'cancel', () => {
			this._hideUI();
		} );

		// Hide the form view when clicking outside the balloon.
		clickOutsideHandler( {
			emitter: formView,
			activator: () => this._balloon.visibleView === formView,
			contextElements: [ this._balloon.view.element ],
			callback: () => this._hideUI()
		} );

		return formView;
	}

	_showUI() {
		this._balloon.add( {
			view: this.formView,
			position: this._getBalloonPositionData()
		} );

	}

	_hideUI() {
		// Clear the input field values and reset the form.
		// this.formView.abbrInputView.fieldView.value = '';
		// this.formView.titleInputView.fieldView.value = '';
		// this.formView.element.reset();

		this._balloon.remove( this.formView );

		// Focus the editing view after inserting the abbreviation so the user can start typing the content
		// right away and keep the editor focused.
		this.editor.editing.view.focus();
	}
	_getSelectedElement() {
		const model = this.editor.model;
      	const selection = model.document.selection;
      	const selectedElement = selection.getSelectedElement();
		if (selectedElement && selectedElement.name == "ucsfcalloutbox") {
			return selectedElement
		} else {
			return false
		}
	}
	_getBalloonPositionData() {
		const view = this.editor.editing.view;
		const viewDocument = view.document;
		let target = null;

		// Set a target position by converting view selection range to DOM
		target = () => view.domConverter.viewRangeToDom( viewDocument.selection.getFirstRange() );

		return {
			target
		};
	}
}
