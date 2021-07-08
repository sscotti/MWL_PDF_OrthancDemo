<?php
use App\MyModels\Widgets;
use App\Actions\Orthanc\OrthancAPI;
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('DEV TOOL') }}
        </h2>
    </x-slot>
    <!-- This is supplemental component for JS for the RIS migration pages -->
    <x-myjs />
    <!-- This is the main entry point for the dev tool, multiple includes for various component-->
    <div class = "ajaxdata"></div>
    <div class="container admins" >
        <h5>Admininistration & Development Tool</h5>
        <ul class="centertabs">
                <li>
                <a href="#devtools">Development Tools</a>
                 </li>
                <li>
                <a href="#ris">RIS Configuration</a>
                 </li>
                 <li>
                <a href="#pacs">PACS / ORTHANC Configuration</a>
                 </li>
                 <li>
                <a href="#sns">Notification Service</a>
                 </li>
                 <li>
                <a href="#reporting_tools">Reporting Tools</a>
                 </li>
        </ul>

        <div id="devtools">
            <x-devtoolmain/>
        </div>
        <div id="ris">
<pre>
<?php // sample of dcmdump echo system(Config::get('PATH_DCMTK') . "dcmdump /dicomFiles/source.dcm", $result_code);
?>
</pre>
<pre class = "syntaxHighlighted" id = "syntaxHighlightedRIS"></pre>
<pre class = "syntaxHighlighted" id = "syntaxHighlightedLaravel"></pre>
</div>
        <div id="pacs" class = "syntaxHighlighted"><pre id = "syntaxHighlightedPACS"></pre></div>
        <div id="sns">

<form id = "snsform">
  <div class="form-row">
    <div class="col">
      <input name = "message" type="text" class="form-control" placeholder="Message" value="This is a Test.">
    </div>
    <div class="col">
      <input name = "phone" type="text" class="form-control" placeholder="Phone" value="+16513130209">
    </div>
  </div>
    <div class="form-group row">
    <div class="col-sm-10">
      <button type="submit" class="btn btn-danger">Submit</button>
    </div>
  </div>
</form>

</div>
        <div id="reporting_tools"></div>
    </div>
    
<script>
$(".admins").tabs();

function syntaxHighlight(json) {

    if (typeof json != 'string') {
         json = JSON.stringify(json, undefined, 2);
    }
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                cls = 'key';
            } else {
                cls = 'string';
            }
        } else if (/true|false/.test(match)) {
            cls = 'boolean';
        } else if (/null/.test(match)) {
            cls = 'null';
        }
        return '<span class="' + cls + '">' + match + '</span>';
    });
}

$('#snsform').on('submit', function(e) {

	e.preventDefault();

    $.ajax({
       type: "POST",
       url: "/AWS/sendMessage",
       dataType: "json",
       data: $(this).serialize(),
       beforeSend: function(e) {
           $("#spinner").css("display", "block");
       }
   })
   .done(function(data, textStatus, jqXHR) {

       alert(data.status);

   });
});
$("#syntaxHighlightedPACS").html(syntaxHighlight(<?php echo (new OrthancAPI())->getOrthancConfigs("ALL"); ?>));
$("#syntaxHighlightedLaravel").html(syntaxHighlight(<?php echo json_encode( Config::all()); ?>));
$("#syntaxHighlightedRIS").html(syntaxHighlight(<?php echo json_encode( Config::get("myconfigs")); ?>));
</script>
</x-app-layout>
