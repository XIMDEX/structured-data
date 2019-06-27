<?php   

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\PropertySchema;
use Ximdex\StructuredData\Requests\PropertySchemaRequest;

class PropertySchemaController extends Controller
{
    public function avaliableTypes(PropertySchema $propSchema)
    {
        return response()->json($propSchema->availableTypes);
    }
    
    public function index()
    {
        return response()->json(PropertySchema::all());
    }
    
    public function show(PropertySchema $propSchema)
    {
        return response()->json($propSchema);
    }
    
    public function store(PropertySchemaRequest $request)
    {
        return PropertySchema::create($request->all());
    }
    
    public function update(PropertySchemaRequest $request, PropertySchema $propSchema)
    {
        if ($request->input('label') and $propSchema->label != $request->input('label')) {
            
            // New property label was given
            $propSchema->property_id = null;
        }
        $propSchema->update($request->all());
        return $propSchema;
    }
    
    public function destroy(PropertySchema $propSchema)
    {
        $propSchema->delete();
    }
}
