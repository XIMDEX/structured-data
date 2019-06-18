<?php

namespace Ximdex\StructuredData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ximdex\StructuredData\Models\Property;
use Ximdex\StructuredData\Models\PropertySchema;
use Ximdex\StructuredData\Models\Schema;
use Ximdex\StructuredData\Models\Version;
use GuzzleHttp\Client;
use Ximdex\StructuredData\Models\AvailableType;

class SchemaImporter extends Command
{
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
     * The name and signature of the console command
     *
     * @var string
     */
    protected $signature = 'schemas:import {url} {tag?}';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Schemas and attributes importer';
    
    private $schemas;
    
    private $properties;
    
    private $version;
    
    /**
     * Create a new command instance
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command
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
        $this->version = new Version();
        $this->version->url = $url;
        if ($tag = $this->argument('tag')) {
            $this->version->tag = $tag;
        }
        
        // Read URL content
        try {
            $data = $this->readFromURL($url);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 2;
        }
        
        // Load schemas and properties from JSON source
        try {
            $this->readFromJson($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 3;
        }
        unset($data);
        $this->info('Schemas information has been processed successfully');
        
        // Start a transaction
        $this->info('Starting database transaction');
        DB::beginTransaction();
        try {
            
            // Generate a new importing version
            $this->version->save();
            
            // Process schemas
            $this->processSchemas();
            
            // Create the heritable relations between schemas
            $this->processSchemasRelations();
            
            // Create the schemas properties
            $this->processProperties();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return 4;
        }
        
        // Commit and close transaction
        $this->info('Closing database transaction');
        DB::commit();
        
        // Importation has finished
        $this->info('Importation finished successfully');
    }
    
    /**
     * Read and return the content of an URL given, also check if this content is LD+JSON type
     * 
     * @param string $url
     * @throws \Exception
     * @return array
     */
    private function readFromURL(string $url): array
    {
        // Read from URL content
        $this->info('Reading ' . $url);
        $client = new Client();
        $response = $client->request('GET', $url);
        unset($client);
        if ($response->getHeader('Content-Type')[0] != 'application/ld+json') {
            throw new \Exception('The content of the URL must be of type ld+json');
        }
        $content = $response->getBody();
        unset($response);
        $this->info('Content from URL has been readed');
        
        // Load JSON code
        $this->info('Getting schemas information from URL content');
        $data = json_decode($content, true);
        return $data;
    }
    
    /**
     * Read an array with JSON retrieved data and return an array ready to use for this database importer
     * 
     * @param array $data
     * @throws \Exception
     */
    private function readFromJson(array $data): void
    {
        if (! isset($data['@graph'])) {
            throw new \Exception('URL does not contain schemas information');
        }
        $this->schemas = [];
        $this->properties = [];
        if ($this->version->tag === null) {
            if (array_key_exists('@id', $data)) {
                $this->version->tag = $data['@id'];
            } else {
                throw new \Exception('Cannot load a version tag from source data given (@id)');
            }
        }
        foreach ($data['@graph'] as $element) {
            if (! array_key_exists('@type', $element)) {
                continue;
            }
            if ($element['@type'] == 'rdfs:Class') {
                
                // Element is an schema type
                $schema = $this->retrieveElementData($element);
                if (isset($element['rdfs:subClassOf'])) {
                    $schema['inheritedOf'] = $this->retrieveElements($element['rdfs:subClassOf']);
                }
                $this->schemas[$element['@id']] = $schema;
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
                    $supersededBy = $this->retrieveElements($element['http://schema.org/supersededBy']);
                    $property['supersededBy'] = $supersededBy[0];
                }
                $this->properties[$element['@id']] = $property;
            }
        }
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
    
    /**
     * Create or update database schemas from privous loaded content
     *
     * @throws \Exception
     */
    private function processSchemas(): void
    {
        $this->info('Generating schemas...');
        if (! is_array($this->schemas)) {
            throw new \Exception('No schemas loaded');
        }
        $bar = $this->output->createProgressBar(count($this->schemas));
        $bar->start();
        foreach ($this->schemas as & $schema) {
            
            // Create or update the schema
            $schemaModel = Schema::updateOrCreate(
                ['name' => $schema['name']],
                ['comment' => $schema['comment'], 'version_id' => $this->version->id]
            );
            $schema['id'] = $schemaModel->id;
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
    }
    
    /**
     * Generate the inheritable relations between schemas in database
     *
     * @throws \Exception
     */
    private function processSchemasRelations(): void
    {
        $this->info('Creating the relations between schemas...');
        if (! is_array($this->schemas)) {
            throw new \Exception('No schemas loaded');
        }
        $bar = $this->output->createProgressBar(count($this->schemas));
        $bar->start();
        $errors = [];
        foreach ($this->schemas as $schema) {
            if (! isset($schema['inheritedOf'])) {
                continue;
            }
            $inheritedSchemas = [];
            foreach ($schema['inheritedOf'] as $schemaId) {
                if (array_key_exists($schemaId, self::SIMPLE_SCHEMA_TYPES)) {
                    
                    // Avoid possible relations to simple types
                    continue;
                }
                if (! array_key_exists($schemaId, $this->schemas)) {
                    $errors[] = "There is not a schema {$schemaId} to make the relation with {$schema['name']} schema";
                    continue;
                }
                $inheritedSchemas[$this->schemas[$schemaId]['id']] = [
                    'version_id' => $this->version->id
                ];
            }
            Schema::findOrFail($schema['id'])->inheritedSchemas()->syncWithoutDetaching($inheritedSchemas);
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
        foreach ($errors as $error) {
            $this->warn($error);
        }
    }
    
    /**
     * Create or update the database properties and available types
     *
     * @throws \Exception
     */
    private function processProperties(): void
    {
        $this->info('Creating properties...');
        if (! is_array($this->schemas)) {
            throw new \Exception('No schemas loaded');
        }
        if (! is_array($this->properties)) {
            throw new \Exception('No properties loaded');
        }
        $bar = $this->output->createProgressBar(count($this->properties));
        $bar->start();
        $errors = [];
        foreach ($this->properties as $property) {
            
            // If this property is superseded by another one
            if (array_key_exists('supersededBy', $property)) {
                
                // Check if deprecated property does not exists in database to continue to avoid its updation
                $propertyModel = Property::where('name', $property['name'])->first();
                if (! $propertyModel) {
                    
                    // Dont create this deprecated property
                    continue;
                }
                
                // If there is not created a superseder property with new version name, update the name (reuse)
                $newProperty = $this->properties[$property['supersededBy']];
                if (! Property::where('name', $newProperty['name'])->first()) {
                    
                    // Update the deprecated property to new name 
                    $propertyModel->name = $newProperty['name'];
                    $propertyModel->save();
                }
                
                // Dont update this deprecated property
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
                ['comment' => $property['comment'], 'version_id' => $this->version->id]);
            
            // Assing the property to its schemas
            $propSchemas = [];
            if (array_key_exists('schemas', $property)) {
                foreach ($property['schemas'] as $schemaId) {
                    if (! array_key_exists($schemaId, $this->schemas)) {
                        $errors[] = "Schema {$schemaId} not found for {$property['name']} property";
                        continue;
                    }
                    
                    // Create the relation between the schema and property if not exists
                    $propSchemas[] = PropertySchema::updateOrCreate([
                        'schema_id' => $this->schemas[$schemaId]['id'], 
                        'property_id' => $propertyModel->id
                    ], [
                        'version_id' => $this->version->id
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
                    if (! array_key_exists($type, $this->schemas)) {
                        $errors[] = "Schema type {$type} not found for {$property['name']} property";
                        continue;
                    }
                    $schemaId = $this->schemas[$type]['id'];
                    $type = Schema::THING_TYPE;
                }
                foreach ($propSchemas as $propSchema) {
                    AvailableType::updateOrCreate([
                        'type' => $type, 
                        'schema_id' => $schemaId, 
                        'property_schema_id' => $propSchema->id
                    ], [
                        'version_id' => $this->version->id
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
    }
}
