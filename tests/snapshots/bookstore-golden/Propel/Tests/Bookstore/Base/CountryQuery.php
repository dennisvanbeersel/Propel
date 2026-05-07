<?php

declare(strict_types=1);

namespace Propel\Tests\Bookstore\Base;

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
use Propel\Tests\Bookstore\Country as ChildCountry;
use Propel\Tests\Bookstore\CountryQuery as ChildCountryQuery;
use Propel\Tests\Bookstore\Map\CountryTableMap;

/**
 * Base class that represents a query for the `country` table.
 *
 * @method     ChildCountryQuery orderByCode($order = Criteria::ASC) Order by the code column
 * @method     ChildCountryQuery orderByCapital($order = Criteria::ASC) Order by the capital column
 *
 * @method     ChildCountryQuery groupByCode() Group by the code column
 * @method     ChildCountryQuery groupByCapital() Group by the capital column
 *
 * @method     ChildCountryQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildCountryQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildCountryQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildCountryQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildCountryQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildCountryQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildCountryQuery leftJoinContest($relationAlias = null) Adds a LEFT JOIN clause to the query using the Contest relation
 * @method     ChildCountryQuery rightJoinContest($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Contest relation
 * @method     ChildCountryQuery innerJoinContest($relationAlias = null) Adds a INNER JOIN clause to the query using the Contest relation
 *
 * @method     ChildCountryQuery joinWithContest($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Contest relation
 *
 * @method     ChildCountryQuery leftJoinWithContest() Adds a LEFT JOIN clause and with to the query using the Contest relation
 * @method     ChildCountryQuery rightJoinWithContest() Adds a RIGHT JOIN clause and with to the query using the Contest relation
 * @method     ChildCountryQuery innerJoinWithContest() Adds a INNER JOIN clause and with to the query using the Contest relation
 *
 * @method     ChildCountryQuery leftJoinCountryTranslation($relationAlias = null) Adds a LEFT JOIN clause to the query using the CountryTranslation relation
 * @method     ChildCountryQuery rightJoinCountryTranslation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CountryTranslation relation
 * @method     ChildCountryQuery innerJoinCountryTranslation($relationAlias = null) Adds a INNER JOIN clause to the query using the CountryTranslation relation
 *
 * @method     ChildCountryQuery joinWithCountryTranslation($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the CountryTranslation relation
 *
 * @method     ChildCountryQuery leftJoinWithCountryTranslation() Adds a LEFT JOIN clause and with to the query using the CountryTranslation relation
 * @method     ChildCountryQuery rightJoinWithCountryTranslation() Adds a RIGHT JOIN clause and with to the query using the CountryTranslation relation
 * @method     ChildCountryQuery innerJoinWithCountryTranslation() Adds a INNER JOIN clause and with to the query using the CountryTranslation relation
 *
 * @method     \Propel\Tests\Bookstore\ContestQuery|\Propel\Tests\Bookstore\CountryTranslationQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildCountry|null findOne(?ConnectionInterface $con = null) Return the first ChildCountry matching the query
 * @method     ChildCountry findOneOrCreate(?ConnectionInterface $con = null) Return the first ChildCountry matching the query, or a new ChildCountry object populated from the query conditions when no match is found
 *
 * @method     ChildCountry|null findOneByCode(string $code) Return the first ChildCountry filtered by the code column
 * @method     ChildCountry|null findOneByCapital(string $capital) Return the first ChildCountry filtered by the capital column
 *
 * @method     ChildCountry requirePk($key, ?ConnectionInterface $con = null) Return the ChildCountry by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildCountry requireOne(?ConnectionInterface $con = null) Return the first ChildCountry matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildCountry requireOneByCode(string $code) Return the first ChildCountry filtered by the code column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildCountry requireOneByCapital(string $capital) Return the first ChildCountry filtered by the capital column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildCountry[]|Collection find(?ConnectionInterface $con = null) Return ChildCountry objects based on current ModelCriteria
 * @psalm-method Collection&\Traversable<ChildCountry> find(?ConnectionInterface $con = null) Return ChildCountry objects based on current ModelCriteria
 *
 * @method     ChildCountry[]|Collection findByCode(string|array<string> $code) Return ChildCountry objects filtered by the code column
 * @psalm-method Collection&\Traversable<ChildCountry> findByCode(string|array<string> $code) Return ChildCountry objects filtered by the code column
 * @method     ChildCountry[]|Collection findByCapital(string|array<string> $capital) Return ChildCountry objects filtered by the capital column
 * @psalm-method Collection&\Traversable<ChildCountry> findByCapital(string|array<string> $capital) Return ChildCountry objects filtered by the capital column
 *
 * @method     ChildCountry[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 * @psalm-method \Propel\Runtime\Util\PropelModelPager&\Traversable<ChildCountry> paginate($page = 1, $maxPerPage = 10, ?ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 */
abstract class CountryQuery extends ModelCriteria
{
    protected ?string $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Propel\Tests\Bookstore\Base\CountryQuery object.
     *
     * @param string $dbName The database name
     * @param string $modelName The phpName of a model, e.g. 'Book'
     * @param string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'bookstore', $modelName = '\\Propel\\Tests\\Bookstore\\Country', ?string $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildCountryQuery object.
     *
     * @param string $modelAlias The alias of a model in the query
     * @param Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildCountryQuery
     */
    public static function create(?string $modelAlias = null, ?Criteria $criteria = null): static
    {
        if ($criteria instanceof static) {
            return $criteria;
        }
        if ($criteria instanceof ChildCountryQuery) {
            $query = $criteria;
        } else {
            $query = new static();
            if ($criteria instanceof Criteria) {
                $query->mergeWith($criteria);
            }
        }
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
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
     * @return ChildCountry|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ?ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(CountryTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = CountryTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildCountry A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT code, capital FROM country WHERE code = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildCountry $obj */
            $obj = new ChildCountry();
            $obj->hydrate($row);
            CountryTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildCountry|array|mixed the result, formatted by the current formatter
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

        $this->addUsingAlias(CountryTableMap::COL_CODE, $key, Criteria::EQUAL);

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

        $this->addUsingAlias(CountryTableMap::COL_CODE, $keys, Criteria::IN);

        return $this;
    }

    /**
     * Filter the query on the code column
     *
     * Example usage:
     * <code>
     * $query->filterByCode('fooValue');   // WHERE code = 'fooValue'
     * $query->filterByCode('%fooValue%', Criteria::LIKE); // WHERE code LIKE '%fooValue%'
     * $query->filterByCode(['foo', 'bar']); // WHERE code IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $code The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCode($code = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($code)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(CountryTableMap::COL_CODE, $code, $comparison);

        return $this;
    }

    /**
     * Filter the query on the capital column
     *
     * Example usage:
     * <code>
     * $query->filterByCapital('fooValue');   // WHERE capital = 'fooValue'
     * $query->filterByCapital('%fooValue%', Criteria::LIKE); // WHERE capital LIKE '%fooValue%'
     * $query->filterByCapital(['foo', 'bar']); // WHERE capital IN ('foo', 'bar')
     * </code>
     *
     * @param string|string[] $capital The value to use as filter.
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCapital($capital = null, ?string $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($capital)) {
                $comparison = Criteria::IN;
            }
        }

        $this->addUsingAlias(CountryTableMap::COL_CAPITAL, $capital, $comparison);

        return $this;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\Contest object
     *
     * @param \Propel\Tests\Bookstore\Contest|ObjectCollection $contest the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByContest($contest, ?string $comparison = null)
    {
        if ($contest instanceof \Propel\Tests\Bookstore\Contest) {
            $this
                ->addUsingAlias(CountryTableMap::COL_CODE, $contest->getCountryCode(), $comparison);

            return $this;
        } elseif ($contest instanceof ObjectCollection) {
            $this
                ->useContestQuery()
                ->filterByPrimaryKeys($contest->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByContest() only accepts arguments of type \Propel\Tests\Bookstore\Contest or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Contest relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinContest(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Contest');

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
            $this->addJoinObject($join, 'Contest');
        }

        return $this;
    }

    /**
     * Use the Contest relation Contest object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\ContestQuery A secondary query class using the current class as primary query
     */
    public function useContestQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinContest($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Contest', '\Propel\Tests\Bookstore\ContestQuery');
    }

    /**
     * Use the Contest relation Contest object
     *
     * @param callable(\Propel\Tests\Bookstore\ContestQuery):\Propel\Tests\Bookstore\ContestQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withContestQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useContestQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to Contest table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the EXISTS statement
     */
    public function useContestExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useExistsQuery('Contest', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to Contest table for a NOT EXISTS query.
     *
     * @see useContestExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the NOT EXISTS statement
     */
    public function useContestNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useExistsQuery('Contest', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to Contest table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the IN statement
     */
    public function useInContestQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useInQuery('Contest', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to Contest table for a NOT IN query.
     *
     * @see useContestInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\ContestQuery The inner query object of the NOT IN statement
     */
    public function useNotInContestQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\ContestQuery */
        $q = $this->useInQuery('Contest', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Filter the query by a related \Propel\Tests\Bookstore\CountryTranslation object
     *
     * @param \Propel\Tests\Bookstore\CountryTranslation|ObjectCollection $countryTranslation the related object to use as filter
     * @param string|null $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this The current query, for fluid interface
     */
    public function filterByCountryTranslation($countryTranslation, ?string $comparison = null)
    {
        if ($countryTranslation instanceof \Propel\Tests\Bookstore\CountryTranslation) {
            $this
                ->addUsingAlias(CountryTableMap::COL_CODE, $countryTranslation->getCountryCode(), $comparison);

            return $this;
        } elseif ($countryTranslation instanceof ObjectCollection) {
            $this
                ->useCountryTranslationQuery()
                ->filterByPrimaryKeys($countryTranslation->getPrimaryKeys())
                ->endUse();

            return $this;
        } else {
            throw new PropelException('filterByCountryTranslation() only accepts arguments of type \Propel\Tests\Bookstore\CountryTranslation or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CountryTranslation relation
     *
     * @param string|null $relationAlias Optional alias for the relation
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this The current query, for fluid interface
     */
    public function joinCountryTranslation(?string $relationAlias = null, ?string $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CountryTranslation');

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
            $this->addJoinObject($join, 'CountryTranslation');
        }

        return $this;
    }

    /**
     * Use the CountryTranslation relation CountryTranslation object
     *
     * @see useQuery()
     *
     * @param string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \Propel\Tests\Bookstore\CountryTranslationQuery A secondary query class using the current class as primary query
     */
    public function useCountryTranslationQuery(?string $relationAlias = null, string $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinCountryTranslation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CountryTranslation', '\Propel\Tests\Bookstore\CountryTranslationQuery');
    }

    /**
     * Use the CountryTranslation relation CountryTranslation object
     *
     * @param callable(\Propel\Tests\Bookstore\CountryTranslationQuery):\Propel\Tests\Bookstore\CountryTranslationQuery $callable A function working on the related query
     *
     * @param string|null $relationAlias optional alias for the relation
     *
     * @param string|null $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this
     */
    public function withCountryTranslationQuery(
        callable $callable,
        ?string $relationAlias = null,
        ?string $joinType = Criteria::LEFT_JOIN
    ) {
        $relatedQuery = $this->useCountryTranslationQuery(
            $relationAlias,
            $joinType
        );
        $callable($relatedQuery);
        $relatedQuery->endUse();

        return $this;
    }

    /**
     * Use the relation to CountryTranslation table for an EXISTS query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     * @param string $typeOfExists Either ExistsQueryCriterion::TYPE_EXISTS or ExistsQueryCriterion::TYPE_NOT_EXISTS
     *
     * @return \Propel\Tests\Bookstore\CountryTranslationQuery The inner query object of the EXISTS statement
     */
    public function useCountryTranslationExistsQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfExists = 'EXISTS')
    {
        /** @var $q \Propel\Tests\Bookstore\CountryTranslationQuery */
        $q = $this->useExistsQuery('CountryTranslation', $modelAlias, $queryClass, $typeOfExists);
        return $q;
    }

    /**
     * Use the relation to CountryTranslation table for a NOT EXISTS query.
     *
     * @see useCountryTranslationExistsQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the exists query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CountryTranslationQuery The inner query object of the NOT EXISTS statement
     */
    public function useCountryTranslationNotExistsQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CountryTranslationQuery */
        $q = $this->useExistsQuery('CountryTranslation', $modelAlias, $queryClass, 'NOT EXISTS');
        return $q;
    }

    /**
     * Use the relation to CountryTranslation table for an IN query.
     *
     * @see \Propel\Runtime\ActiveQuery\ModelCriteria::useInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the IN query, like ExtendedBookQuery::class
     * @param string $typeOfIn Criteria::IN or Criteria::NOT_IN
     *
     * @return \Propel\Tests\Bookstore\CountryTranslationQuery The inner query object of the IN statement
     */
    public function useInCountryTranslationQuery(?string $modelAlias = null, ?string $queryClass = null, string $typeOfIn = 'IN')
    {
        /** @var $q \Propel\Tests\Bookstore\CountryTranslationQuery */
        $q = $this->useInQuery('CountryTranslation', $modelAlias, $queryClass, $typeOfIn);
        return $q;
    }

    /**
     * Use the relation to CountryTranslation table for a NOT IN query.
     *
     * @see useCountryTranslationInQuery()
     *
     * @param string|null $modelAlias sets an alias for the nested query
     * @param string|null $queryClass Allows to use a custom query class for the NOT IN query, like ExtendedBookQuery::class
     *
     * @return \Propel\Tests\Bookstore\CountryTranslationQuery The inner query object of the NOT IN statement
     */
    public function useNotInCountryTranslationQuery(?string $modelAlias = null, ?string $queryClass = null)
    {
        /** @var $q \Propel\Tests\Bookstore\CountryTranslationQuery */
        $q = $this->useInQuery('CountryTranslation', $modelAlias, $queryClass, 'NOT IN');
        return $q;
    }

    /**
     * Exclude object from result
     *
     * @param ChildCountry $country Object to remove from the list of results
     *
     * @return $this The current query, for fluid interface
     */
    public function prune($country = null)
    {
        if ($country) {
            $this->addUsingAlias(CountryTableMap::COL_CODE, $country->getCode(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
