<?php
$file = 'resources/views/admin/dashboard.blade.php';
$content = file_get_contents($file);

// Find the dropdown around line 1330:
// We want to replace it specifically in the context of the collection items catalog loop.
// Let's use a regex to find:
// @foreach($designCollections as $collection)
// inside a select element that doesn't have class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2.5 text-sm..." (which is the new/create forms)
// Actually, let's find the select that has class="w-full bg-white border border-slate-300 rounded px-2.5 py-2 text-xs..." 
// and replace its inner loop.

$pattern = '/(<select name="design_collection_id" class="w-full bg-white border border-slate-300 rounded px-2\.5 py-2 text-xs[^>]*>\s*<option value="">No Collection<\/option>\s*)@foreach\s*\(\s*\\\$designCollections\s+as\s+\\\$collection\s*\)\s*<option value="\{\{\s*\\\$collection->id\s*\}\}"\s*\{\{\s*\\\$design->design_collection_id\s*==\s*\\\$collection->id\s*\?\s*\'selected\'\s*:\s*\'\'\s*\}\}>\{\{\s*\\\$collection->name\s*\}\}<\/option>\s*@endforeach/s';

$content = preg_replace_callback($pattern, function($matches) {
    echo "Found dropdown block!\n";
    return $matches[1] . '@foreach($designCollections as $col)' . "\n" .
           '                                                             <option value="{{ $col->id }}" {{ $design->design_collection_id == $col->id ? ? \'selected\' : \'\' }}>{{ $col->name }}</option>' . "\n" .
           '                                                         @endforeach';
}, $content);

// Let's verify and also do a simpler approach:
// Since there is only one place where `@foreach($designCollections as $collection)` is followed by:
// `<option value="{{ $collection->id }}" {{ $design->design_collection_id == $collection->id ?`
// We can just use a simple regex on that!
$simplePattern = '/@foreach\s*\(\s*\\\$designCollections\s+as\s+\\\$collection\s*\)\s*<option value="\{\{\s*\\\$collection->id\s*\}\}"\s*\{\{\s*\\\$design->design_collection_id\s*==\s*\\\$collection->id\s*\?\s*\'selected\'\s*:\s*\'\'\s*\}\}>\{\{\s*\\\$collection->name\s*\}\}<\/option>\s*@endforeach/s';

$content = preg_replace($simplePattern, '@foreach($designCollections as $col)
                                                             <option value="{{ $col->id }}" {{ $design->design_collection_id == $col->id ? \'selected\' : \'\' }}>{{ $col->name }}</option>
                                                         @endforeach', $content);

file_put_contents($file, $content);
echo "Dropdown fix completed.\n";
