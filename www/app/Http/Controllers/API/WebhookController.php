<?php
namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function apple(Request $request, $applicationID)
    {
        dump($applicationID);
        dd($request->all());



    }


}