define([
    'jquery',
    'prototype'
], function (jQuery) {

    if (!window.hasOwnProperty('Potato')) {
        Potato = {};
    }
    if (!Potato.hasOwnProperty('AddressAutocomplete')) {
        Potato.AddressAutocomplete = {};
    }

    Potato.AddressAutocomplete.GooglePlaces = Class.create({
        initialize: function (config) {
            this.initConfig(config);
            this.initJs();
            this.initStyle();
        },

        initConfig: function (config) {
            this.config = config;
        },

        initJs: function () {
            var poGooglePlacesInstance = this;
            if (!this.isJsInited() && !this.isJsLoaded() && this.getApiKey()) {
                Potato.AddressAutocomplete.GooglePlaces._isJsInited = true;

                var s = document.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.defer = true;
                s.src = this._buildGooglePlacesSrc();

                Event.observe(s, 'load', poGooglePlacesInstance._onGooglePlacesLoaded.bind(poGooglePlacesInstance));

                var x = document.getElementsByTagName('script')[0];
                x.parentNode.insertBefore(s, x);
            } else if (this.isJsInited() && !this.isJsLoaded()) {
                poGooglePlacesInstance._intervalId = setInterval(
                    poGooglePlacesInstance._waitOnGooglePlacesLoaded.bind(poGooglePlacesInstance),
                    100
                );
            } else if (this.isJsInited() && this.isJsLoaded()) {
                this._onGooglePlacesLoaded();
            }
        },

        _buildGooglePlacesSrc: function () {
            var src = '//maps.googleapis.com/maps/api/js?';
            var query = Object.toQueryString(
                {
                    'key': this.getApiKey(),
                    'libraries': 'places',
                    'language': this.config.locale ? this.config.locale : ''
                }
            );
            return src + query;
        },

        initStyle: function () {
            if (!this.config.hidePoweredByGoogleLabel) {
                return;
            }
            if (this.isStyleInited()) {
                return;
            }
            Potato.AddressAutocomplete.GooglePlaces._isStyleInited = true;

            var s = document.createElement('style');
            s.type = 'text/css';
            s.innerText = '.pac-logo:after{display:none !important;}';

            var x = document.head || document.getElementsByTagName('head')[0];
            x.appendChild(s);
        },

        initObservers: function () {
            if (!this.getLocationElement()) {
                setTimeout(this.initObservers.bind(this), 100);
                return;
            }
            this.initAutocomplete();
            this.initBrowserGeolocation();
            this.initCountryInputChange();
        },

        initCountryInputChange: function () {
            if (!this.getCountryInput()) {
                return;
            }
            var poGooglePlacesInstance = this;
            this.getCountryInput().observe(
                'change',
                poGooglePlacesInstance._onCountryInputChanged.bind(poGooglePlacesInstance)
            );
        },

        _onCountryInputChanged: function () {
            this._setCountryRestriction();
        },

        _setCountryRestriction: function () {
            if (!this.config.useCountryRestriction) {
                return;
            }
            if (!this.getAutocomplete()) {
                return;
            }
            if (!this.getCountryInput()) {
                return;
            }
            if (!this.getCountryInput().getValue()) {
                return;
            }
            this.getAutocomplete().setComponentRestrictions({
                country: this.getCountryInput().getValue()
            });
        },

        getCountryInput: function () {
            return $$(this.config.countryInput).first();
        },

        _onGooglePlacesLoaded: function () {
            Potato.AddressAutocomplete.GooglePlaces._isJsLoaded = true;
            this.initObservers();
        },

        _waitOnGooglePlacesLoaded: function () {
            if (Potato.AddressAutocomplete.GooglePlaces._isJsLoaded) {
                clearInterval(this._intervalId);
                this.initObservers();
            }
        },

        _fireOnAddressChangedCallback: function () {
            if (!this.config.onAddressChangedCallback) {
                return;
            }
            if (typeof this.config.onAddressChangedCallback !== 'function') {
                return;
            }
            this.config.onAddressChangedCallback();
        },

        getApiKey: function () {
            return this.config.apiKey;
        },

        canUseBrowserGeolocation: function () {
            return this.config.canUseBrowserGeolocation;
        },

        initBrowserGeolocation: function () {
            if (!this.canUseBrowserGeolocation()) {
                return;
            }
            if (!this.getAutocomplete()) {
                return;
            }
            if (!navigator.geolocation) {
                return;
            }

            var poGooglePlacesInstance = this;
            navigator.geolocation.getCurrentPosition(function (position) {
                var geolocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                var circle = new google.maps.Circle({
                    center: geolocation,
                    radius: position.coords.accuracy
                });
                poGooglePlacesInstance.getAutocomplete().setBounds(circle.getBounds());
            });
        },

        getLocationElement: function () {
            return $$(this.config.locationInput).first();
        },

        getAutocomplete: function () {
            var locationElement = this.getLocationElement();
            return (locationElement && locationElement.addressAutocomplete) ? locationElement.addressAutocomplete : null;
        },

        setAutocomplete: function (autocomplete) {
            var locationElement = this.getLocationElement();
            if (locationElement) {
                locationElement.addressAutocomplete = autocomplete;
            }
        },

        initAutocomplete: function () {
            this._initAutocomplete();
            this._setCountryRestriction();
        },

        _initAutocomplete: function () {
            if (this.getAutocomplete()) {
                return;
            }

            var locationElement = this.getLocationElement();
            if (!locationElement) {
                return;
            }

            var autocomplete = new google.maps.places.Autocomplete(
                /* @type {!HTMLInputElement} */(locationElement),
                {types: ['geocode']}
            );

            var poGooglePlacesInstance = this;
            autocomplete.addListener(
                'place_changed', poGooglePlacesInstance.fillAddressForm.bind(poGooglePlacesInstance)
            );

            this.setAutocomplete(autocomplete);
        },

        getPlace: function () {
            if (!this.getAutocomplete()) {
                return null;
            }
            return this.getAutocomplete().getPlace();
        },

        fillAddressForm: function () {
            if (!this.getAutocomplete()) {
                return;
            }

            var poGooglePlacesInstance = this;
            $H(this.config.addressForm).each(
                poGooglePlacesInstance.processField.bind(poGooglePlacesInstance)
            );

            this._fireOnAddressChangedCallback();
        },

        processField: function (fieldConfig) {
            var input = $$(fieldConfig.value.inputId).first();
            if (!input) {
                return;
            }

            var poGooglePlacesInstance = this;

            var inputValue = null;
            if ('processorFn' in fieldConfig.value && typeof fieldConfig.value['processorFn'] == 'function') {
                inputValue = fieldConfig.value['processorFn'].apply(poGooglePlacesInstance, fieldConfig);
            } else if ('processorName' in fieldConfig.value && typeof poGooglePlacesInstance[fieldConfig.value['processorName']] == 'function') {
                inputValue = poGooglePlacesInstance[fieldConfig.value['processorName']](fieldConfig);
            } else {
                inputValue = this.getPlaceComponentValue(fieldConfig.value.component, fieldConfig.value.name_type);
            }

            input.setValue(inputValue);
            input.simulate('change');
            if (typeof(jQuery) != "undefined" && jQuery(fieldConfig.value['inputId'].replace(":",  "\\:")).length) {
                jQuery(fieldConfig.value['inputId'].replace(":",  "\\:")).trigger("chosen:updated");
            }
        },

        getPlaceComponentValue: function (componentName, nameType) {
            var componentIndex = this.getPlaceComponentIndex(componentName);
            if (componentIndex === null) {
                return null;
            }
            if (!(nameType in this.getPlace().address_components[componentIndex])) {
                return null;
            }
            return this.getPlace().address_components[componentIndex][nameType];
        },

        getPlaceComponentIndex: function (componentName) {
            var componentIndex = null;
            if (this.getPlace().address_components) {
                this.getPlace().address_components.each(
                    function (component, index) {
                        if (componentIndex) {
                            return null;
                        }
                        if (component.types.first() !== componentName) {
                            return null;
                        }
                        componentIndex = index;
                    }
                );
            }
            return componentIndex;
        },

        _prepareRegionId: function (fieldConfig) {
            var componentIndex = null;
            if (!this.getPlace().address_components) {
                return null;
            }
            this.getPlace().address_components.each(
                function (component, index) {
                    if (componentIndex) {
                        return;
                    }
                    if (component.types.first() !== 'country') {
                        return;
                    }
                    componentIndex = index;
                }
            );

            if (!componentIndex) {
                return null;
            }

            var countryCode = this.getPlace().address_components[componentIndex]['short_name'];

            if (this.config.regionConfig.config.regions_required.indexOf(countryCode) === -1) {
                return null;
            }
            if (!this.config.regionConfig[countryCode]) {
                return null;
            }

            var regionLongName = this.getPlaceComponentValue(fieldConfig.value.component, 'long_name');
            var regionShortName = this.getPlaceComponentValue(fieldConfig.value.component, 'short_name');

            var regionIndex = this._findRegionIdByName(countryCode, regionLongName, regionShortName);
            if (!regionIndex) {
                var regionLongName = this.getPlaceComponentValue('administrative_area_level_2', 'long_name');
                var regionShortName = this.getPlaceComponentValue('administrative_area_level_2', 'short_name');
                regionIndex = this._findRegionIdByName(countryCode, regionLongName, regionShortName);
            }
            return regionIndex;
        },

        _findRegionIdByName: function(countryCode, regionLongName, regionShortName) {
            if (!regionLongName || !regionShortName) {
                return null;
            }
            var regionIndex = null;
            $H(this.config.regionConfig[countryCode]).each(
                function (region) {
                    if (regionIndex) {
                        return;
                    }
                    if (
                        region.value.code.toLowerCase() !== regionLongName.toLowerCase()
                        && region.value.name.toLowerCase() !== regionLongName.toLowerCase()
                        && region.value.code.toLowerCase() !== regionShortName.toLowerCase()
                        && region.value.name.toLowerCase() !== regionShortName.toLowerCase()
                    ) {
                        return;
                    }
                    regionIndex = region.key;
                }
            );
            return regionIndex;
        },

        _prepareCombinedStreet1: function (fieldConfig) {
            if (!this.getPlace().address_components) {
                return null;
            }
            if (this.getPlace().name) {
                return this.getPlace().name;
            }
            var result = '';

            var subpremise = subpremiseNumberIndex = this.getPlaceComponentValue('subpremise', 'long_name');
            if (subpremise) {
                result += subpremise + '/';
            }

            var streetNumber = streetNumberIndex = this.getPlaceComponentValue('street_number', 'long_name');
            if (streetNumber) {
                result += streetNumber;
            }
            var route = this.getPlaceComponentValue(fieldConfig.value.component, fieldConfig.value.name_type);
            if (route) {
                if (parseFloat(this.getPlace().name)) {
                    result += ' ' + route;
                } else {
                    result = route + ' ' + result;
                }
            }
            return result;
        },

        _prepareCombinedStreet2: function (fieldConfig) {
            return '';
        },

        _prepareStreet2: function (fieldConfig) {
            if (!this.getPlace().address_components) {
                return null;
            }
            var result = '';
            var subpremise = subpremiseNumberIndex = this.getPlaceComponentValue('subpremise', 'long_name');
            if (subpremise) {
                result += subpremise + '/';
            }

            var streetNumber = streetNumberIndex = this.getPlaceComponentValue('street_number', 'long_name');
            if (streetNumber) {
                result += streetNumber;
            }
            return result;
        },

        isJsInited: function () {
            return Potato.AddressAutocomplete.GooglePlaces._isJsInited;
        },

        isJsLoaded: function () {
            return Potato.AddressAutocomplete.GooglePlaces._isJsLoaded;
        },

        isStyleInited: function () {
            return Potato.AddressAutocomplete.GooglePlaces._isStyleInited;
        }
    });
    Potato.AddressAutocomplete.GooglePlaces._isJsInited = false;
    Potato.AddressAutocomplete.GooglePlaces._isJsLoaded = false;
    Potato.AddressAutocomplete.GooglePlaces._isStyleInited = false;

});