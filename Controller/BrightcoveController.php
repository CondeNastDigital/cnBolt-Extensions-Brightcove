<?php

namespace Bolt\Extension\CND\Brightcove\Controller;

use Bolt\Application;
use Bolt\Content;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;

class BrightcoveController implements ControllerProviderInterface
{
    private $app;
    private $config;

    public function __construct (Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->app['twig.loader.filesystem']->prependPath(__DIR__."/../twig");
    }

    public function connect(\Silex\Application $app)
    {
        $ctr = $app['controllers_factory'];
        $ctr->post('/search/{searchterm}', array($this, 'search'));

        return $ctr;
    }

    /**
     * List content as specified
     *
     * request body has to contain a JSON string in this format:
     * {content: [ <type>/<id>, <type>/<id>, <type>/<id>, ... ] }
     *
     * @param string $searchterm
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function search($searchterm, Request $request)
    {
        return new JsonResponse();
    }

}