<?php

/**
 * Connect
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
 * Connect
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Block_System_Config_Api_Oauthbutton
    extends Mageflow_Connect_Block_System_Config_Api_Basebutton
{

    /**
     * Creates "connect to api" button
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
                "Set up Oauth"
            ),
            'class'       => '',
            'comment'     => 'Set up Oauth connection from MageFlow to Magento',
            'id'          => "btn_oauth",
            'data-url'    => Mage::helper("adminhtml")->getUrl(
                'mageflow_connect/ajax/createoauth'
            ),
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
