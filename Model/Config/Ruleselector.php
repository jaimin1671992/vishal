<?php
namespace Tvape\ReferralProgram\Model\Config;
class Ruleselector implements \Magento\Framework\Option\ArrayInterface
{
	protected $_ruleFactory;
	
	public function __construct(
		\Magento\SalesRule\Model\RuleFactory $ruleFactory
	){
		$this->_ruleFactory = $ruleFactory;
	}

	public function toOptionArray()
	{
		$optionArray = [];
		$ruleCollection = $this->_ruleFactory->create()->getCollection();
		$ruleCollection->addFieldToFilter('code', ['neq'=>null]);
		foreach($ruleCollection as $rule){
			$optionArray[] = ['value' => $rule->getId(), 'label' => $rule->getName()];
		}
		return $optionArray;
	}
}