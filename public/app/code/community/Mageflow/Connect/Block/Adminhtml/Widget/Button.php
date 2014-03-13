<?php
/**
 *
 * Button.php
 *
 * @author  sven
 * @created 11/07/2013 15:48
 */

class Mageflow_Connect_Block_Adminhtml_Widget_Button
    extends Mage_Adminhtml_Block_Widget_Button
{
    protected $_customAttributes = array();

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds custom attribute that will be added to final
     * button tag when rendered
     *
     * @param $name
     * @param $value
     */
    public function addAttribute($name, $value)
    {
        $this->_customAttributes[$name] = $value;
    }

    /**
     * Let's make the standard button a little bit more flexible
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = $this->getBeforeHtml() . '<button '
            . ($this->getId() ? ' id="' . $this->getId() . '"' : '')
            . ($this->getElementName() ?
                ' name="' . $this->getElementName() . '"' : '')
            . ' title="'
            . Mage::helper('core')->quoteEscape(
                $this->getTitle() ? $this->getTitle() : $this->getLabel()
            )
            . '"'
            . ($this->getType() ? ' type="' . $this->getType() . '"' : '')
            . ' class="scalable ' . $this->getClass() . ($this->getDisabled()
                ? ' disabled' : '') . '"'
            . ($this->getOnClick() ? ' onclick="' . $this->getOnClick() . '"'
                : '')
            . ($this->getStyle() ? ' style="' . $this->getStyle() . '"' : '')
            . ($this->getValue() ? ' value="' . $this->getValue() . '"' : '')
            . ($this->getDisabled() ? ' disabled="disabled"' : '');
        foreach ($this->_data as $name => $value) {
            if (substr($name, 0, 4) == 'data') {
                $html .= sprintf(' %s="%s"', $name, $value);
            }
        }
        $html .= '><span><span><span>' . $this->getLabel()
            . '</span></span></span></button>' . $this->getAfterHtml();

        return $html;
    }
}