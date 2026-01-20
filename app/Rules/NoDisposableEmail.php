<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class NoDisposableEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow all emails outside production
        if (! App::environment('production')) {
            return;
        }

        if (! is_string($value) || ! str_contains($value, '@')) {
            return;
        }

        $blockedDomains = config('disposableemail.domains', []);

        $domain = strtolower(trim(Str::after($value, '@')));

        foreach ($blockedDomains as $blocked) {
            $blocked = strtolower($blocked);

            // Exact match or subdomain match
            if ($domain === $blocked || Str::endsWith($domain, '.'.$blocked)) {
                $fail('Disposable or test email addresses are not allowed.');

                return;
            }
        }
    }
}
