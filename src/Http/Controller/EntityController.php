<?php

namespace Ximdex\StructuredData\Controllers;

use Illuminate\Support\Facades\DB;
use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Requests\EntityRequest;
use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Models\Schema;

class EntityController extends Controller
{
    public function index()
    {
        return response()->json(Entity::all());
    }
    
    public function show(Entity $entity)
    {
        return response()->json($entity->toJsonLD(true));
    }
    
    public function store(EntityRequest $request)
    {
        DB::beginTransaction();
        
        // Create the new entity
        $entity = Entity::create($request->all());
        
        // Add the properties values
        $properties = [];
        foreach ($request->properties as $property) {
            $position = 1;
            foreach ($property['values'] as $value) {
                $type = AvailableType::findOrFail($property['type']);
                if ($type->type == Schema::THING_TYPE) {
                    
                    // Value is an entity ID
                    $entityId = $value;
                    $value = null;
                } else {
                    $entityId = null;
                }
                $properties[] = [
                    'available_type_id' => $type->id,
                    'value' => $value,
                    'ref_entity_id' => $entityId,
                    'position' => $position++
                ];
            }
        }
        $entity->values()->createMany($properties);
        
        // Save entity data
        DB::commit();
        $entity->values;
        return $entity;
    }
    
    public function update(EntityRequest $request, Entity $entity)
    {
        $entity->update($request->all());
    }
    
    public function destroy(Entity $entity)
    {
        $entity->delete();
    }
    
    public function loadNodes(int $id)
    {
        $entity = Entity::findOrFail($id);
        return response()->json($entity->nodes);
    }
}
