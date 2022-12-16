<?php

namespace Tvape\ReferralProgram\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class Removeblock implements \Magento\Framework\Event\ObserverInterface
{
	
	protected $_customerSession;
	protected $_orderCollectionFactory;
	protected $_referralHelper;
	
	public function __construct(
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Tvape\ReferralProgram\Helper\Data $referralHelper
	){
		$this->_customerSession = $customerSession;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->_referralHelper = $referralHelper;
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $layout = $observer->getLayout();

        $isVisible = 0; // write your conditional logic here
		if ($this->_customerSession->isLoggedIn()) {
			$customerId = $this->_customerSession->getCustomer()->getId();
			$collection = $this->_orderCollectionFactory->create();
			$collection->addFieldToFilter('customer_id', $customerId);
			if(count($collection) && count($collection) > 0){
				$isVisible = 1;
			}
		}
		
		$functionOff = $this->_referralHelper->isFunctionOff();

        if($isVisible == 0 && !$functionOff){
            $layout->unsetElement('tvape_referral_program');
        }
    }
}