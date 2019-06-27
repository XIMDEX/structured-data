<?php   

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\AvailableType;
use Ximdex\StructuredData\Requests\AvailableTypeRequest;

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
        return AvailableType::create($request->all());
    }
    
    public function update(AvailableTypeRequest $request, AvailableType $availableType)
    {
        $availableType->update($request->all());
        return $availableType;
    }
    
    public function destroy(AvailableType $availableType)
    {
        $availableType->delete();
    }
}
