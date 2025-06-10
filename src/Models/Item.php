<?php

namespace Ximdex\StructuredData\Models;

use Illuminate\Support\Str;
use Ximdex\StructuredData\Core\Model;

class Item extends Model
{
    public $fillable = ['schema_id'];
    
    public $hidden = ['created_at', 'updated_at', 'schema'];
    
    public static $except = ['schema_id'];
    
    protected $appends = ['schema_url', 'schema_label'];
    
    public function getSchemaUrlAttribute(): string
    {
        return route('structured-data.' . config('structureddata.api.routes.load-item') . '.show', ['item' => $this->id]);
    }
    
    public function getSchemaLabelAttribute() : ?string
    {
        if ($this->schema_id) {
            return $this->schema->label;
        }
        return null;
    }
    
    public function schema()
    {
        return $this->belongsTo(Schema::class);
    }
   
    public function nodes()
    {
        return $this->belongsToMany(Node::class, (new ItemNode)->getTable());
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
     * Load the values for the given properties to add or update in this item
     * 
     * @param array $properties
     * @param bool $delete
     */
    public function loadValuesFromProperties(array $properties, bool $delete = false): void
    {
        foreach ($properties as $property) {
            $position = 1;
            $type = AvailableType::findOrFail($property['type']);
            if ($delete or (isset($property['delete']) and $property['delete'])) {
                
                // Delete all the current values for this property
                $this->values()->where('available_type_id', $type->id)->delete();
            }
            foreach ($property['values'] as $value) {
                
                // Value can be an array contain the id, value and other optional information
                if (is_array($value)) {
                    $id = $value['id'];
                    $value = $value['value'];
                } else {
                    $id = null;
                }
                if ($type->type == Schema::THING_TYPE) {
                    
                    // Value is an item ID
                    $itemId = $value;
                    $value = null;
                } else {
                    $itemId = null;
                }
                $updated = false;
                if ($id and $itemValue = $this->values->find($id)) {
                        
                    // Update an existing property value in this item
                    $itemValue->value = $value;
                    $itemValue->ref_item_id = $itemId;
                    $itemValue->position = $position++;
                    $updated = true;
                }
                if (! $updated) {
                    
                    // Create a new property value with given data
                    $this->values->add(new Value([
                        'item_id' => $this->id, 
                        'available_type_id' => $type->id,
                        'value' => $value,
                        'ref_item_id' => $itemId,
                        'position' => $position++
                    ]));
                }
            }
        }
    }
    
    public function reference(array $show = []): array
    {
        $reference = [
            '@type' => $this->schema->label,
            '@id' => $this->schema_url
        ];
        if ($show) {
            if (in_array('uid', $show)) {
                $reference['@uid'] = $this->schema->id;
                $reference['@item'] = $this->id;
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
    
    public function toJsonLD(array $show = []): array
    {
        $object = [
            '@context' => 'http://schema.org'
        ];
        return array_merge($object, $this->itemToSchema($show));
    }
    
    public function toRDF(): string
    {
        $result = $this->toJsonLD();
        $graph = new \EasyRdf_Graph();
        $graph->parse(json_encode($result), 'jsonld');
        $format = \EasyRdf_Format::getFormat('rdfxml');
        return $graph->serialise($format);
    }
    
    public function toNeo4j(): string
    {
        // Get JSON+LD  
        $result = $this->toJsonLD(['uid']);
        
        // Obtain the script code for each item in the result
        $query = $this->itemToNeo4j($result);
        
        // Retrieve main item generated
        return $query . 'RETURN ' . Str::camel($result['@type']) . $result['@item'];
    }
    
    protected function itemToSchema(array $show, ?int $depth = null, array & $items = []): array
    {
        $properties = [];
        if (in_array($this->id, $items) === false) {
            
            // This item will never be shown in later levels
            $items[] = $this->id;
        }
        
        // Schema type
        $object = $this->reference($show);
        
        // Map for properties order
        $propertiesOrder = array_map(function() { return 0; }, $object);
        
        // Properties values
        foreach ($this->values(in_array('deprecated', $show))->orderBy('position')->get() as $value) {
            $property = $value->availableType->propertySchema->property->label;
            $order = $value->availableType->propertySchema->order;
            if ($value->availableType->type == Schema::THING_TYPE) {
                if (! $value->ref_item_id) {
                    
                    // No item defined for this value !
                    continue;
                }
                if (in_array($value->ref_item_id, $items) !== false || $depth === 0) {
                    
                    // We dont continue if the item has been showed before
                    $referenceItem = $value->referenceItem->reference();
                } else {
                    $referenceItem = $value->referenceItem->itemToSchema([], $depth - 1, $items);
                }
                if (! $referenceItem) {
                    
                    // There is not values for this property
                    continue;
                }
                $itemValue = $referenceItem;
            } else {
                
                // This property as simple type value
                $itemValue = $value->value;
            }
            if ($show) {
                $itemValue = $this->addExtraInfoToValue($value, $show, is_array($itemValue) ? $itemValue : null);
            }
            if (array_key_exists($property, $object)) {
                
                // If the property is already setted, an array will be used adding current value
                if (! in_array($property, $properties)) {
                    $object[$property] = [$object[$property]];
                    $properties[] = $property;
                }
                $object[$property][] = $itemValue;
            } else {
                
                // Property with a single value
                $object[$property] = $itemValue;
            }
            $propertiesOrder[$property] = $order;
        }
        
        // Sort properties by order attribute
        array_multisort($propertiesOrder, $object);
        return $object;
    }
    
    /**
     * Return an array with some extra information
     * 
     * @param Value $value
     * @param array $show
     * @param array $data
     * @return string|array
     */
    private function addExtraInfoToValue(Value $value, array $show, ?array $data = null)
    {
        if ($data === null) {
            $data = [];
        }
        if (in_array('uid', $show)) {
            $data['@uid'] = $value->id;
            if ($value->ref_item_id) {
                $data['@item'] = $value->ref_item_id;
            }
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
        if (! $value->ref_item_id) {
            $data['@value'] = $value->value;
        }
        return $data;
    }
    
    private function itemToNeo4j(array $item, array & $items = []): string
    {
        // Create or update main item
        $itemName = Str::camel($item['@type']) . $item['@item'];
        
        // Only declare an item one time
        if (! in_array($item['@item'], $items)) {
            $query = "MERGE ({$itemName}:{$item['@type']} {id:{$item['@item']}})" . PHP_EOL;
            $items[] = $item['@item'];
        } else {
            $query = '';
        }
        
        // Item properties
        foreach ($item as $property => $values) {
            if (Str::startsWith($property, '@')) {
                continue;
            }
            if (array_key_exists('@value', $values) or array_key_exists('@item', $values)) {
                $values = [$values];
            }
            $simpleValues = [];
            foreach ($values as $value) {
                if (array_key_exists('@item', $value)) {
                    
                    // This is a item, so a relation for this property is needed
                    $query .= $this->itemToNeo4j($value, $items);
                    $relatedItemName = Str::camel($value['@type']) . $value['@item'];
                    $query .= "MERGE ({$itemName})-[:" . strtoupper($property) . "]->({$relatedItemName})" . PHP_EOL;
                } elseif (array_key_exists('@value', $value)) {
                    $simpleValues[] = $value['@value'];
                }
            }
            if ($simpleValues) {
                
                // This property contains an array of values
                $query .= "SET {$itemName}.{$property} = ['" . implode("', '", $simpleValues) . "']" . PHP_EOL;
            }
        }
        return $query;
    }
}
