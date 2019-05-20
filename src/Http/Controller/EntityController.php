<?php

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\Entity;

class EntityController extends Controller
{
    public function load(int $id)
    {
        $entity = Entity::findOrFail($id);
        return response()->json($entity);
    }
}
