<?php   

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Requests\ValueRequest;
use Ximdex\StructuredData\Models\Value;

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
        return Value::create($request->all());
    }
    
    public function update(ValueRequest $request, Value $value)
    {
        return $value->update($request->all());
    }
    
    public function destroy(Value $value)
    {
        $value->delete();
    }
}
