<?php

namespace Tvape\ReferralProgram\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class ReferralDiscount extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setReferralDiscount(0);
        
        $amount = $invoice->getOrder()->getReferralDiscount();
        $invoice->setReferralDiscount($amount);
       

        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getReferralDiscount());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getReferralDiscount());

        return $this;
    }
}
