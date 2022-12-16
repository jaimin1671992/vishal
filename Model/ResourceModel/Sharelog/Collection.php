<?php
namespace Tvape\ReferralProgram\Model\ResourceModel\Sharelog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\Sharelog::class, \Tvape\ReferralProgram\Model\ResourceModel\Sharelog::class);
    }
}