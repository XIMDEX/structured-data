# Guía de usuario de datos estructurados

Este es un servicio API para la gestión y mantenimiento de datos estructurados. Escrito en **PHP** como un paquete para el *framework* **Laravel**.

Este documento proporciona la información del proceso de instalación y el uso de la API.

## Tabla de contenidos

- [Procedimiento de instalación](#installation-procedure)
  * [Solución de problemas en el error de instalación]
- [Ejemplo de uso básico](#basic-usage-example)
- [Especificación de puntos finales (endpoints) de la API](#api-endpoints-specification)
  * [Importación de esquemas](#schemas-importation)
  * [Operaciones de esquema](#schema-operations)
    + [Recuperar un esquema específico](#retrieve-a-specific-schema)
      **[Ajustando la ruta del punto final de la API]
    + [Creación de esquema](#schema-creation)
    + [Actualización de un esquema](#updating-a-schema)
    + [Eliminación de esquema](#schema-deletion)
  * [Gestión de propiedades de esquema](#schema-properties-management)
    + [Información de propiedad](#property-information)
    + [Crear una nueva propiedad](#create-a-new-property)
    + [Atributos de propiedad especificados para un esquema](#specified-property-attributes-for-a-schema)
    + [Actualización de una propiedad de esquema](#updating-a-schema-property)
  * [Manipulación de tipos de propiedad](#property-types-manipulation)
    + [Creación de tipo](#type-creation)
    + [Actualización de tipos de propiedad](#property-types-update)
  * [Gestión de ítems](#items-management)
    + [Usando el parámetro de mostrar ítem (item show)](#using-item-show-parameter)
    + [Exportación de formatos usando el parámetro de formato](#exporting-formats-using-the-format-parameter)
      - [Exportar valores de ítem a RDF/XML](#export-item-values-to-rdf-xml)
      - [Exportar valores de ítem a script *neo4j cypher*](#export-item-values-to-neo4j-cypher-script)
  * [Manipulación de ítems](#items-manipulation)
    + [Creación de ítem](#item-creation)
    + [Actualización de ítem](#item-update)
    + [Eliminación de ítem](#item-deletion)
- [Colaboradores](#contributors)

## Procedimiento de instalación

Primero necesitas una instancia de proyecto Laravel para poder usar este paquete. Se puede descargar desde el repositorio de Github ubicado en https://github.com/laravel/laravel.
También puedes usar [Composer](https://getcomposer.org/download/) para crearlo mediante el comando:

```shell
composer create-project laravel/laravel .
A continuación, ejecuta composer para requerir nuestra extensión en tu directorio Laravel:

Shell

composer require ximdex/structured-data
Este comando instala el paquete en la carpeta vendor.

Antes de continuar, debes configurar la base de datos correctamente en tu proyecto Laravel en tu archivo .env en el directorio raíz. Por ejemplo:

structured-data-refactor-code-update\vendor\ximdex\structured-data\doc\images

Solución de problemas en el error de instalación
Si encuentras un error como:

El siguiente paso es la generación de la base de datos. Debes ejecutar el proceso de migración usando el comando para este propósito:

Shell

php artisan migrate
Después de esta operación, hemos creado las tablas de la base de datos en nuestro proyecto con el prefijo strdata para evitar la colisión de nombres de tablas anteriores.

Ejemplo de uso básico
Requiere Postman o similar
En este momento podemos consumir las operaciones de la API para gestionar los esquemas y los datos de los ítems.

Asumimos en este manual que el host para nuestra instancia de Laravel está bajo un host llamado localhost y se está sirviendo. De lo contrario, necesitaríamos agregar el resto de la jerarquía de carpetas. Por ejemplo, para un proyecto Laravel en una carpeta llamada structured-data:

''Postman

http://localhost/structured-data/public/api/v1

Asumiendo lo anterior, entonces llamamos al endpoint con este ejemplo de uso:

''Postman

[GET] http://localhost/api/v1/schema

Esta solicitud recuperará un código JSON con todos los esquemas que están realmente creados en nuestro almacenamiento. Dado que es una instalación nueva, no habría ningún esquema. Esto se cubrirá más adelante.

Especificación de puntos finales (endpoints) de la API
Las operaciones sobre esquemas y datos de ítems te dan un control completo para crear o actualizar esquemas, generar ítems de un tipo de estos esquemas y asociar información a sus propiedades.

Importación de esquemas
Para importar una URL de definiciones de esquema proporcionada por schema.org puedes ejecutar este comando de consola en el directorio de Laravel:

Shell

php artisan schemas:import [https://schema.org/version/latest/schemaorg-current-http.jsonld](https://schema.org/version/latest/schemaorg-current-http.jsonld)
Este comando solo es compatible con las definiciones de schemas.org en formato JSON+LD.

Actualmente, Schema.org solo sirve la última versión, pero por motivos de legacy puedes especificar la versión como otro argumento:

Shell

php artisan schemas:import [https://schema.org/version/latest/schemaorg-current-http.jsonld](https://schema.org/version/latest/schemaorg-current-http.jsonld) 29.2
Si no se proporciona ningún argumento, la versión se etiquetará internamente como 'Latest'.

Si hay alguna definición de esquema creada por una importación anterior o por cualquier usuario, esta información se actualizará a la nueva versión y solo las definiciones ausentes se marcarán como obsoletas. Sin embargo, todavía podemos usar esta información desaprobada más tarde.

También es posible realizar esta importación utilizando el siguiente endpoint:

''Postman

[GET] http://localhost/api/v1/schemas-import?url=https://schema.org/version/latest/schemaorg-current-http.jsonld

Visita http://schema.org para más detalles.

Operaciones de esquema
Cada esquema es un tipo de ítem que contiene un número variable de propiedades y puede heredar propiedades adicionales de un esquema padre.

Recuperar un esquema específico
Puedes usar el ID único del esquema para cargar los atributos del esquema, sus propiedades y los otros esquemas que son heredados. Uso:

''Postman

[GET] http://localhost/api/v1/schema/132

** Ajustando la ruta del punto final de la API

Cuando se ejecuta el proyecto bajo un subdirectorio de un servidor web (como XAMPP, donde el proyecto está bajo structured-data-definitivo), debes incluir la ruta del proyecto y la carpeta public en las llamadas a la API.

La documentación asume que el proyecto Laravel se sirve directamente desde la raíz del host virtual (http://localhost).

URL de ejemplo de la Guía	Estructura de URL de tu Proyecto
http://localhost/api/v1/schema/132	http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/132

Exportar a Hojas de cálculo

Para un proyecto ubicado en C:\Xampp\htdocs\structured-data-1, el endpoint de API correcto para recuperar un esquema específico (por ejemplo, ID de esquema 132) será:

http://localhost/structured-data-1/public/api/v1/schema/132

El resultado de esta solicitud será un objeto JSON que contiene la definición de un esquema identificado por el número 132, en este ejemplo el esquema Person (ACTUALIZADO):

JSON

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
          
Nota: las propiedades se simplifican a solo 1 para servir como ejemplo debido a la mayor cantidad de ellas disponibles.

Podemos ver todas las propiedades y sus tipos disponibles para el tipo SingleFamilyResidence y sus esquemas heredados listados en el nodo "schemas". En este ejemplo, algunas de las propiedades provienen del esquema Place.

Creación de esquema
Se pueden crear nuevos esquemas con un nombre de etiqueta (label) dado y un comentario opcional. Si este nombre ya está en uso, la operación se cancelará.

Si el esquema extiende los atributos de otros esquemas, puedes usar el parámetro opcional schemas para enviar una lista de IDs únicos de esquemas y su prioridad. Este último argumento es opcional.

Adaptación del punto final de la API

Cuando se ejecuta el proyecto bajo un subdirectorio de un servidor web (como XAMPP, donde el proyecto está en structured-data-1), debes incluir la ruta del proyecto y la carpeta public en tus llamadas a la API.

URL de ejemplo de la Guía	Estructura de URL de tu Proyecto

Exportar a Hojas de cálculo

''Postman

| [POST] http://localhost/api/v1/schema | [POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema |

Para un proyecto ubicado en C:\Xampp\htdocs\structured-data-1, el endpoint correcto para crear un nuevo esquema será:

''Postman

[POST] http://localhost/structured-data-1/public/api/v1/schema

Ejemplo de Carga Útil (Payload) JSON (Adaptado a tu importación):

En este ejemplo adaptado, creamos el esquema LuxuryResidence, que extiende el esquema con ID 132 (que, después de la última importación, ahora es SingleFamilyResidence):

JSON

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

El resultado de la operación exitosa será el esquema creado que contiene el nuevo ID único asignado (por ejemplo, 920):

'''JSON

{
    "label": "LuxuryResidence",
    "comment": "A single-family residence with premium features.",
    "id": 920,
    "version_tag": null
}
Los esquemas de usuario no tienen una versión (valor *null* en su lugar), por lo que siempre están listos para ser utilizados en futuras versiones importadas.

Este es un ejemplo de un error de respuesta que ocurre cuando intentas crear un esquema con un nombre ya en uso:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "label": [
            "The label has already been taken."
        ]
    }
}
Otro error común ocurre cuando la etiqueta contiene algo más que letras. La etiqueta tampoco puede contener espacios, como por ejemplo "Club Member". El error devuelto se ve así:

JSON

{
    "message": "The label field must only contain letters.",
    "errors": {
        "label": [
            "The label field must only contain letters."
        ]
    }
}
Este es el formato común para cualquier error en una operación de gestión. Ten en cuenta que puedes tener múltiples errores con diferentes campos POST.

Actualización de un esquema
Similar a la creación de un nuevo esquema, la única diferencia es la inclusión del ID único del esquema a editar en la operación de solicitud, y el uso del método PUT o PATCH en lugar de POST.

Adaptación del punto final de la API

Cuando se ejecuta el proyecto bajo un subdirectorio, debes incluir la ruta del proyecto y la carpeta public en tus llamadas a la API.

Para un proyecto ubicado en C:\Xampp\htdocs\[tu-carpeta-de-proyecto], el endpoint correcto para actualizar el esquema con ID 920 (que ahora es HotelRoom) es:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/920

1. Actualización del comentario del esquema

Usamos PATCH para actualizar el campo comment:

JSON

{
    "comment": "Una residencia de lujo con caracteristicas premium (Actualizada)"
}
El resultado exitoso (después de que la importación asignara el ID 920 a HotelRoom) será:

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

2. Cambio de herencia de esquema

Es posible enviar una lista de esquemas para cambiar la herencia de la propiedad del esquema. Este argumento es opcional, pero si se usa, la relación con cualquier esquema asociado previamente se eliminará y se reemplazará con los dados.

Por ejemplo, actualizamos el esquema con ID 920 para que extienda el esquema ID 133:

''Postman

`[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/920`

JSON

{
    "schemas": [
        {
            "id": 133,
            "priority": 1
        }
    ]
}

El resultado exitoso muestra la herencia reemplazada. Nuestro esquema HotelRoom (ID 920) ahora extiende DownloadAction (ID 133):

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

Ahora nuestro esquema con ID único 920 extiende el esquema DownloadAction con prioridad 1.

______________________________________________________________________________________________________________

#### Eliminación de esquema

**Adaptación del punto final de la API**

Cuando se ejecuta el proyecto bajo un subdirectorio, debes incluir la ruta del proyecto y la carpeta `public` en tus llamadas a la API.

Para un proyecto ubicado en `C:\Xampp\htdocs\[tu-carpeta-de-proyecto]`, el *endpoint* correcto para eliminar el esquema con ID **920** (que era el esquema creado por el usuario, ahora **HotelRoom**) es:

''Postman

`[DELETE] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/920`

Si el esquema ya está en uso por algún ítem, la operación será denegada.

**Resultado exitoso**

Una operación de eliminación exitosa a menudo devuelve un código de estado **HTTP 204 No Content**. Sin embargo, dependiendo de la configuración interna del *framework*, puede devolver un simple indicador de éxito.

* Si la operación es exitosa, la API podría devolver un cuerpo de texto simple que contiene **`1`** (que representa *boolean* `true` en PHP/Laravel).
* Para confirmar la eliminación, realizar una solicitud **`GET`** posterior en la misma URL (`.../schema/920`) ahora debería devolver un error **HTTP 404 Not Found**.

______________________________________________________________________________________________________________

### Gestión de propiedades de esquema

Admitimos algunas operaciones para gestionar las propiedades en uso para los esquemas.

______________________________________________________________________________________________________________

#### Información de propiedad
Se pueden modificar todos los detalles de una propiedad en un esquema. 

______________________________________________________________________________________________________________

### Crear una nueva propiedad
Puedes asociar una propiedad a un esquema usando esta función. Para hacer esto, es necesario proporcionar el ID único del esquema y el ID único de la propiedad para crear la relación. Además, el tipo o la lista de tipos disponibles deben estar presentes en esta solicitud para definir qué tipo de valor admitirá esta propiedad en este esquema.

**Adaptación del punto final de la API

El *endpoint* para crear una relación de propiedad es [POST] /api/v1/property-schema.

Para un proyecto ubicado en http://localhost/[tu-carpeta-de-proyecto], la URL correcta es:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema

Los tipos disponibles pueden ser un tipo simple de la siguiente lista:

Boolean

Date

DateTime

Time

Number

Text

O un tipo *Thing* que incluye un ID de esquema para admitir un ítem como valor para este tipo. El ejemplo anterior muestra que el esquema *Organization* admitirá ítems de tipo *Person* para la propiedad *employee*.

A veces queremos crear una nueva propiedad en lugar de usar una existente. Es posible enviar un nombre de propiedad (etiqueta) y un comentario opcional para crear tanto la propiedad como la relación con el esquema especificado.

Ejemplo de uso (Creación de la propiedad *checksum*):

Crearemos la propiedad **checksum** y la asignaremos al esquema **DownloadAction** (ID 133 de nuestra importación).

JSON

{
    "schema_id": 133,
    "label": "checksum",
    "comment": "Integrity hash value of the file.",
    "types": [
        {"type": "Text"}
    ]
}
Resultado de Creación Exitosa

La operación devuelve la propiedad/relación recién creada con un ID único (por ejemplo, 2185). Este ID se utiliza para operaciones de gestión posteriores.

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
Actualizar una propiedad
Similar a la creación de un nuevo esquema, la única diferencia es la inclusión del ID único de la propiedad/relación a editar en la operación, y el uso del método PUT o PATCH en lugar de POST.

CORRECCIÓN IMPORTANTE DE LA RUTA DE LA API: El *endpoint* correcto para actualizar los parámetros de relación (como cardinalidades o tipos) es **/api/v1/property-schema/{id}**, utilizando el ID de relación (por ejemplo, 2185). Intentar actualizar campos de texto descriptivos (como *comment* o *label*) a través de este *endpoint* será ignorado.

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema/2185

Ejemplo de Carga Útil JSON (Actualización de Cardinalidad):

Actualizamos la propiedad *checksum* (ID 2185) para que sea obligatoria y única estableciendo la cardinalidad mínima y máxima en 1.

JSON

{
    "min_cardinality": 1,
    "max_cardinality": 1
}
Resultado de Actualización Exitosa

La API devuelve la definición de propiedad/relación actualizada, confirmando el cambio en las cardinalidades:

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

#### Atributos de propiedad especificados para un esquema

Una propiedad en un esquema puede admitir algún tipo de restricciones y otras características.

Estos son parámetros opcionales en la solicitud, como se enumeran:

* **min_cardinality**: Especifica que debe haber un número de valores en esta propiedad igual o mayor que el número especificado para el esquema asociado. Por defecto no hay una cantidad mínima.

* **max_cardinality**: La propiedad en este esquema no admite un número de valores mayor que el dado. Por defecto no hay límite.

* **default_value**: Si se crea un valor con contenido vacío en esta propiedad, se usará este valor por defecto. Por defecto los valores no pueden estar vacíos.

* **order**: El orden de la propiedad en este esquema. Para fines de presentación.

Aquí hay un ejemplo de estos atributos:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema

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
Ahora, esta propiedad admitirá solo valores en modo texto, al menos un valor y un máximo de un valor definido para un ítem. La propiedad tendrá un orden de 1, y cualquier valor creado con contenido vacío se establecerá por defecto en "SHA-256".

Actualización de una propiedad de esquema
Para actualizar los atributos de propiedad ya definidos en un esquema, debes incluir el ID único de la relación esquema-propiedad en la solicitud.

CORRECCIÓN IMPORTANTE DE LA RUTA DE LA API: El endpoint correcto para actualizar los parámetros de relación (como tipos y cardinalidades) es /api/v1/property-schema/{id}.

Utiliza el endpoint con el método PUT o PATCH como en este ejemplo:

''Postman

[PUT] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema/2185

En este ejemplo, usamos PUT para redefinir completamente los atributos de nuestra propiedad checksum (ID 2185). Ten en cuenta que PUT generalmente requiere todos los campos, incluso si solo estás cambiando uno.

JSON

{
    "comment": "Default checksum value updated",
    "min_cardinality": 0,
    "max_cardinality": 0,
    "default_value": "0x0",
    "order": 5
}
Cada atributo es opcional. Usa el valor cero para restablecer las restricciones de cardinalidad a sus valores por defecto.

También puedes proporcionar una lista de tipos disponibles para cambiar los valores admitidos en esta propiedad. Por ejemplo, usando **PATCH** para cambiar solo los tipos:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema/2185

La operación exitosa que reemplazó el tipo antiguo (Text) con Number fue:

JSON

{
    "types": [
        {"type": "Number"}
    ]
}

Recuerda que los tipos antiguos se eliminarán.
______________________________________________________________________________________________________________

### Manipulación de tipos de propiedad

A menudo, necesitamos cambiar un tipo o agregar uno nuevo a una propiedad en un esquema. Si no quieres especificar todos los tipos en la operación de actualización de la propiedad, para cambiar los deseados, es posible agregarlos o actualizarlos por separado.

______________________________________________________________________________________________________________

#### Creación de tipo

Debes proporcionar el ID único de la relación entre el esquema y la propiedad (*property_schema_id*), y el tipo a crear:

''Postman

> [POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/available-types
```json
{
    "property_schema_id": 159, 
    "type": "Date"
}
En este momento tenemos un nuevo tipo que admite valores de tipo Date.

Esta operación devolverá un objeto JSON con la propiedad que contiene el nuevo tipo:

JSON

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
Actualización de tipos de propiedad
Para actualizar los atributos de tipo es necesario incluir el ID único del tipo.

Por ejemplo, queremos cambiar el tipo creado anteriormente de Date al esquema Person, para permitir ítems de este tipo como esta propiedad employee.

Para hacer esto, es necesario proporcionar el ID único para el esquema Person (132) y especificar Thing para el campo type:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/available-types/3161

JSON

{
    "type": "Thing",
    "schema_id": 145
}
Ahora el tipo actualizado que admite ítems del esquema Person se ve así:

JSON

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
Gestión de ítems
Un ítem es un grupo de valores para un tipo de esquema. Estos valores se agruparán en las propiedades del esquema de acuerdo con las especificaciones del esquema, como el número máximo y mínimo de valores (cardinalidad) y los tipos de apoyo en los tipos disponibles de las propiedades.

++Manipulación de ítems Los valores de un ítem se pueden crear o actualizar a través del endpoint de gestión de ítems.

Básicamente, la solicitud incluirá el ID de ítem único en la operación de actualización, o el ID de esquema único en el proceso de creación.

El resto de los datos requeridos son los valores para las propiedades para crear el contenido del ítem.

++Creación de ítem Si deseas crear un nuevo ítem basado en el esquema SingleFamilyResidence (ID 132) con una cantidad mínima de datos (utilizando las propiedades válidas permittedUsage y numberOfBedrooms confirmadas para este esquema), la solicitud será la siguiente:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item

JSON

{ "schema_id": "132", "properties": { "permittedUsage": { "type": 21, "values": [ "The Luxury Casa" ] }, "numberOfBedrooms": { "type": 1169, "values": [ 4 ] } } }

Ten en cuenta que con cada propiedad proporcionaremos el ID de tipo disponible para el admitido por dicha propiedad en el esquema SingleFamilyResidence, y los valores para este tipo siempre se devuelven como un array, incluso si el elemento solo puede contener un valor simple.

Recuerda que puedes obtener información sobre estos tipos recuperando la especificación completa de las propiedades del esquema. Por ejemplo, en este caso:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/schema/132

Si es exitosa, el resultado de esta operación devolverá el nuevo ítem creado:

JSON

{ "schema_id": "132", "id": 1, "schema_url": "http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/1", "schema_label": "SingleFamilyResidence", "values": [ { "item_id": 1, "available_type_id": 21, "value": "The Luxury Casa", "ref_item_id": null, "position": 1, "id": 1 }, { "item_id": 1, "available_type_id": 1169, "value": 4, "ref_item_id": null, "position": 1, "id": 2 } ] }

Puedes recuperar los valores de un ítem por su ID de ítem único, por ejemplo:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/item/1

La respuesta será un objeto JSON+LD para el ítem con ID único 1, un tipo Person:

JSON

{
    "@context": "[http://schema.org](http://schema.org)",
    "@type": "SingleFamilyResidence",
    "@id": "http://localhost/structured-data-definitivo/public/api/v1/item/1",
    "numberOfBedrooms": "4",
    "permittedUsage": "The Luxury Casa"
}
Usando el parámetro de mostrar ítem (item show)
Este argumento se puede usar para mostrar información adicional sobre el ítem especificado. Puede contener una lista de valores separados por comas.

Uso:

''Postman

[GET] http://localhost//api/v1/item/{id}?show=deprecated,uid

Esta es la lista de valores:

uid: muestra los valores de ID único para cada elemento en el ítem.

deprecated: Es posible recuperar las definiciones obsoletas como propiedades o tipos de versiones anteriores para este esquema, usando el valor deprecated.

version: muestra el ID único de la versión.

Por ejemplo:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/item/1?show=deprecated,uid,version

Esta solicitud recupera la misma información sobre los valores del ítem, agregando el ID único para cada valor como propiedad @uid, el ID único del ítem como @item, y la versión de la importación del esquema como @version.

JSON

{
    "@context": "[http://schema.org](http://schema.org)",
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
Ten en cuenta que cualquier valor de propiedad para un tipo simple se especifica en el atributo @value.

Exportación de formatos usando el parámetro de formato
Es posible expresar los datos del ítem en otras expresiones de lenguaje con el argumento format.

Exportar valores de ítem a RDF XML
Muestra los valores del ítem en sintaxis RDF/XML.

Por ejemplo, el ítem definido como tipo Person se exportará como esta sintaxis:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/1?format=rdf

El resultado de esta operación de solicitud será:

XML

<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF xmlns:rdf="[http://www.w3.org/1999/02/22-rdf-syntax-ns#](http://www.w3.org/1999/02/22-rdf-syntax-ns#)"
         xmlns:schema="[http://schema.org/](http://schema.org/)">
    <schema:SingleFamilyResidence rdf:about="http://localhost/structured-data-definitivo/public/api/v1/item/1">
        <schema:numberOfBedrooms rdf:datatype="[http://www.w3.org/2001/XMLSchema#string](http://www.w3.org/2001/XMLSchema#string)">4</schema:numberOfBedrooms>
        <schema:permittedUsage rdf:datatype="[http://www.w3.org/2001/XMLSchema#string](http://www.w3.org/2001/XMLSchema#string)">The Luxury Casa</schema:permittedUsage>
    </schema:SingleFamilyResidence>
</rdf:RDF>
Exportar valores de ítem a script neo4j cypher
Si deseas importar un ítem y sus relaciones en un grafo neo4j, esta opción será útil para hacerlo. Este formato genera una lista de comandos en lenguaje cypher que se pueden importar en un proyecto neo4j.

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/1?format=neo4j

El resultado de esta operación de solicitud será:

MERGE (singleFamilyResidence1:SingleFamilyResidence {id:1})
SET singleFamilyResidence1.permittedUsage = ['The Luxury Casa']
SET singleFamilyResidence1.numberOfBedrooms = ['4']
RETURN singleFamilyResidence1
Esto resulta en la creación de un nodo SingleFamilyResidence (sfr1) con los atributos dados.

Manipulación de ítems
Los valores de un ítem se pueden crear o actualizar a través del endpoint de gestión de ítems.

Básicamente, la solicitud incluirá el ID de ítem único en la operación de actualización, o el ID de esquema único en el proceso de creación.

El resto de los datos requeridos son los valores para las propiedades para crear el contenido del ítem.

Creación de ítem
Si deseas crear un nuevo ítem basado en el esquema SingleFamilyResidence (ID 132) con una cantidad mínima de datos (permittedUsage y numberOfBedrooms), la solicitud será la siguiente:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item

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
Ten en cuenta que con cada propiedad proporcionaremos el ID de tipo disponible para el admitido por dicha propiedad en el esquema SingleFamilyResidence, y los valores para este tipo siempre se devuelven como un array, incluso si el elemento solo puede contener un valor simple.

Recuerda que puedes obtener información sobre estos tipos recuperando la especificación completa de las propiedades del esquema. Por ejemplo, en este caso:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/schema/132

Si es exitosa, el resultado de esta operación devolverá el nuevo ítem creado:

JSON

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
Actualización de ítem
Ahora, tenemos un ítem llamado 'Open Ximdex' de tipo Corporation. Necesitamos actualizar el correo electrónico de contacto y agregar información adicional, como dos empleados y la dirección del país.

El ejemplo anterior muestra cómo se puede hacer esto:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

JSON

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
Recuerda que si no especificas el ID del valor anterior, se agregará un nuevo correo electrónico a la propiedad email.

Utiliza el argumento show para conocer el ID único correspondiente a los valores del ítem para actualizar o eliminar:

''Postman

[GET] http://localhost/api/[tu-carpeta-de-proyecto]/v1/item/2?show=uid

Así que ahora tenemos un ítem Corporation con una relación con muchos ítems Person a través de la propiedad employee:

JSON

{
    "@context": "[http://schema.org](http://schema.org)",
    "@type": "SingleFamilyResidence",
    "@id": "http://localhost/structured-data-definitivo/public/api/v1/item/2",
    "permittedUsage": "The Modern Luxury Casa",
    "numberOfBedrooms": 4,
    "yearBuilt": 2018
}
Si deseas eliminar un valor de este ítem, por ejemplo, el empleado llamado David, podemos usar el argumento delete para eliminar solo este valor:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

JSON

{
    "delete": [6]
}
Se pueden eliminar muchos valores de propiedad especificando una lista de IDs de valores únicos en este atributo, y se pueden enviar con otras propiedades.

Se puede eliminar un conjunto completo de valores de propiedad especificando el atributo delete en lugar de la lista de IDs únicos.

Por ejemplo, si queremos eliminar todos los empleados de nuestro ítem creado anteriormente, usaremos el argumento delete en la propiedad del ítem correspondiente:

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

JSON

{
    "properties": {
        "permittedUsage": {"type": 21, "values": [], "delete": true}
    }
}
Solo los valores de tipo Person (2407) se eliminarán en la propiedad employee.

Si envías cualquier valor en el atributo values, entonces estos valores reemplazarán a los anteriores.

Eliminación de ítem
Todos los datos de un ítem se pueden eliminar usando su ID único. Los posibles ítems relacionados no se eliminarán, pero las relaciones con otros ítems desaparecerán.

Uso:

''Postman

[DELETE] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

*********Haz esto con cuidado, esta operación no se puede deshacer.

Colaboradores

Antonio Jesús Lucena @ajlucena78.

David Arroyo @davarresc.

Daniel Domínguez @daniel423615.

Fernando Quintero Gómez [@Quintero4] (https://github.com/Quintero4)



# Guía de usuario de datos estructurados

Este es un servicio API para la gestión y mantenimiento de datos estructurados. Escrito en **PHP** como un paquete para el *framework* **Laravel**.

Este documento proporciona la información del proceso de instalación y el uso de la API.

## Tabla de contenidos

- [Procedimiento de instalación](#installation-procedure)
  * [Solución de problemas en el error de instalación]
- [Ejemplo de uso básico](#basic-usage-example)
- [Especificación de puntos finales (endpoints) de la API](#api-endpoints-specification)
  * [Importación de esquemas](#schemas-importation)
  * [Operaciones de esquema](#schema-operations)
    + [Recuperar un esquema específico](#retrieve-a-specific-schema)
      **[Ajustando la ruta del punto final de la API]
    + [Creación de esquema](#schema-creation)
    + [Actualización de un esquema](#updating-a-schema)
    + [Eliminación de esquema](#schema-deletion)
  * [Gestión de propiedades de esquema](#schema-properties-management)
    + [Información de propiedad](#property-information)
    + [Crear una nueva propiedad](#create-a-new-property)
    + [Atributos de propiedad especificados para un esquema](#specified-property-attributes-for-a-schema)
    + [Actualización de una propiedad de esquema](#updating-a-schema-property)
  * [Manipulación de tipos de propiedad](#property-types-manipulation)
    + [Creación de tipo](#type-creation)
    + [Actualización de tipos de propiedad](#property-types-update)
  * [Gestión de ítems](#items-management)
    + [Usando el parámetro de mostrar ítem (item show)](#using-item-show-parameter)
    + [Exportación de formatos usando el parámetro de formato](#exporting-formats-using-the-format-parameter)
      - [Exportar valores de ítem a RDF/XML](#export-item-values-to-rdf-xml)
      - [Exportar valores de ítem a script *neo4j cypher*](#export-item-values-to-neo4j-cypher-script)
  * [Manipulación de ítems](#items-manipulation)
    + [Creación de ítem](#item-creation)
    + [Actualización de ítem](#item-update)
    + [Eliminación de ítem](#item-deletion)
- [Colaboradores](#contributors)

## Procedimiento de instalación

Primero necesitas una instancia de proyecto Laravel para poder usar este paquete. Se puede descargar desde el repositorio de Github ubicado en https://github.com/laravel/laravel.
También puedes usar [Composer](https://getcomposer.org/download/) para crearlo mediante el comando:

```shell
composer create-project laravel/laravel .
A continuación, ejecuta composer para requerir nuestra extensión en tu directorio Laravel:

Shell

composer require ximdex/structured-data
Este comando instala el paquete en la carpeta vendor.

Antes de continuar, debes configurar la base de datos correctamente en tu proyecto Laravel en tu archivo .env en el directorio raíz. Por ejemplo:

structured-data-refactor-code-update\vendor\ximdex\structured-data\doc\images

Solución de problemas en el error de instalación
Si encuentras un error como:

El siguiente paso es la generación de la base de datos. Debes ejecutar el proceso de migración usando el comando para este propósito:

Shell

php artisan migrate
Después de esta operación, hemos creado las tablas de la base de datos en nuestro proyecto con el prefijo strdata para evitar la colisión de nombres de tablas anteriores.

Ejemplo de uso básico
Requiere Postman o similar
En este momento podemos consumir las operaciones de la API para gestionar los esquemas y los datos de los ítems.

Asumimos en este manual que el host para nuestra instancia de Laravel está bajo un host llamado localhost y se está sirviendo. De lo contrario, necesitaríamos agregar el resto de la jerarquía de carpetas. Por ejemplo, para un proyecto Laravel en una carpeta llamada structured-data:

''Postman

http://localhost/structured-data/public/api/v1

Asumiendo lo anterior, entonces llamamos al endpoint con este ejemplo de uso:

''Postman

[GET] http://localhost/api/v1/schema

Esta solicitud recuperará un código JSON con todos los esquemas que están realmente creados en nuestro almacenamiento. Dado que es una instalación nueva, no habría ningún esquema. Esto se cubrirá más adelante.

Especificación de puntos finales (endpoints) de la API
Las operaciones sobre esquemas y datos de ítems te dan un control completo para crear o actualizar esquemas, generar ítems de un tipo de estos esquemas y asociar información a sus propiedades.

Importación de esquemas
Para importar una URL de definiciones de esquema proporcionada por schema.org puedes ejecutar este comando de consola en el directorio de Laravel:

Shell

php artisan schemas:import [https://schema.org/version/latest/schemaorg-current-http.jsonld](https://schema.org/version/latest/schemaorg-current-http.jsonld)
Este comando solo es compatible con las definiciones de schemas.org en formato JSON+LD.

Actualmente, Schema.org solo sirve la última versión, pero por motivos de legacy puedes especificar la versión como otro argumento:

Shell

php artisan schemas:import [https://schema.org/version/latest/schemaorg-current-http.jsonld](https://schema.org/version/latest/schemaorg-current-http.jsonld) 29.2
Si no se proporciona ningún argumento, la versión se etiquetará internamente como 'Latest'.

Si hay alguna definición de esquema creada por una importación anterior o por cualquier usuario, esta información se actualizará a la nueva versión y solo las definiciones ausentes se marcarán como obsoletas. Sin embargo, todavía podemos usar esta información desaprobada más tarde.

También es posible realizar esta importación utilizando el siguiente endpoint:

''Postman

[GET] http://localhost/api/v1/schemas-import?url=https://schema.org/version/latest/schemaorg-current-http.jsonld

Visita http://schema.org para más detalles.

Operaciones de esquema
Cada esquema es un tipo de ítem que contiene un número variable de propiedades y puede heredar propiedades adicionales de un esquema padre.

Recuperar un esquema específico
Puedes usar el ID único del esquema para cargar los atributos del esquema, sus propiedades y los otros esquemas que son heredados. Uso:

''Postman

[GET] http://localhost/api/v1/schema/132

** Ajustando la ruta del punto final de la API

Cuando se ejecuta el proyecto bajo un subdirectorio de un servidor web (como XAMPP, donde el proyecto está bajo structured-data-definitivo), debes incluir la ruta del proyecto y la carpeta public en las llamadas a la API.

La documentación asume que el proyecto Laravel se sirve directamente desde la raíz del host virtual (http://localhost).

URL de ejemplo de la Guía	Estructura de URL de tu Proyecto
http://localhost/api/v1/schema/132	http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/132

Exportar a Hojas de cálculo

Para un proyecto ubicado en C:\Xampp\htdocs\structured-data-1, el endpoint de API correcto para recuperar un esquema específico (por ejemplo, ID de esquema 132) será:

http://localhost/structured-data-1/public/api/v1/schema/132

El resultado de esta solicitud será un objeto JSON que contiene la definición de un esquema identificado por el número 132, en este ejemplo el esquema Person (ACTUALIZADO):

JSON

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
          
Nota: las propiedades se simplifican a solo 1 para servir como ejemplo debido a la mayor cantidad de ellas disponibles.

Podemos ver todas las propiedades y sus tipos disponibles para el tipo SingleFamilyResidence y sus esquemas heredados listados en el nodo "schemas". En este ejemplo, algunas de las propiedades provienen del esquema Place.

Creación de esquema
Se pueden crear nuevos esquemas con un nombre de etiqueta (label) dado y un comentario opcional. Si este nombre ya está en uso, la operación se cancelará.

Si el esquema extiende los atributos de otros esquemas, puedes usar el parámetro opcional schemas para enviar una lista de IDs únicos de esquemas y su prioridad. Este último argumento es opcional.

Adaptación del punto final de la API

Cuando se ejecuta el proyecto bajo un subdirectorio de un servidor web (como XAMPP, donde el proyecto está en structured-data-1), debes incluir la ruta del proyecto y la carpeta public en tus llamadas a la API.

URL de ejemplo de la Guía	Estructura de URL de tu Proyecto

Exportar a Hojas de cálculo

''Postman

| [POST] http://localhost/api/v1/schema | [POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema |

Para un proyecto ubicado en C:\Xampp\htdocs\structured-data-1, el endpoint correcto para crear un nuevo esquema será:

''Postman

[POST] http://localhost/structured-data-1/public/api/v1/schema

Ejemplo de Carga Útil (Payload) JSON (Adaptado a tu importación):

En este ejemplo adaptado, creamos el esquema LuxuryResidence, que extiende el esquema con ID 132 (que, después de la última importación, ahora es SingleFamilyResidence):

JSON

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

El resultado de la operación exitosa será el esquema creado que contiene el nuevo ID único asignado (por ejemplo, 920):

'''JSON

{
    "label": "LuxuryResidence",
    "comment": "A single-family residence with premium features.",
    "id": 920,
    "version_tag": null
}
Los esquemas de usuario no tienen una versión (valor *null* en su lugar), por lo que siempre están listos para ser utilizados en futuras versiones importadas.

Este es un ejemplo de un error de respuesta que ocurre cuando intentas crear un esquema con un nombre ya en uso:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "label": [
            "The label has already been taken."
        ]
    }
}
Otro error común ocurre cuando la etiqueta contiene algo más que letras. La etiqueta tampoco puede contener espacios, como por ejemplo "Club Member". El error devuelto se ve así:

JSON

{
    "message": "The label field must only contain letters.",
    "errors": {
        "label": [
            "The label field must only contain letters."
        ]
    }
}
Este es el formato común para cualquier error en una operación de gestión. Ten en cuenta que puedes tener múltiples errores con diferentes campos POST.

Actualización de un esquema
Similar a la creación de un nuevo esquema, la única diferencia es la inclusión del ID único del esquema a editar en la operación de solicitud, y el uso del método PUT o PATCH en lugar de POST.

Adaptación del punto final de la API

Cuando se ejecuta el proyecto bajo un subdirectorio, debes incluir la ruta del proyecto y la carpeta public en tus llamadas a la API.

Para un proyecto ubicado en C:\Xampp\htdocs\[tu-carpeta-de-proyecto], el endpoint correcto para actualizar el esquema con ID 920 (que ahora es HotelRoom) es:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/920

1. Actualización del comentario del esquema

Usamos PATCH para actualizar el campo comment:

JSON

{
    "comment": "Una residencia de lujo con caracteristicas premium (Actualizada)"
}
El resultado exitoso (después de que la importación asignara el ID 920 a HotelRoom) será:

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

2. Cambio de herencia de esquema

Es posible enviar una lista de esquemas para cambiar la herencia de la propiedad del esquema. Este argumento es opcional, pero si se usa, la relación con cualquier esquema asociado previamente se eliminará y se reemplazará con los dados.

Por ejemplo, actualizamos el esquema con ID 920 para que extienda el esquema ID 133:

''Postman

`[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/920`

JSON

{
    "schemas": [
        {
            "id": 133,
            "priority": 1
        }
    ]
}

El resultado exitoso muestra la herencia reemplazada. Nuestro esquema HotelRoom (ID 920) ahora extiende DownloadAction (ID 133):

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

Ahora nuestro esquema con ID único 920 extiende el esquema DownloadAction con prioridad 1.

______________________________________________________________________________________________________________

#### Eliminación de esquema

**Adaptación del punto final de la API**

Cuando se ejecuta el proyecto bajo un subdirectorio, debes incluir la ruta del proyecto y la carpeta `public` en tus llamadas a la API.

Para un proyecto ubicado en `C:\Xampp\htdocs\[tu-carpeta-de-proyecto]`, el *endpoint* correcto para eliminar el esquema con ID **920** (que era el esquema creado por el usuario, ahora **HotelRoom**) es:

''Postman

`[DELETE] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/schema/920`

Si el esquema ya está en uso por algún ítem, la operación será denegada.

**Resultado exitoso**

Una operación de eliminación exitosa a menudo devuelve un código de estado **HTTP 204 No Content**. Sin embargo, dependiendo de la configuración interna del *framework*, puede devolver un simple indicador de éxito.

* Si la operación es exitosa, la API podría devolver un cuerpo de texto simple que contiene **`1`** (que representa *boolean* `true` en PHP/Laravel).
* Para confirmar la eliminación, realizar una solicitud **`GET`** posterior en la misma URL (`.../schema/920`) ahora debería devolver un error **HTTP 404 Not Found**.

______________________________________________________________________________________________________________

### Gestión de propiedades de esquema

Admitimos algunas operaciones para gestionar las propiedades en uso para los esquemas.

______________________________________________________________________________________________________________

#### Información de propiedad
Se pueden modificar todos los detalles de una propiedad en un esquema. 

______________________________________________________________________________________________________________

### Crear una nueva propiedad
Puedes asociar una propiedad a un esquema usando esta función. Para hacer esto, es necesario proporcionar el ID único del esquema y el ID único de la propiedad para crear la relación. Además, el tipo o la lista de tipos disponibles deben estar presentes en esta solicitud para definir qué tipo de valor admitirá esta propiedad en este esquema.

**Adaptación del punto final de la API

El *endpoint* para crear una relación de propiedad es [POST] /api/v1/property-schema.

Para un proyecto ubicado en http://localhost/[tu-carpeta-de-proyecto], la URL correcta es:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema

Los tipos disponibles pueden ser un tipo simple de la siguiente lista:

Boolean

Date

DateTime

Time

Number

Text

O un tipo *Thing* que incluye un ID de esquema para admitir un ítem como valor para este tipo. El ejemplo anterior muestra que el esquema *Organization* admitirá ítems de tipo *Person* para la propiedad *employee*.

A veces queremos crear una nueva propiedad en lugar de usar una existente. Es posible enviar un nombre de propiedad (etiqueta) y un comentario opcional para crear tanto la propiedad como la relación con el esquema especificado.

Ejemplo de uso (Creación de la propiedad *checksum*):

Crearemos la propiedad **checksum** y la asignaremos al esquema **DownloadAction** (ID 133 de nuestra importación).

JSON

{
    "schema_id": 133,
    "label": "checksum",
    "comment": "Integrity hash value of the file.",
    "types": [
        {"type": "Text"}
    ]
}
Resultado de Creación Exitosa

La operación devuelve la propiedad/relación recién creada con un ID único (por ejemplo, 2185). Este ID se utiliza para operaciones de gestión posteriores.

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
Actualizar una propiedad
Similar a la creación de un nuevo esquema, la única diferencia es la inclusión del ID único de la propiedad/relación a editar en la operación, y el uso del método PUT o PATCH en lugar de POST.

CORRECCIÓN IMPORTANTE DE LA RUTA DE LA API: El *endpoint* correcto para actualizar los parámetros de relación (como cardinalidades o tipos) es **/api/v1/property-schema/{id}**, utilizando el ID de relación (por ejemplo, 2185). Intentar actualizar campos de texto descriptivos (como *comment* o *label*) a través de este *endpoint* será ignorado.

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema/2185

Ejemplo de Carga Útil JSON (Actualización de Cardinalidad):

Actualizamos la propiedad *checksum* (ID 2185) para que sea obligatoria y única estableciendo la cardinalidad mínima y máxima en 1.

JSON

{
    "min_cardinality": 1,
    "max_cardinality": 1
}
Resultado de Actualización Exitosa

La API devuelve la definición de propiedad/relación actualizada, confirmando el cambio en las cardinalidades:

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

#### Atributos de propiedad especificados para un esquema

Una propiedad en un esquema puede admitir algún tipo de restricciones y otras características.

Estos son parámetros opcionales en la solicitud, como se enumeran:

* **min_cardinality**: Especifica que debe haber un número de valores en esta propiedad igual o mayor que el número especificado para el esquema asociado. Por defecto no hay una cantidad mínima.

* **max_cardinality**: La propiedad en este esquema no admite un número de valores mayor que el dado. Por defecto no hay límite.

* **default_value**: Si se crea un valor con contenido vacío en esta propiedad, se usará este valor por defecto. Por defecto los valores no pueden estar vacíos.

* **order**: El orden de la propiedad en este esquema. Para fines de presentación.

Aquí hay un ejemplo de estos atributos:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema

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
Ahora, esta propiedad admitirá solo valores en modo texto, al menos un valor y un máximo de un valor definido para un ítem. La propiedad tendrá un orden de 1, y cualquier valor creado con contenido vacío se establecerá por defecto en "SHA-256".

Actualización de una propiedad de esquema
Para actualizar los atributos de propiedad ya definidos en un esquema, debes incluir el ID único de la relación esquema-propiedad en la solicitud.

CORRECCIÓN IMPORTANTE DE LA RUTA DE LA API: El endpoint correcto para actualizar los parámetros de relación (como tipos y cardinalidades) es /api/v1/property-schema/{id}.

Utiliza el endpoint con el método PUT o PATCH como en este ejemplo:

''Postman

[PUT] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema/2185

En este ejemplo, usamos PUT para redefinir completamente los atributos de nuestra propiedad checksum (ID 2185). Ten en cuenta que PUT generalmente requiere todos los campos, incluso si solo estás cambiando uno.

JSON

{
    "comment": "Default checksum value updated",
    "min_cardinality": 0,
    "max_cardinality": 0,
    "default_value": "0x0",
    "order": 5
}
Cada atributo es opcional. Usa el valor cero para restablecer las restricciones de cardinalidad a sus valores por defecto.

También puedes proporcionar una lista de tipos disponibles para cambiar los valores admitidos en esta propiedad. Por ejemplo, usando **PATCH** para cambiar solo los tipos:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/property-schema/2185

La operación exitosa que reemplazó el tipo antiguo (Text) con Number fue:

JSON

{
    "types": [
        {"type": "Number"}
    ]
}

Recuerda que los tipos antiguos se eliminarán.
______________________________________________________________________________________________________________

### Manipulación de tipos de propiedad

A menudo, necesitamos cambiar un tipo o agregar uno nuevo a una propiedad en un esquema. Si no quieres especificar todos los tipos en la operación de actualización de la propiedad, para cambiar los deseados, es posible agregarlos o actualizarlos por separado.

______________________________________________________________________________________________________________

#### Creación de tipo

Debes proporcionar el ID único de la relación entre el esquema y la propiedad (*property_schema_id*), y el tipo a crear:

''Postman

> [POST] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/available-types
```json
{
    "property_schema_id": 159, 
    "type": "Date"
}
En este momento tenemos un nuevo tipo que admite valores de tipo Date.

Esta operación devolverá un objeto JSON con la propiedad que contiene el nuevo tipo:

JSON

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
Actualización de tipos de propiedad
Para actualizar los atributos de tipo es necesario incluir el ID único del tipo.

Por ejemplo, queremos cambiar el tipo creado anteriormente de Date al esquema Person, para permitir ítems de este tipo como esta propiedad employee.

Para hacer esto, es necesario proporcionar el ID único para el esquema Person (132) y especificar Thing para el campo type:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/available-types/3161

JSON

{
    "type": "Thing",
    "schema_id": 145
}
Ahora el tipo actualizado que admite ítems del esquema Person se ve así:

JSON

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
Gestión de ítems
Un ítem es un grupo de valores para un tipo de esquema. Estos valores se agruparán en las propiedades del esquema de acuerdo con las especificaciones del esquema, como el número máximo y mínimo de valores (cardinalidad) y los tipos de apoyo en los tipos disponibles de las propiedades.

++Manipulación de ítems Los valores de un ítem se pueden crear o actualizar a través del endpoint de gestión de ítems.

Básicamente, la solicitud incluirá el ID de ítem único en la operación de actualización, o el ID de esquema único en el proceso de creación.

El resto de los datos requeridos son los valores para las propiedades para crear el contenido del ítem.

++Creación de ítem Si deseas crear un nuevo ítem basado en el esquema SingleFamilyResidence (ID 132) con una cantidad mínima de datos (utilizando las propiedades válidas permittedUsage y numberOfBedrooms confirmadas para este esquema), la solicitud será la siguiente:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item

JSON

{ "schema_id": "132", "properties": { "permittedUsage": { "type": 21, "values": [ "The Luxury Casa" ] }, "numberOfBedrooms": { "type": 1169, "values": [ 4 ] } } }

Ten en cuenta que con cada propiedad proporcionaremos el ID de tipo disponible para el admitido por dicha propiedad en el esquema SingleFamilyResidence, y los valores para este tipo siempre se devuelven como un array, incluso si el elemento solo puede contener un valor simple.

Recuerda que puedes obtener información sobre estos tipos recuperando la especificación completa de las propiedades del esquema. Por ejemplo, en este caso:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/schema/132

Si es exitosa, el resultado de esta operación devolverá el nuevo ítem creado:

JSON

{ "schema_id": "132", "id": 1, "schema_url": "http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/1", "schema_label": "SingleFamilyResidence", "values": [ { "item_id": 1, "available_type_id": 21, "value": "The Luxury Casa", "ref_item_id": null, "position": 1, "id": 1 }, { "item_id": 1, "available_type_id": 1169, "value": 4, "ref_item_id": null, "position": 1, "id": 2 } ] }

Puedes recuperar los valores de un ítem por su ID de ítem único, por ejemplo:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/item/1

La respuesta será un objeto JSON+LD para el ítem con ID único 1, un tipo Person:

JSON

{
    "@context": "[http://schema.org](http://schema.org)",
    "@type": "SingleFamilyResidence",
    "@id": "http://localhost/structured-data-definitivo/public/api/v1/item/1",
    "numberOfBedrooms": "4",
    "permittedUsage": "The Luxury Casa"
}
Usando el parámetro de mostrar ítem (item show)
Este argumento se puede usar para mostrar información adicional sobre el ítem especificado. Puede contener una lista de valores separados por comas.

Uso:

''Postman

[GET] http://localhost//api/v1/item/{id}?show=deprecated,uid

Esta es la lista de valores:

uid: muestra los valores de ID único para cada elemento en el ítem.

deprecated: Es posible recuperar las definiciones obsoletas como propiedades o tipos de versiones anteriores para este esquema, usando el valor deprecated.

version: muestra el ID único de la versión.

Por ejemplo:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/public/api/v1/item/1?show=deprecated,uid,version

Esta solicitud recupera la misma información sobre los valores del ítem, agregando el ID único para cada valor como propiedad @uid, el ID único del ítem como @item, y la versión de la importación del esquema como @version.

JSON

{
    "@context": "[http://schema.org](http://schema.org)",
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
Ten en cuenta que cualquier valor de propiedad para un tipo simple se especifica en el atributo @value.

Exportación de formatos usando el parámetro de formato
Es posible expresar los datos del ítem en otras expresiones de lenguaje con el argumento format.

Exportar valores de ítem a RDF XML
Muestra los valores del ítem en sintaxis RDF/XML.

Por ejemplo, el ítem definido como tipo Person se exportará como esta sintaxis:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/1?format=rdf

El resultado de esta operación de solicitud será:

XML

<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF xmlns:rdf="[http://www.w3.org/1999/02/22-rdf-syntax-ns#](http://www.w3.org/1999/02/22-rdf-syntax-ns#)"
         xmlns:schema="[http://schema.org/](http://schema.org/)">
    <schema:SingleFamilyResidence rdf:about="http://localhost/structured-data-definitivo/public/api/v1/item/1">
        <schema:numberOfBedrooms rdf:datatype="[http://www.w3.org/2001/XMLSchema#string](http://www.w3.org/2001/XMLSchema#string)">4</schema:numberOfBedrooms>
        <schema:permittedUsage rdf:datatype="[http://www.w3.org/2001/XMLSchema#string](http://www.w3.org/2001/XMLSchema#string)">The Luxury Casa</schema:permittedUsage>
    </schema:SingleFamilyResidence>
</rdf:RDF>
Exportar valores de ítem a script neo4j cypher
Si deseas importar un ítem y sus relaciones en un grafo neo4j, esta opción será útil para hacerlo. Este formato genera una lista de comandos en lenguaje cypher que se pueden importar en un proyecto neo4j.

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/1?format=neo4j

El resultado de esta operación de solicitud será:

MERGE (singleFamilyResidence1:SingleFamilyResidence {id:1})
SET singleFamilyResidence1.permittedUsage = ['The Luxury Casa']
SET singleFamilyResidence1.numberOfBedrooms = ['4']
RETURN singleFamilyResidence1
Esto resulta en la creación de un nodo SingleFamilyResidence (sfr1) con los atributos dados.

Manipulación de ítems
Los valores de un ítem se pueden crear o actualizar a través del endpoint de gestión de ítems.

Básicamente, la solicitud incluirá el ID de ítem único en la operación de actualización, o el ID de esquema único en el proceso de creación.

El resto de los datos requeridos son los valores para las propiedades para crear el contenido del ítem.

Creación de ítem
Si deseas crear un nuevo ítem basado en el esquema SingleFamilyResidence (ID 132) con una cantidad mínima de datos (permittedUsage y numberOfBedrooms), la solicitud será la siguiente:

''Postman

[POST] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item

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
Ten en cuenta que con cada propiedad proporcionaremos el ID de tipo disponible para el admitido por dicha propiedad en el esquema SingleFamilyResidence, y los valores para este tipo siempre se devuelven como un array, incluso si el elemento solo puede contener un valor simple.

Recuerda que puedes obtener información sobre estos tipos recuperando la especificación completa de las propiedades del esquema. Por ejemplo, en este caso:

''Postman

[GET] http://localhost/[tu-carpeta-de-proyecto]/api/v1/schema/132

Si es exitosa, el resultado de esta operación devolverá el nuevo ítem creado:

JSON

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
Actualización de ítem
Ahora, tenemos un ítem llamado 'Open Ximdex' de tipo Corporation. Necesitamos actualizar el correo electrónico de contacto y agregar información adicional, como dos empleados y la dirección del país.

El ejemplo anterior muestra cómo se puede hacer esto:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

JSON

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
Recuerda que si no especificas el ID del valor anterior, se agregará un nuevo correo electrónico a la propiedad email.

Utiliza el argumento show para conocer el ID único correspondiente a los valores del ítem para actualizar o eliminar:

''Postman

[GET] http://localhost/api/[tu-carpeta-de-proyecto]/v1/item/2?show=uid

Así que ahora tenemos un ítem Corporation con una relación con muchos ítems Person a través de la propiedad employee:

JSON

{
    "@context": "[http://schema.org](http://schema.org)",
    "@type": "SingleFamilyResidence",
    "@id": "http://localhost/structured-data-definitivo/public/api/v1/item/2",
    "permittedUsage": "The Modern Luxury Casa",
    "numberOfBedrooms": 4,
    "yearBuilt": 2018
}
Si deseas eliminar un valor de este ítem, por ejemplo, el empleado llamado David, podemos usar el argumento delete para eliminar solo este valor:

''Postman

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

JSON

{
    "delete": [6]
}
Se pueden eliminar muchos valores de propiedad especificando una lista de IDs de valores únicos en este atributo, y se pueden enviar con otras propiedades.

Se puede eliminar un conjunto completo de valores de propiedad especificando el atributo delete en lugar de la lista de IDs únicos.

Por ejemplo, si queremos eliminar todos los empleados de nuestro ítem creado anteriormente, usaremos el argumento delete en la propiedad del ítem correspondiente:

[PATCH] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

JSON

{
    "properties": {
        "permittedUsage": {"type": 21, "values": [], "delete": true}
    }
}
Solo los valores de tipo Person (2407) se eliminarán en la propiedad employee.

Si envías cualquier valor en el atributo values, entonces estos valores reemplazarán a los anteriores.

Eliminación de ítem
Todos los datos de un ítem se pueden eliminar usando su ID único. Los posibles ítems relacionados no se eliminarán, pero las relaciones con otros ítems desaparecerán.

Uso:

''Postman

[DELETE] http://localhost/[tu-carpeta-de-proyecto]/api/v1/item/2

*********Haz esto con cuidado, esta operación no se puede deshacer.

Colaboradores

Antonio Jesús Lucena @ajlucena78.

David Arroyo @davarresc.

Daniel Domínguez @daniel423615.

Fernando Quintero Gómez [@Quintero4] (https://github.com/Quintero4)

>>>>>>> df47be4aae923edcda5bd311132e9d6fc578b683
