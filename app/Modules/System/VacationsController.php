<?php

namespace App\Modules\System;
use App\Models\Staff;
use App\Models\Vacation;
use App\Models\VacationTypes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Facades\Datatables;

class VacationsController extends SystemController
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
            $eloquentData = Vacation::select([
                'vacations.id',
                'vacations.type',
                'vacations.num_of_days',
                'vacations.staff_id',
                'vacations.added_to',
                'vacations.created_at',
                \DB::Raw("CONCAT(staff.firstname,' ',staff.lastname) as staff_name"),
                ])
                ->join('staff', 'staff.id', '=', 'vacations.staff_id');

            if ($request->withTrashed) {
                $eloquentData->onlyTrashed();
            }

            whereBetween($eloquentData, 'DATE(vacations.created_at)', $request->created_at1, $request->created_at2);

            if ($request->id) {
                $eloquentData->where('vacations.id', '=', $request->id);
            }
            if ($request->staff_id) {
                $eloquentData->where('vacations.staff_id', '=', $request->staff_id);
            }
            if ($request->added_to) {
                $eloquentData->where('vacations.added_to', '=', $request->added_to);
            }
            if ($request->type) {
                $eloquentData->where('vacations.type', 'LIKE', '%' . $request->type . '%');
            }
            if ($request->num_of_days) {
                $eloquentData->where('vacations.num_of_days', '=', $request->num_of_days);
            }

            return Datatables::eloquent($eloquentData)
                ->addColumn('id', '{{$id}}')
                ->addColumn('type', '{{$type}}')
                ->addColumn('num_of_days','{{$num_of_days}}')
                ->addColumn('staff_name', '<a href="{{route(\'system.staff.show\',$staff_id)}}" target="_blank">{{$staff_name}}</a>')
                ->addColumn('added_to',function ($data){
                  return "<a target='_blank' href=\"" . route('system.staff.show', $data->addedTo->id) . "\">".$data->addedTo->Fullname."</a>";
                })
                ->addColumn('created_at', function ($data) {
                    return $data->created_at->diffForHumans();
                })
                ->addColumn('action', function ($data) {
                    return " <div class=\"dropdown\">
                              <button class=\"btn btn-primary dropdown-toggle\" type=\"button\" data-toggle=\"dropdown\"><i class=\"ft-cog icon-left\"></i>
                              <span class=\"caret\"></span></button>
                              <ul class=\"dropdown-menu\">
                                <li class=\"dropdown-item\"><a href=\"" . route('system.vacation.show', $data->id) . "\">" . __('View') . "</a></li>
                                <li class=\"dropdown-item\"><a href=\"" . route('system.vacation.edit', $data->id) . "\">" . __('Edit') . "</a></li>
                                <li class=\"dropdown-item\"><a onclick=\"deleteRecord('" . route('system.vacation.destroy', $data->id) . "')\" href=\"javascript:void(0)\">" . __('Delete') . "</a></li>
                              </ul>
                            </div>";
                })
                ->make(true);
        } else {
            // View Data
            $this->viewData['tableColumns'] = [__('ID'), __('Type'), __('Vacation Num Of Days'),  __('Created By'),__('added To'), __('Created At'), __('Action')];
            $this->viewData['breadcrumb'][] = [
                'text' => __('Vacations')
            ];

            if ($request->withTrashed) {
                $this->viewData['pageTitle'] = __('Deleted Vacation');
            } else {
                $this->viewData['pageTitle'] = __('Vacations');
            }
            $return = [];
            $data = VacationTypes::get(['id', 'name']);
            foreach ($data as $key => $value) {
                $return[$value->id] = $value->name;
            }
            $this->viewData['vacation_types'] = $return;


            return $this->view('vacation.index', $this->viewData);
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
            'text' => __('Vacation'),
            'url' => route('system.vacation.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Create Vacation'),
        ];

        $return = [];
        $data = VacationTypes::get(['id', 'name']);
        foreach ($data as $key => $value) {
            $return[$value->id] = $value->name;
        }
        $this->viewData['vacation_types'] = $return;

        $this->viewData['pageTitle'] = __('Create Vacation ');
        return $this->view('vacation.create', $this->viewData);
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
            'type'=> 'required|in:paid,un-paid',
          //  'num_of_days'=> 'required|numeric',
            'added_to'=> 'required|exists:staff,id',
            'vacation_type_id'=> 'required|exists:vacation_types,id',
            'vacation_start'=> 'required|date_format:"Y-m-d"',
            'vacation_end'=> 'required|after_or_equal:"'.$request->vacation_start.'"',
        ]);

        $theRequest = $request->only([
            'type',
            'num_of_days',
            'added_to',
            'vacation_type_id',
            'vacation_start',
            'vacation_end',
            'notes',
        ]);

        $theRequest['staff_id'] = Auth::id();

       $staff = Staff::where('id',$request->added_to)->first();
       $vacationTypedate = VacationTypes::where('id',$request->vacation_type_id)->first()->after_months;

        $joining = Carbon::createFromFormat('Y-m-d',$staff->joining_date);
        $now = Carbon::createFromFormat('Y-m-d',date('Y-m-d'));

         $diffBetweenJoingingAndVacationTypeDate = $now->diffInDays($joining);

         if ($diffBetweenJoingingAndVacationTypeDate < $vacationTypedate*30){
             return redirect()
                 ->route('system.vacation.create')
                 ->with('status', 'danger')
                 ->with('msg', __('Sorry Couldn\'t add Vacation Before ' .($vacationTypedate*30). ' Day of Joining Date and '.$staff->Fullname . ' Has Join from ' .$diffBetweenJoingingAndVacationTypeDate. ' Days'));
         }

        $from = Carbon::createFromFormat('Y-m-d',$request->vacation_start);
        $to   =  Carbon::createFromFormat('Y-m-d', $request->vacation_end);
        $theRequest['num_of_days'] = $to->diffInDays($from);
        $vacation = Vacation::create($theRequest);
        if ($vacation)
            return redirect()
                ->route('system.vacation.create')
                ->with('status', 'success')
                ->with('msg', __('Data has been added successfully'));
        else {
            return redirect()
                ->route('system.vacation.create')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t add Vacation'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */

    public function show(Vacation $vacation)
    {
        //dd($supplier_category);
        $this->viewData['breadcrumb'] = [
            [
                'text' => __('Home'),
                'url' => url('system'),
            ],
            [
                'text' => __('Vacations'),
                'url' => route('system.type.index'),
            ],
            [
                'text' => 'Show',
            ]
        ];
//
//
        $this->viewData['pageTitle'] = 'Vacations';
        $this->viewData['result'] = $vacation;
        return $this->view('vacation.show', $this->viewData);
    }

    public function edit(Vacation $vacation)
    {
        $this->viewData['breadcrumb'][] = [
            'text' => __('Vacation'),
            'url' => route('system.vacation.index')
        ];

        $this->viewData['breadcrumb'][] = [
            'text' => __('Edit Vacation'),
        ];

        $return = [];
        $data = VacationTypes::get(['id', 'name']);
        foreach ($data as $key => $value) {
            $return[$value->id] = $value->name;
        }
        $this->viewData['vacation_types'] = $return;
        $this->viewData['pageTitle'] = __('Edit Vacation');
        $this->viewData['result'] = $vacation;

        return $this->view('vacation.create', $this->viewData);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Vacation $vacation)
    {
        $this->validate($request,[
            'type'=> 'required|in:paid,un-paid',
            //  'num_of_days'=> 'required|numeric',
            'added_to'=> 'required|exists:staff,id',
            'vacation_type_id'=> 'required|exists:vacation_types,id',
            'vacation_start'=> 'required|date_format:"Y-m-d"',
            'vacation_end'=> 'required|after_or_equal:"'.$request->vacation_start.'"',
        ]);
        $theRequest = $request->only([
            'type',
            'num_of_days',
            'added_to',
            'vacation_type_id',
            'decision',
            'notes'
        ]);
        if ($request->has('added_to')){
            $theRequest['added_to'] = $request->added_to;
        }else{
            unset($theRequest['added_to']);
        }


        $staff = Staff::where('id',$request->added_to)->first();
        $vacationTypedate = VacationTypes::where('id',$request->vacation_type_id)->first()->after_months;

        $joining = Carbon::createFromFormat('Y-m-d',$staff->joining_date);
        $now = Carbon::createFromFormat('Y-m-d',date('Y-m-d'));

        $diffBetweenJoingingAndVacationTypeDate = $now->diffInDays($joining);

        if ($diffBetweenJoingingAndVacationTypeDate < $vacationTypedate*30){
            return redirect()
                ->route('system.vacation.create')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t Edit Vacation Before ' .($vacationTypedate*30). ' Day of Joining Date and '.$staff->Fullname . ' Has Join from ' .$diffBetweenJoingingAndVacationTypeDate. ' Days'));
        }


        $from = Carbon::createFromFormat('Y-m-d',$request->vacation_start);
        $to   =  Carbon::createFromFormat('Y-m-d', $request->vacation_end);
        $theRequest['num_of_days'] = $to->diffInDays($from);

        if ($vacation->update($theRequest)) {
          //  dd($category);
            return redirect()
                ->route('system.vacation.edit', $vacation->id)
                ->with('status', 'success')
                ->with('msg', __('Successfully Edit Vacation'));
        }
        else {
            return redirect()
                ->route('system.vacation.edit')
                ->with('status', 'danger')
                ->with('msg', __('Sorry Couldn\'t Edit Vacation'));
        }
    }

    /**
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Vacation $vacation)
    {
        $vacation->delete();
        if ($request->ajax()) {
            return ['status' => true, 'msg' => __('Item Category has been deleted successfully')];
        } else {
            redirect()
                ->route('system.vacation.index')
                ->with('status', 'success')
                ->with('msg', __('This vacation has been deleted'));
        }
    }
}
