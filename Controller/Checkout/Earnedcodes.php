<?php
namespace Tvape\ReferralProgram\Controller\Checkout;

class Earnedcodes extends \Magento\Framework\App\Action\Action
{
	protected $_quoteFactory;
	protected $_earnedFactory;
	protected $_redeemFactory;
	protected $_checkoutSession;
	protected $_customerSession;
	protected $_storeManager;
	protected $_quotecouponFactory;
	protected $_giftcard;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quoteCouponFactory,
		\Tvape\Giftcard\Model\GiftcardFactory $giftcard
	){
		$this->_quoteFactory = $quoteFactory;
		$this->_earnedFactory = $earnedFactory;
		$this->_redeemFactory = $redeemFactory;
		$this->_checkoutSession = $checkoutSession;
		$this->_customerSession = $customerSession;
		$this->_storeManager = $storeManager;
		$this->_quotecouponFactory = $quoteCouponFactory;
		$this->_giftcard = $giftcard;
		return parent::__construct($context);
	}
	
	public function execute()
	{
		$this->checkEarnedCodes();
		$quote = $this->_checkoutSession->getQuote();
		$quoteId = $quote->getId();
		$appliedCodeArray = [];
		if ($this->_customerSession->isLoggedIn()) { 
			echo ''; exit;
		}
		$redeemCollection = $this->_redeemFactory->create()->getCollection();
		$redeemCollection->addFieldToFilter('quote_id', $quoteId);
		foreach($redeemCollection as $redeemModel){
			$appliedCodeArray[] = "<li><span>".$redeemModel->getRedeemCode()."</span> <a data-code='".$redeemModel->getRedeemCode()."' class='referral-remove-link'>".__("Remove")."</a></li>";
		}
		echo implode(",",$appliedCodeArray);
	}
	
	protected function checkEarnedCodes(){
		$quote = $this->_checkoutSession->getQuote();
		$params = $this->getRequest()->getParams();
		$quoteId = $quote->getId();
		$customerEmail = $quote->getCustomerEmail();
		$storeId = $this->_storeManager->getStore()->getId();
		
		if(isset($params['customer_email']) && $params['customer_email'] != ""){
			if($customerEmail != $params['customer_email']){
				try{
					$quote->setCouponCode('')->collectTotals()->save();
					$quoteCouponCollection = $this->_quotecouponFactory->create()->getCollection();
					$quoteCouponCollection->addFieldToFilter('quote_id', $quoteId);
					if(count($quoteCouponCollection)){
						$quoteCoupon = $quoteCouponCollection->getFirstItem();
						$quoteCoupon->delete();
					}
				}catch(Exception $e){
					
				}
			}
			$customerEmail = $params['customer_email'];
		}
		
		if (!$this->_customerSession->isLoggedIn()) { 
			$earnedCoupons = $this->_earnedFactory->create()->getCollection();
			$earnedCoupons->addFieldToFilter('customer_email', $customerEmail);
			$earnedCoupons->addFieldToFilter('store_id', $storeId);
			$earnedCoupons->addFieldToFilter('is_used', ['neq'=>1]);
			$availableCodes = [];
			foreach($earnedCoupons as $earnedCoupon){
				$availableCodes[] = $earnedCoupon->getEarnedCode();
			}
			$giftCardCollection = $this->_giftcard->create()->getCollection();
			$giftCardCollection->addFieldToFilter('customer_email', $customerEmail);
			$giftCardCollection->addFieldToFilter('store_id', $storeId);
			$giftCardCollection->addFieldToFilter('is_used', ['neq'=>1]);
			foreach($giftCardCollection as $giftcard){
				$availableCodes[] = $giftcard->getGiftCode();
			}
			
			$redeemCollection = $this->_redeemFactory->create()->getCollection();
			$redeemCollection->addFieldToFilter('quote_id', $quoteId);
			foreach($redeemCollection as $redeemModel){
				if(!in_array($redeemModel->getRedeemCode(), $availableCodes)){
					try{
						$redeemModel->delete();
					}catch(Exception $e){
						
					}
				}
			}
		}
	}
}