<?PHP namespace CSH;

require '../vendor/autoload.php';
require '../config.php';

function get_webauth($app) {
  global $cfg;

  // This is just to fake webauth when developing on systems without it.
  if ($cfg['webauth']) {
    return [
      'ldap' => $_SERVER['WEBAUTH_USER'],
      'firstname' => $_SERVER['WEBAUTH_LDAP_GIVENNAME'],
      'lastname' => $_SERVER['WEBAUTH_LDAP_SN'],
    ];
  } else {
    return [
      'ldap' => 'csher',
      'firstname' => 'John',
      'lastname' => 'Smith',
    ];
  }
}

