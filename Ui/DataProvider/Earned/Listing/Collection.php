<?php

namespace Tvape\ReferralProgram\Ui\DataProvider\Earned\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;


class Collection extends SearchResult{

    protected function _initSelect(){

        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        
        parent::_initSelect();
        $this->getSelect()
            ->join(
                ['ref' => $this->getTable('customer_entity')], 
                'main_table.referrer_id = ref.entity_id',
                array('firstname' => 'firstname', 'lastname' => 'lastname')
            )->join(
                ['cus' => $this->getTable('customer_entity')], 
                'main_table.customer_id = cus.entity_id',
                array('firstname' => 'firstname', 'lastname' => 'lastname')
            )->join(
                ['thirdTable' => $this->getTable('sales_order')], 
                'main_table.order_id = thirdTable.entity_id',
                array('increment_id' => 'thirdTable.increment_id')
            )
            ->columns(new \Zend_Db_Expr("CONCAT(`ref`.`firstname`, ' ',`ref`.`lastname`) AS ref_fullname"))
            ->columns(new \Zend_Db_Expr("CONCAT(`cus`.`firstname`, ' ',`cus`.`lastname`) AS cus_fullname"));

            //$select->getSelect()->columns(new \Zend_Db_Expr('CONCAT_WS(" ", main_table.lastname, main_table.firstname) as customer_name'));

            $this->addFilterToMap('firstname', 'ref.firstname');
            $this->addFilterToMap('lastname', 'ref.lastname');
            $this->addFilterToMap('increment_id', 'thirdTable.increment_id');
            $this->addFilterToMap('customer_email', 'main_table.customer_email');
            $this->addFilterToMap('store_id', 'main_table.store_id');
            $this->addFilterToMap(
                'ref_fullname',
                new \Zend_Db_Expr('CONCAT_WS(" ", ref.lastname, ref.firstname)')
            );
            $this->addFilterToMap(
                'cus_fullname',
                new \Zend_Db_Expr('CONCAT_WS(" ", cus.lastname, cus.firstname)')
            );


    }

}