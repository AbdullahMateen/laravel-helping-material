<?php

namespace AbdullahMateen\LaravelHelpingMaterial;

use AbdullahMateen\LaravelHelpingMaterial\Commands\LhmMakeEnumCommand;
use AbdullahMateen\LaravelHelpingMaterial\Commands\LhmMakeModelCommand;
use AbdullahMateen\LaravelHelpingMaterial\Commands\LhmPublishCommand;
use AbdullahMateen\LaravelHelpingMaterial\Middleware\AuthorizationMiddleware;
use AbdullahMateen\LaravelHelpingMaterial\Services\Media\MediaService;
use AbdullahMateen\LaravelHelpingMaterial\Traits\Api\ApiResponseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class LaravelHelpingMaterialServiceProvider extends ServiceProvider
{
    use ApiResponseTrait;

    /**
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/lhm.php' => config_path('lhm.php'),
        ], 'laravel-helping-material-config');

        $this->publishes([
            __DIR__ . '/migrations' => database_path('migrations'),
        ], 'laravel-helping-material-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        $this->registerDirectories();
        $this->registerDirectives();
        $this->registerMacros();
        $this->registerCommands();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/lhm.php', 'lhm'
        );

        Model::shouldBeStrict(config('lhm.models.should_be_strict'));

        if (function_exists('get_morphs_maps')) {
            Relation::enforceMorphMap(get_morphs_maps());
        }

        $this->app['router']->aliasMiddleware('authorize', AuthorizationMiddleware::class);

        $this->registerFacades();
    }

    /**
     * @return void
     */
    private function registerDirectories(): void
    {
        $folder = (string) config('lhm.storage.folder', 'storage');

        if ($folder === '') {
            return;
        }

        File::ensureDirectoryExists(public_path($folder));
    }

    /**
     * @return void
     */
    private function registerDirectives(): void
    {
        Blade::directive('hasError', function ($keys) {
            return "<?php
                \$fields = explode(',', $keys);
                foreach (\$fields as \$key) {
                    if (\$errors->has(\$key)) {
                        echo 'is-invalid';
                        break;
                    }
                }
            ?>";
        });
        Blade::directive('showError', function ($keys) {
            return "<?php
                \$fields = explode(',', $keys);
                foreach (\$fields as \$key) {
                    if (\$errors->has(\$key)) {
                        echo '<span class=\"invalid-feedback d-block\" role=\"alert\"><strong>'. \$errors->first(\$key) .'</strong></span>';
                        break;
                    }
                }
            ?>";
        });
    }

    /**
     * @return void
     */
    private function registerFacades(): void
    {
        $this->app->bind(MediaService::class, function () {
            return new MediaService();
        });

        $this->app->bind('MediaService', function ($app) {
            return $app->make(MediaService::class);
        });
    }

    /**
     * @return void
     */
    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->app->singleton(
            'command.lhm.publish',
            function ($app) {
                return new LhmPublishCommand($app['files']);
            }
        );
        $this->commands(array_filter([
            version_compare($this->app->version(), '10.0.0', '>=') ? LhmMakeEnumCommand::class : null,
            LhmMakeModelCommand::class,
            'command.lhm.publish',
        ]));
    }

    private function registerMacros(): void
    {
        $this->generalMacros();
        $this->authMacros();
    }

    private function generalMacros(): void
    {
        $that = $this;

        Response::macro('response', function (
            $response = HttpFoundationResponse::HTTP_OK,
            $message = '',
            $data = [],
            $errors = [],
            $source = null,
        ) use ($that) {
            return $that->response($response, __($message), $data, $errors, $source);
        });

        Response::macro('everythingOK', function (
            $message,
        ) {
            return response()->response(HttpFoundationResponse::HTTP_OK, __($message));
        });

        Response::macro('invalid', function (
            $message = 'Invalid data provided',
        ) {
            return response()->response(HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY, __($message));
        });
    }

    private function authMacros(): void
    {
        Response::macro('unauthenticated', function (
            $message = 'unauthenticated',
        ) {
            return response()->response(HttpFoundationResponse::HTTP_UNAUTHORIZED, __($message));
        });

        Response::macro('loginAttemptFailed', function (
            $message = 'These credentials do not match our records.',
        ) {
            return response()->response(HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY, __($message), [], [
                'email' => [__($message)],
            ]);
        });

        Response::macro('authNotFound', function (
            $message = 'User not found',
        ) {
            return response()->response(HttpFoundationResponse::HTTP_UNAUTHORIZED, __($message));
        });

        Response::macro('refreshToken', function (
            $data = [],
            $message = 'Token refreshed successfully',
        ) {
            return response()->response(HttpFoundationResponse::HTTP_OK, __($message), $data);
        });

        Response::macro('loggedIn', function (
            $data = [],
            $message = 'Logged In Successfully',
        ) {
            return response()->response(HttpFoundationResponse::HTTP_OK, __($message), $data);
        });

        Response::macro('loggedOut', function (
            $message = 'Logged out Successfully',
        ) {
            return response()->response(HttpFoundationResponse::HTTP_OK, __($message));
        });
    }

}
