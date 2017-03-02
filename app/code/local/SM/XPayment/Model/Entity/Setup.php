<?php

class SM_XPayment_Model_Entity_Setup extends Mage_Eav_Model_Entity_Setup
{
 public function getDefaultEntities()
    {          
        return array (
            'sp205' => array(
                'entity_model'      => 'xpayment-eav/xpayment',
                'attribute_model'   => '',
                'table'             => 'xpayment-eav/xpayment',
                'attributes'        => array(
                    'store_id' => array(
                        //the EAV attribute type, NOT a mysql varchar
                        'type'              => 'int',
                        'backend'           => '',
                        'frontend'          => '',
                        'label'             => 'Store ID',
                        'input'             => 'text',
                        'class'             => '',
                        'source'            => '',
                        // store scope == 0
                        // global scope == 1
                        // website scope == 2
                        'global'            => 0,
                        'visible'           => true,
                        'required'          => true,
                        'user_defined'      => true,
                        'default'           => '',
                        'searchable'        => false,
                        'filterable'        => false,
                        'comparable'        => false,
                        'visible_on_front'  => false,
                        'unique'            => false,
                    ),
                ),
            ),
        );
    }

}
