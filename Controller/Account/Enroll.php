<?php
namespace Tvape\ReferralProgram\Controller\Account;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Enroll extends \Magento\Customer\Controller\AbstractAccount
{
    protected $resultPageFactory;
	protected $_referral;
	protected $_customerSession;
	protected $_storeManager;
	protected $_referralHelper;
	protected $_resultFactory;
	protected $_messageManager;
	protected $_orderCollectionFactory;
    
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referral,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Tvape\ReferralProgram\Helper\Data $referralHelper,
		ResultFactory $resultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->resultPageFactory    = $resultPageFactory;
		$this->_customerSession = $customerSession;
		$this->_referral = $referral;
		$this->_storeManager = $storeManager;
		$this->_referralHelper = $referralHelper;
		$this->_resultFactory = $resultFactory;
		$this->_messageManager = $messageManager;
		$this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $customerId = $this->_customerSession->getCustomer()->getId();
		$customerEmail = $this->_customerSession->getCustomer()->getEmail();
		
		/*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$emailHelper = $objectManager->create('\Tvape\ReferralProgram\Helper\Email');
		$emailHelper->sendReferralWelcomeEmail($customerId);
		exit;*/
		
		$storeId = $this->_storeManager->getStore()->getId();
		
		$collection = $this->_orderCollectionFactory->create();
		$collection->addFieldToFilter('customer_id', $customerId);
		//$collection->addFieldToFilter('status', 'complete');
		
		if(count($collection) && count($collection) > 0){
			$random = $this->_referralHelper->getRandomStrings(5);
			$referralCode = "referral_". $customerId . "_" . $random;
			$referralData = [];
			$referralData['store_id'] = $storeId;
			$referralData['customer_id'] = $customerId;
			$referralData['customer_email'] = $customerEmail;
			$referralData['referral_code'] = $referralCode;
			$referral = $this->_referral->create();
			$referral->setData($referralData);
			try{
				$referral->save();
				$this->_messageManager->addSuccess(__("You are enrolled in Referral Program"));
			}catch(Exception $e){
				$this->_messageManager->addError(__("Something went wrong. Please try again."));
			}
		}else{
			$this->_messageManager->addError(__("You must place order first."));
		}
		$resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;
    }
	
	
}