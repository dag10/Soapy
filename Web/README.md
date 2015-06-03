# Soapy-Web
Website for Soapy to provide Spotify O-Auth.

## Installing

Copy config.php.default to config.php and customize as needed, adding your Spotify API client ID and secret token.

Then copy propel.yaml.default to propel.yaml and add your mysql connection settings.
Once you edit propel.yaml, run `php vendor/bin/propel config:convert`. You must do this every time you edit propel.yaml.


Create the mysql database, then run `php vendor/bin/propel sql:insert` to populate it.

