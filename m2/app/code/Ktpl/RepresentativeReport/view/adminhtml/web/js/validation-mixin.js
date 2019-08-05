define([
    'jquery'
], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod(
            'valid-date',
            function (value) {
                
                try { $.datepicker.parseDate("dd-mm-yy",value,null); return true; }
                catch(e) { 
                    console.log(e);
                    return false;
                }
            },
            $.mage.__('Please enter a valid date')
        );
    }
});