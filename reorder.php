<?php
$content = file_get_contents('resources/views/admin/dashboard.blade.php');

// We need to carefully split by the known HTML comments or structure.
// Instead of array indexing which might be fragile if line numbers changed slightly, I'll use string positions.

$posAddCollection = strpos($content, '<!-- ADD NEW COLLECTION -->');
$posAddDesign = strpos($content, '<div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden xl:col-span-2">', $posAddCollection + 100);
$posUpdateCollections = strpos($content, '<!-- UPDATE EXISTING COLLECTIONS -->');
$posUpdateCatalog = strpos($content, '<!-- UPDATE EXISTING CATALOG -->');

if ($posAddCollection === false || $posAddDesign === false || $posUpdateCollections === false || $posUpdateCatalog === false) {
    die("Could not find sections");
}

$top = substr($content, 0, $posAddCollection);
$addCollection = substr($content, $posAddCollection, $posAddDesign - $posAddCollection);
$addDesign = substr($content, $posAddDesign, $posUpdateCollections - $posAddDesign);
$updateCollections = substr($content, $posUpdateCollections, $posUpdateCatalog - $posUpdateCollections);
$bottom = substr($content, $posUpdateCatalog);

// Create Add New Package by duplicating Add New Design
$addPackageStr = str_replace('Add New Design', 'Add New Package', $addDesign);
// Remove Package Category select and add hidden input
$addPackageStr = preg_replace('/<div[^>]*>\s*<label[^>]*>Package Category<\/label>[\s\S]*?<\/select>\s*<\/div>/', '<input type="hidden" name="category" value="package">', $addPackageStr);
$addPackageStr = str_replace('Add to Design Catalog', 'Add Package to Catalog', $addPackageStr);
$addPackageStr = "<!-- ADD NEW PACKAGE -->\n" . $addPackageStr;

// Modify Add New Design
$addDesignStr = preg_replace('/<div[^>]*>\s*<label[^>]*>Package Category<\/label>[\s\S]*?<\/select>\s*<\/div>/', '<input type="hidden" name="category" value="individual">', $addDesign);
$addDesignStr = "<!-- ADD NEW DESIGN -->\n" . $addDesignStr;

$newContent = $top . $addCollection . $updateCollections . $addPackageStr . $addDesignStr . $bottom;

file_put_contents('resources/views/admin/dashboard.blade.php', $newContent);
echo "Done\n";
