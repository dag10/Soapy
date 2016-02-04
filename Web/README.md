Soapy Web
==

This is the website for Soapy to provide Spotify O-Auth and a web interface
for pairing one's Computer Science House account with their personal Spotify
account.

Installation
--

#### Packages

Make sure you have [Composer](https://getcomposer.org) and [npm](https://www.npmjs.com) installed.
From the Soapy/Web directory, run the following to install the required php and client packages:

```
composer install
npm install
```

#### Configuration

Copy `config.php.default` to `config.php` and customize as needed, adding your Spotify API client ID and secret token.

Then copy `propel.yaml.default` to `propel.yaml` and add your mysql connection settings.
Once you edit `propel.yaml`, run:

```
php vendor/bin/propel config:convert
```

You must do this every time you edit propel.yaml.

#### Populating the database

Create the mysql database, then populate it by running:

```
php vendor/bin/propel sql:insert
```

#### Building client-side application

To compile the client-side typescript and copy the required javascript resources to the public directory,
run:

```
npm run gulp
```

You must do this every time you edit any files in the `Soapy/Web/typescript` directory.

#### Serving

For production, point your web server to serve out of the `Soapy/Web/public` directory.

For development, go to the `Soapy/Web/public` directory and run:

```
php -S 0.0.0.0:9000
```

Now just point your browser [there](http://localhost:9000).

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

