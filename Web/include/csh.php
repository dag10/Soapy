<?PHP namespace CSH;

require '../vendor/autoload.php';
require '../config.php';

function get_webauth($app) {
  global $cfg;

  // Find out who the current user is from the webauth env vars.
  // More info at https://wiki.csh.rit.edu/wiki/Member_Pages
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

    // This is just to fake webauth when developing on systems without it.
    return [
      'ldap' => 'dev',
      'firstname' => 'John',
      'lastname' => 'Smith',
    ];
  }
}

function user_for_rfid($rfid) {
  global $cfg;

  if (!$cfg['ibutton']) {
    return null;
  }

  if (isset($cfg['ibutton']['overrides'][$rfid])) {
    return \UserQuery::create()->findOneByLDAP(
      $cfg['ibutton']['overrides'][$rfid]);
  }

  if (!$cfg['ibutton']['ibutton_server'] ||
    !$cfg['ibutton']['ibutton_server']['enabled'] ||
    !$cfg['ibutton']['ibutton_server']['url']) {
    return null;
  }

  // Use JD's server for fetching user info for iButton/RFID id.
  $url = sprintf($cfg['ibutton']['ibutton_server']['url'], $rfid);
  if (($json = @file_get_contents($url)) === false) {
    return null;
  }

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

