<?php 

namespace Tvape\ReferralProgram\Plugin\Block\Checkout;

class Coupon 
{
	
	protected $_customerSession;
	protected $_quoteCoupon;
	protected $_checkoutSession;
	
	public function __construct(
		\Magento\Customer\Model\Session $customerSession,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quoteCoupon,
		\Magento\Checkout\Model\Session $checkoutSession
	){
		$this->_customerSession = $customerSession;
		$this->_quoteCoupon = $quoteCoupon;
		$this->_checkoutSession = $checkoutSession;
	}
	
	
	public function afterGetCouponCode($subject, $result){
		if ($this->_customerSession->isLoggedIn()) {
			$customerId = $this->_customerSession->getCustomer()->getId();
			$quoteId = (int)$this->_checkoutSession->getQuote()->getId();
			$quoteCouponCollection = $this->_quoteCoupon->create()->getCollection();
			$quoteCouponCollection->addFieldToFilter('quote_id', $quoteId);
			if(count($quoteCouponCollection)){
				$quoteCoupon = $quoteCouponCollection->getFirstItem();
				if($result != "")
				return $quoteCoupon->getReferralCode();
			}
		}
		return $result;
	}
}