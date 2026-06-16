<?php

namespace App\Providers;

use App\Support\Dates;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'bs'));

        Carbon::macro('display', function (bool $withTime = false) {
            return Dates::format($this, $withTime);
        });

        Blade::directive('date', function (string $expression) {
            return "<?php echo e(\\App\\Support\\Dates::formatOr($expression)); ?>";
        });
    }
}
