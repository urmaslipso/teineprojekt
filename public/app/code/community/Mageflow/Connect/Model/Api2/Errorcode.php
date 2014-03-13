<?php

/**
 * @package    Mageflow
 * @subpackage Connect
 */

/**
 * ErrorCode class holds error codes for errors that may occure
 * during usage of various API resources
 *
 * PLEASE READ THIS SOFTWARE LICENSE AGREEMENT ("LICENSE") CAREFULLY
 * BEFORE USING THE SOFTWARE. BY USING THE SOFTWARE, YOU ARE AGREEING
 * TO BE BOUND BY THE TERMS OF THIS LICENSE.
 * IF YOU DO NOT AGREE TO THE TERMS OF THIS LICENSE, DO NOT USE THE SOFTWARE.
 *
 * Full text of this license is available @license
 *
 * @license    http://mageflow.com/licenses/mfx/eula.txt MageFlow EULA
 * @version    1.0
 * @author     MageFlow
 * @copyright  2014 MageFlow http://mageflow.com/
 *
 * @package    Mageflow
 * @subpackage Connect
 * @category   Mageflow
 */
class Mageflow_Connect_Model_Api2_Errorcode
{

    //10 series - CMS Page
    const COULD_NOT_SAVE_CMS_PAGE = 10;
    //20 series - CMS Block
    const COULD_NOT_SAVE_CMS_BLOCK = 20;
    //30 series - ...

    public static $errorMessages
        = array(
            self::COULD_NOT_SAVE_CMS_PAGE  => 'Could not save CMS page',
            self::COULD_NOT_SAVE_CMS_BLOCK => 'Could not save CMS block'
        );
}