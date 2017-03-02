<?php

class SM_XPos_Block_Adminhtml_Index_Orderlist_Saved extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_filterVisibility = false;
    protected $_headersVisibility = true;
    protected $_pagerVisibility = false;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sm/xpos/widget/grid.phtml');
        $this->setId('sales_order_create_order_grid');
        $this->setRowClickCallback('order.selectOrder.bind(order)');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setPagerVisibility(true);
        $this->setFilterVisibility(true);
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection()
    {
        $isXman = 0;
        if (Mage::getStoreConfig('xmanager/general/enabled') == 1) {
            /*xmanager has been installed*/
            $isXman = 1;
        }
        if ($isXman == 1) {
            $per = Mage::getModel('xmanager/permission');
            if ($per->getShareCustomer() == 1 || $per->getPermission() == '0' || $per->getModuleStatus() == 0) {
                $collection = Mage::getResourceModel('sales/order_grid_collection');
                $collection->join(array('so' => 'sales/order'), 'main_table.entity_id=so.entity_id', array('xpos' => 'xpos'), null, 'left');
                $collection->addAttributeToFilter('main_table.status', array('eq'=>'pending'));
                $collection->addAttributeToFilter('xpos',  array('eq'=>'1'));
                $this->setCollection($collection);
//            die($collection->getSelect());
                return $collection;
            } else {
                $allow = $per->getAllowAfterAss();
                $ass = $per->getAssigned();
                $isAss = '0';
                foreach ($ass as $as) {
                    if ($as != '0') {
                        $isAss = '1';
                    }
                }
                if ($allow == '0' && $isAss != '0') {
                    echo '';
                }
                $arrAdminId = array();
                $currentId = $per->getCurrentAdmin();
                $currentId = $currentId['id'];

                $arrAdminId[] = $currentId;
                foreach ($per->getIdReceive() as $id) {
                    $arrAdminId[] = $id;
                }
                $collection = Mage::getResourceModel('sales/order_grid_collection');
                $collection->addAttributeToFilter('xpos',  array('eq'=>'1'));
                $collection->join(array('so' => 'sales/order'), 'main_table.entity_id=so.entity_id', array('admin_id_create' => 'admin_id_create', 'admin_id_edit' => 'admin_id_edit'), null, 'left')
                    ->addAttributeToFilter('admin_id_create', array('in' => $arrAdminId));
                $collection->addAttributeToFilter('main_table.status', array('eq' => 'pending'));
                $this->setCollection($collection);
                return $collection;
            }
        } else {
            $collection = Mage::getResourceModel('sales/order_grid_collection');
            $collection->join(array('so' => 'sales/order'), 'main_table.entity_id=so.entity_id', array('xpos' => 'xpos'), null, 'left');
            $collection->addAttributeToFilter('main_table.status', array('eq'=>'pending'));
            $collection->addAttributeToFilter('xpos',  array('eq'=>'1'));
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }
    }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'main_table.increment_id',
            'renderer'=> 'xpos/adminhtml_index_orderlist_renderer_increment',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'type'  => 'text',
            'index' => 'billing_name',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'filter_index' => 'main_table.created_at',
            'filter' => false,
            'width' => '100px',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('Grand Total'),
            'index' => 'grand_total',
            'type' => 'price',
            'filter' => false,
            'width' => '120px',
        ));

        $this->addColumn('option_name', array(
            'header' => Mage::helper('sales')->__('Quick action'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'filter'    => false,
            'renderer' => 'xpos/adminhtml_index_orderlist_renderer_actionsaved',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Deprecated since 1.1.7
     */
    public function getRowId($row)
    {
        return $row->getId();
    }

    public function getRowUrl($row)
    {
        //if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
        //    return $this->getUrl('*/xPos/index', array('order_id' => $row->getId()));
        //}
        return '';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/loadBlock', array('block'=>'order_saved'));
    }

    public function getTypeTable()
    {
        return '-save';
    }
}
