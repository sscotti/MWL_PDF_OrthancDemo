<?php
use App\Models\Referrers\ReferringPhysician;
use Illuminate\Support\Facades\Log;
$doctor = ReferringPhysician::where('identifier', $doctor_id)->first();
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Practice Profile') }}
        </h2>
    </x-slot>
    @include('referrers.referrers_demographics')
    <div id = "licensewrapper">
    @include('referrers.referrers_licenses')
    </div>
    <div class="form-group shadedform row">
        <div class="col-form-label-sm col-sm-2" id="add_license" style="cursor:pointer" data-id= "<?php echo $doctor->id ?>" data-identifier = "<?php echo $doctor_id ?>">Add License: <i class="fas fa-plus"></i></div>
    </div>

    <x-myjs />
    <script nonce= "{{ csp_nonce() }}">
    
    attachSumoSelect("#demographics");
    attachSumoSelect("#licenselist");
    
    function licenseAction(action, data) {
    
        let identifier = data.identifier;
        
        $.ajax({
            type: "POST",
            url: "/provider_licenses/" + action,
            dataType: "json",
            data: data
        })
        .done(function(data, textStatus, jqXHR) {
            getLicenseList(identifier);
        });
    }
    
    function getLicenseList(identifier) {
	    $.ajax({
            type: "POST",
            url: "/provider_licenses/listlicenses",
            dataType: "html",
            data: {identifier:identifier}
        })
        .done(function(data, textStatus, jqXHR) {
            $("#licenselist").replaceWith(data);
            attachSumoSelect("#licenselist");
        });
    }
    
    $('#add_license').on('click', function(e) {
            data = {"identifier":$(this).data("identifier"),"license_provider_id":$(this).data("id")};
            licenseAction('addlicense', data);
    });
    $('#licensewrapper').on('click', '.deletelicense', function(e) {
            data = {"license_id" : $(this).closest("form").find("[name='license_id']").val(),"identifier":$(this).closest("form").find("[name='license_provider_identifier']").val(),"id":$(this).closest("form").find("[name='license_provider_id']").val()};
            licenseAction('deletelicense', data);
    });
    
    </script>
</x-app-layout>