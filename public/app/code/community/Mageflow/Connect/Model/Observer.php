<?php

/**
 * Observer
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Observer
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_Observer extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Collects each request's memory usage
     * to database table
     */
    public function collectMemoryUsage()
    {
        /**
         * @var Mageflow_Connect_Model_System_Info_Memory
         */
        $performanceHistoryModel = Mage::getModel(
            'mageflow_connect/system_info_performance'
        );
        $performanceHistoryModel->setRequestPath(
            Mage::app()->getFrontController()->getRequest()->getRequestUri()
        );
        $performanceHistoryModel->setMemory(memory_get_usage(true));
        $performanceHistoryModel->setSessions(
            Mage::getModel('mageflow_connect/system_info_session')
                ->getNumberOfActiveSessions()
        );
        $now = new DateTime();
        $performanceHistoryModel->setCreatedAt($now->format('c'));
        $performanceHistoryModel->setCpuLoad(
            Mage::getModel('mageflow_connect/system_info_cpu')->getSystemLoad()
        );
        $performanceHistoryModel->save();
    }

    /**
     * Saves changeset item for allowed types
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveChangesetItem(Varien_Event_Observer $observer)
    {
        if ($observer->getEvent()) {
            $dataArr = $observer->getEvent()->getData();
            $type = get_class($dataArr['object']);
            Mage::helper('mageflow_connect/log')->log('###' . $type);
            $allowedTypes = $this->getAllowedTypes();
            if (!empty($type) && in_array($type, $allowedTypes)) {
                $resourceName = $dataArr['object']->_resourceName;
                Mage::helper('mageflow_connect/log')->log($type);
                Mage::helper('mageflow_connect/log')->log($resourceName);
                $content = $dataArr['object']->getData();
                $type = str_replace('/', ':', $this->convertTypeToShort($type));
                if (!isset($content['mf_guid'])) {
                    $content['mf_guid']
                        = $dataArr['object']->_origData['mf_guid'];
                }
                $changesetItem = Mage::helper('mageflow_connect/data')
                    ->createChangesetFromItem($type, $content);
                if ($changesetItem) {
                    $changesetItem->save();
                }
//                Mage::helper('mageflow_connect/log')->log($changesetItem);
                return;
            }
        }
        return;
    }

    /**
     * convert long entity type to short
     *
     * @param $type
     *
     * @return string
     */
    public function convertTypeToShort($type)
    {
        $types = $this->getTypes();
        foreach ($types as $typeDescription) {
            if ($type == $typeDescription['type']) {
                return $typeDescription['short'];
            }
        }
        return '';
    }

    /**
     * Adds MageFlow data to every supported entity type
     */
    public function onBeforeSave(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        $dataArr = $event->getData();
        $type = get_class($dataArr['object']);
        $allowedTypes = $this->getAllowedTypes();
        if (!empty($type) && in_array($type, $allowedTypes)) {
            $now = new Zend_Date();
            $dataArr['object']->setUpdatedAt($now->toString('c'));
            if ($dataArr['object']->isObjectNew()) {
                $dataArr['object']->setCreatedAt($now->toString('c'));
                $dataArr['object']->setMfGuid(
                    Mage::helper('mageflow_connect')->randomHash()
                );
            }
            if (trim($dataArr['object']->_origData['mf_guid']) == ''
                && trim($dataArr['object']->_data['mf_guid']) == ''
            ) {
                $dataArr['object']->setMfGuid(
                    Mage::helper('mageflow_connect')->randomHash()
                );
            }
        }
    }

    /**
     * Returns list of MageFlow enabled types. It uses cache for faster lookups.
     *
     * @return array
     */
    public function getAllowedTypes()
    {
        $cacheId = md5(__METHOD__);
        $cache = Mage::app()->getCache();
        $allowedTypes = array();
        if ($cache->load($cacheId)) {
            $allowedTypes = unserialize($cache->load($cacheId));
        } else {
            $types = $this->getTypes();
            foreach ($types as $typeData) {
                if (isset($typeData['type']) && trim($typeData['type']) != '') {
                    $allowedTypes[] = $typeData['type'];
                }
            }
            $cache->save(serialize($allowedTypes), $cacheId);
        }
        return $allowedTypes;
    }

    /**
     * Loads all mageflow supported types from cache or from config
     *
     * @return array|mixed|string
     */
    public function getTypes()
    {
        $cacheId = md5(__METHOD__);
        $cache = Mage::app()->getCache();
        if ($cache->load($cacheId)) {
            $types = unserialize($cache->load($cacheId));
        } else {
            $types = Mage::app()->getConfig()->getNode(
                'default/mageflow_connect/supported_types'
            )->asArray();
            $cache->save(serialize($types), $cacheId);
        }
        return $types;
    }

    /**
     * This observer handles onControllerFrontInitBefore by Magento.
     * It checks for parameters and enables maintenance mode.
     * It let's through requests from IP-s that are in the developer whitelist.
     *
     * @param Varien_Event_Observer $observer
     */
    public function activateMaintenanceMode(Varien_Event_Observer $observer)
    {
        if (
            Mage::app()->getStore()->getConfig(
                'mageflow_connect/system/maintenance_mode'
            )
            && (!Mage::app()->getStore()->isAdmin()
                && Mage::getDesign()->getArea() !== 'adminhtml')
        ) {
            $allowIps = Mage::app()->getStore()->getConfig(
                'dev/restrict/allow_ips'
            );
            if (!is_null($allowIps)) {
                $ipWhiteList = array_map('trim', explode(',', $allowIps));
                $ip = $_SERVER['REMOTE_ADDR'];
                if (is_array($ipWhiteList) && in_array($ip, $ipWhiteList)) {
                    return;
                }
            }
            include_once MAGENTO_ROOT . '/errors/503.php';
            exit();
        }
    }

}
