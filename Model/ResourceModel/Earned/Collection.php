<?php
namespace Tvape\ReferralProgram\Model\ResourceModel\Earned;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\Earned::class, \Tvape\ReferralProgram\Model\ResourceModel\Earned::class);
    }
}