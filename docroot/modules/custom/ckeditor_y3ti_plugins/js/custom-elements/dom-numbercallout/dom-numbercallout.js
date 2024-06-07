class DomNumberCallout extends HTMLElement {
    constructor() {
      super();
      const shadow = this.attachShadow({ mode: 'open' });
      shadow.innerHTML += `
      <style>
        :host {
            font-family: "Neue Helvetica W01", "Helvetica", "Arial", sans-serif;
            text-align: center;
            --font-navy: #052049;
            --font-turquise: #058488;
            --font-green: #6EA400;
            --font-blue: #007CBE;
            --font-orange: #F26D04;
            --font-purple: #716FB2;
            --font-red: #EB093C;
            --font-yellow: #FFDD00;
            --font-light-gray: #D1D3D3;
            --font-gray: #B4B9BF;
            --font-dark-gray: #646e76;
            --font-default: #404040;
        }
        ::slotted([slot=number-highlight]), ::slotted(p) {
            margin: 0 !important;
            font-size: 70px;
            font-weight: 100;
            line-height: 1.1;
            margin-bottom: 10px !important;
            color: var(--number-color);
        }
        ::slotted([slot=number-desc]) {
            text-transform: uppercase;
            font-size: 12px;
            color: #646e76;
            letter-spacing: 0.15em;
        }
        ::slotted([slot=number-desc]) {
            margin-top: 2em;
        }

        </style>
        <slot name="number-highlight" class="number-highlight"></slot>
        <slot name="number-desc" class="number-desc"></slot>
        <slot name="number-link" class="number-link"></slot>    
      `;
    }
  
    connectedCallback() {      
        this._initializeStyles();
    }
  
    _initializeStyles() {  
        const dataNumberColor = this.dataset.numberColor || 'default';
        this.style.setProperty('--number-color', `var(--font-${dataNumberColor})`);
    }
  
    static get observedAttributes() {
      return ['data-number-color'];
    }
  
    attributeChangedCallback(name, oldValue, newValue) {
      if (oldValue !== newValue) {
        this._initializeStyles();
      }
    }
  }
  
  customElements.define('dom-numbercallout', DomNumberCallout);