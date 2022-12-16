<?php
namespace Tvape\ReferralProgram\Controller\Checkout;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Share extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
	protected $_referral;
	protected $_customerSession;
	protected $_storeManager;
	protected $_referralHelper;
	protected $_resultFactory;
	protected $_messageManager;
	protected $_emailHelper;
	protected $_orderFactory;
    
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referral,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Tvape\ReferralProgram\Helper\Data $referralHelper,
		ResultFactory $resultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Tvape\ReferralProgram\Helper\Email $emailHelper,
		\Magento\Sales\Model\OrderFactory $orderFactory
    ) {
        $this->resultPageFactory    = $resultPageFactory;
		$this->_customerSession = $customerSession;
		$this->_referral = $referral;
		$this->_storeManager = $storeManager;
		$this->_referralHelper = $referralHelper;
		$this->_resultFactory = $resultFactory;
		$this->_messageManager = $messageManager;
		$this->_emailHelper = $emailHelper;
		$this->_orderFactory = $orderFactory;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        //$customerId = $this->_customerSession->getCustomer()->getId();
		//$customerEmail = $this->_customerSession->getCustomer()->getEmail();
		
		
		
		$storeId = $this->_storeManager->getStore()->getId();
		$emailIds = $this->getRequest()->getParam('email_ids');
		$orderId = $this->getRequest()->getParam('order_id');
		$order = $this->_orderFactory->create()->load($orderId);
		
		$success = false;
		$iCounter = 0;
		if($emailIds != "" && $order){
			$customerEmail = $order->getCustomerEmail();
			
			$emailAddressArray = explode(',', $emailIds);
			$emailAddressArray = array_unique($emailAddressArray);
			if (($key = array_search($customerEmail, $emailAddressArray)) !== false) {
				unset($emailAddressArray[$key]);
			}
			if(count($emailAddressArray)){
				$referralCollection = $this->_referral->create()->getCollection();
				$referralCollection->addFieldToFilter('customer_email', $customerEmail);
				$referralCollection->addFieldToFilter('store_id', $storeId);
				if(count($referralCollection)){
					$referralObject = $referralCollection->getFirstItem();
					//if($referralObject->getCodeUsed() < 5){
						foreach($emailAddressArray as $email){
							$iCounter++;
							//if($iCounter > 5){ break; }
							if(!$this->_emailHelper->sendEmail($email, 0, $customerEmail, $orderId)){
								//$this->_messageManager->addError(__("Something went wrong. Please try again."));
								//break;
							}
						}
						$success = true;
						//$this->_messageManager->addSuccess(__("Your coupon shared to the email addresses."));
						echo __("Your coupon shared to the email addresses."); exit; 
					/*}else{
							//$this->_messageManager->addError(__("Referral code use limit reached."));
					}*/
				}else{
					//$this->_messageManager->addError(__("No referral found."));
					echo __("No referral found.");
					exit;
				}
			}
		}
		if($success == false){
			//$this->_messageManager->addError(__("No email address found."));
		}
		/*$resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;*/
		//$this->_redirect('referralprogram/account/referral');
    }
}