<?php

namespace Mageflow\Connect;

require_once __DIR__ . '/../../../../app/Mage.php';
\Mage::app();
require_once __DIR__ . '/Runner.php';
$r = new Runner();
$r->run($argv);