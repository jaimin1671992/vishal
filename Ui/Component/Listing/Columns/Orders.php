<?php

namespace Tvape\ReferralProgram\Ui\Component\Listing\Columns;

use Magento\Sales\Model\Order;

class Orders extends \Magento\Ui\Component\Listing\Columns\Column {

    public function __construct(
        \Magento\Backend\Helper\Data $backendHelper,  
        Order $order,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ){
        $this->order = $order;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->backendHelper = $backendHelper;
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $orderViewUrl = $this->backendHelper->getUrl(
                    'sales/order/view',
                    ['order_id' => $item['order_id']]
                );
                $item[$this->getName()] = [
                    'url' => $orderViewUrl,
                    'text' => $item['increment_id'],
                ];
            }
        }

        return $dataSource;
    }
}