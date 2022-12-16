<?php

namespace Tvape\ReferralProgram\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class CheckShoppingCartObserver implements ObserverInterface
{  
    protected $redirect;
    protected $checkoutSession;
    protected $productRepository;  
    protected $categoryRepository;  
    protected $customerRepositoryInterface;
    protected $cart;
	protected $_messageManager;
	
	
    public function __construct(
        RedirectInterface                                 $_redirect,
        \Magento\Checkout\Model\Session                   $_checkoutSession,
        \Magento\Catalog\Model\ProductRepository          $_productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface  $_categoryRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Checkout\Model\Cart $cart,
		\Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->redirect                    = $_redirect;
        $this->checkoutSession             = $_checkoutSession;
        $this->productRepository           = $_productRepository;
        $this->categoryRepository          = $_categoryRepository;
        $this->customerRepositoryInterface = $_customerRepositoryInterface;
        $this->cart = $cart;
		$this->_messageManager = $messageManager;
    }

    public function execute(EventObserver $observer)
    {
		/*$quote = $this->checkoutSession->getQuote();
        if((float)$quote->getGrandTotal() <= 0){
			$this->_messageManager->addError(__("Grandtotal can not be 0."));
			$controller = $observer->getControllerAction();
			$this->redirect->redirect($controller->getResponse(), 'checkout/cart');
        }*/
    }
}