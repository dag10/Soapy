<?PHP namespace CSH;

require '../vendor/autoload.php';
require '../config.php';

function get_webauth($app) {
  global $cfg;

  // This is just to fake webauth when developing on systems without it.
  if ($cfg['webauth']) {
    foreach (['WEBAUTH_USER', 'WEBAUTH_LDAP_GIVENNAME', 'WEBAUTH_LDAP_SN'] as $key) {
      if (!isset($_SERVER[$key])) {
        return null;
      }
    }

    return [
      'ldap' => $_SERVER['WEBAUTH_USER'],
      'firstname' => $_SERVER['WEBAUTH_LDAP_GIVENNAME'],
      'lastname' => $_SERVER['WEBAUTH_LDAP_SN'],
    ];
  } else {
    return [
      'ldap' => 'dag10',
      'firstname' => 'John',
      'lastname' => 'Smith',
    ];
  }
}

function user_for_rfid($rfid) {
  global $cfg;

  $tempMappings = [
    ];

  if ($rfid == "12345") {
    return \UserQuery::create()->findPk(1);
  } else if (isset($tempMappings[$rfid])) {
    return \UserQuery::create()->findOneByLDAP($tempMappings[$rfid]);
  }

  // Use JD's server for fetching user info for iButton/RFID id.
  if (!$cfg['ldap'] || ($json = @file_get_contents(
      "http://www.csh.rit.edu:56124/?ibutton=" . $rfid)) === false) {
    return null;
  }

  // TODO: Cache RFID->LDAP mappings in db.

  try {
    $user_data = json_decode($json, true);
    if (isset($user_data['error'])) {
      return null;
    }
    $uid = $user_data['uid'];
    return \UserQuery::create()->findOneByLDAP($uid);
  } catch (Exception $e) {
    return null;
  }
}

