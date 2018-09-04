<?php

namespace App\Modules\System;

use App\Models\Complain;
use App\Models\ItemCategories;
use App\Models\Project;
use App\Models\SupplierCategories;
use App\Models\VacationTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Facades\Datatables;

class ComplainController extends SystemController
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
            $eloquentData = Complain::select([
                'complains.id',
                'complains.project_id',
                'complains.call_details',
                'complains.staff_id',
                'complains.complain_client_id',
                'complains.order_date',
                'complains.complain_of_staff_id',
                'complains.created_at',
                \DB::Raw("CONCAT(staff.firstname,' ',staff.lastname) as staff_name"),
            ])
                ->join('staff', 'staff.id', '=', 'complains.staff_id');

            if ($request->withTrashed) {
                $eloquentData->onlyTrashed();
            }

            whereBetween($eloquentData, 'DATE(complains.created_at)', $request->created_at1, $request->created_at2);

            if ($request->id) {
                $eloquentData->where('complains.id', '=', $request->id);
            }
            if ($request->staff_id) {
                $eloquentData->where('complains.staff_id', '=', $request->staff_id);
            }
            if ($request->complain_of_staff_id) {
                $eloquentData->where('complains.complain_of_staff_id', '=', $request->complain_of_staff_id);
            }

            if ($request->comment) {
                $eloquentData->where('complains.comment', 'LIKE', '%' . $request->call_details . '%');
            }
            if ($request->project_id) {
                $eloquentData->where('complains.project_id', '=', $request->project_id);
            }

            return Datatables::eloquent($eloquentData)
                ->addColumn('id', '{{$id}}')
                ->addColumn('project', function ($data){
                    return $data->project->name;
                })
                ->addColumn('client',function ($data){
                    if ($data->client_id){
                        return "<a target='_blank' href=\"" . route('system.client.show', $data->client->id) . "\">".$data->client->name."</a>";
                    }
                    return '--';
                })
                ->addColumn('order_date',function ($data){
                    if ($data->order_date){
                        //  return $data->order_date->format('l j F Y');
                        return $data->order_date;
                    }
                    return '--';
                })
                ->addColumn('call_details',function ($data){
                    return str_limit($data->call_details,25);
                })
                ->addColumn('staff_name', '<a href="{{route(\'system.staff.show\',$staff_id)}}" target="_blank">{{$staff_name}}</a>')
                ->addColumn('added_to_staff',function ($data){
                    if ($data->complain_of_staff_id){
                        return "<a target='_blank' href=\"" . route('system.staff.show', $data->complainOfStaff->id) . "\">".$data->complainOfStaff->Fullname."</a>";
                    }
                    return '--';
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at->diffForHumans();
                })

                ->addColumn('action', function ($data) {
                    return " <div class=\"dropdown\">
                              <button class=\"btn btn-primary dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><i class=\"ft-cog icon-left\"></i>
                              <span class=\"caret\"></span></button>
                              <ul class=\"dropdown-menu\">
                                <li class=\"dropdown-item\"><a href=\"" . route('system.complain.show', $data->id) . "\">" . __('View') . "</a></li>
                                <li class=\"dropdown-item\"><a href=\"" . route('system.complain.edit', $data->id) . "\">" . __('Edit') . "</a></li>
                                <li class=\"dropdown-item\"><a onclick=\"deleteRecord('" . route('system.complain.destroy', $data->id) . "')\" href=\"javascript:void(0)\">" . __('Delete') . "</a></li>
                              </ul>
                            </div>";
                })
                ->make(true);
        } else {
            // View Data
            $this->viewData['tableColumns'] = [__('ID'), __('Project'),__('Client'),__('Order Date'), __('Call Details'),  __('Created By'), __('Complain Of Staff'),__('Created At'), __('Action')];
            $this->viewData['breadcrumb'][] = [
                'text' => __('Complains')
            ];

            if ($request->withTrashed) {
                $this->viewData['pageTitle'] = __('Deleted Complains');
            } else {
                $this->viewData['pageTitle'] = __('Complains');
            }
            $return  = [];
            $projects  = Project::get(['id','name']);
            foreach ($projects as $key=>$value){
                $return[$value->id] = $value->name;
            }
            $this->viewData['projects'] = $return;
            return $this->view('complains.index', $this->viewData);
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
            'text' => __('Complain'),
            'url' => route('system.complain.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Create Complain'),
        ];
        $return  = [];
        $projects  = Project::get(['id','name']);
        foreach ($projects as $key=>$value){
            $return[$value->id] = $value->name;
        }
        $this->viewData['projects'] = $return;
        $this->viewData['pageTitle'] = __('Create Complain');
        return $this->view('complains.create', $this->viewData);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request,[
            'project_id'               =>'required|exists:projects,id',
            'complain_client_id'                =>'nullable|exists:clients,id',
            'call_details'                  =>'required|min:5',
            'order_date'               =>'required|date'
        ]);
        $theRequest = $request->only([
            'project_id',
            'complain_of_staff_id',
            'call_details',
            'order_date',
            'complain_client_id',
        ]);

        $theRequest['staff_id'] = Auth::id();
        $marketingMessage = Complain::create($theRequest);
        if ($marketingMessage)
            return redirect()
                ->route('system.complain.create')
                ->with('status', 'success')
                ->with('msg', __('Data has been added successfully'));
        else {
            return redirect()
                ->route('system.complain.create')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t add Complain'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */

    public function show(Complain $complain)
    {
        // dd($complain);
        $this->viewData['breadcrumb'] = [
            [
                'text' => __('Home'),
                'url' => url('system'),
            ],
            [
                'text' => __('Vacation Type'),
                'url' => route('system.complain.index'),
            ],
            [
                'text' => 'Show',
            ]
        ];
//
//
        $this->viewData['pageTitle'] = 'Complain';
        $this->viewData['result'] = $complain;
        return $this->view('complains.show', $this->viewData);
    }

    public function edit(Complain $complain)
    {
        $this->viewData['breadcrumb'][] = [
            'text' => __('Complain'),
            'url' => route('system.complain.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Edit Complain'),
        ];

        $return  = [];
        $projects  = Project::get(['id','name']);
        foreach ($projects as $key=>$value){
            $return[$value->id] = $value->name;
        }
        $this->viewData['projects'] = $return;
        $this->viewData['pageTitle'] = __('Edit Complain');
        $this->viewData['result'] = $complain;

        return $this->view('complains.create', $this->viewData);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Complain $complain)
    {
        $this->validate($request,[
            'project_id'               =>'required|exists:projects,id',
            'complain_of_staff_id'     =>'nullable|exists:staff,id',
            'call_details'             =>'required|min:5',
        ]);
        //call_details
        $theRequest = [];
        $theRequest = $request->only([
            'project_id',
            'complain_of_staff_id',
            'call_details',
        ]);
        if ($request->has('complain_of_staff_id')){
            $theRequest['complain_of_staff_id'] = $request->complain_of_staff_id;
        }else{
            unset($theRequest['complain_of_staff_id']);
        }
        if ($complain->update($theRequest)) {
            return redirect()
                ->route('system.complain.edit', $complain->id)
                ->with('status', 'success')
                ->with('msg', __('Successfully Edit Complain'));
        }
        else {
            return redirect()
                ->route('system.complain.edit')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t Edit Complain'));
        }
    }

    /**
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Complain $complain)
    {
        $complain->delete();
        if ($request->ajax()) {
            return ['status' => true, 'msg' => __('Complain has been deleted successfully')];
        } else {
            redirect()
                ->route('system.complain.index')
                ->with('status', 'success')
                ->with('msg', __('This Complain  has been deleted'));
        }
    }
}
