<?php

namespace Tvape\ReferralProgram\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class ReferralDiscount extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setReferralDiscount(0);
        
        $amount = $creditmemo->getOrder()->getReferralDiscount();
        $creditmemo->setReferralDiscount($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getReferralDiscount());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getReferralDiscount());

        return $this;
    }
}