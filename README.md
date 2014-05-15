packages
========

Packages is a custom implementation of [Satis](https://github.com/composer/satis) tailored for the needs of Terramar Labs.

Version 2.0 features a web management interface that allows for easy management of exposed
packages and configured source control repositories.


Installation
------------

Packages requires:
 * PHP 5.4 or later
 * Some database platform supported by [Doctrine 2](http://doctrine-project.org)
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

Create your database schema.

```
bin/doctrine orm:schema-tool:create
```

Your installation is complete! Visit the project's web directory from your browser to see the result.


### Updating satis.json

```
bin/console update
```

This command parses the project `config.yml` file and generates an updated satis.json with
all valid composer packages it is able to locate.



### Updating the exposed packages.json

`packages.json` is publicly accessible, exposing information about the available repositories
and their branches, tags, etc. Once `satis.json` is updated, run the build command to update `packages.json`.

```
bin/console build --no-html-output
```


