<?php

namespace Tvape\ReferralProgram\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Redeem extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('redeem_earned_coupon', 'rec_id');
    }
}