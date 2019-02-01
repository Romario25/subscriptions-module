<?php
namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    private $applicationService;

    public function __construct(ApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }


    public function add(Request $request)
    {
        $this->applicationService->add(
             $request->input("bundle_id"),
            $request->input('app_id'),
             $request->input('name'),
             $request->input('environment')
        );

        return [
            'status' => 'OK'
        ];
    }
}