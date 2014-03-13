<?php

/**
 * Instancebutton
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Mageflow_connect
 * @author   Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */

/**
 * Instancebutton class
 *
 * @category Deployment
 * @package  Mageflow_connect
 * @author   Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Block_System_Config_Api_Instancebutton
    extends Mageflow_Connect_Block_System_Config_Api_Basebutton
{

    /**
     * Creates "register instance" button
     *
     * @param type $buttonBlock
     *
     * @return string
     */
    public function getButtonData($buttonBlock)
    {
        $afterHtml = $this->getAfterHtml();

        $data = array(
            'label'       => Mage::helper('mageflow_connect')->__(
                "Register Instance"
            ),
            'class'       => '',
            'comment'     => 'Register Instance in MageFlow',
            'id'          => "btn_instance",
            'onclick'     =>
            "mageflow.registerInstance('" . Mage::helper("adminhtml")->getUrl(
                'mageflow_connect/ajax/registerinstance'
            ) . "?isAjax=true')",
            'after_html'  => '',
            'before_html' => $this->getBeforeHtml()
        );
        return $data;
    }

    /**
     * Returns HTML that is prepended to button
     *
     * @return string
     */
    protected function getBeforeHtml()
    {
        $html
            = <<<HTML
        <div style="    margin-top:5px;">
                Register Instance
        </div>
HTML;

        return $html;
    }

    /**
     * Returns HTML that is appended to button
     *
     * @return string
     */
    protected function getAfterHtml()
    {
        $link = $this->getSignupUrl();
        $html
            = <<<HTML

HTML;

        return $html;
    }


}