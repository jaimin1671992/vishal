<?php

namespace Tvape\ReferralProgram\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;

class Used extends AbstractModel
{
    protected $_dateTime;

    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\ResourceModel\Used::class);
    }
    
}