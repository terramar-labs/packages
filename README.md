packages
========

Packages is a custom implementation of Satis tailored for the needs of Terramar Labs.


Description
-----------

This web application allows Terramar Labs to expose various private composer packages for
other machines and deployments.


### Updating satis.json

```
bin/console update
```

This command parses the project `config.yml` file and generates an updated satis.json with
all valid composer packages it is able to locate.



### Updating the exposed packages.json

`packages.json` is publically accessible, exposing information about the available repositories
and their branches, tags, etc. Once `satis.json` is updated, run the build command to update `packages.json`.

```
bin/console build
```


