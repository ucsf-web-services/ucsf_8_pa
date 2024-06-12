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
		this.formView = this._createFormView();

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
				console.log('plop 1')
				// editor.execute('insertCalloutBox')
				this._showUI();
			} );

			return button;
		} );
	}

	_createFormView() {
		const editor = this.editor;
		const formView = new UcsfCalloutboxView( editor.locale );

		// Execute the command after clicking the "Save" button.
		this.listenTo( formView, 'submit', () => {
			// Grab values from the abbreviation and title input fields.
			console.log(formView)
			const select = formView.element.querySelector('#align-dropdown').value;
			const radio = formView.element.querySelector('input[name="corner"]:checked').value;
			const formValues = { select, radio}
			editor.model.change( writer => {
				editor.execute('insertCalloutBox', formValues)
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

		this.formView.focus();
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
// export default class UcsfCalloutboxUI extends Plugin {
//   init() {
//     const editor = this.editor;
//     const t = editor.t;

//     editor.ui.componentFactory.add('UcsfCalloutbox', locale => {
//         const dropdownView = createDropdown(locale);

//         dropdownView.buttonView.set({
//             label: t('Edit Callout Settings'),
//             tooltip: true,
//             withText: true
//         });

//         // Create a form view.
//         const formView = this._createFormView(locale);

//         dropdownView.panelView.children.add(formView);

//         // Add a click event to the button.
//         dropdownView.buttonView.on('execute', () => {
//             const selectedElement = this._getSelectedCalloutBox();

//             if (selectedElement) {
//                 formView.alignInputView.fieldView.element.value = selectedElement.getAttribute('data-align') || 'left';
//                 formView.calloutInputView.fieldView.element.value = selectedElement.getAttribute('data-callout') || '0';

//                 formView.listenTo(formView.saveButtonView, 'execute', () => {
//                     editor.model.change(writer => {
//                         writer.setAttribute('data-align', formView.alignInputView.fieldView.element.value, selectedElement);
//                         writer.setAttribute('data-callout', formView.calloutInputView.fieldView.element.value, selectedElement);
//                     });

//                     dropdownView.isOpen = false;
//                 });
//             }
//         });

//         return dropdownView;
//     });
//   }

//   _createFormView(locale) {
//       const t = this.editor.t;
//       const formView = new View();

//       formView.setTemplate({
//           tag: 'form',
//           children: [
//               this._createLabeledInput(t('Align'), 'left', locale),
//               this._createLabeledInput(t('Corner Image'), '0', locale, 'radio'),
//               this._createButton(t('Save'), locale)
//           ]
//       });

//       return formView;
//   }

//   _createLabeledInput(labelText, defaultValue, locale, type = 'text') {
//       const labeledInput = new LabeledFieldView(locale, InputTextView);

//       labeledInput.set({
//           label: labelText,
//           fieldView: {
//               type: type,
//               value: defaultValue
//           }
//       });

//       return labeledInput;
//   }

//   _createButton(label, locale) {
//       const buttonView = new ButtonView(locale);

//       buttonView.set({
//           label: label,
//           withText: true,
//           tooltip: true
//       });

//       buttonView.extendTemplate({
//           attributes: {
//               class: 'ck-button-save'
//           }
//       });

//       return buttonView;
//   }

//   _getSelectedCalloutBox() {
//       const editor = this.editor;
//       const selection = editor.model.document.selection;

//       return selection.getSelectedElement();
//   }
// }
