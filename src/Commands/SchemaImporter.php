<?php

namespace Ximdex\StructuredData\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ximdex\StructuredData\Models\Schema;
use GuzzleHttp\Client;

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

    const DATA_TYPES = ['rdfs:Class', 'Boolean', 'Date', 'DateTime', 'Number', 'Text', 'Time'];
    
    const SCHEMA_ORG_URL = 'https://schema.org';
    
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
            $schemas = $this->readFromJson($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 3;
        }
        unset($data);
        $this->info('Schemas information has been processed successfully');
        
        // Start a transaction
        $this->info('Starting database transaction');
        DB::beginTransaction();
        
        // Process data
        $this->info('Generating schemas...');
        $bar = $this->output->createProgressBar(count($schemas));
        $bar->start();
        foreach ($schemas as & $schemaData) {
            
            // Create the schema
            $schema = Schema::updateOrCreate(
                ['name' => $schemaData['name']],
                ['comment' => $schemaData['comment']]
            );
            $schemaData['id'] = $schema->id;
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
        
        // Create the heritable relations between schemas
        $this->info('Creating the relations between schemas...');
        $bar->start();
        $errors = [];
        foreach ($schemas as $schemaData) {
            if (! isset($schemaData['inheritedOf'])) {
                continue;
            }
            $inheritedSchemas = [];
            foreach ($schemaData['inheritedOf'] as $schemaId) {
                if (! array_key_exists($schemaId, $schemas)) {
                    $errors[] = "There is not a schema {$schemaId} to make the relation with {$schemaData['name']} schema";
                    // return 4;
                    continue;
                }
                $inheritedSchemas[] = $schemas[$schemaId]['id'];
            }
            Schema::findOrFail($schemaData['id'])->inheritedSchemas()->syncWithoutDetaching($inheritedSchemas);
            $bar->advance();
        }
        $bar->finish();
        $this->line(' Finished');
        foreach ($errors as $error) {
            $this->warn($error);
        }
        
        // TODO Create the schemas properties
        
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
        foreach ($data['@graph'] as $element) {
            if ($element['@type'] == 'rdfs:Class') {
                
                // Element is an schema type
                $schemas[$element['@id']] = [
                    'name' => trim(isset($element['rdfs:label']['@value']) ? $element['rdfs:label']['@value'] : $element['rdfs:label']),
                    'comment' => trim(strip_tags($element['rdfs:comment']))
                ];
                if (isset($element['rdfs:subClassOf'])) {
                    $schemas[$element['@id']]['inheritedOf'] = $this->retrieveElements($element['rdfs:subClassOf']);
                    // $schemas[$element['@id']]['inheritedOf'] = array_column($element['rdfs:subClassOf'], '@id');
                }
            }
        }
        return $schemas;
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
