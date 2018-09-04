@extends('system.layouts')
@section('header')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/forms/selects/select2.min.css')}}">
@endsection
@section('content')
    <div class="app-content content container-fluid">
                <div class="content-wrapper">
                    <div class="content-header row">
                        <div class="content-header-left col-md-4 col-xs-12">
                            <h4>
                                {{$pageTitle}}
                            </h4>
                        </div>
                        <div class="content-header-right col-md-8 col-xs-12">
                            <div class=" content-header-title mb-0" style="float: right;">
                                @include('system.breadcrumb')
                            </div>
                        </div>
                    </div>
            <div class="content-body">
                <!-- Server-side processing -->
                <section id="server-processing">
                    <div class="row">
                            @if($errors->any())
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="alert alert-danger">
                                            {{__('Some fields are invalid please fix them')}}
                                            <ul>
                                                @foreach($errors->all() as $key => $value)
                                                    <li>{{$key}}: {{$value}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @elseif(Session::has('status'))
                                <div class="col-sm-12">
                                    <div class="card">
                                        <div class="alert alert-{{Session::get('status')}}">
                                            {{ Session::get('msg') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            {!! Form::open(['route' => isset($result->id) ? ['system.vacation.update',$result->id]:'system.vacation.store','method' => isset($result->id) ?  'PATCH' : 'POST']) !!}

                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h2>{{__('Vacation Data')}}</h2>
                                    </div>
                                    <div class="card-block card-dashboard">

                                        <div class="form-group col-sm-6{!! formError($errors,'vacation_start',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('vacation_start', __('Vacation Start At')) !!}
                                                {!! Form::text('vacation_start',isset($result->id) ? $result->vacation_start:old('vacation_start'),['class'=>'form-control datepicker']) !!}
                                            </div>
                                            {!! formError($errors,'vacation_start') !!}
                                        </div>

                                        <div class="form-group col-sm-6{!! formError($errors,'vacation_end',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('vacation_end', __('Vacation End At')) !!}
                                                {!! Form::text('vacation_end',isset($result->id) ? $result->vacation_end:old('vacation_end'),['class'=>'form-control datepicker']) !!}
                                            </div>
                                            {!! formError($errors,'vacation_end') !!}
                                        </div>

                                        <div class="form-group col-sm-6{!! formError($errors,'vacation_type_id',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('vacation_type_id', __('Vacation Type')) !!}
                                                {!! Form::select('vacation_type_id',['0'=>'Select Vacation Type']+$vacation_types,isset($result->id) ? $result->vacation_type_id:old('vacation_type_id'),['class'=>'form-control vacation_type_id']) !!}
                                            </div>
                                            {!! formError($errors,'vacation_type_id') !!}
                                        </div>
                                        <div class="form-group col-sm-6{!! formError($errors,'added_to',true) !!}">
                                            <div class="controls">
                                                    {!! Form::label('type', __('Select Staff To Add Vacation').':') !!}
                                                {!! Form::select('added_to',isset($result->id) ? [$result->added_to =>$result->addedTo->Fullname]:[''=>__('Select Driver')],isset($result->id) ? $result->added_to:old('added_to'),['style'=>'width: 100%;' ,'id'=>'staffSelect2','class'=>'form-control col-md-12']) !!}

                                            </div>
                                            {!! formError($errors,'added_to') !!}
                                        </div>

                                        <div class="form-group col-sm-12{!! formError($errors,'type',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('type', __('Type').':') !!}
                                                {!! Form::select('type',[''=>__('Select Status'),'paid'=>__('Paid'),'un-paid'=>__('un-paid')],isset($result->id) ? $result->type:old('type'),['class'=>'form-control']) !!}
                                            </div>
                                            {!! formError($errors,'type') !!}
                                        </div>


                                        @if(isset($result->id))
                                            <div class="form-group col-sm-12{!! formError($errors,'decision',true) !!}">
                                                <div class="controls">
                                                    {!! Form::label('decision', __('Decision').':') !!}
                                                    {!! Form::select('decision',[''=>__('Select decision'),'approved'=>__('Approved'),'rejected'=>__('Rejected')],isset($result->id) ? $result->decision:old('decision'),['class'=>'form-control']) !!}
                                                </div>
                                                {!! formError($errors,'decision') !!}
                                            </div>
                                        @endif

                                        <div class="form-group col-sm-12{!! formError($errors,'notes',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('notes', __('Notes')) !!}
                                                {!! Form::textarea('notes',isset($result->id) ? $result->notes:old('notes'),['class'=>'form-control']) !!}
                                            </div>
                                            {!! formError($errors,'notes') !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12" style="padding-top: 20px;">
                                <div class="card-header">
                                    <div class="card-body">
                                        <div class="card-block card-dashboard">
                                            {!! Form::submit(__('Save'),['class'=>'btn btn-success pull-right']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </section>
                <!--/ Javascript sourced data -->
            </div>
        </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->
@endsection
@section('header')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/pickers/datetime/bootstrap-datetimepicker.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/pickers/pickadate/pickadate.css')}}">
@endsection

@section('footer')
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/extensions/pace.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/pickers/pickadate/pickadate.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/forms/selects/select2.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/pickers/daterange/daterangepicker.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/system/vendors/css/pickers/datetime/bootstrap-datetimepicker.css')}}">
    <script src="{{asset('assets/system')}}/vendors/js/forms/select/select2.full.min.js" type="text/javascript"></script>

    <!-- BEGIN PAGE VENDOR JS-->
    <script src="{{asset('assets/system/vendors/js/pickers/dateTime/moment-with-locales.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/system/vendors/js/pickers/dateTime/bootstrap-datetimepicker.min.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/system/vendors/js/pickers/pickadate/picker.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/system/vendors/js/pickers/pickadate/picker.date.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/system/vendors/js/pickers/pickadate/picker.time.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/system/vendors/js/pickers/pickadate/legacy.js')}}" type="text/javascript"></script>
    <script src="{{asset('assets/system/vendors/js/pickers/daterange/daterangepicker.js')}}" type="text/javascript"></script>

<script>
    $(document).ready(function() {
        $('.vacation_type_id').select2();
    });
    $(function(){
        $('.datepicker').datetimepicker({
            viewMode: 'months',
            format: 'YYYY-MM-DD'
        });
    });
    ajaxSelect2('#staffSelect2','staff','',"{{route('system.ajax.get')}}");
        {{--<script type="text/javascript">--}}

            {{--$(document).ready(function(){--}}
                {{--list_type_function();--}}
            {{--});--}}

            {{--function list_type_function(){--}}
                {{--$value = $('#list_type').val();--}}
                {{--if($value == 'static'){--}}
                    {{--$('#dynamic-point-div').hide();--}}
                    {{--$('#static-point-div').show();--}}
                {{--}else{--}}
                    {{--$('#static-point-div').hide();--}}
                    {{--$('#dynamic-point-div').show();--}}
                {{--}--}}
            {{--}--}}

        </script>
@endsection