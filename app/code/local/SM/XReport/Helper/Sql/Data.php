<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/9/15
 * Time: 12:09 PM
 */
class SM_XReport_Helper_Sql_Data extends Mage_Core_Helper_Abstract {
    /**
     * Zend_Date date format for Mysql requests
     */
    const MYSQL_ZEND_DATE_FORMAT = 'yyyy-MM-dd HH:mm:ss';
    const FRONTEND_STRFTIME_FORMAT = '%b %d, %Y';
    const FRONTEND_ZEND_DATE_FORMAT = 'MMM dd, YYYY';
    const EXCEPTION_MESSAGE_LOOP = 'Unexpected loop';
    const DEFAULT_CUSTOM_DATE_RANGE = 'this_month';

    const NEW_PROTOTYPE_REQUIRED = '_awaff_new_prototype_required';

    protected $_weekdays;
    protected $_locale;

    public function getGridTemplate() {
        return self::GRID_TEMPLATE;
    }

    /**
     * Creates a connection to resource whenever needed
     *
     * @param string $connectionName
     *
     * @return mixed
     */
    protected function _getConnection($connectionName) {
        return $this->_getResource()->getConnection($connectionName);
    }

    /**
     * Retrieves resource
     *
     * @return Mage_Core_Model_Resource
     */
    protected function _getResource() {
        return Mage::getSingleton('core/resource');
    }

    /**
     * Retrieve connection for read data
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    public function getReadAdapter() {
        return $this->_getConnection('core_read');
    }

    /**
     * Retrieve connection for write data
     *
     * @return  Varien_Db_Adapter_Pdo_Mysql
     */
    public function getWriteAdapter() {
        return $this->_getConnection('core_write');
    }

    public function setNeedMainTableAlias($need) {
        Mage::register('aw_ar_need_main_table_alias', $need, true);
        return $this;
    }

    public function getNeedMainTableAlias() {
        return Mage::registry('aw_ar_need_main_table_alias');
    }

    /**
     * Retrieves Default Custom Date Range
     *
     * @return string
     */
    public function getDefaultCustomDateRange() {
        return self::DEFAULT_CUSTOM_DATE_RANGE;
    }

    protected function _getFirstWeekDay() {
        return Mage::getStoreConfig('general/locale/firstday') ? Mage::getStoreConfig('general/locale/firstday') : 0;
    }

    protected function _getLastWeekDay() {
        $firstDayNum = $this->_getFirstWeekDay();
        $lastDayNum = $firstDayNum + 6;
        return $lastDayNum > 6 ? $lastDayNum - 7 : $lastDayNum;
    }

    protected function _getWeekDayName($index) {
        $days = array(
            0 => 'sun',
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
        );
        return isset($days[$index]) ? $days[$index] : null;
    }

    /**
     * Retrieves global timezone
     *
     * @return string
     */
    public function getTimezone() {
        return Mage::app()->getStore()->getConfig('general/locale/timezone');
    }

    /**
     * Retrieves global timezone offset in seconds
     *
     * @param boolean $isMysql If true retrives mysql formmatted offset (+00:00) in hours
     *
     * @return int
     */
    public function getTimeZoneOffset($isMysql = false) {
        $date = new Zend_Date();
        $date->setTimezone($this->getTimezone());
        if ($isMysql) {
            $offsetInt = -$date->getGmtOffset();
            $offset = ($offsetInt >= 0 ? '+' : '-') . sprintf('%02.0f', round(abs($offsetInt / 3600))) . ':'
                . (sprintf('%02.0f', abs(round((abs($offsetInt) - round(abs($offsetInt / 3600)) * 3600) / 60))));
            return $offset;
        } else {
            return $date->getGmtOffset();
        }
    }

    /**
     * Make correction for store timezone
     *
     * @param string $datetime
     *
     * @return string
     */
    public function timezoneFactory($datetime) {
        $newDate = $datetime;
        try {
            $newDate = Mage::app()->getLocale()
                ->utcDate(null, $datetime, true, Varien_Date::DATETIME_INTERNAL_FORMAT)
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }

        return $newDate;
    }

    /**
     * Convert datetime to GMT timezonne
     *
     * @param string $datetime
     *
     * @return string
     */
    public function gmtTimezoneFactory($datetime) {
        $newdate = $datetime;
        try {
            $dateObj = new Zend_Date($datetime, Zend_Date::ISO_8601);
            $dateObj->setTimezone('GMT');
            $newdate = $dateObj->subSecond($this->getTimeZoneOffset())->toString(self::MYSQL_ZEND_DATE_FORMAT);
        } catch (Exception $e) {
            Mage::logException($e->getMessage());
        }
        return $newdate;
    }

    public function getOptions($gridType = null) {
        $options = array(
            array('value' => 'today', 'label' => $this->__('Today')),
            array('value' => 'yesterday', 'label' => $this->__('Yesterday')),
            array('value' => 'last_7_days', 'label' => $this->__('Last 7 days')),
            array('value' => 'last_week', 'label' => $this->__($this->getLastWeekLabel())),
            array('value' => 'last_business_week', 'label' => $this->__($this->getLastBusinessWeekLabel())),
            array('value' => 'this_month', 'label' => $this->__('This month'), 'default' => 1),
            array('value' => 'last_month', 'label' => $this->__('Last month')),
            array('value' => 'custom', 'label' => $this->__('Custom date range')),
        );

        return array_merge($options, $this->getCustomizedLabels($gridType));
    }

    protected function _rangeHash($range) {
        $str = '';
        foreach ($range as $key => $value) {
            $str .= $key . '=' . $value;
        }
        return md5($str);
    }

    protected function _prepareHumanDate($date) {
        if (!$date) {
            return '';
        }
        $dateObj = new Zend_Date($date, null, $this->getLocale()->getLocaleCode());
        return $dateObj->toString(self::FRONTEND_ZEND_DATE_FORMAT);
    }

    public function getCustomizedLabels($gridType = null) {
        $result = array();
        foreach ($this->getQueue()->getStoredRanges($gridType) as $range) {
            $result[] = array(
                'value' => 'custom_' . $this->_rangeHash($range),
                'label' => $this->__(
                    "* From %s to %s (%s)", $this->_prepareHumanDate(@$range['report_from']),
                    $this->_prepareHumanDate(@$range['report_to']), $this->getPeriodLabel(@$range['report_period'])
                ),
            );
        }
        return $result;
    }


    /**
     * Retrieves Last Week Label
     *
     * @return string
     */
    public function getLastWeekLabel() {
        $firstDayNum = $this->_getFirstWeekDay();
        $lastDayNum = $this->_getLastWeekDay();
        return $this->__('Last week') . ' (' . substr($this->getWeekday($firstDayNum), 0, 3) . ' - ' . substr(
            $this->getWeekday($lastDayNum), 0, 3
        ) . ')';
    }

    /**
     * Retrieves array with Week Day Nums
     *
     * @return array
     */
    protected function _getBusinessWeekDays() {
        $week = array(0, 1, 2, 3, 4, 5, 6);
        $week = array_diff($week, explode(',', Mage::getStoreConfig('general/locale/weekend')));
        $bWeek = array();
        foreach ($week as $dayNum) {
            $bWeek[] = $dayNum;
        }
        return $bWeek;
    }

    /**
     * Retrieves Last Business Week Label
     *
     * @return string
     */
    public function getLastBusinessWeekLabel() {
        $bWeek = $this->_getBusinessWeekDays();

        /** @var First Week Day */
        $fWD = ucfirst($this->_getWeekDayName($bWeek[0]));
        /** @var First Week Day */
        $lWD = ucfirst($this->_getWeekDayName($bWeek[count($bWeek) - 1]));

        return "Last business week ({$fWD} - {$lWD})";
    }

    public function getDateFormatWithLongYear() {
        if (!method_exists($this->getLocale(), 'getDateFormatWithLongYear')) {
            return preg_replace(
                '/(?<!y)yy(?!y)/', 'yyyy',
                $this->getLocale()->getTranslation(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, 'date')
            );
        } else {
            return $this->getLocale()->getDateFormatWithLongYear();
        }
    }

    /**
     * Last week values fix #7225 (Use Zend_Date)
     *
     * @param Zend_Date $nowDate
     *
     * @return Varien_Object
     */
    public function getLastWeekRange(Zend_Date $nowDate = null) {
        $format = $this->getDateFormatWithLongYear();
        $locale = $this->getLocale()->getLocaleCode();
        $zendDay = $nowDate ? $nowDate : new Zend_Date(null, null, $locale);
        while ($this->_getWeekDayNum($zendDay) != $this->_getFirstWeekDay()) {
            $zendDay->subDay(1);
        }
        $zendDay->subDay(1);
        $lastDay = $zendDay->toString($format);
        $zendDay->subDay(6);
        $firstDay = $zendDay->toString($format);
        return new Varien_Object(
            array(
                'from' => $firstDay,
                'to' => $lastDay
            )
        );
    }

    /**
     * Retrieves digit of week from Zend Date
     *
     * @param Zend_Date $zendDate
     *
     * @return string
     */
    protected function _getWeekDayNum(Zend_Date $zendDate) {
        return $zendDate->toString(Zend_Date::WEEKDAY_DIGIT);
    }

    /**
     * Last business week date range
     *
     * @param Zend_Date $nowDate
     *
     * @return Varien_Object
     */
    public function getLastBusinessWeekRange(Zend_Date $nowDate = null) {
        $format = $this->getDateFormatWithLongYear();
        $locale = $this->getLocale()->getLocaleCode();

        $zendDay = $nowDate ? $nowDate : new Zend_Date(null, null, $locale);

        $bWeek = $this->_getBusinessWeekDays();
        $zendDay->subDay(1);
        $i = 10;
        while (($this->_getWeekDayNum($zendDay) != $bWeek[count($bWeek) - 1]) && (--$i > 0)) {
            $zendDay->subDay(1);
            if ($i == 1) {
                return new Varien_Object();
            }
        }

        $lastDay = $zendDay->toString($format);
        $i = 10;
        while (($this->_getWeekDayNum($zendDay) != $bWeek[0]) && (--$i > 0)) {
            $zendDay->subDay(1);
            if ($i == 1) {
                return new Varien_Object();
            }
        }
        $firstDay = $zendDay->toString($format);
        return new Varien_Object(
            array(
                'from' => $firstDay,
                'to' => $lastDay
            )
        );
    }

    public function getRangeValues() {
        $ctz = date_default_timezone_get();
        $mtz = Mage::app()->getStore()->getConfig('general/locale/timezone');
        @date_default_timezone_set($mtz);

        $res = array(
            array(
                'key' => 'today',
                'from' => strftime(self::FRONTEND_STRFTIME_FORMAT),
                'to' => strftime(self::FRONTEND_STRFTIME_FORMAT),
            ),
            array(
                'key' => 'yesterday',
                'from' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime('yesterday')),
                'to' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime('yesterday')),
            ),
            array(
                'key' => 'last_7_days',
                'from' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime('- 6 days')),
                'to' => strftime(self::FRONTEND_STRFTIME_FORMAT),
            ),
            array(
                'key' => 'last_week',
                'from' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime($this->getLastWeekRange()->getFrom())),
                'to' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime($this->getLastWeekRange()->getTo())),
            ),
            array(
                'key' => 'last_business_week',
                'from' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime($this->getLastBusinessWeekRange()->getFrom())),
                'to' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime($this->getLastBusinessWeekRange()->getTo())),
            ),
            array(
                'key' => 'this_month',
                'from' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime(date('m/01/y'))),
                'to' => strftime(self::FRONTEND_STRFTIME_FORMAT),
            ),
            array(
                'key' => 'last_month',
                'from' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime(date('m/01/y', strtotime('last month')))),
                'to' => strftime(self::FRONTEND_STRFTIME_FORMAT, strtotime(date('m/01/y') . ' - 1 day')),
            ),
        );

        @date_default_timezone_set($ctz);
        $res = array_merge($res, $this->getCustomizedValues());
        return $res;
    }

    public function getDateFormat() {
        return $this->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }

    /**
     * Retrieves period label
     *
     * @param string $code
     *
     * @return string
     */

    public function checkCatalogPermissionsActive() {
        return (boolean)Mage::app()->getConfig()->getNode('modules/Enterprise_CatalogPermissions/active');
    }

    /**
     * Retrieves locale
     *
     * @return Mage_Core_Model_Locale
     */
    public function getLocale() {
        if (!$this->_locale) {
            $this->_locale = Mage::app()->getLocale();
        }
        return $this->_locale;
    }


    public function getDataKey($key) {
        if ($key) {
            $in = explode("_", $key);
            $out = array();
            foreach ($in as $el) {
                $out[] = ucwords(strtolower($el));
            }
            return implode("", $out);
        }
        return 'NullKey';
    }


    public function getReloadKeys() {
        return array(
            array('value' => 'qty', 'label' => 'Quantity'),
            array('value' => 'total', 'label' => 'Total'),
        );
    }

    public function setChartKeys($data, $key = 'Chart') {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        $route = 'setAwChartKeys' . $key;
        $session->$route($data);
        return $this;
    }

    public function getChartKeys($key = 'Chart') {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        $route = 'getAwChartKeys' . $key;
        return $session->$route();
    }

    public function setChartData($data, $key = 'Chart') {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        $route = 'setAwChartData' . $key;
        $session->$route($data);
        return $this;
    }

    public function getChartData($key = 'Chart') {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        $route = 'getAwChartData' . $key;
        return $session->$route();
    }

    public function setChartLabels($data, $key = 'Chart') {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        $route = 'setAwChartLabels' . $key;
        $session->$route($data);
        return $this;
    }

    public function getChartLabels($key = 'Chart') {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        $route = 'getAwChartLabels' . $key;
        return $session->$route();
    }

    public function setSkus($data) {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        $session->setAwSkus($data);
        return $this;
    }

    public function getSkus() {
        $session = Mage::getSingleton('core/session', array('name' => 'backend'))->start();
        return $session->getAwSkus();
    }

    public function getOrderItemBySku($sku) {
        $flatOrderItems = Mage::getModel('sales/order_item');
        if ($flatOrderItems && $flatOrderItems->getCollection()) {
            $flatOrderItems = $flatOrderItems->getCollection();
            $flatOrderItems->addFieldToFilter('sku', array('eq' => $sku));
            if ($flatOrderItems->getSize()) {
                return $flatOrderItems->getFirstItem();
            }
        }
        return false;
    }

    public function getProductSkuBySku($sku) {
        if ($productId = Mage::getModel('catalog/product')->getIdBySku($sku)) {
            return Mage::getModel('catalog/product')->load($productId)->getSku();
        } elseif ($productId = Mage::getModel('catalog/product')->getIdBySku($this->getDisoptedSku($sku))) {
            return $sku;
        } elseif ($this->getOrderItemBySku($sku)) {
            return $sku;
        }
        return '';
    }

    /*
    * Intelegent SKU extraction
    */
    public function getDisoptedSku($sku) {
        $arr = explode('-', $sku);
        while (count($arr) > 1 && !($productId = Mage::getModel('catalog/product')->getIdBySku(implode('-', $arr)))) {
            unset($arr[count($arr) - 1]);
        }
        return implode('-', $arr);
    }

    /**
     * Get Product Name by Product Sku
     *
     * @param string $sku
     *
     * @return string
     */
    public function getProductNameBySku($sku) {
        if ($productId = Mage::getModel('catalog/product')->getIdBySku($sku)) {
            try {
                return Mage::getModel('catalog/product')->load($productId)->getName();
            } catch (Exception $e) {
                $this->addReportError($sku . ": " . $e->getMessage());
                return '';
            }
        } elseif ($productId = Mage::getModel('catalog/product')->getIdBySku($this->getDisoptedSku($sku))) {
            try {
                return Mage::getModel('catalog/product')->load($productId)->getName() . " ({$sku})";
            } catch (Exception $e) {
                $this->addReportError($sku . ": " . $e->getMessage());
                return '';
            }
        } elseif ($product = $this->getOrderItemBySku($sku)) {
            return $product->getName() ? $product->getName() . ' (' . $sku . ')' : $sku;
        }
        return '';
    }

    public function getWeekday($weekday) {
        if (!$this->_weekdays) {
            $this->_weekdays = Mage::app()->getLocale()->getOptionWeekdays();
        }
        foreach ($this->_weekdays as $day) {
            if ($day['value'] == $weekday) {
                return $day['label'];
            }
        }
        return '';
    }

    /**
     * Retrieves sorted chain of week day's ISO numbers
     *
     * @return array
     */
    public function getWeekChain() {
        $firstDayNum = $this->_getFirstWeekDay() + 0;
        $chain = array($firstDayNum);
        $i = ($firstDayNum != 6) ? $firstDayNum + 1 : 0;
        while ($i != $firstDayNum) {
            $chain[] = $i;
            $i = ($i != 6) ? $i + 1 : 0;
        }
        return $chain;
    }


    public function checkExtensionVersion($extensionName, $extVersion, $operator = '>=') {
        if (
            $this->isExtensionInstalled($extensionName)
            && ($version = Mage::getConfig()->getModuleConfig($extensionName)->version)
        ) {
            return version_compare($version, $extVersion, $operator);
        }
        return false;
    }

    public function updatePrototypeJS() {
        if ($this->checkExtensionVersion('Mage_Core', '1.6.0.6', '<=')) {
            Mage::register(self::NEW_PROTOTYPE_REQUIRED, true);
        }
    }

    public function isNewPrototypeRequired() {
        return Mage::registry(self::NEW_PROTOTYPE_REQUIRED);
    }


    private $_tables = array();

    /**
     * Retrieves name of table in DB
     *
     * @param string $tableName
     *
     * @return string
     */
    public function getTable($tableName) {
        if (!isset($this->_tables[$tableName])) {
            $this->_tables[$tableName] = Mage::getSingleton('core/resource')->getTableName($tableName);
        }
        return $this->_tables[$tableName];
    }
}
