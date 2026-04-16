<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Warehouse;
use App\Product_Warehouse;
use App\Product;
use App\Adjustment;
use App\ProductAdjustment;
use DB;
use App\StockCount;
use Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class AdjustmentController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        if( $role->hasPermissionTo('adjustment') ) {
            if(Auth::user()->role_id > 2 && $general_setting->staff_access == 'own')
                $lims_adjustment_all = Adjustment::orderBy('id', 'desc')->where('user_id', Auth::id())->get();
            else
                $lims_adjustment_all = Adjustment::orderBy('id', 'desc')->get();
            return view('adjustment.index', compact('lims_adjustment_all'));
        }
        else
            return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function getProduct($id)
    {
        $lims_product_warehouse_data = DB::table('products')
            ->join('product_warehouse', 'products.id', '=', 'product_warehouse.product_id')->where([ ['products.is_active', 1], ['product_warehouse.warehouse_id', $id] ])->select('product_warehouse.qty', 'products.code', 'products.name')->get();
        $product_code = [];
        $product_name = [];
        $product_qty = [];
        $product_data = [];
        foreach ($lims_product_warehouse_data as $product_warehouse) 
        {
            $product_qty[] = $product_warehouse->qty;
            $product_code[] =  $product_warehouse->code;
            $product_name[] = $product_warehouse->name;
        }

        $product_data[] = $product_code;
        $product_data[] = $product_name;
        $product_data[] = $product_qty;
        return $product_data;
    }

    public function limsProductSearch(Request $request)
    {
        $product_code = explode(" ", $request['data']);
        $lims_product_data = Product::where('code', $product_code[0])->first();

        $product[] = $lims_product_data->name;
        $product[] = $lims_product_data->code;
        $product[] = $lims_product_data->id;
        return $product;
    }

    public function create()
    {
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('adjustment.create', compact('lims_warehouse_list'));
    }

    public function store(Request $request)
    {
        $data = $request->except('document');
        $data['reference_no'] = 'adr-' . date("Ymd") . '-'. date("his");
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $documentName = 'adjustment_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . strtolower($document->getClientOriginalExtension());
            $document->move('public/documents/adjustment', $documentName);
            $data['document'] = $documentName;
        }

        try {
            DB::beginTransaction();

            if( isset($data['stock_count_id']) ){
                $lims_stock_count_data = StockCount::where('id', $data['stock_count_id'])->lockForUpdate()->first();
                if($lims_stock_count_data) {
                    $lims_stock_count_data->is_adjusted = true;
                    $lims_stock_count_data->save();
                }
            }

            $lims_adjustment_data = Adjustment::create($data);

            $product_id = $data['product_id'];
            $qty = $data['qty'];
            $action = $data['action'];

            foreach ($product_id as $key => $pro_id) {
                $line_qty = (float)$qty[$key];
                if($line_qty <= 0) {
                    throw new \RuntimeException('Quantity must be greater than zero');
                }

                $lims_product_data = Product::where('id', $pro_id)->lockForUpdate()->first();
                if(!$lims_product_data) {
                    throw new \RuntimeException('Invalid product in adjustment');
                }

                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $pro_id],
                    ['warehouse_id', $data['warehouse_id']],
                ])->lockForUpdate()->first();
                if(!$lims_product_warehouse_data) {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = 0;
                }

                if($action[$key] == '-') {
                    if((float)$lims_product_warehouse_data->qty < $line_qty || (float)$lims_product_data->qty < $line_qty) {
                        throw new \RuntimeException('Insufficient stock for adjustment');
                    }
                    $lims_product_data->qty -= $line_qty;
                    $lims_product_warehouse_data->qty -= $line_qty;
                }
                elseif($action[$key] == '+') {
                    $lims_product_data->qty += $line_qty;
                    $lims_product_warehouse_data->qty += $line_qty;
                }
                else {
                    throw new \RuntimeException('Invalid adjustment action');
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                ProductAdjustment::create([
                    'product_id' => $pro_id,
                    'adjustment_id' => $lims_adjustment_data->id,
                    'qty' => $line_qty,
                    'action' => $action[$key],
                ]);
            }

            DB::commit();
            return redirect('qty_adjustment')->with('message', 'Data inserted successfully');
        }
        catch(\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('not_permitted', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $lims_adjustment_data = Adjustment::find($id);
        $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        return view('adjustment.edit', compact('lims_adjustment_data', 'lims_warehouse_list', 'lims_product_adjustment_data'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $documentName = 'adjustment_' . date('YmdHis') . '_' . bin2hex(random_bytes(8)) . '.' . strtolower($document->getClientOriginalExtension());
            $document->move('public/documents/adjustment', $documentName);
            $data['document'] = $documentName;
        }

        try {
            DB::beginTransaction();

            $lims_adjustment_data = Adjustment::where('id', $id)->lockForUpdate()->first();
            if(!$lims_adjustment_data) {
                throw new \RuntimeException('Adjustment not found');
            }

            $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
            $product_id = $data['product_id'];
            $qty = $data['qty'];
            $action = $data['action'];
            $old_product_id = [];

            //revert previous adjustment
            foreach ($lims_product_adjustment_data as $key => $product_adjustment_data) {
                $old_product_id[] = $product_adjustment_data->product_id;
                $revert_qty = (float)$product_adjustment_data->qty;

                $lims_product_data = Product::where('id', $product_adjustment_data->product_id)->lockForUpdate()->first();
                if(!$lims_product_data) {
                    throw new \RuntimeException('Invalid product in adjustment');
                }

                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $product_adjustment_data->product_id],
                    ['warehouse_id', $lims_adjustment_data->warehouse_id],
                ])->lockForUpdate()->first();
                if(!$lims_product_warehouse_data) {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $product_adjustment_data->product_id;
                    $lims_product_warehouse_data->warehouse_id = $lims_adjustment_data->warehouse_id;
                    $lims_product_warehouse_data->qty = 0;
                }

                if($product_adjustment_data->action == '-') {
                    $lims_product_data->qty += $revert_qty;
                    $lims_product_warehouse_data->qty += $revert_qty;
                }
                elseif($product_adjustment_data->action == '+') {
                    if((float)$lims_product_warehouse_data->qty < $revert_qty || (float)$lims_product_data->qty < $revert_qty) {
                        throw new \RuntimeException('Cannot update adjustment: stock is already consumed');
                    }
                    $lims_product_data->qty -= $revert_qty;
                    $lims_product_warehouse_data->qty -= $revert_qty;
                }
                else {
                    throw new \RuntimeException('Invalid adjustment action');
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                if( !(in_array($old_product_id[$key], $product_id)) )
                    $product_adjustment_data->delete();
            }

            //apply new adjustment
            foreach ($product_id as $key => $pro_id) {
                $line_qty = (float)$qty[$key];
                if($line_qty <= 0) {
                    throw new \RuntimeException('Quantity must be greater than zero');
                }

                $lims_product_data = Product::where('id', $pro_id)->lockForUpdate()->first();
                if(!$lims_product_data) {
                    throw new \RuntimeException('Invalid product in adjustment');
                }

                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $pro_id],
                    ['warehouse_id', $data['warehouse_id']],
                ])->lockForUpdate()->first();
                if(!$lims_product_warehouse_data) {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = 0;
                }

                if($action[$key] == '-') {
                    if((float)$lims_product_warehouse_data->qty < $line_qty || (float)$lims_product_data->qty < $line_qty) {
                        throw new \RuntimeException('Insufficient stock for adjustment');
                    }
                    $lims_product_data->qty -= $line_qty;
                    $lims_product_warehouse_data->qty -= $line_qty;
                }
                elseif($action[$key] == '+') {
                    $lims_product_data->qty += $line_qty;
                    $lims_product_warehouse_data->qty += $line_qty;
                }
                else {
                    throw new \RuntimeException('Invalid adjustment action');
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_adjustment = [
                    'product_id' => $pro_id,
                    'adjustment_id' => $id,
                    'qty' => $line_qty,
                    'action' => $action[$key],
                ];

                if(in_array($pro_id, $old_product_id)){
                    ProductAdjustment::where([
                        ['adjustment_id', $id],
                        ['product_id', $pro_id]
                    ])->update($product_adjustment);
                }
                else {
                    ProductAdjustment::create($product_adjustment);
                }
            }

            $lims_adjustment_data->update($data);
            DB::commit();
            return redirect('qty_adjustment')->with('message', 'Data updated successfully');
        }
        catch(\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('not_permitted', $e->getMessage());
        }
    }

    public function deleteBySelection(Request $request)
    {
        $adjustment_id = $request['adjustmentIdArray'];
        foreach ($adjustment_id as $id) {
            try {
                DB::beginTransaction();

                $lims_adjustment_data = Adjustment::where('id', $id)->lockForUpdate()->first();
                if(!$lims_adjustment_data) {
                    throw new \RuntimeException('Adjustment not found');
                }

                $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
                foreach ($lims_product_adjustment_data as $key => $product_adjustment_data) {
                    $revert_qty = (float)$product_adjustment_data->qty;
                    $lims_product_data = Product::where('id', $product_adjustment_data->product_id)->lockForUpdate()->first();
                    if(!$lims_product_data) {
                        throw new \RuntimeException('Invalid product in adjustment');
                    }

                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_adjustment_data->product_id],
                        ['warehouse_id', $lims_adjustment_data->warehouse_id]
                    ])->lockForUpdate()->first();
                    if(!$lims_product_warehouse_data) {
                        $lims_product_warehouse_data = new Product_Warehouse();
                        $lims_product_warehouse_data->product_id = $product_adjustment_data->product_id;
                        $lims_product_warehouse_data->warehouse_id = $lims_adjustment_data->warehouse_id;
                        $lims_product_warehouse_data->qty = 0;
                    }

                    if($product_adjustment_data->action == '-') {
                        $lims_product_data->qty += $revert_qty;
                        $lims_product_warehouse_data->qty += $revert_qty;
                    }
                    elseif($product_adjustment_data->action == '+') {
                        if((float)$lims_product_warehouse_data->qty < $revert_qty || (float)$lims_product_data->qty < $revert_qty) {
                            throw new \RuntimeException('Cannot delete adjustment: stock is already consumed');
                        }
                        $lims_product_data->qty -= $revert_qty;
                        $lims_product_warehouse_data->qty -= $revert_qty;
                    }
                    else {
                        throw new \RuntimeException('Invalid adjustment action');
                    }

                    $lims_product_data->save();
                    $lims_product_warehouse_data->save();
                    $product_adjustment_data->delete();
                }
                $lims_adjustment_data->delete();

                DB::commit();
            }
            catch(\Throwable $e) {
                DB::rollBack();
                return $e->getMessage();
            }
        }
        return 'Data deleted successfully';
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $lims_adjustment_data = Adjustment::where('id', $id)->lockForUpdate()->first();
            if(!$lims_adjustment_data) {
                throw new \RuntimeException('Adjustment not found');
            }

            $lims_product_adjustment_data = ProductAdjustment::where('adjustment_id', $id)->get();
            foreach ($lims_product_adjustment_data as $key => $product_adjustment_data) {
                $revert_qty = (float)$product_adjustment_data->qty;
                $lims_product_data = Product::where('id', $product_adjustment_data->product_id)->lockForUpdate()->first();
                if(!$lims_product_data) {
                    throw new \RuntimeException('Invalid product in adjustment');
                }

                $lims_product_warehouse_data = Product_Warehouse::where([
                    ['product_id', $product_adjustment_data->product_id],
                    ['warehouse_id', $lims_adjustment_data->warehouse_id]
                ])->lockForUpdate()->first();
                if(!$lims_product_warehouse_data) {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $product_adjustment_data->product_id;
                    $lims_product_warehouse_data->warehouse_id = $lims_adjustment_data->warehouse_id;
                    $lims_product_warehouse_data->qty = 0;
                }

                if($product_adjustment_data->action == '-') {
                    $lims_product_data->qty += $revert_qty;
                    $lims_product_warehouse_data->qty += $revert_qty;
                }
                elseif($product_adjustment_data->action == '+') {
                    if((float)$lims_product_warehouse_data->qty < $revert_qty || (float)$lims_product_data->qty < $revert_qty) {
                        throw new \RuntimeException('Cannot delete adjustment: stock is already consumed');
                    }
                    $lims_product_data->qty -= $revert_qty;
                    $lims_product_warehouse_data->qty -= $revert_qty;
                }
                else {
                    throw new \RuntimeException('Invalid adjustment action');
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();
                $product_adjustment_data->delete();
            }

            $lims_adjustment_data->delete();
            DB::commit();

            return redirect('qty_adjustment')->with('not_permitted', 'Data deleted successfully');
        }
        catch(\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('not_permitted', $e->getMessage());
        }
    }
}
