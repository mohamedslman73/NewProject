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
                            {!! Form::open(['route' => isset($result->id) ? ['system.department.update',$result->id]:'system.department.store','method' => isset($result->id) ?  'PATCH' : 'POST']) !!}

                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h2>{{__('department')}}</h2>
                                    </div>
                                    <div class="card-block card-dashboard">

                                        <div class="form-group col-sm-12{!! formError($errors,'name',true) !!}">
                                            <div class="controls">
                                                {!! Form::label('name', __('Name')) !!}
                                                {!! Form::text('name',isset($result->id) ? $result->name:old('name'),['class'=>'form-control']) !!}
                                            </div>
                                            {!! formError($errors,'name') !!}
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
