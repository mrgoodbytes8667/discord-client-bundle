# This package/repository has been renamed/moved!
Please use [![GitHub](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/repo-mrgoodbytes8667_discord--client--bundle-lightgrey.png)](https://packagist.org/packages/mrgoodbytes8667/discord-client-bundle) / [![Packagist](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/package-mrgoodbytes8667_discord--client--bundle-lightgrey.png)](https://packagist.org/packages/mrgoodbytes8667/discord-client-bundle) instead!

# discord-bundle
[![Packagist Version](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/packagist.png)](https://packagist.org/packages/mrgoodbytes8667/discord-bundle)
[![PHP from Packagist](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/php.png)](https://packagist.org/packages/mrgoodbytes8667/discord-bundle)
![Symfony Version](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/symfony.png)
![Packagist License](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/license.png)  
![GitHub Workflow Status](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/stable.png)
![GitHub Workflow Status](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/build.png)
[![codecov](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/coverage.png)](https://codecov.io/gh/mrgoodbytes8667/discord-bundle)  
A Symfony bundle that adds some of the API calls, along with some routes and mechanisms for Discord OAuth

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require mrgoodbytes8667/discord-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require mrgoodbytes8667/discord-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Bytes\DiscordBundle\BytesDiscordBundle::class => ['all' => true],
];
```

## Mocks and Fixtures
The sample Discord API responses are generated using [mrgoodbytes8667/mock-generator](https://github.com/mrgoodbytes8667/mock-generator)

## License
[![License](https://github.com/mrgoodbytes8667/discord-bundle/raw/main/license-cc.png)]("http://creativecommons.org/licenses/by-nc/4.0/)  
discord-bundle by [MrGoodBytes](https://www.goodbytes.live) is licensed under a [Creative Commons Attribution-NonCommercial 4.0 International License](http://creativecommons.org/licenses/by-nc/4.0/).  
Based on a work at [https://github.com/mrgoodbytes8667/discord-bundle](https://github.com/mrgoodbytes8667/discord-bundle).
