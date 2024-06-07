class DomCta extends HTMLElement {
    constructor() {
      super();
      const shadow = this.attachShadow({ mode: 'open' });
      shadow.innerHTML += `
      <style>
        :host {
            margin: 2% 0;
            display: inline-block;
            --font-white: #FFF;
            --font-black: #000;
            --bg-interactive-blue: #0071ad;
            --bg-interactive-dark-blue: #052049;
            --bg-interactive-light-navy: #506380;
            --bg-interactive-green: #6EA400;
            --bg-interactive-dark-grey: #737373;
            --bg-interactive-orange: #F26D04;
            --bg-interactive-teal: #058488;
            --bg-interactive-purple: #716FB2;
            --bg-interactive-red: #EB093C;
            --bg-interactive-yellow: #FFDD00;
            --bg-transparent: transparent;
            background-color: var(--cta-color);
            /* width: 100%; */
        }
        ::slotted(a::after){
            background-color: var(--cta-color);
        }
        </style>
        <!-- <slot name="title" class="title"></slot>
        <slot name="image" class="image"></slot>
        <slot name="description" class="description"></slot>
        <slot name="cta" class="cta"></slot> -->
        <slot></slot>
      `;
    }
  
    connectedCallback() {
        this.dataset.ctaColor = this.dataset.ctaColor || 'transparent';
        this._initializeStyles();
    }
  
    _initializeStyles() {  
      const ctaFontColor = (this.dataset.ctaColor === 'transparent' || this.dataset.ctaColor === 'interactive-yellow') ? 'black' : 'white';
  
  
      this.style.setProperty('--cta-font-color', `var(--font-${ctaFontColor})`);
      this.style.setProperty('--cta-color', `var(--bg-${this.dataset.ctaColor})`);
    }
  
    static get observedAttributes() {
      return ['data-cta-color'];
    }
  
    attributeChangedCallback(name, oldValue, newValue) {
      if (oldValue !== newValue) {
        this._initializeStyles();
      }
    }
  }
  
  customElements.define('dom-cta', DomCta);