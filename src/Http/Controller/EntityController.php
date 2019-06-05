<?php

namespace Ximdex\StructuredData\Controllers;

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
        return response()->json($entity->toJsonLD());
    }
    
    public function store(EntityRequest $request)
    {
        Entity::create($request->all());
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
