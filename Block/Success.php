<?php

namespace Tvape\ReferralProgram\Block;

class Success extends \Magento\Framework\View\Element\Template
{
	protected $_checkoutSession;
	protected $_referral;
	protected $_customerSession;
	protected $_referralHelper;
    protected $_storeManager;


    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Tvape\ReferralProgram\Model\ReferralFactory $referral,
		\Magento\Customer\Model\Session $customerSession,
		\Tvape\ReferralProgram\Helper\Data $referralHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
		parent::__construct($context);
		$this->_checkoutSession = $checkoutSession;
		$this->_referral = $referral;
		$this->_customerSession = $customerSession;
		$this->_referralHelper = $referralHelper;
        $this->_storeManager = $storeManager;
    }
	
	public function getOrderId(){
		$order = $this->_checkoutSession->getLastRealOrder();
		return $order->getId();
	}
	
	public function getCustomerId(){
		$order = $this->_checkoutSession->getLastRealOrder();
		return $order->getCustomerId();
	}
	
	public function getOrderIncrementId(){
		$order = $this->_checkoutSession->getLastRealOrder();
		return $order->getIncrementId();
	}
	
	public function getReferralCode(){
		$referralCollection = $this->_referral->create()->getCollection();
		/*$customerId = $this->getCustomerId();
		if($customerId > 0){
			$referralCollection->addFieldToFilter('customer_id', $customerId);
		}else{
			$order = $this->_checkoutSession->getLastRealOrder();
			$storeId = $order->getStoreId();
			$email = $order->getCustomerEmail();
			$referralCollection->addFieldToFilter('store_id', $storeId);
			$referralCollection->addFieldToFilter('customer_email', $email);
		}*/

		$email = $this->getCustomerEmail();
		$storeId = $this->_storeManager->getStore()->getId();

		$referralCollection->addFieldToFilter('store_id', $storeId);
		$referralCollection->addFieldToFilter('customer_email', $email);

		if(count($referralCollection)){
			$referralModel = $referralCollection->getFirstItem();
			if($referralModel->getCodeUsed() == 0){
				return $referralModel->getReferralCode();
			}
		}
		return false;
	}

	public function getCustomerEmail(){
		$customer = $this->_customerSession->getCustomer();
		$email = '';
		if($customer->getId()){
			$email = $customer->getEmail();
		}else{
			$order = $this->_checkoutSession->getLastRealOrder();
			if($order){
				$email = $order->getCustomerEmail();
			}
		}
		return $email;
	}
	
	public function getUsed(){
		$email = $this->getCustomerEmail();
		$referralCol = $this->_referral->create()->getCollection();
		$referralCol->addFieldToFilter('customer_email', $email);
		if(sizeof($referralCol) > 0){
			$referralModel = $referralCol->getFirstItem();
			return $referralModel->getCodeUsed();
		}
		return 0;
	}

    public function getStoreUrl(){
        return $this->_storeManager->getStore()->getBaseUrl();
    }

}