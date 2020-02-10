@extends('components.search')
@push('search.scripts.end)
    $("#searchstatus").on("change", function(){
        var value = $(this).val();
        window.LaravelDataTables["{{ $options['dataTable'] }}"].column({{ $options['searchStatus']['index'] ?? 0 }}).search(value).draw();
    })
@endpush
@push('search.html.start')
    <div class="{{ $options["searchStatus"]["class"] ?? 'col-md-8' }}">            
        <div class="form-group row px-md-2">
            <label class="col-form-label col-6 col-sm-auto">@lang('titles.status')</label>
            <div class="col-md-4 col-lg-3 col-12">
                <select class="form-control" name="searchstatus" id="searchstatus">
                    @foreach($options["searchStatus"]["options"] as $key=>$options)
                    <option value="{{ $key }}">{{ $options }}</option>
                    @endforeach
                </select>
            </div>
        </div>        
    </div>   
@endpush
