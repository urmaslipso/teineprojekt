<?php

/**
 * Memory
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
 * Memory
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 *
 * @method setRequestPath(string $value)
 * @method setMemory(int $value)
 */
class Mageflow_Connect_Model_System_Info_Memory extends Varien_Object
{

    public function _construct()
    {
        parent::_construct();
    }

    /**
     * Returns integer bytes of free memory
     *
     * @return int
     */
    public function getFreeMemory()
    {
        $out = 0;
        if (function_exists('exec')) {
            if (Mage::getModel('mageflow_connect/system_info_os')->getOsType()
                == Mageflow_Connect_Model_System_Info_Os::OS_OSX
            ) {
                $cmd = "/usr/bin/top -l 1 | awk '/PhysMem:/ {print $10}'";
                $retval = exec($cmd, $out);
                $memory
                    = (int)$out[0] * 1024 * 1024; //convert megabytes to bytes
                Mage::helper('mageflow_connect/log')->log($memory);
                return $memory;
            } elseif ($this->getOsType()
                == Mageflow_Connect_Model_System_Info_Os::OS_LINUX
            ) {
                $cmd = 'free';
                $retval = exec($cmd, $out);
                Mage::helper('mageflow_connect/log')->log($out);

                if (isset($out[1])) {
                    $retval = preg_match(
                        '/^Mem:\s*(\d*)\s*(\d*)\s*(\d*).*/i',
                        $out[1],
                        $matches
                    );
                    if (is_array($matches) && sizeof($matches) > 1) {
//                        $outarr['total'] = $matches[1];
//                        $outarr['used'] = $matches[2];
//                        $outarr['free'] = $matches[3];
                        return (int)$matches[3];
                    }
                }
            } else {
                return 0;
            }
        }
    }

    /**
     * Returns int bytes of total memory
     * in the machine
     *
     * @return int
     */
    public function getTotalMemory()
    {
        if (function_exists('exec')) {
            if (Mage::getModel('mageflow_connect/system_info_os')->getOsType()
                == Mageflow_Connect_Model_System_Info_Os::OS_OSX
            ) {
                $cmd = '/usr/sbin/sysctl -n hw.memsize';
                $retval = exec($cmd, $out);
                Mage::helper('mageflow_connect/log')->log($cmd);
                $memory = (int)$out[0];
                return $memory;
            } elseif ($this->getOsType()
                == Mageflow_Connect_Model_System_Info_Os::OS_LINUX
            ) {
                return 0;
            }
        }
        return 0;
    }

}
