# Structured data user guide

This is an API service for structured data management and maintenance. Written in PHP as a package for Laravel framework.

This document provides the installation process information and the API usage.

## Table of contents

- [Installation procedure](#installation-procedure)
  * [Troubleshooting the Installation Error]
- [Basic usage example](#basic-usage-example)
- [API endpoints specification](#api-endpoints-specification)
  * [Schemas importation](#schemas-importation)
  * [Schema operations](#schema-operations)
    + [Retrieve a specific schema](#retrieve-a-specific-schema)
      **[Adjusting the API Endpoint Path]
    + [Schema creation](#schema-creation)
    + [Updating a schema](#updating-a-schema)
    + [Schema deletion](#schema-deletion)
  * [Schema properties management](#schema-properties-management)
    + [Property information](#property-information)
    + [Create a new property](#create-a-new-property)
    + [Specified  property attributes for a schema](#specified-property-attributes-for-a-schema)
    + [Updating a schema property](#updating-a-schema-property)
  * [Property types manipulation](#property-types-manipulation)
    + [Type creation](#type-creation)
    + [Property types update](#property-types-update)
  * [Items management](#items-management)
    + [Using item show parameter](#using-item-show-parameter)
    + [Exporting formats using the format parameter](#exporting-formats-using-the-format-parameter)
      - [Export item values to RDF/XML](#export-item-values-to-rdf-xml)
      - [Export item values to *neo4j cypher* script](#export-item-values-to-neo4j-cypher-script)
  * [Items manipulation](#items-manipulation)
    + [Item creation](#item-creation)
    + [Item update](#item-update)
    + [Item deletion](#item-deletion)
- [Contributors](#contributors)

## Installation procedure

First you need an instance of Laravel project in order to use this package. It can be downloaded from the Github repository located in https://github.com/laravel/laravel.
You can also use [Composer](https://getcomposer.org/download/) to create it via command:

```shell
composer create-project laravel/laravel .
```

Next, run composer to require our extension under your Laravel directory:

```shell
composer require ximdex/structured-data
```

This command installs the package in vendor folder.

Before continuing, you have to configure the database properly in your Laravel project in your .evn file in the root directory. For example:

![database](images/01-database.png)[structured-data-refactor-code-update\vendor\ximdex\structured-data\doc\images](images/01-database.png)

### Troubleshooting the Installation Error

If you encounter an error like:

The next step is the database generation. You need to run the migration process using the command for this purpose:

```shell
php artisan migrate
```

After this operation we have created the database tables in our project with the *strdata* prefix to avoid the previous table names collision.

______________________________________________________________________________________________________________

## Basic usage example
###Requires Postman or similar

At this time we are able to consume the API operations to manage the schemas and items data.

We assume in this manual that the host for our Laravel instance is under a host named localhost and it's being served. Otherwise we would need to add the rest of the folder hierarchy. For example, for a Laravel project in a folder called <i>structured-data</i>:

''Postman

> http://localhost/structured-data/public/api/v1

Assuming the former, then we call the endpoint with this example usage:

''Postman

> [GET] http://localhost/api/v1/schema

This request will retrieve a JSON code with all the schemas that are actually created in our storage. Since it's a brand new installation there wouldn't be any schema. This will be covered later.

______________________________________________________________________________________________________________

## API endpoints specification

Operations over schemas and items data give you a complete control to create or update schemas, generate items of a type of this schemas and associate information to its properties.

______________________________________________________________________________________________________________

### Schemas importation

To import a schema definitions URL provided by schema.org you can run this console command under the Laravel directory:

```shell
php artisan schemas:import https://schema.org/version/latest/schemaorg-current-http.jsonld
```

> This command only supports schemas.org definitions in JSON+LD format.

<!--If the given URL does not contain the schema definitions version you can provide by another argument:-->

Currently, Schema.org just serves the lastest version, but for legacy purposes you can specify the version as another argument:
<!-- php artisan schemas:import http://schema.org/version/3.3/schema.jsonld 3.3 -->

```shell
php artisan schemas:import https://schema.org/version/latest/schemaorg-current-http.jsonld 29.2
```

If no argument is provided, the version will be internally labeled as 'Latest'.

> If there is any schema definition created by a previous importation or by any user, this information will be updated to the new version and only the absent definitions will be marked as obsolete.
> However we can still use this deprecated information later.
 
Also it's possible to perform this import using the following endpoint:

''Postman

> [GET] http://localhost/api/v1/schemas-import?url=https://schema.org/version/latest/schemaorg-current-http.jsonld

> Visit http://schema.org for more details.

______________________________________________________________________________________________________________

### Schema operations

Each schema is a type of item that contains a variable number of properties and can inherit additional properties from a parent schema.

______________________________________________________________________________________________________________

#### Retrieve a specific schema

You can use the schema unique id to load the schema attributes, its properties and the other schemas that are inherited. Usage:

''Postman

> [GET] http://localhost/api/v1/schema/132

** Adjusting the API Endpoint Path

When running the project under a **subdirectory of a web server** (like XAMPP, where the project is under `structured-data-definitivo`), you must include the project path and the `public` folder in the API calls.

The documentation assumes the Laravel project is served directly from the root of the virtual host (`http://localhost`).

| Guide Example URL | Your Project URL Structure |
| :--- | :--- |
| `http://localhost/api/v1/schema/132` | `http://localhost/[your-project-folder]/public/api/v1/schema/132` |

For a project located at `C:\Xampp\htdocs\structured-data-1`, the correct API endpoint to retrieve a specific schema (e.g., schema ID 132) will be:

`http://localhost/structured-data-1/public/api/v1/schema/132`


The result of this request will be a JSON object containing the definition of a schema identified by the number 132, in this example the *Person* schema (UPDATED):

```json
{
    "id": 132,
    "label": "SingleFamilyResidence",
    "comment": "Residence type: Single-family home.",
    "version_id": 1,
    "properties": {
        "0": {
            "id": 159,
            "min_cardinality": 0,
            "max_cardinality": null,
            "order": 1,
            "default_value": null,
            "version_id": 1,
            "label": "occupancy",
            "comment": "The allowed total occupancy for the accommodation in persons (including infants etc). For individual accommodations, this is not necessarily the legal maximum but defines the permitted usage as per the contractual agreement (e.g. a double room used by a single person).\nTypical unit code(s): C62 for person.",
            "schema_label": "SingleFamilyResidence",
            "version_tag": "Latest",
            "types": [
                {
                    "id": 229,
                    "schema_id": 40,
                    "type": "Thing",
                    "version_id": 1,
                    "schema_label": "QuantitativeValue",
                    "version_tag": "Latest"
                }
            ]
        },

      "version_tag": "Latest",
            "schemas": [
                {
                    "id": 909,
                    "label": "Accommodation",
                    "comment": "An accommodation is a place that can accommodate human beings, e.g. a hotel room, a camping pitch, or a meeting room. Many accommodations are for overnight stays, but this is not a mandatory requirement.\nFor more specific types of accommodations not defined in schema.org, one can use [[additionalType]] with external vocabularies.\n\nSee also the dedicated document on the use of schema.org for marking up hotels and other forms of accommodations.",
                    "version_id": 1,
                    "version_tag": "Latest",
                    "schemas": [
                        {
                            "id": 258,
                            "label": "Place",
                            "comment": "Entities that have a somewhat fixed, physical extension.",
                            "version_id": 1,
                            "version_tag": "Latest",
                            "schemas": [
                                {
                                    "id": 58,
                                    "label": "Thing",
                                    "comment": "The most generic type of item.",
                                    "version_id": 1,
                                    "version_tag": "Latest",
                                    "schemas": []
                                }
                            ]
          
```

> Note: the properties are simplified to just 1 to serve as example due to the bigger amount of them available.

We can see all the properties and their available types for the *SingleFamilyResidence* type and their inherited schemas listed in the "schemas" node. In this example, some of the properties come from the *Place* schema.

______________________________________________________________________________________________________________

#### Schema creation

New schemas can be created with a given label name and an optional comment. If this name is already in use, the operation will be canceled. 

If the schema extends the attributes of other schemas, you can use the optional parameter `schemas` to send a list of unique ID schemas and their priority. This last argument is optional.

**API Endpoint Adaptation**

When running the project under a **web server subdirectory** (like XAMPP, where the project is in `structured-data-1`), you must include the project path and the `public` folder in your API calls.

| Guide Example URL | Your Project URL Structure |
| :--- | :--- |

''Postman

| `[POST] http://localhost/api/v1/schema` | `[POST] http://localhost/[your-project-folder]/public/api/v1/schema` |

For a project located at `C:\Xampp\htdocs\structured-data-1`, the correct endpoint to create a new schema will be:

''Postman

`[POST] http://localhost/structured-data-1/public/api/v1/schema`

**JSON Payload Example (Adapted to your import):**

In this adapted example, we create the **`LuxuryResidence`** schema, which extends the schema with ID **132** (which, after the latest import, is now `SingleFamilyResidence`):

```json
{
    "label": "LuxuryResidence",
    "comment": "A single-family residence with premium features.",
    "schemas": [
        {
            "id": 132,
            "priority": 1
        }
    ]
}

The result of the successful operation will be the created schema containing the new unique ID assigned (e.g., 920):

'''JSON

{
    "label": "LuxuryResidence",
    "comment": "A single-family residence with premium features.",
    "id": 920,
    "version_tag": null
}
User schemas do not have a version (null value instead), so they are always ready to be used in future imported versions.

This is an example of a response error that happens when you try to create a schema with a name already in use:

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

Another common error happens when the label contains anything else but letters. The label can't contain spaces either, like for example "Club Member". The returned error looks like this:

```json
{
    "message": "The label field must only contain letters.",
    "errors": {
        "label": [
            "The label field must only contain letters."
        ]
    }
}
```

This is the common format for any error in a management operation. Note that you can have multiple errors with different POST fields.

______________________________________________________________________________________________________________


#### Updating a schema
Similar to the creation of a new schema, the only difference is the inclusion of the unique id of the schema to edit in the request operation, and the usage of either the PUT or PATCH method instead of POST.

**API Endpoint Adaptation**

When running the project under a subdirectory, you must include the project path and the `public` folder in your API calls.

For a project located at `C:\Xampp\htdocs\[your-project-folder]`, the correct endpoint to update the schema with ID **920** (which is now **HotelRoom**) is:

''Postman

`[PATCH] http://localhost/[your-project-folder]/public/api/v1/schema/920`

**1. Updating the Schema Comment**

We use **PATCH** to update the comment field:

```json
{
    "comment": "Una residencia de lujo con caracteristicas premium (Actualizada)"
}
The successful result (after the import assigned ID 920 to HotelRoom) will be:

JSON

{
    "id": 920,
    "label": "HotelRoom",
    "comment": "Una residencia de lujo con caracteristicas premium (Actualizada)",
    "version_id": 1,
    "version_tag": "Latest",
    "schemas": [
        {
            "id": 692,
            "label": "Room",
            "comment": "A room is a distinguishable space within a structure...",
            "version_id": 1,
            "version_tag": "Latest"
        }
    ]
}

2. Changing Schema Inheritance

It's possible to send a list of schemas to change the properties schema inheritance. This argument is optional, but if it's used, the relationship with any schemas previously associated will be removed and replaced with the given ones.

For example, we update the schema with ID 920 to extend schema ID 133:

''Postman

`[PATCH] http://localhost/[your-project-folder]/public/api/v1/schema/920`

JSON

{
    "schemas": [
        {
            "id": 133,
            "priority": 1
        }
    ]
}

The successful result shows the inheritance replaced. Our schema HotelRoom (ID 920) now extends DownloadAction (ID 133):

JSON

{
    "id": 920,
    "label": "HotelRoom",
    "comment": "Una residencia de lujo con caracteristicas premium (Actualizada)",
    "version_id": 1,
    "version_tag": "Latest",
    "schemas": [
        {
            "id": 133,
            "label": "DownloadAction",
            "comment": "The act of downloading an object.",
            "version_id": 1,
            "version_tag": "Latest"
        }
    ]
}

Now our schema with unique id 920 extends the DownloadAction schema with priority 1.

______________________________________________________________________________________________________________

#### Schema deletion

**API Endpoint Adaptation**

When running the project under a subdirectory, you must include the project path and the `public` folder in your API calls.

For a project located at `C:\Xampp\htdocs\[your-project-folder]`, the correct endpoint to delete the schema with ID **920** (which was the custom-created schema, now **HotelRoom**) is:

''Postman

`[DELETE] http://localhost/[your-project-folder]/public/api/v1/schema/920`

If the schema is already in use by any item, the operation will be denied.

**Successful Result**

A successful deletion operation often returns an **HTTP 204 No Content** status code. However, depending on the internal framework configuration, it may return a simple success indicator.

* If the operation is successful, the API might return a simple text body containing **`1`** (representing boolean `true` in PHP/Laravel).
* To confirm the deletion, performing a subsequent **`GET`** request on the same URL (`.../schema/920`) should now return an **HTTP 404 Not Found** error.

______________________________________________________________________________________________________________

### Schema properties management

We support some operations to manage the properties in use for the schemas.

______________________________________________________________________________________________________________

#### Property information
All the details from a property in a schema can be modified. 

______________________________________________________________________________________________________________

### Create a new property
You can associate a property to a schema using this function. To do this, it's necessary to provide the schema unique id and the property unique id in order to create the relationship. Also, the type or list of types available must be present in this request to define what kind of type this property will support in this schema.

**API Endpoint Adaptation

The endpoint to create a property relationship is [POST] /api/v1/property-schema.

For a project located at http://localhost/[your-project-folder], the correct URL is:

''Postman

[POST] http://localhost/[your-project-folder]/public/api/v1/property-schema

The available types can be a simple type from the following list:

Boolean

Date

DateTime

Time

Number

Text

Or a type Thing including a schema id to support an item as value for this type. The example above shows that the Organization schema will support items of type Person for the property employee.

Sometimes we want to create a new property instead of using an existing one. It is possible to send a property name (label) and an optional comment to create both the property and the relationship to the specified schema.

Example of usage (Creating the checksum property):

We will create the checksum property and assign it to the DownloadAction schema (ID 133 from our import).

JSON

{
    "schema_id": 133,
    "label": "checksum",
    "comment": "Integrity hash value of the file.",
    "types": [
        {"type": "Text"}
    ]
}
Successful Creation Result

The operation returns the newly created property/relationship with a unique ID (e.g., 2185). This ID is used for subsequent management operations.

JSON

{
    "id": 2185,
    "min_cardinality": 0,
    "max_cardinality": null,
    "order": 1,
    "default_value": null,
    "version_id": null,
    "label": "checksum",
    "comment": null, 
    "schema_label": "DownloadAction",
    "version_tag": null,
    "types": [
        {
            "id": 3158,
            "schema_id": null,
            "type": "Text",
            "version_id": null,
            "schema_label": null,
            "version_tag": null
        }
    ]
}
Update a property
Similar to the creation of a new schema, the only difference is the inclusion of the unique id of the property/relationship to edit in the operation, and the usage of the PUT or PATCH method instead of POST.

IMPORTANT API ROUTE CORRECTION: The correct endpoint to update relationship parameters (like cardinalities or types) is /api/v1/property-schema/{id}, using the relationship ID (e.g., 2185). Attempting to update descriptive text fields (like comment or label) via this endpoint will be ignored.

''Postman

[PATCH] http://localhost/[your-project-folder]/public/api/v1/property-schema/2185

JSON Payload Example (Updating Cardinality):

We update the property checksum (ID 2185) to be mandatory and unique by setting the minimum and maximum cardinality to 1.

JSON

{
    "min_cardinality": 1,
    "max_cardinality": 1
}
Successful Update Result

The API returns the updated property/relationship definition, confirming the change in cardinalities:

JSON

{
    "id": 2185,
    "min_cardinality": 1,
    "max_cardinality": 1,
    "order": 1,
    "default_value": null,
    "version_id": null,
    "label": "checksum",
    "comment": null,
    "schema_label": "DownloadAction",
    "version_tag": null,
    "types": [
        {
            "id": 3158,
            "schema_id": null,
            "type": "Text",
            "version_id": null,
            "schema_label": null,
            "version_tag": null
        }
    ]
}

______________________________________________________________________________________________________________

#### Specified property attributes for a schema

A property in a schema can support some kind of restrictions and other features.

These are optional parameters in the request as listed:

* **min_cardinality**: Specifies that there must be a number of values in this property equal to or greater than the specified number for the associated schema. By default there is not a minimum quantity.

* **max_cardinality**: The property in this schema does not support a number of values greater than the given one. By default there is not limit.

* **default_value**: If a value is created with empty content in this property, this default value will be use. By default values cannot be empty.

* **order**: The order of the property in this schema. For presentation purposes.

Here is an example of these attributes:

''Postman

[POST] http://localhost/[your-project-folder]/public/api/v1/property-schema

JSON

{
    "schema_id": 133,
    "label": "checksum",
    "types": [
        {"type": "Text"}
    ],
    "min_cardinality": 1,
    "max_cardinality": 1,
    "default_value": "SHA-256",
    "order": 1
}
```

Now, this property will support only values in text mode, at least one value and a maximum of one value defined for an item. 
The property will have an order of 1, and any value created with empty content will be defaulted to "SHA-256".

______________________________________________________________________________________________________________

#### Updating a schema property

To update the property attributes already defined in a schema, you must include the unique id of the schema-property relationship in the request.

**IMPORTANT API ROUTE CORRECTION:** The correct endpoint to update the relationship parameters (like types and cardinalities) is **`/api/v1/property-schema/{id}`**.

Use the endpoint with the PUT or PATCH method like this example:

''Postman

> `[PUT] http://localhost/[your-project-folder]/public/api/v1/property-schema/2185`

In this example, we use **PUT** to completely redefine the attributes of our **checksum** property (ID **2185**). Note that **PUT** typically requires all fields, even if you are only changing one.

```json
{
    "comment": "Default checksum value updated",
    "min_cardinality": 0,
    "max_cardinality": 0,
    "default_value": "0x0",
    "order": 5
}
Every attribute is optional. Use zero value to reset the cardinality restrictions to their default values.

Also you can provide a list of available types to change the supported values in this property. For example, using PATCH to only change the types:

''Postman

[PATCH] http://localhost/[your-project-folder]/public/api/v1/property-schema/2185

The successful operation that replaced the old type (Text) with Number was:

JSON

{
    "types": [
        {"type": "Number"}
    ]
}

Remember that the old types will be deleted.
______________________________________________________________________________________________________________

### Property types manipulation

Often, we need to change a type or add a new one to a property in a schema. If you don't want to specify all the types in the property update operation, to change the desired ones, it's possible to add or update separately.

______________________________________________________________________________________________________________

#### Type creation

You need to provide the unique id for the relationship between the schema and the property (property_schema_id), and the type to create:

''Postman

> [POST] http://localhost/[your-project-folder]/public/api/v1/available-types
```json
{
    "property_schema_id": 159, 
    "type": "Date"
}
```

At this time we have a new type supporting values of Date type.

This operation will return a JSON object with the property containing the new type:

```json
{
    "id": 159,
    "min_cardinality": 0,
    "max_cardinality": null,
    "order": 1,
    "default_value": null,
    "version_id": 1,
    "label": "occupancy",
    "comment": "The allowed total occupancy for the accommodation in persons (including infants etc). For individual accommodations, this is not necessarily the legal maximum but defines the permitted usage as per the contractual agreement (e.g. a double room used by a single person).\nTypical unit code(s): C62 for person.",
    "schema_label": "SingleFamilyResidence",
    "version_tag": "Latest",
    "types": [
        {
            "id": 3161,
            "schema_id": null,
            "type": "Date",
            "version_id": null,
            "schema_label": null,
            "version_tag": null
        },
        {
            "id": 229,
            "schema_id": 40,
            "type": "Thing",
            "version_id": 1,
            "schema_label": "QuantitativeValue",
            "version_tag": "Latest"
        }
    ]
}
```

______________________________________________________________________________________________________________

#### Property types update

To update the type attributes is necessary to include the unique type id.

For example, we want to change the previous created type from *Date* to *Person* schema, in order to allow items of this type as this *employee* property.

To do this it is necessary to provide the unique id for the *Person* schema (132) and specify the *Thing* for the type field:

''Postman

> [PATCH] http://localhost/[your-project-folder]/public/api/v1/available-types/3161
```json
{
    "type": "Thing",
    "schema_id": 145
}
```

Now the updated type support items of type Person schema looks like this:

```json
{
    "id": 159,
    "min_cardinality": 0,
    "max_cardinality": null,
    "order": 1,
    "default_value": null,
    "label": "occupancy",
    "schema_label": "SingleFamilyResidence",
    "types": [
        {
            "id": 3161,
            "schema_id": 145,
            "type": "Thing",
            "schema_label": "Person"
        },
        {
            "id": 229,
            "schema_id": 40,
            "type": "Thing",
            "schema_label": "QuantitativeValue"
        }
    ]
}
```

______________________________________________________________________________________________________________

### Items management

An item is a group of values for an schema type. These values will be grouped in the schema properties according to the schema specifications, like maximum and minimum number of values (cardinality) and supporting types in the properties available types.

++Items manipulation
The values of an item can be created or updated through the item management endpoint.

Basically the request will include the unique item ID in the update operation, or the unique schema ID in creation process.

The rest of the required data is the values for the properties to create the content of the item.

++Item creation
If you want to create a new item based on the SingleFamilyResidence schema (ID 132) with a minimal amount of data (using the valid properties permittedUsage and numberOfBedrooms confirmed for this schema), the request will be the following:

''Postman

[POST] http://localhost/[your-project-folder]/api/v1/item

JSON

{
    "schema_id": "132",
    "properties": {
        "permittedUsage": {
            "type": 21,
            "values": [
                "The Luxury Casa"
            ]
        },
        "numberOfBedrooms": {
            "type": 1169,
            "values": [
                4
            ]
        }
    }
}

Note that with each property we will provide the available type ID for the one supported by such property in the SingleFamilyResidence schema, and the values for this type are always returned as an array, even if the element can only contain a simple value.

Remember that you can obtain information about these types by retrieving the entire schema properties specification. For example, in this case:

''Postman

[GET] http://localhost/[your-project-folder]/api/v1/schema/132

If successful, the result of this operation will return the new item created:

JSON

{
    "schema_id": "132",
    "id": 1,
    "schema_url": "http://localhost/[your-project-folder]/api/v1/item/1",
    "schema_label": "SingleFamilyResidence",
    "values": [
        {
            "item_id": 1,
            "available_type_id": 21,
            "value": "The Luxury Casa",
            "ref_item_id": null,
            "position": 1,
            "id": 1
        },
        {
            "item_id": 1,
            "available_type_id": 1169,
            "value": 4,
            "ref_item_id": null,
            "position": 1,
            "id": 2
        }
    ]
}

You can retrieve the values of an item for its unique item id, for example:

''Postman

> [GET] http://localhost/[your-project-folder]/public/api/v1/item/1

The response will be a JSON+LD object for the item with unique id 1, a type *Person*:

```json
{
    "@context": "http://schema.org",
    "@type": "SingleFamilyResidence",
    "@id": "http://localhost/structured-data-definitivo/public/api/v1/item/1",
    "numberOfBedrooms": "4",
    "permittedUsage": "The Luxury Casa"
}
```
______________________________________________________________________________________________________________

#### Using item show parameter

This argument can be used to show extra information about the specified item. It can contain a list of values separated by commas. 

Usage:

''Postman

> [GET] http://localhost//api/v1/item/{id}?show=deprecated,uid

This is the list of values:

* **uid**: show the unique id values for each element in the item.

* **deprecated**: It's possible to retrieve the deprecated definitions like old version properties or types for this schema, using the *deprecated* value.

* **version**: show the version unique id.

For example:

''Postman

> [GET]  http://localhost/[your-project-folder]/public/api/v1/item/1?show=deprecated,uid,version

This request retrieves the same information about the item values, adding the unique id for each value as *@uid* property, the item unique id as *@item*, and the version of the schema import as *@version*.

```json
{
    "@context": "http://schema.org",
    "@item": 1,
    "@version": 1,
    "@uid": 132,
    "@type": "SingleFamilyResidence",
    "@id": "http://localhost/structured-data-definitivo/public/api/v1/item/1",
    "permittedUsage": {
        "@uid": 1,
        "@version": 1,
        "@value": "The Luxury Casa"
    },
    "numberOfBedrooms": {
        "@uid": 2,
        "@version": 1,
        "@value": "4"
    }
}
```

Note that any property value for a simple type is specified in the *@value* attribute.

______________________________________________________________________________________________________________

#### Exporting formats using the format parameter

It's possible to express the item data in other language expressions with the format argument.

______________________________________________________________________________________________________________

##### Export item values to RDF XML

Show the item values in RDF/XML syntax.

For example, the item defined as type Person will be exported as this syntax:

''Postman

> [GET] http://localhost/[your-project-folder]/api/v1/item/1?format=rdf

The result of this request operation will be:

```xml
<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:schema="http://schema.org/">
    <schema:SingleFamilyResidence rdf:about="http://localhost/structured-data-definitivo/public/api/v1/item/1">
        <schema:numberOfBedrooms rdf:datatype="http://www.w3.org/2001/XMLSchema#string">4</schema:numberOfBedrooms>
        <schema:permittedUsage rdf:datatype="http://www.w3.org/2001/XMLSchema#string">The Luxury Casa</schema:permittedUsage>
    </schema:SingleFamilyResidence>
</rdf:RDF>
```

______________________________________________________________________________________________________________

##### Export item values to *neo4j cypher* script

If you want to import an item and its relationships into a neo4j graph, this option will be helpfully to do this.
This format generates a list of commands in *cypher* language that can be imported in a neo4j project.

''Postman

> [GET] http://localhost/[your-project-folder]/api/v1/item/1?format=neo4j

The result of this request operation will be:

```
MERGE (singleFamilyResidence1:SingleFamilyResidence {id:1})
SET singleFamilyResidence1.permittedUsage = ['The Luxury Casa']
SET singleFamilyResidence1.numberOfBedrooms = ['4']
RETURN singleFamilyResidence1
```

This result in a creation of one node SingleFamilyResidence (sfr1) with the given attributes.

![A nodes relation graph in neo4j](https://raw.githubusercontent.com/XIMDEX/structured-data/master/doc/images/nodes-neo4j.png)

______________________________________________________________________________________________________________

### Items manipulation

The values of an item can be created or updated through the item management endpoint.

Basically the request will include the unique item id in the update operation, or the unique schema id in creation process.

The rest of the required data is the values for the properties to create the content of the item.

______________________________________________________________________________________________________________

#### Item creation

f you want to create a new item based on the SingleFamilyResidence schema (ID 132) with a minimal amount of data (permittedUsage and numberOfBedrooms), the request will be the following:

''Postman

> [POST] http://localhost/[your-project-folder]/api/v1/item
```json
{
    "schema_id": "132",
    "properties": {
        "permittedUsage": { 
            "type": 21, 
            "values": [
                "The Luxury Casa"
            ]
        },
        "numberOfBedrooms": { 
            "type": 1169, 
            "values": [
                4
            ]
        }
    }
}
```

Note that with each property we will provide the available type ID for the one supported by such property in the SingleFamilyResidence schema, and the values for this type are always returned as an array, even if the element can only contain a simple value.

> Remember that you can obtain information about these types retrieving the entire schema properties specification. For example, in this case:
>

''Postman

> [GET] http://localhost/[your-project-folder]/api/v1/schema/132

If successful, the result of this operation will return the new item created:

```json
{
    "schema_id": "132",
    "id": 2,
    "schema_url": "http://localhost/structured-data-definitivo/public/api/v1/item/2",
    "schema_label": "SingleFamilyResidence",
    "values": [
        {
            "available_type_id": 21,
            "value": "The Luxury Casa",
            "ref_item_id": null,
            "position": 1,
            "id": 3
        },
        {
            "available_type_id": 1169,
            "value": 4,
            "ref_item_id": null,
            "position": 1,
            "id": 4
        }
    ]
}
```

______________________________________________________________________________________________________________

#### Item update

Now, we have an item named 'Open Ximdex' of type *Corporation*. We need to **update** the contact email and add some extra information, like two employees and the country address.

The example above shows how this can be done:

''Postman

> [PATCH] http://localhost/[your-project-folder]/api/v1/item/2
```json
{
    "properties": {
        "permittedUsage": {
            "type": 21, 
            "values": ["The Modern Luxury Casa"] 
        },
        "yearBuilt": {
            "type": 1620, // Type ID for 'Number' in yearBuilt property
            "values": [2018] // New property value added
        }
    }
}
```

Remember that if you don't specify the previous value id, a new email will be added to the email property.

> Use the show argument to know the unique id corresponding to the values of the item to update or delete:

''Postman

> [GET] http://localhost/api/[your-project-folder]/v1/item/2?show=uid

So now we have a *Corporation* item with a relationship with many *Person* items through the *employee* property:

```json
{
    "@context": "http://schema.org",
    "@type": "SingleFamilyResidence",
    "@id": "http://localhost/structured-data-definitivo/public/api/v1/item/2",
    "permittedUsage": "The Modern Luxury Casa",
    "numberOfBedrooms": 4,
    "yearBuilt": 2018
}
```

If you want to delete a value from this item, for example, the employee called David, we can use the delete argument to remove only this value:

''Postman

> [PATCH] http://localhost/[your-project-folder]/api/v1/item/2
```json
{
    "delete": [6]
}
```

Many property values can be removed specifying a list of unique values id in this attribute, and they can be sent with other properties.

An entire set of property values can be removed specifying the *delete* attribute instead of the unique id list. 

For example, if we want to remove all of the employees for our item previously created, we will use the argument *delete* in the corresponding item property:

> [PATCH] http://localhost/[your-project-folder]/api/v1/item/2
```json
{
    "properties": {
        "permittedUsage": {"type": 21, "values": [], "delete": true}
    }
}
```

Only the values type *Person* (2407) will be removed in the *employee* property.

> If you send any value in the *values* attribute, then these values will replace the previous ones.

______________________________________________________________________________________________________________

#### Item deletion

All data for an item can be removed using its unique id. The possible related items will not be deleted, but the relationships with other items will disappear.

Usage:

''Postman

> [DELETE] http://localhost/[your-project-folder]/api/v1/item/2

*********Do this carefully, this operation cannot be undone.

______________________________________________________________________________________________________________

## Contributors

* Antonio Jesús Lucena [@ajlucena78](https://github.com/ajlucena78).
* David Arroyo [@davarresc](https://github.com/davarresc).
* Daniel Domínguez [@daniel423615](https://github.com/daniel423615).
* Fernando Quintero Gómez [@Quintero4] (https://github.com/Quintero4)