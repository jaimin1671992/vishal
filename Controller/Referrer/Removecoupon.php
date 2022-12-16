<?php
namespace Tvape\ReferralProgram\Controller\Referrer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;

class Removecoupon extends \Magento\Framework\App\Action\Action
{
	
	protected $_checkoutSession;
	protected $_redeemFactory;
	protected $_earnedFactory;
	protected $_customerSession;
	protected $_resultFactory;
	protected $_messageManager;
	
	public function __construct(
		Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager
	){
		$this->_checkoutSession = $checkoutSession;
		$this->_redeemFactory = $redeemFactory;
		$this->_earnedFactory = $earnedFactory;
		$this->_customerSession = $customerSession;
		$this->_resultFactory = $resultFactory;
		$this->_messageManager = $messageManager;
		parent::__construct($context);
	}
	
	public function execute()
	{
		$params = $this->getRequest()->getParams();
		if(isset($params['redeem_code']) && $params['redeem_code'] != ""){
			$redeemCode = $params['redeem_code'];
			$quote = $this->_checkoutSession->getQuote();
			$quoteId = $quote->getId();
			$customerId = 0;
			if ($this->_customerSession->isLoggedIn()) {
				$customerId = $this->_customerSession->getCustomer()->getId();
			}
			foreach ($this->_messageManager->getMessages()->getItems() as $message) {
				$id = bin2hex(random_bytes(10));
				$message->setIdentifier($id);
				$this->_messageManager->getMessages()->deleteMessageByIdentifier($id);
			}
			$redeemCollection = $this->_redeemFactory->create()->getCollection();
			$redeemCollection->addFieldToFilter('redeem_code', $redeemCode);
			if(count($redeemCollection)){
					$redeemCodeObject = $redeemCollection->getFirstItem();
				try{
					$redeemCodeObject->delete();
					$this->messageManager->addSuccess(__("Commission code is removed."));
				}catch(Exception $e){
					$this->_messageManager->addError(__("Something went wrong. Please try again."));
				}
			}
		}else{
			$this->_messageManager->addError(__("Something went wrong. Please try again."));
		}
		$resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;
	}
}