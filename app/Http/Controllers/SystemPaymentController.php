<?php

namespace App\Http\Controllers;

use App\Organization;
use App\SystemPayment;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SystemPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $payment = new SystemPayment();
        $payment->fill($request->all());
        $payment->save();
        $organization = Organization::find($payment->organization_id);
        $user = User::find($payment->user_id);
        $user->expires_at =  Carbon::parse($user->expires_at)->addMonths($payment->duration);
        $user->role_id = $payment->category->role_id;
        $user->save();
        return redirect(route('organization.users' , ['organization' => $organization ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SystemPayment  $systemPayment
     * @return \Illuminate\Http\Response
     */
    public function show(SystemPayment $systemPayment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SystemPayment  $systemPayment
     * @return \Illuminate\Http\Response
     */
    public function edit(SystemPayment $systemPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SystemPayment  $systemPayment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SystemPayment $systemPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SystemPayment  $systemPayment
     * @return \Illuminate\Http\Response
     */
    public function destroy(SystemPayment $systemPayment)
    {
        //
    }
}
