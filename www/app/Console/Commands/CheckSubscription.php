<?php

namespace App\Console\Commands;

use App\Services\SubscriptionsService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param SubscriptionsService $subscriberService
     */
    public function handle(SubscriptionsService $subscriberService)
    {
          \Log::info('CHECK SUBSCRIPTION', [
              'data' => Carbon::now()->timestamp * 1000
          ]);
        $subscriberService->checkSubscription();
    }
}
