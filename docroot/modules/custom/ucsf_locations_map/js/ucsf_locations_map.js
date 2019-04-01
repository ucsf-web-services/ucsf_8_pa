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
      //var nid = Drupal.settings.nid;
      //you must double escape the newlines coming from the Google copy/paste so they look like this [ \\n ]
      $.getJSON('/maps_api.php', function(data) {
        console.dir(data);
        var locations = {};
        var infoWindows = [];
        var markers = [];
        //console.dir(data);
        $.each(data.nodes, function(index, n) {
          var location = n.node;
          if (!locations[location.Type]) {
            locations[location.Type] = { indexes: [], list: [] }
          }
          locations[location.Type].list.push("<li id='loc-" + index + "'>" + location.Title + "</li>");
          locations[location.Type].indexes.push(index);
          //console.dir(location);
          var geofield;
          if (location.hasOwnProperty('Geofield')) {
            geofield = location.Geofield.split(', ');
          } else {
            geofield = location.geofield.split(', ');
          }
          var icon = '';
          if(location.Type == 'Parking') {
            // icon = 'https://www.ucsf.edu/sites/default/files/parking-icon-01.png'
            icon = {
              url: 'https://www.ucsf.edu/sites/default/files/parking-icon-01.png',
              scaledSize: new google.maps.Size(28, 32)
            };
          }
          if(location.Body == null) {
            location.Body = '';
          }
          markers[index] = new google.maps.Marker({
            map: map,
            position: { lat: parseFloat(geofield[0]), lng: parseFloat(geofield[1]) },
            title: location.Title,
            icon: icon
          });

          bounds.extend(markers[index].position);

          var imagesMarkup = '<div class="map-images">';
          if ($.isArray(location.Images)) {
            $.each(location.Images, function(index, img) {
              imagesMarkup += '<div class="map-image"><img src="' + img.src + '" /></div>';
            })
          } else if (location.Images != '' && location.Images != null) {
            imagesMarkup += '<div class="map-image"><img src="' + location.Images + '" /></div>';
          }
          imagesMarkup += '</div>';

          infoWindows[index] = new google.maps.InfoWindow({
            content: "<div><h3>" + location.Title + "</h3><div class='body'>" + location.Body +
            "</div><p class='address'>" + location.Address + "</p>" +
            "<a class='directions' href='https://www.google.com/maps/dir/Current+Location/" + geofield[0] + "," + geofield[1] +
            "'>Directions</a>" + (location.Link ? "<br/><a class='website' href='" + location.Link + "'>Website</a>" : "") + imagesMarkup + "</div>",
            maxWidth: 310
          })
        });

        map.fitBounds(bounds);

        var markup = '';

        $.each(locations, function(type, l) {
          markup += '<div><input id="' + type + '" type="checkbox" checked="checked" /><label for="' + type + '"><h3>' + type + '</h3></label><ul>' + l.list.join('') + '</ul></div>'

          $('body').on('click', "label[for='" + type + "']", function (){
            var input = $('input#' + type);

            if (input.is(':checked')) {
              input.nextAll('ul').hide('fast');
              $.each(locations[type].indexes, function(i, index) {
                infoWindows[index].close();
                markers[index].setVisible(false)
              })
            } else {
              input.nextAll('ul').show('fast');
              $.each(locations[type].indexes, function(i, index) {
                infoWindows[index].close();
                markers[index].setVisible(true)
              })
            }
          })
        });
        //pageTitle =  $('#locations-list .title').html();

        $('#locations-list .loading').html(markup);

        $.each(data.nodes, function(index, n) {
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
