<?php

namespace App\Http\Controllers;

use App\Organization;
use App\PaymentCategory;
use App\SystemPayment;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $organizations = Organization::orderBy('id')->get();
        return view('organization.index' , compact('organizations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $organization = new Organization();
        $organization->fill($request->all());
        $organization->domain = str_replace(' ', '-' , $request->name);
        $organization->expires_at = Carbon::today()->addMonth();
        $organization->save();
        Session::put('new_organization' , $organization->id);
        return redirect(route('user.create'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function edit(Organization $organization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Organization $organization)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Organization  $organization
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        //
    }

    public function payments(Organization $organization , User $user){
        $payments = SystemPayment::where('organization_id' , $organization->id)->where('user_id' , $user->id)->orderBy('id' , 'DESC')->get();

        $categories = PaymentCategory::all();
        return view('organization.payment' , compact('payments' , 'organization' , 'user' , 'categories'));
    }

    public function users(Organization $organization){
        $users = User::with('role')
            ->where('organization_id' , $organization->id)->orderBy('id' , 'DESC')->get();
        $role = Role::find(Auth::user()->role_id);
        $permissions = Role::findByName($role->name)->permissions;
        foreach ($permissions as $permission)
            $all_permission[] = $permission->name;
//        return $users;
//        $payments = SystemPayment::where('organization_id' , $organization->id)->orderBy('id' , 'DESC')->get();
        return view('organization.user' , compact('users' , 'organization' , 'all_permission'));
    }
}
