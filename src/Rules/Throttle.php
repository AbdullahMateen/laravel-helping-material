<?php

namespace AbdullahMateen\LaravelHelpingMaterial\Rules;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;

class Throttle implements ValidationRule
{
    protected $rule = 'limiter';

    protected $key = 'validation';

    protected $maxAttempts = 5;

    protected $decayInMinutes = 10;

    protected $message = 'Too many attempts. Please try again later.';

    public function __construct($key = 'validation', $maxAttempts = 5, $decayInMinutes = 10, $message = 'Too many attempts. Please try again later.')
    {
        $this->key            = $key;
        $this->maxAttempts    = $maxAttempts;
        $this->decayInMinutes = $decayInMinutes;
        $this->message        = $message;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) $fail($this->message());
    }

    public function passes($attribute, $value)
    {
        if ($this->hasTooManyAttempts()) {
            return false;
        }

        $this->incrementAttempts();

        return true;
    }

    public function message()
    {
        return __($this->message);
    }

    protected function hasTooManyAttempts()
    {
        return $this->limiter()->tooManyAttempts(
            $this->throttleKey(), $this->maxAttempts
        );
    }

    protected function incrementAttempts()
    {
        $this->limiter()->hit(
            $this->throttleKey(), $this->decayInMinutes * 60
        );
    }

    protected function throttleKey()
    {
        return $this->key . '|' . $this->request()->ip();
    }

    protected function limiter()
    {
        return app(RateLimiter::class);
    }

    protected function request()
    {
        return app(Request::class);
    }
}
