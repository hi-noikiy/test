define(
   [
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
   ],
   function (Component,quote,total) {
       "use strict";
       return Component.extend({
           defaults: {
               template: 'Ktpl_Wholesaler/checkout/summary/tierdiscount'
           },
           totals: quote.getTotals(),
           isDisplayedCustomdiscount : function(){
               return this.getPureValue() != 0;
           },
           getTierDiscount : function(){
               return this.getFormattedPrice(this.getPureValue());
           },
           getPureValue: function () {
            var price = 0;

            if (this.totals() && this.totals()['tier_discount']) {
                price = parseFloat(this.totals()['tier_discount']);
            }
            return price;
        }
       });
   }
);

