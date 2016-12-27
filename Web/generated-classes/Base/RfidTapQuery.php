<?php

namespace Base;

use \RfidTap as ChildRfidTap;
use \RfidTapQuery as ChildRfidTapQuery;
use \Exception;
use \PDO;
use Map\RfidTapTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'rfidtap' table.
 *
 *
 *
 * @method     ChildRfidTapQuery orderByRfid($order = Criteria::ASC) Order by the rfid column
 * @method     ChildRfidTapQuery orderByTime($order = Criteria::ASC) Order by the time column
 *
 * @method     ChildRfidTapQuery groupByRfid() Group by the rfid column
 * @method     ChildRfidTapQuery groupByTime() Group by the time column
 *
 * @method     ChildRfidTapQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildRfidTapQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildRfidTapQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildRfidTapQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildRfidTapQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildRfidTapQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildRfidTapQuery leftJoinMapping($relationAlias = null) Adds a LEFT JOIN clause to the query using the Mapping relation
 * @method     ChildRfidTapQuery rightJoinMapping($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Mapping relation
 * @method     ChildRfidTapQuery innerJoinMapping($relationAlias = null) Adds a INNER JOIN clause to the query using the Mapping relation
 *
 * @method     ChildRfidTapQuery joinWithMapping($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Mapping relation
 *
 * @method     ChildRfidTapQuery leftJoinWithMapping() Adds a LEFT JOIN clause and with to the query using the Mapping relation
 * @method     ChildRfidTapQuery rightJoinWithMapping() Adds a RIGHT JOIN clause and with to the query using the Mapping relation
 * @method     ChildRfidTapQuery innerJoinWithMapping() Adds a INNER JOIN clause and with to the query using the Mapping relation
 *
 * @method     \RfidQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildRfidTap findOne(ConnectionInterface $con = null) Return the first ChildRfidTap matching the query
 * @method     ChildRfidTap findOneOrCreate(ConnectionInterface $con = null) Return the first ChildRfidTap matching the query, or a new ChildRfidTap object populated from the query conditions when no match is found
 *
 * @method     ChildRfidTap findOneByRfid(string $rfid) Return the first ChildRfidTap filtered by the rfid column
 * @method     ChildRfidTap findOneByTime(string $time) Return the first ChildRfidTap filtered by the time column *

 * @method     ChildRfidTap requirePk($key, ConnectionInterface $con = null) Return the ChildRfidTap by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildRfidTap requireOne(ConnectionInterface $con = null) Return the first ChildRfidTap matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildRfidTap requireOneByRfid(string $rfid) Return the first ChildRfidTap filtered by the rfid column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildRfidTap requireOneByTime(string $time) Return the first ChildRfidTap filtered by the time column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildRfidTap[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildRfidTap objects based on current ModelCriteria
 * @method     ChildRfidTap[]|ObjectCollection findByRfid(string $rfid) Return ChildRfidTap objects filtered by the rfid column
 * @method     ChildRfidTap[]|ObjectCollection findByTime(string $time) Return ChildRfidTap objects filtered by the time column
 * @method     ChildRfidTap[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class RfidTapQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Base\RfidTapQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'soapy', $modelName = '\\RfidTap', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildRfidTapQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildRfidTapQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildRfidTapQuery) {
            return $criteria;
        }
        $query = new ChildRfidTapQuery();
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
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$rfid, $time] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildRfidTap|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = RfidTapTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])])))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(RfidTapTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildRfidTap A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT rfid, time FROM rfidtap WHERE rfid = :p0 AND time = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_STR);
            $stmt->bindValue(':p1', $key[1] ? $key[1]->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildRfidTap $obj */
            $obj = new ChildRfidTap();
            $obj->hydrate($row);
            RfidTapTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildRfidTap|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
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
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildRfidTapQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(RfidTapTableMap::COL_RFID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(RfidTapTableMap::COL_TIME, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildRfidTapQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(RfidTapTableMap::COL_RFID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(RfidTapTableMap::COL_TIME, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the rfid column
     *
     * Example usage:
     * <code>
     * $query->filterByRfid('fooValue');   // WHERE rfid = 'fooValue'
     * $query->filterByRfid('%fooValue%'); // WHERE rfid LIKE '%fooValue%'
     * </code>
     *
     * @param     string $rfid The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildRfidTapQuery The current query, for fluid interface
     */
    public function filterByRfid($rfid = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($rfid)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $rfid)) {
                $rfid = str_replace('*', '%', $rfid);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RfidTapTableMap::COL_RFID, $rfid, $comparison);
    }

    /**
     * Filter the query on the time column
     *
     * Example usage:
     * <code>
     * $query->filterByTime('2011-03-14'); // WHERE time = '2011-03-14'
     * $query->filterByTime('now'); // WHERE time = '2011-03-14'
     * $query->filterByTime(array('max' => 'yesterday')); // WHERE time > '2011-03-13'
     * </code>
     *
     * @param     mixed $time The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildRfidTapQuery The current query, for fluid interface
     */
    public function filterByTime($time = null, $comparison = null)
    {
        if (is_array($time)) {
            $useMinMax = false;
            if (isset($time['min'])) {
                $this->addUsingAlias(RfidTapTableMap::COL_TIME, $time['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($time['max'])) {
                $this->addUsingAlias(RfidTapTableMap::COL_TIME, $time['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RfidTapTableMap::COL_TIME, $time, $comparison);
    }

    /**
     * Filter the query by a related \Rfid object
     *
     * @param \Rfid|ObjectCollection $rfid The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildRfidTapQuery The current query, for fluid interface
     */
    public function filterByMapping($rfid, $comparison = null)
    {
        if ($rfid instanceof \Rfid) {
            return $this
                ->addUsingAlias(RfidTapTableMap::COL_RFID, $rfid->getRfid(), $comparison);
        } elseif ($rfid instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RfidTapTableMap::COL_RFID, $rfid->toKeyValue('PrimaryKey', 'Rfid'), $comparison);
        } else {
            throw new PropelException('filterByMapping() only accepts arguments of type \Rfid or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Mapping relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildRfidTapQuery The current query, for fluid interface
     */
    public function joinMapping($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Mapping');

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
            $this->addJoinObject($join, 'Mapping');
        }

        return $this;
    }

    /**
     * Use the Mapping relation Rfid object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \RfidQuery A secondary query class using the current class as primary query
     */
    public function useMappingQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinMapping($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Mapping', '\RfidQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildRfidTap $rfidTap Object to remove from the list of results
     *
     * @return $this|ChildRfidTapQuery The current query, for fluid interface
     */
    public function prune($rfidTap = null)
    {
        if ($rfidTap) {
            $this->addCond('pruneCond0', $this->getAliasedColName(RfidTapTableMap::COL_RFID), $rfidTap->getRfid(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(RfidTapTableMap::COL_TIME), $rfidTap->getTime(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the rfidtap table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RfidTapTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            RfidTapTableMap::clearInstancePool();
            RfidTapTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RfidTapTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(RfidTapTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            RfidTapTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            RfidTapTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // RfidTapQuery
