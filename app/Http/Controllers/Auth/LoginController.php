<?php

  

namespace App\Http\Controllers\Auth;

  

use App\Http\Controllers\Controller;

use App\Organization;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;


class LoginController extends Controller

{

  

    use AuthenticatesUsers;

    

    protected $redirectTo = '/';

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function __construct()

    {
//        if(request('organization')){
//            session('organization' , request('organization'));
//        }
//        $this->middleware('tenent');
        $this->middleware(['guest' ])->except('logout');
//        $this->middleware(['tenent'])->only('logout');

    }

  

    /**

     * Create a new controller instance.

     *

     * @return void

     */

    public function login(Request $request)

    {   

        $input = $request->all();

        $this->validate($request, [

            'name' => 'required',

            'password' => 'required',

        ]);

  

        $fieldType = filter_var($request->name, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if(auth()->attempt(array($fieldType => $input['name'], 'password' => $input['password'])))

        {

            if(auth()->user()->organization_id){
                $organization = Organization::find(auth()->user()->organization_id);
                Session::put('org', $organization);
                Session::put('organization_id', $organization->id);
//                dd(Session::get('org'));
//                URL::defaults([
//                    'org' => $organization->domain
//                ]);
//                return redirect(
//                    route('org.home' , [
//                        'org' => $organization->domain
//                    ])
//                );
//                return redirect(url('/org/' . $organization->domain . '/'));
            }
            return redirect('/');

        }else{

            return redirect()->route('login')

                ->with('error','Email-Address And Password Are Wrong.');

        }

          

    }

}