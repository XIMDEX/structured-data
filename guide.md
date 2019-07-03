
# Structured data user guide
This is an API service for structured data management and maintenance. Written in PHP as a package for Laravel framework.
This document provide the installation process information and the API usage.
## Installation procedure
First you need an instance of Laravel proyect in order to use this package. It can be downloaded from the Github repository located in https://github.com/laravel/laravel.
Run composer to require our extension under your laravel directory:
> composer require ximdex linked-data

This command install the package in vendor extension.
The next step is the database generation, so is needed to run the migration process for laravel using the appropiate command for this purpose:
> php artisan migrate

After this operation we have created the database tables in our proyect with the *strdata* prefix to avoid the previous table names colision.
## Basic usage example
So at this time we can be able to consume the API operations to manage the schemas and items data.
We assume in this manual that the host for our Laravel instance is under a host named localhost. Then we call the endpoint whit this example usage:
> [GET] http://localhost/api/v1/schema

This petition will retrieve a JSON code with all the schemas that are actually created in our storage. 
## API endpoints specificaction
Operations over schemas an items data give you a complete control to create or update schemas, generate items of a type of this schemas and associate information to its properties.
### Schemas importation
For a brand new installation, there are not any schema to work with. To import a schema definitions URL provided by schema.org you can run this console command under the laravel directory:
`php artisan schemas:import http://schema.org/version/latest/schema.jsonld`
> Actually this command only support schemas.org definitions in JSONLD format.

If the given URL does not contain the schema definitions version you can provide by another argument:
`php artisan schemas:import http://schema.org/version/3.3/schema.jsonld 3.3`
> If there is any schema definition created by a previous importation or by any user, this information will be update to the new version and only the ausent definitions will be marked as obsolete. However we can still use this deprecated information later.
 
Also it's possible to realize this importation using the next endpoint:
`http://localhost/api/v1/schemas-import?url=https://schema.org/version/latest/schema.jsonld`
> We recommend to import always the latest version of the entire definitions from http://schema.org/version/latest/all-layers.jsonld resource.

### Schema operations
Each schema is a type of item that contain a variable number of properties and inhertite any more from other parent schemas.
#### Retrieve a concrete schema
You can use the schema identificator to load the schema attributes, its properties and the other schemas that are inhertied. Usage:
> [GET] http://localhost/api/v1/schema/45

The result of this request will be a JSON code with the definition of a schema identified with number 45, in this example the *Person* schema:
```json
{
  "id": 45,
  "label": "Person",
  "comment": "A person (alive, dead, undead, or fictional).",
  "version_id": 5,
  "properties": [
    {
      "id": 16,
      "min_cardinality": 0,
      "max_cardinality": null,
      "order": 1,
      "default_value": null,
      "version_id": 5,
      "label": "memberOf",
      "comment": "An Organization (or ProgramMembership) to which this Person or Organization belongs.",
      "schema_label": "Person",
      "version_tag": "3.7",
      "types": [
        {
          "id": 18,
          "schema_id": 53,
          "type": "Thing",
          "version_id": 5,
          "schema_label": "Organization",
          "version_tag": "3.7"
        },
        ... other types ...
      ]
    },
    ... other properties ...
    ],
  "version_tag": "3.7",
  "schemas": [
    {
      "id": 488,
      "label": "Thing",
      "comment": "The most generic type of item.",
      "version_id": 5,
      "version_tag": "3.7",
      "schemas": []
    }
  ]
}
```
We can see all the properties and the available types to each one for the type *Person* and the inherited schemas given in the "schemas" node. In this example the properties from the *Thing* schema.
#### The show parameter
This argument can be used to show extra information in the schema definition. Usage:
>[GET] http://localhost/api/v1/schema/45?show=deprecated

This is a list of 
* Deprecated: It's possible to retrieve the deprecated definitions like old version properties or types for this schema, using the *deprecated* value.

