<?php
namespace App\Http\Controllers\API;


use App\Entities\Application;
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

        \Log::info('EVENT DURATION', [
            'data' => $eventDuration
        ]);


        $filteredEventDuration = $eventDuration->filter(function ($item) {

//            \Log::info('FILTERED', [
//                'product_id' => $subscription->product_id,
//                'product_name' => $item->product_name,
//                'data' => $subscription->product_id == $item->product_name
//            ]);

            return 'com.appitate.callrecorder.monthly' == $item->product_name;
        })->values()->toArray();


        \Log::info('FILTERED EVENT DURATION', [
            'data' => $filteredEventDuration,
            'type' => gettype($filteredEventDuration)
        ]);

        \Log::info('COUNT FILTERED EVENT DURATION', [
            'data' => count($filteredEventDuration)
        ]);

        if (count($filteredEventDuration) == 0) {
            throw new \DomainException("Product name not found");
        }

        $applicationProduct = $filteredEventDuration[0];

        dd($applicationProduct);

    }




    public function testServer()
    {
        $application = Application::all();

        return ['status' => 'OK'];
    }
}
