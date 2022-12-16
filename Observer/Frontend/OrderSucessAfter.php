<?php 

namespace Tvape\ReferralProgram\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

class OrderSucessAfter implements \Magento\Framework\Event\ObserverInterface
{
	protected $_usedFactory;
	protected $_quotecouponFactory;
	protected $_referralFactory;
	protected $_timezoneInterface;
	protected $_earnedFactory;
	protected $_redeemFactory;
	protected $giftcard;
	protected $_referralEmailHelper;
	protected $_referralProgramHelper;
	
	public function __construct(
		\Tvape\ReferralProgram\Model\UsedFactory $usedFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quotecouponFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Tvape\Giftcard\Model\GiftcardFactory $giftcard,
		\Tvape\ReferralProgram\Helper\Email $referralEmailHelper,
		\Tvape\ReferralProgram\Helper\Data $referralProgramHelper
	){
		$this->_usedFactory = $usedFactory;
		$this->_quotecouponFactory = $quotecouponFactory;
		$this->_referralFactory = $referralFactory;
		$this->_timezoneInterface = $timezoneInterface;
		$this->_earnedFactory = $earnedFactory;
		$this->_redeemFactory = $redeemFactory;
		$this->_giftcard = $giftcard;
		$this->_referralEmailHelper = $referralEmailHelper;
		$this->_referralProgramHelper = $referralProgramHelper;
	}
	
	
	public function execute(Observer $observer)
    {
		if(!$this->_referralProgramHelper->isModuleEnabled()){
			return;
		}
		
		$order = $observer->getEvent()->getOrder();
		$couponCode = $order->getCouponCode();
		$customerEmail = $order->getCustomerEmail();
		$customerId = $order->getCustomerId();
		$storeId = $order->getStoreId();
		$quoteId = $order->getQuoteId();
		
		$writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/__logfile.log');
		////$logger = new \Laminas\Log\Logger();
		//$logger->addWriter($writer);

		//$logger->info('SUCCESS AFTER FILE');
		//$logger->info('CUSTOMER ID' . $customerId);
		
		if($customerId && $customerId != "" && $customerId > 0){
			$this->_referralEmailHelper->sendReferralWelcomeEmail($customerId, $order->getIncrementId());
			//$logger->info('REFERRAL EMAIL TRIGGERED');

			

			// End - Giftcard

			if($couponCode == "referral_discount_10x"){
				
				//$logger->info('INSIDE DISCOUNT');
				
				$customerId = $order->getCustomerId();
				$quotecouponCollection = $this->_quotecouponFactory->create()->getCollection();
				$quotecouponCollection->addFieldToFilter('quote_id', $quoteId);
				if(count($quotecouponCollection)){
					$quotecouponModel = $quotecouponCollection->getFirstItem();
					$referralCode = $quotecouponModel->getReferralCode();
					
					//$logger->info('ReferralCode:' . $referralCode);
					
					$referralCollection = $this->_referralFactory->create()->getCollection();
					$referralCollection->addFieldToFilter('referral_code', $referralCode);
					if(count($referralCollection)){
						//$logger->info('CODE FOUND FROM QUOTE');
						$referralModel = $referralCollection->getFirstItem();
						$referrerId = $referralModel->getCustomerId();
						$usedCount = $referralModel->getCodeUsed();
						$usedCount = $usedCount + 1;
						$referralModel->setCodeUsed($usedCount);
						
						$dateTime = $this->_timezoneInterface->date()->format('Y-m-d H:i:s');
						
						$usedReferralFactory = $this->_usedFactory->create();
						$usedReferralFactory->setStoreId($storeId);
						$usedReferralFactory->setCustomerId($customerId);
						$usedReferralFactory->setReferrerId($referrerId);
						$usedReferralFactory->setOrderId($order->getId());
						$usedReferralFactory->setReferralCode($referralCode);
						$usedReferralFactory->setCustomerEmail($customerEmail);
						$usedReferralFactory->setUsedDate($dateTime);
						try{
							$usedReferralFactory->save();
							$referralModel->save();
						}catch(Exception $e){
							
						}
						
						$subtotal = $order->getSubtotal() + $order->getDiscountAmount();
						$commAmt = $this->_referralProgramHelper->getEarnedDiscountAmount();
						$commType = $this->_referralProgramHelper->getEarnedDiscountType();
						if($commType == "percentage"){
							$commission = ($subtotal * $commAmt)/100;
						}else{
							$commission = $commAmt;
						}
						
						$expairaryDate = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($dateTime)) );
						$earnedModel = $this->_earnedFactory->create();
						$earnedModel->setStoreId($storeId);
						$earnedModel->setCustomerId($customerId);
						$earnedModel->setReferrerId($referrerId);
						$earnedModel->setOrderId($order->getId());
						$earnedModel->setCustomerEmail($referralModel->getCustomerEmail());
						$earnedModel->setCommission($commission);
						//$earnedModel->setEarnedCode(__("Will available when order comples."));
						$earnedModel->setExpairaryDate($expairaryDate);
						try{
							$earnedModel->save();
						}catch(Exception $e){
							
						}
					}
				}
			}

			if($order->getReferralDiscount() < 0){
				$quoteId = $order->getQuoteId();
				$customerId = $order->getCustomerId();
				$redeemCoupons = $this->_redeemFactory->create()->getCollection();
				$redeemCoupons->addFieldToFilter('quote_id', $quoteId);
				$usedCoupons = [];
				foreach($redeemCoupons as $redeemCouponObject){
					$usedCoupons[] = $redeemCouponObject->getRedeemCode();
				}
				$earnedCoupons = $this->_earnedFactory->create()->getCollection();
				$earnedCoupons->addFieldToFilter('earned_code', array('in'=>$usedCoupons));
				foreach($earnedCoupons as $earnedCouponObject){
					$earnedCouponObject->setIsUsed(1);
					$earnedCouponObject->setUsedOrderId($order->getId());
					$this->_referralEmailHelper->sendCouponUsedEmail($order->getId());
					try{
						$earnedCouponObject->save();
					}catch(Exception $e){
						
					}
				}
				$dateTime = $this->_timezoneInterface->date()->format('Y-m-d');
				$giftcardCollections = $this->_giftcard->create()->getCollection();
				$giftcardCollections->addFieldToFilter('gift_code', array('in'=>$usedCoupons));
				foreach($giftcardCollections as $giftcardCollection){
					$giftcardCollection->setIsUsed(1);
					$giftcardCollection->setUsedOrderId($order->getId());
					$giftcardCollection->setUsedDate($dateTime);
					try{
						$giftcardCollection->save();
					}catch(Exception $e){
						
					}
				}
					
				$redeemCoupons = $this->_redeemFactory->create()->getCollection();
				$redeemCoupons->addFieldToFilter('redeem_code', ['in' => $usedCoupons]);
				$redeemCoupons->addFieldToFilter('quote_id', ["neq" => $quoteId]);
				try{
					foreach($redeemCoupons as $redeemObject){
						$redeemObject->delete();
					}
				}catch(Exception $e){
					
				}
			}
		}
	}
}