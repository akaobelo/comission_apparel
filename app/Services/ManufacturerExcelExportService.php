<?php

namespace App\Services;

use App\Models\DesignCatalog;
use App\Models\ParentOrder;
use App\Models\StoreItem;
use App\Models\TeamStore;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ManufacturerExcelExportService
{
    /**
     * Standard garment size progression for sorting rows cleanly.
     */
    protected static array $sizeOrder = [
        'YXXS'     => 1,
        'YXS'      => 2,
        'YS'       => 3,
        'YM'       => 4,
        'YL'       => 5,
        'YXL'      => 6,
        'AXS'      => 7,
        'XS'       => 8,
        'AS'       => 9,
        'S'        => 10,
        'AM'       => 11,
        'M'        => 12,
        'AL'       => 13,
        'L'        => 14,
        'AXL'      => 15,
        'XL'       => 16,
        'A2XL'     => 17,
        '2XL'      => 18,
        'XXL'      => 19,
        'A3XL'     => 20,
        '3XL'      => 21,
        'XXXL'     => 22,
        'A4XL'     => 23,
        '4XL'      => 24,
        'A5XL'     => 25,
        '5XL'      => 26,
        'ONE SIZE' => 90,
        'INCLUDED' => 91,
        'N/A'      => 99,
    ];

    /**
     * Non-sized garment types that can be part of a multi-item package.
     */
    protected static array $packageNonSizedTypes = [
        'backpack',
    ];

    /**
     * Generate a multi-sheet .xlsx Manufacturer Visual Order Sheet and return the temp file path.
     *
     * @param Collection $orders Collection of ParentOrder models
     * @param TeamStore|null $store Optional TeamStore context
     * @param string $title Title for the export banner
     * @return string Path to the generated temporary .xlsx file
     */
    public function generateXlsx(Collection $orders, ?TeamStore $store = null, string $title = 'Manufacturer Order Sheet'): string
    {
        @ini_set('memory_limit', '512M');

        if ($store) {
            $store->loadMissing(['items' => function ($q) {
                $q->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
            }, 'items.designCatalog', 'items.components.designCatalog']);
        }

        // Preload only relevant DesignCatalog entries for fast lookup
        $designIds = [];
        $designNames = [];
        $collectionIds = [];

        if ($store) {
            foreach ($store->items as $si) {
                if ($si->design_catalog_id) {
                    $designIds[] = (int) $si->design_catalog_id;
                }
                if ($si->designCatalog?->design_collection_id) {
                    $collectionIds[] = (int) $si->designCatalog->design_collection_id;
                }
                foreach (($si->component_design_ids ?? $si->designCatalog?->component_design_ids ?? []) as $cId) {
                    if (!empty($cId) && is_numeric($cId)) {
                        $designIds[] = (int) $cId;
                    }
                }
            }
        }

        foreach ($orders as $ord) {
            $isDirect = $ord->isDirectOrder();
            if (is_array($ord->items_json)) {
                foreach ($ord->items_json as $it) {
                    // Only Direct Orders store design_catalog.id in $it['id']; Store Orders store store_items.id!
                    if ($isDirect && !empty($it['id']) && is_numeric($it['id'])) {
                        $designIds[] = (int) $it['id'];
                    }
                    if (!empty($it['name'])) {
                        $designNames[] = trim((string) $it['name']);
                    }
                }
            }
        }

        $designIds = array_values(array_unique($designIds));
        $designNames = array_values(array_unique($designNames));

        $initialDesigns = collect();
        if (!empty($designIds) || !empty($designNames)) {
            $initialDesigns = DesignCatalog::where(function ($q) use ($designIds, $designNames) {
                if (!empty($designIds)) {
                    $q->whereIn('id', $designIds);
                }
                if (!empty($designNames)) {
                    $q->orWhereIn('name', $designNames);
                }
            })->get();

            foreach ($initialDesigns as $d) {
                if ($d->design_collection_id) {
                    $collectionIds[] = (int) $d->design_collection_id;
                }
                foreach (($d->component_design_ids ?? []) as $cId) {
                    if (!empty($cId) && is_numeric($cId)) {
                        $designIds[] = (int) $cId;
                    }
                }
            }
        }

        $collectionIds = array_values(array_unique(array_filter($collectionIds)));

        // Also extract leading brand/team prefix words (e.g., "Legacy", "RLA", "Elite") in case single designs were left unassigned
        $prefixWords = [];
        if ($store && !empty($store->name)) {
            $firstWord = strtok(trim($store->name), " \t\n\r-—_");
            if ($firstWord && strlen($firstWord) >= 3) {
                $prefixWords[] = $firstWord;
            }
        }
        foreach ($designNames as $dn) {
            $firstWord = strtok(trim($dn), " \t\n\r-—_");
            if ($firstWord && strlen($firstWord) >= 3) {
                $prefixWords[] = $firstWord;
            }
        }
        $prefixWords = array_values(array_unique($prefixWords));

        $designIds = array_values(array_unique($designIds));

        if (!empty($collectionIds) || !empty($prefixWords) || !empty($designIds)) {
            $extraDesigns = DesignCatalog::where(function ($q) use ($collectionIds, $prefixWords, $designIds) {
                if (!empty($collectionIds)) {
                    $q->whereIn('design_collection_id', $collectionIds);
                }
                if (!empty($designIds)) {
                    $q->orWhereIn('id', $designIds);
                }
                foreach ($prefixWords as $pw) {
                    $q->orWhere('name', 'like', $pw . '%');
                }
            })->get();
            $allDesigns = $initialDesigns->merge($extraDesigns)->unique('id')->values();
        } else {
            $allDesigns = $initialDesigns;
        }

        $designsById = $allDesigns->keyBy('id');
        $designsByName = [];
        $designsByNorm = [];
        foreach ($allDesigns as $d) {
            if ($d->name) {
                $designsByName[strtolower(trim($d->name))] = $d;
                $designsByNorm[$this->normalizeKey($d->name)] = $d;
            }
        }

        // 1. Parse all orders and build structured columns + athlete rows + aggregated size rows
        $parsed = $this->parseOrders($orders, $store, $allDesigns, $designsById, $designsByName, $designsByNorm);

        $columns = $parsed['columns'];         // Ordered list of column definitions
        $athleteRows = $parsed['athleteRows']; // Athlete-by-athlete matrix rows
        $aggRows = $parsed['aggRows'];         // Gender-grouped staircase rows (Item + Gender + Size -> Qty)
        $sizeTotals = $parsed['sizeTotals'];   // [colKey][size] => qty
        $allSizesUsed = $parsed['allSizesUsed'];
        $flatRoster = $parsed['flatRoster'];   // Detailed line items for Sheet 3

        // Prepare PNG thumbnails for each column's mockup image (deduplicated by local file path)
        $tempFiles = [];
        $mediaFiles = []; // [mediaIndex => ['path' => ..., 'width' => ..., 'height' => ...]]
        $colMediaMap = []; // [colKey => mediaIndex]
        $pathToMediaIdx = []; // [localImgPath => mediaIndex]

        $mediaIdx = 1;
        foreach ($columns as $colKey => $col) {
            $localImgPath = $this->resolveLocalImagePath($col['image_path'] ?? null);
            if ($localImgPath) {
                if (isset($pathToMediaIdx[$localImgPath])) {
                    $colMediaMap[$colKey] = $pathToMediaIdx[$localImgPath];
                } else {
                    $thumb = $this->createPngThumbnail($localImgPath, 260, 320);
                    if ($thumb) {
                        $tempFiles[] = $thumb['path'];
                        $mediaFiles[$mediaIdx] = $thumb;
                        $colMediaMap[$colKey] = $mediaIdx;
                        $pathToMediaIdx[$localImgPath] = $mediaIdx;
                        $mediaIdx++;
                    }
                }
            }
        }

        // Build the 2 Worksheets requested for manufacturer production:
        // 1. Quantity by Size Chart (pieces needed by size & gender)
        // 2. Full Order Roster (flat roster to label pieces by athlete name)
        $sharedStrings = [];
        $sharedStringMap = [];
        $addSharedString = function (string $str) use (&$sharedStrings, &$sharedStringMap): int {
            // Strip any invalid XML control characters
            $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str) ?? '';
            if (!isset($sharedStringMap[$clean])) {
                $idx = count($sharedStrings);
                $sharedStrings[] = $clean;
                $sharedStringMap[$clean] = $idx;
            }
            return $sharedStringMap[$clean];
        };

        // Sheet 1: Quantity by Size Chart (staircase aggregate grouped by Gender)
        $sheet1 = $this->buildStaircaseAggregateSheet(
            $title,
            $columns,
            $aggRows,
            $sizeTotals,
            $colMediaMap,
            $mediaFiles,
            $addSharedString
        );

        // Sheet 2: Full Order Roster (complete flat line-item table for labeling by name)
        $sheet2 = $this->buildFullRosterSheet(
            $title,
            $flatRoster,
            $addSharedString
        );

        // Package everything into a valid OpenXML .xlsx ZIP archive
        $xlsxPath = tempnam(sys_get_temp_dir(), 'mfg_sheet_') . '.xlsx';
        $zip = new \ZipArchive();
        if ($zip->open($xlsxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create temporary XLSX archive.');
        }

        $hasDrawing1 = !empty($sheet1['drawings']);

        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXml($hasDrawing1, false, !empty($mediaFiles)));
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());
        $zip->addFromString('xl/sharedStrings.xml', $this->buildSharedStringsXml($sharedStrings));

        // Add Sheet 1: Quantity by Size Chart
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1['xml']);
        if ($hasDrawing1) {
            $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', $this->buildSheetRelsXml(1));
            $zip->addFromString('xl/drawings/drawing1.xml', $sheet1['drawingXml']);
            $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', $sheet1['drawingRelsXml']);
        }

        // Add Sheet 2: Full Order Roster
        $zip->addFromString('xl/worksheets/sheet2.xml', $sheet2['xml']);

        // Add embedded PNG images into xl/media/
        foreach ($mediaFiles as $mIdx => $media) {
            if (file_exists($media['path'])) {
                $zip->addFile($media['path'], "xl/media/image{$mIdx}.png");
            }
        }

        $zip->close();

        // Clean up temporary thumbnail files
        foreach ($tempFiles as $tf) {
            if (file_exists($tf)) {
                @unlink($tf);
            }
        }

        return $xlsxPath;
    }

    /**
     * Parse orders and store items into structured columns, athlete rows, and aggregated staircase rows.
     */
    protected function parseOrders(
        Collection $orders,
        ?TeamStore $store,
        Collection $allDesigns,
        Collection $designsById,
        array $designsByName,
        array $designsByNorm
    ): array {
        $columns = [];
        $athleteRows = [];
        $aggMap = []; // [genderGroup][colKey][size] => ['qty' => int, 'order_type' => string]
        $sizeTotals = []; // [colKey][size] => int
        $allSizesSet = [];
        $flatRoster = [];

        $storeItems = $store ? $store->items : collect();
        $sizedTypesList = DesignCatalog::sizedTypes();

        // Helper to resolve a StoreItem and DesignCatalog for an order item entry
        $resolveModels = function (array $item, bool $isDirectOrder = false) use ($storeItems, $designsById, $designsByName, $designsByNorm) {
            $itemId = isset($item['id']) ? (int) $item['id'] : null;
            $itemName = trim((string) ($item['name'] ?? ''));
            $normName = $this->normalizeKey($itemName);

            $storeItem = null;
            if (!$isDirectOrder && $storeItems->isNotEmpty()) {
                if ($itemId) {
                    $cand = $storeItems->firstWhere('id', $itemId);
                    if ($cand && ($itemName === '' || strcasecmp(trim($cand->name), $itemName) === 0)) {
                        $storeItem = $cand;
                    }
                }
                if (!$storeItem && $itemId) {
                    $cand = $storeItems->firstWhere('design_catalog_id', $itemId);
                    if ($cand && ($itemName === '' || strcasecmp(trim($cand->name), $itemName) === 0)) {
                        $storeItem = $cand;
                    }
                }
                if (!$storeItem && $itemName !== '') {
                    $storeItem = $storeItems->first(fn($si) => strcasecmp(trim($si->name), $itemName) === 0);
                }
                if (!$storeItem && $normName !== '') {
                    $storeItem = $storeItems->first(fn($si) => $this->normalizeKey($si->name) === $normName);
                }
                // If the item was renamed in the store/catalog after the order was placed, match by store_items.id!
                if (!$storeItem && $itemId) {
                    $storeItem = $storeItems->firstWhere('id', $itemId) ?? $storeItems->firstWhere('design_catalog_id', $itemId);
                }
            }

            $design = $storeItem?->designCatalog;
            if (!$design && $itemId && isset($designsById[$itemId])) {
                $cand = $designsById[$itemId];
                if ($itemName === '' || strcasecmp(trim($cand->name), $itemName) === 0) {
                    $design = $cand;
                }
            }
            if (!$design && $itemName !== '') {
                $design = $designsByName[strtolower($itemName)] ?? ($designsByNorm[$normName] ?? null);
            }
            // Only fall back to raw $designsById[$itemId] for Direct Orders (where $itemId is a design_catalog.id, not a store_items.id!)
            if (!$design && ($isDirectOrder || $storeItems->isEmpty()) && $itemId && isset($designsById[$itemId])) {
                $design = $designsById[$itemId];
            }

            return [$storeItem, $design];
        };

        // Helper to pick the best flat-lay manufacturer mockup image for a specific (item, garmentType)
        $resolveGarmentImage = function (
            ?StoreItem $storeItem,
            ?DesignCatalog $design,
            string $itemName,
            ?string $garmentType,
            string $gender,
            bool $isMultiPiece
        ) use ($storeItems, $allDesigns): ?string {
            // If this is a multi-piece package or set (e.g., Uniform Set or Gold Package),
            // check if the store or design collection has a dedicated single-garment item for $garmentType
            // so each column header shows that exact individual garment piece's flat-lay mockup!
            if ($isMultiPiece && !empty($garmentType)) {
                $bestSingleImg = $this->findBestSingleGarmentImage(
                    $garmentType,
                    $itemName,
                    $gender,
                    $storeItems,
                    $design?->design_collection_id,
                    $allDesigns
                );
                if ($bestSingleImg) {
                    return $bestSingleImg;
                }
            }

            // Otherwise use the item's own flat-lay mockup image (last image in image_paths is the flat-lay!)
            $paths = [];
            if ($storeItem && !empty($storeItem->image_paths) && is_array($storeItem->image_paths)) {
                $paths = $storeItem->image_paths;
            } elseif ($storeItem && !empty($storeItem->image_url)) {
                $paths = [$storeItem->image_url];
            } elseif ($design && !empty($design->image_paths) && is_array($design->image_paths)) {
                $paths = $design->image_paths;
            } elseif ($design && !empty($design->image_url)) {
                $paths = [$design->image_url];
            }

            return $this->pickFlatLayImage($paths);
        };

        // Helper to register or retrieve a column definition
        $ensureColumn = function (
            string $colKey,
            string $headerLabel,
            ?string $imagePath,
            int $sortOrder,
            int $subOrder = 0
        ) use (&$columns) {
            if (!isset($columns[$colKey])) {
                $columns[$colKey] = [
                    'key'        => $colKey,
                    'label'      => $headerLabel,
                    'image_path' => $imagePath,
                    'sort_order' => $sortOrder,
                    'sub_order'  => $subOrder,
                    'total_qty'  => 0,
                ];
            } elseif (empty($columns[$colKey]['image_path']) && !empty($imagePath)) {
                $columns[$colKey]['image_path'] = $imagePath;
            }
        };

        $typeSortWeight = [
            'shirt_short'    => 1,
            'shirt_long'     => 2,
            'polo'           => 3,
            'uniform_top'    => 4,
            'uniform_bottom' => 5,
            'uniform_set'    => 6,
            'uniform_set_2'  => 7,
            'shorts'         => 8,
            'leggings'       => 9,
            'pants'          => 10,
            'warmup_top'     => 11,
            'warmup_bottom'  => 12,
            'warmup_set'     => 13,
            'hoodie'         => 14,
            'jacket'         => 15,
            'backpack'       => 16,
            'arm_sleeve'     => 17,
            'socks'          => 18,
            'headwear'       => 19,
            'accessory'      => 20,
        ];

        foreach ($orders as $order) {
            if (!is_array($order->items_json) || empty($order->items_json)) {
                continue;
            }

            $athleteRow = [
                'order_id'       => $order->id,
                'first_name'     => trim((string) $order->athlete_first_name),
                'last_name'      => trim((string) $order->athlete_last_name),
                'gender'         => trim((string) ($order->gender ?? '')),
                'jersey_name'    => trim((string) ($order->jersey_name ?? '')),
                'jersey_number'  => trim((string) ($order->jersey_number ?? '')),
                'backpack_name'  => trim((string) ($order->backpack_name ?? '')),
                'special_notes'  => trim((string) ($order->special_notes ?? '')),
                'cells'          => [], // [colKey => ['entries' => [['size' => ..., 'qty' => ...]]]]
            ];

            $orderTypeLabel = $order->isDirectOrder() ? 'Direct Order' : 'Store Order';

            foreach ($order->items_json as $item) {
                $itemQty = max(1, (int) ($item['qty'] ?? 1));
                $itemName = trim((string) ($item['name'] ?? 'Unknown Item'));
                $itemGender = trim((string) ($item['gender'] ?? $order->gender ?? 'Unisex'));
                if ($athleteRow['gender'] === '' && $itemGender !== '') {
                    $athleteRow['gender'] = $itemGender;
                }

                $isDirect = $order->isDirectOrder();
                [$storeItem, $design] = $resolveModels($item, $isDirect);
                if ($storeItem && !empty($storeItem->name)) {
                    $itemName = trim((string) $storeItem->name);
                } elseif ($design && !empty($design->name) && $itemName === 'Unknown Item') {
                    $itemName = trim((string) $design->name);
                }
                $baseSort = (int) ($storeItem?->sort_order ?? $design?->sort_order ?? 999);
                $itemIdentifier = $storeItem ? ('si_' . $storeItem->id) : ($design ? ('dc_' . $design->id) : ('nm_' . $this->normalizeKey($itemName)));

                // Extract individual garment pieces from this item
                $pieces = []; // [['col_key' => ..., 'label' => ..., 'type' => ..., 'size' => ..., 'qty' => ..., 'image' => ..., 'sub_order' => ...]]

                if (!empty($item['components']) && is_array($item['components'])) {
                    foreach ($item['components'] as $compIdx => $comp) {
                        $compName = trim((string) ($comp['name'] ?? 'Component'));
                        $compQty = $itemQty * max(1, (int) ($comp['qty'] ?? 1));
                        [$compStoreItem, $compDesign] = $resolveModels($comp, $isDirect);
                        if ($compStoreItem && !empty($compStoreItem->name)) {
                            $compName = trim((string) $compStoreItem->name);
                        }

                        if (!empty($comp['sizes']) && is_array($comp['sizes'])) {
                            $isMultiComp = count($comp['sizes']) > 1;
                            foreach ($comp['sizes'] as $t => $s) {
                                if (empty($s)) continue;
                                $tLabel = DesignCatalog::formatTypeLabel((string) $t);
                                $colLabel = $isMultiComp ? "{$compName} ({$tLabel})" : $compName;
                                $colKey = "{$itemIdentifier}_comp_{$compIdx}_{$t}";
                                $img = $resolveGarmentImage($compStoreItem ?? $storeItem, $compDesign ?? $design, $compName, (string) $t, $itemGender, $isMultiComp);
                                $pieces[] = [
                                    'col_key'   => $colKey,
                                    'label'     => $colLabel,
                                    'type'      => (string) $t,
                                    'size'      => (string) $s,
                                    'qty'       => $compQty,
                                    'image'     => $img,
                                    'sub_order' => $typeSortWeight[$t] ?? ($compIdx + 1),
                                ];
                            }
                        } else {
                            $s = !empty($comp['size']) ? (string) $comp['size'] : 'One Size';
                            $colKey = "{$itemIdentifier}_comp_{$compIdx}";
                            $img = $resolveGarmentImage($compStoreItem ?? $storeItem, $compDesign ?? $design, $compName, null, $itemGender, false);
                            $pieces[] = [
                                'col_key'   => $colKey,
                                'label'     => $compName,
                                'type'      => (string) ($comp['type'] ?? 'component'),
                                'size'      => $s,
                                'qty'       => $compQty,
                                'image'     => $img,
                                'sub_order' => $compIdx + 1,
                            ];
                        }
                    }
                } else {
                    $itemTypes = isset($item['types']) && is_array($item['types'])
                        ? $item['types']
                        : (!empty($item['type']) ? [$item['type']] : []);

                    $sizesMap = (isset($item['sizes']) && is_array($item['sizes'])) ? array_filter($item['sizes'], fn($v) => $v !== null && $v !== '') : [];

                    // Check if this item also includes non-sized package items (like a Backpack inside a Uniform Package)
                    $extraNonSized = [];
                    foreach ($itemTypes as $t) {
                        if (in_array($t, self::$packageNonSizedTypes, true) && !isset($sizesMap[$t])) {
                            $extraNonSized[] = $t;
                        }
                    }

                    $totalDistinctPieces = count($sizesMap) + count($extraNonSized);
                    $isMultiPiece = $totalDistinctPieces > 1;
                    $componentDesignIds = is_array($storeItem?->component_design_ids) && !empty($storeItem->component_design_ids)
                        ? $storeItem->component_design_ids
                        : (is_array($design?->component_design_ids) ? $design->component_design_ids : []);

                    $resolveMappedPiece = function (string $typeKey, bool $defaultMulti) use (
                        $componentDesignIds,
                        $storeItems,
                        $designsById,
                        $storeItem,
                        $design,
                        $itemName,
                        $itemIdentifier,
                        $baseSort,
                        $itemGender,
                        $resolveGarmentImage,
                        $typeSortWeight
                    ): array {
                        $tLabel = ($typeKey === 'default' || is_numeric($typeKey)) ? '' : DesignCatalog::formatTypeLabel($typeKey);

                        if (!empty($componentDesignIds[$typeKey])) {
                            $linkedDesignId = (int) $componentDesignIds[$typeKey];
                            $linkedStoreItem = $storeItems->firstWhere('design_catalog_id', $linkedDesignId);
                            $linkedDesign = $designsById->get($linkedDesignId) ?? $linkedStoreItem?->designCatalog;

                            if ($linkedStoreItem || $linkedDesign) {
                                $linkedName = trim((string) ($linkedStoreItem?->name ?: $linkedDesign?->name ?: $itemName));
                                $linkedIdentifier = $linkedStoreItem ? ('si_' . $linkedStoreItem->id) : ('dc_' . $linkedDesign->id);
                                $linkedSort = (int) ($linkedStoreItem?->sort_order ?? $linkedDesign?->sort_order ?? $baseSort);
                                $linkedTypes = is_array($linkedStoreItem?->types) && !empty($linkedStoreItem->types)
                                    ? $linkedStoreItem->types
                                    : (is_array($linkedDesign?->types) ? $linkedDesign->types : []);
                                $linkedIsMulti = count($linkedTypes) > 1;

                                $colLabel = ($linkedIsMulti && $tLabel !== '') ? "{$linkedName} - {$tLabel}" : $linkedName;
                                $colKey = $linkedIsMulti ? "{$linkedIdentifier}_{$typeKey}" : $linkedIdentifier;
                                $img = $resolveGarmentImage($linkedStoreItem, $linkedDesign, $linkedName, $typeKey, $itemGender, $linkedIsMulti);

                                return [
                                    'col_key'     => $colKey,
                                    'label'       => $colLabel,
                                    'image'       => $img,
                                    'sort_order'  => $linkedSort,
                                    'sub_order'   => $linkedIsMulti ? ($typeSortWeight[$typeKey] ?? 50) : 0,
                                    'linked_name' => $linkedName,
                                ];
                            }
                        }

                        $colLabel = ($defaultMulti && $tLabel !== '') ? "{$itemName} - {$tLabel}" : $itemName;
                        $colKey = $defaultMulti ? "{$itemIdentifier}_{$typeKey}" : $itemIdentifier;
                        $img = $resolveGarmentImage($storeItem, $design, $itemName, $typeKey, $itemGender, $defaultMulti);

                        return [
                            'col_key'     => $colKey,
                            'label'       => $colLabel,
                            'image'       => $img,
                            'sort_order'  => $baseSort,
                            'sub_order'   => $typeSortWeight[$typeKey] ?? 50,
                            'linked_name' => null,
                        ];
                    };

                    if (!empty($sizesMap)) {
                        foreach ($sizesMap as $t => $s) {
                            $typeKey = (string) $t;
                            $mapped = $resolveMappedPiece($typeKey, $isMultiPiece);

                            $pieces[] = [
                                'col_key'     => $mapped['col_key'],
                                'label'       => $mapped['label'],
                                'type'        => $typeKey,
                                'size'        => trim((string) $s),
                                'qty'         => $itemQty,
                                'image'       => $mapped['image'],
                                'sort_order'  => $mapped['sort_order'],
                                'sub_order'   => $mapped['sub_order'],
                                'linked_name' => $mapped['linked_name'],
                            ];
                        }
                    }

                    foreach ($extraNonSized as $nsType) {
                        $mapped = $resolveMappedPiece($nsType, $isMultiPiece);

                        $pieces[] = [
                            'col_key'     => $mapped['col_key'],
                            'label'       => $mapped['label'],
                            'type'        => $nsType,
                            'size'        => 'One Size',
                            'qty'         => $itemQty,
                            'image'       => $mapped['image'],
                            'sort_order'  => $mapped['sort_order'],
                            'sub_order'   => $mapped['sub_order'],
                            'linked_name' => $mapped['linked_name'],
                        ];
                    }

                    // Fallback when sizesMap and extraNonSized are both empty (e.g. standalone Backpack or legacy 'size' field)
                    if (empty($pieces)) {
                        $primaryType = $itemTypes[0] ?? 'item';
                        $s = !empty($item['size']) ? trim((string) $item['size']) : 'One Size';
                        $img = $resolveGarmentImage($storeItem, $design, $itemName, (string) $primaryType, $itemGender, false);
                        $pieces[] = [
                            'col_key'   => $itemIdentifier,
                            'label'     => $itemName,
                            'type'      => (string) $primaryType,
                            'size'      => $s,
                            'qty'       => $itemQty,
                            'image'     => $img,
                            'sub_order' => $typeSortWeight[$primaryType] ?? 50,
                        ];
                    }
                }

                // Record each piece into columns, athlete row, and aggregate structures
                foreach ($pieces as $p) {
                    $cKey = $p['col_key'];
                    $sizeStr = strtoupper(trim($p['size']));
                    if ($sizeStr === '') {
                        $sizeStr = 'ONE SIZE';
                    }
                    $qty = (int) $p['qty'];
                    $pieceSortOrder = (int) ($p['sort_order'] ?? $baseSort);

                    $ensureColumn($cKey, $p['label'], $p['image'], $pieceSortOrder, $p['sub_order']);
                    $columns[$cKey]['total_qty'] += $qty;

                    $athleteRow['cells'][$cKey][] = [
                        'size' => $sizeStr,
                        'qty'  => $qty,
                        'type' => $p['type'],
                    ];

                    $sizeTotals[$cKey][$sizeStr] = ($sizeTotals[$cKey][$sizeStr] ?? 0) + $qty;
                    $allSizesSet[$sizeStr] = true;

                    $genderBucket = $this->normalizeGenderLabel($itemGender);
                    if (!isset($aggMap[$genderBucket][$cKey][$sizeStr])) {
                        $aggMap[$genderBucket][$cKey][$sizeStr] = [
                            'qty'        => 0,
                            'order_type' => $orderTypeLabel,
                        ];
                    }
                    $aggMap[$genderBucket][$cKey][$sizeStr]['qty'] += $qty;
                }

                // Also add to flat roster for Sheet 2
                $sizesSummary = [];
                foreach ($pieces as $p) {
                    if (count($pieces) > 1) {
                        $piecePrefix = DesignCatalog::formatTypeLabel($p['type']);
                        if (!empty($p['linked_name']) && strcasecmp($p['linked_name'], $itemName) !== 0) {
                            $piecePrefix .= ' (' . $p['linked_name'] . ')';
                        }
                        $sizesSummary[] = $piecePrefix . ': ' . $p['size'];
                    } else {
                        $sizesSummary[] = $p['size'];
                    }
                }

                $flatRoster[] = [
                    'order_id'       => $order->id,
                    'date'           => $order->created_at ? $order->created_at->format('Y-m-d') : '',
                    'first_name'     => $athleteRow['first_name'],
                    'last_name'      => $athleteRow['last_name'],
                    'gender'         => $itemGender,
                    'jersey_name'    => $athleteRow['jersey_name'],
                    'jersey_number'  => $athleteRow['jersey_number'],
                    'backpack_name'  => $athleteRow['backpack_name'],
                    'item_name'      => $itemName,
                    'types'          => implode(', ', array_map(fn($t) => DesignCatalog::formatTypeLabel((string) $t), $item['types'] ?? [$item['type'] ?? ''])),
                    'sizes'          => implode(' | ', $sizesSummary),
                    'qty'            => $itemQty,
                ];
            }

            $athleteRows[] = $athleteRow;
        }

        // If store has items but no orders yet, populate columns from store items in sort_order
        if (empty($columns) && $storeItems->isNotEmpty()) {
            foreach ($storeItems as $si) {
                $cKey = 'si_' . $si->id;
                $paths = !empty($si->image_paths) ? $si->image_paths : ($si->image_url ? [$si->image_url] : ($si->designCatalog?->image_paths ?? []));
                $ensureColumn($cKey, $si->name, $this->pickFlatLayImage($paths), (int) ($si->sort_order ?? 999), 0);
            }
        }

        // Sort columns strictly by store item sort_order, then sub_order
        uasort($columns, function ($a, $b) {
            if ($a['sort_order'] !== $b['sort_order']) {
                return $a['sort_order'] <=> $b['sort_order'];
            }
            return $a['sub_order'] <=> $b['sub_order'];
        });

        // Sort allSizesUsed in logical garment progression
        $allSizesUsed = array_keys($allSizesSet);
        usort($allSizesUsed, fn($a, $b) => $this->compareSizes($a, $b));

        // Build ordered staircase rows grouped by Gender -> Column Order -> Size Order
        $genderPriority = ['Female' => 1, 'Male' => 2, 'Unisex' => 3];
        uksort($aggMap, function ($ga, $gb) use ($genderPriority) {
            return ($genderPriority[$ga] ?? 9) <=> ($genderPriority[$gb] ?? 9);
        });

        $aggRows = [];
        foreach ($aggMap as $genderLabel => $colsInGender) {
            $genderRows = [];
            foreach (array_keys($columns) as $cKey) {
                if (empty($colsInGender[$cKey])) {
                    continue;
                }
                $sizesInCol = $colsInGender[$cKey];
                uksort($sizesInCol, fn($sa, $sb) => $this->compareSizes((string) $sa, (string) $sb));
                foreach ($sizesInCol as $sizeStr => $info) {
                    $genderRows[] = [
                        'order_type' => $info['order_type'],
                        'gender'     => $genderLabel,
                        'qty'        => $info['qty'],
                        'col_key'    => $cKey,
                        'size'       => $sizeStr,
                    ];
                }
            }
            if (!empty($genderRows)) {
                $aggRows[$genderLabel] = $genderRows;
            }
        }

        return [
            'columns'      => $columns,
            'athleteRows'  => $athleteRows,
            'aggRows'      => $aggRows,
            'sizeTotals'   => $sizeTotals,
            'allSizesUsed' => $allSizesUsed,
            'flatRoster'   => $flatRoster,
        ];
    }

    /**
     * Build Sheet 1: Athlete Visual Chart (matches Luke's bottom screenshot + size summary).
     */
    protected function buildAthleteVisualSheet(
        string $title,
        array $columns,
        array $athleteRows,
        array $sizeTotals,
        array $allSizesUsed,
        array $colMediaMap,
        array $mediaFiles,
        callable $addSharedString
    ): array {
        $colKeys = array_keys($columns);
        $fixedCols = [
            'First Name',
            'Last Name',
            'Gender',
            'Jersey Name',
            'Jersey Number',
            'Backpack Name',
            'Special Notes',
        ];
        $fixedCount = count($fixedCols); // 7 columns (A..G), product columns start at H (0-based index 7)

        $rowsXml = [];
        $drawings = [];

        // Row 1: Title Banner
        $totalUnits = array_sum(array_column($columns, 'total_qty'));
        $bannerText = strtoupper($title) . '  |  Orders: ' . count($athleteRows) . '  |  Total Garment Units: ' . $totalUnits . '  |  Exported: ' . now()->format('M d, Y');
        $rowsXml[] = $this->buildRowXml(1, 28, [
            [0, $bannerText, 's', 9],
        ]);

        // Row 2: Tall Product Mockup Image Row (height 135pt = ~180px)
        $imageRowCells = [];
        for ($i = 0; $i < $fixedCount; $i++) {
            $imageRowCells[] = [$i, $i === 0 ? 'PRODUCT MOCKUPS' : '', 's', 11];
        }
        foreach ($colKeys as $idx => $cKey) {
            $colIdx = $fixedCount + $idx;
            $imageRowCells[] = [$colIdx, '', 's', 11];
            if (isset($colMediaMap[$cKey])) {
                $mIdx = $colMediaMap[$cKey];
                $drawings[] = [
                    'col'        => $colIdx,
                    'row'        => 1, // 0-based Row 2
                    'media_idx'  => $mIdx,
                    'width_px'   => $mediaFiles[$mIdx]['width'],
                    'height_px'  => $mediaFiles[$mIdx]['height'],
                    'cell_w_px'  => 145,
                    'cell_h_px'  => 180,
                ];
            }
        }
        $rowsXml[] = $this->buildRowXml(2, 135, $imageRowCells);

        // Row 3: Dark Header Row (#18181B background, bold white text)
        $headerCells = [];
        foreach ($fixedCols as $i => $label) {
            $headerCells[] = [$i, $label, 's', 1];
        }
        foreach ($colKeys as $idx => $cKey) {
            $headerCells[] = [$fixedCount + $idx, $columns[$cKey]['label'], 's', 1];
        }
        $rowsXml[] = $this->buildRowXml(3, 34, $headerCells);

        // Rows 4+: Athlete Order Rows
        $currentRow = 4;
        foreach ($athleteRows as $aRow) {
            $cells = [
                [0, $aRow['first_name'], 's', 6],
                [1, $aRow['last_name'], 's', 6],
                [2, $aRow['gender'], 's', 7],
                // Highlight Jersey Name, Jersey Number, and Backpack Name in yellow (#FEF08A) when populated!
                [3, $aRow['jersey_name'], 's', $aRow['jersey_name'] !== '' ? 8 : 7],
                [4, $aRow['jersey_number'], 's', $aRow['jersey_number'] !== '' ? 8 : 7],
                [5, $aRow['backpack_name'], 's', $aRow['backpack_name'] !== '' ? 8 : 7],
                [6, $aRow['special_notes'], 's', $aRow['special_notes'] !== '' ? 8 : 5],
            ];

            foreach ($colKeys as $idx => $cKey) {
                $colIdx = $fixedCount + $idx;
                if (!empty($aRow['cells'][$cKey])) {
                    // Format size(s) ordered in this column for this athlete
                    $parts = [];
                    foreach ($aRow['cells'][$cKey] as $entry) {
                        $sz = $entry['size'];
                        $q = (int) $entry['qty'];
                        if ($sz === 'ONE SIZE' && $entry['type'] === 'backpack' && $aRow['backpack_name'] !== '') {
                            $parts[] = $q > 1 ? "{$q}x ({$aRow['backpack_name']})" : $aRow['backpack_name'];
                        } elseif ($sz === 'ONE SIZE') {
                            $parts[] = $q > 1 ? "Qty: {$q}" : '1';
                        } else {
                            $parts[] = $q > 1 ? "{$sz} (x{$q})" : $sz;
                        }
                    }
                    $cellVal = implode(', ', $parts);
                    // Style 3: Crisp White active cell with bold black text
                    $cells[] = [$colIdx, $cellVal, 's', 3];
                } else {
                    // Style 4: Dark Charcoal (#333333) empty cell matching Luke's chart
                    $cells[] = [$colIdx, '', 's', 4];
                }
            }

            $rowsXml[] = $this->buildRowXml($currentRow, 22, $cells);
            $currentRow++;
        }

        // Blank separator row
        $currentRow++;

        // Size Breakdown Matrix Summary Block at bottom of Sheet 1
        $summaryHeaderCells = [
            [0, 'SIZE BREAKDOWN SUMMARY', 's', 2],
            [1, '', 's', 2],
            [2, '', 's', 2],
            [3, '', 's', 2],
            [4, '', 's', 2],
            [5, 'SIZE', 's', 2],
            [6, 'TOTAL FOR SIZE', 's', 2],
        ];
        foreach ($colKeys as $idx => $cKey) {
            $summaryHeaderCells[] = [$fixedCount + $idx, $columns[$cKey]['label'], 's', 2];
        }
        $rowsXml[] = $this->buildRowXml($currentRow, 28, $summaryHeaderCells);
        $currentRow++;

        foreach ($allSizesUsed as $sizeStr) {
            $rowTotalForSize = 0;
            foreach ($colKeys as $cKey) {
                $rowTotalForSize += (int) ($sizeTotals[$cKey][$sizeStr] ?? 0);
            }

            $szCells = [
                [0, '', 's', 5],
                [1, '', 's', 5],
                [2, '', 's', 5],
                [3, '', 's', 5],
                [4, '', 's', 5],
                [5, $sizeStr, 's', 6],
                [6, $rowTotalForSize, 'n', 10],
            ];

            foreach ($colKeys as $idx => $cKey) {
                $colIdx = $fixedCount + $idx;
                $qty = (int) ($sizeTotals[$cKey][$sizeStr] ?? 0);
                if ($qty > 0) {
                    $szCells[] = [$colIdx, $qty, 'n', 3];
                } else {
                    $szCells[] = [$colIdx, '', 's', 4];
                }
            }

            $rowsXml[] = $this->buildRowXml($currentRow, 21, $szCells);
            $currentRow++;
        }

        // Grand Total Row per Column
        $totalCells = [
            [0, '', 's', 10],
            [1, '', 's', 10],
            [2, '', 's', 10],
            [3, '', 's', 10],
            [4, '', 's', 10],
            [5, 'TOTAL QUANTITY', 's', 10],
            [6, $totalUnits, 'n', 10],
        ];
        foreach ($colKeys as $idx => $cKey) {
            $totalCells[] = [$fixedCount + $idx, (int) $columns[$cKey]['total_qty'], 'n', 10];
        }
        $rowsXml[] = $this->buildRowXml($currentRow, 25, $totalCells);

        // Column widths
        $colWidths = [
            1 => 16, // First Name
            2 => 16, // Last Name
            3 => 15, // Gender
            4 => 18, // Jersey Name
            5 => 14, // Jersey Number
            6 => 18, // Backpack Name
            7 => 22, // Special Notes
        ];
        foreach ($colKeys as $idx => $cKey) {
            $colWidths[$fixedCount + $idx + 1] = 21;
        }

        return $this->finalizeWorksheetXml($rowsXml, $colWidths, $drawings, $addSharedString);
    }

    /**
     * Build Sheet 2: Quantity by Size Staircase Chart (matches Luke's top screenshot grouped by Female / Male).
     */
    protected function buildStaircaseAggregateSheet(
        string $title,
        array $columns,
        array $aggRows,
        array $sizeTotals,
        array $colMediaMap,
        array $mediaFiles,
        callable $addSharedString
    ): array {
        $colKeys = array_keys($columns);
        $fixedCols = ['Order Type', 'Gender', 'Qty'];
        $fixedCount = count($fixedCols); // 3 columns (A..C), product columns start at D (0-based index 3)

        $rowsXml = [];
        $drawings = [];

        // Row 1: Title Banner
        $totalUnits = array_sum(array_column($columns, 'total_qty'));
        $bannerText = strtoupper($title) . ' — QUANTITY BY SIZE & GENDER  |  Total Garment Units: ' . $totalUnits;
        $rowsXml[] = $this->buildRowXml(1, 28, [
            [0, $bannerText, 's', 9],
        ]);

        // Helper to emit the Tall Product Mockup Image Row (height 135pt = ~180px) at any 1-based row number
        $emitMockupImageRow = function (int $rowNum) use (&$rowsXml, &$drawings, $colKeys, $fixedCount, $colMediaMap, $mediaFiles): void {
            $imageRowCells = [
                [0, 'PRODUCT MOCKUPS', 's', 11],
                [1, '', 's', 11],
                [2, '', 's', 11],
            ];
            foreach ($colKeys as $idx => $cKey) {
                $colIdx = $fixedCount + $idx;
                $imageRowCells[] = [$colIdx, '', 's', 11];
                if (isset($colMediaMap[$cKey])) {
                    $mIdx = $colMediaMap[$cKey];
                    $drawings[] = [
                        'col'        => $colIdx,
                        'row'        => $rowNum - 1, // 0-based row index for Excel drawing anchor
                        'media_idx'  => $mIdx,
                        'width_px'   => $mediaFiles[$mIdx]['width'],
                        'height_px'  => $mediaFiles[$mIdx]['height'],
                        'cell_w_px'  => 145,
                        'cell_h_px'  => 180,
                    ];
                }
            }
            $rowsXml[] = $this->buildRowXml($rowNum, 135, $imageRowCells);
        };

        // Row 2: Tall Product Mockup Image Row at top of sheet
        $emitMockupImageRow(2);

        $currentRow = 3;

        // Render each Gender section with its own Product Mockups + Dark Navy Header Bar (#1E3A5F)
        if (empty($aggRows)) {
            $headerCells = [
                [0, 'Order Type', 's', 2],
                [1, 'Gender', 's', 2],
                [2, 'Qty', 's', 2],
            ];
            foreach ($colKeys as $idx => $cKey) {
                $headerCells[] = [$fixedCount + $idx, $columns[$cKey]['label'], 's', 2];
            }
            $rowsXml[] = $this->buildRowXml($currentRow, 32, $headerCells);
            $currentRow++;
        } else {
            $sectionIdx = 0;
            foreach ($aggRows as $genderLabel => $gRows) {
                // Repeat the Product Mockup Images row at the dividing line before Male (and any subsequent gender section)
                if ($sectionIdx > 0) {
                    $emitMockupImageRow($currentRow);
                    $currentRow++;
                }
                $sectionIdx++;

                // Dark Navy Section Header Row
                $headerCells = [
                    [0, 'Order Type', 's', 2],
                    [1, 'Gender', 's', 2],
                    [2, 'Qty', 's', 2],
                ];
                foreach ($colKeys as $idx => $cKey) {
                    $headerCells[] = [$fixedCount + $idx, $columns[$cKey]['label'], 's', 2];
                }
                $rowsXml[] = $this->buildRowXml($currentRow, 32, $headerCells);
                $currentRow++;

                foreach ($gRows as $gRow) {
                    $cells = [
                        [0, $gRow['order_type'], 's', 7],
                        [1, $gRow['gender'], 's', 7],
                        [2, (int) $gRow['qty'], 'n', 3],
                    ];

                    foreach ($colKeys as $idx => $cKey) {
                        $colIdx = $fixedCount + $idx;
                        if ($cKey === $gRow['col_key']) {
                            // White cell showing the Size under the active product image column!
                            $cells[] = [$colIdx, $gRow['size'], 's', 3];
                        } else {
                            // Dark charcoal (#333333) cell everywhere else
                            $cells[] = [$colIdx, '', 's', 4];
                        }
                    }

                    $rowsXml[] = $this->buildRowXml($currentRow, 21, $cells);
                    $currentRow++;
                }
            }
        }

        // Total Quantity Row at the bottom
        $totalCells = [
            [0, 'TOTAL QUANTITY', 's', 10],
            [1, '', 's', 10],
            [2, $totalUnits, 'n', 10],
        ];
        foreach ($colKeys as $idx => $cKey) {
            $totalCells[] = [$fixedCount + $idx, (int) $columns[$cKey]['total_qty'], 'n', 10];
        }
        $rowsXml[] = $this->buildRowXml($currentRow, 26, $totalCells);

        $colWidths = [
            1 => 16, // Order Type
            2 => 14, // Gender
            3 => 10, // Qty
        ];
        foreach ($colKeys as $idx => $cKey) {
            $colWidths[$fixedCount + $idx + 1] = 21;
        }

        return $this->finalizeWorksheetXml($rowsXml, $colWidths, $drawings, $addSharedString);
    }

    /**
     * Build Sheet 3: Full Order Roster (complete flat line-item export for reference).
     */
    protected function buildFullRosterSheet(
        string $title,
        array $flatRoster,
        callable $addSharedString
    ): array {
        $headers = [
            'Order ID',
            'Date',
            'First Name',
            'Last Name',
            'Gender / Cut',
            'Jersey Name',
            'Jersey Number',
            'Backpack Name',
            'Item Name',
            'Garment Type(s)',
            'Size(s)',
            'Quantity',
        ];

        $rowsXml = [];
        $headerCells = [];
        foreach ($headers as $i => $h) {
            $headerCells[] = [$i, $h, 's', 1];
        }
        $rowsXml[] = $this->buildRowXml(1, 28, $headerCells);

        $rowNum = 2;
        foreach ($flatRoster as $r) {
            $rowsXml[] = $this->buildRowXml($rowNum, 20, [
                [0, (int) $r['order_id'], 'n', 7],
                [1, $r['date'], 's', 7],
                [2, $r['first_name'], 's', 6],
                [3, $r['last_name'], 's', 6],
                [4, $r['gender'], 's', 7],
                [5, $r['jersey_name'], 's', $r['jersey_name'] !== '' ? 8 : 7],
                [6, $r['jersey_number'], 's', $r['jersey_number'] !== '' ? 8 : 7],
                [7, $r['backpack_name'], 's', $r['backpack_name'] !== '' ? 8 : 7],
                [8, $r['item_name'], 's', 5],
                [9, $r['types'], 's', 5],
                [10, $r['sizes'], 's', 6],
                [11, (int) $r['qty'], 'n', 3],
            ]);
            $rowNum++;
        }

        $colWidths = [
            1  => 11,
            2  => 13,
            3  => 16,
            4  => 16,
            5  => 15,
            6  => 18,
            7  => 14,
            8  => 18,
            9  => 28,
            10 => 24,
            11 => 28,
            12 => 11,
        ];

        return $this->finalizeWorksheetXml($rowsXml, $colWidths, [], $addSharedString);
    }

    /**
     * Check whether a single-piece catalog/store item can represent $targetGarmentType,
     * returning a positive type-match score or null if incompatible.
     * Handles cases where a Warm-up Top is categorized as 'jacket', 'hoodie', 'shirt_long', or 'uniform_top'
     * (e.g. "RLA Qt. Zip Jacket", "Elite Warm-up Top"), or Warm-up Bottom as 'pants'/'leggings'.
     */
    protected function matchSingleGarmentTypeScore(string $targetGarmentType, array $itemTypes, string $itemName): ?int
    {
        if (count($itemTypes) !== 1) {
            return null;
        }

        $actualType = (string) $itemTypes[0];
        $nameLower = strtolower(trim($itemName));
        $nameSaysWarmup = str_contains($nameLower, 'warm-up') || str_contains($nameLower, 'warmup') || str_contains($nameLower, 'warm up');
        $nameSaysTop = str_contains($nameLower, 'top') || str_contains($nameLower, 'jacket') || str_contains($nameLower, 'hoodie') || str_contains($nameLower, 'zip') || str_contains($nameLower, 'pullover');
        $nameSaysBottom = str_contains($nameLower, 'bottom') || str_contains($nameLower, 'jogger') || str_contains($nameLower, 'pant') || str_contains($nameLower, 'legging') || str_contains($nameLower, 'tight');

        if ($targetGarmentType === 'warmup_top') {
            if ($actualType === 'warmup_top') {
                return 30;
            }
            if ($nameSaysWarmup && !$nameSaysBottom && in_array($actualType, ['jacket', 'hoodie', 'shirt_long', 'uniform_top', 'shirt_short'], true)) {
                return 28;
            }
            if (in_array($actualType, ['jacket', 'hoodie'], true) && !str_contains($nameLower, 't-shirt') && !str_contains($nameLower, 'tee')) {
                return 22;
            }
            if ($actualType === 'shirt_long' && $nameSaysTop) {
                return 18;
            }
            return null;
        }

        if ($targetGarmentType === 'warmup_bottom') {
            if ($actualType === 'warmup_bottom') {
                return 30;
            }
            if ($nameSaysWarmup && !$nameSaysTop && in_array($actualType, ['pants', 'leggings', 'shorts', 'uniform_bottom'], true)) {
                return 28;
            }
            if (in_array($actualType, ['pants', 'leggings'], true) && $nameSaysBottom) {
                return 22;
            }
            return null;
        }

        if ($targetGarmentType === 'uniform_top') {
            // Prevent a Warm-up Top accidentally tagged as 'uniform_top' from matching as a Uniform Top
            if ($nameSaysWarmup) {
                return null;
            }
            return $actualType === 'uniform_top' ? 30 : null;
        }

        if ($targetGarmentType === 'uniform_bottom') {
            if ($nameSaysWarmup) {
                return null;
            }
            if ($actualType === 'uniform_bottom') {
                return 30;
            }
            if (in_array($actualType, ['shorts', 'leggings'], true)) {
                return 20;
            }
            return null;
        }

        if ($targetGarmentType === 'arm_sleeve') {
            if ($actualType === 'arm_sleeve') {
                return 30;
            }
            if ($actualType === 'accessory' && str_contains($nameLower, 'sleeve')) {
                return 28;
            }
            return null;
        }

        if ($targetGarmentType === 'shirt_short') {
            if ($actualType === 'shirt_short' && !$nameSaysWarmup) {
                return 30;
            }
            if ($actualType === 'hoodie' && (str_contains($nameLower, 't-shirt') || str_contains($nameLower, 'shirt'))) {
                return 25;
            }
            return null;
        }

        return $actualType === $targetGarmentType ? 30 : null;
    }

    /**
     * Find the best single-garment flat-lay image when a Package or Set is split into individual garment columns.
     */
    protected function findBestSingleGarmentImage(
        string $garmentType,
        string $parentItemName,
        string $gender,
        Collection $storeItems,
        ?int $collectionId,
        Collection $allDesigns
    ): ?string {
        $candidates = [];
        $parentPrefix = strtolower((string) strtok(trim($parentItemName), " \t\n\r-—_"));

        // 1. Check single-type items in the current store first
        foreach ($storeItems as $si) {
            $types = is_array($si->types) ? $si->types : ($si->type ? [$si->type] : []);
            $typeScore = $this->matchSingleGarmentTypeScore($garmentType, $types, (string) $si->name);
            if ($typeScore !== null) {
                $paths = !empty($si->image_paths) ? $si->image_paths : ($si->image_url ? [$si->image_url] : ($si->designCatalog?->image_paths ?? []));
                $img = $this->pickFlatLayImage($paths);
                if ($img) {
                    $candidates[] = ['name' => (string) $si->name, 'image' => $img, 'source_bonus' => 20 + $typeScore];
                }
            }
        }

        // 2. Check single-type designs in DesignCatalog (same DesignCollection or matching brand/team prefix)
        foreach ($allDesigns as $d) {
            $sameCollection = $collectionId && (int) $d->design_collection_id === (int) $collectionId;
            $dPrefix = strtolower((string) strtok(trim((string) $d->name), " \t\n\r-—_"));
            $samePrefix = strlen($parentPrefix) >= 3 && $dPrefix === $parentPrefix;

            if (!$sameCollection && !$samePrefix) {
                continue;
            }

            $types = is_array($d->types) ? $d->types : ($d->type ? [$d->type] : []);
            $typeScore = $this->matchSingleGarmentTypeScore($garmentType, $types, (string) $d->name);
            if ($typeScore !== null) {
                $paths = !empty($d->image_paths) ? $d->image_paths : ($d->image_url ? [$d->image_url] : []);
                $img = $this->pickFlatLayImage($paths);
                if ($img) {
                    $colBonus = $sameCollection ? 15 : 8;
                    $candidates[] = ['name' => (string) $d->name, 'image' => $img, 'source_bonus' => $colBonus + $typeScore];
                }
            }
        }

        $parentLower = preg_replace('/#\s+(\d+)/', '#$1', strtolower($parentItemName)) ?? strtolower($parentItemName);
        $isPackage = str_contains($parentLower, 'package') || str_contains($parentLower, 'pkg') || str_contains($parentLower, 'bundle');

        // 3. If this is a multi-piece Package (e.g. "Legacy Uni Package #1") and no 1-piece Warm-up or Uniform exists,
        // fall back to a dedicated 2-piece Set in the store/collection (e.g. "Legacy Warm-up #1 (Set)")
        // so the column shows the Warm-up Set or Uniform Set image instead of the 5-piece full Package collage!
        if (empty($candidates) && $isPackage) {
            foreach ($storeItems as $si) {
                if (strcasecmp(trim((string) $si->name), trim($parentItemName)) === 0) {
                    continue;
                }
                $types = is_array($si->types) ? $si->types : ($si->type ? [$si->type] : []);
                if (count($types) === 2 && in_array($garmentType, $types, true)) {
                    $paths = !empty($si->image_paths) ? $si->image_paths : ($si->image_url ? [$si->image_url] : ($si->designCatalog?->image_paths ?? []));
                    $img = $this->pickFlatLayImage($paths);
                    if ($img) {
                        $candidates[] = ['name' => (string) $si->name, 'image' => $img, 'source_bonus' => 25];
                    }
                }
            }
            foreach ($allDesigns as $d) {
                if (strcasecmp(trim((string) $d->name), trim($parentItemName)) === 0) {
                    continue;
                }
                $sameCollection = $collectionId && (int) $d->design_collection_id === (int) $collectionId;
                $dPrefix = strtolower((string) strtok(trim((string) $d->name), " \t\n\r-—_"));
                $samePrefix = strlen($parentPrefix) >= 3 && $dPrefix === $parentPrefix;
                if (!$sameCollection && !$samePrefix) {
                    continue;
                }
                $types = is_array($d->types) ? $d->types : ($d->type ? [$d->type] : []);
                if (count($types) === 2 && in_array($garmentType, $types, true)) {
                    $paths = !empty($d->image_paths) ? $d->image_paths : ($d->image_url ? [$d->image_url] : []);
                    $img = $this->pickFlatLayImage($paths);
                    if ($img) {
                        $candidates[] = ['name' => (string) $d->name, 'image' => $img, 'source_bonus' => 15];
                    }
                }
            }
        }

        if (empty($candidates)) {
            return null;
        }

        // If this is a multi-piece Package (e.g. "Legacy Uni Package #1") and the club has multiple numbered
        // variations for this garment type (e.g. Uni #1 through Uni #7), do not guess which variation is inside
        // the package based on the package number (#1 != Uni #1); fall back to the Package's own mockup image.
        if ($isPackage) {
            $distinctCandNums = [];
            foreach ($candidates as $cand) {
                $cName = preg_replace('/#\s+(\d+)/', '#$1', strtolower($cand['name'])) ?? strtolower($cand['name']);
                if (preg_match('/#(\d+)/', $cName, $cm)) {
                    $distinctCandNums[$cm[1]] = true;
                }
            }
            if (count($distinctCandNums) > 1) {
                return null;
            }
        }

        // Extract specific design number (#1, #2, #6, #7, etc.) if present on the parent item
        $parentNum = null;
        if (!$isPackage && preg_match('/#(\d+)/', $parentLower, $m)) {
            $parentNum = $m[1];
        }

        // Score candidates by keyword similarity with parent item name & gender
        $genderLower = strtolower($gender);
        $isFemale = str_contains($genderLower, 'fem') || str_contains($genderLower, 'girl') || str_contains($genderLower, 'wom') || str_contains($parentLower, 'racerback') || str_contains($parentLower, 'female') || str_contains($parentLower, 'girl');
        $isMale = (!$isFemale) && (str_contains($genderLower, 'male') || str_contains($genderLower, 'boy') || str_contains($genderLower, 'men') || str_contains($parentLower, 'tank') || str_contains($parentLower, 'male'));

        $styleKeywords = ['racerback', 'tank', 'spandex', 'compression', 'jogger', 'hoodie', 'zipper', 'qt. zip', 'quarter zip', 'pullover', 'drifit', 'alt', 'alternate', 'primary', 'secondary'];

        $bestImg = null;
        $bestScore = -999;

        foreach ($candidates as $cand) {
            $cName = preg_replace('/#\s+(\d+)/', '#$1', strtolower($cand['name'])) ?? strtolower($cand['name']);
            $candNum = null;
            if (preg_match('/#(\d+)/', $cName, $cm)) {
                $candNum = $cm[1];
            }

            // If the parent is a numbered Uniform Set (not a Package, e.g. "Legacy Uni #6 Set" or "Legacy Uni #1 Set"),
            // do NOT pull a single garment from a different uniform number (#1 vs #6)!
            if (!$isPackage && $parentNum !== null && in_array($garmentType, ['uniform_top', 'uniform_bottom'], true)) {
                if ($candNum !== $parentNum) {
                    continue;
                }
            }

            $score = $cand['source_bonus'];

            if ($parentNum !== null && $candNum === $parentNum) {
                $score += 45;
            }

            foreach ($styleKeywords as $kw) {
                if (str_contains($parentLower, $kw) && str_contains($cName, $kw)) {
                    $score += 30;
                }
            }

            // Bonus when the candidate explicitly says 'top' for *_top or 'bottom' for *_bottom
            if (str_ends_with($garmentType, '_top') && (str_contains($cName, 'top') || str_contains($cName, 'jacket') || str_contains($cName, 'hoodie'))) {
                $score += 10;
            } elseif (str_ends_with($garmentType, '_bottom') && (str_contains($cName, 'bottom') || str_contains($cName, 'jogger') || str_contains($cName, 'legging') || str_contains($cName, 'pant'))) {
                $score += 10;
            }

            $candIsFemale = str_contains($cName, 'female') || str_contains($cName, 'girl') || str_contains($cName, 'racerback');
            $candIsMale = (str_contains($cName, 'male') && !str_contains($cName, 'female')) || str_contains($cName, 'boy') || str_contains($cName, 'tank');

            if ($isFemale && $candIsFemale) {
                $score += 15;
            } elseif ($isFemale && $candIsMale) {
                $score -= 15;
            } elseif ($isMale && $candIsMale) {
                $score += 15;
            } elseif ($isMale && $candIsFemale) {
                $score -= 15;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestImg = $cand['image'];
            }
        }

        return $bestImg;
    }

    /**
     * Pick the flat-lay manufacturer mockup from an array of image_paths.
     * In Commission Apparel's catalog convention, image_paths[0] is the model photo
     * and the LAST image (end($image_paths)) is the flat-lay manufacturer technical mockup.
     */
    protected function pickFlatLayImage(array $paths): ?string
    {
        if (empty($paths)) {
            return null;
        }
        $reversed = array_reverse(array_values(array_filter($paths)));
        foreach ($reversed as $p) {
            if ($this->resolveLocalImagePath($p) !== null) {
                return $p;
            }
        }
        return $reversed[0] ?? null;
    }

    /**
     * Resolve a relative/storage image URL or path to an absolute local file path.
     */
    protected function resolveLocalImagePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $trimmed = ltrim(trim($path), '/\\');

        if (str_starts_with($trimmed, 'storage/')) {
            $rel = substr($trimmed, 8);
            $candidates = [
                storage_path('app/public/' . $rel),
                public_path($trimmed),
            ];
        } else {
            $candidates = [
                public_path($trimmed),
                storage_path('app/public/' . $trimmed),
            ];
        }

        foreach ($candidates as $cand) {
            if (is_file($cand) && filesize($cand) > 0) {
                return $cand;
            }
        }

        return null;
    }

    /**
     * Convert any local image (WebP, PNG, JPEG, GIF) into a clean, resized PNG thumbnail for Excel embedding.
     */
    protected function createPngThumbnail(string $sourcePath, int $maxW = 260, int $maxH = 320): ?array
    {
        if (!extension_loaded('gd') || !is_file($sourcePath)) {
            return null;
        }

        $info = @getimagesize($sourcePath);
        if (!$info || empty($info[0]) || empty($info[1])) {
            return null;
        }

        $origW = (int) $info[0];
        $origH = (int) $info[1];
        $mime = $info['mime'] ?? '';

        $src = match ($mime) {
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : null,
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/gif'  => @imagecreatefromgif($sourcePath),
            default      => @imagecreatefromstring((string) @file_get_contents($sourcePath)),
        };

        if (!$src) {
            return null;
        }

        $scale = min($maxW / $origW, $maxH / $origH, 1.0);
        $newW = max(1, (int) round($origW * $scale));
        $newH = max(1, (int) round($origH * $scale));

        $dst = imagecreatetruecolor($newW, $newH);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagealphablending($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_img_') . '.png';
        imagepng($dst, $tmpFile, 6);

        imagedestroy($src);
        imagedestroy($dst);

        if (!is_file($tmpFile) || filesize($tmpFile) === 0) {
            return null;
        }

        return [
            'path'   => $tmpFile,
            'width'  => $newW,
            'height' => $newH,
        ];
    }

    /**
     * Normalize gender strings into clean categories for the Staircase Aggregate sheet.
     */
    protected function normalizeGenderLabel(string $gender): string
    {
        $g = strtolower(trim($gender));
        if (str_contains($g, 'fem') || str_contains($g, 'girl') || str_contains($g, 'wom')) {
            return 'Female';
        }
        if (str_contains($g, 'male') || str_contains($g, 'boy') || str_contains($g, 'men')) {
            return 'Male';
        }
        return 'Unisex';
    }

    /**
     * Compare two garment size labels according to standard Youth -> Adult progression.
     */
    protected function compareSizes(string $a, string $b): int
    {
        $ua = strtoupper(trim($a));
        $ub = strtoupper(trim($b));
        $wa = self::$sizeOrder[$ua] ?? 50;
        $wb = self::$sizeOrder[$ub] ?? 50;
        if ($wa !== $wb) {
            return $wa <=> $wb;
        }
        return strcmp($ua, $ub);
    }

    protected function normalizeKey(string $str): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($str)) ?? '';
    }

    /**
     * Build a single <row> array representation for later XML serialization.
     */
    protected function buildRowXml(int $rowNumber, float $heightPt, array $cells): array
    {
        return [
            'r'     => $rowNumber,
            'ht'    => $heightPt,
            'cells' => $cells,
        ];
    }

    /**
     * Convert structured rows, column widths, and drawings into worksheet XML + drawing XML.
     */
    protected function finalizeWorksheetXml(
        array $rows,
        array $colWidths,
        array $drawings,
        callable $addSharedString
    ): array {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
        $xml .= '<sheetFormatPr defaultRowHeight="20"/>';

        if (!empty($colWidths)) {
            $xml .= '<cols>';
            foreach ($colWidths as $colNum => $width) {
                $xml .= '<col min="' . $colNum . '" max="' . $colNum . '" width="' . $width . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        foreach ($rows as $row) {
            $rNum = $row['r'];
            $ht = $row['ht'];
            $xml .= '<row r="' . $rNum . '" ht="' . $ht . '" customHeight="1">';
            foreach ($row['cells'] as $c) {
                [$colIdx, $val, $type, $styleIdx] = $c;
                $cellRef = $this->colLetter($colIdx) . $rNum;
                if ($type === 'n' && is_numeric($val)) {
                    $xml .= '<c r="' . $cellRef . '" s="' . $styleIdx . '"><v>' . $val . '</v></c>';
                } else {
                    $strVal = (string) $val;
                    if ($strVal === '') {
                        $xml .= '<c r="' . $cellRef . '" s="' . $styleIdx . '"/>';
                    } else {
                        $sIdx = $addSharedString($strVal);
                        $xml .= '<c r="' . $cellRef . '" t="s" s="' . $styleIdx . '"><v>' . $sIdx . '</v></c>';
                    }
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';

        if (!empty($drawings)) {
            $xml .= '<drawing r:id="rId1"/>';
        }

        $xml .= '</worksheet>';

        $drawingXml = '';
        $drawingRelsXml = '';

        if (!empty($drawings)) {
            $drawingXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            $drawingXml .= '<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">';

            $drawingRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
            $drawingRelsXml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

            $relMap = [];
            $nextRelId = 1;

            foreach ($drawings as $dIdx => $d) {
                $mIdx = $d['media_idx'];
                if (!isset($relMap[$mIdx])) {
                    $rId = 'rId' . $nextRelId++;
                    $relMap[$mIdx] = $rId;
                    $drawingRelsXml .= '<Relationship Id="' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image' . $mIdx . '.png"/>';
                }
                $rId = $relMap[$mIdx];

                // Fit image proportionally inside cell padding (max 132x168 px)
                $maxDispW = 132;
                $maxDispH = 168;
                $imgW = max(1, (int) $d['width_px']);
                $imgH = max(1, (int) $d['height_px']);
                $scale = min($maxDispW / $imgW, $maxDispH / $imgH);
                $dispW = max(20, (int) round($imgW * $scale));
                $dispH = max(20, (int) round($imgH * $scale));

                $cx = $dispW * 9525;
                $cy = $dispH * 9525;

                $colOff = max(47625, (int) round((($d['cell_w_px'] - $dispW) / 2) * 9525));
                $rowOff = max(47625, (int) round((($d['cell_h_px'] - $dispH) / 2) * 9525));

                $picId = $dIdx + 1;
                $drawingXml .= '<xdr:oneCellAnchor>';
                $drawingXml .= '<xdr:from><xdr:col>' . $d['col'] . '</xdr:col><xdr:colOff>' . $colOff . '</xdr:colOff><xdr:row>' . $d['row'] . '</xdr:row><xdr:rowOff>' . $rowOff . '</xdr:rowOff></xdr:from>';
                $drawingXml .= '<xdr:ext cx="' . $cx . '" cy="' . $cy . '"/>';
                $drawingXml .= '<xdr:pic>';
                $drawingXml .= '<xdr:nvPicPr><xdr:cNvPr id="' . $picId . '" name="Mockup ' . $picId . '"/><xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr></xdr:nvPicPr>';
                $drawingXml .= '<xdr:blipFill><a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="' . $rId . '"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>';
                $drawingXml .= '<xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>';
                $drawingXml .= '</xdr:pic>';
                $drawingXml .= '<xdr:clientData/>';
                $drawingXml .= '</xdr:oneCellAnchor>';
            }

            $drawingXml .= '</xdr:wsDr>';
            $drawingRelsXml .= '</Relationships>';
        }

        return [
            'xml'            => $xml,
            'drawings'       => $drawings,
            'drawingXml'     => $drawingXml,
            'drawingRelsXml' => $drawingRelsXml,
        ];
    }

    /**
     * Convert 0-based column index to Excel column letter (A, B, ..., Z, AA, AB...).
     */
    protected function colLetter(int $colIdx): string
    {
        $letter = '';
        $n = $colIdx + 1;
        while ($n > 0) {
            $rem = ($n - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $n = intdiv($n - 1, 26);
        }
        return $letter;
    }

    protected function buildContentTypesXml(bool $hasDrawing1, bool $hasDrawing2, bool $hasPng): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml" ContentType="application/xml"/>';
        if ($hasPng) {
            $xml .= '<Default Extension="png" ContentType="image/png"/>';
        }
        $xml .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $xml .= '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $xml .= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
        if ($hasDrawing1) {
            $xml .= '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
        }
        if ($hasDrawing2) {
            $xml .= '<Override PartName="/xl/drawings/drawing2.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
        }
        $xml .= '</Types>';
        return $xml;
    }

    protected function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    protected function buildWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>'
            . '<sheet name="Quantity by Size Chart" sheetId="1" r:id="rId1"/>'
            . '<sheet name="Full Order Roster" sheetId="2" r:id="rId2"/>'
            . '</sheets>'
            . '</workbook>';
    }

    protected function buildWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>';
    }

    protected function buildSheetRelsXml(int $drawingIdx): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing' . $drawingIdx . '.xml"/>'
            . '</Relationships>';
    }

    protected function buildSharedStringsXml(array $strings): string
    {
        $count = count($strings);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $count . '" uniqueCount="' . $count . '">';
        foreach ($strings as $s) {
            $escaped = htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $xml .= '<si><t xml:space="preserve">' . $escaped . '</t></si>';
        }
        $xml .= '</sst>';
        return $xml;
    }

    protected function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="6">'
            // 0: Default 10pt
            . '<font><sz val="10"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
            // 1: Bold 10pt White (Headers)
            . '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            // 2: Bold 11pt Black (Active matrix cells & totals)
            . '<font><b/><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/></font>'
            // 3: Bold 12pt White (Banner)
            . '<font><b/><sz val="12"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            // 4: Bold 10pt Dark (Labels/Names)
            . '<font><b/><sz val="10"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
            // 5: Bold 10pt Muted
            . '<font><b/><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="9">'
            // 0 & 1: Mandatory Excel fills
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            // 2: #18181B Dark Black Header Bar
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF18181B"/><bgColor indexed="64"/></patternFill></fill>'
            // 3: #1E3A5F Dark Navy Header Bar
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A5F"/><bgColor indexed="64"/></patternFill></fill>'
            // 4: #333333 Dark Charcoal Empty Matrix Cell
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF333333"/><bgColor indexed="64"/></patternFill></fill>'
            // 5: #FFFFFF Crisp White Active Matrix Cell
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>'
            // 6: #FEF08A Bright Yellow Highlight for Personalizations (Jersey Name/Number/Backpack Name)
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFEF08A"/><bgColor indexed="64"/></patternFill></fill>'
            // 7: #F8FAFC Light Slate
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>'
            // 8: #DCFCE7 Light Green Totals
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border>'
            . '<left style="thin"><color rgb="FF64748B"/></left>'
            . '<right style="thin"><color rgb="FF64748B"/></right>'
            . '<top style="thin"><color rgb="FF64748B"/></top>'
            . '<bottom style="thin"><color rgb="FF64748B"/></bottom>'
            . '<diagonal/>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="12">'
            // 0: Default
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            // 1: Dark Black Header
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            // 2: Dark Navy Header
            . '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            // 3: Active White Matrix Cell (Bold 11pt Black centered)
            . '<xf numFmtId="0" fontId="2" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            // 4: Empty Dark Charcoal Matrix Cell (#333333)
            . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 5: Left Text Cell
            . '<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            // 6: Left Bold Cell
            . '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
            // 7: Center Regular Cell
            . '<xf numFmtId="0" fontId="0" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            // 8: Yellow Highlighted Personalization Cell
            . '<xf numFmtId="0" fontId="2" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            // 9: Title Banner Cell
            . '<xf numFmtId="0" fontId="3" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            // 10: Totals Summary Cell (Green highlight)
            . '<xf numFmtId="0" fontId="2" fillId="8" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 11: Image Row Background Cell
            . '<xf numFmtId="0" fontId="5" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
