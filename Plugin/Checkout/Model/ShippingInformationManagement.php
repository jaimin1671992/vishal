<?php
namespace Tvape\ReferralProgram\Plugin\Checkout\Model;


class ShippingInformationManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

   
    protected $dataHelper;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magecomp\Extrafee\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Tvape\ReferralProgram\Helper\Data $dataHelper
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    )
    {
        $referralProgramDiscount = $addressInformation->getExtensionAttributes()->getReferralDiscount();
        $quote = $this->quoteRepository->getActive($cartId);
        if ($referralProgramDiscount) {
            $referralDiscount = $this->dataHelper->getReferralDiscountAmount($quote->getId());
            $quote->setReferralDiscount($referralDiscount);
        } else {
            $quote->setReferralDiscount(NULL);
        }
    }
}

