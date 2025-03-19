<?php

use AbdullahMateen\LaravelHelpingMaterial\Enums\Media\MediaTypeEnum;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Files Related Helper Functions
|--------------------------------------------------------------------------
*/

/* ==================== Enums ==================== */

if (!function_exists('get_enums')) {
    /**
     * @param string $key value,name
     * @param string $namespace
     *
     * @return array
     */
    function get_enums(array $filters = null, string $key = 'value', string $namespace = 'App\Enums'): array
    {
        $enums = [];

        $folders = explode('\\', $namespace);
        array_shift($folders);
        $folders = implode('\\', array_map('ucwords', $folders));

        $files = File::allFiles(app_path($folders));
        foreach ($files as $fi => $file) {
            $filename     = $file->getFilenameWithoutExtension();
            $relativePath = Str::replaceArray('/', ['\\'], $file->getRelativePath());

            $dotNotationFile = trim(collect(explode('\\', $relativePath))->map(fn ($segment) => Str::snake($segment))->implode('.') . '.' . Str::snake($filename), '.');
            if (isset($filters) && !collect($filters)->filter(fn ($value) => explode(':', $value)[0] === $dotNotationFile)->count()) continue;

            $enums[$fi] = [];
            $value      = null;
            $path       = empty($relativePath) ? 'General' : $relativePath;

            $class = Str::replaceArray('\\\\', ['\\'], "$namespace\\$relativePath\\$filename");

            $filePath = collect($filters)->first(fn ($value, $key) => explode(':', $value)[0] === $dotNotationFile);
            $cases    = strpos($filePath, ':') ? explode(':', $filePath)[1] : 'cases';
            foreach (arrayify($cases) as $case) {
                $value[$filename] = array_replace($value[$filename] ?? [], $class::toFullArray($case, $key));
            }

            set_nested_array_value($enums[$fi], $path, $value, '\\');
        }

        return array_merge_recursive(...$enums);
    }
}

/* ==================== Media ==================== */

if (!function_exists('is_media_type_image')) {
    /**
     * @param string $string
     *
     * @return bool
     */
    function is_media_type_image(string $string): bool
    {
        try {
            if ($string === strtolower(MediaTypeEnum::Image->name)) {
                return true;
            }
            if (str_contains($string, ' image/')) {
                return true;
            }
            if (in_array(strtolower($string), MediaTypeEnum::Image->extensions())) {
                return true;
            }

            return false;
        } catch (Exception) {
            return false;
        }
    }
}

if (!function_exists('is_media_type_audio')) {
    /**
     * @param string $string
     *
     * @return bool
     */
    function is_media_type_audio(string $string): bool
    {
        try {
            if ($string === strtolower(MediaTypeEnum::Audio->name)) {
                return true;
            }
            if (in_array(strtolower($string), MediaTypeEnum::Audio->extensions())) {
                return true;
            }

            return false;
        } catch (Exception) {
            return false;
        }
    }
}

if (!function_exists('is_media_type_video')) {
    /**
     * @param string $string
     *
     * @return bool
     */
    function is_media_type_video(string $string): bool
    {
        try {
            if ($string === strtolower(MediaTypeEnum::Video->name)) {
                return true;
            }
            if (in_array(strtolower($string), MediaTypeEnum::Video->extensions())) {
                return true;
            }

            return false;
        } catch (Exception) {
            return false;
        }
    }
}

if (!function_exists('is_media_type_document')) {
    /**
     * @param string $string
     *
     * @return bool
     */
    function is_media_type_document(string $string): bool
    {
        try {
            if ($string === strtolower(MediaTypeEnum::Document->name)) {
                return true;
            }
            if (in_array(strtolower($string), MediaTypeEnum::Document->extensions())) {
                return true;
            }

            return false;
        } catch (Exception) {
            return false;
        }
    }
}

if (!function_exists('is_media_type_archive')) {
    /**
     * @param string $string
     *
     * @return bool
     */
    function is_media_type_archive(string $string): bool
    {
        try {
            if ($string === strtolower(MediaTypeEnum::Archive->name)) {
                return true;
            }
            if (in_array(strtolower($string), MediaTypeEnum::Archive->extensions())) {
                return true;
            }

            return false;
        } catch (Exception) {
            return false;
        }
    }
}

if (!function_exists('is_media_type_of')) {
    /**
     * @param string $string
     *
     * @return string|null
     */
    function is_media_type_of(string $string): string|null
    {
        try {
            return strtolower(match (true) {
                is_media_type_image($string)    => MediaTypeEnum::Image->name,
                is_media_type_audio($string)    => MediaTypeEnum::Audio->name,
                is_media_type_video($string)    => MediaTypeEnum::Video->name,
                is_media_type_document($string) => MediaTypeEnum::Document->name,
                is_media_type_archive($string)  => MediaTypeEnum::Archive->name,
                default                         => null
            });
        } catch (Exception) {
            return null;
        }
    }
}

if (!function_exists('is_base64_image')) {
    /**
     * @param string $base64
     *
     * @return bool
     */
    function is_base64_image(string $base64): bool
    {
        if (empty($base64) || !is_string($base64)) {
            return false;
        }

        // Remove data URI scheme if present
        $data = preg_replace('#^data:image/[^;]+;base64,#', '', $base64);

        // Validate base64 format (only contains valid characters)
        if (!preg_match('/^[a-zA-Z0-9\/+\r\n]+={0,2}$/', $data)) {
            return false;
        }

        // Decode the base64 string
        $decodedData = base64_decode($data, true);

        // Debugging: Check if decoding was successful
        if ($decodedData === false) {
            error_log('Base64 decoding failed: Possibly an invalid or incomplete base64 string.');
            return false;
        }

        // Debugging: Check the length of decoded data
        if (strlen($decodedData) < 10) { // Typical image headers are longer
            error_log('Decoded base64 string is too short to be an image.');
            return false;
        }

        // Check if the decoded data is an image
        if (@getimagesizefromstring($decodedData) === false) {
            error_log('Decoded data is not a valid image.');
            return false;
        }

        // Check if the decoding was successful and the result is an image
        return true;// ($decodedData !== false) && (getimagesizefromstring($decodedData) !== false);
    }
}

if (!function_exists('is_file_path')) {
    /**
     * @param string $path
     *
     * @return bool
     */
    function is_file_path(string $path): bool
    {
        return is_file($path) || is_dir($path);
    }
}

if (!function_exists('is_valid_url')) {
    /**
     * @param string $url
     *
     * @return bool
     */
    function is_valid_url(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}

if (!function_exists('base64_to_uploaded_file')) {
    /**
     * @param string $base64String
     * @param string $fileName
     *
     * @return UploadedFile
     */
    function base64_to_uploaded_file(string $base64String, string $fileName, Closure $closure = null): UploadedFile
    {
        if (!is_base64_image($base64String)) throw \Illuminate\Validation\ValidationException::withMessages(['file' => 'Invalid base64 image.']);

        // Remove data URI scheme if present
        $base64String = preg_replace('#^data:image/[^;]+;base64,#', '', $base64String);

        // Decode the base64 string
        $decodedData = base64_decode($base64String);

        // Generate a temporary file path
        $tempFilePath = tempnam(sys_get_temp_dir(), 'base64_to_uploaded_file');

        // Write the decoded data to the temporary file
        file_put_contents($tempFilePath, $decodedData);

        // Create an UploadedFile instance
        $uploadedFile = new UploadedFile(
            $tempFilePath,
            $fileName,
            mime_content_type($tempFilePath)
        );

        // Optionally, you can delete the temporary file
        // unlink($tempFilePath);
        if (isset($closure)) $closure($tempFilePath);

        return $uploadedFile;
    }
}

if (!function_exists('url_to_uploaded_file')) {
    /**
     * @param string      $url
     * @param string|null $fileName
     *
     * @return UploadedFile
     */
    function url_to_uploaded_file(string $url, string $fileName = null, Closure $closure = null): UploadedFile
    {
        $tempFilePath = tempnam(sys_get_temp_dir(), 'url_to_uploaded_file');

        // Download the file from the URL
        $fileContents = file_get_contents($url);
        file_put_contents($tempFilePath, $fileContents);

        // Determine file name if not provided
        $fileName = $fileName ?: str(basename(parse_url($url, PHP_URL_PATH)))->slug('_')->value();

        // Create an UploadedFile instance
        $uploadedFile = new UploadedFile(
            $tempFilePath,
            $fileName,
            mime_content_type($tempFilePath),
        );

        // Optionally, you can delete the temporary file
        // unlink($tempFilePath);
        if (isset($closure)) $closure($tempFilePath);

        return $uploadedFile;
    }
}

if (!function_exists('path_to_uploaded_file')) {
    /**
     * @param string $path
     *
     * @return UploadedFile
     */
    function path_to_uploaded_file(string $path): UploadedFile
    {
        return new UploadedFile($path, last(explode('/', $path)), mime_content_type($path));
    }
}

