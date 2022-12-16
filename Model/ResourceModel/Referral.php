<?php

namespace Tvape\ReferralProgram\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Referral extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('referral_code', 'rec_id');
    }
}