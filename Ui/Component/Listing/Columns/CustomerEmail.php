<?php

namespace Tvape\ReferralProgram\Ui\Component\Listing\Columns;

class CustomerEmail extends \Magento\Ui\Component\Listing\Columns\Column {

    public function __construct(
        \Magento\Backend\Helper\Data $backendHelper, 
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ){
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
                    'text' => $item['customer_email'],
                ];
            }
        }

        return $dataSource;
    }
}