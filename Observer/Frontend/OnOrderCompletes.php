<?php

namespace Tvape\ReferralProgram\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

class OnOrderCompletes implements \Magento\Framework\Event\ObserverInterface
{
	protected $_usedFactory;
	protected $_quotecouponFactory;
	protected $_referralFactory;
	protected $_referralEarned;
	protected $_timezoneInterface;
	protected $_referralHelper;
	protected $_emailHelper;
	
	public function __construct(
		\Tvape\ReferralProgram\Model\UsedFactory $usedFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quotecouponFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $referralEarned,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
		\Tvape\ReferralProgram\Helper\Data $referralHelper,
		\Tvape\ReferralProgram\Helper\Email $emailHelper
	){
		$this->_usedFactory = $usedFactory;
		$this->_quotecouponFactory = $quotecouponFactory;
		$this->_referralFactory = $referralFactory;
		$this->_referralEarned = $referralEarned;
		$this->_timezoneInterface = $timezoneInterface;
		$this->_referralHelper = $referralHelper;
		$this->_emailHelper = $emailHelper;
	}
	
	public function execute(Observer $observer)
    {
		$order = $observer->getEvent()->getOrder();
		//$order   = $invoice->getorder();
		if($order->getState() == 'complete') {
			$customerId = $order->getCustomerId();
			$referralEarnedCollection = $this->_referralEarned->create()->getCollection();
			$referralEarnedCollection->addFieldToFilter('order_id', $order->getId());
			$earnedCode = $this->_referralHelper->getRandomStrings(5) . $customerId ."_" . $this->_referralHelper->getRandomStrings(4);
			if(count($referralEarnedCollection)){
				$referralEarnedModel = $referralEarnedCollection->getFirstItem();
				if($referralEarnedModel->getEarnedCode() == ""){
					$referralEarnedModel->setEarnedCode($earnedCode);
					try{
						$referralEarnedModel->save();
						$this->_emailHelper->sendEarnedCouponEmail($order->getId());
					}catch(Exception $e){
						
					}
				}
			}
		}
	}
}