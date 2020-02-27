/**
 * @file
 * Scripts for UCSF dom
 *
 */
(function ($, Drupal, drupalSettings, window) {

  Drupal.behaviors.map = {
    attach: function (context, settings) {


      // Only run this code once.
      $(window, context).once('map').each( function() {
        // Google map set up and center for the map
        var map = new google.maps.Map(document.getElementById('map'), {
          center: { lat: 37.195, lng: -123.775 },
          scrollwheel: false,
          zoom: 4
        });
        // Geographical area to be included in the map
        var bounds = new google.maps.LatLngBounds();

        //getting the node title for the url from which jsonapi pulls the data
        var pagedata = $("div.title").text();
        var url = '/jsonapi/node/map?include=field_map_locations&include=field_map_locations.field_location_images,field_map_locations.field_location_type&filter%5Btitle%5D=' + pagedata

        $.getJSON(url, function(data) {
          // Parsing JSON API object

          var parcedData = window.jsonapi.parse(data);
          // Variables for holding location information.
          var locations = {};
          var infoWindows = [];
          var markers = [];

          // Array of map location objects
          var rawLocations = purgeLocations(parcedData.data[0].field_map_locations);

          // Looping through each location object in the array,
          // creating HTML markup with data provided in the array.
          $.each(rawLocations, function(index, location) {
            //Location type: Building or Parking.
            var locationType = location.field_location_type.name

            //  Initial state, before type is selected.
            if (!locations[locationType]) {
              locations[locationType] = { indexes: [], list: [] }
            }
            // Generate a list item for location type,
            // inside the list item display building's type (example: campus, main building...).
            locations[locationType].list.push("<li><a id='loc-" + index + "'>" + location.field_title + "</a></li>");
            locations[locationType].indexes.push(index);
            var icon = '';

            // Change icon for Parking location type.
            if (locationType === 'Parking') {
              icon = {
                url: 'https://www.ucsf.edu/sites/default/files/parking-icon-01.png',
                scaledSize: new google.maps.Size(28, 32)
              };
            }
            // Generate a marker for the location using coordinates, on hover title (building's type), icon.
            markers[index] = new google.maps.Marker({
              map: map,
              position: { lat: parseFloat(location.field_geofield.lat), lng: parseFloat(location.field_geofield.lon) },
              title: location.field_title,
              icon: icon
            });

            // Extend the map so that marker location is visible
            bounds.extend(markers[index].position);

            // Address.
            var address = '';
            if (location.field_address) {
              address = location.field_address.address_line1 + "<br>" + location.field_address.locality + " " + location.field_address.administrative_area;
            }

            // Website Link.
            var link = '';
            if (location.field_location_link) {
              link = (location.field_location_link.uri ? "<a class='infobox__website' href='" + location.field_location_link.uri + "'>Website</a>" : "")
            }

            // Array of image objects for current location
            var images = location.field_location_images;

            // Opening tag for group of images wrapper.
            var imagesMarkup = "";
            // loop through all of the images and generate HTML markup for each.
            if (location.field_location_images.length) {
                imagesMarkup += '<div class="infobox__image infobox__item"><img src="' + images[0].uri.url + '" alt="' + images[0].meta.alt + '" /></div>';
            }

            // Pop up window above location marker
            // Displays: Building's Title, Description, Address, Link to driving directions, Website, Location Images,
            infoWindows[index] = new google.maps.InfoWindow({
              content: "<div class='infobox'>" +
                imagesMarkup +
                "<h3 class='infobox__title infobox__item'>" + location.field_title + "</h3>" +
                "<div class='infobox__address infobox__item'>" + address + "</div>" +
                "<div class='infobox__link infobox__item'>" +
                  "<a class='infobox__directions' href='https://www.google.com/maps/dir/Current+Location/" + location.field_geofield.lat + "," + location.field_geofield.lon + "'>Directions</a>" +
                  link +
                "</div>" +
              "</div>",
              // maximup width of the pop up.
              maxWidth: 266
            })
          });
          // Sets the viewport to contain the given bounds.
          // so that pop up for the selected marker fits on screen?
          map.fitBounds(bounds);

          var markup = '';
          // Checkbox to show / hide the list of locations by type.
          $.each(locations, function(type, item) {
            markup += '<div><input id="' + type + '" type="checkbox" checked="checked" /><label class="locations-label" for="' + type + '"><h3>' + type + '</h3></label><ul class="cta--list">' + item.list.join('') + '</ul></div>'
            // Event handler to capture the click hide / show list items for the checked checkbox category.
            $('body').on('click', "label[for='" + type + "']", function (){
              var input = $('input#' + type);

              if (input.is(':checked')) {
                input.nextAll('ul').hide('fast');
                $.each(locations[type].indexes, function(index, value) {
                  infoWindows[value].close();
                  markers[value].setVisible(false)
                })
              } else {
                input.nextAll('ul').show('fast');
                $.each(locations[type].indexes, function(index, value) {
                  infoWindows[value].close();
                  markers[value].setVisible(true)
                })
              }
            })
          });
          // Replace initial loading message with generated markup.
          $('#locations-list .loading').html(markup);

          // Close previous location marker pop up when new location is selected.
          $.each(rawLocations, function(index, location) {
            google.maps.event.addDomListener(document.getElementById('loc-' + index), 'click', (function () {
              $.each(infoWindows, function(index, infoWindow) {
                infoWindow.close()
              });
              infoWindows[index].open(map, markers[index])
              scrollToTop();
              showHeader ();
            }));

            google.maps.event.addListener(markers[index], 'click', (function () {
              $.each(infoWindows, function(index, infoWindow) {
                infoWindow.close()
              });
              infoWindows[index].open(map, markers[index])
            }))
          });

          // adjust the map center on screen resize.
          var center;
          google.maps.event.addDomListener(map, 'idle', function() {
            center = map.getCenter()
          });
          google.maps.event.addDomListener(window, 'resize', function() {
            google.maps.event.trigger(map, 'resize');
            map.setCenter(center);
          })
        })

      });
    }
  }

  /**
   * Remove any items from the array that do not have required fields.
   *
   * @param {array} locations
   */
  function purgeLocations(locations) {
    var remove = [];
    $.each(locations, function(index, location) {
      // add the location to be removed if there are not required fields.
      if (!location.field_title || !location.field_geofield || !location.field_location_type) {
        remove.push(index);
      }
    });

    // Loop throught the raw locations and remove any set to be removed.
    for (var i = remove.length -1; i >= 0; i--) {
      locations.splice(remove[i], 1);
    }

    return locations;
  }

  // Scroll To Top
  function scrollToTop() {
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  // Show header
  function showHeader () {
    header.classList.remove('fixed-nav--hidden').add('fixed-nav--visible');
  }

  // Use minimized header nav whenever this file is used
  var header = document.querySelector('.combined-header-region')
  header.classList.add('fixed-nav', 'fixed-nav--visible');
})(jQuery, Drupal, drupalSettings, window);
