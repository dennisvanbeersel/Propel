<?php

use Propel\Runtime\Connection\DebugPDO;

$wrapped = new DebugPDO($conn);
$wrapped2 = new \Propel\Runtime\Connection\DebugPDO($conn);
