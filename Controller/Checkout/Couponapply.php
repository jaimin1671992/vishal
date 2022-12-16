<?php
namespace Tvape\ReferralProgram\Controller\Checkout;

class Couponapply extends \Magento\Framework\App\Action\Action
{
	protected $_quoteFactory;
	protected $_quotecouponFactory;
	protected $_customerSession;
	protected $_referralFactory;
	protected $_storeManager;
	protected $_checkoutSession;
	protected $_usedFactory;
	protected $_earnedFactory;
	protected $_redeemFactory;
	protected $_urlInterface;
	protected $_orderCollectionFactory;
	protected $_referralHelper;
	protected $_giftcard;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quoteCouponFactory,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Tvape\ReferralProgram\Model\UsedFactory $usedFactory,
		\Tvape\ReferralProgram\Model\EarnedFactory $earnedFactory,
		\Tvape\ReferralProgram\Model\RedeemFactory $redeemFactory,
		\Magento\Framework\UrlInterface $urlInterface,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Tvape\ReferralProgram\Helper\Data $referralHelper,
		\Tvape\Giftcard\Model\GiftcardFactory $giftcard
		)
	{
		$this->_quoteFactory = $quoteFactory;
		$this->_quotecouponFactory = $quoteCouponFactory;
		$this->_customerSession = $customerSession;
		$this->_referralFactory = $referralFactory;
		$this->_storeManager = $storeManager;
		$this->_checkoutSession = $checkoutSession;
		$this->_usedFactory = $usedFactory;
		$this->_earnedFactory = $earnedFactory;
		$this->_redeemFactory = $redeemFactory;
		$this->_urlInterface = $urlInterface;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->_referralHelper = $referralHelper;
		$this->_giftcard = $giftcard;
		return parent::__construct($context);
	}

	public function execute()
	{
		$params = $this->getRequest()->getParams();
		if(isset($params['coupon_code']) && $params['coupon_code'] != ""){
			
			$couponCode = trim($params['coupon_code']);
			$customerId = 0;
			$quote = $this->_checkoutSession->getQuote();
			$quoteId = $quote->getId();
			$customerEmail = $quote->getCustomerEmail();
			if(isset($params['customer_email']) && $params['customer_email'] != ""){
				$customerEmail = $params['customer_email'];
			}
			
			$storeId = $this->_storeManager->getStore()->getId();
			if ($this->_customerSession->isLoggedIn()) {
				$customerId = $this->_customerSession->getCustomer()->getId();
			}else{
			
				// GET APPLIED COUPONS BY QUOTE
				$redeemCouponsArray = ['applied_earned_coupon'];
				$appliedCodeArray = [];
				$redeemCollection = $this->_redeemFactory->create()->getCollection();
				$redeemCollection->addFieldToFilter('quote_id', $quoteId);
				//$redeemCollection->addFieldToFilter('is_giftcard', ['neq'=>1]);
				if(count($redeemCollection)){
					foreach($redeemCollection as $redeemObject){
						/*$removeUrl = $this->_urlInterface->getUrl('referralprogram/checkout/removereferral', ['code'=>$redeemObject->getRedeemCode()]);*/
						$redeemCouponsArray[] = "<li><span>".$redeemObject->getRedeemCode()."</span> <a data-code='".$redeemObject->getRedeemCode()."' class='referral-remove-link'>".__("Remove")."</a></li>";
						$appliedCodeArray[] = $redeemObject->getRedeemCode();
					}
				}
				if(in_array($couponCode, $appliedCodeArray)){
					echo implode(",",$redeemCouponsArray); exit;
				}

				$earnedCoupons = $this->_earnedFactory->create()->getCollection();
				$earnedCoupons->addFieldToFilter('customer_email', $customerEmail);
				$earnedCoupons->addFieldToFilter('store_id', $storeId);
				$earnedCoupons->addFieldToFilter('is_used', ['neq'=>1]);
				$earnedCoupons->addFieldToFilter('earned_code', $couponCode);
				if(count($earnedCoupons)){
					$earnedModel = $earnedCoupons->getFirstItem();
					$earnedCode = $earnedModel->getEarnedCode();
					
					$commission = 0;
					if($earnedModel->getCommission() > 0){
						$commission = $earnedModel->getCommission();
					}
					$subtotal = $quote->getSubtotalWithDiscount();
					if($subtotal <= $commission){
						echo "commission error"; exit;
					}
					
					$redeemFactory = $this->_redeemFactory->create();
					$redeemFactory->setRedeemCode($earnedCode);
					$redeemFactory->setQuoteId($quoteId);
					$redeemFactory->setGiftcardPrice($commission);
					//$removeUrl = $this->_urlInterface->getUrl('referralprogram/checkout/removereferral', ['code'=>$earnedCode]);
					$redeemCouponsArray[] = "<li><span>".$earnedCode."</span> <a data-code='".$earnedCode."' class='referral-remove-link'>".__("Remove")."</a></li>";
					try{
						$redeemFactory->save();
						$quote->collectTotals()->save();
						echo implode(",",$redeemCouponsArray);
						exit;
					}catch(Exception $e){
						
					}
				}
				
				$giftCardCollection = $this->_giftcard->create()->getCollection();
				$giftCardCollection->addFieldToFilter('customer_email', $customerEmail);
				$giftCardCollection->addFieldToFilter('store_id', $storeId);
				$giftCardCollection->addFieldToFilter('is_used', ['eq'=>0]);
				$giftCardCollection->addFieldToFilter('gift_code', $couponCode);
				
				if(count($giftCardCollection)){
					$giftCardModel = $giftCardCollection->getFirstItem();
					$giftCode = $giftCardModel->getGiftCode();
					$giftPrice = 0;
					if($giftCardModel->getGiftPrice() > 0){
						$giftPrice = $giftCardModel->getGiftPrice();
					}
					$subtotal = $quote->getSubtotalWithDiscount();
					if($subtotal <= $giftPrice){
						echo "Gift coupon error"; exit;
					}
					$redeemFactory = $this->_redeemFactory->create();
					$redeemFactory->setRedeemCode($giftCode);
					$redeemFactory->setQuoteId($quoteId);
					$redeemFactory->setGiftcardPrice($giftPrice);
					$redeemFactory->setIsGiftcard(1);
					
					$redeemCouponsArray[] = "<li><span>".$giftCode."</span> <a data-code='".$giftCode."' class='referral-remove-link'>".__("Remove")."</a></li>";
					try{
						$redeemFactory->save();
						$quote->collectTotals()->save();
						echo implode(",",$redeemCouponsArray);
						exit;
					}catch(Exception $e){
						
					}
				}
			}
			/* END */ 
			
			
			$writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/__referral12.log');
			$logger = new \Laminas\Log\Logger();
			$logger->addWriter($writer);
			$logger->info('Your text message');
			
			$referralCode = $this->_referralHelper->getReferralDiscountCode();
			if($couponCode == $referralCode){ echo $couponCode ."invalid"; exit; }
			
			$orderCollection = $this->_orderCollectionFactory->create();
			$orderCollection->addFieldToFilter('customer_email', $customerEmail);
			$orderCollection->addFieldToFilter('store_id', $storeId);
			

			if(count($orderCollection)){
				$logger->info('ORDER FOUND ');
				echo $couponCode; exit;
			}
			
			$referralCollection = $this->_referralFactory->create()->getCollection();
			$referralCollection->addFieldToFilter('referral_code', $couponCode);
			if ($this->_customerSession->isLoggedIn()) {
				$referralCollection->addFieldToFilter('customer_id', ['neq' => $customerId]);
			}else{
				//$storeId = 4;
				$referralCollection->addFieldToFilter('customer_email', ['neq' => $customerEmail]);
				$referralCollection->addFieldToFilter('store_id', $storeId);
			}
			$logger->info((string)$referralCollection->getSelect());
			if(count($referralCollection)){
				$referral = $referralCollection->getFirstItem();
				if($referral->getCodeUsed() <5){
					
					$usedCouponCollection = $this->_usedFactory->create()->getCollection();
					//$usedCouponCollection->addFieldToFilter('customer_id', $customerId);
					$usedCouponCollection->addFieldToFilter('customer_email', $customerEmail);
					$usedCouponCollection->addFieldToFilter('store_id', $storeId);
					
					if(count($usedCouponCollection)){
						echo $couponCode;  exit;
					}
					
					$quoteCouponCollection = $this->_quotecouponFactory->create()->getCollection();
					$quoteCouponCollection->addFieldToFilter('quote_id', $quoteId);
					if(count($quoteCouponCollection)){
						$quoteCoupon = $quoteCouponCollection->getFirstItem();
					}else{
						$quoteCoupon = $this->_quotecouponFactory->create();
						$quoteCoupon->setQuoteId($quoteId);
						$quoteCoupon->setStoreId($storeId);
					}
					$quoteCoupon->setReferralCode($couponCode);
					try{
						$quoteCoupon->save();
					}catch(Exception $e){
						
					}
					echo $referralCode;
					exit;
				}
			}
			
			echo $couponCode;
			exit;
		}
	}
}