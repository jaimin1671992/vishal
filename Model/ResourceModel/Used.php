<?php

namespace Tvape\ReferralProgram\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Used extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('referral_used', 'rec_id');
    }
}