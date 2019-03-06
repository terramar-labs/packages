Packages
========

> Source code repository management made simple.

[![Build Status](https://travis-ci.org/terramar-labs/packages.svg?branch=master)](https://travis-ci.org/terramar-labs/packages)

Packages is a PHP 5.6 and 7.x application providing an interface and tools for maintaining a private [Composer](https://getcomposer.org) repository. Packages extends [Satis](https://github.com/composer/satis), adding a web frontend and useful management functionality like GitHub and GitLab integration.

Packages automatically registers GitLab, GitHub, and Bitbucket project webhooks to keep Satis up to date every time you push code. Packages also features a web management interface that allows for easy management of exposed packages and configured source control repositories.

Packages version 3 works on a plugin based system based around source code repositories. Packages can trigger, with each code push, many automated tasks like documentation generation or code  analysis. The simple event-based architecture allows easy creation of new automation tasks.

[View the docs online](http://docs.terramarlabs.com/packages/3.2).

Installation
------------

Requirements:
 * PHP 5.6 or later
 * [Composer](https://getcomposer.org)
 * Some database platform supported by [Doctrine 2](http://doctrine-project.org) (sqlite works great!)
 * [Redis](https://redis.io/) 

Download the [latest release](https://github.com/terramar-labs/packages/releases/latest), or clone the repository.

```bash
git clone git@github.com:terramar-labs/packages.git
```

### Install dependencies

Switch to the project root directory and run `composer install`.

```bash
cd packages
composer install
```

### Edit configuration

Copy `config.yml.dist` to `config.yml` and edit as appropriate.

```bash
cp config.yml.dist config.yml
vi config.yml
```

### Generate the database schema

Packages uses Doctrine ORM to auto-generate the database schema for your configured platform.

```bash
bin/console orm:schema-tool:create
```

Running the application
-----------------------

Start PHP's built-in webserver to run the Packages web application with minimal effort.

```bash
# Visit http://localhost:8080 to see the landing page.
php -S localhost:8080 -t web
```

### Start a Resque worker

For fully-automatic integration with GitHub, GitLab, and your Satis repository, you must always have at least one Resque worker running. 

```bash
bin/console resque:worker:start
```

> For more information on Resque workers, check [the dedicated section](http://docs.terramarlabs.com/packages/3.2/managing-packages/resque).


Using the application
---------------------

Read the [usage and design documentation](http://docs.terramarlabs.com/packages/3.2/getting-started/usage) for an overview of Packages functionality.


### Development/debug mode

Visit `index_dev.php` in your browser to view the site with the `dev` environment configuration. In this env, views and the service container are not cached, so changes made are immediately visible.

### Customizing

Check out the [Contributing Guide](CONTRIBUTING.md) for the recommended way to set up your development environment.

Some tips:

* Views are written using Twig and stored in `views/`.
  * Views are cached in `prod` env; use `http://localhost:8080/index_dev.php` to develop.
  * All pages inherit from `views/base.html.twig`, except for
  * Public landing page views inherit from `views/Default/base.html.twig`.
* [Composer Components](http://robloach.github.io/component-installer/) are used to manage front-end dependencies. The respective `web/images/`, `web/js/bootstrap.min.js`, and such are symlinks pointing to the real files installed by Composer in `vendor/`.
* Check [the documentation](http://docs.terramarlabs.com/packages/3.2/managing-packages/customizing) for additional information.


Docker support
--------------

You can find a fully configured instance of Packages on [Docker Hub](https://hub.docker.com/r/mkerix/packages/).

### Installation

After booting up the container you still need to create the database schema by running in the container `bin/console orm:schema-tool:create`.
This container does not apply migrations automatically, you'll have to run the migrations manually in the same fashion when updating the version.

The docker-compose file in this repository is great for local development use, but in production you should consider the following setup:

```yml
version: '2'
services:
  packages:
    image: mkerix/packages
    environment:
      PACKAGES_NAME: Name
      PACKAGES_HOMEPAGE: Homepage
      PACKAGES_CONTACT: contact@example.com
      PACKAGES_BASEPATH: https://satis.example.com
      PACKAGES_USER: user
      PACKAGES_PASSWORD: password
      PACKAGES_PDO_PATH: '%app.root_dir%/database/database.sqlite'
    volumes:
    - /host/packages/database:/app/database
    - /host/packages/satis:/app/satis
    - /host/packages/ssh:/home/application/.ssh
  redis:
    image: redis
```

Adjust the environment variables as well as the volume links on the host to your liking and then generate a new SSH key and known_hosts file in the ssh folder.
The generated public key needs to be added to a user with access to your composer repositories.
This is needed so that Satis can pull the repositories successfully.

### Configuration

The following environment variables can optionally be used to configure your instance.

#### Customization
- `PACKAGES_NAME` - instance name
- `PACKAGES_HOMEPAGE` - instance homepage link
- `PACKAGES_CONTACT` - contact mail
- `PACKAGES_BASEPATH` - full base URL to the instance

#### Security
- `PACKAGES_SECURE` - whether Satis should be secured too or not
- `PACKAGES_USER` - username
- `PACKAGES_PASSWORD` - password

#### Database
- `PACKAGES_PDO_DRIVER` - pdo driver
- `PACKAGES_PDO_PATH` - path to the database file, e.g. for sqlite
- `PACKAGES_PDO_HOST` - database host
- `PACKAGES_PDO_USER` - database user
- `PACKAGES_PDO_PASSWORD` - database password
- `PACKAGES_PDO_DBNAME` - database name
- `PACKAGES_REDIS_HOST` - Redis host
- `PACKAGES_REDIS_PORT` - Redis port

Visit [the documentation](http://docs.terramarlabs.com/packages/3.2/getting-started/docker) to learn more.

Troubleshooting
---------------

1. `index_dev.php` contains a non-localhost block, comment it out when using Docker.
2. Manage Resque and Satis using the `bin/console` command-line utility.
  * **Build the Satis `packages.json` file** with the `satis:build` command.
    ```bash
    bin/console satis:build
    ```
  * **View queued Resque jobs in Redis** with the `resque:queue:list` command.
    ```bash
    bin/console resque:queue:list
    ```
  * **View active Resque workers** with the `resque:worker:list` command.
    ```bash
    bin/console resque:worker:list
    ```
  * **Start a Resque worker** with `resque:worker:start`.
    ```bash
    bin/console resque:worker:start
    ```
3. Check the Resque logs to see if Resque is working properly.
  ```bash
  tail -f logs/resque.log
  ```
4. If running in Docker make sure that all files belong to the application user. Change the ownership from within the container:
  ```bash
  chown -R application:application /app
  ```
  
