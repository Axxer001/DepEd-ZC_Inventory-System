<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$fks = Illuminate\Support\Facades\Schema::getForeignKeys('items');
foreach($fks as $fk) {
    echo $fk['name'] . "\n";
}
