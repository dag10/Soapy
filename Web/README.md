Soapy Web
==

This is the website for Soapy to provide Spotify O-Auth and a web interface
for pairing one's Computer Science House account with their personal Spotify
account.

Installation
--

Copy config.php.default to config.php and customize as needed, adding your Spotify API client ID and secret token.

Then copy propel.yaml.default to propel.yaml and add your mysql connection settings.
Once you edit propel.yaml, run `php vendor/bin/propel config:convert`. You must do this every time you edit propel.yaml.

Create the mysql database, then run `php vendor/bin/propel sql:insert` to populate it.

Development
--

### Adding tables

To add a new mysql table, add the table to schema.xml. Then run:

```
php vendor/bin/propel sql:build --overwrite
php vendor/bin/propel model:build
composer dump-autoload
php vendor/bin/propel sql:insert # only if the table needs to be created in db
```

The `composer dump-autoload` is used to autoload the new table classes. This
is apparently the [correct](http://stackoverflow.com/a/25634655/3333841) way
of doing things.

### Modifying tables

Follow the sql:build and model:build steps from the "Adding tables" section.

I haven't set up any sort of migration system yet. So if you already have a
database, you'll have to manually update the structure. If you don't, then just
run the sql:insert command.

