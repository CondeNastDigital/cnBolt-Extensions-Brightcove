<?php

namespace Bolt\Extension\CND\Brightcove;

use Bolt\BaseExtension;
use Bolt\Content;
use Bolt\Extension\CND\Brightcove\Controller\BrightcoveController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig_Markup;

/**
 * Class Extension
 *
 * Parses a string for any shortcodes and fetches the specified contentobject, renders and replaces the shortcode with it.
 *
 * Shortcode Syntax: [shortcode:<slug>|template=<template.twig>]
 * The second Template parameter is optional. Defaults back to the following in this order
 * - selected template in "embedding_template" field of contentobject
 * - configured template in field "embedding_template" of contenttype
 * - configured template in config value "theme/embedding_template" of theme config.yml
 * - configured template in global config value "general/embedding_template"
 *
 * Please read the README.MD for a complete guide
 *
 * @package Bolt\Extension\CND\ShortCodes
 */
class Extension extends BaseExtension
{

    /**
     * Bolt Extension Initialization
     */
    public function initialize() {
        // Frontend twig function
        $this->addTwigFilter('bcPlayer', 'twigBCPlayer');

        // New field
        $this->app['config']->getFields()->addField(new BrightcoveField());

        // Backend object selection
        if ($this->app['config']->getWhichEnd()=='backend'){
            $this->app['htmlsnippets'] = true;
            $this->addJquery();
            $this->addJavascript('assets/backend.js', true);
			$this->addCSS('assets/backend.css');
        }

        // Set up the routes for backend ajax calls
        $this->app->mount('/brightcove', new BrightcoveController($this->app, $this->config));
    }

    /**
     * Return the name of this extension
     * @return string
     */
    public function getName()
    {
        return "ShortCodes";
    }

    /**
     * Twig function {{ content\shortCodes() }} in Namespace extension.
     * @param string $input      contents to parse
     * @param array $options     (Optional) additional options like "contenttypes"
     * @return Twig_Markup       parsed and replaced content
     */
    function twigBCPlayer($input, $options = array())
    {
        $rendered = $this->app['render']->render($template, array("record" => $record));
        $input = str_replace($match[0], $rendered, $input);

        return new Twig_Markup($input, 'UTF-8');
    }

}






