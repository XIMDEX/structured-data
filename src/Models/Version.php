<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Version extends Model
{
    public $hidden = ['updated_at'];

    /**
     * Latest version
     * 
     * @var int
     */
    private static $versionId;
    
    /**
     * *
     * Return the last version imported
     *
     * @return int|NULL
     */
    public static function getLatest(): ?int
    {
        if (! self::$versionId) {
            self::$versionId = (new static)->orderBy('id', 'desc')->first()->id;
        }
        return self::$versionId;
    }
}
