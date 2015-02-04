Packages
========

> Source code repository management made simple.

Packages extends [Satis](https://github.com/composer/satis), adding useful management functionality like GitHub
and GitLab integration.

Packages automatically registers GitLab and GitHub project web hooks to keep Satis up to date every time you
push code. Packages also features a web management interface that allows for easy management of exposed
packages and configured source control repositories.

Packages 3.0 works on a plugin based system based around source code repositories. Packages 
can trigger, with each code push, many automated tasks like documentation generation or code 
analysis. The simple event-based architecture allows easy creation of new automation tasks.

[View the docs online](http://docs.terramarlabs.com/packages/3.0)
