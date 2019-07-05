<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Value extends Model
{
    public $fillable = ['available_type_id', 'item_id', 'ref_item_id', 'value', 'position'];
    
    public $hidden = ['created_at', 'updated_at'];
    
    public static $except = ['available_type_id', 'item_id'];
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
    public function referenceItem()
    {
        return $this->belongsTo(Item::class, 'ref_item_id');
    }
    
    public function availableType()
    {
        return $this->belongsTo(AvailableType::class);
    }
}
