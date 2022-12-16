<?php

namespace Tvape\ReferralProgram\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
	
	protected $_checkoutSession;
	protected $_redeemFactory;
	protected $_earnedFactory;
	protected $_scopeConfig;
	protected $_ruleFactory;
	protected $_pricingHelper;
	protected $_quoteFactory;
	protected $_orderFactory;
	protected $storeManager;
	
	public function __construct(
		\Magento\Checkout\Model\Session $checkoutSession,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\SalesRule\Model\RuleFactory $ruleFactory,
		\Magento\Framework\Pricing\Helper\Data $pricingHelper,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	){
		$this->_checkoutSession = $checkoutSession;
		$this->_redeemFactory = $redeemFactory;
		$this->_earnedFactory = $earnedFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_ruleFactory = $ruleFactory;
		$this->_pricingHelper = $pricingHelper;
		$this->_quoteFactory = $quoteFactory;
		$this->_orderFactory = $orderFactory;
		$this->storeManager = $storeManager;
	}
	
	public function getRandomStrings($length)
	{
  		$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
 		return substr(str_shuffle($str_result),0, $length);
	}
	
	public function getReferralDiscountCode(){
		//$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		//$referralRuleId = $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/referral_rule", $storeScope);
		
		//print_r($referralRuleId); 
		
		$referralRuleId = $this->getReferralRuleId();
		$ruleModel = $this->_ruleFactory->create()->load($referralRuleId);
		
		//echo $ruleModel->getId();
		
		return $ruleModel->getPrimaryCoupon()->getCode();
	}
	
	public function getReferralRuleId(){
		return 558;
	}
	
	public function getReferralDiscountAmt(){
		//$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		//$referralRuleId = $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/referral_rule", $storeScope);
		$referralRuleId = $this->getReferralRuleId();
		$ruleModel = $this->_ruleFactory->create()->load($referralRuleId);
		
		$referralAmount = (int)$ruleModel->getDiscountAmount();
		$referralDiscountType = $ruleModel->getSimpleAction();
		if($referralDiscountType == "by_percent"){
			return $referralAmount . "%";
		}
		return $referralAmount;
	}

	public function getSenderEmail(){
		$storeId = $this->storeManager->getStore()->getId();
		$customerEmail = "noreply@torontovaporizer.ca";
		if($storeId == 3){
			$customerEmail = "noreply@tvape.com";
		}
		return $customerEmail;
	}
	
	public function getCommitionAmt(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$earnedDiscountAmount = (int)$this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/referral_earned", $storeScope);
		$earnedDiscountType = $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/discount_type", $storeScope);
		if($earnedDiscountType == "percentage"){
			return $earnedDiscountAmount . "%";
		}return $earnedDiscountAmount;
	}
	
	public function getReferralTitleString(){
		//$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		//$referralRuleId = $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/referral_rule", $storeScope);
		$referralRuleId = $this->getReferralRuleId();
		$ruleModel = $this->_ruleFactory->create()->load($referralRuleId);
		
		$string = "Give ";
		$referralAmount = (int)$ruleModel->getDiscountAmount();
		$referralDiscountType = $ruleModel->getSimpleAction();
		
		//$string .= $this->_pricingHelper->currency($referralAmount, false, false);
		$string .= $referralAmount;
		if($referralDiscountType == "by_percent"){
			$string .= "%";
		}
		
		$earnedDiscountAmount = (int)$this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/referral_earned", $storeScope);
		$earnedDiscountType = $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/discount_type", $storeScope);
		//$string .= ", Get " . $this->_pricingHelper->currency($earnedDiscountAmount, false, false);
		$string .= ", Get " . $earnedDiscountAmount;
		if($earnedDiscountType == "percentage"){
			$string .= "%";
		}
		return $string;
	}
	
	public function isModuleEnabled()
    {
		//return true;
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/active", $storeScope);
    }
	
	public function isFunctionOff()
    {
		//return true;
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/function_off", $storeScope);
    }
	
	public function getEarnedDiscountAmount(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/referral_earned", $storeScope);
	}
	
	public function getEarnedDiscountType(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/discount_type", $storeScope);
	}
	
	public function getReferralDiscountAmount($quoteId = null)
    {
		
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $discountType = $this->_scopeConfig->getValue("tvape_referralprogram/referralprogram_configuration/discount_type", $storeScope);
		
		//$quote = $this->_checkoutSession->getQuote();
		//$subtotal = $quote->getSubtotal();
		$subtotal = 0;
		
		$earnedDiscount = 0;
		if($this->getEarnedDiscountAmount() != ""){
			$earnedDiscount = $this->getEarnedDiscountAmount();
		}
		if($quoteId == null){
			//$quote = $this->_checkoutSession->getQuote();
			//$quoteId = $quote->getId();
			return 0;
		}
		$quote = $this->_quoteFactory->create()->load($quoteId);
		
		
		//$subtotal = $quote->getSubtotalWithDiscount();
		/*if($quote->getAwUseStoreCredit() == 1){
			$subtotal += $quote->getAwStoreCreditDiscount();
		}*/
		/*$subtotal = $quote->getSubtotal();
		$itemDiscount = 0;
		$cartItems = $quote->getAllVisibleItems();
		foreach ($cartItems as $item) {
			$itemDiscount += $item->getDiscountAmount();
		}
		$subtotal = $subtotal - $itemDiscount;*/
		
		/*$subtotal = $quote->getSubtotal();
		if($quote->getShippingAddress()){
			$subtotal += $quote->getShippingAddress()->getDiscountAmount();
		}*/
		
		//echo $earnedDiscount; exit;
		$redeemCollections = $this->_redeemFactory->create()->getCollection();
		$redeemCollections->addFieldToFilter('quote_id', $quoteId);
		$total_discount = 0;
		$referralDiscountFig = 0;
		foreach($redeemCollections as $redeemCollection){
			if($redeemCollection->getGiftcardPrice() != ''){
				$total_discount += $redeemCollection->getGiftcardPrice();
			}/*else{
				//$total_discount += $earnedDiscount;
				$referralDiscountFig += $earnedDiscount;
			}*/
		}
		/*if($discountType == "percentage"){
			$total_discount += (($subtotal * $referralDiscountFig)/100); 
		}else{
			$total_discount += $referralDiscountFig;
		}*/
		return $total_discount * -1;
		//$noOfRedeemCouponApplied = count($redeemCollection);
		//return $noOfRedeemCouponApplied * -10;
    }
	
	public function hasGiftDiscount($quoteId = null){
		if($quoteId == null){
			return false;
		}
		$redeemCollections = $this->_redeemFactory->create()->getCollection();
		$redeemCollections->addFieldToFilter('quote_id', $quoteId);
		foreach($redeemCollections as $redeemCollection){
			if($redeemCollection->getIsGiftcard() == 1){
				return true;
			}
		}
		return false;
	}

    /**
     * Get custom fee
     *
     * @return mixed
     */
    public function getLabel($quoteId = null)
    {
		if($this->hasGiftDiscount($quoteId)){
			return __("Other Discounts");
		}else{
			return __("Referral Discount");
		}
    }
	
	public function getMinimumOrderAmount(){
		return 0;
	}
	
	public function getOrderFromId(){
		//return $this->_orderFactory->create()->load($orderId);
		return  $this->_checkoutSession->getLastRealOrder();
	}
	
}