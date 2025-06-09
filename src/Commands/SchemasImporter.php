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

class SchemasImporter extends Command
{
    /**
     * Simple types used for schema properties for available types
     *
     * @var array
     */
    const SIMPLE_SCHEMA_TYPES = [
        'schema:Boolean' => AvailableType::BOOLEAN_TYPE,
        'schema:Date' => AvailableType::DATE_TYPE,
        'schema:DateTime' => AvailableType::DATETIME_TYPE,
        'schema:Number' => AvailableType::NUMBER_TYPE,
        'schema:Text' => AvailableType::TEXT_TYPE,
        'schema:Time' => AvailableType::TIME_TYPE,
        'schema:Integer' => AvailableType::NUMBER_TYPE,
        'schema:Float' => AvailableType::NUMBER_TYPE,
        'schema:URL' => AvailableType::TEXT_TYPE,
        'rdfs:Class' => AvailableType::THING_TYPE,
        'schema:thing' => AvailableType::THING_TYPE
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
    protected $description = 'Schemas and properties importer';
    
    private $schemas;
    
    private $properties;
    
    private $version;

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
            $this->processSubclasses();
            
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
        return json_decode($content, true);
    }

    /**
     * Safely retrieve a property from a Schema.org JSON-LD element,
     * supporting both legacy full URIs and compact/prefixed keys.
     */
    protected function getSchemaOrgKey(array $element, string $key) {
        // Try full URI (legacy style)
        $fullUri = "http://schema.org/$key";
        if (isset($element[$fullUri])) {
            return $element[$fullUri];
        }

        // Try compacted with prefix (modern JSON-LD style)
        $prefixed = "schema:$key";
        if (isset($element[$prefixed])) {
            return $element[$prefixed];
        }

        // Rare fallback: plain key
        if (isset($element[$key])) {
            return $element[$key];
        }

        return null;
    }


    private function normalizeId($id): string {
        if (is_array($id) && isset($id['@id'])) {
            $id = $id['@id'];
        }
    
        if (!is_string($id)) {
            return '';
        }

        // Fix DataType not being a simple type
        if($id == 'rdfs:Class'){
            return $id;
        }
    
        // Handle compact IRIs (e.g. "schema:Text")
        if (str_starts_with($id, 'schema:')) {
            return str_replace('schema:', 'http://schema.org/', $id);
        }
    
        // If it's already a full URI, return it
        if (str_starts_with($id, 'http://schema.org/')) {
            return $id;
        }
    
        // Fallback: treat as a local term, prefix it
        return 'http://schema.org/' . ltrim($id, '#:/');
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
                // throw new \Exception('Cannot load a version tag from source data given (@id)');
                
                // Version is not supported anymore but still used in the api
                // This allows the user to not to have to specify any given version tag
                $this->version->tag = 'Latest';
            }
        }
        foreach ($data['@graph'] as $element) {
            if (! array_key_exists('@type', $element)) {
                continue;
            }
            // if ($element['@type'] == 'rdfs:Class') {
            $type = (array) ($element['@type'] ?? []);
            if (in_array('rdfs:Class', $type)) {
                
                // Element is a schema type
                $schema = $this->retrieveElementData($element);
                if (isset($element['rdfs:subClassOf'])) {
                    $schema['inheritedOf'] = $this->retrieveElements($element['rdfs:subClassOf']);
                }

                if (!isset($element['@id'])) {
                    $errors[] = 'Schema element missing @id: ' . json_encode($element);
                    continue;
                }
                
                // FIXED PROPERTIES-SCHEMAS 
                $id = $this->normalizeId($element['@id']);
                $this->schemas[$id] = $schema;                

                $this->schemas[$element['@id']] = $schema;

            } elseif ($element['@type'] == 'rdf:Property') {
                
                // Element is a property type
                $property = $this->retrieveElementData($element);
                

                // domainIncludes → $property['schemas']
                $domainIncludes = $this->getSchemaOrgKey($element, 'domainIncludes');
                if ($domainIncludes) {
                    $property['schemas'] = $this->retrieveElements($domainIncludes);
                } else {
                    $errors[] = "Property {$property['label']} does not provide a schema (domainIncludes)";
                }

                // rangeIncludes → $property['types']
                $rangeIncludes = $this->getSchemaOrgKey($element, 'rangeIncludes');
                if ($rangeIncludes) {
                    $property['types'] = $this->retrieveElements($rangeIncludes);
                } else {
                    $errors[] = "Property {$property['label']} does not provide a type (rangeIncludes)";
                }

                // supersededBy → $property['supersededBy']
                $supersededBy = $this->getSchemaOrgKey($element, 'supersededBy');
                if ($supersededBy) {
                    $superseded = $this->retrieveElements($supersededBy);
                    if (!empty($superseded)) {
                        $property['supersededBy'] = $superseded[0];
                    }
                }


                foreach (($property['schemas'] ?? []) as $schemaRef) {
                    if (is_array($schemaRef) && isset($schemaRef['@id'])) {
                        $id = $this->normalizeId($schemaRef['@id']);
                    } elseif (is_string($schemaRef)) {
                        $id = $this->normalizeId($schemaRef);
                    } else {
                        $errors[] = "Invalid schemaRef structure for property {$property['label']}: " . json_encode($schemaRef);
                        continue;
                    }
                
                    if (!is_string($id)) {
                        $errors[] = "Invalid schema ID (not a string) for {$property['label']}: " . json_encode($id);
                        continue;
                    }
                
                    if (!isset($this->schemas[$id])) {
                        $errors[] = "Schema {$id} not found for {$property['label']} property";
                    }
                }

                foreach (($property['types'] ?? []) as $typeRef) {
                    if (is_array($typeRef) && isset($typeRef['@id'])) {
                        $id = $this->normalizeId($typeRef['@id']);
                    } elseif (is_string($typeRef)) {
                        $id = $this->normalizeId($typeRef);
                    } else {
                        $errors[] = "Invalid typeRef structure for property {$property['label']}: " . json_encode($typeRef);
                        continue;
                    }
                
                    if (!is_string($id)) {
                        $errors[] = "Invalid type ID (not a string) for {$property['label']}: " . json_encode($id);
                        continue;
                    }
                
                    if (
                        !isset($this->schemas[$id]) &&
                        !array_key_exists($id, self::SIMPLE_SCHEMA_TYPES)
                    ) {
                        $errors[] = "Schema type {$id} not found for {$property['label']} property";
                    }
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
        // return [
        //     'label' => trim(isset($element['rdfs:label']['@value']) ? $element['rdfs:label']['@value'] : $element['rdfs:label']),
        //     'comment' => trim(strip_tags($element['rdfs:comment']))
        // ];
        $label = isset($element['rdfs:label']['@value'])
        ? $element['rdfs:label']['@value']
        : (is_array($element['rdfs:label']) ? json_encode($element['rdfs:label']) : $element['rdfs:label']);

        $comment = '';
        if (isset($element['rdfs:comment'])) {
            if (is_array($element['rdfs:comment']) && isset($element['rdfs:comment']['@value'])) {
                $comment = $element['rdfs:comment']['@value'];
            } elseif (is_string($element['rdfs:comment'])) {
                $comment = $element['rdfs:comment'];
            } elseif (is_array($element['rdfs:comment']) && is_array($element['rdfs:comment'][0]) && isset($element['rdfs:comment'][0]['@value'])) {
                $comment = $element['rdfs:comment'][0]['@value'];
            }
        }

        return [
            'label' => trim($label),
            'comment' => trim(strip_tags($comment)),
        ];
    }
    
    /**
     * Recursive method to search in an array of elements the values with key given 
     * 
     * @param array $elements
     * @param string $key
     * @return array
     */
    private function retrieveElements($elements, string $key = '@id'): array{
        $result = [];

        if (!is_array($elements)) {
            return $result;
        }

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
     * Create or update database schemas from previous loaded content
     *
     * @throws \Exception
     */
    private function processSchemas(): void
    {
        $this->info('Creating or updating schemas...');
        if (! is_array($this->schemas)) {
            throw new \Exception('No schemas loaded');
        }
        $bar = $this->output->createProgressBar(count($this->schemas));
        $bar->start();
        foreach ($this->schemas as & $schema) {
            
            // Create or update the schema
            $schemaModel = Schema::updateOrCreate(
                ['label' => $schema['label']],
                ['comment' => $schema['comment'], 'version_id' => $this->version->id]
            );
            $schema['id'] = $schemaModel->id;
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
    }
    
    /**
     * Generate the inheritable relations between schemas and subclasses in database
     *
     * @throws \Exception
     */
    private function processSubclasses(): void
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
            $parentSchemas = [];
            foreach ($schema['inheritedOf'] as $schemaEntry) {
                $schemaId = is_array($schemaEntry) && isset($schemaEntry['@id']) 
                    ? $this->normalizeId($schemaEntry['@id']) 
                    : $this->normalizeId($schemaEntry);
            
                if (array_key_exists($schemaId, self::SIMPLE_SCHEMA_TYPES)) {
                    continue; // Skip simple types
                }
            
                if (!array_key_exists($schemaId, $this->schemas)) {
                    $errors[] = "There is not a schema {$schemaId} to make the relation with {$schema['label']} schema";
                    continue;
                }
            
                $parentSchemas[$this->schemas[$schemaId]['id']] = [
                    'version_id' => $this->version->id
                ];
            } 

            Schema::findOrFail($schema['id'])->schemas()->syncWithoutDetaching($parentSchemas);
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
                
                // Check if deprecated property does not exist in database to continue to avoid its update
                $propertyModel = Property::where('label', $property['label'])->first();
                if (! $propertyModel) {
                    
                    // Dont create this deprecated property
                    continue;
                }
                
                // If there is not created a superseder property with new version label, update the label (reuse)
                $newProperty = $this->properties[$property['supersededBy']];
                if (! Property::where('label', $newProperty['label'])->first()) {
                    
                    // Update the deprecated property to new label 
                    $propertyModel->label = $newProperty['label'];
                    $propertyModel->save();
                }
                
                // Dont update this deprecated property
                continue;
            }
            
            // Check the schemas and values given
            if (! array_key_exists('schemas', $property)) {
                $errors[] = "Property {$property['label']} does not provide a schema to assing";
                continue;
            }
            if (! array_key_exists('types', $property)) {
                $errors[] = "Property {$property['label']} does not provide a type value to use";
                continue;
            }
            
            // Create or update the property
            $propertyModel = Property::updateOrCreate(
                ['label' => $property['label']], 
                ['comment' => $property['comment'], 'version_id' => $this->version->id]);
            
            // Assing the property to its schemas
            $propSchemas = [];
            if (array_key_exists('schemas', $property)) {
                foreach ($property['schemas'] as $schemaId) {
                    if (! array_key_exists($schemaId, $this->schemas)) {
                        
                        // Hide deprecated schemas warning
                        // Add new ones to the list when needed
                        $deprecatedSchemas = [
                            'schema:DeliveryTimeSettings'
                        ];
                        
                        if (!in_array($schemaId,$deprecatedSchemas)) {
                            $errors[] = "Schema {$schemaId} not found for {$property['label']} property";
                        }
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
                ///// DEBUG
                // $this->warn($type);
                ///// DEBUG

                // Load the schema or simple type
                if (array_key_exists($type, self::SIMPLE_SCHEMA_TYPES)) {
                    
                    // It is a simple schema type
                    $schemaId = null;
                    $type = self::SIMPLE_SCHEMA_TYPES[$type];
                } else {
                    
                    // the available type must be a schema
                    if (! array_key_exists($type, $this->schemas)) {
                        $errors[] = "Schema type {$type} not found for {$property['label']} property";
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
