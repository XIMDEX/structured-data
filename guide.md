
# Structured data user guide
This is an API service for structured data management and maintenance. Written in PHP as a package for Laravel framework.
This document provide the installation process information and the API usage.
## Installation procedure
First you need an instance of Laravel project in order to use this package. It can be downloaded from the Github repository located in https://github.com/laravel/laravel.
Run composer to require our extension under your Laravel directory:
```shell
composer require ximdex/linked-data
```
This command install the package in vendor extension.
The next step is the database generation, so is needed to run the migration process for Laravel using the appropriate command for this purpose:
```shell
php artisan migrate
```
After this operation we have created the database tables in our project with the *strdata* prefix to avoid the previous table names collision.
## Basic usage example
So at this time we can be able to consume the API operations to manage the schemas and items data.
We assume in this manual that the host for our Laravel instance is under a host named localhost. Then we call the endpoint whit this example usage:
> [GET] http://localhost/api/v1/schema

This petition will retrieve a JSON code with all the schemas that are actually created in our storage. 
## API endpoints specification
Operations over schemas an items data give you a complete control to create or update schemas, generate items of a type of this schemas and associate information to its properties.
### Schemas importation
For a brand new installation, there are not any schema to work with. To import a schema definitions URL provided by schema.org you can run this console command under the Laravel directory:
```shell
php artisan schemas:import http://schema.org/version/latest/schema.jsonld
```
> Actually this command only support schemas.org definitions in JSON+LD format.

If the given URL does not contain the schema definitions version you can provide by another argument:
```shell
php artisan schemas:import http://schema.org/version/3.3/schema.jsonld 3.3`
```
> If there is any schema definition created by a previous importation or by any user, this information will be update to the new version and only the absent definitions will be marked as obsolete. However we can still use this deprecated information later.
 
Also it's possible to realize this importation using the next endpoint:
> [GET] http://localhost/api/v1/schemas-import?url=https://schema.org/version/latest/schema.jsonld

> We recommend to import always the latest version of the entire definitions from http://schema.org/version/latest/all-layers.jsonld resource. Visit http://schema.org for more details.

### Schema operations
Each schema is a type of item that contain a variable number of properties and inheritance any more from other parent schemas.
#### Retrieve a concrete schema
You can use the schema unique id to load the schema attributes, its properties and the other schemas that are inherited. Usage:
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
      ]
    }
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
#### Schema creation
New schemas can be created by a given label name and optional comment. If this name is in use, the operation will be canceled. 
If the schema extends another schemas attributes, you can use the optional parameter *schemas* to send a list of unique id schemas and the priority of those. This last argument is optional.
Example usage:
> [POST] http://localhost/api/v1/schema
```json
{
    "label": "CafeOrCoffeeShop",
    "comment": "A cafe or coffee shop",
    "schemas": [
        {
            "id": 48,
            "priority": 1
        }
    ]
}
```
The result of this operation will be the created schema containing the new unique id assigned:
```json
{
    "label": "CafeOrCoffeeShop",
    "comment": "A cafe or coffee shop",
    "id": 797,
    "version_tag": null
}
```
> User schemas don't have a version (*null* value instead), so always be ready to use in future imported versions.

This is an example of a response error that happens when  you try to create a schema with a name already in use:
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "label": [
            "The label has already been taken."
        ]
    }
}
```
This is the common format for any error in a manage operation. Look that you can give too many errors with different post fields.
#### Updating a schema
Similar to the creation of a new schema, the only difference is the inclusion of the unique id of the schema to edit in the request operation, and the usage of PUT of PATCH method instead of POST.
> [PATCH] http://localhost/api/v1/schema/1
```json
{
    "comment": "A cafe or coffee shop updated"
}
```
The label parameter (or each you use with minimum cardinality equal or greater to one) is required is you use the PUT method. With the above example using PUT instead of PATCH the response will be:
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "label": [
            "The label field is required."
        ]
    }
}
```
Like in the creation operation, it's possible to send a list schemas to change the properties schema inheritance. This argument is optional, but if it's used, the relation with any schemas previously associated will be removed and replaced with the given ones. For example:
> [PATCH] http://localhost/api/v1/schema/1
```json
{
    "schemas": [
        {
            "id": 45,
            "priority": 1
        }
    ]
}
```
Now our schema with unique id 1 will extends the Person schema with priority 1.
#### Schema deletion
A schema can be deleted using the DELETE method and the unique id of the schema to delete. This operation can't be undone.
> [DELETE] http://localhost/api/v1/schema/1

If the schema is already in use by any item, the operation will be denied.
### Schema properties management
We support some operations to maintenance the properties in use for the schemas.
#### Create a new property
You can associate a property to a schema using this function.
To do this it's necessary to provide the schema unique id and the property unique id in order to make the relation. Also the type or list of types available must be present in this petition to define what king of type will support this property in this schema.
Here is an example of usage:
> [POST] http://localhost/api/v1/property-schema
```json
{
    "schema_id": 53,
    "property_id": 576,
    "types": [
        {"type": "Text"},
        {"type": "Thing", "schema_id": 45}
    ]
}
```
The available types can be a simple type from given list:
* Boolean
* Date
* DateTime
* Time
* Number
* Text

Or a type Thing including a schema id to support an item as value for this type. The above example shows that the *Organization* schema will support items of type *Person* for the property *employee*.
Now we have created a type text and Person for this relation:
```json
{
    "id": 1746,
    "label": "employee",
    "comment": "Someone working for this organization.",
    "schema_label": "Organization",
    "version_tag": null,
    "types": [
        {
            "type": "Text",
            "id": 2403,
            "schema_label": null,
            "version_tag": null
        },
        {
            "type": "Thing",
            "schema_id": 45,
            "id": 2404,
            "schema_label": "Person",
            "version_tag": null
        }
    ]
}
```
Sometimes you want to create a new property instead of use an existent one. It possible to send a property name and an optional comment to create both property and the relation to the desired schema.
For example:
> [POST] http://localhost/api/v1/property-schema
```json
{
    "schema_id": 53,
    "label": "employee",
    "comment": "Someone working for this organization.",
    "types": [
        {"type": "Thing", "schema_id": 45}
    ]
}
```


#### The show parameter
This argument can be used to show extra information in the desired item. It can contain some values separated by commas. Usage:
> [GET] http://localhost/api/v1/item/1?show=deprecated,uid

This is the list of values:
* **uid**: show the unique id values for each element in the item.
* **deprecated**: It's possible to retrieve the deprecated definitions like old version properties or types for this schema, using the *deprecated* value.
* **version**: show the version unique id.


## Contributors
Antonio Jesús Lucena [@ajlucena78](https://github.com/ajlucena78).
David Arroyo [@davarresc](https://github.com/davarresc).
