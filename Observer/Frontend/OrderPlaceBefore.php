<?php 

namespace Tvape\ReferralProgram\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

class OrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface
{
	
	protected $_usedFactory;
	protected $_quotecouponFactory;
	protected $_referralFactory;
	protected $_redeemFactory;
	protected $_earnedFactory;
	protected $_referralProgramHelper;
	protected $_giftcard;

	public function __construct(
		\Tvape\ReferralProgram\Model\UsedFactory $usedFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quotecouponFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Tvape\ReferralProgram\Helper\Data $referralProgramHelper,
		\Tvape\Giftcard\Model\GiftcardFactory $giftcard
	){
		$this->_usedFactory = $usedFactory;
		$this->_quotecouponFactory = $quotecouponFactory;
		$this->_referralFactory = $referralFactory;
		$this->_redeemFactory = $redeemFactory;
		$this->_earnedFactory = $earnedFactory;
		$this->_referralProgramHelper = $referralProgramHelper;
		$this->_giftcard = $giftcard;
	}
	
	public function execute(Observer $observer)
    {
		if(!$this->_referralProgramHelper->isModuleEnabled()){
			return;
		}
		
		/*$writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/__logfile.log');
		$logger = new \Laminas\Log\Logger();
		$logger->addWriter($writer);

		$logger->info('BEFORE FILE');*/
		
		
		$order = $observer->getEvent()->getOrder();
		//echo $order->getId(); exit;
		$couponCode = $order->getCouponCode();
		$customerEmail = $order->getCustomerEmail();
		$storeId = $order->getStoreId();
		$quoteId = $order->getQuoteId();
		if($couponCode == "referral_discount_10x"){
			//$customerId = $order->getCustomerId();
			$usedReferralCollection = $this->_usedFactory->create()->getCollection();
			$usedReferralCollection->addFieldToFilter('customer_email', $customerEmail);
			$usedReferralCollection->addFieldToFilter('store_id', $storeId);
			//$usedReferralCollection->addFieldToFilter('customer_id', $customerId);
			if(count($usedReferralCollection)){ 
				throw new LocalizedException(__("Referral code invalid")); 
			}else{
				$quotecouponCollection = $this->_quotecouponFactory->create()->getCollection();
				$quotecouponCollection->addFieldToFilter('quote_id', $quoteId);
				if(count($quotecouponCollection)){
					$quotecouponModel = $quotecouponCollection->getFirstItem();
					$referralCode = $quotecouponModel->getReferralCode();
					
					$referralCollection = $this->_referralFactory->create()->getCollection();
					$referralCollection->addFieldToFilter('referral_code', $referralCode);
					if(count($referralCollection)){
						$referralModel = $referralCollection->getFirstItem();
						if($referralModel->getCodeUsed() >= 5){
							throw new LocalizedException(__("Referral code invalid"));
						}
					}
				}
			}
		}
		//$writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/logfile.log');
		//$logger = new \Laminas\Log\Logger();
		//$logger->addWriter($writer);

		//$logger->info('BEFORE FILE');
		if($order->getReferralDiscount() < 0){
			
			//$logger->info('INSIDE BEFORE');
			
			$quoteId = $order->getQuoteId();
			
			$customerId = $order->getCustomerId();
			
			$customerEmail = $order->getCustomerEmail();
			$storeId = $order->getStoreId();
			
			$availableCoupons = [];
			$earnedCoupons = $this->_earnedFactory->create()->getCollection();
			if($customerId && $customerId != "" && $customerId > 0){
				$earnedCoupons->addFieldToFilter('referrer_id', $customerId);
			}else{
				$earnedCoupons->addFieldToFilter('customer_email', $customerEmail);
				$earnedCoupons->addFieldToFilter('store_id', $storeId);
			}
			$earnedCoupons->addFieldToFilter('is_used', array('neq'=>1));
			foreach($earnedCoupons as $earnedCouponObject){
				$availableCoupons[] = $earnedCouponObject->getEarnedCode();
			}
			
			$giftCardCollection = $this->_giftcard->create()->getCollection();
			if($customerId && $customerId != "" && $customerId > 0){
				$giftCardCollection->addFieldToFilter('customer_id', $customerId);
			}else{
				$giftCardCollection->addFieldToFilter('customer_email', $customerEmail);
				$giftCardCollection->addFieldToFilter('store_id', $storeId);
			}
			$giftCardCollection->addFieldToFilter('is_used', ['neq'=>1]);
			foreach($giftCardCollection as $giftcardObject){
				$availableCoupons[] = $giftcardObject->getGiftCode(); 
			}

			$error = false;
			$errorCoupons = [];
			$redeemCoupons = $this->_redeemFactory->create()->getCollection();
			$redeemCoupons->addFieldToFilter('quote_id', $quoteId);
			//$logger->info("COUNT " .count($redeemCoupons));
			foreach($redeemCoupons as $redeemCouponObject){
				$couponCode = $redeemCouponObject->getRedeemCode();
				if(!in_array($couponCode, $availableCoupons)){
					$error = true;
					$errorCoupons[] = $couponCode;
				}
			}
			if($error == true){
				throw new LocalizedException(__("Referral redeem code invalid %1", implode(', ',$errorCoupons)));
			}
		}
	}
}