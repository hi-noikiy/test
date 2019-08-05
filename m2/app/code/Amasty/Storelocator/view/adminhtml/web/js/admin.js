define([
    "jquery",
    "jquery/ui"
], function ($) {
    $.widget('mage.amLocator', {
        options: {},
        apiKey: null,
        markers: [],

        _create: function () {
            this.apiKey = this.options.apiKey;

            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = 'https://maps.googleapis.com/maps/api/js?v=3.17.exp&key=' + this.apiKey;
            document.body.appendChild(script);
            var self = this;
            self.observGallery();

            $('#location_lat').keyup(function() {
                document.getElementById("location_lat").value = document.getElementById("location_lat").value.replace(",",".");
                self.displayByLatLng();
            });

            $('div[data-index="map"]').click(function() {
                self.displayByLatLng();
            });

            $('#amlocator_fill').click(function() {
                self.display();
            });
            
            $('#location_lng').keyup(function() {
                document.getElementById("location_lng").value = document.getElementById("location_lng").value.replace(",",".");
                self.displayByLatLng();
            });
        },

        observGallery () {
            self = this;
            var target = $('div[data-index="image_gallery"]').find('.file-uploader')[0],
                config = {
                    attributes: true,
                    childList: true,
                    characterData: true
                },
                observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    var newNodes = mutation.addedNodes;

                    if (newNodes !== null) {
                        var nodes = $(newNodes);
                        nodes.each(function () {
                            var node = $(this).find('.am-make-base-button');

                            if (node.length !== 0) {
                                if (node.parent().parent().find('.preview-image')[0].title
                                    === $('input[name="base_img"]').val()
                                ) {
                                    node.parent().parent().addClass('am-base-img');
                                }
                                node.click(function () {
                                    self.makeBase(node)
                                });
                            }
                        });
                    }
                })
            });
            observer.observe(target, config);
        },

        makeBase: function(el) {
            var baseImage = el.parent().parent();
            $('.am-preview-image').each(function () {
                $(this).removeClass('am-base-img');
            });
            baseImage.addClass('am-base-img');
            $('input[name="base_img"]').val(baseImage.find('.preview-image')[0].title).change();
        },

        displayByLatLng: function() {
            document.getElementById("map-canvas").style.display = "block";
            var mapOptions = {
                zoom: 4
            };

            if (!this.map) {
                this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            }
            var lat = $('input[name="lat"]').val();
            var lng = $('input[name="lng"]').val();
            if ($('.marker-uploader-preview').find('.preview-image')) {
                var markerImage = $('.marker-uploader-preview').find('.preview-image').attr('src');
            }
            var myLatlng = new google.maps.LatLng(lat, lng),
            marker = new google.maps.Marker({
                map: this.map,
                position: myLatlng,
                icon: markerImage ? markerImage : ''
            });
            this.deleteMarkers();
            this.markers.push(marker);
            this.map.setCenter(myLatlng);

            return true;
        },

        deleteMarkers: function() {
            for (var i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap();
            }
            this.markers = [];
        },

        display: function(){
            var country = $('select[name="country"]').val();
            var city = $('input[name="city"]').val();
            var zip = $('input[name="zip"]').val();
            var address = $('input[name="address"]').val();

            address = country +','+ city+','+zip+','+address;

            geocoder = new google.maps.Geocoder();
            document.getElementById("map-canvas").style.display = "block";
            var mapOptions = {
                zoom: 4
            };

            if (!this.map)
                this.map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);


            self = this;
            geocoder.geocode( { 'address': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if ($('.marker-uploader-preview').find('.preview-image')) {
                        var markerImage = $('.marker-uploader-preview').find('.preview-image').attr('src');
                    }
                    self.map.setCenter(results[0].geometry.location);
                    $('input[name="lat"]').val(results[0].geometry.location.lat()).trigger('change');
                    $('input[name="lng"]').val(results[0].geometry.location.lng()).trigger('change');

                    var marker = new google.maps.Marker({
                        map: self.map,
                        position: results[0].geometry.location,
                        icon: markerImage
                    });
                    self.deleteMarkers();
                    self.markers.push(marker);

                }
            });
        }
    });
    return $.mage.amLocator;
});
