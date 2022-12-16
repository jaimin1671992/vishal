<?php

namespace Tvape\ReferralProgram\Ui\Component\Listing\Columns;

use Magento\Customer\Model\Customer;

class Customers extends \Magento\Ui\Component\Listing\Columns\Column {

    public function __construct(
        \Magento\Backend\Helper\Data $backendHelper, 
        Customer $customer,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ){
        $this->customer = $customer;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->backendHelper = $backendHelper;
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $CustEditUrl = $this->backendHelper->getUrl(
                    'customer/index/edit',
                    ['id' => $item['customer_id']]
                );
                $item[$this->getName()] = [
                    'url' => $CustEditUrl,
                    'text' => $item['cus_fullname'],
                ];
            }
        }

        return $dataSource;
    }
}