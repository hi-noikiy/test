define([
    'jquery'
], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod(
                'validate-gift',
                function (value) {                          
                   return !value?false:true;                  
                },
                $.mage.__('Please select your free gift.')
                );
    }
});