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

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        $this->publishes([
            __DIR__ . '/migrations' => database_path('migrations'),
        ], 'laravel-helping-material-migrations');

        $this->registerFacades();
        $this->registerCommands();

        $this->registerDirectories();
        $this->registerDirectives();

        $this->registerMacros();
    }

    /**
     * @return void
     */
    private function registerDirectories(): void
    {
        $folder = config('lhm.storage.folder');
        if (!File::exists(public_path($folder))) {
            File::makeDirectory(public_path($folder), 0777, true);
        }
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
        $this->app->bind('MediaService', function () {
            return new MediaService();
        });
    }

    /**
     * @return void
     */
    private function registerCommands(): void
    {
        $this->app->singleton(
            'command.lhm.publish',
            function ($app) {
                return new LhmPublishCommand($app['files']);
            }
        );
        $this->commands(array_filter([
            $this->app->version()[0] >= 10 ? LhmMakeEnumCommand::class : null,
            LhmMakeModelCommand::class,
            'command.lhm.publish',
        ]));
    }

    private function registerMacros()
    {
        $this->generalMacros();
        $this->authMacros();
    }

    private function generalMacros() {
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

    private function authMacros()
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
