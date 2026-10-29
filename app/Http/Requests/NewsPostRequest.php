<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class NewsPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'excerpt' => ['required', 'string', 'max:500'],
            'published_at' => ['required', 'date'],
            'link_url' => ['bail', 'nullable', 'required_with:link_label', 'string', 'max:2048',
                function (string $attribute, mixed $value, Closure $fail) {
                    $relative = str_starts_with($value, '/') && ! str_starts_with($value, '//');
                    $absolute = filter_var($value, FILTER_VALIDATE_URL)
                        && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);

                    if ((! $relative && ! $absolute) || preg_match('/[\x00-\x20\x7f]/', $value) || str_contains($value, '\\')) {
                        $fail('The link must be an HTTP/HTTPS URL or a site-relative path beginning with a single /.');
                    }
                },
            ],
            'link_label' => ['nullable', 'required_with:link_url', 'string', 'max:150'],
        ];
    }

    public function newsPostData(): array
    {
        return array_merge(['link_url' => null, 'link_label' => null], $this->validated());
    }
}
