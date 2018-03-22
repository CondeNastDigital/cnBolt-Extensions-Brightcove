<?php

namespace Bolt\Extension\CND\Brightcove\Field;

use Bolt\Storage\Field\Type\FieldTypeBase;

class BrightcoveField extends FieldTypeBase
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
        return ['notnull' => false];
    }    

}
