<?php

/**
 * Collection
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
 * Collection
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_Resource_Extension_Collection
    extends Varien_Data_Collection
{

    protected $_originalItems = array();

    /**
     * Class constructor
     *
     * @return Collection
     */
    public function __construct(array $options = array())
    {
        return $this;
    }

    /**
     * @return type
     */
    public function getItems()
    {
        return $this->_items;
    }

    public function addFieldToFilter(array $filter = array())
    {
        return $this;
    }

    /**
     * Retrieve collection all items count.
     *
     * Overloads the original getSize because
     * we use _originalItems internaly
     *
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $this->_totalRecords
                =
                count($this->_originalItems) > 0 ? count($this->_originalItems)
                    : count($this->_items);
        }
        return intval($this->_totalRecords);
    }

    /**
     * Loads extensions from ESC
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $offset = ($this->getCurPage() - 1) * $this->getPageSize();
//        if ( $this_ ) $this->_originalItems = $this->getItems();
            $this->_originalItems = $this->_items;
            $this->_totalRecords = sizeof($this->_items);
            $this->_items = array_slice(
                $this->_items,
                $offset,
                $this->getPageSize(),
                true
            );
            $this->_isCollectionLoaded = true;
        }
        return $this;
    }

}
