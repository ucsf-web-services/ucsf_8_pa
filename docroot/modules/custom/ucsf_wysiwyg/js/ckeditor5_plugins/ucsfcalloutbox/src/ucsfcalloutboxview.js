import {
    View,
    LabeledFieldView,
    createLabeledInputText,
    InputTextView,
    ButtonView,
    submitHandler,
    Template
  } from 'ckeditor5/src/ui';
  import { icons } from 'ckeditor5/src/core';
  
  // cSpell:ignore UcsfCalloutboxview
  export default class UcsfCalloutboxView extends View {
    constructor( locale ) {
      super( locale );
  
      this.selectLabel = new Template({
        tag: 'label',
        attributes: {
            for: 'select-dropdown'
        },
        children: ['Align Option:']
    });

    this.select = new Template({
        tag: 'select',
        attributes: {
            id: 'align-dropdown',
            class: 'ck-balloon-panel__select'
        },
        children: [
            new Template({
                tag: 'option',
                attributes: {
                    value: 'left'
                },
                children: ['Align Left']
            }),
            new Template({
                tag: 'option',
                attributes: {
                    value: 'right'
                },
                children: ['Align Right']
            })
        ]
    });

    this.radioLabel = new Template({
        tag: 'label',
        attributes: {
            for: 'corner-on'
        },
        children: ['Corner Image: On']
    });

    this.radioInputOn = new Template({
        tag: 'input',
        attributes: {
            type: 'radio',
            name: 'corner',
            value: 'on',
            id: 'corner-on'
        }
    });

    this.radioLabelOff = new Template({
        tag: 'label',
        attributes: {
            for: 'corner-off'
        },
        children: ['Corner Image: Off']
    });

    this.radioInputOff = new Template({
        tag: 'input',
        attributes: {
            type: 'radio',
            name: 'corner',
            value: 'off',
            id: 'corner-off'
        }
    });
  
      this.saveButtonView = this._createButton( 'Save', icons.check, 'ck-button-save' );
      // Submit type of the button will trigger the submit event on entire form when clicked 
          // (see submitHandler() in render() below).
      this.saveButtonView.type = 'submit';
  
      this.cancelButtonView = this._createButton( 'Cancel', icons.cancel, 'ck-button-cancel' );
  
      // Delegate ButtonView#execute to FormView#cancel
      this.cancelButtonView.delegate( 'execute' ).to( this, 'cancel' );

      this.setTemplate( {
        tag: 'form',
        attributes: {
          class: [ 'ck', 'ck-abbr-form' ],
          tabindex: '-1'
        },
        children:  [
          this.selectLabel,
        this.select,
        this.radioLabel,
        this.radioInputOn,
        this.radioLabelOff,
        this.radioInputOff,
        this.saveButtonView,
        this.cancelButtonView
      ],
      } );
    }
  
    render() {
      super.render();
  
      // Submit the form when the user clicked the save button or pressed enter in the input.
      submitHandler( {
        view: this
      } );
    }

    _createInput( label ) {
      const labeledInput = new LabeledFieldView( this.locale, createLabeledInputText );
  
      labeledInput.label = label;
  
      return labeledInput;
    }
  
    _createButton( label, icon, className ) {
      const button = new ButtonView();
  
      button.set( {
        label,
        icon,
        tooltip: true,
        class: className
      } );
  
      return button;
    }
  }
  // export default class UcsfCalloutboxView extends View {
  //   constructor(locale) {
  //     super(locale);
  
  //     this.alignView = this._createLabeledInput(t('Align'), 'left' )
  //     this.cornerImageView = this._createLabeledInput(t('Corner Image'), '0', 'radio')
  
  //     this.saveButtonView = this._createButton('Save', icons.check, 'ck-button-save');
  //     this.saveButtonView.type = 'submit';
  //     this.cancelButtonView = this._createButton( 'Cancel', icons.cancel, 'ck-button-cancel' );
  //     this.cancelButtonView.delegate( 'execute' ).to( this, 'cancel' );
  
  //     this.childViews = this.createCollection( [
  //       this.alignView,
  //       this.cornerImageView,
  //       this.saveButtonView,
  //       this.cancelButtonView
  //     ] );
  
  //     this.setTemplate( {
  //       tag: 'form',
  //       attributes: {
  //         class: [ 'ck', 'ck-div-form' ],
  //         tabindex: '-1'
  //       },
  //       children: this.childViews
  //     });
  //   }
  
  //   render() {
  //     super.render();
  
  //     // Submit the form when the user clicked the save button or pressed enter in the input.
  //     submitHandler( {
  //       view: this
  //     } );
  //   }
  
  //   focus() {
  //     // If the text field is enabled, focus it.
  //     if ( this.textInputView.isEnabled ) {
  //       this.textInputView.focus();
  //     }
  //     else {
  //       this.titleInputView.focus();
  //     }
  //   }
  //   _createLabeledInput(labelText, defaultValue, type = 'text') {
  //     const labeledInput = new LabeledFieldView(locale, InputTextView);

  //     labeledInput.set({
  //         label: labelText,
  //         fieldView: {
  //             type: type,
  //             value: defaultValue
  //         }
  //     });

  //     return labeledInput;
  // }
  //   _createInput(label) {
  //     const labeledInput = new LabeledFieldView( this.locale, createLabeledInputText );
  //     labeledInput.label = label;
  //     return labeledInput;
  //   }
  
  //   _createButton( label, icon, className ) {
  //     const button = new ButtonView();
  
  //     button.set( {
  //       label,
  //       icon,
  //       tooltip: true,
  //       class: className
  //     } );
  
  //     return button;
  //   }
  // }
  