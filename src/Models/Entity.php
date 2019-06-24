<?php

namespace Ximdex\StructuredData\Models;

use Ximdex\StructuredData\Core\Model;

class Entity extends Model
{
    public $fillable = ['schema_id'];
    
    public $hidden = ['created_at', 'updated_at', 'schema'];
    
    public static $except = ['schema_id'];
    
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
    
    public function values(bool $deprecated = false)
    {
        $result = $this->hasMany(Value::class);
        if ($deprecated === false and Version::getLatest()) {
            
            // Get the latest version of values with custom user ones (using related available type)
            $result->select((new Value)->getTable() . '.*');
            $result->join((new AvailableType)->getTable(), (new AvailableType)->getTable() . '.id', '=', 'available_type_id');
            $result->where(function ($query) {
                $query->whereRaw('version_id IS NULL OR version_id = ?', Version::getLatest());
            });
        }
        return $result;
    }
    
    /**
     * Load the values for the given properties to add or update in this entity
     * 
     * @param array $properties
     * @param bool $delete
     */
    public function loadValuesFromProperties(array $properties, bool $delete = false): void
    {
        foreach ($properties as $property) {
            $position = 1;
            $deleted = [];
            foreach ($property['values'] as $value) {
                
                // Value can be an array contain the id, value and other optional information
                if (is_array($value)) {
                    $id = $value['id'];
                    $value = $value['value'];
                } else {
                    $id = null;
                }
                $type = AvailableType::findOrFail($property['type']);
                if ($delete or (isset($property['deleteAll']) and $property['deleteAll']) and ! in_array($type->id, $deleted)) {
                    
                    // Delete all the current values for this property
                    $this->values()->where('available_type_id', $type->id)->delete();
                    $deleted[] = $type->id;
                }
                if ($type->type == Schema::THING_TYPE) {
                    
                    // Value is an entity ID
                    $entityId = $value;
                    $value = null;
                } else {
                    $entityId = null;
                }
                $updated = false;
                if ($id) {
                    if ($entityValue = $this->values->find($id)) {
                        
                        // Update an existing property value in this entity
                        $entityValue->value = $value;
                        $entityValue->ref_entity_id = $entityId;
                        $entityValue->position = $position++;
                        $updated = true;
                    }
                }
                if (! $updated) {
                    
                    // Create a new property value with given data
                    $this->values->add(new Value([
                        'entity_id' => $this->id, 
                        'available_type_id' => $type->id,
                        'value' => $value,
                        'ref_entity_id' => $entityId,
                        'position' => $position++
                    ]));
                }
            }
        }
    }
    
    public function reference(array $show = [])
    {
        $reference = [
            '@type' => $this->schema->name,
            '@id' => $this->schema_url
        ];
        if ($show) {
            if (in_array('uid', $show)) {
                $reference['@uid'] = $this->schema->id;
            }
            if (in_array('version', $show)) {
                $reference['@version'] = $this->schema->version->id;
            }
            if (in_array('tag', $show)) {
                $reference['@tag'] = $this->schema->version_tag;
            }
        }
        return $reference;
    }
    
    public function toJsonLD(array $show = []) : array
    {
        $object = [
            '@context' => 'http://schema.org'
        ];
        $object = array_merge($object, $this->entityToSchema($show));
        return $object;
    }
    
    protected function entityToSchema(array $show, int $depth = null, array & $entities = []): array
    {
        $properties = [];
        if (in_array($this->id, $entities) === false) {
            
            // This entity will never be shown in later levels
            $entities[] = $this->id;
        }
        
        // Schema type
        $object = $this->reference($show);
        
        // Map for properties order
        $propertiesOrder = array_map(function() { return 0; }, $object);
        
        // Properties values
        foreach ($this->values(in_array('deprecated', $show))->orderBy('position')->get() as $value) {
            $property = $value->availableType->propertySchema->property->name;
            $order = $value->availableType->propertySchema->order;
            if ($value->availableType->type == Schema::THING_TYPE) {
                if (! $value->ref_entity_id) {
                    
                    // No entity defined for this value !
                    continue;
                }
                if (! $value->referenceEntity->schema->extends($value->availableType->schema)) {
                    
                    // Schema for entity is different to property type !
                    continue;
                }
                if (in_array($value->ref_entity_id, $entities) !== false || $depth === 0) {
                    
                    // We dont continue if the entity has been showed before
                    $referenceEntity = $value->referenceEntity->reference();
                } else {
                    $referenceEntity = $value->referenceEntity->entityToSchema([], $depth - 1, $entities);
                }
                if (! $referenceEntity) {
                    
                    // There is not values for this property
                    continue;
                }
                $entityValue = $referenceEntity;
            } else {
                
                // This property as simple type value
                $entityValue = $value->value;
            }
            if ($show) {
                $entityValue = $this->addExtraInfoToValue($value, $show, is_array($entityValue) ? $entityValue : null);
            }
            if (array_key_exists($property, $object)) {
                
                // If the property is already setted, an array will be used adding current value
                if (! in_array($property, $properties)) {
                    $object[$property] = [$object[$property]];
                    $properties[] = $property;
                }
                $object[$property][] = $entityValue;
            } else {
                
                // Property with a single value
                $object[$property] = $entityValue;
            }
            $propertiesOrder[$property] = $order;
        }
        
        // Sort properties by order attribute
        array_multisort($propertiesOrder, $object);
        return $object;
    }
    
    private function addExtraInfoToValue(Value $value, array $show, array $data = null)
    {
        if ($data === null) {
            $data = [];
        }
        if (in_array('uid', $show)) {
            $data['@uid'] = $value->id;
        }
        if (in_array('type', $show)) {
            $data['@type'] = $value->available_type_id;
        }
        if (in_array('version', $show)) {
            $data['@version'] = $value->availableType->version->id;
        }
        if (in_array('tag', $show)) {
            $data['@tag'] = $value->availableType->version_tag;
        }
        if (! $data) {
            return $value->value;
        }
        if (! $value->ref_entity_id) {
            $data['@value'] = $value->value;
        }
        return $data;
    }
}
