<?php

/**
 * Grid
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Grid
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Block_Adminhtml_Pullgrid_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    private $_items;

    /**
     *
     * @var Mageflow_Connect_Helper_Log
     */
    private $_logger;

    /**
     *
     * @return Mageflow_Connect_Helper_Log
     */
    private function getLogger()
    {
        if (is_null($this->_logger)) {
            $this->_logger = Mage::helper('mageflow_connect/log');
        }
        return $this->_logger;
    }

    public function __construct()
    {
        parent::__construct();
        $this->_items = Mage::getModel('mageflow_connect/data_collection');
        $this->setId('pullGrid');
        $this->setDefaultSort('id');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Returns item collection
     *
     * @return Varien_Data_Collection
     */
    public function getItems()
    {
        return $this->_items;
    }


    protected function _prepareCollection()
    {
        $collection = $this->getParentBlock()->getItemdata();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('ID'),
                 'width'  => '50px',
                 'index'  => 'id',
                 'type'   => 'text',
            )
        );
        $this->addColumn(
            'ChangeSet',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('ChangeSet'),
                 'index'  => 'changeset',
                 'type'   => 'text',
            )
        );
        $this->addColumn(
            'Type',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Type'),
                 'index'  => 'type',
                 'type'   => 'text',
            )
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('mageflow_connect')->__('Action'),
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('mageflow_connect')->__(
                             'Pull'
                         ),
                         'url'     => array('base' => '*/*/pull'),
                         'field'   => 'id'
                     ),
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'id',
                 'is_system' => true,
            )
        );

        $this->addExportType(
            '*/*/exportCsv',
            Mage::helper('customer')->__('CSV')
        );
        $this->addExportType(
            '*/*/exportXml',
            Mage::helper('customer')->__('Excel XML')
        );
        return parent::_prepareColumns();
    }

    /**
     * Returns row url
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        $url = $this->getUrl(
            '*/*/view',
            array('type' => $row->getType(), 'id' => $row->getId())
        );
        return $url;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'push',
            array(
                 'label'   => Mage::helper('mageflow_connect')->__(
                     'Pull from MageFlow'
                 ),
                 'url'     => $this->getUrl('*/*/pull'),
                 'confirm' => Mage::helper('mageflow_connect')->__(
                     'Are you sure you want
                         to pull these objects from MageFlow?'
                 )
            )
        );
        return $this;
    }

}
