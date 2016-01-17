<?php

namespace Base;

use \Playlist as ChildPlaylist;
use \PlaylistQuery as ChildPlaylistQuery;
use \SpotifyAccount as ChildSpotifyAccount;
use \SpotifyAccountQuery as ChildSpotifyAccountQuery;
use \User as ChildUser;
use \UserQuery as ChildUserQuery;
use \Exception;
use \PDO;
use Map\UserTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;

/**
 * Base class that represents a row from the 'user' table.
 *
 *
 *
* @package    propel.generator..Base
*/
abstract class User implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\Map\\UserTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the id field.
     *
     * @var        int
     */
    protected $id;

    /**
     * The value for the ldap field.
     *
     * @var        string
     */
    protected $ldap;

    /**
     * The value for the firstname field.
     *
     * @var        string
     */
    protected $firstname;

    /**
     * The value for the lastname field.
     *
     * @var        string
     */
    protected $lastname;

    /**
     * The value for the playlist_id field.
     *
     * @var        int
     */
    protected $playlist_id;

    /**
     * @var        ChildPlaylist
     */
    protected $aPlaylist;

    /**
     * @var        ObjectCollection|ChildSpotifyAccount[] Collection to store aggregation of ChildSpotifyAccount objects.
     */
    protected $collSpotifyAccounts;
    protected $collSpotifyAccountsPartial;

    /**
     * @var        ObjectCollection|ChildPlaylist[] Collection to store aggregation of ChildPlaylist objects.
     */
    protected $collPastPlaylists;
    protected $collPastPlaylistsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildSpotifyAccount[]
     */
    protected $spotifyAccountsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildPlaylist[]
     */
    protected $pastPlaylistsScheduledForDeletion = null;

    /**
     * Initializes internal state of Base\User object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>User</code> instance.  If
     * <code>obj</code> is an instance of <code>User</code>, delegates to
     * <code>equals(User)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|User The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [id] column value.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the [ldap] column value.
     *
     * @return string
     */
    public function getLdap()
    {
        return $this->ldap;
    }

    /**
     * Get the [firstname] column value.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstname;
    }

    /**
     * Get the [lastname] column value.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastname;
    }

    /**
     * Get the [playlist_id] column value.
     *
     * @return int
     */
    public function getPlaylistId()
    {
        return $this->playlist_id;
    }

    /**
     * Set the value of [id] column.
     *
     * @param int $v new value
     * @return $this|\User The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[UserTableMap::COL_ID] = true;
        }

        return $this;
    } // setId()

    /**
     * Set the value of [ldap] column.
     *
     * @param string $v new value
     * @return $this|\User The current object (for fluent API support)
     */
    public function setLdap($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->ldap !== $v) {
            $this->ldap = $v;
            $this->modifiedColumns[UserTableMap::COL_LDAP] = true;
        }

        return $this;
    } // setLdap()

    /**
     * Set the value of [firstname] column.
     *
     * @param string $v new value
     * @return $this|\User The current object (for fluent API support)
     */
    public function setFirstName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->firstname !== $v) {
            $this->firstname = $v;
            $this->modifiedColumns[UserTableMap::COL_FIRSTNAME] = true;
        }

        return $this;
    } // setFirstName()

    /**
     * Set the value of [lastname] column.
     *
     * @param string $v new value
     * @return $this|\User The current object (for fluent API support)
     */
    public function setLastName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->lastname !== $v) {
            $this->lastname = $v;
            $this->modifiedColumns[UserTableMap::COL_LASTNAME] = true;
        }

        return $this;
    } // setLastName()

    /**
     * Set the value of [playlist_id] column.
     *
     * @param int $v new value
     * @return $this|\User The current object (for fluent API support)
     */
    public function setPlaylistId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->playlist_id !== $v) {
            $this->playlist_id = $v;
            $this->modifiedColumns[UserTableMap::COL_PLAYLIST_ID] = true;
        }

        if ($this->aPlaylist !== null && $this->aPlaylist->getId() !== $v) {
            $this->aPlaylist = null;
        }

        return $this;
    } // setPlaylistId()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : UserTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : UserTableMap::translateFieldName('Ldap', TableMap::TYPE_PHPNAME, $indexType)];
            $this->ldap = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : UserTableMap::translateFieldName('FirstName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->firstname = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : UserTableMap::translateFieldName('LastName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->lastname = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : UserTableMap::translateFieldName('PlaylistId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->playlist_id = (null !== $col) ? (int) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 5; // 5 = UserTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\User'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aPlaylist !== null && $this->playlist_id !== $this->aPlaylist->getId()) {
            $this->aPlaylist = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildUserQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aPlaylist = null;
            $this->collSpotifyAccounts = null;

            $this->collPastPlaylists = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see User::setDeleted()
     * @see User::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildUserQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $isInsert = $this->isNew();
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                UserTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aPlaylist !== null) {
                if ($this->aPlaylist->isModified() || $this->aPlaylist->isNew()) {
                    $affectedRows += $this->aPlaylist->save($con);
                }
                $this->setPlaylist($this->aPlaylist);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->spotifyAccountsScheduledForDeletion !== null) {
                if (!$this->spotifyAccountsScheduledForDeletion->isEmpty()) {
                    \SpotifyAccountQuery::create()
                        ->filterByPrimaryKeys($this->spotifyAccountsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->spotifyAccountsScheduledForDeletion = null;
                }
            }

            if ($this->collSpotifyAccounts !== null) {
                foreach ($this->collSpotifyAccounts as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->pastPlaylistsScheduledForDeletion !== null) {
                if (!$this->pastPlaylistsScheduledForDeletion->isEmpty()) {
                    \PlaylistQuery::create()
                        ->filterByPrimaryKeys($this->pastPlaylistsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->pastPlaylistsScheduledForDeletion = null;
                }
            }

            if ($this->collPastPlaylists !== null) {
                foreach ($this->collPastPlaylists as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[UserTableMap::COL_ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . UserTableMap::COL_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(UserTableMap::COL_ID)) {
            $modifiedColumns[':p' . $index++]  = 'id';
        }
        if ($this->isColumnModified(UserTableMap::COL_LDAP)) {
            $modifiedColumns[':p' . $index++]  = 'ldap';
        }
        if ($this->isColumnModified(UserTableMap::COL_FIRSTNAME)) {
            $modifiedColumns[':p' . $index++]  = 'firstname';
        }
        if ($this->isColumnModified(UserTableMap::COL_LASTNAME)) {
            $modifiedColumns[':p' . $index++]  = 'lastname';
        }
        if ($this->isColumnModified(UserTableMap::COL_PLAYLIST_ID)) {
            $modifiedColumns[':p' . $index++]  = 'playlist_id';
        }

        $sql = sprintf(
            'INSERT INTO user (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'id':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'ldap':
                        $stmt->bindValue($identifier, $this->ldap, PDO::PARAM_STR);
                        break;
                    case 'firstname':
                        $stmt->bindValue($identifier, $this->firstname, PDO::PARAM_STR);
                        break;
                    case 'lastname':
                        $stmt->bindValue($identifier, $this->lastname, PDO::PARAM_STR);
                        break;
                    case 'playlist_id':
                        $stmt->bindValue($identifier, $this->playlist_id, PDO::PARAM_INT);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = UserTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getId();
                break;
            case 1:
                return $this->getLdap();
                break;
            case 2:
                return $this->getFirstName();
                break;
            case 3:
                return $this->getLastName();
                break;
            case 4:
                return $this->getPlaylistId();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['User'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['User'][$this->hashCode()] = true;
        $keys = UserTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getId(),
            $keys[1] => $this->getLdap(),
            $keys[2] => $this->getFirstName(),
            $keys[3] => $this->getLastName(),
            $keys[4] => $this->getPlaylistId(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aPlaylist) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'playlist';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'playlist';
                        break;
                    default:
                        $key = 'Playlist';
                }

                $result[$key] = $this->aPlaylist->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collSpotifyAccounts) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'spotifyAccounts';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'spotifyaccounts';
                        break;
                    default:
                        $key = 'SpotifyAccounts';
                }

                $result[$key] = $this->collSpotifyAccounts->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPastPlaylists) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'playlists';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'playlists';
                        break;
                    default:
                        $key = 'Playlists';
                }

                $result[$key] = $this->collPastPlaylists->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\User
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = UserTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\User
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setId($value);
                break;
            case 1:
                $this->setLdap($value);
                break;
            case 2:
                $this->setFirstName($value);
                break;
            case 3:
                $this->setLastName($value);
                break;
            case 4:
                $this->setPlaylistId($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = UserTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setLdap($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setFirstName($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setLastName($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setPlaylistId($arr[$keys[4]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\User The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(UserTableMap::DATABASE_NAME);

        if ($this->isColumnModified(UserTableMap::COL_ID)) {
            $criteria->add(UserTableMap::COL_ID, $this->id);
        }
        if ($this->isColumnModified(UserTableMap::COL_LDAP)) {
            $criteria->add(UserTableMap::COL_LDAP, $this->ldap);
        }
        if ($this->isColumnModified(UserTableMap::COL_FIRSTNAME)) {
            $criteria->add(UserTableMap::COL_FIRSTNAME, $this->firstname);
        }
        if ($this->isColumnModified(UserTableMap::COL_LASTNAME)) {
            $criteria->add(UserTableMap::COL_LASTNAME, $this->lastname);
        }
        if ($this->isColumnModified(UserTableMap::COL_PLAYLIST_ID)) {
            $criteria->add(UserTableMap::COL_PLAYLIST_ID, $this->playlist_id);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildUserQuery::create();
        $criteria->add(UserTableMap::COL_ID, $this->id);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getId();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \User (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setLdap($this->getLdap());
        $copyObj->setFirstName($this->getFirstName());
        $copyObj->setLastName($this->getLastName());
        $copyObj->setPlaylistId($this->getPlaylistId());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getSpotifyAccounts() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addSpotifyAccount($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPastPlaylists() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPastPlaylist($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \User Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildPlaylist object.
     *
     * @param  ChildPlaylist $v
     * @return $this|\User The current object (for fluent API support)
     * @throws PropelException
     */
    public function setPlaylist(ChildPlaylist $v = null)
    {
        if ($v === null) {
            $this->setPlaylistId(NULL);
        } else {
            $this->setPlaylistId($v->getId());
        }

        $this->aPlaylist = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildPlaylist object, it will not be re-added.
        if ($v !== null) {
            $v->addUserRelatedByPlaylistId($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildPlaylist object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildPlaylist The associated ChildPlaylist object.
     * @throws PropelException
     */
    public function getPlaylist(ConnectionInterface $con = null)
    {
        if ($this->aPlaylist === null && ($this->playlist_id !== null)) {
            $this->aPlaylist = ChildPlaylistQuery::create()->findPk($this->playlist_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aPlaylist->addUsersRelatedByPlaylistId($this);
             */
        }

        return $this->aPlaylist;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('SpotifyAccount' == $relationName) {
            return $this->initSpotifyAccounts();
        }
        if ('PastPlaylist' == $relationName) {
            return $this->initPastPlaylists();
        }
    }

    /**
     * Clears out the collSpotifyAccounts collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addSpotifyAccounts()
     */
    public function clearSpotifyAccounts()
    {
        $this->collSpotifyAccounts = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collSpotifyAccounts collection loaded partially.
     */
    public function resetPartialSpotifyAccounts($v = true)
    {
        $this->collSpotifyAccountsPartial = $v;
    }

    /**
     * Initializes the collSpotifyAccounts collection.
     *
     * By default this just sets the collSpotifyAccounts collection to an empty array (like clearcollSpotifyAccounts());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initSpotifyAccounts($overrideExisting = true)
    {
        if (null !== $this->collSpotifyAccounts && !$overrideExisting) {
            return;
        }
        $this->collSpotifyAccounts = new ObjectCollection();
        $this->collSpotifyAccounts->setModel('\SpotifyAccount');
    }

    /**
     * Gets an array of ChildSpotifyAccount objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildSpotifyAccount[] List of ChildSpotifyAccount objects
     * @throws PropelException
     */
    public function getSpotifyAccounts(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collSpotifyAccountsPartial && !$this->isNew();
        if (null === $this->collSpotifyAccounts || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collSpotifyAccounts) {
                // return empty collection
                $this->initSpotifyAccounts();
            } else {
                $collSpotifyAccounts = ChildSpotifyAccountQuery::create(null, $criteria)
                    ->filterByUser($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collSpotifyAccountsPartial && count($collSpotifyAccounts)) {
                        $this->initSpotifyAccounts(false);

                        foreach ($collSpotifyAccounts as $obj) {
                            if (false == $this->collSpotifyAccounts->contains($obj)) {
                                $this->collSpotifyAccounts->append($obj);
                            }
                        }

                        $this->collSpotifyAccountsPartial = true;
                    }

                    return $collSpotifyAccounts;
                }

                if ($partial && $this->collSpotifyAccounts) {
                    foreach ($this->collSpotifyAccounts as $obj) {
                        if ($obj->isNew()) {
                            $collSpotifyAccounts[] = $obj;
                        }
                    }
                }

                $this->collSpotifyAccounts = $collSpotifyAccounts;
                $this->collSpotifyAccountsPartial = false;
            }
        }

        return $this->collSpotifyAccounts;
    }

    /**
     * Sets a collection of ChildSpotifyAccount objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $spotifyAccounts A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildUser The current object (for fluent API support)
     */
    public function setSpotifyAccounts(Collection $spotifyAccounts, ConnectionInterface $con = null)
    {
        /** @var ChildSpotifyAccount[] $spotifyAccountsToDelete */
        $spotifyAccountsToDelete = $this->getSpotifyAccounts(new Criteria(), $con)->diff($spotifyAccounts);


        $this->spotifyAccountsScheduledForDeletion = $spotifyAccountsToDelete;

        foreach ($spotifyAccountsToDelete as $spotifyAccountRemoved) {
            $spotifyAccountRemoved->setUser(null);
        }

        $this->collSpotifyAccounts = null;
        foreach ($spotifyAccounts as $spotifyAccount) {
            $this->addSpotifyAccount($spotifyAccount);
        }

        $this->collSpotifyAccounts = $spotifyAccounts;
        $this->collSpotifyAccountsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related SpotifyAccount objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related SpotifyAccount objects.
     * @throws PropelException
     */
    public function countSpotifyAccounts(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collSpotifyAccountsPartial && !$this->isNew();
        if (null === $this->collSpotifyAccounts || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collSpotifyAccounts) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getSpotifyAccounts());
            }

            $query = ChildSpotifyAccountQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByUser($this)
                ->count($con);
        }

        return count($this->collSpotifyAccounts);
    }

    /**
     * Method called to associate a ChildSpotifyAccount object to this object
     * through the ChildSpotifyAccount foreign key attribute.
     *
     * @param  ChildSpotifyAccount $l ChildSpotifyAccount
     * @return $this|\User The current object (for fluent API support)
     */
    public function addSpotifyAccount(ChildSpotifyAccount $l)
    {
        if ($this->collSpotifyAccounts === null) {
            $this->initSpotifyAccounts();
            $this->collSpotifyAccountsPartial = true;
        }

        if (!$this->collSpotifyAccounts->contains($l)) {
            $this->doAddSpotifyAccount($l);
        }

        return $this;
    }

    /**
     * @param ChildSpotifyAccount $spotifyAccount The ChildSpotifyAccount object to add.
     */
    protected function doAddSpotifyAccount(ChildSpotifyAccount $spotifyAccount)
    {
        $this->collSpotifyAccounts[]= $spotifyAccount;
        $spotifyAccount->setUser($this);
    }

    /**
     * @param  ChildSpotifyAccount $spotifyAccount The ChildSpotifyAccount object to remove.
     * @return $this|ChildUser The current object (for fluent API support)
     */
    public function removeSpotifyAccount(ChildSpotifyAccount $spotifyAccount)
    {
        if ($this->getSpotifyAccounts()->contains($spotifyAccount)) {
            $pos = $this->collSpotifyAccounts->search($spotifyAccount);
            $this->collSpotifyAccounts->remove($pos);
            if (null === $this->spotifyAccountsScheduledForDeletion) {
                $this->spotifyAccountsScheduledForDeletion = clone $this->collSpotifyAccounts;
                $this->spotifyAccountsScheduledForDeletion->clear();
            }
            $this->spotifyAccountsScheduledForDeletion[]= clone $spotifyAccount;
            $spotifyAccount->setUser(null);
        }

        return $this;
    }

    /**
     * Clears out the collPastPlaylists collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addPastPlaylists()
     */
    public function clearPastPlaylists()
    {
        $this->collPastPlaylists = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collPastPlaylists collection loaded partially.
     */
    public function resetPartialPastPlaylists($v = true)
    {
        $this->collPastPlaylistsPartial = $v;
    }

    /**
     * Initializes the collPastPlaylists collection.
     *
     * By default this just sets the collPastPlaylists collection to an empty array (like clearcollPastPlaylists());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPastPlaylists($overrideExisting = true)
    {
        if (null !== $this->collPastPlaylists && !$overrideExisting) {
            return;
        }
        $this->collPastPlaylists = new ObjectCollection();
        $this->collPastPlaylists->setModel('\Playlist');
    }

    /**
     * Gets an array of ChildPlaylist objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildUser is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildPlaylist[] List of ChildPlaylist objects
     * @throws PropelException
     */
    public function getPastPlaylists(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collPastPlaylistsPartial && !$this->isNew();
        if (null === $this->collPastPlaylists || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collPastPlaylists) {
                // return empty collection
                $this->initPastPlaylists();
            } else {
                $collPastPlaylists = ChildPlaylistQuery::create(null, $criteria)
                    ->filterByOwner($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collPastPlaylistsPartial && count($collPastPlaylists)) {
                        $this->initPastPlaylists(false);

                        foreach ($collPastPlaylists as $obj) {
                            if (false == $this->collPastPlaylists->contains($obj)) {
                                $this->collPastPlaylists->append($obj);
                            }
                        }

                        $this->collPastPlaylistsPartial = true;
                    }

                    return $collPastPlaylists;
                }

                if ($partial && $this->collPastPlaylists) {
                    foreach ($this->collPastPlaylists as $obj) {
                        if ($obj->isNew()) {
                            $collPastPlaylists[] = $obj;
                        }
                    }
                }

                $this->collPastPlaylists = $collPastPlaylists;
                $this->collPastPlaylistsPartial = false;
            }
        }

        return $this->collPastPlaylists;
    }

    /**
     * Sets a collection of ChildPlaylist objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $pastPlaylists A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildUser The current object (for fluent API support)
     */
    public function setPastPlaylists(Collection $pastPlaylists, ConnectionInterface $con = null)
    {
        /** @var ChildPlaylist[] $pastPlaylistsToDelete */
        $pastPlaylistsToDelete = $this->getPastPlaylists(new Criteria(), $con)->diff($pastPlaylists);


        $this->pastPlaylistsScheduledForDeletion = $pastPlaylistsToDelete;

        foreach ($pastPlaylistsToDelete as $pastPlaylistRemoved) {
            $pastPlaylistRemoved->setOwner(null);
        }

        $this->collPastPlaylists = null;
        foreach ($pastPlaylists as $pastPlaylist) {
            $this->addPastPlaylist($pastPlaylist);
        }

        $this->collPastPlaylists = $pastPlaylists;
        $this->collPastPlaylistsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Playlist objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related Playlist objects.
     * @throws PropelException
     */
    public function countPastPlaylists(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collPastPlaylistsPartial && !$this->isNew();
        if (null === $this->collPastPlaylists || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPastPlaylists) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getPastPlaylists());
            }

            $query = ChildPlaylistQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByOwner($this)
                ->count($con);
        }

        return count($this->collPastPlaylists);
    }

    /**
     * Method called to associate a ChildPlaylist object to this object
     * through the ChildPlaylist foreign key attribute.
     *
     * @param  ChildPlaylist $l ChildPlaylist
     * @return $this|\User The current object (for fluent API support)
     */
    public function addPastPlaylist(ChildPlaylist $l)
    {
        if ($this->collPastPlaylists === null) {
            $this->initPastPlaylists();
            $this->collPastPlaylistsPartial = true;
        }

        if (!$this->collPastPlaylists->contains($l)) {
            $this->doAddPastPlaylist($l);
        }

        return $this;
    }

    /**
     * @param ChildPlaylist $pastPlaylist The ChildPlaylist object to add.
     */
    protected function doAddPastPlaylist(ChildPlaylist $pastPlaylist)
    {
        $this->collPastPlaylists[]= $pastPlaylist;
        $pastPlaylist->setOwner($this);
    }

    /**
     * @param  ChildPlaylist $pastPlaylist The ChildPlaylist object to remove.
     * @return $this|ChildUser The current object (for fluent API support)
     */
    public function removePastPlaylist(ChildPlaylist $pastPlaylist)
    {
        if ($this->getPastPlaylists()->contains($pastPlaylist)) {
            $pos = $this->collPastPlaylists->search($pastPlaylist);
            $this->collPastPlaylists->remove($pos);
            if (null === $this->pastPlaylistsScheduledForDeletion) {
                $this->pastPlaylistsScheduledForDeletion = clone $this->collPastPlaylists;
                $this->pastPlaylistsScheduledForDeletion->clear();
            }
            $this->pastPlaylistsScheduledForDeletion[]= clone $pastPlaylist;
            $pastPlaylist->setOwner(null);
        }

        return $this;
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        if (null !== $this->aPlaylist) {
            $this->aPlaylist->removeUserRelatedByPlaylistId($this);
        }
        $this->id = null;
        $this->ldap = null;
        $this->firstname = null;
        $this->lastname = null;
        $this->playlist_id = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collSpotifyAccounts) {
                foreach ($this->collSpotifyAccounts as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPastPlaylists) {
                foreach ($this->collPastPlaylists as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collSpotifyAccounts = null;
        $this->collPastPlaylists = null;
        $this->aPlaylist = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(UserTableMap::DEFAULT_STRING_FORMAT);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
