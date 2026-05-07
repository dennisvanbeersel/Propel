<?php

use Propel\Runtime\Connection\DebugPDO;

$wrapped = (new \Propel\Runtime\Connection\ConnectionWrapper($conn))->useDebug(true);
$wrapped2 = (new \Propel\Runtime\Connection\ConnectionWrapper($conn))->useDebug(true);
