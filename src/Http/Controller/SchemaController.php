<?php

namespace Ximdex\StructuredData\Http\Controller;

use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Http\Requests\SchemaRequest;

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
        $schema = Schema::create($request->all());
        $this->assingSchemas($schema, $request);
        return $schema;
    }
    
    public function update(SchemaRequest $request, Schema $schema)
    {
        $schema->update($request->all());
        $this->assingSchemas($schema, $request);
        $schema->schemas;
        return $schema;
    }
    
    public function destroy(Schema $schema)
    {
        $schema->delete();
    }
    
    private function assingSchemas(Schema $schema, SchemaRequest $request): void
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
    }
}
