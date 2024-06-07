class DomQuote extends HTMLElement {
    constructor() {
      super();
      const shadow = this.attachShadow({ mode: 'open' });
      shadow.innerHTML += `
      <style>
        :host {
            position: relative;
            background-color: whitesmoke;
            word-wrap: break-word;
            box-shadow: 10px 10px #007CBE;
            padding: 30px;
            margin-bottom: 2em;
            margin: 0 0 27px;
            font-size: 21.25px;
            border-left: 5px solid whitesmoke;
            display: block;
        }
        ::slotted([slot=blockquote-content]) {
            font-size: 22px;
            line-height: 1.4;
            margin-bottom: 1em;
            position: relative;
            font-family: "Granjon W01", Garamond, serif;
        }
        ::slotted([slot=blockquote-title]) {
            word-wrap: break-word;
            font-size: 15px !important;
            font-weight: 700;
            line-height: 1.5;
        }
        ::slotted([slot=blockquote-source]) {
            word-wrap: break-word;
            font-size: 15px !important;
            line-height: 1.5;
        }

        </style>
        <slot name="blockquote-content" class="blockquote-content"></slot>
        <slot name="blockquote-title" class="blockquote-title"></slot>
        <slot name="blockquote-source" class="blockquote-source"></slot>    
      `;
    }
  }
  
  customElements.define('dom-quote', DomQuote);