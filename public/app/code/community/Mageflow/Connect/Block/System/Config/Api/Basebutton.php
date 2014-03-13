<?php

/**
 * BaseButton
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
 * BaseButton class that is used to generate buttons
 * in admin config section
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
abstract class Mageflow_Connect_Block_System_Config_Api_Basebutton
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_dummy;
    protected $_fieldRenderer;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $buttonBlock = $element->getForm()->getParent()->getLayout()
            ->createBlock('adminhtml/widget_button');
        $data = $this->getButtonData($buttonBlock);

        $id = $element->getHtmlId();

        $html = sprintf(
            '<tr><td class="label"><label for="%s">%s</label></td>',
            $id, $element->getLabel()
        );
        $html .= sprintf(
            '<td class="value">%s</td>',
            $buttonBlock->setData($data)->toHtml()
        );
        $html .= '</tr>';
        return $html;
    }


    public abstract function getButtonData($buttonBlock);

    protected function _getDummyElement()
    {
        if (empty($this->_dummy)) {
            $this->_dummy = new Varien_Object(
                array('show_in_default' => 1,
                                                    'show_in_website' => 0,
                                                    'show_in_store'   => 0)
            );
        }
        return $this->_dummy;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton(
                'adminhtml/system_config_form_field'
            );
        }
        return $this->_fieldRenderer;
    }

}
