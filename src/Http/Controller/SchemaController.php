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
        $schema->inheritedSchemas();
        $schema['properties'] = $schema->properties();
        return response()->json($schema);
    }
    
    public function store(SchemaRequest $request)
    {
        return Schema::create($request->all());
    }
    
    public function update(SchemaRequest $request, Schema $schema)
    {
        $inheritedSchemas = $request->get('inherited_schemas', false);
        if ($inheritedSchemas !== false) {
            $syncSchemas = [];
            foreach ($inheritedSchemas as $data) {
                if (isset($data['priority'])) {
                    $syncSchemas[$data['id']] = ['priority' => (int) $data['priority']];
                } else {
                    $syncSchemas[] = $data['id'];
                }
            }
            $schema->inheritedSchemas()->sync($syncSchemas);
        }
        return $schema->update($request->all());
    }
    
    public function destroy(Schema $schema)
    {
        $schema->delete();
    }
}
