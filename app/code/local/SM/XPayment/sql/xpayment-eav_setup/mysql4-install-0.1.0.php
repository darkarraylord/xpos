<?php

	 
				$installer = $this;
				$installer->addEntityType('sp205',Array(
				'entity_model'          =>'xpayment-eav/xpayment',
				'attribute_model'       =>'',
				'table'         =>'xpayment-eav/xpayment',
				'increment_model'       =>'',
				'increment_per_store'   =>'0'
				));

				$installer->createEntityTables('xpayment-eav/xpayment');

				$installer->installEntities();
	 
	 
