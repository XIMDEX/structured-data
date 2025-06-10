<?php   

namespace Ximdex\StructuredData\Http\Controller;

use Illuminate\Support\Facades\DB;
use Ximdex\StructuredData\Models\PropertySchema;
use Ximdex\StructuredData\Http\Requests\PropertySchemaRequest;

class PropertySchemaController extends Controller
{
    public function avaliableTypes(PropertySchema $property)
    {
        return response()->json($property->availableTypes);
    }
    
    public function index()
    {
        return response()->json(PropertySchema::all());
    }
    
    public function show(PropertySchema $property)
    {
        return response()->json($property);
    }
    
    public function store(PropertySchemaRequest $request)
    {
        DB::beginTransaction();
        $property = PropertySchema::create($request->all());
        $property->assingTypes($request->get('types'));
        DB::commit();
        return $property;
    }
    
    public function update(PropertySchemaRequest $request, PropertySchema $property)
    {
        if ($request->input('label') and $property->label != $request->input('label')) {
            
            // New property label was given
            $property->property_id = null;
        }
        DB::beginTransaction();
        $property->update($request->all());
        $property->assingTypes($request->get('types'));
        DB::commit();
        return $property;
    }
    
    public function destroy(PropertySchema $property)
    {
        $property->delete();
    }
}
