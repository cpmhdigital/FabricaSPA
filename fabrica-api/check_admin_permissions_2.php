<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('email', 'admin@fabrica.local')->first();
if (!$user) {
    echo "USER_NOT_FOUND\n";
    exit(0);
}
echo 'email=' . $user->email . PHP_EOL;
echo 'roles=' . implode(',', $user->getRoleNames()->toArray()) . PHP_EOL;
echo 'direct_permissions_count=' . $user->getDirectPermissions()->count() . PHP_EOL;
echo 'all_permissions_count=' . $user->getAllPermissions()->count() . PHP_EOL;
