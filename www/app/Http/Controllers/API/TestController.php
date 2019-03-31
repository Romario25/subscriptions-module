<?php
namespace App\Http\Controllers\API;


use App\Entities\ApplicationProduct;
use App\Entities\Subscription;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class TestController extends Controller
{
    public function index()
    {
//        echo "Test";
//
//        $now = Carbon::now()->timestamp;
//
//        $subscriptions = Subscription::where('end_date', '<', $now * 1000)
//            ->whereIn('type', [Subscription::TYPE_TRIAL, Subscription::TYPE_INITIAL_BUY, Subscription::TYPE_RENEWAL])
//            ->get();
//
//        dd($subscriptions);


        $eventDuration = ApplicationProduct::where('application_id', 1)
            ->get();


        $filteredEventDuration = $eventDuration->filter(function ($item) {
            return 'com.appitate.callrecorder.monthly' == $item->product_name;
        })->values()->toArray();


        if (is_null($filteredEventDuration)) {
            throw new \DomainException("Product name not found");
        }

        $applicationProduct = $filteredEventDuration;

        dd($filtered_collection);

        $keyEventDuration = array_keys($eventDuration);

      //  dd($keyEventDuration);

        $keySearch = array_search('com.appitate.callrecorder.monthly', array_keys($eventDuration));

        $applicationProduct = $eventDuration[$keyEventDuration[$keySearch]];

        dd($applicationProduct);

    }
}
