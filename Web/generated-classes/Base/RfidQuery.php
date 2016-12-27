<?php

namespace Base;

use \Rfid as ChildRfid;
use \RfidQuery as ChildRfidQuery;
use \Exception;
use \PDO;
use Map\RfidTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'rfid' table.
 *
 *
 *
 * @method     ChildRfidQuery orderByRfid($order = Criteria::ASC) Order by the rfid column
 * @method     ChildRfidQuery orderByLdap($order = Criteria::ASC) Order by the ldap column
 *
 * @method     ChildRfidQuery groupByRfid() Group by the rfid column
 * @method     ChildRfidQuery groupByLdap() Group by the ldap column
 *
 * @method     ChildRfidQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildRfidQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildRfidQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildRfidQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildRfidQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildRfidQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildRfidQuery leftJoinUser($relationAlias = null) Adds a LEFT JOIN clause to the query using the User relation
 * @method     ChildRfidQuery rightJoinUser($relationAlias = null) Adds a RIGHT JOIN clause to the query using the User relation
 * @method     ChildRfidQuery innerJoinUser($relationAlias = null) Adds a INNER JOIN clause to the query using the User relation
 *
 * @method     ChildRfidQuery joinWithUser($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the User relation
 *
 * @method     ChildRfidQuery leftJoinWithUser() Adds a LEFT JOIN clause and with to the query using the User relation
 * @method     ChildRfidQuery rightJoinWithUser() Adds a RIGHT JOIN clause and with to the query using the User relation
 * @method     ChildRfidQuery innerJoinWithUser() Adds a INNER JOIN clause and with to the query using the User relation
 *
 * @method     ChildRfidQuery leftJoinTap($relationAlias = null) Adds a LEFT JOIN clause to the query using the Tap relation
 * @method     ChildRfidQuery rightJoinTap($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Tap relation
 * @method     ChildRfidQuery innerJoinTap($relationAlias = null) Adds a INNER JOIN clause to the query using the Tap relation
 *
 * @method     ChildRfidQuery joinWithTap($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Tap relation
 *
 * @method     ChildRfidQuery leftJoinWithTap() Adds a LEFT JOIN clause and with to the query using the Tap relation
 * @method     ChildRfidQuery rightJoinWithTap() Adds a RIGHT JOIN clause and with to the query using the Tap relation
 * @method     ChildRfidQuery innerJoinWithTap() Adds a INNER JOIN clause and with to the query using the Tap relation
 *
 * @method     \UserQuery|\RfidTapQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildRfid findOne(ConnectionInterface $con = null) Return the first ChildRfid matching the query
 * @method     ChildRfid findOneOrCreate(ConnectionInterface $con = null) Return the first ChildRfid matching the query, or a new ChildRfid object populated from the query conditions when no match is found
 *
 * @method     ChildRfid findOneByRfid(string $rfid) Return the first ChildRfid filtered by the rfid column
 * @method     ChildRfid findOneByLdap(string $ldap) Return the first ChildRfid filtered by the ldap column *

 * @method     ChildRfid requirePk($key, ConnectionInterface $con = null) Return the ChildRfid by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildRfid requireOne(ConnectionInterface $con = null) Return the first ChildRfid matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildRfid requireOneByRfid(string $rfid) Return the first ChildRfid filtered by the rfid column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildRfid requireOneByLdap(string $ldap) Return the first ChildRfid filtered by the ldap column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildRfid[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildRfid objects based on current ModelCriteria
 * @method     ChildRfid[]|ObjectCollection findByRfid(string $rfid) Return ChildRfid objects filtered by the rfid column
 * @method     ChildRfid[]|ObjectCollection findByLdap(string $ldap) Return ChildRfid objects filtered by the ldap column
 * @method     ChildRfid[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class RfidQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Base\RfidQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'soapy', $modelName = '\\Rfid', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildRfidQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildRfidQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildRfidQuery) {
            return $criteria;
        }
        $query = new ChildRfidQuery();
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
     * @return ChildRfid|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = RfidTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(RfidTableMap::DATABASE_NAME);
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
     * @return ChildRfid A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT rfid, ldap FROM rfid WHERE rfid = :p0';
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
            /** @var ChildRfid $obj */
            $obj = new ChildRfid();
            $obj->hydrate($row);
            RfidTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildRfid|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildRfidQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(RfidTableMap::COL_RFID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildRfidQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(RfidTableMap::COL_RFID, $keys, Criteria::IN);
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
     * @return $this|ChildRfidQuery The current query, for fluid interface
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

        return $this->addUsingAlias(RfidTableMap::COL_RFID, $rfid, $comparison);
    }

    /**
     * Filter the query on the ldap column
     *
     * Example usage:
     * <code>
     * $query->filterByLdap('fooValue');   // WHERE ldap = 'fooValue'
     * $query->filterByLdap('%fooValue%'); // WHERE ldap LIKE '%fooValue%'
     * </code>
     *
     * @param     string $ldap The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildRfidQuery The current query, for fluid interface
     */
    public function filterByLdap($ldap = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($ldap)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $ldap)) {
                $ldap = str_replace('*', '%', $ldap);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(RfidTableMap::COL_LDAP, $ldap, $comparison);
    }

    /**
     * Filter the query by a related \User object
     *
     * @param \User|ObjectCollection $user The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildRfidQuery The current query, for fluid interface
     */
    public function filterByUser($user, $comparison = null)
    {
        if ($user instanceof \User) {
            return $this
                ->addUsingAlias(RfidTableMap::COL_LDAP, $user->getLdap(), $comparison);
        } elseif ($user instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RfidTableMap::COL_LDAP, $user->toKeyValue('PrimaryKey', 'Ldap'), $comparison);
        } else {
            throw new PropelException('filterByUser() only accepts arguments of type \User or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the User relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildRfidQuery The current query, for fluid interface
     */
    public function joinUser($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('User');

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
            $this->addJoinObject($join, 'User');
        }

        return $this;
    }

    /**
     * Use the User relation User object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \UserQuery A secondary query class using the current class as primary query
     */
    public function useUserQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinUser($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'User', '\UserQuery');
    }

    /**
     * Filter the query by a related \RfidTap object
     *
     * @param \RfidTap|ObjectCollection $rfidTap the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildRfidQuery The current query, for fluid interface
     */
    public function filterByTap($rfidTap, $comparison = null)
    {
        if ($rfidTap instanceof \RfidTap) {
            return $this
                ->addUsingAlias(RfidTableMap::COL_RFID, $rfidTap->getRfid(), $comparison);
        } elseif ($rfidTap instanceof ObjectCollection) {
            return $this
                ->useTapQuery()
                ->filterByPrimaryKeys($rfidTap->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTap() only accepts arguments of type \RfidTap or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Tap relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildRfidQuery The current query, for fluid interface
     */
    public function joinTap($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Tap');

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
            $this->addJoinObject($join, 'Tap');
        }

        return $this;
    }

    /**
     * Use the Tap relation RfidTap object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \RfidTapQuery A secondary query class using the current class as primary query
     */
    public function useTapQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinTap($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Tap', '\RfidTapQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildRfid $rfid Object to remove from the list of results
     *
     * @return $this|ChildRfidQuery The current query, for fluid interface
     */
    public function prune($rfid = null)
    {
        if ($rfid) {
            $this->addUsingAlias(RfidTableMap::COL_RFID, $rfid->getRfid(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the rfid table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(RfidTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            RfidTableMap::clearInstancePool();
            RfidTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(RfidTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(RfidTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            RfidTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            RfidTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // RfidQuery
