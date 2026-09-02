<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$indexes = DB::select("SHOW INDEXES FROM orders");
foreach ($indexes as $idx) {
    echo $idx->Key_name . " -> " . $idx->Column_name . "\n";
}
