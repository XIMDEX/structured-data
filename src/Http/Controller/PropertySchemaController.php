<?php   

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\PropertySchema;
use Ximdex\StructuredData\Requests\PropertySchemaRequest;

class PropertySchemaController extends Controller
{
    public function avaliableTypes(PropertySchema $propSchema)
    {
        // $propSchema = PropertySchema::findOrFail($id);
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
        if ($request->input('name') and $propSchema->name != $request->input('name')) {
            
            // New property name was given
            $propSchema->property_id = null;
        }
        return $propSchema->update($request->all());
    }
    
    public function destroy(PropertySchema $propSchema)
    {
        $propSchema->delete();
    }
}
