<?php

class SM_XPos_Adminhtml_XposproductController extends Mage_Adminhtml_Controller_Action {
    const XPOS_DETECT_NEW_CACHE_DATA_KEY = 'XPOS_DETECT_NEW_CACHE_DATA_KEY';

    public function countAllAction() {
        $warehouseId = $this->getRequest()->getParam('warehouse');
        $is_integrate_with_MWH = Mage::helper('xpos')->isWarehouseIntegrate();
        if (!empty($is_integrate_with_MWH) && !empty($warehouseId)) {
            $this->_getSession()->setWarehouseId($warehouseId);
        } else {
            $warehouseId = false;
        }

        $countNumber = Mage::helper('xpos/product')->getCountAllProducts($warehouseId);

        $res = json_encode(array('number' => $countNumber));

        $this->getResponse()->setBody($res);
    }

    public function loadAction() {

        $page = $this->getRequest()->getParam('page');
        $warehouseId = $this->getRequest()->getParam('warehouse');
        $storeView = $this->getRequest()->getParam('storeId');
        $is_integrate_with_MWH = Mage::helper('xpos')->isWarehouseIntegrate();
        if (!empty($is_integrate_with_MWH) && !empty($warehouseId)) {
            $this->_getSession()->setWarehouseId($warehouseId);
        } else {
            $warehouseId = '';
        }
        if (!!$storeView) {
            Mage::getSingleton('adminhtml/session')->setCurrentStoreView($storeView);
        }


        $cacheKey = 'xpos_' . $page . '_' . $warehouseId . '_' . $storeView;
        $cacheProducts = Mage::app()->getCache()->load($cacheKey);
        if (!$cacheProducts) {
            $result = Mage::helper('xpos/product')->getProductList($this, $page, $warehouseId);
            if ($result['totalLoad'] == 0) {
                if (!$this->getCkeRInServer()) {
                    $this->setCkeRinServer(md5(microtime()));
                }
                $result['xpos_cache_key'] = $this->getCkeRInServer();
            }
            $cacheProducts = Mage::helper('core')->jsonEncode($result);
            Mage::app()->getCache()->save($cacheProducts, $cacheKey, array("xpos_cache"), 2592000);
        }
        $this->getResponse()->setBody($cacheProducts);
    }

    public function searchAction() {

        $dataAll = $this->getRequest()->getParams();
        $query = $this->getRequest()->getParam('query');
        // $warehouseId = $this->getRequest()->getParam('warehouse');
        $warehouseId = Mage::getSingleton('adminhtml/session')->getWarehouseId();
        $billingAdd = new Varien_Object();
        $billingAdd->setCountryId($dataAll['billingCountryId'])
            ->setRegionId($dataAll['billingRegionId'])
            ->setPostcode($dataAll['billingPostCode']);
        $shippingAdd = new Varien_Object();
        $shippingAdd->setCountryId($dataAll['shippingCountryId'])
            ->setRegionId($dataAll['shippingRegionId'])
            ->setPostcode($dataAll['shippingPostCode']);
        $customerId = $dataAll['customerId'];
        $is_integrate_with_MWH = Mage::helper('xpos')->isWarehouseIntegrate();
        if (!empty($is_integrate_with_MWH) && !empty($warehouseId)) {
            $this->_getSession()->setWarehouseId($warehouseId);
        } else {
            $warehouseId = '';
        }

        $result = Mage::helper('xpos/product')->searchProduct($this, $query, $warehouseId, $billingAdd, $shippingAdd, $customerId);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        //echo Mage::helper('core')->jsonEncode($result);
    }

    const IAYZ_CACHE = 'IAYZ_CACHE'; //  key.store = dataStore
    const REAL_TIME_CACHE = 'REAL_TIME_CACHE'; // store key
    const NEW_KEY = 'XPOS_NEW_KEY';
    public $_processPro = null;

    public function checkRealTimeProductAction() {
        if (!$this->getNewKeyInServer()) {
            if (!$this->getCkeRInServer()) {
                /*TODO: Not existed cache s-AP*/
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'update_real_time' => false,
                    'need_update_allProduct' => true,
                    'reason' => 'Server hasn\'t new data & server not existed AP. Need load all product',
                    'storeId_C' => $this->getRequest()->getParam('storeId'),
                    'storeId_S' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                    'keyCheckRealTimeInSv' => $this->getCkeRInServer(),
                )));
                Mage::helper('xpos/realTimeProduct')->resetRealTimeUpdate();

                return;
            } else {
                /*TODO: Not existed new product, but c-AP diff s-AP*/
                if ($this->getCkeRInServer() != $this->getCkeR()) {
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'update_real_time' => false,
                        'need_update_allProduct' => true,
                        'reason' => 'Server not has new data & c-AP diff s-AP. Need reload all Product',
                        'storeId_C' => $this->getRequest()->getParam('storeId'),
                        'storeId_S' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                    )));
                    Mage::helper('xpos/realTimeProduct')->resetRealTimeUpdate();

                    return;
                } else {
                    /*TODO: Not existed new product, c-AP = s-AP*/
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'update_real_time' => false,
                        'need_update_allProduct' => false,
                        'reason' => 'Server not has new data',
                        'storeId_C' => $this->getRequest()->getParam('storeId'),
                        'storeId_S' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                    )));

                    return;
                }
            }
        } else {
            $keyCkeR = $this->getCkeR();
            $positionNew = $this->getNewKeyInServer();
            if ($this->getCkeRInServer() != $positionNew) {
                if (!$this->getCkeRInServer()) {
                    /*TODO: s-AP not existed, must create AP*/
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'update_real_time' => true,
                        'need_update_allProduct' => true,
                        'reason' => 'Server has new product, but not existed s-AP',
                        'storeId_C' => $this->getRequest()->getParam('storeId'),
                        'storeId_S' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                    )));
                    Mage::helper('xpos/realTimeProduct')->resetRealTimeUpdate();

                    return;
                } elseif($positionNew) {
                    //TODO: Generate new product data
                    $productUpdated = array();
                    $dataProductNewGenerate = Mage::helper('xpos/realTimeProduct')->getProductForUseReload();
                    foreach ($dataProductNewGenerate as $productId => $storeId) {
                        if (in_array($this->getRequest()->getParam('storeId'), $storeId))
                            continue;
                        $productData = $this->loadProcessPro()->getProduct($this, $productId, $this->getRequest()->getParam('storeId'), null);
                        $this->saveProDataIayz($productData['productInfo']);
                        $productUpdated[$productId] = $storeId;
                    }

                    // reset product updated in current store
                    Mage::helper('xpos/realTimeProduct')->updateListProductFinished($productUpdated, $this->getRequest()->getParam('storeId'));
                    // set current position to real_time key in current store
                    $this->setCkeRinServer($positionNew);
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'update_real_time' => true,
                        'need_update_allProduct' => false,
                        'reason' => 'Server has new key data & IAYZ-CACHED not catch up',
                        'storeId_C' => $this->getRequest()->getParam('storeId'),
                        'storeId_S' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                        'server_cacheKey' => $positionNew,
                        'dataProduct' => $this->getProDataIayz(),
                    )));

                    return;
                }
            } else {
                if ($this->getCkeR() != $positionNew) {
                    /*TODO: Has new data, IAYZ-CAHED updated, client not catch up*/
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'update_real_time' => true,
                        'need_update_allProduct' => false,
                        'reason' => 'Server has exist iayz cache & iayz cached catch up',
                        'storeId_C' => $this->getRequest()->getParam('storeId'),
                        'storeId_S' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                        'server_cacheKey' => $positionNew,
                        'client_cacheKey' => $keyCkeR,
                        'dataProduct' => $this->getProDataIayz(),
                    )));

                    return;
                } else {
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                        'update_real_time' => false,
                        'need_update_allProduct' => false,
                        'reason' => 'Nothing to do',
                        'storeId_C' => $this->getRequest()->getParam('storeId'),
                        'storeId_S' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                        'server_cachekey' => $positionNew,
                        'client_cacheKey' => $keyCkeR,
                    )));

                    return;
                }
            }
        }
    }


    public function loadProcessPro() {
        if (is_null($this->_processPro)) {
            $this->_processPro = Mage::helper('xpos/product');
        }

        return $this->_processPro;
    }

    public function getCkeRInServer() {

        return Mage::app()->getCache()->load(self::REAL_TIME_CACHE . $this->getRequest()->getParam('storeId'));
    }

    public function setCkeRinServer($key) {
        Mage::app()->getCache()->save($key, self::REAL_TIME_CACHE . $this->getRequest()->getParam('storeId'), array("xpos_cache"), 2592000);
    }

    public function getNewKeyInServer() {
        return Mage::app()->getCache()->load(self::NEW_KEY);
    }

    public function getCkeR() {
        return $this->getRequest()->getParam('cKeR');
    }

    public function getProDataIayz() {

        $productData = Mage::app()->getCache()->load(self::IAYZ_CACHE . $this->getRequest()->getParam('storeId'));
        if (!$productData) {
            return null;
        } else {
            return Mage::helper('core')->jsonDecode($productData);
        }
    }

    public function saveProDataIayz($data) {
        if (is_array($data)) {
            if (is_null($this->getProDataIayz())) {
                Mage::app()->getCache()->save(Mage::helper('core')->jsonEncode($data), self::IAYZ_CACHE . $this->getRequest()->getParam('storeId'), array("xpos_cache"), 9999999);

                return true;
            } else {
                $oldData = $this->getProDataIayz();
                $newIayzProductData = array_replace_recursive($oldData, $data);
                Mage::app()->getCache()->save(Mage::helper('core')->jsonEncode($newIayzProductData), self::IAYZ_CACHE . $this->getRequest()->getParam('storeId'), array("xpos_cache"), 9999999);

                return true;
            }
        } else {
            return false;
        }
    }

    /*-------------------------------------------------------------------------------------------------------------------------------------------------------*/
    public function getProOpAction() {
        $beforeStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore(Mage::helper('xpos/product')->getCurrentSessionStoreId());
        $productId = $this->getRequest()->getParam('productId');
        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::helper('xpos/product')->getCurrentSessionStoreId())
            ->load($productId);
        $op = Mage::getModel('xpos/iayz')->getProOp($this, $product);
        if ($op == null) {
            $data = array(
                'options' => '0',
            );
        } else {
            $data = array(
                'options' => $op,
            );
        }
        Mage::app()->setCurrentStore($beforeStore);
        $result = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setBody($result);
    }

    public function setSessionStoreViewAction() {
        $storeBefore = Mage::app()->getStore();
        Mage::app()->setCurrentStore(Mage::helper('xpos/product')->getCurrentSessionStoreId());
        try {
            $needReaload = false;
            $storeId = $this->getRequest()->getParam('storeId');
            $currentStoreIdInServer = Mage::helper('xpos/product')->getCurrentSessionStoreId();
            if ($storeId != $currentStoreIdInServer) {
                $needReaload = true;
            }
            Mage::getSingleton('adminhtml/session')->setCurrentStoreView($storeId);
            Mage::getSingleton('adminhtml/session_quote')->setStoreId($storeId);

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    'status' => 'true',
                    'currentStoreFromeServer' => Mage::helper('xpos/product')->getCurrentSessionStoreId(),
                    'needReload' => $needReaload,
                    'priceFormat' => (Mage::app()->getLocale()->getJsPriceFormat()),
                    'beforeCheck' => $currentStoreIdInServer,
                    'storeClient' => $storeId)
            ));
            Mage::app()->setCurrentStore($storeBefore);

        } catch (Exception $e) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'status' => $e->getMessage())));
        }
    }

    public function getSessionStoreViewAction() {
        $currentStoreViewId = Mage::getSingleton('adminhtml/session')->getCurrentStoreView();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
            'storeId' => $currentStoreViewId,
        )));
    }

    protected function _isAllowed() {
        return true;
    }


    /*TODO: Integrate Webtex GiftRegistry*/
    public function getProductByGiftRegistryIdAction() {
        $params = $this->getRequest()->getParams();
        $giftRegistryId = $params['giftRegistryId'];
        $collection = Mage::getModel('xpos/integrate')->getItemsCollectionByGiftRegestryId($giftRegistryId);
        $dataProduct = array();
        if (!$collection) {
            $dataProduct = array(
                'noData' => true,
            );
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($dataProduct));
        }
        foreach ($collection as $item) {
            $dataProduct[] = array(
                'productId' => $item->getData('product_id'),
                'priority' => $item->getData('priority'),
                'desired' => $item->getData('desired'),
                'received' => $item->getData('received'),
                'purchased' => $item->getData('purchased')
            );
        }
        Mage::getSingleton('adminhtml/session')->setGiftRegistry($giftRegistryId);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($dataProduct));
    }


}
