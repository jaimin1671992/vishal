<?php
namespace Tvape\ReferralProgram\Controller\Account;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;

class Referral extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @param Context                                             $context
     * @param PageFactory                                         $resultPageFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \MageDelight\CustomerAttachments\Helper\Data            $dataHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Refer & earn'));
        return $resultPage;
    }
}