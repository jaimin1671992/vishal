<?php
namespace Tvape\ReferralProgram\Model\ResourceModel\Redeem;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\Redeem::class, \Tvape\ReferralProgram\Model\ResourceModel\Redeem::class);
    }
}