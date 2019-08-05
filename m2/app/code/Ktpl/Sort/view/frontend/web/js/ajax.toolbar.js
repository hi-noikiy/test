/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
define([
    "jquery",
    "jquery/ui"
], function($) {
    /**
     * Custom ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
     */
    $.widget('yosto.productListToolbarForm', {

        options: {
            modeControl: '[data-role="mode-switcher"]',
            directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            mode: 'product_list_mode',
            direction: 'product_list_dir',
            order: 'product_list_order',
            limit: 'product_list_limit',
            modeDefault: 'grid',
            directionDefault: 'asc',
            orderDefault: 'position',
            limitDefault: '9',
            url: ''
        },

        _create: function () {
            this._bind($(this.options.modeControl), this.options.mode, this.options.modeDefault);
            this._bind($(this.options.directionControl), this.options.direction, this.options.directionDefault);
            this._bind($(this.options.orderControl), this.options.order, this.options.orderDefault);
            this._bind($(this.options.limitControl), this.options.limit, this.options.limitDefault);
        },

        _bind: function (element, paramName, defaultValue) {
            if (element.is("select")) {
                element.on('change', {paramName: paramName, default: defaultValue}, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {paramName: paramName, default: defaultValue}, $.proxy(this._processLink, this));
            }
        },

        _processLink: function (event) {
            event.preventDefault();
            this.changeUrl(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default
            );
        },

        _processSelect: function (event) {
            this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
        },

        changeUrl: function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent;
            var urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;
            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    ? decode(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }
            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                //delete paramData[paramName];
            }
            //paramData = $.param(paramData);

            var currentUrl = baseUrl ;//+ (paramData.length ? '?' + paramData : '');

            //Fix bug duplicate ajax request because of multiple js event binding.
//            if (window.location.href == currentUrl) {
//                return;
//            }

            window.history.pushState(null, 'toolbar filter', currentUrl);
            /** ajax submit */
                // var updateUrl = window.location.href;
            var $loader = $('.ln-loading-wrap');
            var $content = $('.columns');
            $loader.css('height', $("#maincontent").height());
            $loader.show();
            $('.products-grid').fadeTo('slow', 0.5, function() {});
            /*Fix error: tool bar duplicate ajax request*/
            $.ajax({
                url: currentUrl,
                type: 'POST',
                cache: false,
                data : paramData,
                success: function(res){
                    $content.empty();
                    var newColumnsContent = $(res).find('.columns').html();
                    $content.html(newColumnsContent).trigger('contentUpdated');
                    $('.attribute-detail').fadeTo('slow', 1, function() {});
                    $('html, body').animate({
                        scrollTop: $('.attribute-detail').offset().top - 100
                    }, 2000);
                    $('.products-grid').fadeTo('slow', 1, function() {});
                    $(".attribute-detail .more").toggle(function(){
                        $(this).text("Read Less").siblings(".complete").show();    
                    }, function(){
                        $(this).text("Read More").siblings(".complete").hide();    
                    });
                }
            }).always(function(){$loader.hide();});

        }
    });
        
    return $.yosto.productListToolbarForm;
});
