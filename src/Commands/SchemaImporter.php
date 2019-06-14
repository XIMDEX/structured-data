<?php

namespace Ximdex\StructuredData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Models\PropertySchema;
use Ximdex\StructuredData\Models\Schema;
use GuzzleHttp\Client;
use Ximdex\StructuredData\Models\AvailableType;

class SchemaImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schemas:import {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schemas and attributes importer';
    
    /**
     * Simple types used for schema properties for available types
     * 
     * @var array
     */
    const SIMPLE_SCHEMA_TYPES = [
        'http://schema.org/Boolean' => AvailableType::BOOLEAN_TYPE, 
        'http://schema.org/Date' => AvailableType::DATE_TYPE, 
        'http://schema.org/DateTime' => AvailableType::DATETIME_TYPE, 
        'http://schema.org/Number' => AvailableType::NUMBER_TYPE, 
        'http://schema.org/Text' => AvailableType::TEXT_TYPE, 
        'http://schema.org/Time' => AvailableType::TIME_TYPE,
        'rdfs:Class' => AvailableType::THING_TYPE
    ];
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = $this->argument('url');
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error("Invalid URL {$url} (Ex. use http://schema.org/version/latest/all-layers.jsonld)");
            return 1;
        }
        // $verbose = $this->options('v');
        
        // Read URL content
        $this->info('Reading ' . $url);
        try {
            $client = new Client();
            $response = $client->request('GET', $url);
            unset($client);
            if ($response->getHeader('Content-Type')[0] != 'application/ld+json') {
                throw new \Exception('The content of the URL must be of type ld+json');
            }
            $content = $response->getBody();
            unset($response);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 2;
        }
        $this->info('Content from URL has been readed');
        
        // Load JSON code
        $this->info('Getting schemas information from URL content');
        $data = json_decode($content, true);
        unset($content);
        
        // Load schemas and properties from JSON source
        try {
            $result = $this->readFromJson($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 3;
        }
        unset($data);
        $schemas = & $result['schemas'];
        $properties = & $result['properties'];
        $this->info('Schemas information has been processed successfully');
        
        // Start a transaction
        $this->info('Starting database transaction');
        DB::beginTransaction();
        
        // Process data
        $this->info('Generating schemas...');
        $bar = $this->output->createProgressBar(count($schemas));
        $bar->start();
        foreach ($schemas as & $schema) {
            
            // Create or update the schema
            $schemaModel = Schema::updateOrCreate(
                ['name' => $schema['name']],
                ['comment' => $schema['comment']]
            );
            $schema['id'] = $schemaModel->id;
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
        
        // Create the heritable relations between schemas
        $this->info('Creating the relations between schemas...');
        $bar->start();
        $errors = [];
        foreach ($schemas as $schema) {
            if (! isset($schema['inheritedOf'])) {
                continue;
            }
            $inheritedSchemas = [];
            foreach ($schema['inheritedOf'] as $schemaId) {
                if (array_key_exists($schemaId, self::SIMPLE_SCHEMA_TYPES)) {
                    
                    // Avoid possible relations to simple types
                    continue;
                }
                if (! array_key_exists($schemaId, $schemas)) {
                    $errors[] = "There is not a schema {$schemaId} to make the relation with {$schema['name']} schema";
                    continue;
                }
                $inheritedSchemas[] = $schemas[$schemaId]['id'];
            }
            Schema::findOrFail($schema['id'])->inheritedSchemas()->syncWithoutDetaching($inheritedSchemas);
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
        foreach ($errors as $error) {
            $this->warn($error);
        }
        
        // Create the schemas properties
        $this->info('Creating properties...');
        $bar = $this->output->createProgressBar(count($properties));
        $bar->start();
        $errors = [];
        foreach ($properties as $property) {
            
            // If the property is superseded by another one, avoid the creation and warn it
            if (array_key_exists('supersededBy', $property)) {
                $errors[] = "Property {$property['name']} is superseded by {$property['supersededBy'][0]}";
                continue;
            }
            
            // Check the schemas and values given
            if (! array_key_exists('schemas', $property)) {
                $errors[] = "Property {$property['name']} does not provide a schema to assing";
                continue;
            }
            if (! array_key_exists('types', $property)) {
                $errors[] = "Property {$property['name']} does not provide a type value to use";
                continue;
            }
            
            // Create or update the property
            $propertyModel = Property::updateOrCreate(
                ['name' => $property['name']],
                ['comment' => $property['comment']]
            );
            
            // Assing the property to its schemas
            $propSchemas = [];
            if (array_key_exists('schemas', $property)) {
                foreach ($property['schemas'] as $schemaId) {
                    if (! array_key_exists($schemaId, $schemas)) {
                        $errors[] = "Schema {$schemaId} not found for {$property['name']} property";
                        continue;
                    }
                    
                    // Create the relation between the schema and property if not exists
                    $propSchemas[] = PropertySchema::firstOrCreate([
                        'schema_id' => $schemas[$schemaId]['id'],
                        'property_id' => $propertyModel->id
                    ]);
                }
            }
            
            // For any schema create the available type information from possible values supported in this property
            foreach ($property['types'] as $type) {
                
                // Load the schema or simple type
                if (array_key_exists($type, self::SIMPLE_SCHEMA_TYPES)) {
                    
                    // It is a simple schema type
                    $schemaId = null;
                    $type = self::SIMPLE_SCHEMA_TYPES[$type];
                } else {
                    
                    // the available type must be a schema
                    if (! array_key_exists($type, $schemas)) {
                        $errors[] = "Schema type {$type} not found for {$property['name']} property";
                        continue;
                    }
                    $schemaId = $schemas[$type]['id'];
                    $type = Schema::THING_TYPE;
                }
                foreach ($propSchemas as $propSchema) {
                    AvailableType::updateOrCreate([
                        'type' => $type,
                        'schema_id' => $schemaId,
                        'property_schema_id' => $propSchema->id
                    ]);
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
        foreach ($errors as $error) {
            $this->warn($error);
        }
        
        // Commit and close transaction
        $this->info('Closing database transaction');
        DB::commit();
        
        // Importation has finished
        $this->info('Importation finished successfully');
    }
    
    /**
     * Read an array with JSON retrieved data and return an array ready to use for this database importer
     * 
     * @param array $data
     * @throws \Exception
     * @return array
     */
    private function readFromJson(array $data): array
    {
        if (! isset($data['@graph'])) {
            throw new \Exception('URL does not contain schemas information');
        }
        $schemas = [];
        $properties = [];
        foreach ($data['@graph'] as $element) {
            if ($element['@type'] == 'rdfs:Class') {
                
                // Element is an schema type
                $schema = $this->retrieveElementData($element);
                if (isset($element['rdfs:subClassOf'])) {
                    $schema['inheritedOf'] = $this->retrieveElements($element['rdfs:subClassOf']);
                    // $schema['inheritedOf'] = array_column($element['rdfs:subClassOf'], '@id');
                }
                $schemas[$element['@id']] = $schema;
            } elseif ($element['@type'] == 'rdf:Property') {
                
                // Element is a property type
                $property = $this->retrieveElementData($element);
                
                // Schemas using this property
                if (isset($element['http://schema.org/domainIncludes'])) {
                    $property['schemas'] = $this->retrieveElements($element['http://schema.org/domainIncludes']);
                }
                
                // type of values supported
                if (isset($element['http://schema.org/rangeIncludes'])) {
                    $property['types'] = $this->retrieveElements($element['http://schema.org/rangeIncludes']);
                }
                
                // May property is superseded by another one
                if (isset($element['http://schema.org/supersededBy'])) {
                    $property['supersededBy'] = $this->retrieveElements($element['http://schema.org/supersededBy']);
                }
                $properties[$element['@id']] = $property;
            }
        }
        return [
            'schemas' => $schemas, 
            'properties' => $properties
        ];
    }
    
    /**
     * Return an array with common element information
     * 
     * @param array $element
     * @return array
     */
    private function retrieveElementData(array $element): array
    {
        return [
            'name' => trim(isset($element['rdfs:label']['@value']) ? $element['rdfs:label']['@value'] : $element['rdfs:label']),
            'comment' => trim(strip_tags($element['rdfs:comment']))
        ];
    }
    
    /**
     * Recursive method to search in an array of elements the values with key given 
     * 
     * @param array $elements
     * @param string $key
     * @return array
     */
    private function retrieveElements(array $elements, string $key = '@id'): array
    {
        $result = [];
        if (array_key_exists($key, $elements)) {
            $result[] = $elements[$key];
        } else {
            foreach ($elements as $element) {
                if (is_array($element)) {
                    $result = array_merge($result, $this->retrieveElements($element, $key));
                }
            }
        }
        return $result;
    }
}
