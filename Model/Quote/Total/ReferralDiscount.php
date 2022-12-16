<?php
namespace Tvape\ReferralProgram\Model\Quote\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;

class ReferralDiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    protected $helperData;
	protected $_priceCurrency;
    private $taxCalculator;

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
								\Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
                                \Tvape\ReferralProgram\Helper\Data $helperData
    )
    {
        $this->quoteValidator = $quoteValidator;
		$this->_priceCurrency = $priceCurrency;
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
            $referralDiscount = $this->helperData->getReferralDiscountAmount($quote->getId());
            $total->setTotalAmount('referral_discount', $referralDiscount);
            $total->setBaseTotalAmount('referral_discount', $referralDiscount);
            $total->setReferralDiscount($referralDiscount);
            $quote->setReferralDiscount($referralDiscount);
			/*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
			$version = (float)$productMetadata->getVersion();

			if($version > 2.1)
			{
				//$total->setGrandTotal($total->getGrandTotal() + $referralDiscount);
			}
			else
			{
				$total->setGrandTotal($total->getGrandTotal() + $referralDiscount);
			}*/
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
        $minimumOrderAmount = $this->helperData->getMinimumOrderAmount();
        $subtotal = $quote->getSubtotal();
        $referralDiscount = $quote->getReferralDiscount();
        $address = $this->_getAddressFromQuote($quote);
		
		
		$description = $total->getDiscountDescription();

        $result = [];
        if ($enabled && ($minimumOrderAmount <= $subtotal) && $referralDiscount) {
            $result = [
                'code' => 'referral_discount',
                'title' => strlen($description) ? __('Discount (%1)', $description) : __('Discount'),
                'value' => $referralDiscount
            ];
        }

        return $result;
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
    protected function _getAddressFromQuote(Quote $quote)
    {
        return $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
    }

    protected function _getAddressTaxRequest($address)
    {
        $addressTaxRequest = $this->taxCalculator->getRateRequest(
            $address,
            $address->getQuote()->getBillingAddress(),
            $address->getQuote()->getCustomerTaxClassId(),
            $address->getQuote()->getStore()
        );
        return $addressTaxRequest;
    }

    protected function _saveAppliedTaxes(
        Address $address,
        $applied,
        $amount,
        $baseAmount,
        $rate
    ) {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = 0;
        if(is_array($previouslyAppliedTaxes)) {
            $process = count($previouslyAppliedTaxes);
        }
        foreach ($applied as $row) {
            if ($row['percent'] == 0) {
                continue;
            }
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if ($row['percent'] !== null) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount / $rate * $row['percent'];
                $baseAppliedAmount = $baseAmount / $rate * $row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }

            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }
}
