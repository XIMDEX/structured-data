<?php   

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\PropertySchema;
use Ximdex\StructuredData\Requests\PropertySchemaRequest;

class PropertySchemaController extends Controller
{
    public function avaliableTypes(int $id)
    {
        $propSchema = PropertySchema::findOrFail($id);
        return response()->json($propSchema->availableTypes);
    }
    
    public function index()
    {
        $propSchemas = PropertySchema::all();
        return response()->json($propSchemas);
    }
    
    public function show(PropertySchema $propSchema)
    {
        $propSchema->availableTypes;
        return response()->json($propSchema);
    }
    
    public function store(PropertySchemaRequest $request)
    {
        $propSchema = new PropertySchema();
        $propSchema->save();
    }
    
    public function update(PropertySchemaRequest $request, PropertySchema $propSchema)
    {
        $propSchema->save();
    }
    
    public function destroy(PropertySchema $propSchema)
    {
        $propSchema->delete();
    }
}
