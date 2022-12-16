define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address',
		'mage/url',
		'Magento_Checkout/js/model/totals',
		'Magento_Checkout/js/action/recollect-shipping-rates',
		'Magento_Checkout/js/action/get-payment-information',
		'Magento_Checkout/js/action/get-totals',
		'Magento_Checkout/js/model/cart/cache',
		'Magento_Checkout/js/model/cart/totals-processor/default',
		'mage/translate',
		'Magento_SalesRule/js/model/payment/discount-messages',
		'Magento_Customer/js/model/customer'
    ],
    function (
        $,
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
		url,
		totalModel,
		recollectShippingRates,
		getPaymentInformationAction,
		getTotalsAction,
		cartCache,
		defaultTotal,
		$t,
		messageContainer,
		customer
    ) {
        'use strict';
		
		var referralEarnedCoupons = url.build('referralprogram/checkout/earnedcodes');
		var earnedRemove = url.build('referralprogram/checkout/earnedremove');

        return {
            saveShippingInformation: function () {
                var payload;

                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }
				if(window.referralenabled == true){
					this.getAllReferralCoupons();
				}
                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code,
                        extension_attributes:{
                            referralDiscount: $('[name="referral-discount"]').prop("checked")
                    
                        }
                    }
                };

                fullScreenLoader.startLoader();
				var currentClass = this;

                return storage.post(
                    resourceUrlManager.getUrlForSetShippingInformation(quote),
                    JSON.stringify(payload)
                ).done(
                    function (response) {
                        quote.setTotals(response.totals);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
						
                        fullScreenLoader.stopLoader();
						
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
            },
			getAllReferralCoupons: function(){
				var currentClass = this;
				totalModel.isLoading(true);
				var customerEmail = "";
				if($('#customer-email') != "undefined")
					customerEmail = $('#customer-email').val();
				$.ajax({
					url: referralEarnedCoupons,
					type: "POST",
					data: {'customer_email': customerEmail}
				}).done(function (data) {
					var dataArray = data.split(',');
					if(dataArray.length > 0 && data != ""){
						var ulHtml = '<h4>'+$t('Applied discount coupons')+'</h4><ul>';
						for(var i=0;i<dataArray.length;i++){
							ulHtml += dataArray[i];
						}
						ulHtml += '</ul>';
						$("#earned_coupons").html(ulHtml);
						$("#earned_coupons").show();
					}else{
						$("#earned_coupons").html('');
						$("#earned_coupons").show();
					}
					//$("#earned_coupons").html(data);
					currentClass.updateReferralLink();
				});
			},
			updateReferralLink: function(){
				var deferred;
				cartCache.set('totals',null);
                defaultTotal.estimateTotals();
				deferred = $.Deferred();
				recollectShippingRates();
				getPaymentInformationAction(deferred);
				var currentClass = this;
				$(".referral-remove-link").each(function(){
					$(this).click(function(){
						var code = $(this).data('code');
						currentClass.removeEarned(code);
					});
				});
				
				getTotalsAction([], deferred);
				defaultTotal.estimateTotals();
				$.when(deferred).done(function () {
					totalModel.isLoading(false);
				});
			},
			removeEarned: function(code){
				var currentClass = this;
				totalModel.isLoading(true);
				$.ajax({
					url: earnedRemove,
					type: "POST",
					data: {'coupon_code': code}, 
				}).done(function (data) {
					var dataArray = data.split(',');
					if(dataArray.length > 0 && data != ""){
						var ulHtml = '<h4>'+$t('Applied discount coupons.')+'</h4><ul>';
						//console.log('INSIDE IF');
						for(var i=0;i<dataArray.length;i++){
							ulHtml += dataArray[i];
						}
						ulHtml += '</ul>';
						$("#earned_coupons").html(ulHtml);
						$("#earned_coupons").show();
					}else{
						$("#earned_coupons").html('');
						$("#earned_coupons").show();
					}
					//$("#earned_coupons").html(data);
					var message = $t('Your referral/gift coupon is removed.');
					messageContainer.addSuccessMessage({
						'message': message
					});
					currentClass.updateReferralLink();
					
				});
			}
			
        };
    }
);
 


