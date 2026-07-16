<?php

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Scheme;

return [

    /*
     * Presets will determine which CSP headers will be set. A valid CSP preset is
     * any class that implements `Spatie\Csp\Preset`
     */
    'presets' => [
        Spatie\Csp\Presets\Basic::class,
    ],

    /**
     * Register additional global CSP directives here.
     */
    'directives' => [
        // [Directive::SCRIPT, [Keyword::UNSAFE_EVAL, Keyword::UNSAFE_INLINE]],

        [Directive::DEFAULT, Keyword::SELF],
        
        // Allows local hot module replacements (Vite / Mix via WebSockets)
        [Directive::CONNECT, Keyword::SELF],
        [Directive::CONNECT, 'ws://127.0.0.1:*'],
        [Directive::CONNECT, 'wss://127.0.0.1:*'],
        [Directive::CONNECT, 'http://localhost:*'],
        [Directive::CONNECT, 'https://cloudflareinsights.com'],

        // Images: allows local files, base64 data, and object blobs
        [Directive::IMG, Keyword::SELF],
        [Directive::IMG, Scheme::DATA],
        [Directive::IMG, Scheme::BLOB],
        [Directive::IMG, 'https:'],
        
        // Add external image URLs here:
        [Directive::IMG, 'https://images.unsplash.com'],
        [Directive::IMG, 'https://*.amazonaws.com'],     
        [Directive::IMG, 'https://*.wp.com'],       

        // Styles & Scripts: uses 'self' + allows you to use @nonce in Blade layout
        [Directive::STYLE, Keyword::SELF],
        [Directive::SCRIPT, Keyword::SELF],
        
        // Add external style URLs here:
        [Directive::STYLE, '\'unsafe-hashes\''],
        [Directive::STYLE, 'https://fonts.googleapis.com'],
        [Directive::STYLE, 'sha256-hI8efTOPRiIGrd87X040JueDVUl1QhtnuuKuWdKeLpE='],
        [Directive::STYLE, 'sha256-bpqk8WwYOr1nqYWuY+bFZrAeRDP1wZHGsXpYhWFiA1s='],
        [Directive::STYLE, 'sha256-2EA12+9d+s6rrc0rkdIjfmjbh6p2o0ZSXs4wbZuk/tA='],

        [Directive::SCRIPT, 'https://static.cloudflareinsights.com'],
        
        // Standard fonts fallback
        [Directive::FONT, Keyword::SELF],
        [Directive::FONT, Scheme::DATA],
        
        // Add external fonts URLs here:
        [Directive::FONT, 'https://fonts.gstatic.com'],

    ],

    /*
     * These presets which will be put in a report-only policy. This is great for testing out
     * a new policy or changes to existing CSP policy without breaking anything.
     */
    'report_only_presets' => [
        //
    ],

    /**
     * Register additional global report-only CSP directives here.
     */
    'report_only_directives' => [
        // [Directive::SCRIPT, [Keyword::UNSAFE_EVAL, Keyword::UNSAFE_INLINE]],
    ],

    /*
     * All violations against a policy will be reported to this url.
     * A great service you could use for this is https://report-uri.com/
     */
    'report_uri' => env('CSP_REPORT_URI', ''),

    /*
     * Optional separate report url for the report-only policy. When empty,
     * the report-only policy falls back to `report_uri` above. Useful for
     * services like report-uri.com that require different paths for enforcing
     * (`/enforce`) and report-only (`/reportOnly`) policies.
     */
    'report_only_uri' => env('CSP_REPORT_ONLY_URI', ''),

    /*
     * The name of the reporting endpoint that violations should be sent to.
     * The endpoint itself must be defined in `reporting_endpoints` below.
     */
    'report_to' => env('CSP_REPORT_TO', ''),

    /*
     * Optional separate reporting endpoint name for the report-only policy.
     * When empty, the report-only policy falls back to `report_to` above.
     */
    'report_only_to' => env('CSP_REPORT_ONLY_TO', ''),

    /*
     * Reporting endpoints that will be sent in the `Reporting-Endpoints` HTTP
     * header. The keys are the endpoint names that can be referenced from
     * `report_to` above.
     *
     * Example: ['default' => 'https://example.com/csp-reports']
     */
    'reporting_endpoints' => [
        //
    ],

    /*
     * Headers will only be added if this setting is set to true.
     */
    'enabled' => env('CSP_ENABLED', true),

    /**
     * Headers will be added when Vite is hot reloading.
     */
    'enabled_while_hot_reloading' => env('CSP_ENABLED_WHILE_HOT_RELOADING', false),

    /*
     * The class responsible for generating the nonces used in inline tags and headers.
     */
    'nonce_generator' => Spatie\Csp\Nonce\RandomString::class,

    /*
     * Set false to disable automatic nonce generation and handling.
     * This is useful when you want to use 'unsafe-inline' for scripts/styles
     * and cannot add inline nonces.
     * Note that this will make your CSP policy less secure.
     */
    'nonce_enabled' => env('CSP_NONCE_ENABLED', true),
];
