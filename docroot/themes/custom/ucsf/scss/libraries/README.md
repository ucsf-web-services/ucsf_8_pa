Css files that should not compile into `style.css` because they are a part of library
and are only used if specific conditions are met, or on specific pages.
<hr>

`ucsf_locations_map.scss`
We moved css for a map from `styles.css` file to the modules library because it is only used on map page https://www.ucsf.edu/maps/main-locations. When you style the map:
* Write your scss in *docroot/themes/custom/ucsf/scss/libraries/ucsf_locations_map.scss*
it will compile into docroot/themes/custom/ucsf/assets/css/ucsf_locations_map.css
which is then used by the custom module via .libraries
*docroot/modules/custom/ucsf_locations_map/ucsf_locations_map.libraries.yml*
