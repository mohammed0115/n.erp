@extends('layout.main') @section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4>{{trans('file.edit')}} {{trans('file.organization')}}</h4>
                    </div>
                    <div class="card-body">
                        <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        {!! Form::open(['route' => ['organization.update', $organization->id], 'method' => 'put']) !!}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{trans('file.organization_name')}} *</label>
                                    <input type="text" name="name" value="{{$organization->name}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{trans('file.organization_phone')}} *</label>
                                    <input type="text" name="phone" value="{{$organization->phone}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{trans('file.organization_location')}} *</label>
                                    <input type="text" name="location" value="{{$organization->location}}" required class="form-control">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mt-3">
                                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                                    <a href="{{route('organization.index')}}" class="btn btn-secondary">{{trans('file.Back')}}</a>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $("ul#organization").siblings('a').attr('aria-expanded','true');
    $("ul#organization").addClass("show");
    $("ul#organization #organization-menu").addClass("active");
</script>
@endsection

