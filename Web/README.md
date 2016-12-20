Soapy Web
==

This is the website for Soapy to provide Spotify O-Auth and a web interface
for pairing one's Computer Science House account with their personal Spotify
account.

Installation
--

#### Packages

Make sure you have [Composer](https://getcomposer.org) and
[npm](https://www.npmjs.com) installed.  From the Soapy/Web directory, run the
following to install the required php and client packages:

```
composer install
npm install
```

#### Configuration

Copy `config.php.default` to `config.php` and customize as needed, adding your
Spotify API client ID and secret token.

Then copy `propel.yaml.default` to `propel.yaml` and add your mysql connection
settings.

Once you edit `propel.yaml`, run:

```
php vendor/bin/propel config:convert
```

You must do this every time you edit propel.yaml.

#### Populating the database

Create the mysql database, then populate it by running:

```
php vendor/bin/propel migrate
```

#### Building client-side application

To compile the client-side typescript and copy the required javascript
resources to the public directory, run:

```
npm run gulp
```

You must do this every time you edit any files in the `Soapy/Web/typescript`,
`Soapy/Web/templates/app`, or `Soapy/Web/less`  directories.

#### Serving

For production, point your web server to serve out of the `Soapy/Web/public`
directory.

For development, go to the `Soapy/Web/public` directory and run:

```
php -S 0.0.0.0:9000
```

Now just point your browser [there](http://localhost:9000).

Development
--

### Adding and modifying tables

To add or alter mysql table, edit schema.xml. Then run:

```
php vendor/bin/propel migration:diff # This creates a script that updates the
                                     # mysql database to the new schema.

php vendor/bin/propel model:build    # This creates the base PHP class for the
                                     # model logic.

composer dump-autoload               # Needed only if a new table is created.
```

The `composer dump-autoload` is used to autoload the new table classes. This
is apparently the [correct](http://stackoverflow.com/a/25634655/3333841) way
of doing things.

Once your migration file has been created, feel free to add any necessary code
to the generated file in the `generated-migrations` directory to populate/alter
any existing data. This may not be necessary. For more information, read
about [migrations in Propel](http://propelorm.org/documentation/09-migrations.html).

Finally, update the website's live database by running:

```
php vendor/bin/propel migrate
```
