<?php
namespace App\Http\Controllers\API;


use App\Entities\Subscription;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class TestController extends Controller
{
    public function index()
    {
        echo "Test";

        $now = Carbon::now()->timestamp;

        $subscriptions = Subscription::where('end_date', '<', $now * 1000)
            ->whereIn('type', [Subscription::TYPE_TRIAL, Subscription::TYPE_INITIAL_BUY, Subscription::TYPE_RENEWAL])
            ->get();

        dd($subscriptions);
    }
}