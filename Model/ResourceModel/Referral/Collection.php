<?php
namespace Tvape\ReferralProgram\Model\ResourceModel\Referral;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\Referral::class, \Tvape\ReferralProgram\Model\ResourceModel\Referral::class);
    }
}