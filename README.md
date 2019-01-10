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

Packages comes with an example `docker-compose.yml` that starts an nginx container and a Redis container, ready to get up and running quickly.

Visit [the documentation](http://docs.terramarlabs.com/packages/3.2/getting-started/docker) to get started.

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
  
