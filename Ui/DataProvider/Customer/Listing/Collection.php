<?php

namespace Tvape\ReferralProgram\Ui\DataProvider\Customer\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;


class Collection extends SearchResult{

    protected function _initSelect(){

        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        
        parent::_initSelect();
        $this->getSelect()
            ->join(
                ['cus' => $this->getTable('customer_entity')], 
                'main_table.customer_id = cus.entity_id',
                array('firstname' => 'firstname', 'lastname' => 'lastname', 'email' => 'email')
            )->columns(new \Zend_Db_Expr("CONCAT(`cus`.`firstname`, ' ',`cus`.`lastname`) AS cus_fullname"));

            $this->addFilterToMap('email', 'cus.email');
            $this->addFilterToMap('store_id', 'main_table.store_id');
            $this->addFilterToMap(
                'cus_fullname',
                new \Zend_Db_Expr('CONCAT_WS(" ", cus.lastname, cus.firstname)')
            );


    }

}