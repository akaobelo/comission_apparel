<?php
$db = new SQLite3('database/database.sqlite');
$results = $db->query("SELECT id, team_store_id, batch_id, is_archived, status FROM parent_orders LIMIT 50");
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    echo "ID: {$row['id']}, Store ID: " . ($row['team_store_id'] ?? 'NULL') . ", Batch: " . ($row['batch_id'] ?? 'NULL') . ", Archived: {$row['is_archived']}, Status: {$row['status']}\n";
}







