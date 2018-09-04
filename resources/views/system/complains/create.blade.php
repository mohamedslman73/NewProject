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
                            {!! Form::open(['route' => isset($result->id) ? ['system.complain.update',$result->id]:'system.complain.store','method' => isset($result->id) ?  'PATCH' : 'POST']) !!}

                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h2>{{__('Complain Data')}}</h2>
                                    </div>
                                    <div class="card-block card-dashboard">

                                        <div class="form-group col-sm-6{!! formError($errors,'project_id',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('project_id', __('Select Project')) !!}
                                                {!! Form::select('project_id',['0'=>'Select Project']+$projects,isset($result->id) ? $result->project_id:old('project_id'),['class'=>'form-control project_id']) !!}
                                            </div>
                                            {!! formError($errors,'project_id') !!}
                                        </div>

                                        <div class="form-group col-md-6{!! formError($errors,'complain_of_staff_id',true) !!}">
                                            <div class="controls">
                                                {{ Form::label('staffSelect2',__('Complain of Staff (optional):')) }}
                                                {!! Form::select('complain_of_staff_id',[''=>__('Select Staff')],null,['style'=>'width: 100%;' ,'id'=>'staffSelect2','class'=>'form-control col-md-12']) !!}
                                            </div>
                                            {!! formError($errors,'complain_of_staff_id') !!}
                                        </div>

                                        <div class="form-group col-md-12{!! formError($errors,'complain_client_id',true) !!}">
                                            <div class="controls">
                                                {{ Form::label('complain_client_id',__('Client:')) }}
                                                {!! Form::select('complain_client_id',[''=>__('Select Client')],null,['style'=>'width: 100%;' ,'id'=>'client_id','class'=>'form-control col-md-12']) !!}
                                            </div>
                                            {!! formError($errors,'complain_client_id') !!}
                                        </div>

                                        <div class="form-group col-md-12{!! formError($errors,'order_date',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('order_date', __('Date').':') !!}
                                                {!! Form::text('order_date',isset($result->id) ? $result->order_date:old('order_date'),['class'=>'form-control datepicker']) !!}
                                            </div>
                                            {!! formError($errors,'order_date') !!}
                                        </div>

                                        <div class="form-group col-sm-12{!! formError($errors,'call_details',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('call_details', __('Description')) !!}
                                                {!! Form::textarea('call_details',isset($result->id) ? $result->comment:old('call_details'),['class'=>'form-control']) !!}
                                            </div>
                                            {!! formError($errors,'call_details') !!}
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


    <script type="text/javascript">
        ajaxSelect2('#staffSelect2','staff','',"{{route('system.ajax.get')}}");
        ajaxSelect2('#client_id','client','',"{{route('system.ajax.get')}}");
        $(document).ready(function() {
            $('.project_id').select2();
        });

        $(function(){
            $('.datepicker').datetimepicker({
                viewMode: 'months',
                format: 'YYYY-MM-DD'
            });
        });
    </script>
@endsection