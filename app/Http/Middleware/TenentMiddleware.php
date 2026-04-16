<?php

namespace App\Http\Middleware;

use App\Branch;
use App\Organization;
use Closure;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class TenentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next , $org=null)
    {

//        Session::put('org_test' , $request->org);
        if($request->org){
//            abort(404);

            if(!Session::has('org')){
                dd(Session::get('org'));
                try {
                    $organization = Organization::where('domain', $request->route('org'))->firstOrFail();
                    Session::put('org', $organization);
                    Session::put('organization_id', $organization->id);
                } catch (Exception $e) {
                    abort(500);
                }
            }
            else if (Session::get('org')->domain != $request->route('org')){
                try {
                    $organization = Organization::where('domain', $request->route('org'))->firstOrFail();
                    Session::put('org', $organization);
                    Session::put('organization_id', $organization->id);
                } catch (Exception $e) {
                    abort(500);
                }
            }
        }

//        else {
//            dd($org);
//        }



        if(request('org')){
            URL::defaults([
                'org' => request('org')
            ]);
//            $request->route()->forgetParameter('organization');
        }
        if(request('branch')){
            URL::defaults([
                'branch' => request('branch')
            ]);
//            $request->route()->forgetParameter('branch');
        }


        return $next($request);
    }
}
