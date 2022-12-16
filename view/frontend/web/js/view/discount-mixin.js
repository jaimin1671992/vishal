/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon',
	'mage/url',
	'mage/translate',
	'Magento_Checkout/js/model/totals',
	'Magento_Checkout/js/model/full-screen-loader',
	'Magento_Checkout/js/action/recollect-shipping-rates',
	'Magento_Checkout/js/action/get-payment-information',
	'Magento_SalesRule/js/model/payment/discount-messages',
	'Magento_Checkout/js/model/cart/cache',
	'Magento_Checkout/js/model/cart/totals-processor/default',
	'Magento_Checkout/js/action/get-totals',
	'Magento_Customer/js/model/customer'
], function ($,
		ko, 
		Component, 
		quote, 
		setCouponCodeAction, 
		cancelCouponAction, 
		coupon, 
		url, 
		$t , 
		totalModel, 
		fullScreenLoader,
		recollectShippingRates, 
		getPaymentInformationAction, 
		messageContainer, 
		cartCache, 
		defaultTotal, 
		getTotalsAction,
		customer
	) {
    'use strict';

    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

	var referralCode = ko.observable(null);
	var referralCouponCheck = url.build('referralprogram/checkout/couponload');
	var referralCouponApply = url.build('referralprogram/checkout/couponapply');
	var referralCouponRemove = url.build('referralprogram/checkout/couponremove');
	var referralEarnedCoupons = url.build('referralprogram/checkout/earnedcodes');
	var earnedRemove = url.build('referralprogram/checkout/earnedremove');
	var referralCoupons = {};
	
	if(window.referralenabled == false){
		$.ajax({
			url: referralCouponCheck,
			type: "POST",
			//data: {'quote_id':quote.getQuoteId()}, 
		}).done(function (data) {
			//console.log(data);
			referralCode(data);
		});
	}
	//console.log(referralCode);

    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied(couponCode() != null);
	
	

    

    return Component.extend({
        defaults: {
            //template: 'Magento_SalesRule/payment/discount'
			template: 'Tvape_ReferralProgram/payment/discount',
        },
        couponCode: couponCode,
		referralcode:referralCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,
		
		

        /**
         * Coupon code application procedure
         */
        apply: function () {
			/*if(window.referralenabled == false){
				if (discountMixin.validate()) {
					setCouponCodeAction(couponCode(), isApplied);
				}
				return;
			}*/
			var preCoupon = couponCode();
			var discountMixin = this;
			var customerEmail = "";
			if($('#customer-email') != "undefined")
				customerEmail = $('#customer-email').val();
			$.ajax({
				url: referralCouponApply,
				type: "POST",
				data: {'coupon_code': couponCode(), 'customer_email': customerEmail}, 
			}).done(function (data) {
				if(data != ""){
					
					var dataArray = data.split(',');
					if(data == "commission error"){
						var message = $t('Commission Discount is graterthan total.');
						messageContainer.addErrorMessage({
							'message': message
						});
					}else if(dataArray[0] == "applied_earned_coupon"){
						var deferred;
						//fullScreenLoader.startLoader();
						deferred = $.Deferred();
						totalModel.isLoading(true);
						recollectShippingRates();
						getPaymentInformationAction(deferred);
						$("#discount-code").val('');
						if(dataArray.length > 1){
							var ulHtml = '<h4>'+$t('Applied discount coupons.')+'</h4><ul>';
							for(var i=1;i<dataArray.length;i++){
								ulHtml += dataArray[i];
							}
							ulHtml += '</ul>';
							$("#earned_coupons").html(ulHtml);
						}
						discountMixin.updateReferralLink();
						$.when(deferred).done(function () {
							//fullScreenLoader.stopLoader();
							totalModel.isLoading(false);
						});
						getTotalsAction([], deferred);
						var message = $t('Your referral/gift coupon was successfully applied.');
						messageContainer.addSuccessMessage({
							'message': message
						});
					}else{
						couponCode(data);
						if (discountMixin.validate()) {
							setCouponCodeAction(data, isApplied);
						}
						$("#discount-code").val(preCoupon);
					}
				}else{
					if (discountMixin.validate()) {
						setCouponCodeAction(couponCode(), isApplied);
					}
				}
			});
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
			var discountMixin = this;
			$.ajax({
				url: referralCouponRemove,
				type: "POST",
				//data: {'coupon_code': couponCode()}, 
			}).done(function (data) {
				if (discountMixin.validate()) {
					couponCode('');
					cancelCouponAction(isApplied);
				}
			});
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
			
            var form = '#discount-form';

            return $(form).validation() && $(form).validation('isValid');
        },
		
		referralCodeVisible: function(){
			
			$("#discount-code").val(referralCode()); 
			return true;
		},
		cancelreferral: function(){
			//console.log('INSIDE');
		},
		getAllReferralCoupons: function(){
			var currentClass = this;
			if(customer.isLoggedIn()){
				return false;
			}
			var customerEmail = "";
			if($('#customer-email') != "undefined")
				customerEmail = $('#customer-email').val();
			$.ajax({
				url: referralEarnedCoupons,
				type: "POST",
				data: {'customer_email': customerEmail}
			}).done(function (data) {
				if(data != ""){
					$("#earned_coupons").html(data);
					currentClass.updateReferralLink();
					return true;
				}
			});
			return true;
		},
		updateReferralLink: function(){
			var currentClass = this;
			$(".referral-remove-link").each(function(){
				$(this).click(function(){
					var code = $(this).data('code');
					currentClass.removeEarned(code);
				});
			});
		},
		removeEarned: function(code){
			totalModel.isLoading(true);
			var discountMixin = this;
			$.ajax({
				url: earnedRemove,
				type: "POST",
				data: {'coupon_code': code}, 
			}).done(function (data) {
				cartCache.set('totals',null);
                defaultTotal.estimateTotals();
				var dataArray = data.split(',');
				var deferred;
				deferred = $.Deferred();
				totalModel.isLoading(true);
				recollectShippingRates();
				getPaymentInformationAction(deferred);
				//$("#discount-code").val('');
				if(dataArray.length > 0){
					var ulHtml = '<h4>'+$t('Applied discount coupons.')+'</h4><ul>';
					for(var i=0;i<dataArray.length;i++){
						ulHtml += dataArray[i];
					}
					ulHtml += '</ul>';
					$("#earned_coupons").html(ulHtml);
				}
				discountMixin.updateReferralLink();
				$.when(deferred).done(function () {
					//fullScreenLoader.stopLoader();
					totalModel.isLoading(false);
				});
				getTotalsAction([], deferred);
				var message = $t('Your referral/gift coupon is removed.');
				messageContainer.addSuccessMessage({
					'message': message
				});
				totalModel.isLoading(false);
			});
		}
    });
});