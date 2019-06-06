<?php   

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Requests\ValueRequest;
use Ximdex\StructuredData\src\Models\Value;

class ValueController extends Controller
{   
    public function index()
    {
        return response()->json(Value::all());
    }
    
    public function show(Value $value)
    {
        return response()->json($value);
    }
    
    public function store(ValueRequest $request)
    {
        Value::create($request->all());
    }
    
    public function update(ValueRequest $request, Value $value)
    {
        $value->update($request->all());
    }
    
    public function destroy(Value $value)
    {
        $value->delete();
    }
}
