<?php

namespace Tvape\ReferralProgram\Block;

class Checkoutjs extends \Magento\Framework\View\Element\Template
{
	protected $_helper;
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Tvape\ReferralProgram\Helper\Data $helper
	)
	{
		parent::__construct($context);
		$this->_helper = $helper;
	}
	
	public function isModuleEnabled(){
		return $this->_helper->isModuleEnabled();
	}
}