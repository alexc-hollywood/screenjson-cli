<?php

namespace App\Services\ScreenJSON\Model\Visualization;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;

use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Style extends Assignable implements JsonSerializable, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['id', 'content'];

    public $id;

    public $default = false;

    public $content;

    public function __construct ( ?string $id, ?string $content, ?bool $is_default = false )
    {
        if ( empty ($id) )
        {
            throw new InvalidParameterException ("Style ID cannot be blank.");
        }

        if ( !preg_match ('/^[\p{L}\p{N}_-]+$/u', $id) )
        {
            throw new InvalidParameterException ("Style IDs may only contain letters, numbers, hyphens, and/or underscores.");
        }

        if ( empty ($content) )
        {
            throw new InvalidParameterException ("Style content cannot be blank.");
        }

        $this->id = $id;

        $this->content = $content;

        $this->default = $is_default;
    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);

        return $data;
    }
}
