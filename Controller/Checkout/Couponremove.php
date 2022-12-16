<?php
namespace Tvape\ReferralProgram\Controller\Checkout;

class Couponremove extends \Magento\Framework\App\Action\Action
{
	protected $_quoteFactory;
	protected $_quotecouponFactory;
	protected $_customerSession;
	protected $_referralFactory;
	protected $_storeManager;
	protected $_checkoutSession;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quoteCouponFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Checkout\Model\Session $checkoutSession
		)
	{
		$this->_quoteFactory = $quoteFactory;
		$this->_quotecouponFactory = $quoteCouponFactory;
		$this->_customerSession = $customerSession;
		$this->_referralFactory = $referralFactory;
		$this->_storeManager = $storeManager;
		$this->_checkoutSession = $checkoutSession;
		return parent::__construct($context);
	}

	public function execute()
	{
		$quote = $this->_checkoutSession->getQuote();
		$quoteId = $quote->getId();
		$storeId = $this->_storeManager->getStore()->getId();
		if ($this->_customerSession->isLoggedIn()) {
			$customerId = $this->_customerSession->getCustomer()->getId();
		}
		$quoteCouponCollection = $this->_quotecouponFactory->create()->getCollection();
		$quoteCouponCollection->addFieldToFilter('quote_id', $quoteId);
		if(count($quoteCouponCollection)){
			$quoteCoupon = $quoteCouponCollection->getFirstItem();
			try{
				$quoteCoupon->delete();
			}catch(Exception $e){
				
			}
		}
	}
}