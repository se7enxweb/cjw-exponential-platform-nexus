# Exponential Platform Nexus 1.0.0.x INSTALL Instructions

## Requirements

### Apache version:

   The latest version of the 1.3 branch.
   or
   Apache 2.x run in "prefork" mode.

### PHP version:

   The latest version of the 8.5 branch is strongly recommended.

   Note that you will have to increase the default "memory_limit" setting
   which is located in the "php.ini" configuration file to 464 MB or larger. (Don't
   forget to restart Apache after editing "php.ini".)

   The date.timezone directive must be set in php.ini or in
   .htaccess. For a list of supported timezones please see
   http://php.net/manual/en/timezones.php

### Composer version:

   The latest version of the 2.x branch is recommended.

### Database server:

   MySQL 4.1 or later (UTF-8 is required)
   or
   PostgreSQL 8.x
   or
   Oracle 11g

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
- pdo_mysql
- phar
- session
- simplexml
- tokenizer
- xml
- xmlreader
- xmlwriter
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


# GitHub Installation Guide (Create project for contribution)

## Create Database

### MySQL database

Use the following MySQL DDL to create a database which will be used for your project:

```mysql
CREATE DATABASE <db_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_general_ci;
```

## Clone the Git repository

```shell 
git clone git@github.com:se7enxweb/exponential-platform-nexus.git;
```

## Install default installation demo site database

Run the built-in console installer. It imports the schema and content from the
[`se7enxweb/cjw-exponential-media-site-data`](https://github.com/se7enxweb/cjw-exponential-media-site-data)
package (installed automatically by Composer in the previous step).

```shell
php bin/console ezplatform:install cjw-exponential-media
```

This installs the full JAC Example project with content, content classes, sections,
languages (ger-DE + eng-GB), and all required configuration.

**Default Admin Credentials:**
- Username: `admin`
- Password: `publish`

**⚠️ IMPORTANT:** Change the admin password immediately after installation!

#### Available installer types

| Type | Description |
|------|-------------|
| `cjw-exponential-media` | **Default.** Full JAC Example site with demo content |
| `exponential-oss` | Minimal clean schema only, no content |

** Later step comming up, Composr! Install Exponential Platform Nexus required PHP libraries like Zeta Components and Exponential Platform bundles and bundle extensions as specified in this project's composer.json. **

## Install Node

### Download Node.js

* https://nodejs.org/en/download

## Install Node.js

### Download and install nvm:

```shell 
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
```

### in lieu of restarting the shell

```shell
\. "$HOME/.nvm/nvm.sh"
```

### Download and install Node.js:

```shell
nvm install 20
```

### Verify the Node.js version:

```shell
node -v # Should print "v20.20.0".
```

### Verify npm version:

```shell 
npm -v # Should print "10.8.2".
```

## Install Yarn

### Download Yarn

* https://classic.yarnpkg.com/lang/en/docs/install/#mac-stable

### Install Yarn

```shell
npm install --global yarn
```

## Install the required composer packages and start the installation process

```shell
cd exponential-platform-nexus; composer install --keep-vcs --ignore-platform-reqs;
```

*Note*: For the short term future the composer argument '--ignore-platform-reqs' is required to install the software via composer package dependencies successfully. This limitation will soon be removed in the future as we continue to update requirements within the composer package definition files for each package repostiory (tedious detail oriented work which is currently on going).

### (Option) Run via composer script

```shell
composer run ezplatform-install
```

This is equivalent to running `php bin/console ezplatform:install cjw-exponential-media` directly.

## Assign Database User, Password and Database Name information in app/config/parameters.yml

## Manual Symlink Installation for default legacy storage images is required. 

This is required to store the storage directory outside of the ezpublish_legacy directory which is *erased* upon composer update (when new releases are installed via update of package se7enxweb/exponential). This is a known issue due to the behavior and nature of composer usage.

To see the images provided in the starter design and avoid a potential site bug of missing images perform the following commands:

### Install `app` ezpublish_legacy extension (Required by 7x admin-ui-bundle/ngadminui).

```shell
cd ezpublish_legacy/extension/;
ln -s ../../../src/AppBundle/ezpublish_legacy/extension/app .;
cd ../../../;
```

### Install `ngadminui` ezpublish_legacy extension (Required by 7x admin-ui-bundle/ngadminui).

```shell
cd ezpublish_legacy/extension/;
ln -s ../../../vendor/se7enxweb/admin-ui-bundle/bundle/ezpublish_legacy/ngadminui .;
cd ../../../;
```

### Install `app storage dir symlink` in var siteaccess dir

```shell
cd ezpublish_legacy/var/site/;
mv storage storage-empty;
ln -s ../../../src/AppBundle/ezpublish_legacy/var/site/storage .;
cd ../../../;
```

### Install `app` bundle public dir symlink in web/bundles

```shell
cd web/bundles/;
ln -s ../../src/AppBundle/Resources/public app;
cd ../../;
```

### Permissions: Replace user and group as needed or desired

```shell
chown -R www-data:www-data .;
```

## Siteaccess Configuration

Remember to review the defeault siteaccess configuration yaml settings in `app/config/ezplatform_siteaccess.yml`

You may wish to alter the Host (name) match doman names mappings to named siteaccesses. We recommend using at least 2 hosts. One for user and second for editor / admin ui.

## Clear cache

There might not be any cache or related issues but it's recommended practice to clear cache after completing your installation to avoid any possible issues.

```shell
php bin/console cache:clear --env=dev;
```

Both of these sets of demo data add an administrator user to the database. This user's username is admin and its password is publish.


### Additional Instructions

For the rest of the installation steps you will find the installation guide at https://exponential.doc.exponential.earth/display/DEVELOPER/Step%2b1_%2bInstallation.html

- And at: https://exponential.doc.exponential.earth/display/DEVELOPER/Installation%2bUsing%2bComposer.html

- And at: https://exponential.doc.exponential.earth/display/DEVELOPER/Installation%2bGuide%2bfor%2bUnix-Based%2bSystems.html

This software is based upon the elder 1.x branch of 7x Media-site.

* Installation Guide for the Media-site software requirements (Helpful): https://github.com/se7enxweb/media-site/blob/1.12/doc/netgen/INSTALL.md

*Security Note*: Like all Symfony based applications please remember to set the yaml parameter `SYMFONY_SECRET` durring the installation process (towards the end of a successfull instalation durring app/config/parameters.yml content population prompts) to asign a very unique, long, secure and regularly updated to a new secure value each financial quarter (maintinence). This is this the most commonly reported cause of eZ Platform based attacks. Make note that if you secure this text string value securely (hash of keys; not words) your attack vector is drasticaly reduced both in principle and in general proven security best-practice for Symfony Web App Developers. We can not repeate proven fact enough. Protect your installations on a regular basis and don't forget to rotate your keys each financial quarter. 

# Composer Installation Guide

## Create Database

### MySQL database

Use the following MySQL DDL to create a database which will be used for your project:

```mysql
CREATE DATABASE <db_name> CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_general_ci;
```

## Install Exponential Platform Nexus required PHP libraries like Zeta Components and Exponential Platform Bundles and Bundle extensions as specified in this project's composer.json.

```shell
cd www-root-directory; composer create-project se7enxweb/exponential-platform-nexus:v1.0.0.x-dev --ignore-platform-reqs;
```

Note: For the short term future the composer argument '--ignore-platform-reqs' is required to install the software via composer package dependencies successfully. This limitation will soon be removed in the future as we continue to update requirements within the composer package definition files for each package repostiory (tedious detail oriented work which is ongoing).

## Install default installation demo site database

Run the built-in console installer. It imports the schema and content from the
[`se7enxweb/cjw-exponential-media-site-data`](https://github.com/se7enxweb/cjw-exponential-media-site-data)
package (installed automatically by Composer in the previous step).

```shell
php bin/console ezplatform:install cjw-exponential-media
```

This installs the full JAC Example project with content, content classes, sections,
languages (ger-DE + eng-GB), and all required configuration.

**Default Admin Credentials:**
- Username: `admin`
- Password: `publish`

**⚠️ IMPORTANT:** Change the admin password immediately after installation!

#### Available installer types

| Type | Description |
|------|-------------|
| `cjw-exponential-media` | **Default.** Full JAC Example site with demo content |
| `exponential-oss` | Minimal clean schema only, no content |

** Later step comming up, Composr! Install Exponential Platform Nexus required PHP libraries like Zeta Components and Exponential Platform bundles and bundle extensions as specified in this project's composer.json. **

## Install Node

### Download Node.js

* https://nodejs.org/en/download

## Install Node.js

### Download and install nvm:

```shell 
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
```

### in lieu of restarting the shell

```shell
\. "$HOME/.nvm/nvm.sh"
```

### Download and install Node.js:

```shell
nvm install 20
```

### Verify the Node.js version:

```shell
node -v # Should print "v20.20.0".
```

### Verify npm version:

```shell 
npm -v # Should print "10.8.2".
```

## Install Yarn

### Download Yarn

* https://classic.yarnpkg.com/lang/en/docs/install/#mac-stable

### Install Yarn

```shell
npm install --global yarn
```

## Install the required composer packages and start the installation process

```shell
cd exponential-platform-nexus; composer install --keep-vcs --ignore-platform-reqs;
```

*Note*: For the short term future the composer argument '--ignore-platform-reqs' is required to install the software via composer package dependencies successfully. This limitation will soon be removed in the future as we continue to update requirements within the composer package definition files for each package repostiory (tedious detail oriented work which is currently on going).

### (Option) Run via composer script

```shell
composer run ezplatform-install
```

This is equivalent to running `php bin/console ezplatform:install cjw-exponential-media` directly.

## Assign Database User, Password and Database Name information in app/config/parameters.yml

## Manual Symlink Installation for default legacy storage images is required. 

This is required to store the storage directory outside of the ezpublish_legacy directory which is *erased* upon composer update (when new releases are installed via update of package se7enxweb/exponential). This is a known issue due to the behavior and nature of composer usage.

To see the images provided in the starter design and avoid a potential site bug of missing images perform the following commands:

### Install `app` ezpublish_legacy extension (Required by 7x admin-ui-bundle/ngadminui).

```shell
cd ezpublish_legacy/extension/;
ln -s ../../../src/AppBundle/ezpublish_legacy/extension/app .;
cd ../../../;
```

### Install `app storage dir symlink` in var siteaccess dir

```shell
cd ezpublish_legacy/var/site/;
mv storage storage-empty;
ln -s ../../../src/AppBundle/ezpublish_legacy/var/site/storage .;
cd ../../../;
```

### Install `app` bundle public dir symlink in web/bundles

```shell
cd web/bundles/;
ln -s ../../src/AppBundle/Resources/public app;
cd ../../;
```

### Permissions: Replace user and group as needed or desired

```shell
chown -R www-data:www-data .;
```

## Siteaccess Configuration

Remember to review the defeault siteaccess configuration yaml settings in `app/config/ezplatform_siteaccess.yml`

You may wish to alter the Host (name) match doman names mappings to named siteaccesses. We recommend using at least 2 hosts. One for user and second for editor / admin ui.

## Clear cache

There might not be any cache or related issues but it's recommended practice to clear cache after completing your installation to avoid any possible issues.

```shell
php bin/console cache:clear --env=dev;
```

Both of these sets of demo data add an administrator user to the database. This user's username is admin and its password is publish.


### Additional Instructions

For the rest of the installation steps you will find the installation guide at https://exponential.doc.exponential.earth/display/DEVELOPER/Step%2b1_%2bInstallation.html

- And at: https://exponential.doc.exponential.earth/display/DEVELOPER/Installation%2bUsing%2bComposer.html

- And at: https://exponential.doc.exponential.earth/display/DEVELOPER/Installation%2bGuide%2bfor%2bUnix-Based%2bSystems.html

This software is based upon the elder 1.x branch of 7x Media-site.

* Installation Guide for the Media-site software requirements (Helpful): https://github.com/se7enxweb/media-site/blob/1.12/doc/netgen/INSTALL.md
