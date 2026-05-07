<?php

use Propel\Runtime\Connection\PropelPDO;

$wrapped = new PropelPDO($conn);
$wrapped2 = new \Propel\Runtime\Connection\PropelPDO($conn);
