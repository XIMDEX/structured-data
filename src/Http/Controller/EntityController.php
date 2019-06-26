<?php

namespace Ximdex\StructuredData\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Ximdex\StructuredData\Models\Entity;
use Ximdex\StructuredData\Models\Value;
use Ximdex\StructuredData\Requests\EntityRequest;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class EntityController extends Controller
{
    const RDF_FORMAT = 'rdf';
    
    const GOOGLE_TESTING_TOOL_URL = 'https://search.google.com/structured-data/testing-tool/validate';
    
    public function index()
    {
        return response()->json(Entity::all());
    }
    
    public function show(Entity $entity)
    {
        if (Request::get('show')) {
            $show = explode(',', Request::get('show'));
        } else {
            $show = [];
        }
        $result = $entity->toJsonLD($show);
        if (Request::get('format') == self::RDF_FORMAT) {
            $graph = new \EasyRdf_Graph();
            $graph->parse(json_encode($result), 'jsonld');
            $format = \EasyRdf_Format::getFormat('rdfxml');
            $result = $graph->serialise($format);
            return response($result, 200, ['Content-Type' => 'application/xml']);
        }
        return response()->json($result);
    }
    
    public function store(EntityRequest $request)
    {
        // Start a transaction
        DB::beginTransaction();
        
        // Create the new entity
        $entity = Entity::create($request->all());
        
        // Add the properties values
        $entity->loadValuesFromProperties($request->properties);
        $entity->push();
        
        // Save entity data
        DB::commit();
        $entity->values;
        return $entity;
    }
    
    public function update(EntityRequest $request, Entity $entity)
    {
        // Start a transaction
        DB::beginTransaction();
        
        // Update the entity values
        $entity->update($request->all());
        
        // Values deletion by given ids in request parameter
        if (is_array($request->delete)) {
            Value::destroy($request->delete);
        }
        
        // Update the entity properties values
        $entity->loadValuesFromProperties($request->properties);
        $entity->push();
        
        // Save entity data
        DB::commit();
        $entity->values;
        return $entity;
    }
    
    public function destroy(Entity $entity)
    {
        $entity->delete();
    }
    
    public function loadNodes(int $id)
    {
        $entity = Entity::findOrFail($id);
        return response()->json($entity->nodes);
    }
    
    public function validation(Entity $entity)
    {
        $client = new Client();
        try {
            $response = $client->request('POST', self::GOOGLE_TESTING_TOOL_URL, [
                'form_params' => [
                    'html' => json_encode($entity->toJsonLD())
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
