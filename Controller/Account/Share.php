<?php
namespace Tvape\ReferralProgram\Controller\Account;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Share extends \Magento\Customer\Controller\AbstractAccount
{
    protected $resultPageFactory;
	protected $_referral;
	protected $_customerSession;
	protected $_storeManager;
	protected $_referralHelper;
	protected $_resultFactory;
	protected $_messageManager;
	protected $_emailHelper;
    
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referral,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Tvape\ReferralProgram\Helper\Data $referralHelper,
		ResultFactory $resultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Tvape\ReferralProgram\Helper\Email $emailHelper
    ) {
        $this->resultPageFactory    = $resultPageFactory;
		$this->_customerSession = $customerSession;
		$this->_referral = $referral;
		$this->_storeManager = $storeManager;
		$this->_referralHelper = $referralHelper;
		$this->_resultFactory = $resultFactory;
		$this->_messageManager = $messageManager;
		$this->_emailHelper = $emailHelper;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $customerId = $this->_customerSession->getCustomer()->getId();
		$customerEmail = $this->_customerSession->getCustomer()->getEmail();
		$storeId = $this->_storeManager->getStore()->getId();
		$emailIds = $this->getRequest()->getParam('email_ids');
		
		
		$success = false;
		$iCounter = 0;
		if($emailIds != ""){
			$emailAddressArray = explode(',', $emailIds);
			$emailAddressArray = array_unique($emailAddressArray);
			if (($key = array_search($customerEmail, $emailAddressArray)) !== false) {
				unset($emailAddressArray[$key]);
			}
			if(count($emailAddressArray)){
				$referralCollection = $this->_referral->create()->getCollection();
				$referralCollection->addFieldToFilter('customer_id', $customerId);
				if(count($referralCollection)){
					$referralObject = $referralCollection->getFirstItem();
					if($referralObject->getCodeUsed() < 5){
						foreach($emailAddressArray as $email){
							$iCounter++;
							if($iCounter > 5){ break; }
							if(!$this->_emailHelper->sendEmail($email, $customerId)){
								//$this->_messageManager->addError(__("Something went wrong. Please try again."));
								//break;
							}
						}
						$success = true;
						$this->_messageManager->addSuccess(__("Your coupon shared to the email addresses."));
						
					}else{
							$this->_messageManager->addError(__("Referral code use limit reached."));
					}
				}else{
					$this->_messageManager->addError(__("No referral found."));
				}
			}
		}
		if($success == false){
			//$this->_messageManager->addError(__("No email address found."));
		}
		/*$resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;*/
		$this->_redirect('referralprogram/account/referral');
    }
}