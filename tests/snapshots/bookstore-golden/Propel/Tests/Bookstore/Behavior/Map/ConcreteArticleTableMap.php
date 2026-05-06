<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Map;

use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;
use Propel\Tests\Bookstore\Behavior\ConcreteArticle;
use Propel\Tests\Bookstore\Behavior\ConcreteArticleQuery;


/**
 * This class defines the structure of the 'concrete_article' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ConcreteArticleTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.ConcreteArticleTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'concrete_article';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'ConcreteArticle';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteArticle';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.ConcreteArticle';

    /**
     * The total number of columns
     */
    public const NUM_COLUMNS = 6;

    /**
     * The number of lazy-loaded columns
     */
    public const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    public const NUM_HYDRATE_COLUMNS = 6;

    /**
     * the column name for the body field
     */
    public const COL_BODY = 'concrete_article.body';

    /**
     * the column name for the author_id field
     */
    public const COL_AUTHOR_ID = 'concrete_article.author_id';

    /**
     * the column name for the id field
     */
    public const COL_ID = 'concrete_article.id';

    /**
     * the column name for the title field
     */
    public const COL_TITLE = 'concrete_article.title';

    /**
     * the column name for the category_id field
     */
    public const COL_CATEGORY_ID = 'concrete_article.category_id';

    /**
     * the column name for the descendant_class field
     */
    public const COL_DESCENDANT_CLASS = 'concrete_article.descendant_class';

    /**
     * The default string format for model objects of the related table
     */
    public const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     *
     * @var array<string, mixed>
     */
    protected static $fieldNames = [
        self::TYPE_PHPNAME       => ['Body', 'AuthorId', 'Id', 'Title', 'CategoryId', 'DescendantClass', ],
        self::TYPE_CAMELNAME     => ['body', 'authorId', 'id', 'title', 'categoryId', 'descendantClass', ],
        self::TYPE_COLNAME       => [ConcreteArticleTableMap::COL_BODY, ConcreteArticleTableMap::COL_AUTHOR_ID, ConcreteArticleTableMap::COL_ID, ConcreteArticleTableMap::COL_TITLE, ConcreteArticleTableMap::COL_CATEGORY_ID, ConcreteArticleTableMap::COL_DESCENDANT_CLASS, ],
        self::TYPE_FIELDNAME     => ['body', 'author_id', 'id', 'title', 'category_id', 'descendant_class', ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
    ];

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     *
     * @var array<string, mixed>
     */
    protected static $fieldKeys = [
        self::TYPE_PHPNAME       => ['Body' => 0, 'AuthorId' => 1, 'Id' => 2, 'Title' => 3, 'CategoryId' => 4, 'DescendantClass' => 5, ],
        self::TYPE_CAMELNAME     => ['body' => 0, 'authorId' => 1, 'id' => 2, 'title' => 3, 'categoryId' => 4, 'descendantClass' => 5, ],
        self::TYPE_COLNAME       => [ConcreteArticleTableMap::COL_BODY => 0, ConcreteArticleTableMap::COL_AUTHOR_ID => 1, ConcreteArticleTableMap::COL_ID => 2, ConcreteArticleTableMap::COL_TITLE => 3, ConcreteArticleTableMap::COL_CATEGORY_ID => 4, ConcreteArticleTableMap::COL_DESCENDANT_CLASS => 5, ],
        self::TYPE_FIELDNAME     => ['body' => 0, 'author_id' => 1, 'id' => 2, 'title' => 3, 'category_id' => 4, 'descendant_class' => 5, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Body' => 'BODY',
        'ConcreteArticle.Body' => 'BODY',
        'body' => 'BODY',
        'concreteArticle.body' => 'BODY',
        'ConcreteArticleTableMap::COL_BODY' => 'BODY',
        'COL_BODY' => 'BODY',
        'concrete_article.body' => 'BODY',
        'AuthorId' => 'AUTHOR_ID',
        'ConcreteArticle.AuthorId' => 'AUTHOR_ID',
        'authorId' => 'AUTHOR_ID',
        'concreteArticle.authorId' => 'AUTHOR_ID',
        'ConcreteArticleTableMap::COL_AUTHOR_ID' => 'AUTHOR_ID',
        'COL_AUTHOR_ID' => 'AUTHOR_ID',
        'author_id' => 'AUTHOR_ID',
        'concrete_article.author_id' => 'AUTHOR_ID',
        'Id' => 'ID',
        'ConcreteArticle.Id' => 'ID',
        'id' => 'ID',
        'concreteArticle.id' => 'ID',
        'ConcreteArticleTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'concrete_article.id' => 'ID',
        'Title' => 'TITLE',
        'ConcreteArticle.Title' => 'TITLE',
        'title' => 'TITLE',
        'concreteArticle.title' => 'TITLE',
        'ConcreteArticleTableMap::COL_TITLE' => 'TITLE',
        'COL_TITLE' => 'TITLE',
        'concrete_article.title' => 'TITLE',
        'CategoryId' => 'CATEGORY_ID',
        'ConcreteArticle.CategoryId' => 'CATEGORY_ID',
        'categoryId' => 'CATEGORY_ID',
        'concreteArticle.categoryId' => 'CATEGORY_ID',
        'ConcreteArticleTableMap::COL_CATEGORY_ID' => 'CATEGORY_ID',
        'COL_CATEGORY_ID' => 'CATEGORY_ID',
        'category_id' => 'CATEGORY_ID',
        'concrete_article.category_id' => 'CATEGORY_ID',
        'DescendantClass' => 'DESCENDANT_CLASS',
        'ConcreteArticle.DescendantClass' => 'DESCENDANT_CLASS',
        'descendantClass' => 'DESCENDANT_CLASS',
        'concreteArticle.descendantClass' => 'DESCENDANT_CLASS',
        'ConcreteArticleTableMap::COL_DESCENDANT_CLASS' => 'DESCENDANT_CLASS',
        'COL_DESCENDANT_CLASS' => 'DESCENDANT_CLASS',
        'descendant_class' => 'DESCENDANT_CLASS',
        'concrete_article.descendant_class' => 'DESCENDANT_CLASS',
    ];

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function initialize(): void
    {
        // attributes
        $this->setName('concrete_article');
        $this->setPhpName('ConcreteArticle');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteArticle');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(false);
        // columns
        $this->addColumn('body', 'Body', 'LONGVARCHAR', false, null, null);
        $this->addForeignKey('author_id', 'AuthorId', 'INTEGER', 'concrete_author', 'id', false, null, null);
        $this->addForeignPrimaryKey('id', 'Id', 'INTEGER' , 'concrete_content', 'id', true, null, null);
        $this->addColumn('title', 'Title', 'VARCHAR', false, 100, null);
        $this->getColumn('title')->setPrimaryString(true);
        $this->addForeignKey('category_id', 'CategoryId', 'INTEGER', 'concrete_category', 'id', false, null, null);
        $this->addColumn('descendant_class', 'DescendantClass', 'VARCHAR', false, 100, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('ConcreteAuthor', '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteAuthor', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':author_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('ConcreteContent', '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteContent', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('ConcreteCategory', '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteCategory', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':category_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('ConcreteNews', '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteNews', RelationMap::ONE_TO_ONE, array (
  0 =>
  array (
    0 => ':id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
    }

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array<string, array> Associative array (name => parameters) of behaviors
     */
    public function getBehaviors(): array
    {
        return [
            'concrete_inheritance' => ['extends' => 'concrete_content', 'descendant_column' => 'descendant_class', 'copy_data_to_parent' => 'true', 'copy_data_to_child' => 'false', 'schema' => '', 'exclude_behaviors' => ''],
            'concrete_inheritance_parent' => ['descendant_column' => 'descendant_class'],
        ];
    }

    /**
     * Method to invalidate the instance pool of all tables related to concrete_article     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool(): void
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        ConcreteNewsTableMap::clearInstancePool();
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string|null The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): ?string
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 2 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 2 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 2 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 2 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 2 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 2 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array $row Resultset row.
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 2 + $offset
                : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param bool $withPrefix Whether to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass(bool $withPrefix = true): string
    {
        return $withPrefix ? ConcreteArticleTableMap::CLASS_DEFAULT : ConcreteArticleTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array $row Row returned by DataFetcher->fetch().
     * @param int $offset The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array (ConcreteArticle object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ConcreteArticleTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ConcreteArticleTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ConcreteArticleTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ConcreteArticleTableMap::OM_CLASS;
            /** @var ConcreteArticle $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ConcreteArticleTableMap::addInstanceToPool($obj, $key);
        }

        return [$obj, $col];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array<object>
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher): array
    {
        $results = [];

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = ConcreteArticleTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ConcreteArticleTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var ConcreteArticle $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ConcreteArticleTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria Object containing the columns to add.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function addSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->addSelectColumn(ConcreteArticleTableMap::COL_BODY);
            $criteria->addSelectColumn(ConcreteArticleTableMap::COL_AUTHOR_ID);
            $criteria->addSelectColumn(ConcreteArticleTableMap::COL_ID);
            $criteria->addSelectColumn(ConcreteArticleTableMap::COL_TITLE);
            $criteria->addSelectColumn(ConcreteArticleTableMap::COL_CATEGORY_ID);
            $criteria->addSelectColumn(ConcreteArticleTableMap::COL_DESCENDANT_CLASS);
        } else {
            $criteria->addSelectColumn($alias . '.body');
            $criteria->addSelectColumn($alias . '.author_id');
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.title');
            $criteria->addSelectColumn($alias . '.category_id');
            $criteria->addSelectColumn($alias . '.descendant_class');
        }
    }

    /**
     * Remove all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be removed as they are only loaded on demand.
     *
     * @param Criteria $criteria Object containing the columns to remove.
     * @param string|null $alias Optional table alias
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return void
     */
    public static function removeSelectColumns(Criteria $criteria, ?string $alias = null): void
    {
        if (null === $alias) {
            $criteria->removeSelectColumn(ConcreteArticleTableMap::COL_BODY);
            $criteria->removeSelectColumn(ConcreteArticleTableMap::COL_AUTHOR_ID);
            $criteria->removeSelectColumn(ConcreteArticleTableMap::COL_ID);
            $criteria->removeSelectColumn(ConcreteArticleTableMap::COL_TITLE);
            $criteria->removeSelectColumn(ConcreteArticleTableMap::COL_CATEGORY_ID);
            $criteria->removeSelectColumn(ConcreteArticleTableMap::COL_DESCENDANT_CLASS);
        } else {
            $criteria->removeSelectColumn($alias . '.body');
            $criteria->removeSelectColumn($alias . '.author_id');
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.title');
            $criteria->removeSelectColumn($alias . '.category_id');
            $criteria->removeSelectColumn($alias . '.descendant_class');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap(): TableMap
    {
        return Propel::getServiceContainer()->getDatabaseMap(ConcreteArticleTableMap::DATABASE_NAME)->getTable(ConcreteArticleTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a ConcreteArticle or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or ConcreteArticle object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?ConnectionInterface $con = null): int
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteArticleTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\ConcreteArticle) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ConcreteArticleTableMap::DATABASE_NAME);
            $criteria->add(ConcreteArticleTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ConcreteArticleQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ConcreteArticleTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ConcreteArticleTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the concrete_article table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ConcreteArticleQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ConcreteArticle or Criteria object.
     *
     * @param mixed $criteria Criteria or ConcreteArticle object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteArticleTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ConcreteArticle object
        }


        // Set the correct dbName
        $query = ConcreteArticleQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
