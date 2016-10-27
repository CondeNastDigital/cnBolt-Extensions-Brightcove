<?php

namespace Bolt\Extension\CND\Brightcove;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\File\Stylesheet;
use Bolt\Controller\Zone;
use Bolt\Extension\CND\Brightcove\Controller\BrightcoveController;
use Bolt\Extension\SimpleExtension;
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
class Extension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    public function registerFields()
    {
        return [
            new Field\BrightcoveField(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendControllers()
    {
        /* @var \Bolt\Application $app */
        $app = $this->getContainer();
        $config = $this->getConfig();

        return [
            '/brightcove' => new BrightcoveController($app, $config),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigFilters()
    {
        return [
           'brightcovePlayer' => 'twigBCPlayer',
         ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        $this->addJquery();

        $resources    = $this->container['resources'];
        $extensionUrl = $resources->getUrl('bolt').'brightcove';
        $extensionWebPath = $resources->getUrl('extensions')."vendor/cnd/brightcove/";

        return [
            (new JavaScript('js/backend.js'))->setZone(Zone::BACKEND)->setPriority(10),
            (new JavaScript('js/extension-for/sir-trevor.js'))
                ->setZone(Zone::BACKEND)
                ->setPriority(11)
                ->setAttributes([
                    'data-extension-url="'.$extensionUrl.'"',
                    'data-extension-web-path="'.$extensionWebPath.'"'
                ]),
            (new Stylesheet('css/backend.css'))->setZone(Zone::BACKEND),
            (new Stylesheet('css/frontend.css'))->setZone(Zone::FRONTEND),
        ];
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
            "player" => $this->getConfig()["player"],
            "account" => $this->getConfig()["account"]
        );

        $options = $options + $this->getConfig()["options"] + $defaults;

        $rendered = $this->renderTemplate($options["template"], array(
            "video" => array(
                "id" => $input
            ),
            "options" => $options
        ));

        return new Twig_Markup($rendered, 'UTF-8');
    }

}






