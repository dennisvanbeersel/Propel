<?php

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
use Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestion;
use Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery;


/**
 * This class defines the structure of the 'concrete_quizz_question' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 */
class ConcreteQuizzQuestionTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    public const CLASS_NAME = 'Propel.Tests.Bookstore.Behavior.Map.ConcreteQuizzQuestionTableMap';

    /**
     * The default database name for this class
     */
    public const DATABASE_NAME = 'bookstore-behavior';

    /**
     * The table name for this class
     */
    public const TABLE_NAME = 'concrete_quizz_question';

    /**
     * The PHP name of this class (PascalCase)
     */
    public const TABLE_PHP_NAME = 'ConcreteQuizzQuestion';

    /**
     * The related Propel class for this table
     */
    public const OM_CLASS = '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteQuizzQuestion';

    /**
     * A class that can be returned by this tableMap
     */
    public const CLASS_DEFAULT = 'Propel.Tests.Bookstore.Behavior.ConcreteQuizzQuestion';

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
     * the column name for the id field
     */
    public const COL_ID = 'concrete_quizz_question.id';

    /**
     * the column name for the question field
     */
    public const COL_QUESTION = 'concrete_quizz_question.question';

    /**
     * the column name for the answer_1 field
     */
    public const COL_ANSWER_1 = 'concrete_quizz_question.answer_1';

    /**
     * the column name for the answer_2 field
     */
    public const COL_ANSWER_2 = 'concrete_quizz_question.answer_2';

    /**
     * the column name for the correct_answer field
     */
    public const COL_CORRECT_ANSWER = 'concrete_quizz_question.correct_answer';

    /**
     * the column name for the quizz_id field
     */
    public const COL_QUIZZ_ID = 'concrete_quizz_question.quizz_id';

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
        self::TYPE_PHPNAME       => ['Id', 'Question', 'Answer1', 'Answer2', 'CorrectAnswer', 'QuizzId', ],
        self::TYPE_CAMELNAME     => ['id', 'question', 'answer1', 'answer2', 'correctAnswer', 'quizzId', ],
        self::TYPE_COLNAME       => [ConcreteQuizzQuestionTableMap::COL_ID, ConcreteQuizzQuestionTableMap::COL_QUESTION, ConcreteQuizzQuestionTableMap::COL_ANSWER_1, ConcreteQuizzQuestionTableMap::COL_ANSWER_2, ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER, ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID, ],
        self::TYPE_FIELDNAME     => ['id', 'question', 'answer_1', 'answer_2', 'correct_answer', 'quizz_id', ],
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
        self::TYPE_PHPNAME       => ['Id' => 0, 'Question' => 1, 'Answer1' => 2, 'Answer2' => 3, 'CorrectAnswer' => 4, 'QuizzId' => 5, ],
        self::TYPE_CAMELNAME     => ['id' => 0, 'question' => 1, 'answer1' => 2, 'answer2' => 3, 'correctAnswer' => 4, 'quizzId' => 5, ],
        self::TYPE_COLNAME       => [ConcreteQuizzQuestionTableMap::COL_ID => 0, ConcreteQuizzQuestionTableMap::COL_QUESTION => 1, ConcreteQuizzQuestionTableMap::COL_ANSWER_1 => 2, ConcreteQuizzQuestionTableMap::COL_ANSWER_2 => 3, ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER => 4, ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID => 5, ],
        self::TYPE_FIELDNAME     => ['id' => 0, 'question' => 1, 'answer_1' => 2, 'answer_2' => 3, 'correct_answer' => 4, 'quizz_id' => 5, ],
        self::TYPE_NUM           => [0, 1, 2, 3, 4, 5, ]
    ];

    /**
     * Holds a list of column names and their normalized version.
     *
     * @var array<string>
     */
    protected array $normalizedColumnNameMap = [
        'Id' => 'ID',
        'ConcreteQuizzQuestion.Id' => 'ID',
        'id' => 'ID',
        'concreteQuizzQuestion.id' => 'ID',
        'ConcreteQuizzQuestionTableMap::COL_ID' => 'ID',
        'COL_ID' => 'ID',
        'concrete_quizz_question.id' => 'ID',
        'Question' => 'QUESTION',
        'ConcreteQuizzQuestion.Question' => 'QUESTION',
        'question' => 'QUESTION',
        'concreteQuizzQuestion.question' => 'QUESTION',
        'ConcreteQuizzQuestionTableMap::COL_QUESTION' => 'QUESTION',
        'COL_QUESTION' => 'QUESTION',
        'concrete_quizz_question.question' => 'QUESTION',
        'Answer1' => 'ANSWER_1',
        'ConcreteQuizzQuestion.Answer1' => 'ANSWER_1',
        'answer1' => 'ANSWER_1',
        'concreteQuizzQuestion.answer1' => 'ANSWER_1',
        'ConcreteQuizzQuestionTableMap::COL_ANSWER_1' => 'ANSWER_1',
        'COL_ANSWER_1' => 'ANSWER_1',
        'answer_1' => 'ANSWER_1',
        'concrete_quizz_question.answer_1' => 'ANSWER_1',
        'Answer2' => 'ANSWER_2',
        'ConcreteQuizzQuestion.Answer2' => 'ANSWER_2',
        'answer2' => 'ANSWER_2',
        'concreteQuizzQuestion.answer2' => 'ANSWER_2',
        'ConcreteQuizzQuestionTableMap::COL_ANSWER_2' => 'ANSWER_2',
        'COL_ANSWER_2' => 'ANSWER_2',
        'answer_2' => 'ANSWER_2',
        'concrete_quizz_question.answer_2' => 'ANSWER_2',
        'CorrectAnswer' => 'CORRECT_ANSWER',
        'ConcreteQuizzQuestion.CorrectAnswer' => 'CORRECT_ANSWER',
        'correctAnswer' => 'CORRECT_ANSWER',
        'concreteQuizzQuestion.correctAnswer' => 'CORRECT_ANSWER',
        'ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER' => 'CORRECT_ANSWER',
        'COL_CORRECT_ANSWER' => 'CORRECT_ANSWER',
        'correct_answer' => 'CORRECT_ANSWER',
        'concrete_quizz_question.correct_answer' => 'CORRECT_ANSWER',
        'QuizzId' => 'QUIZZ_ID',
        'ConcreteQuizzQuestion.QuizzId' => 'QUIZZ_ID',
        'quizzId' => 'QUIZZ_ID',
        'concreteQuizzQuestion.quizzId' => 'QUIZZ_ID',
        'ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID' => 'QUIZZ_ID',
        'COL_QUIZZ_ID' => 'QUIZZ_ID',
        'quizz_id' => 'QUIZZ_ID',
        'concrete_quizz_question.quizz_id' => 'QUIZZ_ID',
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
        $this->setName('concrete_quizz_question');
        $this->setPhpName('ConcreteQuizzQuestion');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteQuizzQuestion');
        $this->setPackage('Propel.Tests.Bookstore.Behavior');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id', 'Id', 'INTEGER', true, null, null);
        $this->addColumn('question', 'Question', 'VARCHAR', false, 100, null);
        $this->getColumn('question')->setPrimaryString(true);
        $this->addColumn('answer_1', 'Answer1', 'VARCHAR', false, 100, null);
        $this->addColumn('answer_2', 'Answer2', 'VARCHAR', false, 100, null);
        $this->addColumn('correct_answer', 'CorrectAnswer', 'INTEGER', false, null, null);
        $this->addForeignKey('quizz_id', 'QuizzId', 'INTEGER', 'concrete_quizz', 'id', true, null, null);
    }

    /**
     * Build the RelationMap objects for this table relationships
     *
     * @return void
     */
    public function buildRelations(): void
    {
        $this->addRelation('ConcreteQuizz', '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteQuizz', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':quizz_id',
    1 => ':id',
  ),
), 'CASCADE', null, null, false);
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
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
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
                ? 0 + $offset
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
        return $withPrefix ? ConcreteQuizzQuestionTableMap::CLASS_DEFAULT : ConcreteQuizzQuestionTableMap::OM_CLASS;
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
     * @return array (ConcreteQuizzQuestion object, last column rank)
     */
    public static function populateObject(array $row, int $offset = 0, string $indexType = TableMap::TYPE_NUM): array
    {
        $key = ConcreteQuizzQuestionTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ConcreteQuizzQuestionTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ConcreteQuizzQuestionTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ConcreteQuizzQuestionTableMap::OM_CLASS;
            /** @var ConcreteQuizzQuestion $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ConcreteQuizzQuestionTableMap::addInstanceToPool($obj, $key);
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
            $key = ConcreteQuizzQuestionTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ConcreteQuizzQuestionTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var ConcreteQuizzQuestion $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ConcreteQuizzQuestionTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(ConcreteQuizzQuestionTableMap::COL_ID);
            $criteria->addSelectColumn(ConcreteQuizzQuestionTableMap::COL_QUESTION);
            $criteria->addSelectColumn(ConcreteQuizzQuestionTableMap::COL_ANSWER_1);
            $criteria->addSelectColumn(ConcreteQuizzQuestionTableMap::COL_ANSWER_2);
            $criteria->addSelectColumn(ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER);
            $criteria->addSelectColumn(ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID);
        } else {
            $criteria->addSelectColumn($alias . '.id');
            $criteria->addSelectColumn($alias . '.question');
            $criteria->addSelectColumn($alias . '.answer_1');
            $criteria->addSelectColumn($alias . '.answer_2');
            $criteria->addSelectColumn($alias . '.correct_answer');
            $criteria->addSelectColumn($alias . '.quizz_id');
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
            $criteria->removeSelectColumn(ConcreteQuizzQuestionTableMap::COL_ID);
            $criteria->removeSelectColumn(ConcreteQuizzQuestionTableMap::COL_QUESTION);
            $criteria->removeSelectColumn(ConcreteQuizzQuestionTableMap::COL_ANSWER_1);
            $criteria->removeSelectColumn(ConcreteQuizzQuestionTableMap::COL_ANSWER_2);
            $criteria->removeSelectColumn(ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER);
            $criteria->removeSelectColumn(ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID);
        } else {
            $criteria->removeSelectColumn($alias . '.id');
            $criteria->removeSelectColumn($alias . '.question');
            $criteria->removeSelectColumn($alias . '.answer_1');
            $criteria->removeSelectColumn($alias . '.answer_2');
            $criteria->removeSelectColumn($alias . '.correct_answer');
            $criteria->removeSelectColumn($alias . '.quizz_id');
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
        return Propel::getServiceContainer()->getDatabaseMap(ConcreteQuizzQuestionTableMap::DATABASE_NAME)->getTable(ConcreteQuizzQuestionTableMap::TABLE_NAME);
    }

    /**
     * Performs a DELETE on the database, given a ConcreteQuizzQuestion or Criteria object OR a primary key value.
     *
     * @param mixed $values Criteria or ConcreteQuizzQuestion object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteQuizzQuestionTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestion) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ConcreteQuizzQuestionTableMap::DATABASE_NAME);
            $criteria->add(ConcreteQuizzQuestionTableMap::COL_ID, (array) $values, Criteria::IN);
        }

        $query = ConcreteQuizzQuestionQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            ConcreteQuizzQuestionTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                ConcreteQuizzQuestionTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the concrete_quizz_question table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(?ConnectionInterface $con = null): int
    {
        return ConcreteQuizzQuestionQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ConcreteQuizzQuestion or Criteria object.
     *
     * @param mixed $criteria Criteria or ConcreteQuizzQuestion object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed The new primary key.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteQuizzQuestionTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ConcreteQuizzQuestion object
        }

        if ($criteria->containsKey(ConcreteQuizzQuestionTableMap::COL_ID) && $criteria->keyContainsValue(ConcreteQuizzQuestionTableMap::COL_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ConcreteQuizzQuestionTableMap::COL_ID.')');
        }


        // Set the correct dbName
        $query = ConcreteQuizzQuestionQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

}
