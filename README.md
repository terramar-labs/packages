packages
========

Packages extends [Satis](https://github.com/composer/satis), adding useful management functionality.

Packages automatically registers GitLab and GitHub project web hooks to keep Satis up to date. Packages also
features a web management interface that allows for easy management of exposed packages and configured source 
control repositories.

Packages 3.0 works on a plugin based system based around source code repositories. Packages 
can trigger, with each code push, many automated tasks like documentation generation or code 
analysis. The simple event-based architecture allows easy creation of new automation tasks.

Currently implemented plugins:

* **GitLab integration plugin**
  Provides project sync support and automatic webhook registration within GitLab.

* **GitHub integration plugin**
  Provides project sync support and automatic webhook registration within GitHub.

* **Satis plugin**
  Updates Satis when source code is updated.


Installation
------------

Packages requires:
 * PHP 5.4 or later
 * Some database platform supported by [Doctrine 2](http://doctrine-project.org)
 * Redis
 * [Composer](https://getcomposer.org)


First, clone the project and install dependencies.

```
git clone https://github.com/terramar-labs/packages
cd packages
composer install
```

Next, copy `config.yml.dist` to `config.yml`, editing any values necessary.

```
cp config.yml.dist config.yml
vi config.yml
```

Create your database if necessary, then run the schema tool to update your database schema.

```
bin/console orm:schema-tool:create
```

Your installation is complete! Visit the project's web directory from your browser to configure your packages.


Usage
-----

### Using the web interface

Point your webserver's webroot to your Packages installation's `web` folder, then visit the app in your browser.

You can login with the configured credentials.


### Creating Remotes

Before you can use Packages, you need to configure a GitHub or GitLab remote. Do this by logging into the
web interface and going to Remotes. Add a Remote, select the Remote type, then configure any additional
parameters necessary for the chosen type.

Now Sync your remote to create a package within your Packages installation.


### Enabling Package plugins

After you have synced your Remote, you can configure each package. Go to Packages and click Edit on one of your
packages. Check the "Enabled" checkbox to enable and configure further plugins.


Satis Configuration
-------------------

Enable the Satis plugin on each Package you want to expose via Satis. A webhook will be installed in GitHub or
GitLab to enable the automatic update of your Satis repository information.


### Manually updating Satis

Sometimes you need to manually build or generate the exposed Satis information. You can do this by using the
Packages command-line interface.


#### Updating satis.json

`satis.json` is the Satis configuration file. This file tells Satis which repositories to look at.

```
bin/console satis:update
```

This command generates an updated satis.json with all enabled packages.


#### Updating the exposed packages.json

`packages.json` is publicly accessible, exposing information about the available repositories
and their branches, tags, etc. Once `satis.json` is updated, run the build command to update `packages.json`.

```
bin/console satis:build
```

Alternatively, running the `satis:update` command while passing `--build` will both 
update `satis.json` and build `packages.json`.

```
bin/console satis:update --build
```


