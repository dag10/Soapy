<?php

namespace Base;

use \Playlist as ChildPlaylist;
use \PlaylistQuery as ChildPlaylistQuery;
use \Exception;
use \PDO;
use Map\PlaylistTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'playlist' table.
 *
 *
 *
 * @method     ChildPlaylistQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildPlaylistQuery orderByUri($order = Criteria::ASC) Order by the uri column
 * @method     ChildPlaylistQuery orderByNextSong($order = Criteria::ASC) Order by the nextsong column
 * @method     ChildPlaylistQuery orderByOwnerId($order = Criteria::ASC) Order by the owner_id column
 *
 * @method     ChildPlaylistQuery groupById() Group by the id column
 * @method     ChildPlaylistQuery groupByUri() Group by the uri column
 * @method     ChildPlaylistQuery groupByNextSong() Group by the nextsong column
 * @method     ChildPlaylistQuery groupByOwnerId() Group by the owner_id column
 *
 * @method     ChildPlaylistQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildPlaylistQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildPlaylistQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildPlaylistQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildPlaylistQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildPlaylistQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildPlaylistQuery leftJoinOwner($relationAlias = null) Adds a LEFT JOIN clause to the query using the Owner relation
 * @method     ChildPlaylistQuery rightJoinOwner($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Owner relation
 * @method     ChildPlaylistQuery innerJoinOwner($relationAlias = null) Adds a INNER JOIN clause to the query using the Owner relation
 *
 * @method     ChildPlaylistQuery joinWithOwner($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Owner relation
 *
 * @method     ChildPlaylistQuery leftJoinWithOwner() Adds a LEFT JOIN clause and with to the query using the Owner relation
 * @method     ChildPlaylistQuery rightJoinWithOwner() Adds a RIGHT JOIN clause and with to the query using the Owner relation
 * @method     ChildPlaylistQuery innerJoinWithOwner() Adds a INNER JOIN clause and with to the query using the Owner relation
 *
 * @method     ChildPlaylistQuery leftJoinUserRelatedByPlaylistId($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserRelatedByPlaylistId relation
 * @method     ChildPlaylistQuery rightJoinUserRelatedByPlaylistId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserRelatedByPlaylistId relation
 * @method     ChildPlaylistQuery innerJoinUserRelatedByPlaylistId($relationAlias = null) Adds a INNER JOIN clause to the query using the UserRelatedByPlaylistId relation
 *
 * @method     ChildPlaylistQuery joinWithUserRelatedByPlaylistId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserRelatedByPlaylistId relation
 *
 * @method     ChildPlaylistQuery leftJoinWithUserRelatedByPlaylistId() Adds a LEFT JOIN clause and with to the query using the UserRelatedByPlaylistId relation
 * @method     ChildPlaylistQuery rightJoinWithUserRelatedByPlaylistId() Adds a RIGHT JOIN clause and with to the query using the UserRelatedByPlaylistId relation
 * @method     ChildPlaylistQuery innerJoinWithUserRelatedByPlaylistId() Adds a INNER JOIN clause and with to the query using the UserRelatedByPlaylistId relation
 *
 * @method     \UserQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildPlaylist findOne(ConnectionInterface $con = null) Return the first ChildPlaylist matching the query
 * @method     ChildPlaylist findOneOrCreate(ConnectionInterface $con = null) Return the first ChildPlaylist matching the query, or a new ChildPlaylist object populated from the query conditions when no match is found
 *
 * @method     ChildPlaylist findOneById(int $id) Return the first ChildPlaylist filtered by the id column
 * @method     ChildPlaylist findOneByUri(string $uri) Return the first ChildPlaylist filtered by the uri column
 * @method     ChildPlaylist findOneByNextSong(string $nextsong) Return the first ChildPlaylist filtered by the nextsong column
 * @method     ChildPlaylist findOneByOwnerId(int $owner_id) Return the first ChildPlaylist filtered by the owner_id column *

 * @method     ChildPlaylist requirePk($key, ConnectionInterface $con = null) Return the ChildPlaylist by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPlaylist requireOne(ConnectionInterface $con = null) Return the first ChildPlaylist matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPlaylist requireOneById(int $id) Return the first ChildPlaylist filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPlaylist requireOneByUri(string $uri) Return the first ChildPlaylist filtered by the uri column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPlaylist requireOneByNextSong(string $nextsong) Return the first ChildPlaylist filtered by the nextsong column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildPlaylist requireOneByOwnerId(int $owner_id) Return the first ChildPlaylist filtered by the owner_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildPlaylist[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildPlaylist objects based on current ModelCriteria
 * @method     ChildPlaylist[]|ObjectCollection findById(int $id) Return ChildPlaylist objects filtered by the id column
 * @method     ChildPlaylist[]|ObjectCollection findByUri(string $uri) Return ChildPlaylist objects filtered by the uri column
 * @method     ChildPlaylist[]|ObjectCollection findByNextSong(string $nextsong) Return ChildPlaylist objects filtered by the nextsong column
 * @method     ChildPlaylist[]|ObjectCollection findByOwnerId(int $owner_id) Return ChildPlaylist objects filtered by the owner_id column
 * @method     ChildPlaylist[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class PlaylistQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Base\PlaylistQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'soapy', $modelName = '\\Playlist', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildPlaylistQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildPlaylistQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildPlaylistQuery) {
            return $criteria;
        }
        $query = new ChildPlaylistQuery();
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
     * @return ChildPlaylist|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = PlaylistTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(PlaylistTableMap::DATABASE_NAME);
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
     * @return ChildPlaylist A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, uri, nextsong, owner_id FROM playlist WHERE id = :p0';
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
            /** @var ChildPlaylist $obj */
            $obj = new ChildPlaylist();
            $obj->hydrate($row);
            PlaylistTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildPlaylist|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(PlaylistTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(PlaylistTableMap::COL_ID, $keys, Criteria::IN);
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
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(PlaylistTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(PlaylistTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PlaylistTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the uri column
     *
     * Example usage:
     * <code>
     * $query->filterByUri('fooValue');   // WHERE uri = 'fooValue'
     * $query->filterByUri('%fooValue%'); // WHERE uri LIKE '%fooValue%'
     * </code>
     *
     * @param     string $uri The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterByUri($uri = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($uri)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $uri)) {
                $uri = str_replace('*', '%', $uri);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PlaylistTableMap::COL_URI, $uri, $comparison);
    }

    /**
     * Filter the query on the nextsong column
     *
     * Example usage:
     * <code>
     * $query->filterByNextSong('fooValue');   // WHERE nextsong = 'fooValue'
     * $query->filterByNextSong('%fooValue%'); // WHERE nextsong LIKE '%fooValue%'
     * </code>
     *
     * @param     string $nextSong The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterByNextSong($nextSong = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($nextSong)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $nextSong)) {
                $nextSong = str_replace('*', '%', $nextSong);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(PlaylistTableMap::COL_NEXTSONG, $nextSong, $comparison);
    }

    /**
     * Filter the query on the owner_id column
     *
     * Example usage:
     * <code>
     * $query->filterByOwnerId(1234); // WHERE owner_id = 1234
     * $query->filterByOwnerId(array(12, 34)); // WHERE owner_id IN (12, 34)
     * $query->filterByOwnerId(array('min' => 12)); // WHERE owner_id > 12
     * </code>
     *
     * @see       filterByOwner()
     *
     * @param     mixed $ownerId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterByOwnerId($ownerId = null, $comparison = null)
    {
        if (is_array($ownerId)) {
            $useMinMax = false;
            if (isset($ownerId['min'])) {
                $this->addUsingAlias(PlaylistTableMap::COL_OWNER_ID, $ownerId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($ownerId['max'])) {
                $this->addUsingAlias(PlaylistTableMap::COL_OWNER_ID, $ownerId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(PlaylistTableMap::COL_OWNER_ID, $ownerId, $comparison);
    }

    /**
     * Filter the query by a related \User object
     *
     * @param \User|ObjectCollection $user The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterByOwner($user, $comparison = null)
    {
        if ($user instanceof \User) {
            return $this
                ->addUsingAlias(PlaylistTableMap::COL_OWNER_ID, $user->getId(), $comparison);
        } elseif ($user instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(PlaylistTableMap::COL_OWNER_ID, $user->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByOwner() only accepts arguments of type \User or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Owner relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function joinOwner($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Owner');

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
            $this->addJoinObject($join, 'Owner');
        }

        return $this;
    }

    /**
     * Use the Owner relation User object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \UserQuery A secondary query class using the current class as primary query
     */
    public function useOwnerQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinOwner($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Owner', '\UserQuery');
    }

    /**
     * Filter the query by a related \User object
     *
     * @param \User|ObjectCollection $user the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildPlaylistQuery The current query, for fluid interface
     */
    public function filterByUserRelatedByPlaylistId($user, $comparison = null)
    {
        if ($user instanceof \User) {
            return $this
                ->addUsingAlias(PlaylistTableMap::COL_ID, $user->getPlaylistId(), $comparison);
        } elseif ($user instanceof ObjectCollection) {
            return $this
                ->useUserRelatedByPlaylistIdQuery()
                ->filterByPrimaryKeys($user->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserRelatedByPlaylistId() only accepts arguments of type \User or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserRelatedByPlaylistId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function joinUserRelatedByPlaylistId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserRelatedByPlaylistId');

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
            $this->addJoinObject($join, 'UserRelatedByPlaylistId');
        }

        return $this;
    }

    /**
     * Use the UserRelatedByPlaylistId relation User object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \UserQuery A secondary query class using the current class as primary query
     */
    public function useUserRelatedByPlaylistIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinUserRelatedByPlaylistId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserRelatedByPlaylistId', '\UserQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildPlaylist $playlist Object to remove from the list of results
     *
     * @return $this|ChildPlaylistQuery The current query, for fluid interface
     */
    public function prune($playlist = null)
    {
        if ($playlist) {
            $this->addUsingAlias(PlaylistTableMap::COL_ID, $playlist->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the playlist table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(PlaylistTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            PlaylistTableMap::clearInstancePool();
            PlaylistTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(PlaylistTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(PlaylistTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            PlaylistTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            PlaylistTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // PlaylistQuery
