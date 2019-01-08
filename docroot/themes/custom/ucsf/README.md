Built with scaffolding from [CThreem Core](https://www.npmjs.com/package/cthreem-core).

Dependencies:
Node v10.1.0 (node -v)
NPM v5.6.0 (npm -v)

To install: 
Run `npm install`

To compile:
Navigate to theme root.
Run `gulp`

Else see [documenation](https://www.npmjs.com/package/cthreem-core)

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