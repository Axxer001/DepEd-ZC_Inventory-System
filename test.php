$c = app(\App\Http\Controllers\InventorySetupController::class);
$r = $c->getUnassignedAssets(request());
file_put_contents('test_out.txt', json_encode($r->getData(true)['assets'] ?? []));
