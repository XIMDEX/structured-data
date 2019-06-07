<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Entity extends Model
{
    public $fillable = ['schema_id'];
    
    public $hidden = ['created_at', 'updated_at', 'schema'];
    
    protected $appends = ['schema_url', 'schema_name'];
    
    public function getSchemaUrlAttribute(): string
    {
        return route('linked-data.' . config('structureddata.api.routes.load-entity') . '.show', ['entity' => $this->id]);
    }
    
    public function getSchemaNameAttribute() : ?string
    {
        if ($this->schema_id) {
            return $this->schema->name;
        }
        return null;
    }
    
    public function schema()
    {
        return $this->belongsTo(Schema::class);
    }
   
    public function nodes()
    {
        return $this->belongsToMany(Node::class, (new EntityNode)->getTable());
    }
    
    public function values()
    {
        return $this->hasMany(Value::class);
    }
    
    public function reference()
    {
        return [
            '@type' => $this->schema->name,
            '@id' => $this->schema_url
        ];
    }
    
    public function toJsonLD(bool $uid = false) : array
    {
        $object = [
            '@context' => 'http://schema.org'
        ];
        $object = array_merge($object, $this->entityToSchema($uid));
        return $object;
    }
    
    protected function entityToSchema(bool $uid = false, int $depth = null, array & $entities = []): array
    {
        if (in_array($this->id, $entities) === false) {
            
            // This entity will never be shown in later levels
            $entities[] = $this->id;
        }
        
        // Schema type
        $object = $this->reference();
        
        // Map for properties order
        $propertiesOrder = array_map(function() { return 0; }, $object);
        
        // Properties values
        foreach ($this->values()->orderBy('position')->get() as $value) {
            $property = $value->availableType->propertySchema->property->name;
            $order = $value->availableType->propertySchema->order;
            if ($value->availableType->type == Schema::THING_TYPE) {
                if (! $value->ref_entity_id) {
                    
                    // No entity defined for this value !
                    continue;
                }
                if ($value->referenceEntity->schema_id != $value->availableType->schema_id) {
                    
                    // Schema for entity is different to property type !
                    continue;
                }
                if (in_array($value->ref_entity_id, $entities) !== false || $depth === 0) {
                    
                    // We dont continue if the entity has been showed before
                    $referenceEntity = $value->referenceEntity->reference();
                } else {
                    $referenceEntity = $value->referenceEntity->entityToSchema($uid, $depth - 1, $entities);
                }
                if (! $referenceEntity) {
                    
                    // There is not values for this property
                    continue;
                }
                $entityValue = $referenceEntity;
            } else {
                $entityValue = $value->value;
            }
            $maxCardinality = $value->availableType->propertySchema->max_cardinality;
            if ($maxCardinality === null or $maxCardinality > 1) {
                $object[$property][] = $entityValue;
            } else {
                $object[$property] = $entityValue;
            }
            $propertiesOrder[$property] = $order;
        }
        array_multisort($propertiesOrder, $object);
        return $object;
    }
}
