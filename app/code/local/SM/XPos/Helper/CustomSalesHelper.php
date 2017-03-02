<?php
require_once(BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'Catalog' . DS . 'ProductController.php');

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 12/23/15
 * Time: 2:40 PM
 */
class SM_XPos_Helper_CustomSalesHelper extends Mage_Core_Helper_Abstract {

    /**
     * @var
     */
    protected $_customSalesProductId;
    /**
     *
     */
    const CUSTOM_SALES_PRODUCT_SKU = 'custom_sales_product_for_xpos_new';
    /**
     * @var string
     */
    const CUSTOM_SALES_PRODUCT_QUERY_SEARCH = 'custom_sales_product_for_xpos_query_search';
    /**
     * @var bool
     */
    public static $IS_COLLECT = false;
    /**
     * @var int
     */
    public static $COUNT_CURRENT_CUSTOM_SALES = 0;

    /**
     * @return bool
     */
    public static function getFlagCollect() {
        return SM_XPos_Helper_CustomSalesHelper::$IS_COLLECT;
    }

    /**
     * @param bool $flag
     * @return bool
     */
    public static function setFlagCollect($flag = true) {
        return SM_XPos_Helper_CustomSalesHelper::$IS_COLLECT = $flag;
    }

    /**
     * @return false|int
     */
    public function getCustomSalesId() {
        if (is_null($this->_customSalesProductId)) {
            $model = $this->getProductModel();
            $this->_customSalesProductId = $model->getResource()->getIdBySku(self::CUSTOM_SALES_PRODUCT_SKU);
            if (!$this->_customSalesProductId) {
                $this->_customSalesProductId = null;
                $this->createNewCustomSalesProduct();
                $this->_customSalesProductId = $this->getCustomSalesId();
            }

        }
        return $this->_customSalesProductId;
    }

    /**
     * @return Mage_Catalog_Model_Product|null
     */
    public function getCustomerSalesProduct() {
        $customSalesId = $this->getCustomSalesId();
        if (is_null($customSalesId))
            return null;
        return Mage::getModel('catalog/product')->load($customSalesId);
    }

    /**
     * @var
     */
    protected $_produceModel;

    /**
     * @return Mage_Catalog_Model_Product
     */
    protected function getProductModel() {
        if (is_null($this->_produceModel))
            $this->_produceModel = Mage::getModel('catalog/product');
        return $this->_produceModel;
    }

    /**
     * @throws Zend_Controller_Exception
     */
    protected function createNewCustomSalesProduct() {
        $productData = array(
            'form_key' => 'pBD76JbdmOcyFnmY',
            'product' =>
                array(
                    'name' => 'Custom Sales',
                    'description' => 'Custom Sales For Xpos',
                    'short_description' => 'Custom Sales For Xpos',
                    'sku' => self::CUSTOM_SALES_PRODUCT_SKU,
                    'weight' => '0',
                    'news_from_date' => '',
                    'news_to_date' => '',
                    'status' => '2',
                    'url_key' => '',
                    'visibility' => '1',
                    'country_of_manufacture' => '',
                    'price' => '10',
                    'special_price' => '',
                    'special_from_date' => '',
                    'special_to_date' => '',
                    'msrp_enabled' => '2',
                    'msrp_display_actual_price_type' => '4',
                    'msrp' => '',
                    'tax_class_id' => '2',
                    'meta_title' => '',
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'image' => 'no_selection',
                    'small_image' => 'no_selection',
                    'thumbnail' => 'no_selection',
                    'media_gallery' =>
                        array(
                            'images' => '[]',
                            'values' => '{"image":null,"small_image":null,"thumbnail":null}',
                        ),
                    'is_recurring' => '0',
                    'recurring_profile' => '',
                    'custom_design' => '',
                    'custom_design_from' => '',
                    'custom_design_to' => '',
                    'custom_layout_update' => '',
                    'page_layout' => '',
                    'options_container' => 'container2',
                    'use_config_gift_message_available' => '1',
                    'gift_wrapping_available' => '0',
                    'gift_wrapping_price' => '',
                    'stock_data' =>
                        array(
                            'manage_stock' => '1',
                            'original_inventory_qty' => '999999',
                            'qty' => '999999',
                            'use_config_min_qty' => '1',
                            'use_config_min_sale_qty' => '1',
                            'use_config_max_sale_qty' => '1',
                            'is_qty_decimal' => '0',
                            'is_decimal_divided' => '0',
                            'use_config_backorders' => '1',
                            'use_config_notify_stock_qty' => '1',
                            'use_config_enable_qty_increments' => '1',
                            'use_config_qty_increments' => '1',
                            'is_in_stock' => '1',
                        ),
                ),
            'category_ids' => '',
            'page' => '1',
            'limit' => '20',
            'in_products' => '',
            'entity_id' => '',
            'name' => 'Custom Sales',
            'type' => 'simple',
            'set_name' => 'Custom Sales',
            'status' => '2',
            'visibility' => '1',
            'sku' => self::CUSTOM_SALES_PRODUCT_SKU,
            'price' =>
                array(
                    'from' => '',
                    'to' => '',
                ),
            'position' =>
                array(
                    'from' => '',
                    'to' => '',
                ),
            'links' =>
                array(
                    'related' => '',
                ),
            'affect_product_custom_options' => '1',
        );
        $request = new  Zend_Controller_Request_Http();
        $request->setParams($productData);
        $request->setPost($productData);
        $request->setParam('set', $this->getAttributeSetForCustomSalesProduct());
        $response = new Zend_Controller_Response_Http();
        $controller = new Mage_Adminhtml_Catalog_ProductController($request, $response);
        $controller->saveAction();
    }


    /**
     * PERFECT CODE
     * @return int
     */
    public function getAttributeSetForCustomSalesProduct() {
        $eavEntityTypeModel = Mage::getModel('eav/entity_type');
        $productEntityTypeId = $eavEntityTypeModel->loadByCode('catalog_product')->getId();
        $eavAttributeSetCollection = Mage::getModel('eav/entity_attribute_set')->getCollection();
        $eavAttributeSetCollection->addFieldToFilter('attribute_set_name', 'Default')->addFieldToFilter('entity_type_id', $productEntityTypeId);
        return $eavAttributeSetCollection->getFirstItem()->getId();
    }
}
