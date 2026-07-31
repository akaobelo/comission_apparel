<?php
$file = 'resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);

// Replace the loop variable name for the unassigned catalog items to only include unassigned designs
$findUnassigned = "/@foreach\s*\(\s*\\\$designCatalog\s+as\s+\\\$design\s*\)/";
if (preg_match($findUnassigned, $content)) {
    echo "Found unassigned loop, replacing...\n";
    $content = preg_replace($findUnassigned, '@foreach($designCatalog->whereNull(\'design_collection_id\') as $design)', $content);
} else {
    echo "Unassigned loop not found!\n";
}

// Rename inner loop variable in collections dropdown (inside collection items edit loop) to prevent conflict
// Look at the select: name="design_collection_id"
// We want to replace only the select options loops inside the edit forms to use $col instead of $collection
// Specifically, let's find the select dropdown block and replace the foreach inside it.
$findDropdown = "/<select name=\"design_collection_id\"[^>]*>\s*<option value=\"\">No Collection<\/option>\s*@foreach\(\\\$designCollections as \\\$collection\)\s*<option value=\"\{\{ \\\$collection->id \}\}\" \{\{ \\\$design->design_collection_id == \\\$collection->id \? 'selected' : '' \}\}>\{\{ \\\$collection->name \}\}<\/option>\s*@endforeach\s*<\/select>/s";

// Since indentation might vary, let's do a more robust replace:
// Replace `@foreach($designCollections as $collection)` inside option blocks to `@foreach($designCollections as $col)`
// Let's do a simple str_replace of the specific blocks:
$content = str_replace(
    "@foreach(\$designCollections as \$collection)\r\n                                                             <option value=\"{{ \$collection->id }}\" {{ \$design->design_collection_id == \$collection->id ? 'selected' : '' }}>{{ \$collection->name }}</option>\r\n                                                         @endforeach",
    "@foreach(\$designCollections as \$col)\r\n                                                             <option value=\"{{ \$col->id }}\" {{ \$design->design_collection_id == \$col->id ? 'selected' : '' }}>{{ \$col->name }}</option>\r\n                                                         @endforeach",
    $content
);

// Also in case it uses \n instead of \r\n
$content = str_replace(
    "@foreach(\$designCollections as \$collection)\n                                                             <option value=\"{{ \$collection->id }}\" {{ \$design->design_collection_id == \$collection->id ? 'selected' : '' }}>{{ \$collection->name }}</option>\n                                                         @endforeach",
    "@foreach(\$designCollections as \$col)\n                                                             <option value=\"{{ \$col->id }}\" {{ \$design->design_collection_id == \$col->id ? 'selected' : '' }}>{{ \$col->name }}</option>\n                                                         @endforeach",
    $content
);

file_put_contents($file, $content);
echo "Replacement script completed.\n";
