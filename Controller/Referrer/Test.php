<?php
namespace Tvape\ReferralProgram\Controller\Referrer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;

class Test extends \Magento\Framework\App\Action\Action
{
	
	public function execute()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$emailHelper = $objectManager->get('\Tvape\ReferralProgram\Helper\Email');
		$emailHelper->sendReferralWelcomeEmailTest();
	}
}