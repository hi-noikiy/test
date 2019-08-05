define(
    [
        'ko',
        'jquery',
        'uiComponent'
    ],
    function (ko, $, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Cminds_Salesrep/salesrep-step'
            },

            context: function () {
                return this;
            },

            isSalesrepEnabled: function () {
                return window.checkoutConfig.salesrep.isSalesrepEnabled;
            },

            getSalesrepList: function () {
                return window.checkoutConfig.salesrep.getSalesrepList;
            },

            getSalesrepLabel: function () {
                return window.checkoutConfig.salesrep.getSalesrepLabel;
            },

            getSalesrepNote: function () {
                return window.checkoutConfig.salesrep.getSalesrepNote;
            },

            saveSelectedSalesrep: function () {
                $('#salesrep-choose #co-salesrep-form #salesrep option').each(function () {
                    if ($(this).is(':selected')) {
                        if (this.value) {
                            var selectedSalesrep = this.value;
                            $.ajax({
                                url: window.checkoutConfig.salesrep.getAjaxUrl,
                                type: "POST",
                                data: {selectedSalesrep: selectedSalesrep},
                                dataType: 'json',
                                success: function () {
                                }
                            });
                        }
                    }
                })
            }
        });
    }
);
