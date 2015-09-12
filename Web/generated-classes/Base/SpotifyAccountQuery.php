<?php

namespace Base;

use \SpotifyAccount as ChildSpotifyAccount;
use \SpotifyAccountQuery as ChildSpotifyAccountQuery;
use \Exception;
use \PDO;
use Map\SpotifyAccountTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'spotifyaccount' table.
 *
 *
 *
 * @method     ChildSpotifyAccountQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildSpotifyAccountQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildSpotifyAccountQuery orderByUsername($order = Criteria::ASC) Order by the username column
 * @method     ChildSpotifyAccountQuery orderByAccessToken($order = Criteria::ASC) Order by the accesstoken column
 * @method     ChildSpotifyAccountQuery orderByRefreshToken($order = Criteria::ASC) Order by the refreshtoken column
 * @method     ChildSpotifyAccountQuery orderByExpiration($order = Criteria::ASC) Order by the expiration column
 * @method     ChildSpotifyAccountQuery orderByAvatar($order = Criteria::ASC) Order by the avatar column
 * @method     ChildSpotifyAccountQuery orderByPlaylist($order = Criteria::ASC) Order by the playlist column
 *
 * @method     ChildSpotifyAccountQuery groupById() Group by the id column
 * @method     ChildSpotifyAccountQuery groupByUserId() Group by the user_id column
 * @method     ChildSpotifyAccountQuery groupByUsername() Group by the username column
 * @method     ChildSpotifyAccountQuery groupByAccessToken() Group by the accesstoken column
 * @method     ChildSpotifyAccountQuery groupByRefreshToken() Group by the refreshtoken column
 * @method     ChildSpotifyAccountQuery groupByExpiration() Group by the expiration column
 * @method     ChildSpotifyAccountQuery groupByAvatar() Group by the avatar column
 * @method     ChildSpotifyAccountQuery groupByPlaylist() Group by the playlist column
 *
 * @method     ChildSpotifyAccountQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildSpotifyAccountQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildSpotifyAccountQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildSpotifyAccountQuery leftJoinUser($relationAlias = null) Adds a LEFT JOIN clause to the query using the User relation
 * @method     ChildSpotifyAccountQuery rightJoinUser($relationAlias = null) Adds a RIGHT JOIN clause to the query using the User relation
 * @method     ChildSpotifyAccountQuery innerJoinUser($relationAlias = null) Adds a INNER JOIN clause to the query using the User relation
 *
 * @method     \UserQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildSpotifyAccount findOne(ConnectionInterface $con = null) Return the first ChildSpotifyAccount matching the query
 * @method     ChildSpotifyAccount findOneOrCreate(ConnectionInterface $con = null) Return the first ChildSpotifyAccount matching the query, or a new ChildSpotifyAccount object populated from the query conditions when no match is found
 *
 * @method     ChildSpotifyAccount findOneById(int $id) Return the first ChildSpotifyAccount filtered by the id column
 * @method     ChildSpotifyAccount findOneByUserId(int $user_id) Return the first ChildSpotifyAccount filtered by the user_id column
 * @method     ChildSpotifyAccount findOneByUsername(string $username) Return the first ChildSpotifyAccount filtered by the username column
 * @method     ChildSpotifyAccount findOneByAccessToken(string $accesstoken) Return the first ChildSpotifyAccount filtered by the accesstoken column
 * @method     ChildSpotifyAccount findOneByRefreshToken(string $refreshtoken) Return the first ChildSpotifyAccount filtered by the refreshtoken column
 * @method     ChildSpotifyAccount findOneByExpiration(string $expiration) Return the first ChildSpotifyAccount filtered by the expiration column
 * @method     ChildSpotifyAccount findOneByAvatar(string $avatar) Return the first ChildSpotifyAccount filtered by the avatar column
 * @method     ChildSpotifyAccount findOneByPlaylist(string $playlist) Return the first ChildSpotifyAccount filtered by the playlist column *

 * @method     ChildSpotifyAccount requirePk($key, ConnectionInterface $con = null) Return the ChildSpotifyAccount by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOne(ConnectionInterface $con = null) Return the first ChildSpotifyAccount matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSpotifyAccount requireOneById(int $id) Return the first ChildSpotifyAccount filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOneByUserId(int $user_id) Return the first ChildSpotifyAccount filtered by the user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOneByUsername(string $username) Return the first ChildSpotifyAccount filtered by the username column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOneByAccessToken(string $accesstoken) Return the first ChildSpotifyAccount filtered by the accesstoken column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOneByRefreshToken(string $refreshtoken) Return the first ChildSpotifyAccount filtered by the refreshtoken column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOneByExpiration(string $expiration) Return the first ChildSpotifyAccount filtered by the expiration column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOneByAvatar(string $avatar) Return the first ChildSpotifyAccount filtered by the avatar column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildSpotifyAccount requireOneByPlaylist(string $playlist) Return the first ChildSpotifyAccount filtered by the playlist column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildSpotifyAccount[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildSpotifyAccount objects based on current ModelCriteria
 * @method     ChildSpotifyAccount[]|ObjectCollection findById(int $id) Return ChildSpotifyAccount objects filtered by the id column
 * @method     ChildSpotifyAccount[]|ObjectCollection findByUserId(int $user_id) Return ChildSpotifyAccount objects filtered by the user_id column
 * @method     ChildSpotifyAccount[]|ObjectCollection findByUsername(string $username) Return ChildSpotifyAccount objects filtered by the username column
 * @method     ChildSpotifyAccount[]|ObjectCollection findByAccessToken(string $accesstoken) Return ChildSpotifyAccount objects filtered by the accesstoken column
 * @method     ChildSpotifyAccount[]|ObjectCollection findByRefreshToken(string $refreshtoken) Return ChildSpotifyAccount objects filtered by the refreshtoken column
 * @method     ChildSpotifyAccount[]|ObjectCollection findByExpiration(string $expiration) Return ChildSpotifyAccount objects filtered by the expiration column
 * @method     ChildSpotifyAccount[]|ObjectCollection findByAvatar(string $avatar) Return ChildSpotifyAccount objects filtered by the avatar column
 * @method     ChildSpotifyAccount[]|ObjectCollection findByPlaylist(string $playlist) Return ChildSpotifyAccount objects filtered by the playlist column
 * @method     ChildSpotifyAccount[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class SpotifyAccountQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Base\SpotifyAccountQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'soapy', $modelName = '\\SpotifyAccount', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildSpotifyAccountQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildSpotifyAccountQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildSpotifyAccountQuery) {
            return $criteria;
        }
        $query = new ChildSpotifyAccountQuery();
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
     * @return ChildSpotifyAccount|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = SpotifyAccountTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(SpotifyAccountTableMap::DATABASE_NAME);
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
     * @return ChildSpotifyAccount A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, user_id, username, accesstoken, refreshtoken, expiration, avatar, playlist FROM spotifyaccount WHERE id = :p0';
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
            /** @var ChildSpotifyAccount $obj */
            $obj = new ChildSpotifyAccount();
            $obj->hydrate($row);
            SpotifyAccountTableMap::addInstanceToPool($obj, (string) $key);
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
     * @return ChildSpotifyAccount|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(SpotifyAccountTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(SpotifyAccountTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE user_id > 12
     * </code>
     *
     * @see       filterByUser()
     *
     * @param     mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(SpotifyAccountTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(SpotifyAccountTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_USER_ID, $userId, $comparison);
    }

    /**
     * Filter the query on the username column
     *
     * Example usage:
     * <code>
     * $query->filterByUsername('fooValue');   // WHERE username = 'fooValue'
     * $query->filterByUsername('%fooValue%'); // WHERE username LIKE '%fooValue%'
     * </code>
     *
     * @param     string $username The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByUsername($username = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($username)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $username)) {
                $username = str_replace('*', '%', $username);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_USERNAME, $username, $comparison);
    }

    /**
     * Filter the query on the accesstoken column
     *
     * Example usage:
     * <code>
     * $query->filterByAccessToken('fooValue');   // WHERE accesstoken = 'fooValue'
     * $query->filterByAccessToken('%fooValue%'); // WHERE accesstoken LIKE '%fooValue%'
     * </code>
     *
     * @param     string $accessToken The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByAccessToken($accessToken = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($accessToken)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $accessToken)) {
                $accessToken = str_replace('*', '%', $accessToken);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_ACCESSTOKEN, $accessToken, $comparison);
    }

    /**
     * Filter the query on the refreshtoken column
     *
     * Example usage:
     * <code>
     * $query->filterByRefreshToken('fooValue');   // WHERE refreshtoken = 'fooValue'
     * $query->filterByRefreshToken('%fooValue%'); // WHERE refreshtoken LIKE '%fooValue%'
     * </code>
     *
     * @param     string $refreshToken The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByRefreshToken($refreshToken = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($refreshToken)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $refreshToken)) {
                $refreshToken = str_replace('*', '%', $refreshToken);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_REFRESHTOKEN, $refreshToken, $comparison);
    }

    /**
     * Filter the query on the expiration column
     *
     * Example usage:
     * <code>
     * $query->filterByExpiration('2011-03-14'); // WHERE expiration = '2011-03-14'
     * $query->filterByExpiration('now'); // WHERE expiration = '2011-03-14'
     * $query->filterByExpiration(array('max' => 'yesterday')); // WHERE expiration > '2011-03-13'
     * </code>
     *
     * @param     mixed $expiration The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByExpiration($expiration = null, $comparison = null)
    {
        if (is_array($expiration)) {
            $useMinMax = false;
            if (isset($expiration['min'])) {
                $this->addUsingAlias(SpotifyAccountTableMap::COL_EXPIRATION, $expiration['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($expiration['max'])) {
                $this->addUsingAlias(SpotifyAccountTableMap::COL_EXPIRATION, $expiration['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_EXPIRATION, $expiration, $comparison);
    }

    /**
     * Filter the query on the avatar column
     *
     * Example usage:
     * <code>
     * $query->filterByAvatar('fooValue');   // WHERE avatar = 'fooValue'
     * $query->filterByAvatar('%fooValue%'); // WHERE avatar LIKE '%fooValue%'
     * </code>
     *
     * @param     string $avatar The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByAvatar($avatar = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($avatar)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $avatar)) {
                $avatar = str_replace('*', '%', $avatar);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_AVATAR, $avatar, $comparison);
    }

    /**
     * Filter the query on the playlist column
     *
     * Example usage:
     * <code>
     * $query->filterByPlaylist('fooValue');   // WHERE playlist = 'fooValue'
     * $query->filterByPlaylist('%fooValue%'); // WHERE playlist LIKE '%fooValue%'
     * </code>
     *
     * @param     string $playlist The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByPlaylist($playlist = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($playlist)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $playlist)) {
                $playlist = str_replace('*', '%', $playlist);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(SpotifyAccountTableMap::COL_PLAYLIST, $playlist, $comparison);
    }

    /**
     * Filter the query by a related \User object
     *
     * @param \User|ObjectCollection $user The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function filterByUser($user, $comparison = null)
    {
        if ($user instanceof \User) {
            return $this
                ->addUsingAlias(SpotifyAccountTableMap::COL_USER_ID, $user->getId(), $comparison);
        } elseif ($user instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(SpotifyAccountTableMap::COL_USER_ID, $user->toKeyValue('PrimaryKey', 'Id'), $comparison);
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
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function joinUser($relationAlias = null, $joinType = Criteria::INNER_JOIN)
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
    public function useUserQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUser($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'User', '\UserQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildSpotifyAccount $spotifyAccount Object to remove from the list of results
     *
     * @return $this|ChildSpotifyAccountQuery The current query, for fluid interface
     */
    public function prune($spotifyAccount = null)
    {
        if ($spotifyAccount) {
            $this->addUsingAlias(SpotifyAccountTableMap::COL_ID, $spotifyAccount->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the spotifyaccount table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(SpotifyAccountTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            SpotifyAccountTableMap::clearInstancePool();
            SpotifyAccountTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(SpotifyAccountTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(SpotifyAccountTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            SpotifyAccountTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            SpotifyAccountTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // SpotifyAccountQuery
