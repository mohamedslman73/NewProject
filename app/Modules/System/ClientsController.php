<?php

namespace App\Modules\System;

use App\Libs\Payments\Validator;
use App\Models\Client;
use App\Models\ClientTypes;
use App\Models\Item;
use App\Models\ItemCategories;
use App\Models\Supplier;
use App\Models\SupplierCategories;
use function foo\func;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;

class ClientsController extends SystemController
{
    public function __construct(){
        parent::__construct();
        $this->viewData['breadcrumb'] = [
            [
                'text'=> __('Home'),
                'url'=> url('system'),
            ]
        ];
    }
    public function index(Request $request)
    {

        if ($request->isDataTable) {
            $eloquentData = Client::select([
                'clients.id',
                'clients.name',
                'clients.email',
                'clients.status',
                'clients.staff_id',
                'clients.phone',
                'clients.mobile',
                'clients.address',
                'clients.organization_name',
                'clients.created_at',
                'clients.client_type_id',
                \DB::Raw("CONCAT(staff.firstname,' ',staff.lastname) as staff_name"),
                ])
                ->join('staff', 'staff.id', '=', 'clients.staff_id');

            if ($request->withTrashed) {
                $eloquentData->onlyTrashed();
            }

            whereBetween($eloquentData, 'DATE(clients.created_at)', $request->created_at1, $request->created_at2);

            if ($request->id) {
                $eloquentData->where('clients.id', '=', $request->id);
            }
            if ($request->name) {
                $eloquentData->where('clients.name', 'LIKE', '%' . $request->name . '%');
            }
            if ($request->organization_name) {
                $eloquentData->where('clients.organization_name', 'LIKE', '%'.$request->organization_name.'%');
            }

            if ($request->status) {
                $eloquentData->where('clients.status', '=', $request->status);
            }
            if ($request->staff_id) {
                $eloquentData->where('clients.staff_id', '=', $request->staff_id);
            }
            if ($request->client_type_id) {
                $eloquentData->where('clients.client_type_id', '=', $request->client_type_id );
            }
            if ($request->mobile) {
                $eloquentData->where('clients.mobile', '=', $request->mobile);
            }
            if ($request->phone) {
                $eloquentData->where('clients.phone', '=', $request->phone);
            }
            return Datatables::eloquent($eloquentData)
                ->addColumn('id', '{{$id}}')
                ->addColumn('name', '{{$name}}')
                ->addColumn('organization_name', '{{$organization_name}}')
                ->addColumn('email', ' <a href="mailto:{{$email}}">{{$email}}</a>')
                ->addColumn('mobile', '  <a href="tel:{{$mobile}}">{{$mobile}}</a>')
                ->addColumn('address', '{{$address}}')
                ->addColumn('client_type', function ($data){
                    //return $data->client_types->name;
                    return "<a target='_blank' href=\"" . route('system.types.show', $data->client_types->id) . "\">".$data->client_types->name."</a>";
                })
               // ->addColumn('staff_name', '<a href="{{route(\'system.staff.show\',$staff_id)}}" target="_blank">{{$staff_name}}</a>')
                ->addColumn('created_at', function ($data) {
                    return $data->created_at->format('Y-m-d h:iA');
                })
                ->addColumn('action', function ($data) {
                    return " <div class=\"dropdown\">
                              <button class=\"btn btn-primary dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><i class=\"ft-cog icon-left\"></i>
                              <span class=\"caret\"></span></button>
                              <ul class=\"dropdown-menu\">
                                <li class=\"dropdown-item\"><a href=\"" . route('system.client.show', $data->id) . "\">" . __('View') . "</a></li>
                                <li class=\"dropdown-item\"><a href=\"" . route('system.client.edit', $data->id) . "\">" . __('Edit') . "</a></li>
                                <li class=\"dropdown-item\"><a onclick=\"deleteRecord('" . route('system.client.destroy', $data->id) . "')\" href=\"javascript:void(0)\">" . __('Delete') . "</a></li>
                              </ul>
                            </div>";
                })
                ->addColumn('status', function ($data) {
                    if ($data->status == 'in-active') {
                        return 'tr-danger';
                    }
                })
                ->make(true);
        } else {
            // View Data
            $this->viewData['tableColumns'] = [
                __('ID'),
                __('Name'),
                __('Organization Name'),
                __('E-mail'),
                __('Mobile'),
                __('Address'),
                __('Client Type'),
                __('Created At'),
                __('Action')];
            $this->viewData['breadcrumb'][] = [
                'text' => __('Clients')
            ];

            if ($request->withTrashed) {
                $this->viewData['pageTitle'] = __('Deleted Clients');
            } else {
                $this->viewData['pageTitle'] = __('Clients');
            }

            $return = [];
            $data = ClientTypes::get(['id', 'name']);
            foreach ($data as $key => $value) {
                $return[$value->id] = $value->name;
            }
            $this->viewData['client_types'] = $return;

            return $this->view('clients.index', $this->viewData);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Main View Vars
        $this->viewData['breadcrumb'][] = [
            'text' => __('Client'),
            'url' => route('system.client.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Create Client'),
        ];
        $return = [];
        $data = ClientTypes::get(['id', 'name']);
        foreach ($data as $key => $value) {
            $return[$value->id] = $value->name;
        }
        $this->viewData['client_types'] = $return;
        $this->viewData['pageTitle'] = __('Create Client');
        return $this->view('clients.create', $this->viewData);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
           'name'                               =>'required',
           'status'                             =>'required|in:active,in-active',
           'client_type_id'                     =>'required|exists:client_types,id',
           'organization_name'                  =>'required',
           'email'                              =>'nullable|email',
           'address'                            =>'required|min:5',
            'phone'                             =>'required|numeric',
            'mobile'                            =>'required|numeric',
         //   'id_number'                     =>'required|numeric|unique:clients,id_number',

        ]);
        $theRequest = $request->all();
     $theRequest['staff_id']  = Auth::id();
        $supplier = Client::create($theRequest);
        if ($supplier)
            return redirect()
                ->route('system.client.create')
                ->with('status', 'success')
                ->with('msg', __('Data has been added successfully'));
        else {
            return redirect()
                ->route('system.client.create')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t add Client'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */

    public function show(Client $client)
    {
        $this->viewData['breadcrumb'] = [
            [
                'text' => __('Home'),
                'url' => url('system'),
            ],
            [
                'text' => __('Client'),
                'url' => route('system.client.index'),
            ],
            [
                'text' => 'Show',
            ]
        ];

        $this->viewData['total_orders'] = $client->client_order()->sum('total_price');
        $this->viewData['total_revenue'] = $client->client_revenue()->sum('amount');
        $this->viewData['order_count'] = $client->client_order()->count('id');


        $this->viewData['pageTitle'] = 'Client';
        $this->viewData['result'] = $client;
        return $this->view('clients.show', $this->viewData);
    }

    public function edit(Client $client)
    {
        $this->viewData['breadcrumb'][] = [
            'text' => __('Client'),
            'url' => route('system.client.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Edit Client'),
        ];
        $return = [];
        $data = ClientTypes::get(['id', 'name']);
        foreach ($data as $key => $value) {
            $return[$value->id] = $value->name;
        }
        $this->viewData['client_types'] = $return;

        $this->viewData['pageTitle'] = __('Edit Client');
        $this->viewData['result'] = $client;

        return $this->view('clients.create', $this->viewData);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Client $client)
    {
        $this->validate($request,[
            'name'                          =>'required',
            'status'                         =>'required|in:active,in-active',
            'client_type_id'                 =>'required|exists:client_types,id',
            'organization_name'              =>'required',
            'email'                          =>'required|email',
            'address'                        =>'required|min:5',
            'phone'                          =>'required|numeric',
            'mobile'                         =>'required|numeric',
            'id_number'                      =>'numeric|unique:clients,id_number'.iif($request->id, ',' . $client->id),

        ]);
        $theRequest = $request->all();
        if ($client->update($theRequest)) {
            return redirect()
                ->route('system.client.edit', $client->id)
                ->with('status', 'success')
                ->with('msg', __('Successfully Edit Client'));
        }
        else {
            return redirect()
                ->route('system.client.edit')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t Edit Client'));
        }
    }

    /**
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Client $client)
    {
        $client->delete();
        if ($request->ajax()) {
            return ['status' => true, 'msg' => __('Item  has been deleted successfully')];
        } else {
            redirect()
                ->route('system.client.index')
                ->with('status', 'success')
                ->with('msg', __('This Client  has been deleted'));
        }
    }

    public function clientReport(Request $request){

        if ($request->isDataTable) {

            $eloquentData = Client::select([
                'clients.id',
                'clients.name',
                'revenues.created_at',
                'client_orders.created_at as created',
                \DB::raw('SUM(revenues.amount) as total_revenues'),
                \DB::raw('SUM(client_orders.total_price) as total_price'),
            ])
                ->join('revenues','clients.id','=','revenues.client_id')
                ->join('client_orders','clients.id','=','client_orders.client_id')
                ->groupBy('clients.id');
            if ($request->withTrashed) {
                $eloquentData->onlyTrashed();
            }

            whereBetween($eloquentData, 'DATE(client_orders.created)', $request->client_order_created_at1, $request->client_order_created_at2);
            whereBetween($eloquentData, 'DATE(revenues.created_at)', $request->revenue_created_at1, $request->revenue_created_at2);
//            whereBetween($eloquentData, 'total_price', $request->total_order_price1, $request->total_order_price2);
            if ($request->name) {
                $eloquentData->where('clients.name', 'LIKE', '%' . $request->name . '%');
            }
            if ($request->client_id) {
                $eloquentData->where('clients.id', '=',  $request->client_id);
            }

            return Datatables::eloquent($eloquentData)
                ->addColumn('id', '{{$id}}')
                ->addColumn('name', '{{$name}}')
                ->addColumn('total_price', '{{$total_price}}')
                ->addColumn('total_revenues', '{{$total_revenues}}')
                ->addColumn('total_e',function ($data){
                    $difference = $data->total_price - $data->total_revenues;
                    if ($difference >0){
                        return $difference .' On him';
                    }
                    return $difference .' For him';
                })

                ->addColumn('action', function ($data) {
                    return " <div class=\"dropdown\">
                              <button class=\"btn btn-primary dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><i class=\"ft-cog icon-left\"></i>
                              <span class=\"caret\"></span></button>
                              <ul class=\"dropdown-menu\">
                                <li class=\"dropdown-item\"><a href=\"" . route('system.client.credit-details', $data->id) . "\">" . __('View') . "</a></li>
                                
                              </ul>
                            </div>";
                })
                ->make(true);
        } else {
            // View Data
            $this->viewData['tableColumns'] = [
                __('ID'),
                __('Client Name'),
                __('Total Order Prices'),
                __('Total Slices'),
                __('Credit'),
                __('Action')];
            $this->viewData['breadcrumb'][] = [
                'text' => __('Clients Report')
            ];

            if ($request->withTrashed) {
                $this->viewData['pageTitle'] = __('Deleted Clients Report');
            } else {
                $this->viewData['pageTitle'] = __('Clients Report');
            }
            return $this->view('clients.client-order', $this->viewData);
        }
    }

    public function clientCreditDetails(Request $request){

        whereBetween($eloquentData, 'DATE(clients_orders.created_at)', $request->client_order_created_at1, $request->client_order_created_at2);
        whereBetween($eloquentData, 'DATE(revenues.created_at)', $request->revenue_created_at1, $request->revenue_created_at2);

        $eloquentData = Client::where('id',$request->id)
            ->with([
                'client_order'=>function($q){
                    $q->selectRaw("*,'client_order' as type ");
                },'client_revenue'=>function($q){
                    $q->selectRaw("*,'client_revenue' as type ");
                } ])->first()->toArray();
        $all = array_merge($eloquentData['client_order'],$eloquentData['client_revenue']);

        foreach ($all as $key => $row) {
            $orderByDate[$key] = strtotime($row ['created_at'] );
        }

        array_multisort($orderByDate, SORT_ASC, $all);


//        if($request->page)
//            $page = $request->page;
//        else
//            $page = 1;
//        // Get current page form url e.x. &page=1
//         //$currentPage = LengthAwarePaginator::resolveCurrentPage('page',$page);
//         $currentPage = LengthAwarePaginator::resolveCurrentPage('page',$page);
//        // Create a new Laravel collection from the array data
//        $itemCollection = collect($all);
//        // Define how many items we want to be visible in each page
//        $perPage = 5;
//        // Slice the collection to get the items to display in current page
//        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
//        // Create our paginator and pass it to the view
//        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
//        // set url path for generted links
////        $exurl = '';
////
////        $exurl .= '';
//    //    $paginatedItems->setPath($request->url().'?id='.$request->id.$exurl);
//       $paginatedItems->setPath($request->url());
//        $this->viewData['tableColumns'] = $paginatedItems;
//        $this->viewData['supplier'] = $eloquentData;
       // dd($all);
        $this->viewData['tableColumns'] = $all;
        $this->viewData['client'] = $eloquentData;
        $this->viewData['breadcrumb'][] = [
            'text' => __('Client Credit')
        ];
        $this->viewData['pageTitle'] = __('Suppliers Report');
        return $this->view('clients.client-credit-details', $this->viewData);
    }

}
