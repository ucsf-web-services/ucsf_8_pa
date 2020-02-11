/**
 * @file
 * Scripts for UCSF dom
 *
 */
(function ($, Drupal, drupalSettings) {

  Drupal.behaviors.map = {
    attach: function (context, settings) {

      var map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 37.195, lng: -123.775 },
        scrollwheel: false,
        zoom: 4
      });

      var bounds = new google.maps.LatLngBounds();

      //getting the node title to pass to maps_api.php instead of using nid
      var pagedata;
      pagedata = $("div.title").text();

      const url = '/jsonapi/node/map?include=field_map_locations&include=field_map_locations.field_location_images,field_map_locations.field_location_type&filter%5Btitle%5D='+pagedata
      // $.getJSON(url, function(data) {
      //   // Parsing JSON API object
      //   const parcedData = window.jsonapi.parse(data);
      //   // Array of location objects
      //   const locations = parcedData.data[0].field_map_locations;
      //   locations.forEach(location => {
      //     // console.log(location);
      //     const title = location.field_title;
      //     console.log(title);
      //     const address = {
      //       street: location.field_address.address_line1,
      //       locality: location.field_address.locality,
      //       state: location.field_address.administrative_area,
      //       postalCode: location.field_address.postal_code
      //     }
      //     console.log(address);
          // const description = location.field_location_text.processed
          // console.log(description);
      //     const geofield = {
      //       latitude: location.field_geofield.lat,
      //       longitude: location.field_geofield.lon
      //     }
      //     console.log(geofield);

      //     const images = location.field_location_images.map(image => {
      //       return image.uri.url;
      //     });
      //     console.log(images);
      //     const primaryImg = images[0];
      //     console.log(primaryImg);

      //     const locationType = location.field_location_type.name
      //     console.log(locationType);

      //     const website = location.field_location_link.uri
      //     console.log(website);
      //     // console.log(locations);
      //   });
      // })

      $.getJSON(url, function(data) {
        // Parsing JSON API object
        const parcedData = window.jsonapi.parse(data);
        // Array of location objects
        var locations = {};
        var infoWindows = [];
        var markers = [];

        const rawLocations = parcedData.data[0].field_map_locations;
        rawLocations.forEach((location, index) => {
          console.log(location);

          const locationType = location.field_location_type.name
          if (!locations[locationType]) {
            locations[locationType] = { indexes: [], list: [] }
          }
          locations[locationType].list.push("<li id='loc-" + index + "'>" + location.field_title + "</li>");
          locations[locationType].indexes.push(index);
          let icon = '';
          if (locationType === 'Parking') {
            icon = {
              url: 'https://www.ucsf.edu/sites/default/files/parking-icon-01.png',
              scaledSize: new google.maps.Size(28, 32)
            };
          }
          markers[index] = new google.maps.Marker({
            map: map,
            position: { lat: parseFloat(location.field_geofield.lat), lng: parseFloat(location.field_geofield.lon) },
            title: location.field_title,
            icon: icon
          });

          bounds.extend(markers[index].position);
          var imagesMarkup = '<div class="map-images">';
          const images = location.field_location_images;
          images.forEach((image, index ) => {
            imagesMarkup += '<div class="map-image"><img src="' + image.uri.url + '" /></div>';
          })
          imagesMarkup += '</div>';

          infoWindows[index] = new google.maps.InfoWindow({
            content: "<div><h3>" + location.field_title + "</h3><div class='body'>" + location.field_location_text.processed +
            "</div><p class='address'>" + location.field_address.address_line1 + "<br>" + location.field_address.locality + " " + location.field_address.administrative_area + " " + location.field_address.postal_code + "</p>" +
            "<a class='directions' href='https://www.google.com/maps/dir/Current+Location/" + location.field_geofield.lat + "," + location.field_geofield.lon +
            "'>Directions</a>" + (location.field_location_link.uri ? "<br/><a class='website' href='" + location.field_location_link.uri + "'>Website</a>" : "") + imagesMarkup + "</div>",
            maxWidth: 310
          })
          console.log(infoWindows[index].content);
        });

        map.fitBounds(bounds);

        var markup = '';

        $.each(locations, function(type, item) {
          markup += '<div><input id="' + type + '" type="checkbox" checked="checked" /><label for="' + type + '"><h3>' + type + '</h3></label><ul>' + item.list.join('') + '</ul></div>'

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

        $('#locations-list .loading').html(markup);

        $.each(rawLocations, function(index, location) {
          google.maps.event.addDomListener(document.getElementById('loc-' + index), 'click', (function () {
            $.each(infoWindows, function(index, infoWindow) {
              infoWindow.close()
            });
            infoWindows[index].open(map, markers[index])
          }));

          google.maps.event.addListener(markers[index], 'click', (function () {
            $.each(infoWindows, function(index, infoWindow) {
              infoWindow.close()
            });
            infoWindows[index].open(map, markers[index])
          }))
        });
        console.log(infoWindows)
        var center;
        google.maps.event.addDomListener(map, 'idle', function() {
          center = map.getCenter()
        });
        google.maps.event.addDomListener(window, 'resize', function() {
          google.maps.event.trigger(map, 'resize');
          map.setCenter(center);
        })
      })
    }
  }
})(jQuery, Drupal, drupalSettings);
