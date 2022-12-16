<?php
namespace Tvape\ReferralProgram\Controller\Referrer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;

class Addcoupon extends \Magento\Framework\App\Action\Action
{
	
	protected $_checkoutSession;
	protected $_redeemFactory;
	protected $_earnedFactory;
	protected $giftcard;
	protected $_customerSession;
	protected $_resultFactory;
	protected $_messageManager;
	
	public function __construct(
		Context $context,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Tvape\Giftcard\Model\GiftcardFactory $giftcard,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager
	){
		$this->_checkoutSession = $checkoutSession;
		$this->_redeemFactory = $redeemFactory;
		$this->_earnedFactory = $earnedFactory;
		$this->_giftcard = $giftcard;
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
			
			$earnedFactoryCollection = $this->_earnedFactory->create()->getCollection();
			$earnedFactoryCollection->addFieldToFilter('referrer_id', $customerId);
			$earnedFactoryCollection->addFieldToFilter('earned_code', $redeemCode);
			if(count($earnedFactoryCollection)){
				$earnedFactoryObject = $earnedFactoryCollection->getFirstItem();
				$commission = 0;
				if($earnedFactoryObject->getCommission() > 0){
					$commission = $earnedFactoryObject->getCommission();
				}
				$subtotal = $quote->getSubtotalWithDiscount();
				if($subtotal <= $commission){
					$this->_messageManager->addError(__("Commission Discount is graterthan total."));
				}else{
					$redeemCollection = $this->_redeemFactory->create()->getCollection();
					$redeemCollection->addFieldToFilter('redeem_code', $redeemCode);
					if(count($redeemCollection)){
						$redeemCodeObject = $redeemCollection->getFirstItem();
					}else{
						$redeemCodeObject = $this->_redeemFactory->create();
						$redeemCodeObject->setRedeemCode($redeemCode);
						$redeemCodeObject->setGiftcardPrice($commission);
					}
					$redeemCodeObject->setQuoteId($quoteId);
					try{
						$redeemCodeObject->save();
						$this->messageManager->addSuccess(__("Commission code is applied."));
					}catch(Exception $e){
						$this->_messageManager->addError(__("Something went wrong. Please try again."));
					}
				}
			}elseif(isset($params['gift_card']) && $params['gift_card'] != ""){
				$newDate = new \DateTime();
				$giftcardCollections = $this->_giftcard->create()->getCollection();
				$giftcardCollections->addFieldToFilter('customer_id', $customerId);
				$giftcardCollections->addFieldToFilter('gift_code', $params['redeem_code']);
				$giftcardCollections->addFieldToFilter('is_used', ['eq' => 0]);
				if(count($giftcardCollections)){
					$redeemCollection = $this->_redeemFactory->create()->getCollection();
					$redeemCollection->addFieldToFilter('redeem_code', $redeemCode);
					if(count($redeemCollection)){
						$redeemCodeObject = $redeemCollection->getFirstItem();
					}else{
						$redeemCodeObject = $this->_redeemFactory->create();
						$redeemCodeObject->setRedeemCode($redeemCode);
						$redeemCodeObject->setIsGiftcard(1);
						foreach($giftcardCollections as $giftcardCoupon){
							$redeemCodeObject->setGiftcardPrice($giftcardCoupon->getGiftPrice());
						}
					}
					$redeemCodeObject->setQuoteId($quoteId);
					try{
						$redeemCodeObject->save();
						$this->messageManager->addSuccess(__("Giftcard applied."));
					}catch(Exception $e){
						$this->_messageManager->addError(__("Something went wrong. Please try again."));
					}
				}
			}else{
				$this->messageManager->addError(__("Commission code is not matched."));
			}
		}else{
			$this->_messageManager->addError(__("Something went wrong. Please try again."));
		}
		$resultRedirect = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;
	}
}