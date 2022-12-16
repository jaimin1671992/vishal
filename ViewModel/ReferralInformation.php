<?php 

namespace Tvape\ReferralProgram\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class ReferralInformation implements ArgumentInterface
{
	
	protected $_customerSession;
	protected $_earnedFactory;
	protected $_redeemFactory;
	protected $_checkoutSession;
	protected $_helper;
	
	public function __construct(
		\Magento\Customer\Model\Session $customerSession,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Tvape\ReferralProgram\Helper\Data $helper
	)
    {
		$this->_customerSession = $customerSession;
		$this->_earnedFactory = $earnedFactory;
		$this->_redeemFactory = $redeemFactory;
		$this->_checkoutSession = $checkoutSession;
		$this->_helper = $helper;
	}
	
	public function getEarnedCoupons(){
		$customerId = $this->_customerSession->getCustomer()->getId();
		$earnedCouponCollection = $this->_earnedFactory->create()->getCollection();
		$earnedCouponCollection->addFieldToFilter('referrer_id', $customerId);
		$earnedCouponCollection->addFieldToFilter('is_used', array('neq'=>1));
		$earnedCouponCollection->addFieldToFilter('earned_code', array('neq'=>""));
		return $earnedCouponCollection;
	}
	
	public function canShowCoupons(){
		if($this->_customerSession->isLoggedIn() && $this->_helper->isModuleEnabled() && !$this->_helper->isFunctionOff()){
			 return true;
		}else{
			return false;
		}
	}
	
	public function getAppliedCodes(){
		$appliedCodes = [];
		$quote = $this->_checkoutSession->getQuote();
		$quoteId = $quote->getId();
		$redeemCollection = $this->_redeemFactory->create()->getCollection();
		$redeemCollection->addFieldToFilter('quote_id', $quoteId);
		if(count($redeemCollection)){
			foreach($redeemCollection as $redeemCode){
				$appliedCodes[] = $redeemCode->getRedeemCode();
			}
		}
		return $appliedCodes;
	}
}