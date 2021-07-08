<?php
use App\Helpers\Widgets;
use App\Actions\Orthanc\OrthancAPI;
$pacs = new OrthancAPI();
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Provider Shared Study List') }}
        </h2>
    </x-slot>
<!-- For the Viewer and upload pic overlay -->

<div id="myNav" class="vieweroverlay"><a href="" class="closebtn"><i class="fas fa-window-close"></i></a><div id="dynamiciframe"></div></div>
<div id = "teleRadDivOverlayWrapper">
<button id="teleradClose" type="button" class="btn-primary btn-xs">Close Report <span class = "spanclosex">x</span></button>
<div id="APImonitor">
<div id="viewertools">
<button data-filename = "RadiologyReport.pdf" data-css = "report" data-content = "#reportnoheader" type="button" class="btn-primary btn-sm wkdownload" value="Download">Download</button>
<button data-filename = "RadiologyReport.pdf" data-css = "report" data-content = "#reportnoheader" type="button" class="btn-primary btn-sm wknewtab" value="Print">Print</button>

<form id="emailreport" style="display:inline-block;">
<label for="emailreport_to">E-mail to:  </label>
<input id="emailreport_to" name="emailreport_to" type="email" class = "jqvalidmyemail" value = "<?php echo !empty(Auth::user()->email)?Auth::user()->email:''?>">
<input type="submit" class="btn-danger btn-sm" value="Send Report">
</form>

</div>
<div id="apiresults"></div>
</div>
<div id="teleRadDivOverlay"></div>
</div>

<div style = "margin:auto;text-align:center;width:max-content;font-size:12px;">
<?php  echo $pacs->serverStatusWidget(); ?>
<?php  echo Widgets::PACSSelectorTool("referrers_studies"); ?>
</div>

<div id="delegator">


    <h5 style = "text-align: center;">Shared Studies</h5>
    <div class = "listwrapper">
    <div class="container mt-5">
        <table class="table table-bordered yajra-datatable" id = "sharedstudies">
            <thead>
                <tr>
                    <th>patient_name</th>
                    <th>study_description</th>
                    <th>study_date</th>
                    <th>share_note</th>
                    <th>Link</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    </div>
<!-- end of studies -->
</div>
<!-- end of delegator / wrapper -->


<x-myjs />
<script nonce= "{{ csp_nonce() }}">

  $(function () {
    
    var table = $('#sharedstudies').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 10,
        order: [[ 2, "desc" ]], // sort by request date descending
        ajax: {
            url: "{{ route('/sharedstudies_datatable') }}",
            type: 'POST'
        },
        
        columns: [
            {data: 'patient_name', name: 'patient_name'},
            {data: 'study_description', name: 'study_description'},
            {data: 'study_date', name: 'study_date'},
            {data: 'share_note', name: 'share_note'},
            {data: 'action'},
        ],
        "lengthMenu": [ 2,5,10, 25, 50, 75, 100 ]
    });
});
</script>
</x-app-layout>
