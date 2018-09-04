<?php

namespace App\Modules\System;

use App\Libs\Payments\Validator;
use App\Models\Brand;
use App\Models\Bus;
use App\Models\BusTraking;
use App\Models\Client;
use App\Models\ClientTypes;
use App\Models\Item;
use App\Models\ItemCategories;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\SupplierCategories;
use App\Notifications\UserNotification;
use function foo\func;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Facades\Datatables;

class BusTrakingController extends SystemController
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
            $eloquentData = BusTraking::select([
                'bus_traking.id',
                'bus_traking.bus_id',
                'bus_traking.project_id',
                'bus_traking.driver_id',
                'bus_traking.number_km',
                'bus_traking.date_from',
                'bus_traking.date_to',
                'bus_traking.destination_from',
                'bus_traking.destination_to',
                'bus_traking.cost_per_km',
                'bus_traking.staff_id',
                'bus_traking.created_at',
                \DB::Raw("CONCAT(staff.firstname,' ',staff.lastname) as staff_name"),
                ])
                ->join('staff', 'staff.id', '=', 'bus_traking.staff_id');

            if ($request->withTrashed) {
                $eloquentData->onlyTrashed();
            }

            whereBetween($eloquentData, 'DATE(bus_traking.created_at)', $request->created_at1, $request->created_at2);
            whereBetween($eloquentData, 'DATE(bus_traking.date)', $request->date1, $request->date2);
            whereBetween($eloquentData, 'bus_traking.number_km', $request->number_km1, $request->number_km2);

            if ($request->id) {
                $eloquentData->where('bus_traking.id', '=', $request->id);
            }
            if ($request->from) {
                $eloquentData->where('bus_traking.bus_number', 'LIKE', '%'. $request->from.'%');
            }
            if ($request->to) {
                $eloquentData->where('bus_traking.bus_number', 'LIKE', '%'. $request->to.'%');
            }
            if ($request->driver_id) {
                $eloquentData->where('bus_traking.driver_id', '=', $request->driver_id);
            }

            if ($request->bus_id) {
                $eloquentData->where('bus_traking.bus_id', '=', $request->bus_id);
            }
            if ($request->project_id) {
                $eloquentData->where('bus_traking.project_id', '=', $request->project_id);
            }
            if ($request->staff_id) {
                $eloquentData->where('buses.staff_id', '=', $request->staff_id);
            }
            return Datatables::eloquent($eloquentData)
                ->addColumn('id', '{{$id}}')
                ->addColumn('bus_id', function ($data){
                    return $data->bus->bus_number;
                })
                ->addColumn('project_id', function ($data){
                    return $data->project->name;
                })
                ->addColumn('driver_id', function ($data){
                      return "<a target='_blank' href=\"" . route('system.staff.show', $data->busDriver->id) . "\">".$data->busDriver->Fullname."</a>";
                })
                ->addColumn('number_km', '{{$number_km}}')

                ->addColumn('destination_from','{{$destination_from}}')
                ->addColumn('destination_to','{{$destination_to}}')
                ->addColumn('staff_name', '<a href="{{route(\'system.staff.show\',$staff_id)}}" target="_blank">{{$staff_name}}</a>')
                ->addColumn('created_at', function ($data) {
                    return $data->created_at->diffForHumans();
                })
                ->addColumn('action', function ($data) {
                    return " <div class=\"dropdown\">
                              <button class=\"btn btn-primary dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><i class=\"ft-cog icon-left\"></i>
                              <span class=\"caret\"></span></button>
                              <ul class=\"dropdown-menu\">
                                <li class=\"dropdown-item\"><a href=\"" . route('system.traking.show', $data->id) . "\">" . __('View') . "</a></li>
                                <li class=\"dropdown-item\"><a href=\"" . route('system.traking.edit', $data->id) . "\">" . __('Edit') . "</a></li>
                                <li class=\"dropdown-item\"><a onclick=\"deleteRecord('" . route('system.traking.destroy', $data->id) . "')\" href=\"javascript:void(0)\">" . __('Delete') . "</a></li>
                              </ul>
                            </div>";
                })
                ->make(true);
        } else {
            // View Data

            $this->viewData['tableColumns'] = [
                __('ID'),
                __('Bus Number'),
                __('Project name'),
                __('Driver Name'),
                __('Number Of Km'),
                __('From'),
                __('To'),
                __('Created By'),
                __('Created At'),
                __('Action')];
            $this->viewData['breadcrumb'][] = [
                'text' => __('Bus Traking')
            ];

            if ($request->withTrashed) {
                $this->viewData['pageTitle'] = __('Deleted Bus Traking');
            } else {
                $this->viewData['pageTitle'] = __('Bus Traking');
            }

            return $this->view('bus-traking.index', $this->viewData);
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
            'text' => __('Bus Traking'),
            'url' => route('system.traking.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Create Bus Traking'),
        ];

        $this->viewData['pageTitle'] = __('Create Bus Traking');
        return $this->view('bus-traking.create', $this->viewData);

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
           'bus_id'                              =>'required|exists:buses,id',
           'project_id'                          =>'required|exists:projects,id',
           'driver_id'                           =>'required|exists:staff,id',
           'number_km'                           =>'required',
           'date_from'                           =>'required|date',
          //  'date_to'                            =>'required|date',
            'destination_from'                   =>'required',
            'destination_to'                     =>'required',
         //   'cost_per_km'                    =>'required',
        ]);
        $theRequest = $request->all();
     $theRequest['staff_id']  = Auth::id();
        $busTraking = BusTraking::create($theRequest);

        if ($busTraking) {


            $bus = $busTraking->bus;
            $newFixedDistance = $bus->fixed_distance + $theRequest['number_km'];
            $newVariableDistance = $bus->variable_distance + $theRequest['number_km'];
            $bus->update(['fixed_distance' => $newFixedDistance, 'variable_distance' => $newVariableDistance]);


            // Notify Staff For Changing the oil for $busTraking->bus Bus.

            $variable_distance = $busTraking->bus->variable_distance;

            $no_km_moving = $busTraking->bus->maintenance()->latest()->first()->no_km_moving;
            if ($no_km_moving >= $variable_distance) {
                if (!empty(setting('monitor_staff'))) {
                    $monitorStaff = Staff::whereIn('id', explode("\n", setting('monitor_staff')))
                        ->get();

                    foreach ($monitorStaff as $key => $value) {
                        $value->notify(
                            (new UserNotification([
                                'title' => 'Bus Should Change Oil',
                                'description' => 'Bus Number' .$busTraking->bus->bus_number .'Should Change Oil',
                                'url' => route('merchant.maintenance.show', $busTraking->bus->maintenance()->latest()->first()->id)
                            ]))
                                ->delay(5)
                        );
                    }
                }
            }

            return redirect()
                ->route('system.traking.create')
                ->with('status', 'success')
                ->with('msg', __('Data has been added successfully'));
        } else {
            return redirect()
                ->route('system.traking.create')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t add Bus Traking'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */

    public function show(BusTraking $traking)
    {
        $this->viewData['breadcrumb'] = [
            [
                'text' => __('Home'),
                'url' => url('system'),
            ],
            [
                'text' => __('Bus Traking'),
                'url' => route('system.traking.index'),
            ],
            [
                'text' => 'Show',
            ]
        ];
//
//
        $this->viewData['pageTitle'] = 'Bus Traking';
        $this->viewData['result'] = $traking;
        return $this->view('bus-traking.show', $this->viewData);
    }

    public function edit(BusTraking $traking)
    {
//        dd($traking->toArray());
        $this->viewData['breadcrumb'][] = [
            'text' => __('Bus Traking'),
            'url' => route('system.traking.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Edit Bus Traking'),
        ];

        $this->viewData['pageTitle'] = __('Edit Bus Traking');
        $this->viewData['result'] = $traking;

        return $this->view('bus-traking.create', $this->viewData);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,BusTraking $traking)
    {
        if (!empty($traking->date_to)){
            return redirect()
                ->route('system.traking.edit', $traking->id)
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t Edit This Bus Traking Again'));
        }
        $this->validate($request,[
            'bus_id'                              =>'nullable|exists:buses,id',
            'project_id'                          =>'required|exists:projects,id',
            'driver_id'                           =>'nullable|exists:staff,id',
            'number_km'                           =>'required',
            'date_from'                           =>'required|date',
           // 'date_to'                            =>'required|date',
            'destination_from'                   =>'required',
            'destination_to'                     =>'required',
            //   'cost_per_km'                    =>'required',
        ]);

        $theRequest = $request->all();

        if ($request->has('bus_id')){
            $theRequest['bus_id'] = $request->bus_id;
        }else{
            unset($theRequest['bus_id']);
        }
        if ($request->has('driver_id')){
            $theRequest['driver_id'] = $request->driver_id;
        }else{
            unset($theRequest['driver_id']);
        }

        if ($traking->update($theRequest)) {
            $bus = $traking->bus;
            $newFixedDistance = $bus->fixed_distance + $theRequest['number_km'];
            $newVariableDistance = $bus->variable_distance + $theRequest['number_km'];
           $bus->update(['fixed_distance' => $newFixedDistance, 'variable_distance' => $newVariableDistance]);


            $variable_distance = $traking->bus->variable_distance;

            $no_km_moving = $traking->bus->maintenance()->latest()->first()->no_km_moving;
            if ($no_km_moving >= $variable_distance) {
                if (!empty(setting('monitor_staff'))) {
                    $monitorStaff = Staff::whereIn('id', explode("\n", setting('monitor_staff')))
                        ->get();

                    foreach ($monitorStaff as $key => $value) {
                        $value->notify(
                            (new UserNotification([
                                'title' => 'Bus Should Change Oil',
                                'description' => 'Bus Number' .$traking->bus->bus_number .'Should Change Oil',
                                'url' => route('merchant.maintenance.show', $traking->bus->maintenance()->latest()->first()->id)
                            ]))
                                ->delay(5)
                        );
                    }
                }
            }

            return redirect()
                ->route('system.traking.edit', $traking->id)
                ->with('status', 'success')
                ->with('msg', __('Successfully Edit Bus Traking'));
        }
        else {
            return redirect()
                ->route('system.traking.edit')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t Edit Bus Traking'));
        }
    }

    /**
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,BusTraking $traking)
    {
        $traking->delete();
        if ($request->ajax()) {
            return ['status' => true, 'msg' => __('Bus Traking has been deleted successfully')];
        } else {
            redirect()
                ->route('system.traking.index')
                ->with('status', 'success')
                ->with('msg', __('This Bus Traking  has been deleted'));
        }
    }
}
