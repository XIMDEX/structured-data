<?php   

namespace Ximdex\StructuredData\Http\Controller;

use Ximdex\StructuredData\Http\Requests\ValueRequest;
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
        $value->update($request->all());
        return $value;
    }
    
    public function destroy(Value $value)
    {
        $value->delete();
    }
}
