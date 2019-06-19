<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class HereditableSchema extends Model
{
    public $fillable = ['priority', 'version_id'];
    
    public $hidden = ['version_id'];
    
    public $appends = ['version_tag'];
    
    public function getVersionTagAttribute(): ?string
    {
        if ($this->version) {
            return $this->version->tag;
        }
        return null;
    }
    
    public function version()
    {
        return $this->belongsTo(Version::class);
    }
}
