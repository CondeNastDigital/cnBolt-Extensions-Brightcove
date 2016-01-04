<?php

namespace Bolt\Extension\CND\Brightcove;

use Bolt\Field\FieldInterface;

class BrightcoveField implements FieldInterface
{

    public function getName()
    {
        return 'brightcove';
    }

    public function getTemplate()
    {
        return 'brightcove_field.twig';
    }

    public function getStorageType()
    {
        return 'text';
    }

    public function getStorageOptions()
    {
        return array('default'=>'');
    }

}
