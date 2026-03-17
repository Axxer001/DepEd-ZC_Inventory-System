<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('items');
print_r($columns);
$columnsSub = \Illuminate\Support\Facades\Schema::getColumnListing('sub_items');
print_r($columnsSub);
