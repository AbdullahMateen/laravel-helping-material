<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/*
|--------------------------------------------------------------------------
| Application/System Related Helper Functions
|--------------------------------------------------------------------------
*/

/* ==================== Env ==================== */

if (!function_exists('is_production')) {
    /**
     * @return bool
     */
    function is_production(): bool
    {
        return in_array(strtolower(config('app.env')), ['prod', 'production']);
    }
}

if (!function_exists('is_staging')) {
    /**
     * @return bool
     */
    function is_staging(): bool
    {
        return in_array(strtolower(config('app.env')), ['dev', 'development', 'stg', 'staging']);
    }
}

if (!function_exists('is_local')) {
    /**
     * @return bool
     */
    function is_local(): bool
    {
        return strtolower(config('app.env')) === 'local';
    }
}

if (!function_exists('is_testing')) {
    /**
     * @return bool
     */
    function is_testing(): bool
    {
        return in_array(strtolower(config('app.env')), ['test', 'testing']);
    }
}

if (!function_exists('is_debug_mode')) {
    /**
     * @return bool
     */
    function is_debug_mode(): bool
    {
        return config('app.debug', false);
    }
}


/* ==================== Config ==================== */

if (!function_exists('app_name')) {
    /**
     * @param string $default
     *
     * @return string
     */
    function app_name(string $default = 'Website'): string
    {
        return config('app.name', $default);
    }
}

if (!function_exists('app_full_name')) {
    /**
     * @param string $default
     *
     * @return string
     */
    function app_full_name(string $default = 'Website'): string
    {
        return config('app.full_name', $default);
    }
}

if (!function_exists('app_company_name')) {
    /**
     * @param string $default
     *
     * @return string
     */
    function app_company_name(string $default = 'Website'): string
    {
        return config('app.company_name', $default);
    }
}

if (!function_exists('app_url')) {
    /**
     * @param string|null $path
     *
     * @return string
     */
    function app_url(?string $path = null): string
    {
        $url = rtrim(config('app.url'), '/');
        return isset($path) ? sprintf("$url/%s", ltrim($path, '/')) : $url;
    }
}

if (!function_exists('app_asset_url')) {
    /**
     * @param string|null $path
     *
     * @return string
     */
    function app_asset_url(?string $path = null): string
    {
        $url = rtrim(config('app.asset_url') ?? app_url(), '/');
        return isset($path) ? sprintf("$url/%s", ltrim($path, '/')) : $url;
    }
}

if (!function_exists('app_domain')) {
    /**
     * @param string $default
     *
     * @return string
     */
    function app_domain(string $default = '127.0.0.1:8000'): string
    {
        return config('app.domain', $default);
    }
}

if (!function_exists('app_timezone')) {
    /**
     * @param string $default
     *
     * @return string
     */
    function app_timezone(string $default = 'UTC'): string
    {
        return config('app.timezone', $default);
    }
}

if (!function_exists('app_locale')) {
    /**
     * @param string $default
     *
     * @return string
     */
    function app_locale(string $default = 'en'): string
    {
        return config('app.locale', $default);
    }
}


/* ==================== Routes ==================== */

if (!function_exists('get_route_name_from_url')) {
    /**
     * @param string|Request $url
     * @param string         $method
     *
     * @return string
     */
    function get_route_name_from_url(string|Request $url, string $method = 'get'): string
    {
        try {
            if ($url instanceof Request) {
                $method = $url->getMethod();
                $url    = URL::current();
            }
            $request = app('router')->getRoutes()->match(app('request')->create($url, $method));
            return $request->getName();
        } catch (Exception) {
            return '';
        }
    }
}

if (!function_exists('route_url_to_name')) {
    /**
     * @param string|Request $url
     * @param string         $method
     *
     * @return string
     */
    function route_url_to_name(string|Request $url, string $method = 'get'): string
    {
        return get_route_name_from_url($url, $method);
    }
}

if (!function_exists('is_route_name_exists')) {
    /**
     * @param string $routeName
     *
     * @return false
     */
    function is_route_name_exists(string $routeName): bool
    {
        try {
            return Route::has($routeName);
        } catch (Exception) {
            return false;
        }
    }
}

if (!function_exists('get_current_route_name')) {
    /**
     * @return string|null
     */
    function get_current_route_name(): ?string
    {
        return Route::currentRouteName();
    }
}

if (!function_exists('is_current_route')) {
    /**
     * @param string $routeName
     *
     * @return bool
     */
    function is_current_route(string $routeName): bool
    {
        return get_current_route_name() === $routeName;
    }
}

if (!function_exists('is_route')) {
    /**
     * @param string $name
     *
     * @return bool
     */
    function is_route(string $name): bool
    {
        return is_current_route($name);
    }
}

if (!function_exists('is_current_route_in')) {
    /**
     * @param array|string $routeNames
     *
     * @return bool
     */
    function is_current_route_in(array|string $routeNames): bool
    {
        $routeNames = is_array($routeNames) ? $routeNames : explode(',', $routeNames);
        return in_array(get_current_route_name(), $routeNames, true);
    }
}

if (!function_exists('is_route_url')) {
    /**
     * @param string $wildCardURL
     *
     * @return bool
     */
    function is_route_url(string $wildCardURL): bool
    {
        return (new Illuminate\Http\Request)->is($wildCardURL);
    }
}

if (!function_exists('clear_intended_url')) {
    /**
     * @return void
     */
    function clear_intended_url(): void
    {
        session()->forget('url.intended');
    }
}

if (!function_exists('goto_route_encrypt')) {
    /**
     * @param string $routeName
     * @param array  $parameters
     *
     * @return string|null
     */
    function goto_route_encrypt(string $routeName, array $parameters = []): string|null
    {
        try {
            if (!is_route_name_exists($routeName)) {
                return null;
            }
            return encrypt($routeName . '|:|' . json_encode($parameters, JSON_THROW_ON_ERROR));
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists('goto_route_decrypt')) {
    /**
     * @param string $hash
     *
     * @return string|null
     */
    function goto_route_decrypt(string $hash): ?string
    {
        try {
            $route = explode('|:|', decrypt($hash));
            return route($route[0], json_decode($route[1], true, 512, JSON_THROW_ON_ERROR)); //  [$routeName = $route[0], $routeParameters = json_decode($route[1], true)];
        } catch (Exception) {
            return null;
        }
    }
}


/* ==================== General ==================== */

if (!function_exists('webpage_title')) {
    /**
     * @param string $title
     * @param bool   $postfix
     * @param string $name
     *
     * @return string
     */
    function webpage_title(string $title, bool $postfix = true, string $name = 'Website'): string
    {
        return $postfix ? sprintf('%s | %s', $title, app_name($name)) : $title;
    }
}

if (!function_exists('email_subject')) {
    /**
     * @param string $subject
     * @param bool   $showAppName
     *
     * @return string
     */
    function email_subject(string $subject, bool $showAppName = true): string
    {
        return $showAppName ? sprintf('%s - %s', $subject, app_full_name()) : $subject;
    }
}

if (!function_exists('get_model_table')) {
    /**
     * @param string|Model $model
     *
     * @return string|null
     */
    function get_model_table(Model|string $model): string|null
    {
        try {
            if (is_string($model)) {
                $model = (new $model);
            }
            return $model->getTable();
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists('get_model_from_table')) {
    function get_model_from_table(string $tableName)
    {
        // Specify the namespace where your models are located
        $namespace = 'App\\Models\\';

        // Get all model files from the Models directory
        $modelFiles = \Illuminate\Support\Facades\File::allFiles(app_path('Models'));

        foreach ($modelFiles as $file) {
            // Get the fully qualified class name
            $class = $namespace . $file->getRelativePathname(); // pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $class = str_replace('.php', '', $class);

            // Ensure the class exists and is an Eloquent model
            if (class_exists($class) && is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) {
                $model = new $class;

                // Check if the model's table matches the given table name
                if ($model->getTable() === $tableName) {
                    return $class;
                }
            }
        }

        // Return null if no model is found
        return null;
    }
}
