<?php

namespace Ximdex\StructuredData\Http\Controller;

use Ximdex\StructuredData\Models\Node;

class NodeController extends Controller
{
    public function load(string $reference)
    {
        $node = Node::where('reference', $reference)->firstOrFail();
        return response()->json($node->items);
    }
}
