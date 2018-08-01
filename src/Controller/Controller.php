<?php

namespace Paknahad\JsonApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as Base;
use WoohooLabs\Yin\JsonApi\JsonApi;

class Controller extends Base
{
    private static $jsonApi;

    /**
     * @return JsonApi
     */
    public function jsonApi()
    {
        if (! self::$jsonApi) {
            self::$jsonApi = $this->container->get('request_stack')->getCurrentRequest()->get('JsonApi');
        }

        return self::$jsonApi;
    }
}