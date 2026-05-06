<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Behavior\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestion as ChildConcreteQuizzQuestion;
use Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuestionQuery as ChildConcreteQuizzQuestionQuery;
use Propel\Tests\Bookstore\Behavior\Map\ConcreteQuizzQuestionTableMap;

/**
 * Base class that represents a query for the `concrete_quizz_question` table.
 *
 * @method     ChildConcreteQuizzQuestionQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildConcreteQuizzQuestionQuery orderByQuestion($order = Criteria::ASC) Order by the question column
 * @method     ChildConcreteQuizzQuestionQuery orderByAnswer1($order = Criteria::ASC) Order by the answer_1 column
 * @method     ChildConcreteQuizzQuestionQuery orderByAnswer2($order = Criteria::ASC) Order by the answer_2 column
 * @method     ChildConcreteQuizzQuestionQuery orderByCorrectAnswer($order = Criteria::ASC) Order by the correct_answer column
 * @method     ChildConcreteQuizzQuestionQuery orderByQuizzId($order = Criteria::ASC) Order by the quizz_id column
 *
 * @method     ChildConcreteQuizzQuestionQuery groupById() Group by the id column
 * @method     ChildConcreteQuizzQuestionQuery groupByQuestion() Group by the question column
 * @method     ChildConcreteQuizzQuestionQuery groupByAnswer1() Group by the answer_1 column
 * @method     ChildConcreteQuizzQuestionQuery groupByAnswer2() Group by the answer_2 column
 * @method     ChildConcreteQuizzQuestionQuery groupByCorrectAnswer() Group by the correct_answer column
 * @method     ChildConcreteQuizzQuestionQuery groupByQuizzId() Group by the quizz_id column
 *
 * @method     ChildConcreteQuizzQuestionQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildConcreteQuizzQuestionQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildConcreteQuizzQuestionQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildConcreteQuizzQuestionQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildConcreteQuizzQuestionQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildConcreteQuizzQuestionQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildConcreteQuizzQuestionQuery leftJoinConcreteQuizz($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConcreteQuizz relation
 * @method     ChildConcreteQuizzQuestionQuery rightJoinConcreteQuizz($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConcreteQuizz relation
 * @method     ChildConcreteQuizzQuestionQuery innerJoinConcreteQuizz($relationAlias = null) Adds a INNER JOIN clause to the query using the ConcreteQuizz relation
 *
 * @method     ChildConcreteQuizzQuestionQuery joinWithConcreteQuizz($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the ConcreteQuizz relation
 *
 * @method     ChildConcreteQuizzQuestionQuery leftJoinWithConcreteQuizz() Adds a LEFT JOIN clause and with to the query using the ConcreteQuizz relation
 * @method     ChildConcreteQuizzQuestionQuery rightJoinWithConcreteQuizz() Adds a RIGHT JOIN clause and with to the query using the ConcreteQuizz relation
 * @method     ChildConcreteQuizzQuestionQuery innerJoinWithConcreteQuizz() Adds a INNER JOIN clause and with to the query using the ConcreteQuizz relation
 *
 * @method     \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildConcreteQuizzQuestion|null findOne(?ConnectionInterface $con = null) Return the first ChildConcreteQuizzQuestion matching the query
 * @method     ChildConcreteQuizzQuestion findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildConcreteQuizzQuestion matching the query, or a new ChildConcreteQuizzQuestion object populated from the query conditions when no match is found
 *
 * @method     ChildConcreteQuizzQuestion|null findOneById(int $id) Return the first ChildConcreteQuizzQuestion filtered by the id column
 * @method     ChildConcreteQuizzQuestion|null findOneByQuestion(string $question) Return the first ChildConcreteQuizzQuestion filtered by the question column
 * @method     ChildConcreteQuizzQuestion|null findOneByAnswer1(string $answer_1) Return the first ChildConcreteQuizzQuestion filtered by the answer_1 column
 * @method     ChildConcreteQuizzQuestion|null findOneByAnswer2(string $answer_2) Return the first ChildConcreteQuizzQuestion filtered by the answer_2 column
 * @method     ChildConcreteQuizzQuestion|null findOneByCorrectAnswer(int $correct_answer) Return the first ChildConcreteQuizzQuestion filtered by the correct_answer column
 * @method     ChildConcreteQuizzQuestion|null findOneByQuizzId(int $quizz_id) Return the first ChildConcreteQuizzQuestion filtered by the quizz_id column
 *
 * @method     ChildConcreteQuizzQuestion requirePk($key, ?ConnectionInterface $con = null) Return the ChildConcreteQuizzQuestion by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizzQuestion requireOne(?ConnectionInterface $con = null) Return the first ChildConcreteQuizzQuestion matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildConcreteQuizzQuestion requireOneById(int $id) Return the first ChildConcreteQuizzQuestion filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizzQuestion requireOneByQuestion(string $question) Return the first ChildConcreteQuizzQuestion filtered by the question column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizzQuestion requireOneByAnswer1(string $answer_1) Return the first ChildConcreteQuizzQuestion filtered by the answer_1 column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizzQuestion requireOneByAnswer2(string $answer_2) Return the first ChildConcreteQuizzQuestion filtered by the answer_2 column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizzQuestion requireOneByCorrectAnswer(int $correct_answer) Return the first ChildConcreteQuizzQuestion filtered by the correct_answer column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildConcreteQuizzQuestion requireOneByQuizzId(int $quizz_id) Return the first ChildConcreteQuizzQuestion filtered by the quizz_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildConcreteQuizzQuestion[]|Collection find(?ConnectionInterface $con = null) Return ChildConcreteQuizzQuestion objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildConcreteQuizzQuestion> find(?ConnectionInterface $con = null) Return ChildConcreteQuizzQuestion objects based on current ModelCriteria
 *
 * @method     ChildConcreteQuizzQuestion[]|Collection findById(int|array<int> $id) Return ChildConcreteQuizzQuestion objects filtered by the id column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizzQuestion> findById(int|array<int> $id) Return ChildConcreteQuizzQuestion objects filtered by the id column
 * @method     ChildConcreteQuizzQuestion[]|Collection findByQuestion(string|array<string> $question) Return ChildConcreteQuizzQuestion objects filtered by the question column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizzQuestion> findByQuestion(string|array<string> $question) Return ChildConcreteQuizzQuestion objects filtered by the question column
 * @method     ChildConcreteQuizzQuestion[]|Collection findByAnswer1(string|array<string> $answer_1) Return ChildConcreteQuizzQuestion objects filtered by the answer_1 column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizzQuestion> findByAnswer1(string|array<string> $answer_1) Return ChildConcreteQuizzQuestion objects filtered by the answer_1 column
 * @method     ChildConcreteQuizzQuestion[]|Collection findByAnswer2(string|array<string> $answer_2) Return ChildConcreteQuizzQuestion objects filtered by the answer_2 column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizzQuestion> findByAnswer2(string|array<string> $answer_2) Return ChildConcreteQuizzQuestion objects filtered by the answer_2 column
 * @method     ChildConcreteQuizzQuestion[]|Collection findByCorrectAnswer(int|array<int> $correct_answer) Return ChildConcreteQuizzQuestion objects filtered by the correct_answer column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizzQuestion> findByCorrectAnswer(int|array<int> $correct_answer) Return ChildConcreteQuizzQuestion objects filtered by the correct_answer column
 * @method     ChildConcreteQuizzQuestion[]|Collection findByQuizzId(int|array<int> $quizz_id) Return ChildConcreteQuizzQuestion objects filtered by the quizz_id column
 * @psalm-method Collection&\Traversable<ChildConcreteQuizzQuestion> findByQuizzId(int|array<int> $quizz_id) Return ChildConcreteQuizzQuestion objects filtered by the quizz_id column
 *
 * @method     ChildConcreteQuizzQuestion[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildConcreteQuizzQuestion> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class ConcreteQuizzQuestionQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Behavior\Base\ConcreteQuizzQuestionQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore-behavior', $modelName = '\\Propel\\Tests\\Bookstore\\Behavior\\ConcreteQuizzQuestion', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildConcreteQuizzQuestionQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildConcreteQuizzQuestionQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof ChildConcreteQuizzQuestionQuery) {
            return $criteria;
        }
        $query = new ChildConcreteQuizzQuestionQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildConcreteQuizzQuestion|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ConcreteQuizzQuestionTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = ConcreteQuizzQuestionTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildConcreteQuizzQuestion A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, question, answer_1, answer_2, correct_answer, quizz_id FROM concrete_quizz_question WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildConcreteQuizzQuestion $obj */
            $obj = new ChildConcreteQuizzQuestion();
            $obj->hydrate($row);
            ConcreteQuizzQuestionTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con A connection object
     *
     * @return ChildConcreteQuizzQuestion|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param array $keys Primary keys to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return Collection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ?ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param mixed $key Primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ID, $key, Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param array|int $keys The list of primary key to use for the query
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ID, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterById($id = null, ?string $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ID, $id, $comparison);

        return $this;
    }

    /**
     * Filter the query on the question column
     *
     * Example usage:
     * <code>
     * $query->filterByQuestion('fooValue');   // WHERE question = 'fooValue'
     * $query->filterByQuestion('%fooValue%', Criteria::LIKE); // WHERE question LIKE '%fooValue%'
     * $query->filterByQuestion(['foo', 'bar']); // WHERE question IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $question The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByQuestion($question = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($question)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_QUESTION, $question, $comparison);

        return $this;
    }

    /**
     * Filter the query on the answer_1 column
     *
     * Example usage:
     * <code>
     * $query->filterByAnswer1('fooValue');   // WHERE answer_1 = 'fooValue'
     * $query->filterByAnswer1('%fooValue%', Criteria::LIKE); // WHERE answer_1 LIKE '%fooValue%'
     * $query->filterByAnswer1(['foo', 'bar']); // WHERE answer_1 IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $answer1 The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAnswer1($answer1 = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($answer1)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ANSWER_1, $answer1, $comparison);

        return $this;
    }

    /**
     * Filter the query on the answer_2 column
     *
     * Example usage:
     * <code>
     * $query->filterByAnswer2('fooValue');   // WHERE answer_2 = 'fooValue'
     * $query->filterByAnswer2('%fooValue%', Criteria::LIKE); // WHERE answer_2 LIKE '%fooValue%'
     * $query->filterByAnswer2(['foo', 'bar']); // WHERE answer_2 IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $answer2 The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByAnswer2($answer2 = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($answer2)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ANSWER_2, $answer2, $comparison);

        return $this;
    }

    /**
     * Filter the query on the correct_answer column
     *
     * Example usage:
     * <code>
     * $query->filterByCorrectAnswer(1234); // WHERE correct_answer = 1234
     * $query->filterByCorrectAnswer(array(12, 34)); // WHERE correct_answer IN (12, 34)
     * $query->filterByCorrectAnswer(array('min' => 12)); // WHERE correct_answer > 12
     * </code>
     *
     * @param mixed $correctAnswer The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCorrectAnswer($correctAnswer = null, ?string $comparison = null)
    {
        if (is_array($correctAnswer)) {
            $useMinMax = false;
            if (isset($correctAnswer['min'])) {
                $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER, $correctAnswer['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($correctAnswer['max'])) {
                $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER, $correctAnswer['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_CORRECT_ANSWER, $correctAnswer, $comparison);

        return $this;
    }

    /**
     * Filter the query on the quizz_id column
     *
     * Example usage:
     * <code>
     * $query->filterByQuizzId(1234); // WHERE quizz_id = 1234
     * $query->filterByQuizzId(array(12, 34)); // WHERE quizz_id IN (12, 34)
     * $query->filterByQuizzId(array('min' => 12)); // WHERE quizz_id > 12
     * </code>
     *
     * @see       filterByConcreteQuizz()
     *
     * @param mixed $quizzId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByQuizzId($quizzId = null, ?string $comparison = null)
    {
        if (is_array($quizzId)) {
            $useMinMax = false;
            if (isset($quizzId['min'])) {
                $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID, $quizzId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($quizzId['max'])) {
                $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID, $quizzId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID, $quizzId, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Behavior\ConcreteQuizz object
     *
     * @param \Propel\Tests\Bookstore\Behavior\ConcreteQuizz|ObjectCollection $concreteQuizz The related object(s) to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByConcreteQuizz($concreteQuizz, ?string $comparison = null)
    {
        if ($concreteQuizz instanceof \Propel\Tests\Bookstore\Behavior\ConcreteQuizz) {
            return $this
                ->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID, $concreteQuizz->getId(), $comparison);
        } elseif ($concreteQuizz instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            $this
                ->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_QUIZZ_ID, $concreteQuizz->toKeyValue('PrimaryKey', 'Id'), $comparison);

            return $this;
        } else {
            throw new PropelException('filterByConcreteQuizz() only accepts arguments of type \Propel\Tests\Bookstore\Behavior\ConcreteQuizz or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConcreteQuizz relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinConcreteQuizz(?string $relationAlias = null, ?string $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConcreteQuizz');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ConcreteQuizz');
        }

        return $this;
    }

    /**
     * Use the ConcreteQuizz relation ConcreteQuizz object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery A secondary query class using the current class as primary query
     */
    public function useConcreteQuizzQuery(?string $relationAlias = null, string $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinConcreteQuizz($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConcreteQuizz', '\Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery');
    }

    /**
     * Use the ConcreteQuizz relation ConcreteQuizz object
     *
     * @param callable(\Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery):\Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withConcreteQuizzQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::INNER_JOIN
    ) {
        $relatedQuery = $this->useConcreteQuizzQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to ConcreteQuizz table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery The inner query object of the EXISTS statement
     */
    public function useConcreteQuizzExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery */
        $q = $this->useExistsQuery('ConcreteQuizz', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to ConcreteQuizz table for a NOT EXISTS query.
     *
     * @see useConcreteQuizzExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery The inner query object of the NOT EXISTS statement
     */
    public function useConcreteQuizzNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery */
        $q = $this->useExistsQuery('ConcreteQuizz', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to ConcreteQuizz table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery The inner query object of the IN statement
     */
    public function useInConcreteQuizzQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery */
        $q = $this->useInQuery('ConcreteQuizz', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to ConcreteQuizz table for a NOT IN query.
     *
     * @see useConcreteQuizzInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery The inner query object of the NOT IN statement
     */
    public function useNotInConcreteQuizzQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\Behavior\ConcreteQuizzQuery */
        $q = $this->useInQuery('ConcreteQuizz', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildConcreteQuizzQuestion $concreteQuizzQuestion Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($concreteQuizzQuestion = null)
    {
        if ($concreteQuizzQuestion) {
            $this->addUsingAlias(ConcreteQuizzQuestionTableMap::COL_ID, $concreteQuizzQuestion->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the concrete_quizz_question table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteQuizzQuestionTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ConcreteQuizzQuestionTableMap::clearInstancePool();
            ConcreteQuizzQuestionTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws \Propel\Runtime\Exception\PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(?ConnectionInterface $con = null): int
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ConcreteQuizzQuestionTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ConcreteQuizzQuestionTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            ConcreteQuizzQuestionTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ConcreteQuizzQuestionTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

}
