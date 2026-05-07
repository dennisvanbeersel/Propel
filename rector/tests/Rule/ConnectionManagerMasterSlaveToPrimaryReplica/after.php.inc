<?php

use Propel\Runtime\Connection\ConnectionManagerMasterSlave;

$manager = new \Propel\Runtime\Connection\ConnectionManagerPrimaryReplica('default');
$manager->setForcePrimaryConnection(true);
$on = $manager->isForcePrimaryConnection();
