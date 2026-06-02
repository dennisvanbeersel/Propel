<?php

use Propel\Runtime\ActiveQuery\Criteria;

$c = new Criteria();
$c->customCondition('Book.title', 'LIKE');
$c->customCondition('Author.name', "name LIKE '%foo%'");
$c->customCondition(BookTableMap::COL_TITLE, "title LIKE 'War%'");
