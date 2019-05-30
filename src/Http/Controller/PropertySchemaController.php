<?php

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\PropertySchema;

class PropertySchemaController extends Controller
{
    public function avaliableTypes(int $id)
    {
        $propSchema = PropertySchema::findOrFail($id);
        return response()->json($propSchema->availableTypes);
    }
    
    
}
