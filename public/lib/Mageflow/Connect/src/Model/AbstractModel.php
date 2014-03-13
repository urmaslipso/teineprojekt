<?php

/**
 * AbstractModel
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */

namespace Mageflow\Connect\Model;

/**
 * AbstractModel
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
class AbstractModel
{

    /**
     * Class constructor
     *
     * @return AbstractModel
     */
    public function __construct()
    {
        return $this;
    }

    /**
     *
     * @param type $name
     * @param type $arguments
     */
    public function __call($name, $arguments)
    {
        $propertyName = '_' . lcfirst(substr($name, 3));
        if ( substr($name, 0, 3) == 'set' )
        {
            $this->$propertyName = $arguments[0];
        } elseif ( substr($name, 0, 3) == 'get' )
        {
            return $this->$propertyName;
        }
    }

}
