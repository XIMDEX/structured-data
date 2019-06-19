<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Property extends Model
{
    public $fillable = ['name', 'comment', 'version_id', 'version'];
    
    public $hidden = ['version_id'];
    
    public $appends = ['version_tag'];
    
    public function getVersionTagAttribute(): ?string
    {
        if ($this->version) {
            return $this->version->tag;
        }
        return null;
    }
    
    public function schemaProperties()
    {
        return $this->hasMany(PropertySchema::class);
    }
    
    public function version()
    {
        return $this->belongsTo(Version::class);
    }
}
