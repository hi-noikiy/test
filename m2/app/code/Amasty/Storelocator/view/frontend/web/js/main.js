define([
    "jquery",
    "mage/translate",
    "Amasty_Storelocator/vendor/chosen/chosen.min",
    "Amasty_Storelocator/vendor/jquery.ui.touch-punch.min",
    "jquery/ui"
], function ($, $t, chosen) {

    $.widget('mage.amLocator', {
        options: {},
        url: null,
        useBrowserLocation: null,
        useGeo: null,
        imageLocations: null,
        map: {},
        marker: {},
        amLatId: '',
        amLngId: '',
        needGoTo: false,
        markerCluster: {},

        _create: function () {
            var self = this;

            this.ajaxCallUrl = this.options.ajaxCallUrl;
            this.useBrowserLocation = this.options.useBrowserLocation;
            this.useGeo = this.options.useGeo;
            this.imageLocations = this.options.imageLocations;
            this.amLatId = this.options.amLatId;
            this.amLngId = this.options.amLngId;
            this.initializeMap();
            this.Amastyload();
            $("#" + this.options.nearbyButtonId).click(function () {
                self.needGoTo = true;
                self.navigateMe()
            });

            $("#" + this.options.searchId).on('keyup', function () {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    $("#" + self.options.searchButtonId).click();
                }
            });

            if (this.options.isRadiusSlider) {
                this.createRadiusSlider();
            }

            $('[data-amlocator-js="filters-title"]').on('click', function () {
                $('[data-amlocator-js="filters-container"]').slideToggle();
                $(this).find('.amlocator-arrow').toggleClass('-down');
            });

            $('[data-amlocator-js="multiple-select"]').chosen({
                placeholder_text_multiple: $.mage.__("Select Some Options")
            });

            $('[data-amlocator-js="clear-filters"]').on('click', function (e) {
                e.preventDefault();
                $('[data-amlocator-js="attributes-form"]')[0].reset();
                $('[data-amlocator-js="multiple-select"]').val(null).trigger("chosen:updated");
                self.makeAjaxCall();
            });

            $("#" + this.options.searchButtonId).click(function () {
                self.makeAjaxCall()
            });

            $("#" + this.options.attributeFilterId).click(function () {
                self.makeAjaxCall()
            });

            $(document).on('click', ".amlocator-pager-container .item a",function() {
                self.makeAjaxCall('', this.href);
                event.preventDefault();
            });

            if (self.options.automaticLocate) {
                self.needGoTo = true;
                self.makeAjaxCall('', this.options.ajaxCallUrl, 1);
            }

            if (navigator.geolocation && self.useBrowserLocation == 1) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    $("#" + self.options.amLatId).val(position.coords.latitude);
                    $("#" + self.options.amLngId).val(position.coords.longitude);
                });
            }
        },

        goHome: function () {
            window.location.href = window.location.pathname;
        },

        navigateMe: function () {
            if (navigator.geolocation) {
                var self = this;
                navigator.geolocation.getCurrentPosition(function (position) {
                    self.makeAjaxCall(position, null, 1);
                }, this.navigateFail.bind(self));
            } else {
                alert($.mage.__('Sorry we\'re unable to display the nearby stores because the "Use browser location" option is disabled in the module settings. Please, contact the administrator.'));
            }
        },

        navigateFail: function (error) {
            // error param exists when user block browser location
            if (this.options.useGeoConfig == 1 && error.code == 1) {
                this.makeAjaxCall();
            }
        },

        collectParams: function (position, sortByDistance) {
            var self = this,
                lat = $('#' + this.options.amLatId).val(),
                lng = $('#' + this.options.amLngId).val(),
                currentAddress = $('#' + this.options.searchId).val();

            // using position data if current address is empty
            if ((!lat || !lng || !currentAddress) && (position != "" && typeof position !== "undefined")) {
                lat = position.coords.latitude;
                lng = position.coords.longitude;
            }

            var e = ($('[data-amlocator-js="radius-select"]').length)
                ? $('[data-amlocator-js="radius-select"]')
                : $('input[name="' + this.options.searchRadiusId + '"]:checked'),
                radius = (e) ? e.val() : '',
                form = $('[data-amlocator-js="attributes-form"]').serializeArray(),
                params = {
                    'lat': lat,
                    'lng': lng,
                    'radius': radius,
                    'mapId': this.options.mapId,
                    'storeListId': this.options.storeListId,
                    'product': self.options.productId,
                    'category': self.options.categoryId,
                    'attributes': form,
                    'sortByDistance': sortByDistance
                };

            return params;
        },

        makeAjaxCall: function (position, ajaxUrl, sortByDistance = 0) {
            var self = this,
                params = this.collectParams(position, sortByDistance);

            if (!ajaxUrl) {
                ajaxUrl = this.ajaxCallUrl;
            } else {
                // empty radius for pagination
                params['radius'] = '';
                stringParts = ajaxUrl.split('/');
                pageNumber = stringParts.pop() || stringParts.pop();
                params['p'] = pageNumber;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: params,
                showLoader: true
            }).done($.proxy(function (response) {
                response = JSON.parse(response);
                self.options.jsonLocations = response;
                self.Amastyload();
            }));
        },

        calculateDistance: function (lat, lng, measurement) {
            for (var location in this.options.jsonLocations.items) {
                var distance = MarkerClusterer.prototype.distanceBetweenPoints_(
                    new google.maps.LatLng(
                        lat,
                        lng
                    ),
                    new google.maps.LatLng(
                        this.options.jsonLocations.items[location]['lat'],
                        this.options.jsonLocations.items[location]['lng']
                    )
                );
                if (measurement == 'mi') {
                    distance = distance / 1.609344;
                }
                var mainContainer = jQuery('#' + this.options.searchId).closest('.amlocator-main-container'),
                    storeList = mainContainer.find('.amlocator-stores-wrapper'),
                    locationId = this.options.jsonLocations.items[location]['id'],
                    distanceText = '<div class="amasty_distance">Distance: ' + parseInt(distance) + ' ' + measurement + '</div>',
                    locationSelector = '[data-amid=' + locationId + '] .amlocator-block div.amlocator-store-information';
                if (storeList.find(locationSelector).find('div.amasty_distance').length > 0) {
                    storeList.find(locationSelector).find('div.amasty_distance').html(distanceText);
                } else {
                    storeList.find(locationSelector).append('<br />' + distanceText);
                }
            }
        },

        plusCodes: function(self) {
            var value = $('#' + self.options.searchId).val(),
                regExp = new RegExp("^[A-Z0-9]{8}\\+\\S{2}$");
            if (regExp.test(value) === false) {
                return false;
            }
            self.geocoder.geocode({'address' : value}, function (results, status) {
                if (status == 'OK') {
                    $('#' + self.amLatId).val(results[0].geometry.location.lat());
                    $('#' + self.amLngId).val(results[0].geometry.location.lng())
                    if (self.options.enableCountingDistance) {
                        var measurement = self.options.distanceConfig;
                        if ($('#amlocator-measurement').length > 0) {
                            measurement = $('#amlocator-measurement').val();
                        }
                        self.calculateDistance(place.geometry.location.lat(), place.geometry.location.lng(), measurement);
                    }
                    return true;
                } else {
                    return false;
                }
            })
        },

        Amastyload: function () {
            this.deleteMarkers(this.options.mapId);
            var self = this,
                mapId = this.options.mapId;

            this.processLocation();

            if (self.options.enableClustering) {
                self.markerCluster = new MarkerClusterer(this.map[this.options.mapId], this.marker[this.options.mapId], {imagePath: this.imageLocations + '/m'});
            }

            this.geocoder = new google.maps.Geocoder();

            if (this.options.showSearch) {
                var address = $('#' + this.options.searchId)[0],
                    autocompleteOptions = {
                        componentRestrictions: {country: self.options.allowedCountries}
                    },
                    autocomplete = new google.maps.places.Autocomplete(address, autocompleteOptions);

                $('#' + this.options.searchId).keyup(function (e) {
                    if (self.plusCodes(self) === true) {
                        e.preventDefault();
                    }
                });

                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();

                    if (place.geometry != null) {
                        $('#' + self.amLatId).val(place.geometry.location.lat());
                        $('#' + self.amLngId).val(place.geometry.location.lng());
                        if (self.options.enableCountingDistance) {
                            var measurement = self.options.distanceConfig;
                            if ($('#amlocator-measurement').length > 0) {
                                measurement = $('#amlocator-measurement').val();
                            }
                            self.calculateDistance(place.geometry.location.lat(), place.geometry.location.lng(), measurement);
                        }
                    } else {
                        alert($.mage.__('You need to choose address from the dropdown with suggestions.'));
                    }
                });
            }

            $('[data-mapid=' + mapId + ']').click(function () {
                var id = $(this).attr('data-amid');

                self.gotoPoint(id);
            });

            $("#" + this.options.storeListId + " .amlocator-today").click(function (event) {
                $(this).next(".amlocator-week").slideToggle();
                $(this).find(".amlocator-arrow").toggleClass("-down");
                event.stopPropagation();
            });
        },

        initializeMap: function () {
            var myOptions = {
                zoom: 9,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            this.infowindow = [];
            this.marker[this.options.mapId] = [];
            this.map[this.options.mapId] = [];
            this.map[this.options.mapId] = new google.maps.Map($("#" + this.options.mapId)[0], myOptions);
        },

        processLocation: function () {
            var self = this,
                bounds = new google.maps.LatLngBounds(),
                curtemplate = "";
                locations = this.options.jsonLocations;

            for (var i = 0; i < locations.totalRecords; i++) {
                curtemplate = locations.items[i].popup_html;

                this.createMarker(locations.items[i].lat, locations.items[i].lng, curtemplate, locations.items[i].id, locations.items[i].marker_url);
            }

            for (var locationId in this.marker[this.options.mapId]) {
                if (this.marker[this.options.mapId].hasOwnProperty(locationId)) {
                    bounds.extend(this.marker[this.options.mapId][locationId].getPosition());
                }
            }

            this.map[this.options.mapId].fitBounds(bounds);

            if (locations.totalRecords === 1 || self.needGoTo) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'bounds_changed', function () {
                    self.map[self.options.mapId].setZoom(self.options.mapZoom);
                });
            }

            if (locations.totalRecords === 0) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'bounds_changed', function () {
                    self.map[self.options.mapId].setCenter(
                        new google.maps.LatLng(
                            0,
                            0
                        )
                    );
                    self.map[self.options.mapId].setZoom(2);
                    alert($.mage.__('Sorry, no locations were found.'));
                });
            }

            if (locations && locations.storeListId) {
                $("#" + locations.storeListId).replaceWith(locations.block);
                if (locations.totalRecords > 0 && self.needGoTo) {
                    self.gotoPoint(locations.items[0].id);
                    self.needGoTo = false;
                }
            }
        },

        gotoPoint: function (myPoint) {
            var self = this,
                mapId = this.closeAllInfoWindows();

            $('[data-mapid=' + mapId + ']').removeClass('-active');
            // add class if click on marker
            $('[data-mapid=' + mapId + '][data-amid=' + myPoint + ']').addClass('-active');
            this.map[mapId].setCenter(
                new google.maps.LatLng(
                    this.marker[mapId][myPoint].position.lat(),
                    this.marker[mapId][myPoint].position.lng()
                )
            );
            this.map[mapId].setZoom(self.options.mapZoom);
            this.marker[mapId][myPoint]['infowindow'].open(
                this.map[mapId],
                this.marker[mapId][myPoint]
            );
        },

        createMarker: function (lat, lon, html, locationId, marker = '') {
            var self = this,
            newmarker = new google.maps.Marker({
                position: new google.maps.LatLng(lat, lon),
                map: this.map[this.options.mapId],
                icon: marker ? marker : ''
            });

            newmarker['infowindow'] = new google.maps.InfoWindow({
                content: html
            });
            newmarker['locationId'] = locationId;
            google.maps.event.addListener(newmarker, 'click', function () {
                self.gotoPoint(this.locationId);
            });

            // using locationId instead 0, 1, 2, i counter
            this.marker[this.options.mapId][locationId] = newmarker;
        },

        closeAllInfoWindows: function () {
            var mapId = this.element.context.id,
                spans = $("#" + mapId + ' span');

            for (var i = 0, l = spans.length; i < l; i++) {
                spans[i].className = spans[i].className.replace(/\active\b/, '');
            }

            if (typeof this.marker[mapId] !== "undefined") {
                for (var marker in this.marker[mapId]) {
                    if (this.marker[mapId].hasOwnProperty(marker)) {
                        this.marker[mapId][marker]['infowindow'].close();
                    }
                }
            }

            return mapId;
        },

        createRadiusSlider: function () {
            var self = this,
                radiusValue = $('[data-amlocator-js="radius-value"]'),
                radiusMeasurment = $('[data-amlocator-js="radius-measurment"]'),
                measurmentSelect = $('[data-amlocator-js="measurment-select"]');

            if (self.options.minRadiusValue <= self.options.maxRadiusValue) {
                $('[data-amlocator-js="range-slider"]').slider({
                    range: 'min',
                    min: self.options.minRadiusValue,
                    max: self.options.maxRadiusValue,
                    create: function () {
                        radiusValue.text($(this).slider("value"));
                        if (self.options.measurementRadius != '') {
                            radiusMeasurment.text(self.options.measurementRadius);
                        } else {
                            radiusMeasurment.text(measurmentSelect.val());
                        };
                        $("#" + self.options.searchRadiusId).val($(this).slider("value"));
                    },
                    slide: function (event, ui) {
                        radiusValue.text(ui.value);
                        $("#" + self.options.searchRadiusId).val(ui.value);
                    }
                });
            }

            measurmentSelect.on('change', function () {
                radiusMeasurment.text(this.value);
            });
        },

        deleteMarkers: function(mapId) {
            if (!_.isEmpty(this.markerCluster)) {
                this.markerCluster.clearMarkers();
            }
            for (var marker in this.marker[mapId]) {
                if (this.marker[mapId].hasOwnProperty(marker)) {
                    this.marker[mapId][marker].setMap(null);
                }
            }
            this.marker[mapId] = [];
        },

    });

    return $.mage.amLocator;
});
