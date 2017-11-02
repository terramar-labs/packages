# Explain yourself!

This "monkey patch" is necessary to allow Packages to use multiple different 
GitHub and GitLab auth tokens. Composer and Satis do not allow for this, and
it is not yet clear on how or if it will be implemented upstream.

> Yes, the warnings after `composer install` and `composer update` are expected.

The changes to the GitHubDriver and GitLabDriver are very small, so 
maintaining these "forked" files shouldn't be that painful. The warning from
composer sure is annoying though.


# Details, please

Packages 3.1 and earlier rely on `git` and having the user's environment 
configured. In practice, this means having a valid private SSH key on
your server with access to every repository you need.

With Packages 3.2, the github or gitlab auth token are loaded from their 
respective `RemoteConfiguration` and inserted into the repository options in
the generated Satis configuration. This means that Packages no longer requires
that the user running the Packages application to have a bunch of private keys
different SSH keys configured, and adding a Remote no longer requires logging
in to the server to add such a key.

It boils down to adding handling for two new keys in a repository: 
`github-token` and `gitlab-token`.

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:somereally/neatproject.git",
      "github-token": "123abcdef456",
    },
    {
      "type": "vcs",
      "url": "git@gitlab.example.com:another/awesomeproject.git",
      "gitlab-token": "xyz123",
    } 
  ]
}
```

If these values exist, the modified `GitHubDriver` or `GitLabDriver` sets the
token on the Composer IO so the respective API can be used. And since this
is done for every repository, multiple different users on the same GitLab 
instance can now easily share a Packages instance.

Three lines in each file were all that were necessary to accomplish the task:

```php
<?php

// lib/Composer/Repository/Vcs/GitHubDriver.php

namespace Composer\Repository\Vcs;

class GitHubDriver extends VcsDriver 
{
    // ...
    
    protected function getContents($url, $fetchingRepoData = false)
    {
        // These lines are all that changed in GitHubDriver.php.
        if (isset($this->repoConfig['github-token'])) {
            $this->io->setAuthentication("github.com", $this->repoConfig['github-token']);
        }
        
        // ...
    }
    
    // ...
}
```

```php
<?php

// lib/Composer/Repository/Vcs/GitLabDriver.php

namespace Composer\Repository\Vcs;

class GitLabDriver extends VcsDriver 
{
    // ...
    
    protected function getContents($url, $fetchingRepoData = false)
    {
        // These lines are all that changed in GitLabDriver.php.
        if (isset($this->repoConfig['gitlab-token'])) {
            $this->io->setAuthentication($this->originUrl, $this->repoConfig['gitlab-token'], 'private-token');
        }
        
        // ...
    }
    
    // ...
}
```

The final piece of the puzzle is setting up a new PSR-0 root in `composer.json`.

```json
  // ...
  "autoload": {
    // ...
    "psr-0": {
      "Composer\\": "lib/Composer/"
    }
  },
  // ...
}
```