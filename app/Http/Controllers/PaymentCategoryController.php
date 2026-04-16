<?php

namespace App\Http\Controllers;

use App\PaymentCategory;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PaymentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $categories = PaymentCategory::with('role')->get();

        return view('payments_category.index' , compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $roles = Role::all();
        return view('payments_category.create' , compact('roles'));
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
        $category = new PaymentCategory();
        $category->fill($request->all());
        $category->save();
        return redirect(route('payments_category.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\PaymentCategory  $paymentCategory
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentCategory $paymentCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\PaymentCategory  $paymentCategory
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $roles = Role::all();
        $paymentCategory = PaymentCategory::find($id);
        return view('payments_category.edit' , compact('roles' , 'paymentCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\PaymentCategory  $paymentCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $paymentCategory = PaymentCategory::find($id);
        $paymentCategory->fill($request->all());
        $paymentCategory->save();
        return redirect(route('payments_category.index'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PaymentCategory  $paymentCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentCategory $paymentCategory)
    {
        //
    }
}
