<?php
namespace Tvape\ReferralProgram\Controller\Checkout;

class Earnedremove extends \Magento\Framework\App\Action\Action
{
	protected $_quoteFactory;
	protected $_earnedFactory;
	protected $_redeemFactory;
	protected $_checkoutSession;
	protected $_customerSession;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Customer\Model\Session $customerSession
	){
		$this->_quoteFactory = $quoteFactory;
		$this->_earnedFactory = $earnedFactory;
		$this->_redeemFactory = $redeemFactory;
		$this->_checkoutSession = $checkoutSession;
		$this->_customerSession = $customerSession;
		return parent::__construct($context);
	}
	
	public function execute()
	{
		$params = $this->getRequest()->getParams();
		$code = "";
		if(isset($params['coupon_code']) && $params['coupon_code'] != ""){
			$code = $params['coupon_code'];
		}
		
		$quote = $this->_checkoutSession->getQuote();
		$quoteId = $quote->getId();
		$appliedCodeArray = [];
		$redeemCollection = $this->_redeemFactory->create()->getCollection();
		$redeemCollection->addFieldToFilter('quote_id', $quoteId);
		foreach($redeemCollection as $redeemModel){
			if($code == $redeemModel->getRedeemCode()){
				try{
					$redeemModel->delete();
					$quote->collectTotals()->save();
				}catch(Exception $e){
					
				}
				continue;
			}
			$appliedCodeArray[] = "<li>".$redeemModel->getRedeemCode()." <a data-code='".$redeemModel->getRedeemCode()."' class='referral-remove-link'>".__("Remove")."</a></li>";
		}
		echo implode(",",$appliedCodeArray);
	}
}