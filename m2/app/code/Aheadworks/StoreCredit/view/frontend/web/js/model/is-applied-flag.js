/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

define(
    [
        'ko',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, totals) {
        'use strict';

        return ko.pureComputed(function() {
            var scTotals = totals.getSegment('aw_store_credit');

            return scTotals != null && scTotals.value != 0;
        });
    }
);
