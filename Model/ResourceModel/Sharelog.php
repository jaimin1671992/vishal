<?php

namespace Tvape\ReferralProgram\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sharelog extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('share_log', 'rec_id');
    }
}