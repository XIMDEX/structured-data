<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;
use Ximdex\StructuredData\src\Models\Value;

class Entity extends Model
{    
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
    
    public function toJsonLD() : array
    {
        $object = [
            '@context' => 'http://schema.org'
        ];
        $object = array_merge($object, $this->entityToObjectSchema($this));
        return $object;
    }
    
    private function entityToObjectSchema(Entity $entity) : array
    {
        // Schema type
        $object = [
            '@type' => $entity->schema->name
        ];
        
        // Properties values
        foreach ($entity->values as $value) {
            $property = $value->availableType->propertySchema->property->name;
            if ($value->availableType->type == Schema::THING_TYPE) {
                if (! $value->ref_entity_id) {
                    
                    // No entity defined for this value !
                    continue;
                }
                if ($value->referenceEntity->schema_id != $value->availableType->schema_id) {
                    
                    // Schema for entity is different to property type !
                    continue;
                }
                $referenceEntity = $this->entityToObjectSchema($value->referenceEntity);
                if (! $referenceEntity) {
                    
                    // There is no t values for this property
                    continue;
                }
                $maxCardinality = $value->availableType->propertySchema->max_cardinality;
                if ($maxCardinality === null or $maxCardinality > 1) {
                    $object[$property][] = $referenceEntity;
                } else {
                    $object[$property] = $referenceEntity;
                }
                
            } else {
                $object[$property] = $value->value;
            }
        }
        return $object;
    }
}
