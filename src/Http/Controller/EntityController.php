<?php

namespace Ximdex\StructuredData\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Requests\EntityRequest;

class EntityController extends Controller
{
    public function index()
    {
        return response()->json(Entity::all());
    }
    
    public function show(Entity $entity)
    {
        return response()->json($entity->toJsonLD(Request::has('extra')));
    }
    
    public function store(EntityRequest $request)
    {
        // Start a transaction
        DB::beginTransaction();
        
        // Create the new entity
        $entity = Entity::create($request->all());
        
        // Add the properties values
        $entity->loadValuesFromProperties($request->properties);
        $entity->push();
        
        // Save entity data
        DB::commit();
        $entity->values;
        return $entity;
    }
    
    public function update(EntityRequest $request, Entity $entity)
    {
        // Start a transaction
        DB::beginTransaction();
        
        // Update the entity values
        $entity->update($request->all());
        
        // Update the entity properties values
        /*
        if (Request::method() == 'PUT') {
            $entity->values()->delete();
            $delete = false;
        } else  {
            
            // Patch method
            $delete = true;
        }
        */
        $entity->loadValuesFromProperties($request->properties);    // , $delete);
        $entity->push();
        
        // Save entity data
        DB::commit();
        $entity->values;
        return $entity;
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
