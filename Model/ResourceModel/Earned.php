<?php

namespace Tvape\ReferralProgram\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Earned extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('referral_earned', 'rec_id');
    }
	
}