require 'vendor/autoload.php';
 = require 'bootstrap/app.php';
 = ->make(Illuminate\Contracts\Console\Kernel::class);
->bootstrap();
foreach (App\Models\User::with('roles')->orderBy('email')->get() as ) {
    echo ->email . ' | roles=' . implode(',', ->getRoleNames()->toArray()) . ' | perms=' . implode(',', ->getAllPermissions()->pluck('name')->toArray()) . PHP_EOL;
}
