<?php

namespace Base;

use \User as ChildUser;
use \UserQuery as ChildUserQuery;
use \Exception;
use \PDO;
use Map\UserTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'user' table.
 *
 *
 *
 * @method     ChildUserQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildUserQuery orderByLdap($order = Criteria::ASC) Order by the ldap column
 * @method     ChildUserQuery orderByFirstName($order = Criteria::ASC) Order by the firstname column
 * @method     ChildUserQuery orderByLastName($order = Criteria::ASC) Order by the lastname column
 * @method     ChildUserQuery orderByPlaylistId($order = Criteria::ASC) Order by the playlist_id column
 * @method     ChildUserQuery orderByPlaybackMode($order = Criteria::ASC) Order by the playbackmode column
 *
 * @method     ChildUserQuery groupById() Group by the id column
 * @method     ChildUserQuery groupByLdap() Group by the ldap column
 * @method     ChildUserQuery groupByFirstName() Group by the firstname column
 * @method     ChildUserQuery groupByLastName() Group by the lastname column
 * @method     ChildUserQuery groupByPlaylistId() Group by the playlist_id column
 * @method     ChildUserQuery groupByPlaybackMode() Group by the playbackmode column
 *
 * @method     ChildUserQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserQuery leftJoinPlaylist($relationAlias = null) Adds a LEFT JOIN clause to the query using the Playlist relation
 * @method     ChildUserQuery rightJoinPlaylist($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Playlist relation
 * @method     ChildUserQuery innerJoinPlaylist($relationAlias = null) Adds a INNER JOIN clause to the query using the Playlist relation
 *
 * @method     ChildUserQuery joinWithPlaylist($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the Playlist relation
 *
 * @method     ChildUserQuery leftJoinWithPlaylist() Adds a LEFT JOIN clause and with to the query using the Playlist relation
 * @method     ChildUserQuery rightJoinWithPlaylist() Adds a RIGHT JOIN clause and with to the query using the Playlist relation
 * @method     ChildUserQuery innerJoinWithPlaylist() Adds a INNER JOIN clause and with to the query using the Playlist relation
 *
 * @method     ChildUserQuery leftJoinSpotifyAccount($relationAlias = null) Adds a LEFT JOIN clause to the query using the SpotifyAccount relation
 * @method     ChildUserQuery rightJoinSpotifyAccount($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SpotifyAccount relation
 * @method     ChildUserQuery innerJoinSpotifyAccount($relationAlias = null) Adds a INNER JOIN clause to the query using the SpotifyAccount relation
 *
 * @method     ChildUserQuery joinWithSpotifyAccount($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the SpotifyAccount relation
 *
 * @method     ChildUserQuery leftJoinWithSpotifyAccount() Adds a LEFT JOIN clause and with to the query using the SpotifyAccount relation
 * @method     ChildUserQuery rightJoinWithSpotifyAccount() Adds a RIGHT JOIN clause and with to the query using the SpotifyAccount relation
 * @method     ChildUserQuery innerJoinWithSpotifyAccount() Adds a INNER JOIN clause and with to the query using the SpotifyAccount relation
 *
 * @method     ChildUserQuery leftJoinPastPlaylist($relationAlias = null) Adds a LEFT JOIN clause to the query using the PastPlaylist relation
 * @method     ChildUserQuery rightJoinPastPlaylist($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PastPlaylist relation
 * @method     ChildUserQuery innerJoinPastPlaylist($relationAlias = null) Adds a INNER JOIN clause to the query using the PastPlaylist relation
 *
 * @method     ChildUserQuery joinWithPastPlaylist($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the PastPlaylist relation
 *
 * @method     ChildUserQuery leftJoinWithPastPlaylist() Adds a LEFT JOIN clause and with to the query using the PastPlaylist relation
 * @method     ChildUserQuery rightJoinWithPastPlaylist() Adds a RIGHT JOIN clause and with to the query using the PastPlaylist relation
 * @method     ChildUserQuery innerJoinWithPastPlaylist() Adds a INNER JOIN clause and with to the query using the PastPlaylist relation
 *
 * @method     \PlaylistQuery|\SpotifyAccountQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUser findOne(ConnectionInterface $con = null) Return the first ChildUser matching the query
 * @method     ChildUser findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUser matching the query, or a new ChildUser object populated from the query conditions when no match is found
 *
 * @method     ChildUser findOneById(int $id) Return the first ChildUser filtered by the id column
 * @method     ChildUser findOneByLdap(string $ldap) Return the first ChildUser filtered by the ldap column
 * @method     ChildUser findOneByFirstName(string $firstname) Return the first ChildUser filtered by the firstname column
 * @method     ChildUser findOneByLastName(string $lastname) Return the first ChildUser filtered by the lastname column
 * @method     ChildUser findOneByPlaylistId(int $playlist_id) Return the first ChildUser filtered by the playlist_id column
 * @method     ChildUser findOneByPlaybackMode(int $playbackmode) Return the first ChildUser filtered by the playbackmode column *

 * @method     ChildUser requirePk($key, ConnectionInterface $con = null) Return the ChildUser by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOne(ConnectionInterface $con = null) Return the first ChildUser matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser requireOneById(int $id) Return the first ChildUser filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByLdap(string $ldap) Return the first ChildUser filtered by the ldap column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByFirstName(string $firstname) Return the first ChildUser filtered by the firstname column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByLastName(string $lastname) Return the first ChildUser filtered by the lastname column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByPlaylistId(int $playlist_id) Return the first ChildUser filtered by the playlist_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByPlaybackMode(int $playbackmode) Return the first ChildUser filtered by the playbackmode column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUser objects based on current ModelCriteria
 * @method     ChildUser[]|ObjectCollection findById(int $id) Return ChildUser objects filtered by the id column
 * @method     ChildUser[]|ObjectCollection findByLdap(string $ldap) Return ChildUser objects filtered by the ldap column
 * @method     ChildUser[]|ObjectCollection findByFirstName(string $firstname) Return ChildUser objects filtered by the firstname column
 * @method     ChildUser[]|ObjectCollection findByLastName(string $lastname) Return ChildUser objects filtered by the lastname column
 * @method     ChildUser[]|ObjectCollection findByPlaylistId(int $playlist_id) Return ChildUser objects filtered by the playlist_id column
 * @method     ChildUser[]|ObjectCollection findByPlaybackMode(int $playbackmode) Return ChildUser objects filtered by the playbackmode column
 * @method     ChildUser[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Base\UserQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'soapy', $modelName = '\\User', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserQuery) {
            return $criteria;
        }
        $query = new ChildUserQuery();
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = UserTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserTableMap::DATABASE_NAME);
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
     * @return ChildUser A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, ldap, firstname, lastname, playlist_id, playbackmode FROM user WHERE id = :p0';
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
            /** @var ChildUser $obj */
            $obj = new ChildUser();
            $obj->hydrate($row);
            UserTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(UserTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(UserTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(UserTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(UserTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_ID, $id, $comparison);
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
     * @return $this|ChildUserQuery The current query, for fluid interface
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

        return $this->addUsingAlias(UserTableMap::COL_LDAP, $ldap, $comparison);
    }

    /**
     * Filter the query on the firstname column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstName('fooValue');   // WHERE firstname = 'fooValue'
     * $query->filterByFirstName('%fooValue%'); // WHERE firstname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $firstName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByFirstName($firstName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($firstName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $firstName)) {
                $firstName = str_replace('*', '%', $firstName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_FIRSTNAME, $firstName, $comparison);
    }

    /**
     * Filter the query on the lastname column
     *
     * Example usage:
     * <code>
     * $query->filterByLastName('fooValue');   // WHERE lastname = 'fooValue'
     * $query->filterByLastName('%fooValue%'); // WHERE lastname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lastName The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByLastName($lastName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastName)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $lastName)) {
                $lastName = str_replace('*', '%', $lastName);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_LASTNAME, $lastName, $comparison);
    }

    /**
     * Filter the query on the playlist_id column
     *
     * Example usage:
     * <code>
     * $query->filterByPlaylistId(1234); // WHERE playlist_id = 1234
     * $query->filterByPlaylistId(array(12, 34)); // WHERE playlist_id IN (12, 34)
     * $query->filterByPlaylistId(array('min' => 12)); // WHERE playlist_id > 12
     * </code>
     *
     * @see       filterByPlaylist()
     *
     * @param     mixed $playlistId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPlaylistId($playlistId = null, $comparison = null)
    {
        if (is_array($playlistId)) {
            $useMinMax = false;
            if (isset($playlistId['min'])) {
                $this->addUsingAlias(UserTableMap::COL_PLAYLIST_ID, $playlistId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($playlistId['max'])) {
                $this->addUsingAlias(UserTableMap::COL_PLAYLIST_ID, $playlistId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_PLAYLIST_ID, $playlistId, $comparison);
    }

    /**
     * Filter the query on the playbackmode column
     *
     * @param     mixed $playbackMode The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPlaybackMode($playbackMode = null, $comparison = null)
    {
        $valueSet = UserTableMap::getValueSet(UserTableMap::COL_PLAYBACKMODE);
        if (is_scalar($playbackMode)) {
            if (!in_array($playbackMode, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $playbackMode));
            }
            $playbackMode = array_search($playbackMode, $valueSet);
        } elseif (is_array($playbackMode)) {
            $convertedValues = array();
            foreach ($playbackMode as $value) {
                if (!in_array($value, $valueSet)) {
                    throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $value));
                }
                $convertedValues []= array_search($value, $valueSet);
            }
            $playbackMode = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_PLAYBACKMODE, $playbackMode, $comparison);
    }

    /**
     * Filter the query by a related \Playlist object
     *
     * @param \Playlist|ObjectCollection $playlist The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserQuery The current query, for fluid interface
     */
    public function filterByPlaylist($playlist, $comparison = null)
    {
        if ($playlist instanceof \Playlist) {
            return $this
                ->addUsingAlias(UserTableMap::COL_PLAYLIST_ID, $playlist->getId(), $comparison);
        } elseif ($playlist instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserTableMap::COL_PLAYLIST_ID, $playlist->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByPlaylist() only accepts arguments of type \Playlist or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Playlist relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function joinPlaylist($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Playlist');

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
            $this->addJoinObject($join, 'Playlist');
        }

        return $this;
    }

    /**
     * Use the Playlist relation Playlist object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PlaylistQuery A secondary query class using the current class as primary query
     */
    public function usePlaylistQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinPlaylist($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Playlist', '\PlaylistQuery');
    }

    /**
     * Filter the query by a related \SpotifyAccount object
     *
     * @param \SpotifyAccount|ObjectCollection $spotifyAccount the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserQuery The current query, for fluid interface
     */
    public function filterBySpotifyAccount($spotifyAccount, $comparison = null)
    {
        if ($spotifyAccount instanceof \SpotifyAccount) {
            return $this
                ->addUsingAlias(UserTableMap::COL_ID, $spotifyAccount->getUserId(), $comparison);
        } elseif ($spotifyAccount instanceof ObjectCollection) {
            return $this
                ->useSpotifyAccountQuery()
                ->filterByPrimaryKeys($spotifyAccount->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterBySpotifyAccount() only accepts arguments of type \SpotifyAccount or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SpotifyAccount relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function joinSpotifyAccount($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SpotifyAccount');

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
            $this->addJoinObject($join, 'SpotifyAccount');
        }

        return $this;
    }

    /**
     * Use the SpotifyAccount relation SpotifyAccount object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \SpotifyAccountQuery A secondary query class using the current class as primary query
     */
    public function useSpotifyAccountQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinSpotifyAccount($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SpotifyAccount', '\SpotifyAccountQuery');
    }

    /**
     * Filter the query by a related \Playlist object
     *
     * @param \Playlist|ObjectCollection $playlist the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserQuery The current query, for fluid interface
     */
    public function filterByPastPlaylist($playlist, $comparison = null)
    {
        if ($playlist instanceof \Playlist) {
            return $this
                ->addUsingAlias(UserTableMap::COL_ID, $playlist->getOwnerId(), $comparison);
        } elseif ($playlist instanceof ObjectCollection) {
            return $this
                ->usePastPlaylistQuery()
                ->filterByPrimaryKeys($playlist->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByPastPlaylist() only accepts arguments of type \Playlist or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PastPlaylist relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function joinPastPlaylist($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PastPlaylist');

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
            $this->addJoinObject($join, 'PastPlaylist');
        }

        return $this;
    }

    /**
     * Use the PastPlaylist relation Playlist object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \PlaylistQuery A secondary query class using the current class as primary query
     */
    public function usePastPlaylistQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinPastPlaylist($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PastPlaylist', '\PlaylistQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUser $user Object to remove from the list of results
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function prune($user = null)
    {
        if ($user) {
            $this->addUsingAlias(UserTableMap::COL_ID, $user->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserTableMap::clearInstancePool();
            UserTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // UserQuery
