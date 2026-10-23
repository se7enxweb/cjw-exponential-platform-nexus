# CJW Exponential Platform Nexus 1.0.0.x INSTALL Instructions

## Requirements

### Web server:

   Apache 2.4 (see doc/apache2), nginx (see doc/nginx) or, for development and
   testing, PHP's built-in web server (see "Serve the site" below).

### PHP version:

   PHP 8.0 or later. The latest version of the 8.5 branch is recommended.

   Composer installs the Exponential legacy kernel that fits the PHP version: on PHP 8.1
   and later the latest release (6.0.14), on PHP 8.0 the 6.0.x line (6.0.15), which is the
   kernel line that runs on PHP 8.0.

   Note that you will have to increase the default "memory_limit" setting
   which is located in the "php.ini" configuration file to 464 MB or larger. (Don't
   forget to restart Apache after editing "php.ini".)

   The date.timezone directive must be set in php.ini or in
   .htaccess. For a list of supported timezones please see
   http://php.net/manual/en/timezones.php

### Composer version:

   Composer 2.2 or later (the latest 2.x is recommended).

### Node.js and Yarn:

   composer install builds the site's assets and the Platform admin's assets with
   Webpack Encore, so Node.js 20 and Yarn 1.22 must be on the PATH (see "Install Node.js
   and Yarn" below).

### Database server:

   MySQL 5.7 / MariaDB 10.3 or later (utf8mb4), or SQLite 3 (pdo_sqlite).
   The JAC Example demo content is installed on MySQL/MariaDB and on SQLite.

### PHP Extension(s):
- ctype
- date
- dom
- fileinfo
- filter
- hash
- iconv
- intl
- json
- mbstring
- openssl
- pcre
- pdo
- pdo_mysql (MySQL/MariaDB) or pdo_sqlite (SQLite)
- phar
- session
- simplexml
- tokenizer
- xml
- xmlreader
- xmlwriter
- xsl
- zlib

Strongly recommended (but not strictly required). You can run without some of these, but you really shouldn’t in production:

- curl (HTTP integrations, repository calls, external services)
- gd or imagick (image variations & thumbnails)
- opcache (performance)
- APCu (performance)
- zip (Composer + package handling)

The official install docs explicitly mention enabling project-relevant PHP extensions and recommend opcache for performance even if not mandatory.

If you use specific Exponential Platform features

Image handling (almost always used)

- gd or imagick

Without one of these, image aliases and content previews will break.

Search (Legacy Bridge / advanced indexing)

- curl

- pcntl (optional but useful for indexing workers)

- posix (optional)

(These are commonly required by legacy-related tools and indexing processes.)


# Installation

CJW Exponential Platform Nexus is installed from its Git repository. It is not published on
Packagist: `composer create-project se7enxweb/exponential-platform-nexus` installs a different
product (Exponential Platform Nexus).

The repository includes a `vendor/` directory, but `composer install` is still required: it
brings `vendor/` up to date, installs the Exponential legacy kernel into `ezpublish_legacy/`
(which is not part of the repository), links the legacy extensions and bundle assets, and
builds the site and Platform admin assets.

## 1. Install Node.js and Yarn

```shell
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
\. "$HOME/.nvm/nvm.sh"
nvm install 20
node -v   # v20.x
npm install --global yarn
yarn -v   # 1.22.x
```

## 2. Clone the Git repository

```shell
git clone https://github.com/se7enxweb/cjw-exponential-platform-nexus.git
cd cjw-exponential-platform-nexus
```

## 3. Install the Composer packages

```shell
composer install --ignore-platform-req=php+
```

Several packages of this Symfony 3.4 / Platform 2.5 stack still declare an upper PHP bound
(for example `^5.6 || ^7.0`). `--ignore-platform-req=php+` ignores only those upper bounds: a
PHP version below a package's minimum is still refused and the PHP extension checks still
run. To set it once for the shell instead: `export COMPOSER_IGNORE_PLATFORM_REQ=php+`.

When Composer asks for the parameters of `app/config/parameters.yml`, the defaults can be
accepted; the database is configured in the next step. The install ends with `yarn install`,
the Encore build of the site assets and `yarn ezplatform` (the Platform admin assets).

## 4. Configure the database

Edit `app/config/parameters.yml`:

- MySQL/MariaDB: create the database first,

  ```mysql
  CREATE DATABASE <db_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
  ```

  then set `env(DATABASE_DRIVER): pdo_mysql`, `env(DATABASE_HOST)`, `env(DATABASE_NAME)`,
  `env(DATABASE_USER)`, `env(DATABASE_PASSWORD)` and `env(DATABASE_VERSION)` (for example
  `mariadb-10.5.29` or `5.7`).
- SQLite: set `env(DATABASE_DRIVER): pdo_sqlite`. The database file is
  `var/data_<environment>.db` (`var/data_prod.db` for the site served by `web/app.php`); the
  `DATABASE_PATH` environment variable chooses another file.

Set `env(SYMFONY_SECRET)` to a long random value (for example the output of
`openssl rand -hex 32`) and rotate it regularly.

Then clear the cache, because `composer install` compiled the container with the defaults of
`parameters.yml.dist`:

```shell
export SYMFONY_ENV=prod
php bin/console cache:clear
```

`SYMFONY_ENV` selects the environment of `bin/console` (default `dev`). `web/app.php` runs
`prod`; with SQLite each environment has its own database file, so install and serve with
the same environment.

## 5. Install the demo site database

```shell
php -d memory_limit=2000M bin/console ezplatform:install cjw-exponential-media
```

This installs the JAC Example site with its content, content classes, sections, languages
(ger-DE and eng-GB) and Netgen Layouts, from the
[`se7enxweb/cjw-exponential-media-site-data`](https://github.com/se7enxweb/cjw-exponential-media-site-data)
package on MySQL/MariaDB and from `src/AppBundle/Resources/database/sql/sqlite/` on SQLite.
`composer run ezplatform-install` runs the same command.

| Type | Description |
|------|-------------|
| `cjw-exponential-media` | **Default.** JAC Example site with demo content (MySQL/MariaDB, SQLite) |
| `exponential-cjw` | Older name of `cjw-exponential-media` |
| `exponential-oss` | Minimal clean schema only, no content |

**Default Admin Credentials:**
- Username: `admin`
- Password: `publish`

**⚠️ IMPORTANT:** Change the admin password immediately after installation!

## 6. Link the project's legacy extension and storage

`composer install` has already linked the legacy extensions of the bundles (`ngadminui`,
`nglayouts`, `ngsite`, `ezrichtext`) and `web/bundles/app`. Two links remain:

```shell
# The "app" legacy extension of the project (settings and design of the admin siteaccesses).
ln -s ../../src/AppBundle/ezpublish_legacy/extension/app ezpublish_legacy/extension/app

# The storage of the demo content (images and files), kept outside ezpublish_legacy/ because
# composer update replaces that directory.
[ -d ezpublish_legacy/var/site/storage ] && mv ezpublish_legacy/var/site/storage ezpublish_legacy/var/site/storage-empty
ln -s ../../../src/AppBundle/ezpublish_legacy/var/site/storage ezpublish_legacy/var/site/storage

php bin/console ezpublish:legacy:script bin/php/ezpgenerateautoloads.php
```

## 7. Clear the caches

```shell
php bin/console cache:clear
php bin/console ezpublish:legacy:script bin/php/ezcache.php --clear-all
```

## 8. Permissions

Replace user and group as needed:

```shell
chown -R www-data:www-data .
```

## 9. Serve the site

- Apache: see `doc/apache2` (document root `web/`, `DirectoryIndex app.php`).
- nginx: see `doc/nginx` (document root `web/`).
- PHP's built-in web server, for development and testing:

  ```shell
  php -d memory_limit=512M -S 127.0.0.1:8000 -t web
  ```

  `web/index.php` runs the application the same way `web/app.php` does.

With one host name, the siteaccesses are reached by the first part of the path:

| Address | Siteaccess |
|---------|------------|
| `/` | Front site, German (`de`, default) |
| `/en/` | Front site, English |
| `/admin/` | Platform admin |
| `/ngadminui/` | Netgen Admin UI |
| `/legacy_admin/` | Legacy admin |
| `/nglayouts/admin/` | Netgen Layouts admin (sign in on the front site at `/login` first) |

## Siteaccess Configuration

Review the default siteaccess configuration in `app/config/ezplatform_siteaccess.yml`.
You may wish to change the host name to siteaccess mappings (`Map\Host`); we recommend at
least two hosts, one for the site and one for the editors and admin interfaces.

### Additional Instructions

For the rest of the installation steps you will find the installation guide at https://exponential.doc.exponential.earth/display/DEVELOPER/Step%2b1_%2bInstallation.html

- And at: https://exponential.doc.exponential.earth/display/DEVELOPER/Installation%2bUsing%2bComposer.html

- And at: https://exponential.doc.exponential.earth/display/DEVELOPER/Installation%2bGuide%2bfor%2bUnix-Based%2bSystems.html

This software is based upon the elder 1.x branch of 7x Media-site.

* Installation Guide for the Media-site software requirements (Helpful): https://github.com/se7enxweb/media-site/blob/1.12/doc/netgen/INSTALL.md

*Security Note*: Like all Symfony based applications, set `env(SYMFONY_SECRET)` in
`app/config/parameters.yml` to a unique, long, random value during the installation and
replace it regularly. A guessable secret is the most commonly reported cause of attacks on
Platform based sites.
