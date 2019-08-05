define(
   [
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
   ],
   function (Component,quote,total) {
       "use strict";
       return Component.extend({
           defaults: {
               template: 'Ktpl_Paymentcharge/checkout/summary/paymentcharge'
           },
           totals: quote.getTotals(),
           isDisplayedCustomdiscount : function(){
                if(quote.paymentMethod() !=null && quote.paymentMethod().method == 'classyllama_llamacoin'){
                    return this.getPureValue() != 0;
                }
                return 0;
           },
           getPaymentCharge : function(){
               return this.getFormattedPrice(this.getPureValue());
           },
           getPureValue: function () {
            var price = 0;

            if (this.totals() && this.totals()['payment_charge']) {
                price = parseFloat(this.totals()['payment_charge']);
            }
            return price;
        }
       });
   }
);

