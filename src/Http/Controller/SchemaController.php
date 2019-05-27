<?php

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\Schema;

class SchemaController extends Controller
{
    public function load(int $id)
    {
        $schema = Schema::findOrFail($id);
        // $schema->inheritedSchemas;
        $schema['properties'] = $schema->properties();
        return response()->json($schema);
    }
    
    public function list()
    {
        $schemas = Schema::all();
        return response()->json($schemas);
    }
}
