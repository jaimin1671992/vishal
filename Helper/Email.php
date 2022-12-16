<?php 
namespace Tvape\ReferralProgram\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;
    protected $logger;
	protected $referralFactory;
	protected $customerRepositoryInterface;
	protected $storeManager;
	protected $_customerSession;
	protected $urlBuilder;
	protected $_referralHelper;
	protected $_scopeConfig;
	protected $_orderinterface;
	protected $_referralEarned;
	protected $_priceHelper;
	protected $_sharelogFactory;
	protected $_timezoneInterface;

    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
		\Tvape\ReferralProgram\Model\ReferralFactory $referralFactory,
		CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\UrlInterface $urlBuilder,
		\Tvape\ReferralProgram\Helper\Data $referralHelper,
		ScopeConfigInterface $scopeConfig,
		\Magento\Sales\Api\Data\OrderInterface $orderinterface,
		\Tvape\ReferralProgram\Model\EarnedFactory $referralEarned,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
		\Tvape\ReferralProgram\Model\SharelogFactory $sharelogFactory
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $context->getLogger();
		$this->referralFactory = $referralFactory;
		$this->customerRepositoryInterface = $customerRepositoryInterface;
		$this->storeManager = $storeManager;
		$this->_customerSession = $customerSession;
		$this->urlBuilder = $urlBuilder;
		$this->_referralHelper = $referralHelper;
		$this->_scopeConfig = $scopeConfig;
		$this->_orderinterface = $orderinterface;
		$this->_referralEarned = $referralEarned;
		$this->_priceHelper = $priceHelper;
		$this->_sharelogFactory = $sharelogFactory;
		$this->_timezoneInterface = $timezoneInterface;
    }

    public function sendEmail($email, $customerId, $referrerEmail = "", $orderId = 0)
    {
		if($this->_referralHelper->isFunctionOff()){
			return;
		}
		
		$storeId = $this->storeManager->getStore()->getId();
		$websiteId = $this->storeManager->getStore()->getWebsiteId(); 
		$referralCollection = $this->referralFactory->create()->getCollection();
		if($customerId != 0){
			$referralCollection->addFieldToFilter('customer_id', $customerId);
		}else{
			$referralCollection->addFieldToFilter('customer_email', $referrerEmail);
			$referralCollection->addFieldToFilter('store_id', $storeId);
		}
		
		$shareLog = $this->_sharelogFactory->create();
		$shareLog->setCustomerId($customerId);
		$shareLog->setCustomerEmail($referrerEmail);
		$shareLog->setShareEmail($email);
		$shareLog->setStoreId($storeId);
		$dateTime = $this->_timezoneInterface->date()->format('Y-m-d');
		$shareLog->setRecDate($dateTime);
		try{
			$shareLog->save();
		}catch(Exception $e){
			
		}
		
		$referralCode = "";
		if(count($referralCollection)){
			$referralModel = $referralCollection->getFirstItem();
			$referralCode = $referralModel->getReferralCode();
		}
		$customerName = "Your friend";
		$customerEmail = "noreply@torontovaporizer.ca";
		if($storeId == 3){
			$customerEmail = "noreply@tvape.com";
		}
		if($customerId != 0){
			$customerObject = $this->_customerSession->getCustomer();
			$customerName = $customerObject->getName();
			//$customerEmail = $customerObject->getEmail();
		}else{
			/*$order = $this->_orderinterface->load($orderId);
			if($order){
				$customerName = $order->getCustomerName();
			}*/
			/*if($order->getCustomerId() > 0){
				$customerObject = $this->customerRepositoryInterface->getById($order->getCustomerId());
			}*/
		}
		echo $customerName;

		$comAmt = $this->_referralHelper->getCommitionAmt();
		$refAmt = $this->_referralHelper->getReferralDiscountAmt();
		
		//$name = $customerObject->getFirstname() . " " .$customerObject->getLastname();
		
        try {
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($customerName),
                'email' => $this->escaper->escapeHtml($customerEmail)
            ];
		
			$referTemplate = $this->_scopeConfig->getValue('tvape_referralprogram/referralprogram_configuration/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($referTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars([
                    'templateVar'  => $referralCode,
					'customerName' => $customerName,
					'comAmt' => $comAmt,
					'refAmt' => $comAmt,
					'baseUrl' => $this->storeManager->getStore()->getBaseUrl()
                ])
                ->setFrom($sender)
                ->addTo($email)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
			return true;
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
			return false;
        }
    }
	
	public function getCurrentStoreId(){
		return $storeId = $this->storeManager->getStore()->getId();
	}
	
	public function sendReferralWelcomeEmail($customerId, $orderId){
		if($this->_referralHelper->isFunctionOff()){
			return;
		}
		$storeId = $this->storeManager->getStore()->getId();
		
		
		$comAmt = $this->_referralHelper->getCommitionAmt();
		$refAmt = $this->_referralHelper->getReferralDiscountAmt();
		
		$referralCollection = $this->referralFactory->create()->getCollection();
		$referralCollection->addFieldToFilter('customer_id', $customerId);
		$customerObject = $this->customerRepositoryInterface->getById($customerId);
		$email = $customerObject->getEmail();
		$referralCode = "";
		$eligableforemail = true;
		if(count($referralCollection)){
			$eligableforemail = false;
			//$referral = $referralCollection->getFirstItem();
			//$referralCode = $referral->getReferralCode();
		}else{
			$random = $this->_referralHelper->getRandomStrings(5);
			$referralCode = "referral_". $customerId . "_" . $random;
			$referralData = [];
			$referralData['store_id'] = $storeId;
			$referralData['customer_id'] = $customerId;
			$referralData['referral_code'] = $referralCode;
			$referralData['customer_email'] = $email;
			$referral = $this->referralFactory->create();
			$referral->setData($referralData);
			try{
				$referral->save();
			}catch(Exception $e){
				$this->logger->debug($e->getMessage());
			}
		}
		
		if($eligableforemail){
			
			$name = $customerObject->getFirstname() . " " .$customerObject->getLastname();
			$email = $customerObject->getEmail();
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			//$senderEmail = $this->_scopeConfig->getValue('trans_email/ident_support/email',$storeScope);
			$senderName  = $this->_scopeConfig->getValue('trans_email/ident_support/name',$storeScope);
			$senderEmail = "noreply@torontovaporizer.ca";
			if($storeId == 3){
				$senderEmail = "noreply@tvape.com";
			}
			
			
			try {
				$this->inlineTranslation->suspend();
				$sender = [
					'name' => $this->escaper->escapeHtml($senderName),
					'email' => $this->escaper->escapeHtml($senderEmail),
				];
				
				/*$orderTemplate = $this->_scopeConfig->getValue('tvape_referralprogram/referralprogram_configuration/ordertemplate',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$storeId);*/
				$storeAddress = '11 Progress Ave. Unit #17, Scarborough, Toronto, ON M1P 4S7';
				if(in_array($storeId, array(5,18,20,21))){
					$storeAddress = 'Esperantostr. 8B, 70197, Stuttgart.';
				}
				$orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate';
				//if($storeId==4){ $orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate_cafr'; }
				//if($storeId==18){ $orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate_fr'; }
				//if($storeId==20 || $storeId==21){ $orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate_de'; }
				$transport = $this->transportBuilder
					->setTemplateIdentifier($orderTemplate)
					->setTemplateOptions(
						[
							'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
							'store' => $storeId,
						]
					)
					->setTemplateVars([
						'templateVar'  => $referralCode,
						'customerName' => $name,
						'storeName' => $this->storeManager->getStore()->getName(),
						'orderid' => $orderId,
						'comAmt' => $comAmt,
						'refAmt' => $comAmt,
						'baseUrl' => $this->storeManager->getStore()->getBaseUrl(),
						'storeAddress' => $storeAddress
						//'referralCmsUrl' => $this->urlBuilder->getUrl('referrals')
					])
					->setFrom($sender)
					->addTo($email)
					->getTransport();
				$transport->sendMessage();
				$this->inlineTranslation->resume();
				return true;
			} catch (\Exception $e) {
				
				$this->logger->debug($e->getMessage());
				return false;
			}
		}
	}
	
	public function sendEarnedCouponEmail($orderId){
		
		if($this->_referralHelper->isFunctionOff()){
			return;
		}
		
		$storeId = $this->storeManager->getStore()->getId();
		$order = $this->_orderinterface->load($orderId);
		//$customerId = $order->getCustomerId();
		$referralEarnedCollection = $this->_referralEarned->create()->getCollection();
		$referralEarnedCollection->addFieldToFilter('order_id', $order->getId());
		if(count($referralEarnedCollection)){
			$referralEarnedModel = $referralEarnedCollection->getFirstItem();
			$earnedCode = $referralEarnedModel->getEarnedCode();
			$commission = $this->_priceHelper->currency($referralEarnedModel->getCommission(), true, false);
			$customerId = $referralEarnedModel->getReferrerId();
			$customerObject = $this->customerRepositoryInterface->getById($customerId);
			
			$name = $customerObject->getFirstname() . " " .$customerObject->getLastname();
			$email = $customerObject->getEmail();
			
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			//$senderEmail = $this->_scopeConfig->getValue('trans_email/ident_support/email',$storeScope);
			$senderName  = $this->_scopeConfig->getValue('trans_email/ident_support/name',$storeScope);
			$senderEmail = "noreply@torontovaporizer.ca";
			if($storeId == 3){
				$senderEmail = "noreply@tvape.com";
			}
			
			try {
				$this->inlineTranslation->suspend();
				$sender = [
					'name' => $this->escaper->escapeHtml($senderName),
					'email' => $this->escaper->escapeHtml($senderEmail),
				];
				
				$earnedTemplate = $this->_scopeConfig->getValue('tvape_referralprogram/referralprogram_configuration/earnedemail',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$storeId);

				$transport = $this->transportBuilder
					->setTemplateIdentifier($earnedTemplate)
					->setTemplateOptions(
						[
							'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
							'store' => $storeId,
						]
					)
					->setTemplateVars([
						'templateVar'  => $earnedCode,
						'customerName' => $name,
						'storeName' => $this->storeManager->getStore()->getName(),
						'orderid' => $orderId,
						//'referralCmsUrl' => $this->urlBuilder->getUrl('referrals')
						'commission' => $commission,
						'baseUrl' => $this->storeManager->getStore()->getBaseUrl()
					])
					->setFrom($sender)
					->addTo($email)
					->getTransport();
				$transport->sendMessage();
				$this->inlineTranslation->resume();
				return true;
			} catch (\Exception $e) {
				
				$this->logger->debug($e->getMessage());
				return false;
			}
			
		}
	}
	
	public function sendCouponUsedEmail($orderId){
		return true;
		$order = $this->_orderinterface->load($orderId);
		$storeId = $this->storeManager->getStore()->getId();
		
		$referralEarnedCollection = $this->_referralEarned->create()->getCollection();
		$referralEarnedCollection->addFieldToFilter('used_order_id', $orderId);
		
		//echo $referralEarnedCollection->getSelect();
		
		if(count($referralEarnedCollection)){
			//echo "HERE";
			$referralEarnedModel = $referralEarnedCollection->getFirstItem();
			$earnedCode = $referralEarnedModel->getEarnedCode();
			$customerId = $referralEarnedModel->getReferrerId();
			$customerObject = $this->customerRepositoryInterface->getById($customerId);
			
			//echo " CUSTOMERID : " . $customerObject->getId();
			
			$name = $customerObject->getFirstname() . " " .$customerObject->getLastname();
			$email = $customerObject->getEmail();
			
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			$senderEmail = $this->_scopeConfig->getValue('trans_email/ident_support/email',$storeScope);
			$senderName  = $this->_scopeConfig->getValue('trans_email/ident_support/name',$storeScope);
			
			
			/*try {
				$this->inlineTranslation->suspend();
				$sender = [
					'name' => $this->escaper->escapeHtml($senderName),
					'email' => $this->escaper->escapeHtml($senderEmail),
				];
				$couponusedTemplate = $this->_scopeConfig->getValue('tvape_referralprogram/referralprogram_configuration/couponused',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$storeId);
				$transport = $this->transportBuilder
					->setTemplateIdentifier($couponusedTemplate)
					->setTemplateOptions(
						[
							'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
							'store' => $storeId,
						]
					)
					->setTemplateVars([
						'templateVar'  => $earnedCode,
						'customerName' => $name,
						'storeName' => $this->storeManager->getStore()->getName(),
						'orderid' => $order->getIncrementId(),
						//'referralCmsUrl' => $this->urlBuilder->getUrl('referrals')
					])
					->setFrom($sender)
					->addTo($email)
					->getTransport();
				$transport->sendMessage();
				$this->inlineTranslation->resume();
				return true;
			} catch (\Exception $e) {
				
				$this->logger->debug($e->getMessage());
				return false;
			}*/
			
		}
	}
	
	
	
	public function sendReferralWelcomeEmailTest(){
		
		if(!$this->_customerSession->isLoggedIn()) {
			return false;
		}
			
		$customerObject = $this->_customerSession->getCustomer();
		$customerId = $customerObject->getId();
		$storeId = $this->storeManager->getStore()->getId();
		
		$comAmt = $this->_referralHelper->getCommitionAmt();
		$refAmt = $this->_referralHelper->getReferralDiscountAmt();
		
		$referralCollection = $this->referralFactory->create()->getCollection();
		$referralCollection->addFieldToFilter('customer_id', $customerId);
		$customerObject = $this->customerRepositoryInterface->getById($customerId);
		$email = $customerObject->getEmail();
		$referralCode = "";
		$eligableforemail = true;
		if(count($referralCollection)){
			//$eligableforemail = false;
			$referral = $referralCollection->getFirstItem();
			$referralCode = $referral->getReferralCode();
		}else{
			$random = $this->_referralHelper->getRandomStrings(5);
			$referralCode = "referral_". $customerId . "_" . $random;
			$referralData = [];
			$referralData['store_id'] = $storeId;
			$referralData['customer_id'] = $customerId;
			$referralData['referral_code'] = $referralCode;
			$referralData['customer_email'] = $email;
			$referral = $this->referralFactory->create();
			$referral->setData($referralData);
			try{
				$referral->save();
			}catch(Exception $e){
				$this->logger->debug($e->getMessage());
			}
		}
		
		if($eligableforemail){
			
			$name = $customerObject->getFirstname() . " " .$customerObject->getLastname();
			$email = $customerObject->getEmail();
			$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			$senderEmail = $this->_scopeConfig->getValue('trans_email/ident_support/email',$storeScope);
			$senderName  = $this->_scopeConfig->getValue('trans_email/ident_support/name',$storeScope);
			try {
				$this->inlineTranslation->suspend();
				$sender = [
					'name' => $this->escaper->escapeHtml($senderName),
					'email' => $this->escaper->escapeHtml($senderEmail),
				];
				
				/*$orderTemplate = $this->_scopeConfig->getValue('tvape_referralprogram/referralprogram_configuration/ordertemplate',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$storeId);*/
				$orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate';
				if($storeId==4){ $orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate_cafr'; }
				if($storeId==18){ $orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate_fr'; }
				if($storeId==20 || $storeId==21){ $orderTemplate='tvape_referralprogram_referralprogram_configuration_ordertemplate_de'; }
				$transport = $this->transportBuilder
					->setTemplateIdentifier($orderTemplate)
					->setTemplateOptions(
						[
							'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
							'store' => $storeId,
						]
					)
					->setTemplateVars([
						'templateVar'  => $referralCode,
						'customerName' => $name,
						'storeName' => $this->storeManager->getStore()->getName(),
						//'orderid' => 'TEST ORDER',
						'comAmt' => $comAmt,
						'refAmt' => $comAmt
						//'referralCmsUrl' => $this->urlBuilder->getUrl('referrals')
					])
					->setFrom($sender)
					->addTo($email)
					->getTransport();
				$transport->sendMessage();
				$this->inlineTranslation->resume();
				return true;
			} catch (\Exception $e) {
				
				$this->logger->debug($e->getMessage());
				return false;
			}
		}
	}
}