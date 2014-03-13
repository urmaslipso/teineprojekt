<?php

/**
 * ShellTest
 *
 * PHP version 5
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
namespace Mageflow\Connect;

require_once MODULEROOT . '/src/Runner.php';
/**
 * ShellTest
 *
 * @category Deployment
 * @package  Application
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/license/mageflow.txt
 *
 */
class ShellTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \Connect\Runner
     */
    private $object;
    /**
     * Class constructor
     *
     * @return ShellTest
     */
    public function __construct()
    {
        $this->object = new \Connect\Runner();
        return $this;
    }

    public function setUp()
    {
        parent::setUp();
    }
    public function tearDown()
    {
        parent::tearDown();
    }
    public function testShell(){
        $this->assertInstanceOf('Connect\Runner', $this->object);
    }
    public function testListPackages(){
        $retval = $this->object->run(array('list-packages'));
        $this->assertNotEmpty($retval);

    }
}
