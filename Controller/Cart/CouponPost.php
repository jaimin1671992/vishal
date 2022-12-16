<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tvape\ReferralProgram\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponPost extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Coupon factory
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;
	
	protected $_referralFactory;
	protected $_quoteCouponFactory;
	protected $storeManager;
	protected $_customerSession;
	protected $_usedFactory;
	protected $_orderCollectionFactory;
	protected $_referralHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		\Tvape\ReferralProgram\Model\QuotecouponFactory $quoteCouponFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Tvape\ReferralProgram\Model\UsedFactory $usedFactory,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Tvape\ReferralProgram\Helper\Data $referralHelper
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;
		$this->_referralFactory = $referralFactory;
		$this->_quoteCouponFactory = $quoteCouponFactory;
		$this->storeManager = $storeManager;
		$this->_customerSession = $customerSession;
		$this->_usedFactory = $usedFactory;
		$this->_orderCollectionFactory = $orderCollectionFactory;
		$this->_referralHelper = $referralHelper;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));

		$messageCouponcode = $couponCode;
		$couponCode = $this->checkReferralCoupon($couponCode);

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }
		foreach ($this->messageManager->getMessages()->getItems() as $message) {
            $this->removeMessage($message);
            $id = bin2hex(random_bytes(10));
			$message->setIdentifier($id);
			$this->messageManager->getMessages()->deleteMessageByIdentifier($id);
        }
        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($messageCouponcode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($messageCouponcode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($messageCouponcode)
                            )
                        );
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($messageCouponcode)
                            )
                        );
                    }
                }
            } else {
                $this->messageManager->addSuccessMessage(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot apply the coupon code.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }

        return $this->_goBack();
    }
	
	protected function checkReferralCoupon($couponcode){
		if (!$this->_customerSession->isLoggedIn()) {
			return $couponcode;
		}
		$cartQuote = $this->cart->getQuote();
		if($couponcode != ""){
			
			$referralCode = $this->_referralHelper->getReferralDiscountCode();
			if($referralCode == $couponcode){
				return $couponcode . "invalid";
			}
			
			$customerId = $this->_customerSession->getCustomer()->getId();
			$orderCollection = $this->_orderCollectionFactory->create();
			$orderCollection->addFieldToFilter('customer_id', $customerId);
			if(count($orderCollection)){
				//echo $couponCode; exit;
				return $couponcode;
			}
			$customerEmail = $this->_customerSession->getCustomer()->getEmail();
			$storeId = $this->storeManager->getStore()->getId();
			$referralCollection = $this->_referralFactory->create()->getCollection();
			$referralCollection->addFieldToFilter('referral_code', $couponcode);
			$referralCollection->addFieldToFilter('customer_id', ['neq' => $customerId]);
			if(count($referralCollection) > 0){
				$referralModel = $referralCollection->getFirstItem();
				if($referralModel->getCodeUsed() < 5){
					$usedCouponCollection = $this->_usedFactory->create()->getCollection();
					//$usedCouponCollection->addFieldToFilter('customer_id', $customerId);
					$usedCouponCollection->addFieldToFilter('customer_email', $customerEmail);
					$usedCouponCollection->addFieldToFilter('store_id', $storeId);
					if(count($usedCouponCollection)){
						return $couponcode;
					}
					$quoteCouponCollection = $this->_quoteCouponFactory->create()->getCollection();
					$quoteCouponCollection->addFieldToFilter('quote_id', $cartQuote->getId());
					if(count($quoteCouponCollection)){
						$quoteCoupon = $quoteCouponCollection->getFirstItem();
					}else{
						$quoteCoupon = $this->_quoteCouponFactory->create();
						$quoteCoupon->setQuoteId($cartQuote->getId());
						$quoteCoupon->setStoreId($storeId);
					}
					$quoteCoupon->setReferralCode($couponcode);
					try{
						$quoteCoupon->save();
					}catch(Exception $e){
						
					}
					return $referralCode;
				}
			}
		}else{
			$quoteCouponCollection = $this->_quoteCouponFactory->create()->getCollection();
			$quoteCouponCollection->addFieldToFilter('quote_id', $cartQuote->getId());
			if(count($quoteCouponCollection)){
				$quoteCoupon = $quoteCouponCollection->getFirstItem();
				try{
					$quoteCoupon->delete();
				}catch(Exception $e){
					
				}
			}
		}
		return $couponcode;
		
	}
}
