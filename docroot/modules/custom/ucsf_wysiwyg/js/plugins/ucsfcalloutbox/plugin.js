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
            allowedContent: 'aside(*);' +
            'div(*); time;',

            requiredContent: 'aside(!ucsfcallout);',

            editables: {
                content: {
                    selector: '.callout__content',
                    allowedContent: 'p[*]; br strong[*]; em a[*]; time[*]; cite i strike sub sup ul[*]; ol[*]; li dl dd dt address[*]; abbr;'
                },
                title: {
                    selector: '.callout__title',
                    allowedContent: 'h1 h2 h3 h4 h5 h6 p[*]; br strong em;'
                },
                image: {
                    selector: '.callout__image',
                    allowedContent: 'strong em; article[*], img[*]; picture[*]; p[*]; a[*]; source[*]; video[*]; drupal-entity[*];'
                },
                cta: {
                    selector: '.callout__cta',
                    allowedContent: 'picture br em img[*]; p[*]; strong[*]; a[*]; source[*];'
                }
            },

            template: '<aside class="ucsfcallout callout-left">' +
                '<div class="callout__image">&nbsp;</div>' +
                '<div class="callout__title"><h3>Related Stories</h3></div>' +
                '<div class="callout__content">' +
                    '<time>Oct. 24, 2018</time>' +
                    '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum ultricies sit amet.</p>' +
                '</div>' +
                '<div class="callout__cta">Call to action</div>' +
            '</aside>',

            button: 'Create a Callout Box',
            dialog: 'ucsfcalloutbox',
            upcast: function (element) {
                return element.name == 'aside' && element.hasClass('ucsfcallout');
            },

            init: function () {
                if (this.element.hasClass('callout-left'))
                    this.setData('align', 'left');
                if (this.element.hasClass('callout-right'))
                    this.setData('align', 'right');
                if (this.element.hasClass('callout-center'))
                    this.setData('align', 'center');
            },
            data: function () {
                // Brutally remove all align classes and set a new one if "align" widget data is set.
                this.element.removeClass('callout-center');
                this.element.removeClass('callout-left');
                this.element.removeClass('callout-right');
                if (this.data.align)
                    this.element.addClass('callout-' + this.data.align);
                /*
                 if ( this.data.bgColor ) {
                 this.element.addClass( 'bg--' + this.data.bgColor );
                 }
                 */
            }
        });
    }
});

})(jQuery, Drupal, drupalSettings, CKEDITOR);