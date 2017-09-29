Packages
========

> Source code repository management made simple.

[![Build Status](https://img.shields.io/travis/terramar-labs/packages/master.svg?style=flat-square)](https://travis-ci.org/terramar-labs/packages)

Packages is a PHP 5.6 and 7.x application providing an interface and tools for maintaining a private composer repository. Packages extends [Satis](https://github.com/composer/satis), adding a web frontend and useful management functionality like GitHub and GitLab integration.

Packages automatically registers GitLab and GitHub project webhooks to keep Satis up to date every time you push code. Packages also features a web management interface that allows for easy management of exposed packages and configured source control repositories.

Packages version 3 works on a plugin based system based around source code repositories. Packages can trigger, with each code push, many automated tasks like documentation generation or code  analysis. The simple event-based architecture allows easy creation of new automation tasks.

[View the docs online](http://docs.terramarlabs.com/packages/3.1)
