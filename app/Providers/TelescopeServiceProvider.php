<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->hideSensitiveRequestDetails();
        // Telescope::night();

        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true;
            }
            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });

        Telescope::afterStoring(function (array $entries, $batchId) {

            foreach ($entries as $entry) {
                if ($entry->isReportableException()) {

                    \App\Models\User::whereEmail('raymond@ecreatives.nl')->first()->notify(new \App\Notifications\TrelloNotification([
                        'name' => $entry->type . ":" . $entry->content['class'] ,
                        'description' =>
                            "Message: " . $entry->content['message'] . "\n" .
                            "file: " . $entry->content['file'] . "\n" .
                            "User: " . auth()->user()->email . "\n" .
                            "environment: " . app()->environment() . "\n" .
                            "view in Telescope: " . url('telescope/exceptions/' . $entry->uuid)
                        ]
                    ));

                }
            }
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                'raymond@ecreatives.nl'
            ]);
        });
    }
}
