<?php

namespace Tvape\ReferralProgram\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

class InvoiceSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
	protected $_usedFactory;
	protected $_quotecouponFactory;
	protected $_referralFactory;
	protected $_referralEarned;
	protected $_timezoneInterface;
	protected $_referralHelper;
	
	public function __construct(
		\Tvape\ReferralProgram\Model\UsedFactory $usedFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quotecouponFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $referralEarned,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
		\Tvape\ReferralProgram\Helper\Data $referralHelper
	){
		$this->_usedFactory = $usedFactory;
		$this->_quotecouponFactory = $quotecouponFactory;
		$this->_referralFactory = $referralFactory;
		$this->_referralEarned = $referralEarned;
		$this->_timezoneInterface = $timezoneInterface;
		$this->_referralHelper = $referralHelper;
	}
	
	public function execute(Observer $observer)
    {
		
		if(!$this->_referralHelper->isModuleEnabled()){
			return;
		}
		
		$invoice = $observer->getEvent()->getInvoice();
		$order   = $invoice->getorder();
		$couponCode = $order->getCouponCode();
		$customerEmail = $order->getCustomerEmail();
		$storeId = $order->getStoreId();
		$quoteId = $order->getQuoteId();
		$customerId = $order->getCustomerId();
		if($couponCode == "referral_discount_10x"){
			$quoteCouponCollection = $this->_quotecouponFactory->create()->getCollection();
			$quoteCouponCollection->addFieldToFilter('quote_id', $quoteId);
			if(count($quoteCouponCollection)){
				$quoteCoupon = $quoteCouponCollection->getFirstItem();
				$referralCoupon = $quoteCoupon->getReferralCode();
				$referralCollection = $this->_referralFactory->create()->getCollection();
				$referralCollection->addFieldToFilter("referral_code", $referralCoupon);
				if(count($referralCollection)){
					$referralModel = $referralCollection->getFirstItem();
					$referrerId = $referralModel->getCustomerId();
					$earnedCode = $this->_referralHelper->getRandomStrings(5) . $customerId ."_" . $this->_referralHelper->getRandomStrings(4);
					$dateTime = $this->_timezoneInterface->date()->format('Y-m-d H:i:s');
					$expairaryDate = date('Y-m-d H:i:s', strtotime('+1 year', strtotime($dateTime)) );
					$earnedModel = $this->_referralEarned->create();
					$earnedModel->setStoreId($storeId);
					$earnedModel->setCustomerId($customerId);
					$earnedModel->setReferrerId($referrerId);
					$earnedModel->setOrderId($order->getId());
					$earnedModel->setEarnedCode($earnedCode);
					$earnedModel->setCustomerEmail($customerEmail);
					$earnedModel->setExpairaryDate($expairaryDate);
					try{
						$earnedModel->save();
					}catch(Exception $e){
						
					}
				}
			}
		}
	}
}