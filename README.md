JsonApiBundle For Symfony
=========================

JsonApiBundle is a [Symfony][1] bundle. It is the fastest way to generate API based on [JsonApi][2]
 using [woohoolabs/yin][3] Library.

## Installing

1. Install symfony
    ```
    composer create-project symfony/skeleton YOUR_PROJECT
    ```

2. Install the [maker bundle][4]
    ```
    composer require symfony/maker-bundle phootwork/collection --dev
    ```

3. Install the bundle
    ```
    composer require paknahad/jsonapi-bundle
    ```

4. Add below line to ``config/bundles.php``
    ```
    Paknahad\JsonApiBundle\JsonApiBundle::class => ['all' => true],
    ```

## Usage

1. Use below command to generate entities one by one:
    ```
    bin/console make:entity
    ```
    for example, Book and Author entity is as follows:
    ```php
    class Book
    {
        /**
         * @ORM\Id()
         * @ORM\GeneratedValue()
         * @ORM\Column(type="integer")
         */
        private $id;
    
        /**
         * @ORM\Column(type="string", length=255)
         */
        private $title;
    
        /**
         * @ORM\Column(type="string", length=20, nullable=true)
         */
        private $isbn;
    
        /**
         * @ORM\ManyToMany(targetEntity="App\Entity\Author", inversedBy="books")
         */
        private $authors;
     
        ... 
    ```
    ```php
    class Author
    {
        /**
         * @ORM\Id()
         * @ORM\GeneratedValue()
         * @ORM\Column(type="integer")
         */
        private $id;
    
        /**
         * @ORM\Column(type="string", length=255)
         * @Assert\NotBlank()
         * @Assert\Length(min=3)
         */
        private $name;
    
        /**
         * @ORM\ManyToMany(targetEntity="App\Entity\Book", mappedBy="authors")
         */
        private $books;
     
        ...
    ```

2. Generate CRUD API:
    ```
    bin/console make:api
    ```
3. You can find the generated "collections" for [postman][5] and [swagger][6] in the following path and then test the API:
    ```
    collection/postman.json
    collection/swagger.yaml
    ```

## Features

1. Pagination 
    ```
    http://example.com/books?page[number]=5&page[size]=30
    ```
2. Relationships
    ```
    http://example.com/books?include=authors
    ```
    multiple relationships
    ```
    http://example.com/books?include=authors.phones,publishers
    ```
3. Search

    This request will return all the books that author's name begin with ``hamid``
    ```
    http://example.com/books?filter[authors.name]=hamid%
    ```
    Below line has additional condition: books which have "php" in their title.
    ```
    http://example.com/books?filter[title]=%php%&filter[authors.name]=hamid%
    ```
4. Validation

    Error on validating associations
    ```json
    {
        "jsonapi": {
            "version": "1.0"
        },
        "errors": [
            {
                "detail": "Invalid value for this relation",
                "source": {
                    "pointer": "/data/relationships/authors",
                    "parameter": "1"
                }
            }
        ]
    }
    ```
    Validate attributes if you have defined validators on entities.
    ```json
    {
        "jsonapi": {
            "version": "1.0"
        },
        "errors": [
            {
                "detail": "This value is too short. It should have 3 characters or more.",
                "source": {
                    "pointer": "/data/attributes/name",
                    "parameter": "h"
                }
            }
        ]
    }
    ```
5. Error handler

    All errors such as:
    - Internal server error (500)
    - Not found (404)
    - Access denied (403)
    
    has responses like this:
    ```json
    {
        "meta": {
            "code": 0,
            "message": "No route found for \"GET /book\"",
            "file": "/var/www/vendor/symfony/http-kernel/EventListener/RouterListener.php",
            "line": 139,
            "trace": [
                {
                    "file": "/var/www/vendor/symfony/event-dispatcher/EventDispatcher.php",
                    "line": 212,
                    "function": "onKernelRequest"
                },
                {
                    "file": "/var/www/vendor/symfony/event-dispatcher/EventDispatcher.php",
                    "line": 44,
                    "function": "doDispatch"
                },
                {
                    "file": "/var/www/vendor/symfony/http-kernel/HttpKernel.php",
                    "line": 125,
                    "function": "dispatch"
                },
                {
                    "file": "/var/www/vendor/symfony/http-kernel/HttpKernel.php",
                    "line": 66,
                    "function": "handleRaw"
                },
                {
                    "file": "/var/www/vendor/symfony/http-kernel/Kernel.php",
                    "line": 188,
                    "function": "handle"
                },
                {
                    "file": "/var/www/public/index.php",
                    "line": 37,
                    "function": "handle"
                }
            ]
        },
        "links": {
            "self": "/book"
        },
        "errors": [
            {
                "status": "404",
                "code": "NO_ROUTE_FOUND_FOR_\"GET_/BOOK\"",
                "title": "No route found for \"GET /book\""
            }
        ]
    }
    ```
    NOTICE: the "meta" field gets filled just on development environment.

[1]: https://symfony.com/
[2]: http://jsonapi.org/
[3]: https://github.com/woohoolabs/yin
[4]: https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
[5]: https://www.getpostman.com/
[6]: https://swagger.io/
