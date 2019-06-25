<?php

namespace Ximdex\StructuredData\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Models\Value;
use Ximdex\StructuredData\Requests\EntityRequest;

class EntityController extends Controller
{
    const RDF_FORMAT = 'rdf';
    
    public function index()
    {
        return response()->json(Entity::all());
    }
    
    public function show(Entity $entity)
    {
        if (Request::get('show')) {
            $show = explode(',', Request::get('show'));
        } else {
            $show = [];
        }
        $result = $entity->toJsonLD($show);
        if (Request::get('format') == self::RDF_FORMAT) {
            $graph = new \EasyRdf_Graph();
            $graph->parse(json_encode($result), 'jsonld');
            $format = \EasyRdf_Format::getFormat('rdfxml');
            $result = $graph->serialise($format);
            return response($result, 200, ['Content-Type' => 'application/xml']);
        }
        return response()->json($result);
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
        
        // Values deletion by given ids in request parameter
        if (is_array($request->delete)) {
            Value::destroy($request->delete);
        }
        
        // Update the entity properties values
        $entity->loadValuesFromProperties($request->properties);
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
