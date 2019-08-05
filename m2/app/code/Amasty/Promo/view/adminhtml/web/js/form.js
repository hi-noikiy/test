define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    var ampromoForm = {
        update: function (type) {
            var action = '';
            this.resetFields(type);
            var actionFieldSet = $('#' + type +'rule_actions_fieldset_').parent();
            window.amPromoHide = 0;

            actionFieldSet.show();
            if (typeof window.amRulesHide !="undefined" && window.amRulesHide == 1) {
                actionFieldSet.hide();
            }

            var selector = $('[data-index="simple_action"] select');
            if (type !== 'sales_rule_form') {
                action = selector[1] ? selector[1].value ? selector[0].value : selector[0].value : undefined;
            } else {
                action = selector.val();
            }

            if (!action) {
                action = 'by_percent';
            }

            if (action.match(/^ampromo/)) {
                this.hideFields(['simple_free_shipping', 'apply_to_shipping'], type);
            }

            this.renameRulesSetting(action);
            this.hideTabs();
            switch (action) {
                case 'ampromo_cart':
                    actionFieldSet.hide();
                    window.amPromoHide = 1;

                    this.hideFields(['discount_qty', 'discount_step'], type);
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    break;
                case 'ampromo_items':
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showBannersTab();
                    this.showPromoItemPriceTab();
                    this.showProductLabelTab();
                    break;
                case 'ampromo_product':
                    this.showBannersTab();
                    this.showFields(['ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    this.showProductLabelTab();
                    break;
                case 'ampromo_spent':
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    break;
                case 'ampromo_eachn':
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
                    this.showPromoItemPriceTab();
                    this.showBannersTab();
                    this.showProductLabelTab();
                    break;
            }
        },
        showBannersTab: function(){
            $('[data-index=ampromorule_top_banner]').show();
            $('[data-index=ampromorule_after_product_banner]').show();
        },
        hideBannersTab: function(){
            $('[data-index=ampromorule_top_banner]').hide();
            $('[data-index=ampromorule_after_product_banner]').hide();
        },
        showPromoItemPriceTab: function(){
            $('[data-index=ampromorule_items_price]').show();
        },
        hidePromoItemPriceTab: function(){
            $('[data-index=ampromorule_items_price]').hide();
        },
        showProductLabelTab: function(){
            $('[data-index=ampromorule_product_label]').show();
        },
        hideProductLabelTab: function(){
            $('[data-index=ampromorule_product_label]').hide();
        },
        resetFields: function (type) {
            this.showFields([
                'discount_qty', 'discount_step', 'apply_to_shipping', 'simple_free_shipping'
            ], type);
            this.hideFields(['ampromorule[sku]', 'ampromorule[type]', 'ampromorule[apply_tax]', 'ampromorule[apply_shipping]'], type);
        },

        hideFields: function (names, type) {
            return this.toggleFields('hide', names, type);
        },

        showFields: function (names, type) {
            return this.toggleFields('show', names, type);
        },

        addPrefix: function (names, type) {
            for (var i = 0; i < names.length; i++) {
                names[i] = type + '.' + type + '.' + 'actions.' + names[i];
            }

            return names;
        },

        toggleFields: function (method, names, type) {
            registry.get(this.addPrefix(names, type), function () {
                for (var i = 0; i < arguments.length; i++) {
                    arguments[i][method]();
                }
            });
        },

        /**
         *
         * @param action
         */
        renameRulesSetting: function (action) {
            var discountStep = $('[data-index="discount_step"] label span');

            switch (action) {
                case 'ampromo_eachn':
                    discountStep.text($.mage.__("Each N-th"));
                    break;
                default:
                    discountStep.text($.mage.__("Discount Qty Step (Buy X)"));
                    break;
            }
        },

        hideTabs: function () {
            this.hidePromoItemPriceTab();
            this.hideBannersTab();
            this.hideProductLabelTab();
        }
    };

    return ampromoForm;
});