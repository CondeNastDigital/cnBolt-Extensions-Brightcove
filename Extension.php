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
        $this->addTwigFilter('brightcovePlayer', 'twigBCPlayer');

        // New field
        $this->app['config']->getFields()->addField(new BrightcoveField());

        // Backend object selection
        if ($this->app['config']->getWhichEnd()=='backend'){
            $this->app['htmlsnippets'] = true;
            $this->addJquery();
            $this->addJavascript('assets/backend.js');
	    $this->addCSS('assets/backend.css');
        }
        // Frontend assets
        else {
            $this->addCSS('assets/frontend.css');
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
        return "Brightcove";
    }

    /**
     * Twig function {{ content\shortCodes() }} in Namespace extension.
     * @param string $input      contents to parse
     * @param array $options     (Optional) additional options like "contenttypes"
     * @return Twig_Markup       parsed and replaced content
     */
    function twigBCPlayer($input, $options = array())
    {
        $defaults = array(
            "template" => "brightcove_player.twig",
            "player" => $this->config["player"],
            "account" => $this->config["account"]
        );

        $options = $options + $this->config["options"] + $defaults;

        $rendered = $this->app['render']->render($options["template"], array(
            "video" => array(
                "id" => $input
            ),
            "options" => $options
        ));

        return new Twig_Markup($rendered, 'UTF-8');
    }

}






