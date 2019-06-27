<?php

namespace Ximdex\StructuredData\Controllers;

use Ximdex\StructuredData\Models\Node;

class NodeController extends Controller
{
    public function load(string $reference)
    {
        $node = Node::where('reference', $reference)->firstOrFail();
        return response()->json($node->items);
    }
}
