define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'

], function (ko, Component, quote, priceUtils, totals) {
    'use strict';
    var show_hide_referral_blockConfig = window.checkoutConfig.show_hide_referral_block;
    var referral_discount_label = window.checkoutConfig.referral_discount_label;
    var custom_referral_discount = window.checkoutConfig.custom_referral_discount;

    return Component.extend({

        totals: quote.getTotals(),
        canVisibleReferralDiscountBlock: show_hide_referral_blockConfig,
        getFormattedPrice: ko.observable(priceUtils.formatPrice(custom_referral_discount, quote.getPriceFormat())),
        getReferralDiscountLabel:ko.observable(referral_discount_label),
        

        isDisplayed: function () {
            return this.getValue() != 0;
        },
       
        getValue: function() {
            var price = 0;
            if (this.totals() && totals.getSegment('referral_discount')) {
                price = totals.getSegment('referral_discount').value;
            }
            return price;
        },
        getInFormattedPrice: function() {
            var price = 0;
            if (this.totals() && totals.getSegment('referral_discount')) {
                price = totals.getSegment('referral_discount').value;
            }

            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },
    });
});
