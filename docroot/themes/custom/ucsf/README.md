# UCSF Theme
General Drupal theming documentation can be found here: [https://www.drupal.org/docs/theming-drupal](https://www.drupal.org/docs/theming-drupal)

# Prerequisites
You'll need [node.js](http://nodejs.org).

## Install and start watching for changes
Changing into the `docroot/themes/custom/ucsf` directory, run this to install
dependencies:
```
$ npm ci
```
You may have to run that again for updates. **If you have any problems; this is
the first thing to run.**

### Compile for development
Inside `docroot/themes/custom/ucsf` run following command to do an initial build of the site and start watching for changes while doing
local development:
```
$ npm start
```

# Assets (CSS & JS)
To add either CSS or JS from third parties, use one of these methods:

### NPM Dependencies
NPM is a Node package manager for the web. It is useful for adding third party
libraries for both development and site inclusion.
Install any [NPM](https://www.npmjs.com/) component with the `--save` flag. You
can search for anything that NPM can install and run:
    $ npm install {thing} --save
Use `--save` when a package needs to be added as a dependency to the browser.
Use `--save-dev` when a package is only needed for development like Sass
libraries.

# SASS Best Practices
All SASS imports are now done using [@use](https://sass-lang.com/documentation/at-rules/use)
instead of the older `@import` syntax. This allows all variables and mixins to
be scoped per file.

### Include scss partials
 in this file `docroot/themes/custom/ucsf/scss/style.scss`

compiled css will end up inside
`docroot/themes/custom/ucsf/dist/style.css`

### SCSS Folder structure
`O_tools/_index.scss` is a bundle of all of the variables and mixins that can be
imported into a partial at the same time using `@use "../0_tools" as *;`.

Folders are numbered to help with visualizing the CSS cascade order.

# Folder Structure
```
|-- config/
|-- dist/
|-- images/
|-- includes/
|-- js/
|-- node_modules/
|-- sass/
|-- templates/
|-- .gitignore
|-- package-lock.json
|-- package.json
|-- echo.info
|-- echo.libraries.yml
|-- echo.theme
```

## 1. config/
This folder contains configuration that is installed only on the initial
install.

**install/THEMENAME.settings.yml**: This file contains all
default settings.
[https://www.drupal.org/docs/8/theming-drupal-8/creating-advanced-theme-settings](https://www.drupal.org/docs/8/theming-drupal-8/creating-advanced-theme-settings)

**schema/THEMENAME.schema.yml**: This file is used by Drupal to
provide translations for items like the THEMENAME.settings.yml file.
[https://www.drupal.org/docs/drupal-apis/configuration-api/configuration-schemametadata](https://www.drupal.org/docs/drupal-apis/configuration-api/configuration-schemametadata)

## 2. dist/
Sass is compiled into minified CSS files. Files
in this directory are auto-generated so should never be manually edited. This is
what Drupal looks to for production ready assets.

## 3. images/
Image files like jpeg, gif, png, or svg should be added to this directory.

## 4. includes/
PHP files that contain Drupal hooks, form alters, etc.

## 5. js/
Javascript files belong in this directory. A default main.js file is already
available for use. Drupal Behavior called
`customBehavior` has been added as an example. Behaviors are helpful for
allowing your javascript to work nicely with Drupal and Drupal's Ajax system.
More information on using Javascript in Drupal can be found here: [https://www.drupal.org/docs/drupal-apis/javascript-api/javascript-api-overview](https://www.drupal.org/docs/drupal-apis/javascript-api/javascript-api-overview)


You can create additional JS files and add them inside `docroot/themes/custom/echo/echo.libraries.yml`

## 6. node_modules/
This directory does not exist by default, but it will be created automatically
once `npm ci` is run. This directory should never be edited, and it should
not be committed to git.

## 7. sass/
All (S)CSS files should go in this directory.
[Read the Sass Docs](./scss/README.md)

**style.scss**: This is the master file for all scss. All partials should be
imported here using [@use](https://sass-lang.com/documentation/at-rules/use).
Styles should ideally go into individual component files rather than directly
into this file. This will compile to `/dist/style.css`.
Variables and mixins are imported in the `O_tools/_index.scss`
file.

**ckeditor.scss**: When using the CKEditor WYSIWYG, it is helpful to see the styles
which will be applied in the actual theme. This file allows styles to be
injected into the editor so that a user gets a better idea of how text and
components like buttons, lists, and links will really look. This
will compile to `/dist/ckeditor.css`.

**print.scss**: Styling how things will print out.

## 8. templates/
The `templates` directory allows [Twig](http://twig.sensiolabs.org/) files to be
added so that HTML markup can be overridden or customized. These files end with
`.html.twig`. For example: `block.html.twig` or `node--teaser.html.twig`.
Drupal auto-detects twig files based on [naming conventions](https://www.drupal.org/docs/8/theming/twig/twig-template-naming-conventions).
So if you name a Twig file correctly, Drupal will automatically use it.
In addition, you can suggest templates for use when certain conditions are met.
This link provides documentation for working with Drupal 9 templates:
[https://www.drupal.org/docs/theming-drupal/twig-in-drupal/working-with-twig-templates](https://www.drupal.org/docs/theming-drupal/twig-in-drupal/working-with-twig-templates)

## Files
**.gitignore**: This tells Git what files and directories should not be
committed to a Git repository. You may add in extra items to ignore.

**package-lock.json**: (auto-generated by `npm install`) This is a Lock file so that everyone on your team will
be sure to install the exact same Node packages.

**package.json**: This is the main file used by Node to declare any NPM packages
that will be used and custom scripts. Likely you will not need to edit this
file unless there are specific Node packages you need.

**echo.info.yml**: This is the main file which declares a theme to Drupal. It
contains information about the theme, adds libraries,
and declares regions where content can be placed.

**echo.libraries.yml**: Libraries for CSS and Javascript can be defined here. These
only inform drupal that the libraries exist, not that they should be used.
More libraries or dependencies can be declared if needed.
[https://www.drupal.org/docs/theming-drupal/adding-stylesheets-css-and-javascript-js-to-a-drupal-theme](https://www.drupal.org/docs/theming-drupal/adding-stylesheets-css-and-javascript-js-to-a-drupal-theme)

**echo.theme**: This file is for more advanced users. It allows a developer to
alter Drupal's output before it gets to a Twig template. It uses PHP and Drupal
Hooks to change variables and data that is eventually passed to a template. This
is also where any theme suggestions would be located.
[https://www.drupal.org/docs/8/theming-drupal-8/modifying-attributes-in-a-theme-file](https://www.drupal.org/docs/8/theming-drupal-8/modifying-attributes-in-a-theme-file)





# Compile Sass/Scss files to CSS
This uses https://nodejs.org to compile files.

# Dependencies:
make sure you are in the same directory as nvmrc fle
docroot/themes/custom/ucsf
```
$ nvm use
```

## Installation
Install all npm packages, navigate to docroot/themes/custom/ucsf
```
$ npm install
```

### Compiles for development
Compile and watch for changes to sass files. docroot/themes/custom/ucsf
```
$ npm start
```

Where to modify:
- Sass: `[theme]/scss`
- JS: `[theme]/js`
- PHP: `[theme]/php-includes`
- Twig: `[theme]/templates`
- Single Images: `[theme]/images`
- Images for Sprite: `[theme]/sprite`

All assets (non-twig, non-php) are compiled to the `[theme]/scss` folder.

Browser sync:
You may want to change the BrowserSync domain in [theme]/gulp-config.js
