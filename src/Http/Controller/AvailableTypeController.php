<?php   

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Requests\AvailableTypeRequest;
use Ximdex\StructuredData\Models\PropertySchema;

class AvailableTypeController extends Controller
{   
    public function index()
    {
        return response()->json(AvailableType::all());
    }
    
    public function show(AvailableType $availableType)
    {
        $availableType->propertySchema;
        return response()->json($availableType);
    }
    
    public function store(AvailableTypeRequest $request)
    {
        $availableType = AvailableType::create($request->all());
        return PropertySchema::findOrFail($availableType->property_schema_id);
    }
    
    public function update(AvailableTypeRequest $request, AvailableType $availableType)
    {
        $availableType->update($request->all());
        return PropertySchema::findOrFail($availableType->property_schema_id);
    }
    
    public function destroy(AvailableType $availableType)
    {
        $availableType->delete();
        return PropertySchema::findOrFail($availableType->property_schema_id);
    }
}
