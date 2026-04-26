# Helper Functions

This file documents every helper function shipped in `src/Helpers`.

The package autoloads its own helper files through Composer. If you publish a custom helper file into your app, add that custom file to your application's `composer.json` `autoload.files` array and run `composer dump-autoload`.

## Table Of Contents

- [Application Helpers](#application-helpers)
- [File And Media Helpers](#file-and-media-helpers)
- [Number Helpers](#number-helpers)
- [Date And Time Helpers](#date-and-time-helpers)
- [String And Sanitization Helpers](#string-and-sanitization-helpers)
- [Array Helpers](#array-helpers)
- [Symbol Helpers](#symbol-helpers)
- [JSON And XML Helpers](#json-and-xml-helpers)
- [Exception And Pagination Helpers](#exception-and-pagination-helpers)
- [List And Misc Helpers](#list-and-misc-helpers)
- [Auth And User Helpers](#auth-and-user-helpers)
- [Authorization Helpers](#authorization-helpers)
- [User Generation Helpers](#user-generation-helpers)
- [Package Integration Helpers](#package-integration-helpers)

## Application Helpers

- `is_production(): bool` - Checks whether `config('app.env')` is `prod` or `production`. Example: `if (is_production()) { cache()->forever('mode', 'prod'); }`
- `is_staging(): bool` - Checks whether the app environment is `dev`, `development`, `stg`, or `staging`. Example: `if (is_staging()) { logger('staging request'); }`
- `is_local(): bool` - Checks whether the app environment is `local`. Example: `if (is_local()) { Model::preventLazyLoading(false); }`
- `is_testing(): bool` - Checks whether the app environment is `test` or `testing`. Example: `if (is_testing()) { config(['mail.default' => 'array']); }`
- `is_debug_mode(): bool` - Returns `config('app.debug', false)`. Example: `$debug = is_debug_mode();`
- `app_name(string $default = 'Website'): string` - Returns `config('app.name')`. Example: `$name = app_name('My App');`
- `app_full_name(string $default = 'Website'): string` - Returns `config('app.full_name')`. Example: `$name = app_full_name('My Company App');`
- `app_company_name(string $default = 'Website'): string` - Returns `config('app.company_name')`. Example: `$company = app_company_name('Acme');`
- `app_url(?string $path = null): string` - Returns the app URL, optionally with a path. Example: `$url = app_url('dashboard');`
- `app_asset_url(?string $path = null): string` - Returns `app.asset_url` or the app URL, optionally with a path. Example: `$asset = app_asset_url('images/logo.png');`
- `app_domain(string $default = '127.0.0.1:8000'): string` - Returns `config('app.domain')`. Example: `$email = 'admin@'.app_domain('example.com');`
- `app_timezone(string $default = 'UTC'): string` - Returns `config('app.timezone')`. Example: `$timezone = app_timezone();`
- `app_locale(string $default = 'en'): string` - Returns `config('app.locale')`. Example: `$locale = app_locale();`
- `get_route_name_from_url(string|Request $url, string $method = 'get'): string` - Resolves a URL or request to its route name. Example: `$name = get_route_name_from_url(url('/dashboard'));`
- `route_url_to_name(string|Request $url, string $method = 'get'): string` - Alias for `get_route_name_from_url()`. Example: `$name = route_url_to_name(request());`
- `is_route_name_exists(string $routeName): bool` - Checks whether a named route exists. Example: `if (is_route_name_exists('dashboard')) { return route('dashboard'); }`
- `get_current_route_name(): ?string` - Returns the active route name. Example: `$route = get_current_route_name();`
- `is_current_route(string $routeName): bool` - Checks whether the current route matches a name. Example: `@class(['active' => is_current_route('dashboard')])`
- `is_route(string $name): bool` - Alias for `is_current_route()`. Example: `if (is_route('settings')) { ... }`
- `is_current_route_in(array|string $routeNames): bool` - Checks whether the current route is in an array or comma-separated list. Example: `is_current_route_in(['dashboard', 'reports.index']);`
- `is_route_url(string $wildCardURL): bool` - Checks the current request path against a wildcard pattern. Example: `is_route_url('admin/*');`
- `clear_intended_url(): void` - Removes `url.intended` from the session. Example: `clear_intended_url();`
- `goto_route_encrypt(string $routeName, array $parameters = []): string|null` - Encrypts a route name and parameters for later redirecting. Example: `$hash = goto_route_encrypt('orders.show', ['order' => 1]);`
- `goto_route_decrypt(string $hash): ?string` - Decrypts a route hash and returns the generated route URL. Example: `$url = goto_route_decrypt($hash);`
- `webpage_title(string $title, bool $postfix = true, string $name = 'Website'): string` - Builds a page title with the app name. Example: `$title = webpage_title('Dashboard');`
- `email_subject(string $subject, bool $showAppName = true): string` - Builds an email subject with the app full name. Example: `$subject = email_subject('Password changed');`
- `get_model_table(Model|string $model): string|null` - Returns the table for an Eloquent model instance or class. Example: `$table = get_model_table(App\Models\User::class);`
- `get_model_from_table(string $tableName)` - Finds an app model class that uses a given table. Example: `$modelClass = get_model_from_table('users');`

## File And Media Helpers

- `get_enums(?array $filters = null, string $key = 'value', string $namespace = 'App\Enums'): array` - Reads app enum classes into a nested array. Example: `$enums = get_enums(['user.role:cases']);`
- `is_media_type_image(string $string): bool` - Checks whether a value is an image media type or image extension. Example: `is_media_type_image('png');`
- `is_media_type_audio(string $string): bool` - Checks whether a value is an audio type or extension. Example: `is_media_type_audio('mp3');`
- `is_media_type_video(string $string): bool` - Checks whether a value is a video type or extension. Example: `is_media_type_video('mp4');`
- `is_media_type_document(string $string): bool` - Checks whether a value is a document type or extension. Example: `is_media_type_document('pdf');`
- `is_media_type_archive(string $string): bool` - Checks whether a value is an archive type or extension. Example: `is_media_type_archive('zip');`
- `is_media_type_of(string $string): string|null` - Returns `image`, `audio`, `video`, `document`, `archive`, or `null`. Example: `$type = is_media_type_of('webp');`
- `is_base64_image(string $base64): bool` - Validates whether a string contains a base64 image. Example: `if (is_base64_image($payload['avatar'])) { ... }`
- `is_file_path(string $path): bool` - Checks whether a path is an existing file or directory. Example: `is_file_path(storage_path('app/report.pdf'));`
- `is_valid_url(string $url): bool` - Validates a URL string. Example: `is_valid_url('https://example.com/file.png');`
- `base64_to_uploaded_file(string $base64String, string $fileName, ?Closure $closure = null): UploadedFile` - Converts base64 image data into an `UploadedFile`. Example: `$file = base64_to_uploaded_file($base64, 'avatar.png');`
- `url_to_uploaded_file(string $url, ?string $fileName = null, ?Closure $closure = null): UploadedFile` - Downloads a URL into an `UploadedFile`. Example: `$file = url_to_uploaded_file('https://example.com/avatar.png', 'avatar.png');`
- `path_to_uploaded_file(string $path): UploadedFile` - Converts a local path into an `UploadedFile`. Example: `$file = path_to_uploaded_file(storage_path('app/imports/avatar.png'));`

## Number Helpers

- `display_number(float|int|string $number, int $decimal = 2, string $decimalPoint = '.', string $thousandsSeparator = ','): string` - Formats a number for display. Example: `display_number(1234.5); // 1,234.50`
- `to_number(float|int|string $number, int $decimal = 2, string $decimalPoint = '.', string $thousandsSeparator = ''): string` - Formats a number without thousands separators by default. Example: `to_number(1234.567); // 1234.57`
- `human_readable_number(float|int|string $number, int $decimal = 2, string $decimalPoint = '.', string $thousandsSeparator = ','): string` - Formats a number for human-readable output. Example: `human_readable_number(1000000);`
- `is_set(mixed $value): bool` - Checks that a value is set and not empty. Example: `if (is_set($request->name)) { ... }`
- `is_zero(mixed $number): bool` - Checks whether a numeric value is exactly zero. Example: `is_zero(0);`
- `is_negative(mixed $number): bool` - Checks whether a numeric value is below zero. Example: `is_negative(-10);`
- `is_negative_or_zero(mixed $number): bool` - Checks whether a numeric value is zero or below. Example: `is_negative_or_zero($balance);`
- `is_positive(mixed $number): bool` - Checks whether a numeric value is above zero. Example: `is_positive(99);`
- `is_positive_or_zero(mixed $number): bool` - Checks whether a numeric value is zero or above. Example: `is_positive_or_zero($stock);`
- `calculate_age(Carbon|string $dateOfBirth, Carbon|string|null $dateTill = null, bool $todayIncluded = true): int|null` - Calculates age in years. Example: `$age = calculate_age('1995-01-01');`
- `is_age_acceptable(Carbon|string $dateOfBirth, Carbon|string|null $dateTill = null, string $operator = '<=', int $criteria = 16): bool|null` - Compares calculated age with a criteria. Example: `is_age_acceptable('2000-01-01', criteria: 18, operator: '>=');`
- `number_to_words(float|int|string $number): string` - Converts a number to English words. Example: `number_to_words(125); // one hundred twenty-five`
- `get_percentage_of_value(float|int|string $current, float|int|string $total): float|int|string` - Calculates what percent a value is of a total. Example: `get_percentage_of_value(25, 200); // 12.5`
- `get_value_of_percentage(float|int|string $percentage, float|int|string $total): float|int|string` - Calculates the value represented by a percentage of a total. Example: `get_value_of_percentage(10, 250); // 25`
- `get_total_from_amount_n_percentage(float|int|string $amount, float|int|string $percentage): float|int|string` - Calculates total from partial amount and percentage. Example: `get_total_from_amount_n_percentage(25, 10); // 250`
- `percentage_difference(float $amount1, float $amount2, bool $difference = true)` - Calculates percentage difference between two numbers. Example: `percentage_difference(100, 125);`
- `percentage_change(float $amount, float $percentage, bool $increment = true)` - Increments or decrements a number by a percentage. Example: `percentage_change(100, 15); // 115`

## Date And Time Helpers

- `now_now(string $timezone = 'UTC'): Carbon` - Returns `now()` using the configured app timezone fallback. Example: `$now = now_now('Asia/Karachi');`
- `get_date_periods_between($startDate, $endDate = null, string $format = 'd M'): array` - Returns formatted dates between two dates. Example: `get_date_periods_between('2026-01-01', '2026-01-03');`
- `is_datetime_between($start, $end, $date = null, bool $includeBorderDates = true, bool $includeTime = true, string $timezone = 'UTC'): bool` - Checks whether a datetime is within a range. Example: `is_datetime_between('2026-01-01 10:00', '2026-01-01 12:00');`
- `is_date_between($date, $start, $end, string $timezone = 'UTC'): bool` - Checks whether a date is within a date range. Example: `is_date_between('2026-01-15', '2026-01-01', '2026-01-31');`
- `is_today_between($start, $end, string $timezone = 'UTC'): bool` - Checks whether today is within a date range. Example: `is_today_between('2026-01-01', '2026-12-31');`
- `display_datetime($dateTime = null, string $format = 'l jS M, Y', string $timezone = 'UTC', string $formatType = '', bool $showTodayDefault = true): string` - Formats a date, datetime, or timestamp. Example: `display_datetime(now(), 'Y-m-d');`
- `diff_for_humans($date, string $timezone = 'UTC'): string` - Returns a Carbon human diff. Example: `diff_for_humans(now()->subHour());`
- `remaining_days_of_month($date = null, bool $useGivenDateEndOfMonth = false, string $timezone = 'UTC'): int` - Returns remaining days until month end. Example: `remaining_days_of_month(now());`
- `days_between_dates($end, $start = null, string $timezone = 'UTC'): int` - Returns days from start to end. Example: `days_between_dates('2026-05-01', '2026-04-26');`
- `remaining_days_till($end, $start = null, string $timezone = 'UTC'): int` - Alias-style helper for days until a date. Example: `remaining_days_till('2026-12-31');`
- `days_in_month($date = null, string $timezone = 'UTC'): int` - Returns the number of days in a month. Example: `days_in_month('2026-02-01');`
- `time_format_to_number($time, $splitter = ':')` - Converts `HH:MM` to total minutes. Example: `time_format_to_number('02:30'); // 150`
- `number_to_time_format($number, $join = ':')` - Converts total minutes to `HH:MM`. Example: `number_to_time_format(150); // 02:30`

## String And Sanitization Helpers

- `remove_script_tag(string $string): string` - Removes `<script>` blocks from text. Example: `remove_script_tag('<p>ok</p><script>alert(1)</script>');`
- `remove_invalid_html_tags(string $string): string` - Removes empty common HTML tags. Example: `remove_invalid_html_tags('<p></p><p>Hello</p>');`
- `remove_script_tag_from_string(string $string, bool $clearEmptyTag = true): string` - Removes scripts and optionally empty tags. Example: `remove_script_tag_from_string($html);`
- `sanitize_text_editor_text(string $text): string` - Sanitizes text editor HTML output. Example: `$clean = sanitize_text_editor_text($request->body);`
- `sanitize_text_editor_search_and_replace(string $text, int $offset = 0): string` - Cleans problematic style attributes in HTML. Example: `$html = sanitize_text_editor_search_and_replace($html);`
- `telegram_string_sanitizer(string $string): string` - Escapes text for Telegram-friendly formatting. Example: `$safe = telegram_string_sanitizer('Hello [user]');`
- `secret_value(string $string, array $display = [4, -4], bool $displayBetween = false, string $char = '*'): string` - Masks part of a sensitive string. Example: `secret_value('4242424242424242');`
- `words_fc(string $string, string $delimiter = ' ', bool $uppercase = true, int $limit = 0): string` - Gets first characters from words. Example: `words_fc('John Doe', limit: 2); // JD`
- `take_words(string $string, int $count = 2, string $end = '...'): string` - Takes the first words from a string. Example: `take_words('A long product title', 2);`
- `camel_case(string $string, bool $capitalizeFirstCharacter = false)` - Converts text to camelCase. Example: `camel_case('invoice status'); // invoiceStatus`
- `snake_case(string $string): string` - Converts text to snake_case. Example: `snake_case('Invoice Status');`
- `pascal_case(string $string): string` - Converts text to PascalCase. Example: `pascal_case('invoice status');`
- `get_lat_lng_from_address(string $address, $apiKey = null): ?array` - Resolves an address with Google Geocoding. Example: `$coords = get_lat_lng_from_address('Lahore, Pakistan', config('services.google.key'));`
- `lat_long_dist_of_two_points($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 3959)` - Calculates distance between two coordinates. Example: `$miles = lat_long_dist_of_two_points(31.5204, 74.3587, 24.8607, 67.0011);`

## Array Helpers

- `arrayify($value, $separator = ',', $default = [], $filter = true)` - Converts strings and values into arrays. Example: `arrayify('a,b,c');`
- `nested_array_filter(array $array)` - Recursively filters empty values from nested arrays. Example: `$clean = nested_array_filter($payload);`
- `replace_array_keys(array $array)` - Replaces spaces in array keys with underscores. Example: `replace_array_keys(['first name' => 'John']);`
- `array_keys_to_snake_case(array $array): array` - Converts array keys to snake_case recursively. Example: `array_keys_to_snake_case(['First Name' => 'John']);`
- `array_search_recursive(array $haystack, string $needle)` - Searches nested arrays for a value and returns its key. Example: `array_search_recursive($menu, 'settings');`
- `array_search_item(array $haystack, string $needle)` - Searches a first-level nested array item and returns its parent key. Example: `array_search_item([['admin', 'user']], 'admin');`
- `array_flatten(array $array)` - Flattens a nested array. Example: `array_flatten([1, [2, 3]]);`
- `set_nested_array_value(array &$array, string $path, &$value, string $delimiter = '/')` - Sets a nested value by path and returns the previous value. Example: `$old = set_nested_array_value($data, 'user/name', $name);`

## Symbol Helpers

- `html_symbols(?string $name = null)` - Returns a symbol by name or all symbols. Example: `html_symbols('check');`
- `html_symbol_codes(?string $name = null)` - Returns an HTML entity code by name or all codes. Example: `html_symbol_codes('copyright');`

## JSON And XML Helpers

- `json_to_xml(string $json, bool $useFirstKeyAsRootTag = false, ?string $path = null)` - Converts JSON to XML string or file. Example: `$xml = json_to_xml('{"user":{"name":"John"}}');`
- `array_to_xml(array $array, bool $useFirstKeyAsRootTag = false, ?string $path = null)` - Converts an array to XML string or file. Example: `$xml = array_to_xml(['user' => ['name' => 'John']], true);`
- `xml_to_array($xml, ?string $wrap = null)` - Converts XML to an array. Example: `$array = xml_to_array('<root><name>John</name></root>');`
- `xml_to_json($xml, ?string $wrap = null)` - Converts XML to JSON. Example: `$json = xml_to_json('<root><name>John</name></root>');`
- `array_to_xml_conversion_script(array $array, &$simpleXmlElement)` - Low-level recursive writer used by `array_to_xml()`. Example: `array_to_xml_conversion_script($data, $xml);`

## Exception And Pagination Helpers

- `exception_response($exception)` - Converts an exception into an array with message, file, and code. Example: `return response()->json(exception_response($e), 500);`
- `pagination_stats($paginationCollection, $perPage = null): array` - Builds pagination display stats from a paginator. Example: `$stats = pagination_stats(User::paginate());`
- `length_aware_paginator($items, $perPage = 15, $page = null, $options = []): LengthAwarePaginator` - Builds a `LengthAwarePaginator` from an array or collection. Example: `$paginator = length_aware_paginator($collection, 10);`
- `simple_pagination($items, $total = 0, $page = null, $perPage = 15): array` - Builds a simple pagination metadata array. Example: `$meta = simple_pagination($items, $total, request('page', 1));`

## List And Misc Helpers

- `days_list(): array` - Returns weekday keys and names. Example: `$days = days_list();`
- `months_list(): array` - Returns month keys and names. Example: `$months = months_list();`
- `is_leap_year($year = null): bool` - Checks whether a year is a leap year. Example: `is_leap_year(2024);`
- `random_color_hex_part(): string` - Generates one random hex color segment. Example: `$part = random_color_hex_part();`
- `generate_random_color_hex(): string` - Generates a full random hex color. Example: `$color = generate_random_color_hex();`
- `generate_git_branch(string $type, string $name): string` - Reserved helper for branch name generation; currently returns an empty string. Example: `$branch = generate_git_branch('fix', 'login-error');`

## Auth And User Helpers

- `_get_user_model()` - Returns the configured user model class from `config('lhm.models.user')`. Example: `$userModel = _get_user_model();`
- `auth_check(?string $guard = null): bool` - Safely checks whether a user is authenticated. Example: `if (auth_check()) { ... }`
- `auth_user(?string $guard = null): Authenticatable|User|null` - Safely returns the authenticated user. Example: `$user = auth_user();`
- `auth_id(?string $guard = null)` - Safely returns the authenticated user ID. Example: `$id = auth_id();`
- `is_me($user, ?string $guard = null): bool` - Checks whether the given user or user ID is the authenticated user. Example: `is_me($profileUser);`
- `get_user($user = null, ?string $guard = null)` - Resolves null to auth user, numeric value to user model, or returns the given user. Example: `$user = get_user($userId);`

## Authorization Helpers

- `policy_authorization(Model $user, string $ability, ?Model $model = null): bool` - Checks package role permission data for an ability. Example: `policy_authorization($user, 'posts.update', $post);`
- `gate_allows(string $ability, $parameters = [], ?Model $user = null): bool` - Runs Laravel Gate authorization with an optional user. Example: `gate_allows('update', $post);`
- `gate_authorize(string $ability, $parameters = [], ?Model $user = null): Response` - Runs Gate authorization and throws on denial. Example: `gate_authorize('delete', $post);`
- `gate_allows_redirect(string $ability, $parameters = [], ?Model $user = null, $route = 'dashboard'): RedirectResponse` - Redirects when Gate denies access. Example: `return gate_allows_redirect('viewAdmin', route: 'dashboard') ?: view('admin');`

## User Generation Helpers

- `generate_username(string $name = 'Guest'): string` - Generates a slugged unique username. Example: `$username = generate_username('John Doe');`
- `generate_email(string $name, ?string $domain = null): string` - Generates an email using a name and domain. Example: `$email = generate_email('John Doe', 'example.com');`
- `generate_password(int $length = 12, string $chars = '...'): string` - Generates a random password. Example: `$password = generate_password(16);`
- `generate_number(int $length = 10): string` - Generates a random numeric string. Example: `$code = generate_number(6);`
- `generate_unique_id(int $length = 10): string` - Generates a random hex-based ID. Example: `$id = generate_unique_id(12);`
- `generate_unique_id_model(Model $modal, string $column, string $uniqueIdPrefix = '', int $length = 10, int $recursive = 5): string` - Generates an ID unique for a model column. Example: `$orderNo = generate_unique_id_model(new Order(), 'number', 'ORD-');`
- `generate_avatar_name(string $string, string $delimiter = ' ', bool $uppercase = true, int $limit = 2): string` - Generates initials from a name. Example: `generate_avatar_name('John Doe');`
- `generate_avatar(?string $name = null, string $fontColor = 'ffffff', string $backgroundColor = '293042'): string` - Generates a UI Avatars URL. Example: `$avatar = generate_avatar('John Doe');`
- `generate_gravatar(string $email, int $s = 80, string $d = 'mp', string $r = 'g', bool $img = false, array $attr = []): string` - Generates a Gravatar URL or image tag. Example: `$url = generate_gravatar('john@example.com');`
- `is_email_address(string $email): bool` - Validates an email address. Example: `is_email_address('john@example.com');`

## Package Integration Helpers

These helpers expect an impersonation package binding named `impersonate` and routes named `impersonate` and `impersonate.leave`.

- `impersonate_manager(): mixed` - Returns the impersonate manager from the service container. Example: `$manager = impersonate_manager();`
- `impersonate_url($user): string` - Generates the route URL to impersonate a user. Example: `$url = impersonate_url($user);`
- `impersonate_leave_url(): string` - Generates the route URL to leave impersonation. Example: `$url = impersonate_leave_url();`
- `impersonate_user($user): RedirectResponse` - Redirects to the impersonation URL for a user. Example: `return impersonate_user($user);`
- `is_impersonating(): mixed` - Checks whether the current session is impersonating. Example: `if (is_impersonating()) { ... }`
- `leave_impersonate(): mixed` - Ends the current impersonation session. Example: `leave_impersonate();`
- `get_impersonator_id(): mixed` - Returns the ID of the original impersonator. Example: `$adminId = get_impersonator_id();`

## Code Examples

This section keeps the reference above unchanged and adds copyable examples for every helper function. Output comments show the expected return shape or a typical result. Dynamic values such as URLs, dates, random strings, authenticated users, database IDs, and model classes depend on your application state.

### Application Helper Examples

```php
$isProduction = is_production();
// Output: true when APP_ENV is "prod" or "production", otherwise false.

$isStaging = is_staging();
// Output: true when APP_ENV is "dev", "development", "stg", or "staging", otherwise false.

$isLocal = is_local();
// Output: true when APP_ENV is "local", otherwise false.

$isTesting = is_testing();
// Output: true when APP_ENV is "test" or "testing", otherwise false.

$debugMode = is_debug_mode();
// Output: true or false based on config('app.debug').

$applicationName = app_name('Website');
// Output: "Laravel" or your config('app.name'); fallback is "Website".

$applicationFullName = app_full_name('Website');
// Output: config('app.full_name') or "Website".

$companyName = app_company_name('Website');
// Output: config('app.company_name') or "Website".

$homeUrl = app_url();
// Output: "https://example.test"

$dashboardUrl = app_url('dashboard');
// Output: "https://example.test/dashboard"

$assetRoot = app_asset_url();
// Output: config('app.asset_url') or app_url().

$logoUrl = app_asset_url('images/logo.png');
// Output: "https://example.test/images/logo.png"

$domain = app_domain('example.com');
// Output: config('app.domain') or "example.com".

$timezone = app_timezone('UTC');
// Output: config('app.timezone') or "UTC".

$locale = app_locale('en');
// Output: config('app.locale') or "en".

$routeNameFromUrl = get_route_name_from_url(url('/dashboard'));
// Output: "dashboard" when /dashboard is a named route, otherwise "".

$routeNameAlias = route_url_to_name(request());
// Output: current request route name, or "" when not matched.

$routeExists = is_route_name_exists('dashboard');
// Output: true when the route name exists, otherwise false.

$currentRoute = get_current_route_name();
// Output: "dashboard" or null.

$isDashboard = is_current_route('dashboard');
// Output: true when the current route name is "dashboard".

$isSettings = is_route('settings.index');
// Output: true when the current route name is "settings.index".

$isInAccountArea = is_current_route_in(['profile.show', 'profile.edit']);
// Output: true when the current route is one of the given names.

$isAdminUrl = is_route_url('admin/*');
// Output: true when the current path matches admin/*.

clear_intended_url();
// Output: void; removes "url.intended" from the session.

$encryptedRoute = goto_route_encrypt('orders.show', ['order' => 10]);
// Output: encrypted string, or null if the route does not exist.

$redirectUrl = $encryptedRoute ? goto_route_decrypt($encryptedRoute) : null;
// Output: "https://example.test/orders/10" or null.

$pageTitle = webpage_title('Dashboard');
// Output: "Dashboard | Laravel" or "Dashboard | {app_name}".

$plainTitle = webpage_title('Dashboard', postfix: false);
// Output: "Dashboard"

$subject = email_subject('Password changed');
// Output: "Password changed - {app_full_name}".

$subjectWithoutAppName = email_subject('Password changed', showAppName: false);
// Output: "Password changed"

$usersTable = get_model_table(App\Models\User::class);
// Output: "users"

$modelClass = get_model_from_table('users');
// Output: "App\Models\User" or null.
```

### File And Media Helper Examples

```php
$allEnums = get_enums();
// Output: nested array of enum values discovered under App\Enums.

$filteredEnums = get_enums(['user.role:cases'], key: 'name');
// Output: nested array containing only the filtered enum data.

$isImage = is_media_type_image('png');
// Output: true

$isAudio = is_media_type_audio('mp3');
// Output: true

$isVideo = is_media_type_video('mp4');
// Output: true

$isDocument = is_media_type_document('pdf');
// Output: true

$isArchive = is_media_type_archive('zip');
// Output: true

$mediaType = is_media_type_of('webp');
// Output: "image"

$isBase64Image = is_base64_image($request->input('avatar'));
// Output: true when the string is valid base64 image data.

$avatar = base64_to_uploaded_file($request->input('avatar'), 'avatar.png');
// Output: Illuminate\Http\UploadedFile instance.

$pathExists = is_file_path(storage_path('app/reports/monthly.pdf'));
// Output: true when the file or directory exists.

$validUrl = is_valid_url('https://example.com/avatar.png');
// Output: true

$uploadedFromUrl = url_to_uploaded_file(
    url: 'https://example.com/avatar.png',
    fileName: 'avatar.png',
);
// Output: Illuminate\Http\UploadedFile instance downloaded from the URL.

$uploadedFromPath = path_to_uploaded_file(storage_path('app/imports/avatar.png'));
// Output: Illuminate\Http\UploadedFile instance for the local path.

$uploadedFromBase64 = base64_to_uploaded_file(
    base64String: $request->input('signature'),
    fileName: 'signature.png',
    closure: fn (string $temporaryPath) => logger()->info("Created $temporaryPath"),
);
// Output: Illuminate\Http\UploadedFile instance; closure receives the temp path.
```

### Number Helper Examples

```php
$displayAmount = display_number(12500.5);
// Output: "12,500.50"

$plainAmount = to_number(12500.567);
// Output: "12500.57"

$readableAmount = human_readable_number(1000000);
// Output: "1,000,000.00"

$hasName = is_set($request->input('name'));
// Output: true when the value is set and not empty.

$zero = is_zero(0);
// Output: true

$negative = is_negative(-10);
// Output: true

$negativeOrZero = is_negative_or_zero(0);
// Output: true

$positive = is_positive(15);
// Output: true

$positiveOrZero = is_positive_or_zero(0);
// Output: true

$age = calculate_age('1995-01-01');
// Output: integer age in years, or null on invalid date.

$isAdult = is_age_acceptable('1995-01-01', operator: '>=', criteria: 18);
// Output: true when calculated age is 18 or above.

$words = number_to_words(125);
// Output: "one hundred twenty-five"

$percentage = get_percentage_of_value(current: 25, total: 200);
// Output: 12.5

$percentageValue = get_value_of_percentage(percentage: 10, total: 250);
// Output: 25

$total = get_total_from_amount_n_percentage(amount: 25, percentage: 10);
// Output: 250

$difference = percentage_difference(100, 125);
// Output: 22.222222222222...

$increase = percentage_change(100, 15);
// Output: 115

$decrease = percentage_change(100, 15, increment: false);
// Output: 85
```

### Date And Time Helper Examples

```php
$now = now_now();
// Output: Carbon instance for current datetime.

$nowInPakistan = now_now('Asia/Karachi');
// Output: Carbon instance using app_timezone('Asia/Karachi').

$periods = get_date_periods_between('2026-01-01', '2026-01-03', 'Y-m-d');
// Output: ["2026-01-01", "2026-01-02", "2026-01-03"]

$isMeetingActive = is_datetime_between(
    start: '2026-01-01 10:00:00',
    end: '2026-01-01 12:00:00',
    date: '2026-01-01 11:00:00',
);
// Output: true

$isInBillingCycle = is_date_between('2026-01-15', '2026-01-01', '2026-01-31');
// Output: true

$isTodayInCampaign = is_today_between('2026-01-01', '2026-12-31');
// Output: true when today's date is between the given range.

$formattedDate = display_datetime(now(), 'Y-m-d');
// Output: "2026-04-26" style string, depending on the supplied date.

$formattedIsoDate = display_datetime(now(), 'MMMM Do YYYY', formatType: 'iso');
// Output: "April 26th 2026" style string.

$humanDiff = diff_for_humans(now()->subHours(2));
// Output: "2 hours ago"

$daysLeftThisMonth = remaining_days_of_month();
// Output: integer number of days left in the current month.

$daysLeftInGivenMonth = remaining_days_of_month('2026-04-10', useGivenDateEndOfMonth: true);
// Output: 20

$daysBetween = days_between_dates('2026-05-01', '2026-04-26');
// Output: 5

$daysRemaining = remaining_days_till('2026-12-31');
// Output: integer days from today to 2026-12-31, or -1 if past.

$daysInFebruary = days_in_month('2026-02-01');
// Output: 28

$minutes = time_format_to_number('02:30');
// Output: 150

$time = number_to_time_format(150);
// Output: "02:30"
```

### String And Sanitization Helper Examples

```php
$withoutScripts = remove_script_tag('<p>Hello</p><script>alert(1)</script>');
// Output: "<p>Hello</p>"

$withoutEmptyTags = remove_invalid_html_tags('<p></p><p>Hello</p>');
// Output: "<p>Hello</p>"

$cleanHtml = remove_script_tag_from_string($request->input('body'));
// Output: HTML string without script tags and empty common tags.

$editorText = sanitize_text_editor_text($request->input('body'));
// Output: sanitized editor HTML string.

$fixedEditorText = sanitize_text_editor_search_and_replace('<p style="font-family: "Arial";">Text</p>');
// Output: string with problematic quote usage inside style attributes normalized.

$telegramText = telegram_string_sanitizer('Invoice [#1001] is ready.');
// Output: string escaped for Telegram formatting.

$maskedCard = secret_value('4242424242424242');
// Output: "4242********4242"

$maskedMiddle = secret_value('secret-token-value', display: [6, -4], displayBetween: false);
// Output: "secret*********alue"

$initials = words_fc('John Doe', limit: 2);
// Output: "JD"

$excerpt = take_words('A long product title for the catalog', 4);
// Output: "A long product title..."

$camel = camel_case('invoice status');
// Output: "invoiceStatus"

$capitalizedCamel = camel_case('invoice status', capitalizeFirstCharacter: true);
// Output: "InvoiceStatus"

$snake = snake_case('Invoice Status');
// Output: "invoice_status"

$pascal = pascal_case('invoice status');
// Output: "InvoiceStatus"

$coordinates = get_lat_lng_from_address('Lahore, Pakistan', config('services.google.key'));
// Output: ["lat" => 31.5204, "lng" => 74.3587] style array, or null.

$distance = lat_long_dist_of_two_points(
    latitudeFrom: 31.5204,
    longitudeFrom: 74.3587,
    latitudeTo: 24.8607,
    longitudeTo: 67.0011,
);
// Output: numeric distance in miles by default.
```

### Array Helper Examples

```php
$values = arrayify('admin,editor,user');
// Output: ["admin", "editor", "user"]

$valuesWithoutFiltering = arrayify('admin,,user', filter: false);
// Output: ["admin", "", "user"]

$filtered = nested_array_filter([
    'name' => 'John',
    'meta' => [
        'empty' => '',
        'role' => 'admin',
    ],
]);
// Output: ["name" => "John", "meta" => ["role" => "admin"]]

$replacedKeys = replace_array_keys(['first name' => 'John']);
// Output: ["first_name" => "John"]

$snakeKeys = array_keys_to_snake_case(['First Name' => 'John']);
// Output: ["first_name" => "John"]

$recursiveKey = array_search_recursive([
    'menu' => ['dashboard', 'settings'],
], 'settings');
// Output: 1

$itemKey = array_search_item([
    'admins' => ['create', 'update'],
    'users' => ['view'],
], 'update');
// Output: "admins"

$flat = array_flatten([1, [2, [3, 4]]]);
// Output: [1, 2, 3, 4]

$payload = [];
$previous = set_nested_array_value($payload, 'user/profile/name', $name, '/');
// Output: previous value at that path, usually null for a new path.
// $payload becomes ["user" => ["profile" => ["name" => $name]]].
```

### Symbol Helper Examples

```php
$checkSymbol = html_symbols('check');
// Output: "✓"

$allSymbols = html_symbols();
// Output: associative array of symbol names and symbols.

$copyrightCode = html_symbol_codes('copyright');
// Output: "&#169;"

$allSymbolCodes = html_symbol_codes();
// Output: associative array of symbol names and HTML entity codes.
```

### JSON And XML Helper Examples

```php
$xmlFromJson = json_to_xml('{"user":{"name":"John"}}');
// Output: XML string such as "<?xml version=\"1.0\"?><root><user><name>John</name></user></root>"

$xmlFromArray = array_to_xml([
    'user' => [
        'name' => 'John',
        'email' => 'john@example.com',
    ],
], useFirstKeyAsRootTag: true);
// Output: XML string with <user> as the root element.

$arrayFromXml = xml_to_array('<root><name>John</name></root>');
// Output: ["name" => "John"]

$jsonFromXml = xml_to_json('<root><name>John</name></root>');
// Output: "{\"name\":\"John\"}"

$xmlElement = new SimpleXMLElement('<?xml version="1.0"?><root></root>');
array_to_xml_conversion_script(['name' => 'John'], $xmlElement);
// Output: null; $xmlElement becomes <root><name>John</name></root>.
```

### Exception And Pagination Helper Examples

```php
try {
    throw new RuntimeException('Something failed.');
} catch (Throwable $exception) {
    $error = exception_response($exception);
}
// Output: ["message" => "Something failed.", "file" => "... : line", "code" => 0]

$users = App\Models\User::paginate(15);
$stats = pagination_stats($users);
// Output: array with firstPage, lastPage, currentPage, perPage, total, url_page, start, and end.

$collectionPaginator = length_aware_paginator(
    items: collect([1, 2, 3, 4, 5]),
    perPage: 2,
);
// Output: Illuminate\Pagination\LengthAwarePaginator instance.

$simplePagination = simple_pagination(
    items: [1, 2, 3],
    total: 20,
    page: request()->integer('page', 1),
    perPage: 3,
);
// Output: array with url, items, total, per_page, current_page, first_page, previous_page, next_page, and last_page.
```

### List And Misc Helper Examples

```php
$days = days_list();
// Output: ["monday" => "Monday", ..., "sunday" => "Sunday"]

$months = months_list();
// Output: ["jan" => "January", ..., "dec" => "December"]

$leapYear = is_leap_year(2024);
// Output: true

$currentYearIsLeapYear = is_leap_year();
// Output: true or false for the current year.

$hexPart = random_color_hex_part();
// Output: random two-character hex string such as "a3".

$hexColor = generate_random_color_hex();
// Output: random hex color such as "#a3f01c".

$branch = generate_git_branch('fix', 'login-error');
// Output: "" because the helper is currently reserved and not implemented.
```

### Auth And User Helper Examples

```php
$userModelClass = _get_user_model();
// Output: configured user model class, usually "App\Models\User".

$isAuthenticated = auth_check();
// Output: true when a user is authenticated.

$user = auth_user();
// Output: authenticated user model or null.

$id = auth_id();
// Output: authenticated user ID or null.

$admin = auth_user('admin');
// Output: authenticated admin guard user or null.

$adminId = auth_id('admin');
// Output: authenticated admin guard user ID or null.

$isCurrentUser = is_me($profileUser);
// Output: true when $profileUser is the authenticated user.

$isCurrentUserById = is_me($profileUser->id);
// Output: true when the ID belongs to the authenticated user.

$resolvedAuthUser = get_user();
// Output: authenticated user or null.

$resolvedUserById = get_user($profileUser->id);
// Output: user model found by ID or null.

$resolvedUserInstance = get_user($profileUser);
// Output: the given user model instance.
```

### Authorization Helper Examples

```php
$allowedByPackagePolicy = policy_authorization(
    user: $user,
    ability: 'posts.update',
    model: $post,
);
// Output: true when the user's role is reserved or has the ability.

$canUpdate = gate_allows('update', $post);
// Output: true when Laravel Gate allows the ability.

$deleteResponse = gate_authorize('delete', $post);
// Output: Illuminate\Auth\Access\Response on success; throws AuthorizationException on denial.

$redirect = gate_allows_redirect('viewAdmin', route: 'dashboard');
// Output: false when allowed, or Illuminate\Http\RedirectResponse when denied.
```

### User Generation Helper Examples

```php
$username = generate_username('John Doe');
// Output: "john-doe.662df2b7e9d8d0.12345678" style unique string.

$email = generate_email('John Doe', 'example.com');
// Output: "john-doe.{unique}@example.com"

$password = generate_password(16);
// Output: random 16-character password string.

$verificationCode = generate_number(6);
// Output: random numeric string such as "483920".

$publicId = generate_unique_id(12);
// Output: random 12-character hex string.

$orderNumber = generate_unique_id_model(
    modal: new App\Models\Order(),
    column: 'number',
    uniqueIdPrefix: 'ORD-',
    length: 10,
);
// Output: unique string such as "a1b2c3d4e5"; use with prefix as "ORD-a1b2c3d4e5".

$avatarName = generate_avatar_name('John Doe');
// Output: "JD"

$avatarUrl = generate_avatar('John Doe');
// Output: "https://ui-avatars.com/api/?name=John Doe&background=293042&color=ffffff"

$gravatarUrl = generate_gravatar('john@example.com');
// Output: Gravatar URL string.

$gravatarImage = generate_gravatar('john@example.com', img: true, attr: ['class' => 'rounded-full']);
// Output: complete <img> HTML tag string.

$validEmail = is_email_address('john@example.com');
// Output: true
```

### Package Integration Helper Examples

```php
$manager = impersonate_manager();
// Output: impersonation manager instance from the container.

$startUrl = impersonate_url($user);
// Output: URL for the "impersonate" route with the user's ID.

$leaveUrl = impersonate_leave_url();
// Output: URL for the "impersonate.leave" route.

$redirect = impersonate_user($user);
// Output: Illuminate\Http\RedirectResponse to the impersonation URL.

$isImpersonating = is_impersonating();
// Output: true or false depending on current impersonation state.

$impersonatorId = get_impersonator_id();
// Output: original impersonator user ID or null.

$leaveResult = leave_impersonate();
// Output: package-specific result from the impersonation manager.
```
