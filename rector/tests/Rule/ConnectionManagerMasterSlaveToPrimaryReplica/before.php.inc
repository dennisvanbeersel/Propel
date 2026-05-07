<?php

use Propel\Runtime\Connection\ConnectionManagerMasterSlave;

$manager = new \Propel\Runtime\Connection\ConnectionManagerMasterSlave('default');
$manager->setForceMasterConnection(true);
$on = $manager->isForceMasterConnection();
