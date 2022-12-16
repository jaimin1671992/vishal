<?php
namespace Tvape\ReferralProgram\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Model\Quote;

class ReferralDiscountConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magecomp\Extrafee\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    

    /**
     * @param \Magecomp\Extrafee\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Tvape\referralProgram\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $ExtrafeeConfig = [];
        $enabled = $this->dataHelper->isModuleEnabled();
        $minimumOrderAmount = 0;
        $ExtrafeeConfig['referral_discount_label'] = $this->dataHelper->getLabel();
        $quote = $this->checkoutSession->getQuote();
        $subtotal = $quote->getSubtotal();
        $ExtrafeeConfig['custom_referral_discount'] = $this->dataHelper->getReferralDiscountAmount($quote->getId());
        
        
        $ExtrafeeConfig['show_hide_referral_block'] = ($enabled && ($minimumOrderAmount <= $subtotal) && $quote->getReferralDiscountAmount()) ? true : false;
        $ExtrafeeConfig['show_hide_referral_shipblock'] = ($enabled && ($minimumOrderAmount <= $subtotal)) ? true : false;
        return $ExtrafeeConfig;
    }

    protected function _getAddressFromQuote(Quote $quote)
    {
        return $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
    }
}
