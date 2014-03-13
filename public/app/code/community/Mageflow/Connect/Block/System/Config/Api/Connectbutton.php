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
class Mageflow_Connect_Block_System_Config_Api_Connectbutton
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
                "Connect MageFlow API"
            ),
            'class'       => 'disabled',
            'comment'     => "",
            'id'          => "btn_connect",
            'onclick'     => sprintf(
                "if(!jQuery(this).hasClass('disabled'))
                jQuery.ajax('%s', {type:'GET', data:mageflow.getCredentials(),
                 success:function(response){var e=
                 new jQuery.Event('populate_company_select');
                 e.custom_data=response; jQuery(document).trigger(e);}})",
                Mage::helper("adminhtml")->getUrl(
                    'mageflow_connect/ajax/getcompanies'
                ) . '?isAjax=true'
            ),
            'after_html'  => (Mage::getStoreConfig(
                'mageflow_connect/general/api/is_connected'
            ) ? '' : $afterHtml),
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
        <div style="margin-top:5px;">
MageFlow API connection set-up is <strong>simple</strong>:
            <ol style="list-style: decimal inside;">
                <li>Enter your API keys (above)</li>
                <li>Click Connect MageFlow API button</li>
                <li>Select one of your companies that you
                want this instance to be connected to</li>
                <li>Select one of your company's project
                that you want this instance to be connected to</li>
            </ol>
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
            <tr id="row_connect">
                <td>&nbsp;</td>
                <td>
                    <div style="margin-top:5px;">
                        Don't have an account yet?
                        <a href="{$link}">Click here to signup!</a>
                    </div>

                </td>
            </tr>
HTML;

        return $html;
    }

    protected function getSignupUrl()
    {
        return "http://www.mageflow.com/signup/";
    }

}
