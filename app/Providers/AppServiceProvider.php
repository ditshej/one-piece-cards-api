<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi): void {
            $openApi->secure(
                SecurityScheme::http('bearer', 'API Key')->as('BearerToken'),
            );

            $contactName = config('scramble.info.contact.name');
            $contactEmail = config('scramble.info.contact.email');
            $contactUrl = config('scramble.info.contact.url');

            if ($contactName || $contactEmail) {
                $label = $contactName ?? $contactEmail;
                $link = $contactEmail ? "[{$label}](mailto:{$contactEmail})" : $label;
                $contactLine = "\n\nTo request an API key, contact {$link}.";

                if ($contactUrl) {
                    $contactLine .= " — [{$contactUrl}]({$contactUrl})";
                }

                $openApi->info->setDescription($openApi->info->description.$contactLine);
            }
        });
    }
}
