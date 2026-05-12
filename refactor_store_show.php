<?php
$content = file_get_contents('resources/views/store/show.blade.php');

// 1. Rename "Submitted Roster" to "Placed Orders"
$content = str_replace('>Submitted Roster<', '>Placed Orders<', $content);
$content = str_replace('{{-- Submitted Roster Accordion --}}', '{{-- Placed Orders Accordion --}}', $content);

// 2. We need to replace the @if ... @elseif ... @else block starting at line 61.
// Let's find the boundaries of the blocks we need to change.
$startIf = strpos($content, "@if(\$store->status === 'submitted_to_admin')");
$startElse = strpos($content, "@else\n            {{-- ═══ NEW ORDER FORM GRID ═══ --}}");
$startForm = strpos($content, '<form action="{{ route(\'store.order.submit\', $store->slug) }}"');
$endForm = strpos($content, "</form>\n        @endif", $startForm);

if ($startIf === false || $startElse === false || $startForm === false || $endForm === false) {
    die("Could not find blocks\n");
}

$bannersBlock = substr($content, $startIf, $startElse - $startIf);
// Extract banners
$bannersBlock = str_replace("@if(\$store->status === 'submitted_to_admin')", "        @php\n            \$isClosed = true;\n            \$closedReason = null;\n            if (\$store->status === 'submitted_to_admin') {\n                \$closedReason = 'submitted_to_admin';\n            } elseif (\$store->status !== 'approved' && \$store->status !== 'submitted_to_admin') {\n                \$closedReason = 'not_active';\n            } elseif (!\$store->pricing_approved) {\n                \$closedReason = 'pricing_review';\n            } elseif (\$store->items->isEmpty()) {\n                \$closedReason = 'no_items';\n            } elseif (\$store->order_deadline && \$store->order_deadline->isPast()) {\n                \$closedReason = 'deadline_passed';\n            } else {\n                \$isClosed = false;\n            }\n        @endphp\n\n        @if(\$closedReason === 'submitted_to_admin')", $bannersBlock);

$bannersBlock = str_replace("@elseif(\$store->status !== 'approved' && \$store->status !== 'submitted_to_admin')", "@elseif(\$closedReason === 'not_active')", $bannersBlock);
$bannersBlock = str_replace("@elseif(!\$store->pricing_approved)", "@elseif(\$closedReason === 'pricing_review')", $bannersBlock);
$bannersBlock = str_replace("@elseif(\$store->items->isEmpty())", "@elseif(\$closedReason === 'no_items')", $bannersBlock);
$bannersBlock = str_replace("@elseif(\$store->order_deadline && \$store->order_deadline->isPast())", "@elseif(\$closedReason === 'deadline_passed')", $bannersBlock);
$bannersBlock .= "        @endif\n";

$bannersBlock .= "\n        @if(\$closedReason !== 'no_items')\n            {{-- ═══ NEW ORDER FORM GRID (OR ITEMS VIEW) ═══ --}}\n            ";

// Now fix the form contents
$formBlock = substr($content, $startForm, $endForm - $startForm + 7); // include </form>
// Remove the trailing \n        @endif that we included in the search
$formBlock = str_replace("</form>", "</form>\n        @endif", $formBlock);

// Wrap Athlete Info
$athleteInfoStart = strpos($formBlock, "{{-- Athlete Info --}}");
$itemsGridStart = strpos($formBlock, "{{-- Store Items Grid --}}");
$athleteInfoBlock = substr($formBlock, $athleteInfoStart, $itemsGridStart - $athleteInfoStart);
$newAthleteInfoBlock = "                @if(!\$isClosed)\n                " . trim($athleteInfoBlock) . "\n                @endif\n\n                ";
$formBlock = str_replace($athleteInfoBlock, $newAthleteInfoBlock, $formBlock);

// Wrap Special Notes
$specialNotesStart = strpos($formBlock, "{{-- Special Notes --}}");
$sizingPanelStart = strpos($formBlock, "{{-- SIZING SLIDE-OVER PANEL --}}");
$specialNotesBlock = substr($formBlock, $specialNotesStart, $sizingPanelStart - $specialNotesStart);
$newSpecialNotesBlock = "                @if(!\$isClosed)\n                " . trim($specialNotesBlock) . "\n                @endif\n\n                ";
$formBlock = str_replace($specialNotesBlock, $newSpecialNotesBlock, $formBlock);

// Wrap STICKY BOTTOM SUBMIT BAR
$submitBarStart = strpos($formBlock, "{{-- STICKY BOTTOM SUBMIT BAR --}}");
$formEnd = strpos($formBlock, "</form>");
$submitBarBlock = substr($formBlock, $submitBarStart, $formEnd - $submitBarStart);
$newSubmitBarBlock = "                @if(!\$isClosed)\n                " . trim($submitBarBlock) . "\n                @endif\n            ";
$formBlock = str_replace($submitBarBlock, $newSubmitBarBlock, $formBlock);

// Change "ORDER" to "VIEW DETAILS" if closed
$formBlock = str_replace("x-text=\"items['{{ \$item->id }}'].selected ? 'EDIT SIZING' : 'ORDER'\">", "x-text=\"items['{{ \$item->id }}'].selected ? 'EDIT SIZING' : ( '{{ \$isClosed ? 1 : 0 }}' == '1' ? 'VIEW DETAILS' : 'ORDER' )\">", $formBlock);

// Hide Selected text count in the items grid header
$formBlock = str_replace('<span class="text-sm font-bold text-slate-500"><span x-text="Object.values(items).filter(i => i.selected).length">0</span> Selected</span>', '@if(!\$isClosed)<span class="text-sm font-bold text-slate-500"><span x-text="Object.values(items).filter(i => i.selected).length">0</span> Selected</span>@endif', $formBlock);

// Fix Save & Select buttons inside the sizing panel
$buttonsStart = strpos($formBlock, '<div class="pt-8 border-t border-slate-100 mt-8">');
// Since there's a loop, we need to replace it carefully.
$oldButtons = '<button type="button" @click="items[\'{{ $item->id }}\'].selected = true; closePanel()" class="w-full py-4 bg-slate-900 text-white font-black uppercase tracking-widest text-sm rounded-xl hover:bg-secondary transition-colors shadow-lg shadow-slate-900/20">Save & Select</button>
                                    
                                    <button type="button" @click="items[\'{{ $item->id }}\'].selected = false; closePanel()" x-show="items[\'{{ $item->id }}\'].selected" class="w-full py-3 mt-3 bg-red-50 text-red-600 font-bold uppercase tracking-widest text-xs rounded-xl hover:bg-red-100 transition-colors">Remove Item</button>';
$newButtons = '                                    @if(!$isClosed)
                                        <button type="button" @click="items[\'{{ $item->id }}\'].selected = true; closePanel()" class="w-full py-4 bg-slate-900 text-white font-black uppercase tracking-widest text-sm rounded-xl hover:bg-secondary transition-colors shadow-lg shadow-slate-900/20">Save & Select</button>
                                        <button type="button" @click="items[\'{{ $item->id }}\'].selected = false; closePanel()" x-show="items[\'{{ $item->id }}\'].selected" class="w-full py-3 mt-3 bg-red-50 text-red-600 font-bold uppercase tracking-widest text-xs rounded-xl hover:bg-red-100 transition-colors">Remove Item</button>
                                    @else
                                        <button type="button" @click="closePanel()" class="w-full py-4 bg-slate-900 text-white font-black uppercase tracking-widest text-sm rounded-xl hover:bg-secondary transition-colors shadow-lg shadow-slate-900/20">Close Details</button>
                                    @endif';
$formBlock = str_replace($oldButtons, $newButtons, $formBlock);


// Combine everything
$newContent = substr($content, 0, $startIf) . $bannersBlock . $formBlock . substr($content, $endForm + 16);

file_put_contents('resources/views/store/show.blade.php', $newContent);
echo "Done\n";
