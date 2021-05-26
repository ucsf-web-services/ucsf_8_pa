# UCSF Magazine Theme

# Compile Sass/Scss files to CSS
This uses https://nodejs.org to compile files.

## Installation
Install all npm packages.
```
$ npm install
```

### Compiles for development
Compile and watch for changes to sass files. docroot/themes/custom/ucsf_mag
```
$ npm start
```

### Temporary Workflow 5/11/2021
1. Set Magazine theme as your default.
2. Create issue branches off of  `magazine` branch
3. merge `MAG-133-review` into your issue branch (this has the newest styling based on feedback for the MAG-133)

4. Enable theme specific blocks
go to /admin/structure/block

##### Header Region:
###### Blocks to enable.
    - Magazine Main Menu;
###### Blocks to disable.
    - Main navigation;
    - Main navigation - Desktop;

##### Footer Region:

###### Blocks to enable
    - UCSF Magazine;
    - News &amp; Media;

###### Blocks to disable.
    - Get Involved;
    - Resources;

5 Change "Alumni Stories" link inside the Magazine menu to
https://alumni.ucsf.edu/stories