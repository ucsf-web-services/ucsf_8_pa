(function($, Drupal, drupalSettings, CKEDITOR) {

    "use strict";

CKEDITOR.plugins.add('ucsfcalloutbox', {
    requires: 'widget',
    icons: 'ucsfcalloutbox',
    beforeInit: function (editor) {
        // Configure CKEditor DTD for custom drupal-entity element.
        // @see https://www.drupal.org/node/2448449#comment-9717735
        var dtd = CKEDITOR.dtd, tagName;
        dtd['drupal-entity'] = {'#': 1};
        // Register drupal-entity element as allowed child, in each tag that can
        // contain a div element.
        for (tagName in dtd) {
            if (dtd[tagName].div) {
                dtd[tagName]['drupal-entity'] = 1;
            }
        }
        for (tagName in dtd) {
            if (dtd[tagName].aside) {
                dtd[tagName]['drupal-entity'] = 1;
            }
        }
    },

    init: function (editor) {
        CKEDITOR.dialog.add('ucsfcalloutbox', this.path + 'dialogs/ucsfcalloutbox.js');
        //var config = editor.config.y3titemplatemenuButtons.split(',');
        //var button =  config['pullquote'] == undefined ? 'Create a Pullquote' : undefined;
        var pluginDirectory = this.path;

        editor.addContentsCss(pluginDirectory + 'css/ucsfcalloutbox.css');

        editor.ui.addButton('ucsfcalloutbox', {
            label: 'Insert Callout Box',
            command: 'ucsfcalloutbox'
        });

        // Register context menu option for editing widget.
        if (editor.contextMenu) {

            editor.addMenuGroup('ucsfGroup');
            editor.addMenuItem('calloutbox', {
                label: 'Edit Callout',
                icon: this.path + 'icons/ucsfcalloutbox.png',
                command: 'ucsfcalloutbox',
                group: 'ucsfGroup'
            });

            editor.contextMenu.addListener(function (element) {
                if (element.getAscendant('calloutbox', true)) {
                    return {calloutboxItem: CKEDITOR.TRISTATE_OFF};
                }
            });
        }

        editor.widgets.add('ucsfcalloutbox', {
            //allowedContent: 'aside div(*); time;',
            requiredContent: 'aside(!ucsfcallout);',

            // Editables need to be in the order they are placed in DOM.
            editables: {
                image: {
                    selector: '.callout__image',
                    allowedContent: 'img[*](*); picture[*](*); source[*](*); video[*](*); drupal-entity[*](*); figure[*](*); article[*](*); span[*](*); div[*](*);'
                },
                content: {
                    selector: '.callout__content'
                }
            },

            template:
            '<aside class="ucsfcallout callout-left" data-image="0">' +
                '<div class="callout__image">Remove this text and use the embed button to add an image.</div>' +
                '<div class="callout__content">'    +
                    '<h3 class="eyebrow-title">Take Action</h3>'     +
                    '<time>Oct. 24, 2020</time>'    +
                    '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ultricies sit amet.</p>' +
                    '<p><a class="link link--cta" href="/">Learn More</a></p>' +
                '</div>' +
            '</aside>',
            button: 'Create a Callout Box',
            dialog: 'ucsfcalloutbox',

            upcast: function (element) {
                return element.name == 'aside' && element.hasClass('ucsfcallout');
            },

            init: function () {
                //called only when page loads
                if (this.element.hasClass('callout-left'))
                    this.setData('align', 'left');
                if (this.element.hasClass('callout-right'))
                    this.setData('align', 'right');
                if (this.element.hasClass('callout-center'))
                    this.setData('align', 'center');

                // Set data to image option
                var calloutImage = this.element.getAttribute('data-image');
                var calloutImageData = (calloutImage) ? calloutImage : 0;
                this.setData('callout', calloutImageData);
            },
            data: function () {
                //called everytime dialog is opened and closed.
                // Brutally remove all align classes and set a new one if "align" widget data is set.
                this.element.removeClass('callout-center');
                this.element.removeClass('callout-left');
                this.element.removeClass('callout-right');
                if (this.data.align) {
                    this.element.addClass('callout-' + this.data.align);
                }

                // The value is a 1 but given as a string. We will check against
                // any 1 value (string or integer).
                if (this.data.callout && this.data.callout == 1) {
                    //set the image tag to show or hide
                    this.element.setAttribute('data-image', this.data.callout);
                    var imageCallout = this.element.find('.callout__image');
                    if (typeof imageCallout.$[0] !== 'undefined') {
                        imageCallout.$[0].classList.remove('hidden')
                    }
                } else {
                    this.element.setAttribute('data-image', 0);
                    var imageCallout = this.element.find('.callout__image');
                    if (typeof imageCallout.$[0] !== 'undefined') {
                        imageCallout.$[0].classList.add('hidden')
                    }
                }
            }
        });
    }
});

})(jQuery, Drupal, drupalSettings, CKEDITOR);
