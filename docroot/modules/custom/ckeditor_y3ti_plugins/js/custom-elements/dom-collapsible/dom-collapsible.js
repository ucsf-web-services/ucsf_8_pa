class DomCollapsible extends HTMLElement {
    constructor() {
      super();
      const shadow = this.attachShadow({ mode: 'open' });
      shadow.innerHTML += `
      <style>
        :host {
            display: block;
            border-bottom: 1px solid #ccc;
        }

        .header {
            font-size: 17px;
            display: flex;
            align-items: center;
        padding: 15px 15px;
        outline: none;
            cursor: pointer;
        }
        .icon:before {
            color: #6ea400;
            font-size: 14px;
            font-family: "Font Awesome 5 Free";
            content: "\\f068";
            font-weight: 900;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            display: block;
        }
        .icon.expand-less:before {
            content: "\\f068";
        }
        .icon.expand-more:before {
            content: "\\f067";
        }

        ::slotted([slot=header-text]), .header-text,::slotted(h5) {
            flex: 1 1 auto;
            margin: 0 !important;
            color: #007CBE !important;
        font-weight: 500 !important;
        }
        .content {
            padding: 0 15px;
            font-size: 16px;
            /* white-space: pre-line; */
        }
        .expand-less.content-text,
        .expand-less.content {
            display: block;
            visibility: visible;
            opacity: 1;
            height: auto;
            transition: all 0.35s ease;
            transition: visibility 0.35s ease, display 0.35s ease, max-height 0.35s ease, opacity 0.35s ease;
            margin-bottom: 0;
            float: none;
        width: 100%;
        padding: 0 15px;

        }
        .expand-more.content-text,
        .expand-more.content {
            display: none;
            visibility: hidden;
            max-height: 0;
            height: 0;
            opacity: 0;
            transition: visibility 0.35s cubic-bezier(0, 0, 0.2, 1), display 0.35s cubic-bezier(0, 0, 0.2, 1), max-height 0.35s ease-in 0.5s, opacity 0.35s cubic-bezier(0, 0, 0.2, 1);
            margin-bottom: 0;
        }
        </style>
        <div class="header" aria-expanded="${this.getAttribute('opened') === 'true'}" role="button">
			<slot name="header-text" class="header-text"></slot>
			<div class="icon"></div>
		</div>
		<div class="content">
    	    <slot name="content-text" class="content-text" aria-hidden="${this.getAttribute('opened') !== 'true'}" role="region"></slot>
		</div>    
      `;
        // Get elements from shadow DOM
        this._header = this.shadowRoot.querySelector('.header');
        this._icon = this.shadowRoot.querySelector('.icon');
        this._content = this.shadowRoot.querySelector('.content');

        // Add event listeners
        this._header.addEventListener('click', this._toggleOpened.bind(this));
        this._header.addEventListener('keydown', this._handleKeyDown.bind(this));
    }
    static get observedAttributes() {
        return ['opened'];
    }
    attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'opened' && oldValue !== newValue) {
          this._header.setAttribute('aria-expanded', newValue === 'true');
          this._content.setAttribute('aria-hidden', newValue !== 'true');
          this._updateIcon();
        }
    }
    connectedCallback() {
        setTimeout(() => {
            this._updateId();
          }, 0);
        this._updateIcon()
    }
    // Toggle the opened state
    _toggleOpened() {
        this.setAttribute('opened', this.getAttribute('opened') !== 'true');
    }
    // Update the icon based on the opened state
    _updateIcon() {
        this._icon.classList.toggle('expand-less', this.getAttribute('opened') === 'true');
        this._icon.classList.toggle('expand-more', this.getAttribute('opened') !== 'true');
        this._content.classList.toggle('expand-less', this.getAttribute('opened') === 'true');
        this._content.classList.toggle('expand-more', this.getAttribute('opened') !== 'true');
    }
    // Update the ID based on the header text and unique attribute
    _updateId() {
        const headerText = this.querySelector('.header-text').innerText;
        const uniqueText = this.querySelector('.header-text').getAttribute("unique") || '';
        const cleanHeader = headerText.replace(/(<([^>]+)>)/ig, "").replace(/&nbsp;/gi, " ").replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, ' ').trim().replace(/\s/g, "-");
        this.id = cleanHeader + '-' + uniqueText;
    }

    // Handle keydown event for accessibility
    _handleKeyDown(event) {
        const keys = {
        enter: 13,
        space: 32
        };
        if (event.keyCode === keys.enter || event.keyCode === keys.space) {
            this._toggleOpened();
        }
    }
  }
  
  customElements.define('dom-collapsible', DomCollapsible);