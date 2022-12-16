<?php
namespace Tvape\ReferralProgram\Model\ResourceModel\Quotecoupon;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Tvape\ReferralProgram\Model\Quotecoupon::class, \Tvape\ReferralProgram\Model\ResourceModel\Quotecoupon::class);
    }
}