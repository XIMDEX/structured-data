<?php

namespace Ximdex\StructuredData\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Item;
use Ximdex\StructuredData\Models\Value;
use Ximdex\StructuredData\Requests\ItemRequest;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class ItemController extends Controller
{
    const RDF_FORMAT = 'rdf';
    
    const NEO4J_FORMAT = 'neo4j';
    
    const GOOGLE_TESTING_TOOL_URL = 'https://search.google.com/structured-data/testing-tool/validate';
    
    public function index()
    {
        return response()->json(Item::all());
    }
    
    public function show(Item $item)
    {
        if (Request::get('show')) {
            $show = explode(',', Request::get('show'));
        } else {
            $show = [];
        }
        if (Request::get('format') == self::RDF_FORMAT) {
            $result = $item->toRDF($show);
            return response($result, 200, ['Content-Type' => 'application/xml']);
        }
        if (Request::get('format') == self::NEO4J_FORMAT) {
            $result = $item->toNeo4j();
            if (Request::has('download')) {
                return response()->streamDownload(function () use ($result) { echo $result; }, 'neo4j.cypher');
            } else {
                return response($result);
            }
        }
        $result = $item->toJsonLD($show);
        return response()->json($result);
    }
    
    public function store(ItemRequest $request)
    {
        // Start a transaction
        DB::beginTransaction();
        
        // Create the new item
        $item = Item::create($request->all());
        
        // Add the properties values
        $item->loadValuesFromProperties($request->properties);
        $item->push();
        
        // Save item data
        DB::commit();
        $item->values;
        return $item;
    }
    
    public function update(ItemRequest $request, Item $item)
    {
        // Start a transaction
        DB::beginTransaction();
        
        // Update the item values
        $item->update($request->all());
        
        // Values deletion by given ids in request parameter
        if (is_array($request->delete)) {
            Value::destroy($request->delete);
        }
        
        // Update the item properties values
        $item->loadValuesFromProperties($request->properties);
        $item->push();
        
        // Save item data
        DB::commit();
        $item->values;
        return $item;
    }
    
    public function destroy(Item $item)
    {
        $item->delete();
    }
    
    public function loadNodes(int $id)
    {
        $item = Item::findOrFail($id);
        return response()->json($item->nodes);
    }
    
    public function validation(Item $item)
    {
        $client = new Client();
        try {
            $response = $client->request('POST', self::GOOGLE_TESTING_TOOL_URL, [
                'form_params' => [
                    'html' => json_encode($item->toJsonLD())
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['result' => 'error', 'reason' => $e->getMessage()]);
        }
        if ($response->getStatusCode() != Response::HTTP_OK) {
            return response()->json(['result' => 'unavailable', 'reason' => $response->getReasonPhrase()]);
        }
        $test = json_decode(trim(ltrim($response->getBody(), ")]}'")));
        if (! $test->errors or count($test->errors) == 0) {
            return response()->json(['result' => 'success']);
        }
        $result = ['result' => 'fail', 'errors' => []];
        foreach ($test->errors as $error) {
            switch ($error->errorType) {
                case 'INVALID_PREDICATE':
                    $errorMessage = "Invalid property '{$error->args[0]}' for @{$error->args[1]} type";
                    break;
                case 'INVALID_OBJECT':
                    $errorMessage = "Invalid value type @{$error->args[1]} for property '{$error->args[0]}'";
                    break;
                case 'INVALID_ITEMTYPE':
                    $errorMessage = "Invalid schema @{$error->args[0]}";
                    break;
                default:
                    $errorMessage = 'Unknown error';
            }
            if (! in_array($errorMessage, $result['errors'])) {
                $result['errors'][] = $errorMessage;
            }
        }
        return response()->json($result);
    }
}
