<?php
require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $tablesResult = DB::select('SHOW TABLES');
    $tables = [];
    foreach($tablesResult as $t) {
        $tables[] = array_values((array)$t)[0];
    }
    
    $schema = [];
    foreach($tables as $table) {
        $columns = DB::select("SHOW COLUMNS FROM `$table`");
        $colDetails = [];
        foreach($columns as $col) {
            $colDetails[$col->Field] = $col->Type . ($col->Null == 'YES' ? ' (NULL)' : '') . ($col->Key ? ' ('.$col->Key.')' : '');
        }
        $schema[$table] = $colDetails;
    }
    file_put_contents(dirname(__DIR__).'/scratch/schema_output.json', json_encode($schema, JSON_PRETTY_PRINT));
    echo "Done";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
