<?php
namespace Tvape\ReferralProgram\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddReferralDiscountToOrderObserver implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $referralDiscount = $quote->getReferralDiscount();
        if (!$referralDiscount) {
            return $this;
        }
        //Set fee data to order
        $order = $observer->getOrder();
        $order->setData('referral_discount', $referralDiscount);
        
		return $this;
    }
}
