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

Run database migrations to create your database schema.

```
bin/console migrations:migrate
```

Your installation is complete! Visit the project's web directory from your browser to configure your packages.


### Updating satis.json

```
bin/console satis:update
```

This command generates an updated satis.json with all enabled packages.


### Updating the exposed packages.json

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


