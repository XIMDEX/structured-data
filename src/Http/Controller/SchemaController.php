<?php

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Requests\SchemaRequest;

class SchemaController extends Controller
{
    public function index()
    {
        return response()->json(Schema::all());
    }
    
    public function show(Schema $schema)
    {
        $schema->schemas;
        $schema['properties'] = $schema->properties();
        return response()->json($schema);
    }
    
    public function store(SchemaRequest $request)
    {
        return Schema::create($request->all());
    }
    
    public function update(SchemaRequest $request, Schema $schema)
    {
        $schemas = $request->get('schemas', false);
        if ($schemas !== false) {
            $syncSchemas = [];
            foreach ($schemas as $data) {
                if (isset($data['priority'])) {
                    $syncSchemas[$data['id']] = ['priority' => (int) $data['priority']];
                } else {
                    $syncSchemas[] = $data['id'];
                }
            }
            $schema->schemas()->sync($syncSchemas);
        }
        $schema->update($request->all());
        return $schema;
    }
    
    public function destroy(Schema $schema)
    {
        $schema->delete();
    }
}
