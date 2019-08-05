/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */


define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';
        var tempAllaffiliateData = window.affiliateplusInfo;
        var allData = ko.observable(tempAllaffiliateData);

        return {
            allData: allData,
            getData: function(){
                return allData;
            },

            setData: function(data){
                allData(data);
            }
        }
    }
);
