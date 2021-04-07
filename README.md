# discord-bundle
![PHP](https://img.shields.io/badge/php-%5E8.0-red?logo=php&logoColor=FFFFFF&style=flat)
![Symfony Version](https://img.shields.io/badge/symfony-%5E5.2-lightgrey?logo=symfony&logoColor=FFFFFF&style=flat)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/mrgoodbytes8667/discord-bundle/release?style=flat&label=stable)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/mrgoodbytes8667/discord-bundle/tests?style=flat)
![License](https://img.shields.io/badge/license-CC--BY--NC--4.0-lightgrey?logo=creative-commons&logoColor=FFFFFF&style=flat)  
A Symfony bundle that adds some routes and mechanisms for Discord OAuth

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
[![License](https://i.creativecommons.org/l/by-nc/4.0/88x31.png)]("http://creativecommons.org/licenses/by-nc/4.0/)  
discord-bundle by [MrGoodBytes](https://www.goodbytes.live) is licensed under a [Creative Commons Attribution-NonCommercial 4.0 International License](http://creativecommons.org/licenses/by-nc/4.0/).  
Based on a work at [https://github.com/mrgoodbytes8667/discord-bundle](https://github.com/mrgoodbytes8667/discord-bundle).
