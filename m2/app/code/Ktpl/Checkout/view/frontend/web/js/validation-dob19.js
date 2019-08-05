define([
    'jquery',
    'moment'
], function ($, moment) {
    "use strict";  
    return function (validator) {
        validator.addRule(
                'validation-dob19',
                function (value) {
                    var today = new Date();
                    var nowyear = today.getFullYear();
                    var nowmonth = today.getMonth();
                    var nowday = today.getDate();
                    var birth = new Date(value);
                    var birthyear = birth.getFullYear();
                    var birthmonth = birth.getMonth();
                    var birthday = birth.getDate();
                    var age = nowyear - birthyear;
                    var age_month = nowmonth - birthmonth;
                    var age_day = nowday - birthday;
                    if (age_month < 0 || (age_month == 0 && age_day < 0)) {
                        age = parseInt(age) - 1;
                    }
                    if ((age == 17 && age_month <= 0 && age_day <= 0) || age < 17) {
                        return false;
                    }
                    return true;
                },
                $.mage.__('Age cannot be less than 18 Years.Please enter correct age.')
                );
        return validator;
    }
});