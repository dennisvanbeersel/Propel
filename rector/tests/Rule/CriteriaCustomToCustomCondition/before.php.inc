<?php

use Propel\Runtime\ActiveQuery\Criteria;

$c = new Criteria();
$c->add('Book.title', 'LIKE', Criteria::CUSTOM);
$c->add('Author.name', "name LIKE '%foo%'", Criteria::CUSTOM);
$c->add(BookTableMap::COL_TITLE, "title LIKE 'War%'", \Propel\Runtime\ActiveQuery\Criteria::CUSTOM);
