<?php
namespace Tvape\ReferralProgram\Controller\Checkout;

class Couponload extends \Magento\Framework\App\Action\Action
{
	protected $_quoteFactory;
	protected $_quotecouponFactory;
	protected $_checkoutSession;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quoteCouponFactory,
		\Magento\Checkout\Model\Session $checkoutSession
		)
	{
		$this->_quoteFactory = $quoteFactory;
		$this->_quotecouponFactory = $quoteCouponFactory;
		$this->_checkoutSession = $checkoutSession;
		return parent::__construct($context);
	}

	public function execute()
	{
		$params = $this->getRequest()->getParams();
		
		//$this->_quoteFactory->create()->load($params['quote_id']);
		$quote = $this->_checkoutSession->getQuote();
		$quoteCouponCollection = $this->_quotecouponFactory->create()->getCollection();
		$quoteCouponCollection->addFieldToFilter('quote_id', $quote->getId());
		if(count($quoteCouponCollection)){
			$quoteCoupon = $quoteCouponCollection->getFirstItem();
			echo $quoteCoupon->getReferralCode();
			exit;
		}
		echo $this->_checkoutSession->getQuote()->getCouponCode();
		
	}
}