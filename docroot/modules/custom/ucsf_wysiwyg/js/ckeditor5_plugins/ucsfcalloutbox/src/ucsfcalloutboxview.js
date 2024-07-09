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
    constructor( editor ) {
      super( editor.locale );
      const model = editor.model;
      const selection = model.document.selection;
      const selectedElement = selection.getSelectedElement();
      let align = 'left'
      let image = '0'
      if (selectedElement && selectedElement.name == "ucsfcalloutbox") {
        align = selectedElement.getAttribute('data-align')
        image = selectedElement.getAttribute('data-image')
      }
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
                    value: 'left',
                    selected: (align && align != 'right') ? true : false
                },
                children: ['Align Left']
            }),
            new Template({
                tag: 'option',
                attributes: {
                    value: 'right',
                    selected: (align && align == 'right') ? true : false
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
            value: '1',
            id: 'corner-on',
            checked: (image && image == '1') ? true : false
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
            value: '0',
            id: 'corner-off',
            checked: (image && image != '1') ? true : false
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
