<?php

/**
 * V1
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
 * V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_Api2_Help_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Cms
{

    /**
     * Class constructor
     *
     * @return \Mageflow_Connect_Model_Api2_Help_Rest_Admin_V1
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * retrieve
     *
     * @return array
     */
    public function _retrieve()
    {
        $config = $this->getConfig();
        $out = array();
        $out['info']
            = 'NB! Please mind the original Magento resource
            names as these may not be correct in this listing';
        foreach ($config->getResources() as $name => $resource) {
            $attributes = array();
            foreach (
                $config->getResourceAttributes($name) as $attributeNode =>
                $attributeText
            ) {
                $attributes[] = $attributeNode;
            }
            $routesArr = array();
            $routes = $this->getConfig()->getNode(
                'resources/' . $name . '/routes'
            );
            foreach ($routes->children() as $name => $route) {
                $item = array(
                    'name'  => $name,
                    'route' => (string)$route->route
                );
                $routesArr[] = $item;
            }
            $resourceArr = array(
                'resource_type' => $name,
                'routes'        => $routesArr,
                'attributes'    => $attributes
            );
            $out['resources'][] = $resourceArr;
        }
        return $out;
    }

}
