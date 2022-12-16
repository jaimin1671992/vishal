<?php

namespace Tvape\ReferralProgram\Block;

class Referral extends \Magento\Framework\View\Element\Template
{
	
	protected $_referral;
	protected $_customerSession;
	protected $_referralObject;
	protected $_earnedFactory;
	protected $_orderRepository;
    protected $_storeManager;
	
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Tvape\ReferralProgram\Model\ReferralFactory $referral,
		\Magento\Customer\Model\Session $customerSession,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
	)
	{
		parent::__construct($context);
		$this->_referral = $referral;
		$this->_customerSession = $customerSession;
		$this->_earnedFactory = $earnedFactory;
		$this->_orderRepository = $orderRepository;
        $this->_storeManager = $storeManager;
	}
	
	public function getCustomerEmail(){
		return $this->_customerSession->getCustomer()->getEmail();
	}
	
	public function isEnrolled(){
		$this->_referralObject = null;
		$customerId = $this->_customerSession->getCustomer()->getId();
		$referralCollection = $this->_referral->create()->getCollection();
		$referralCollection->addFieldToFilter('customer_id', $customerId);
		if(sizeof($referralCollection) > 0){
			$referralModel = $referralCollection->getFirstItem();
			$this->_referralObject = $referralModel;
		}
		
		
		if($this->_referralObject && $this->_referralObject->getId()){
			return true;
		}
		return false;
	}
	
	
    public function getReferralCode(){
		if($this->_referralObject && $this->_referralObject->getId()){
			return $this->_referralObject->getReferralCode();
		}
		return "";
	}
	
	public function getUsed(){
		if($this->_referralObject && $this->_referralObject->getId()){
			return $this->_referralObject->getCodeUsed();
		}
		return 0;
	}
	
	public function getEarnedCoupons(){
		$customerId = $this->_customerSession->getCustomer()->getId();
		$earnedCouponCollections = $this->_earnedFactory->create()->getCollection();
		$earnedCouponCollections->addFieldToFilter('referrer_id', $customerId);
		return $earnedCouponCollections;
	}
	
	public function getOrderById($orderId){
		return $this->_orderRepository->get($orderId);
	}

    public function getStoreUrl(){
        return $this->_storeManager->getStore()->getBaseUrl();
    }

}