<?php
namespace Tvape\ReferralProgram\Model\ResourceModel\Used;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\Used::class, \Tvape\ReferralProgram\Model\ResourceModel\Used::class); 
    }
}