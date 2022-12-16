<?php
namespace Tvape\ReferralProgram\Model;
use Magento\Framework\Exception\AuthenticationException;

class AddReferralProgram 
{
    protected $collection;
    protected $helper;
    protected $store;
    protected $quoteCollector;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $collection,
        \Tvape\ReferralProgram\Helper\Data $helper,
        \Magento\Store\Model\App\Emulation $store,
        \Magento\Quote\Model\Quote\TotalsCollector $quoteCollector
    ) {

        $this->collection = $collection;
        $this->helper = $helper;
        $this->store=$store;
        $this->quoteCollector = $quoteCollector;
    }

    public function addExtrafee($quoteid,$storeid)
    {
        try {
            if (empty($quoteid) || empty($storeid) )
            {
                $response = ["status"=>false, "message"=>__("Invalid parameter list.")];
                return json_encode($response);
            }

            else {
                $quote = $this->collection->get($quoteid);
                $this->store->startEnvironmentEmulation($storeid , 'frontend');
                $this->quoteCollector->collectQuoteTotals($quote);
                $quote->save();
                $this->store->stopEnvironmentEmulation();
                $response = ["status" => true, "extrafee_title" => $this->helper->getLabel(),
                    "referral_discount" => $quote->getShippingAddress()->getReferralDiscount()];

            }
            return json_encode($response);
        }
        catch (\Exception $e) {
            throw new AuthenticationException(__($e->getMessage()));
        }

    }

}
