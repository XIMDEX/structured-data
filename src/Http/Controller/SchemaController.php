<?php

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Requests\SchemaRequest;

class SchemaController extends Controller
{
    public function index()
    {
        $schemas = Schema::all();
        return response()->json($schemas);
    }
    
    public function show(int $id)
    {
        $schema = Schema::findOrFail($id);
        $schema->inheritedSchemas;
        $schema['properties'] = $schema->properties();
        return response()->json($schema);
    }
    
    public function store(SchemaRequest $request)
    {
        $schema = new Schema();
        $schema->name = $request->input('name');
        $schema->save();
    }
    
    public function update(SchemaRequest $request, int $id)
    {
        $schema = Schema::findOrFail($id);
        $schema->name = $request->input('name');
        $schema->save();
    }
    
    public function destroy(int $id)
    {
        $schema = Schema::findOrFail($id);
        $schema->delete();
    }
}
