CKEditor iFrame Dialog

Installation
============

This module requires the core CKEditor module and the CKEditor FakeObjects module.
See the CKEditor FakeObject module page (https://www.drupal.org/project/fakeobjects)
for more info on installation and required plugin.

    1. Download the plugin from http://ckeditor.com/addon/iframe - the latest 4.5.x version.
    2. Place the plugin in the root libraries folder (/libraries).
    3. Enable the iFrame module in the Drupal admin under "CKEditor" add ons.
    4. If you are not using the Full HTML text format, you have the following two
       options for this module to work.
        a. You can uncheck "Limit allowed HTML tags and correct faulty HTML" for this to work.
        b. After adding the iFrame button to CKEditor, you can update the <iframe>
           tag added to the "Limit allowed HTML tags and correct faulty HTML" section with:
           <iframe longdesc name scrolling src title align height frameborder width>