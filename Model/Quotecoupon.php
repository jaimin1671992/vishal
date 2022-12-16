<?php

namespace Tvape\ReferralProgram\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;

class Quotecoupon extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\ResourceModel\Quotecoupon::class);
    }
    
}