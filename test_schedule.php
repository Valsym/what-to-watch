<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "Testing schedule:list...\n";
$kernel->call('schedule:list');

echo "Testing schedule:run...\n";
$kernel->call('schedule:run');

echo "Output:\n" . $kernel->output();
