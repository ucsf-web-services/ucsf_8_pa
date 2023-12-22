# SCSS local development
[Read the Main Docs](../README.md)

## Compile for development
Inside `docroot/themes/custom/ucsf` run following command to do an initial build of the site and start watching for changes while doing
local development:
```
$ npm start
```

## Folder structure ##
Folders are numbered to help you visualize css cascade. Do not structure future themes this way, keep directory names non-Drupal specific with
most partials being inside `components` directory
```
|-- 0_tools/
|-- 1_global/
|-- 2_components/
|-- 3_navigation/
|-- 4_paragraph/
|-- 5_node/
|-- 6_taxonomy/
|-- 7_views/
|-- 8_region/
|-- 9_utilities/
|-- libraries/
|-- ckeditor.scss
|-- styles.scss
```
**0_tools/**: Variables and mixins are here. None of this code is actually printed to the final styles.css

**1_global/**: Most generic styles for html elements and basic classes.

**2_components/**: These should be self-contained and not spill out to others.

**3_navigation/**: These partials have styles for navigation elements.

**4_paragraph/**: These partials have styles for Drupal paragraphs.

**5_node/**: These partials have styles for Drupal node.

**6_taxonomy**: These partials have styles for Drupal taxonomy pages/elements.

**7_views**: These partials have styles for Drupal view pages/elements.

**8_region/**: These partials have styles for Drupal regions.

**9_utility/**: These should be used to force style regardless of CSS cascade, prefixed with a "u-".

**libraries/**: These should be self contained scss files, they compile into self contained css files.
Instead of being included through style.scss, these files are loaded on a specific page and are included through ucsf.libraries.yml

**style.scss**: This is the master file for all scss. All partials should be imported here using @use. Styles should ideally go into individual component files rather than directly into this file. This will compile to `/dist/style.css`. Variables and mixins are imported in the `O_tools/_index.scss` file.

**ckeditor.scss**: When using the CKEditor WYSIWYG, it is helpful to see the styles which will be applied in the actual theme. This file allows styles to be injected into the editor so that a user gets a better idea of how text and components like buttons, lists, and links will really look. This will compile to `/dist/ckeditor.css`.
