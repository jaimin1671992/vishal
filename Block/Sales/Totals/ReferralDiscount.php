<?php

namespace Tvape\ReferralProgram\Block\Sales\Totals;


class ReferralDiscount extends \Magento\Framework\View\Element\Template
{
    
    protected $_dataHelper;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
         \Tvape\ReferralProgram\Helper\Data $dataHelper,
        array $data = []
    )
    {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
       // $store = $this->getStore();
		$writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/___1.log');
		$logger = new \Laminas\Log\Logger();
		$logger->addWriter($writer);
		$logger->info(get_class($parent));
        $referralDiscount = new \Magento\Framework\DataObject(
            [
                'code' => 'referral_discount',
                'strong' => false,
                'value' => $this->_source->getReferralDiscount(),
                //'label' => $this->_dataHelper->getLabel(),
				'label' => 'NEW TEST',
            ]
        );

        $parent->addTotal($referralDiscount, 'referral_discount');

        return $this;
    }

}
