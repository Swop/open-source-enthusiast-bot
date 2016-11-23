Create GitHub bots with Symfony: Open-Source enthusiast bot (DEMO)
============

This project is the app used as a demo for my ["Create GitHub bots with Symfony" (sildes)](https://speakerdeck.com/swop/create-github-bots-with-php-and-symfony) talk.

## What is the bot goal

The app, named "Open-Source enthusiast bot", will automatically comment on each freshly opened pull-request in order to thank the developer for his/her contribution.

## Things to look at
The interesting stuff is mostly in the controllers:
- [AppBundle\Controller\WebHookController](src/AppBundle/Controller/WebHookController.php): Bot logic, using hook annotation (provided by the [github-webhook-bundle](https://github.com/Swop/github-webhook-bundle) bundle)
- [AppBundle\Controller\WebHookWithoutAnnotationController](src/AppBundle/Controller/WebHookWithoutAnnotationController.php): Bot logic, without annotation

## Requirements

This bot requires:
- a personal token created on GitHub, with "repo" scope: https://github.com/settings/tokens/new
- a registered web hook, configured to listen to the "pull_request" event: https://github.com/{ORG}/{REPO}/settings/hooks/new
