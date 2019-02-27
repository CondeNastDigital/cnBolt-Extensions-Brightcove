<?php

namespace Bolt\Extension\CND\Brightcove\Field;

use Bolt\Storage\Field\Type\FieldTypeBase;
use Doctrine\DBAL\Types\Type;

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
        return Type::getType('text');
    }
    
    public function getStorageOptions()
    {
        return ['notnull' => false];
    }    

}
