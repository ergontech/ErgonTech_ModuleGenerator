# ErgonTech_Module

This is a Magento 1.x standalone module scaffolding system.

## Use
First, run a `create-project` command:
```sh
composer \
  create-project \
  --repository='{"type":"vcs","url":"https://github.com/ErgonTech/ErgonTech_Module.git"}' \
  ergontech/module \
  ./module-directory
```

When prompted to remove VCS history, confirm the default (Yes).

Next the `post-create-project-cmd` script will run, prompting you for module information. Follow the prompts.

When the command finishes executing, navigate to the module directory you specified in the `create-project` command.

Run a `composer install` in this directory.

To test whether things were successful, run `phpunit` in the `vendor/bin` directory. It should successfully run one configuration test.
