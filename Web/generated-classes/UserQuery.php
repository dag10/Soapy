<?php

use Base\UserQuery as BaseUserQuery;

/**
 * Skeleton subclass for performing query and update operations on the 'user' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class UserQuery extends BaseUserQuery
{
  public static function findOneByRFID($rfid) {
    return null;
  }

  public static function GetOrCreateUser($webauth) {
    $user = self::create()->findOneByLDAP($webauth['ldap']);

    if (!$user) {
      $user = new User();
      $user->setLDAP($webauth['ldap']);
      $user->setFirstName($webauth['firstname']);
      $user->setLastName($webauth['lastname']);
      $user->save();
    }

    return $user;
  }
}
