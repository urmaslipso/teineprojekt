<?php
/**
 *
 * System.php
 *
 * @author  sven
 * @created 02/03/2014 10:13
 */

class Mageflow_Connect_Helper_System extends Mage_Core_Helper_Abstract
{

    /**
     * Clean all Magento caches
     */
    public function cleanCache()
    {
        try {
            $allTypes = Mage::app()->useCache();
            foreach ($allTypes as $type => $blah) {
                Mage::app()->getCacheInstance()->cleanType($type);
            }
        } catch (Exception $e) {
            Mage::helper("mageflow_connect")->log($e->getMessage());
            Mage::helper("mageflow_connect")->log($e->getTraceAsString());
        }
    }

    /**
     * return current cache setting
     * or set cache settings to $settingsArray
     *
     * @param array $settingsArray
     *
     * @return array
     */
    public function cacheSettings($settingsArray = null)
    {
        /*
         * a sample data shoulb nice here
         *
                "block_html": "0",
                "collections": "0",
                "config": "0",
                "config_api": "0",
                "config_api2": "0",
                "eav": "0",
                "layout": "0",
                "translate": "0"
        */
        $currentSettingsArray = Mage::app()->useCache();
        if (is_null($settingsArray)) {
            return $currentSettingsArray;
        }
        if (array_key_exists('all', $settingsArray)) {
            foreach ($currentSettingsArray as $key => $setting) {
                $currentSettingsArray[$key] = $settingsArray['all'];
            }
        } else {
            foreach ($settingsArray as $key => $setting) {
                $currentSettingsArray[$key] = $setting;
            }
        }
        $this->cleanCache();
        Mage::app()->saveUseCache($currentSettingsArray);
        $this->cleanCache();
        $currentSettingsArray = Mage::app()->useCache();
        Mage::helper('mageflow_connect/log')->log(
            sprintf(
                'Applied cache settings: %s',
                print_r($currentSettingsArray, true)
            )
        );
        return $currentSettingsArray;

    }
}