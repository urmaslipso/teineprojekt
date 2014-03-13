<?php

/**
 * Collection
 *
 * PHP version 5
 *
 * @category   Deployment
 * @package    Application
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
//namespace Application;

/**
 * Collection
 *
 * @category   Deployment
 * @package    Application
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Model_Data_Collection extends Varien_Data_Collection
{

    /**
     * Class constructor
     *
     * @return Collection
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Adds functionality to Varien_Data_Collection for
     * adding collection of items to current collection
     *
     * @param Varien_Data_Collection $itemList
     *
     * @return Mageflow_Connect_Model_Data_Collection
     */
    public function addItems(Varien_Data_Collection $itemList)
    {
        foreach ($itemList->getItems() as $item) {
            $this->addItem($item);
        }
        return $this;
    }

}
