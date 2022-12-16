<?php

namespace Tvape\ReferralProgram\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quotecoupon extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('referral_quote_coupon', 'rec_id');
    }
}