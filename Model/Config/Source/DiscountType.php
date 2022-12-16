<?php 

namespace Tvape\ReferralProgram\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Profile
 * @package Vendor\Package\Model\Config\Source
 */
class DiscountType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray() : array
    {
        return [
            
            ['value' => 'percentage', 'label' => __('Percentage')],
            ['value' => 'fixed', 'label' => __('Fixed')]
        ];
    }
}