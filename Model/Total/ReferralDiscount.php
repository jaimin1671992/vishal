<?php

namespace Tvape\ReferralProgram\Model\Total;

class ReferralDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    protected $helperData;

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null;

    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator,
                                \Tvape\ReferralProgram\Helper\Data $helperData)
    {
        $this->quoteValidator = $quoteValidator;
        $this->helperData = $helperData;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $enabled = $this->helperData->isModuleEnabled();
        $minimumOrderAmount = 0;
        $subtotal = $total->getTotalAmount('subtotal');
        if ($enabled && $minimumOrderAmount <= $subtotal) {
            $referralDiscount = $quote->getReferralDiscount();
            $total->setTotalAmount('referral_discount', $referralDiscount);
            $total->setBaseTotalAmount('referral_discount', $referralDiscount);
            $total->setReferralDiscount($referralDiscount);
            $quote->setReferralDiscount($referralDiscount);
            $total->setGrandTotal($total->getGrandTotal() + $referralDiscount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $referralDiscount);
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {

        $enabled = $this->helperData->isModuleEnabled();
        $minimumOrderAmount = 0;
        $subtotal = $quote->getSubtotal();
        $referralDiscount = $quote->getReferralDiscount();
		$title = __("Referral Discount");
		if($this->helperData->hasGiftDiscount()){
			$title = __("Other Discounts");
		}
        if ($enabled && $minimumOrderAmount <= $subtotal && $referralDiscount) {
            return [
                'code' => 'referral_discount',
                'title' => 'Referral Discount',
                'value' => $referralDiscount
            ];
        } else {
            return array();
        }
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Referral Discount');
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     */
    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);

    }
}
