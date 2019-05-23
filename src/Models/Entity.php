<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;
use Ximdex\StructuredData\src\Models\Value;

class Entity extends Model
{   
    protected $appends = ['schema_url'];
    
    public function getSchemaUrlAttribute(): string
    {
        return route('linked-data.load-entity', ['entity' => $this->id]);
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
    
    public function toJsonLD() : array
    {
        $object = [
            '@context' => 'http://schema.org'
        ];
        $object = array_merge($object, $this->entityToSchema());
        return $object;
    }
    
    protected function entityToSchema(int $depth = null, array & $entities = []): array
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
                    $referenceEntity = $value->referenceEntity->entityToSchema($depth - 1, $entities);
                }
                if (! $referenceEntity) {
                    
                    // There is not values for this property
                    continue;
                }
                $maxCardinality = $value->availableType->propertySchema->max_cardinality;
                if ($maxCardinality === null or $maxCardinality > 1) {
                    $object[$property][] = $referenceEntity;
                } else {
                    $object[$property] = $referenceEntity;
                }
                $propertiesOrder[$property] = $order;
            } else {
                $object[$property] = $value->value;
                $propertiesOrder[$property] = $order;
            }
        }
        array_multisort($propertiesOrder, $object);
        return $object;
    }
}
