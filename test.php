<?php
$sessions = DB::table('sessions')->get();
foreach($sessions as $s) {
    $payload = unserialize(base64_decode($s->payload));
    if(isset($payload['pif_import_data'])) {
        file_put_contents('pif_data.json', json_encode($payload['pif_import_data']));
        echo "Found session!\n";
        break;
    }
}
