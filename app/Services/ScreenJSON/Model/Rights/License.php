<?php

namespace App\Services\ScreenJSON\Model\Rights;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidLicenseException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

use Composer\Spdx\SpdxLicenses;

class License extends Assignable implements JsonSerializable, MetaObjectInterface
{
    public $required = ['identifier', 'ref'];

    public $identifier;

    public $ref;

    public function __construct ( string $identifier, string $ref )
    {
        if (! (new SpdxLicenses())->getLicenseByIdentifier ($identifier) )
        {
            throw new InvalidLicenseException ("License identifier is not a valid SPDX ID.");
        }

        if (! filter_var ($ref, FILTER_VALIDATE_URL) )
        {
            throw new InvalidLicenseException ("License must specify a valid URL reference with full details.");
        }

        $this->identifier = $identifier;

        $this->ref = $ref;
    }


}
