The Symfony JsonApiBundle
=========================

The JsonApiBundle is a [Symfony][2] bundle. it is the fastest way to generate API based on [JsonApi][1]
 using [woohoolabs/yin][3] Library.

Under development ...
=====================

## Installing

1. Install the bundle
    ```
    composer require paknahad/jsonapi-bundle
    ```

1. Add to ``config/bundles.php``
    ```
    Paknahad\JsonApiBundle\JsonApiBundle::class => ['all' => true],
    ```

## Usage

1. Make Entity
    ```
    bin/console make:entity
    ```
2. Make CRUD API
    ```
    bin/console make:api
    ```

[1]: https://symfony.com/
[2]: http://jsonapi.org/
[3]: https://github.com/woohoolabs/yin