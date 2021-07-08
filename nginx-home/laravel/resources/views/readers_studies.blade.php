<?php
use App\Helpers\Widgets;
use App\Actions\Orthanc\OrthancAPI;
$pacs = new OrthancAPI();
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Readers') }}
        </h2>
    </x-slot>
<!-- For the Viewer and upload pic overlay -->

<div id="myNav" class="vieweroverlay"><a href="" class="closebtn"><i class="fas fa-window-close"></i></a><div id="dynamiciframe"></div></div>
<div id = "teleRadDivOverlayWrapper">
<button type="button" class="btn btn-primary btn-xs closeresults">Clear Viewer <span class = "spanclosex">x</span></button>
<button id="teleradClose" type="button" class="btn-primary btn-xs">Report Viewer - Editor <span class = "spanclosex">x</span></button>
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

<div class="col-sm-12 reportdiv">
	<div id="toolbar" style="width:100%;background:#000;color:black;display:inline-block;">
	<div id="reporttemplatelist" style="color:black;float:left;display:inline-block;"><form name="templatechooser" id="templatechooser" class="reportapi"><select form="templatechooser" name="templateid" id="templateid" style="display:inline;"></select></form></div>
	<span id="tools" data-pacs = "ORTHANC" style="float:left;"><input id = "undoreport" type="submit" class="btn-danger btn-sm" value="Undo Preview" disabled="disabled" /><input id="previewreport" type="submit" class="btn-danger btn-sm" value="Preview" disabled="disabled"><input id = "prelimreport" type="submit" class="btn-danger btn-sm" value="PRELIM" disabled="disabled" /><input id = "finalreport" type="submit" class="btn-danger btn-sm" value="FINAL" disabled="disabled" /><input id = "addendumreport" type="submit" class="btn-danger btn-sm" value="ADDENDUM" disabled="disabled" /></span>
	</div>
	<div id = "lockedwarning" class="row"></div>
<div class = "row">
<div class="reportheader col-sm-6">
<div><span>Name:</span><span class="headername"></span></div>
<div><span>Age:</span><span class="headerage"></span></div>
<div><span>DOB:</span><span class="headerdob"></span></div>
<div><span>Sex:</span><span class="headersex"></span></div>
<div><span>MRN:</span><span class="headermrn"></span></div>
<div><span>Accession:</span><span class="headeracc"></span></div>
<div><span>Description:</span><span class="headerdesc"></span></div>
<div><span>Modality:</span><span class="headermod"></span></div>
<div><span>Exam Date:</span><span class="headerexamdate"></span></div>
<div><span>Referring Provider:</span><span class="headerprovider"></span></div>
<div><span>Indication:</span><span class="headerindication"></span></div>
</div>
<div class="reportheader col-sm-6">
<ol>
<li>Choose a report template, or load an old report into the editor.</li>
<li>If a template, fill out the fields and selects, and then click "Preview" to review before submitting.</li>
<li>If from a prior, you can load the old report, and then edit the old report inline using the mouse and keyboard.</li>
<li>From "Preview", click on "PRELIM", "FINAL", or "ADDENDUM" to submit.  Only allowed options will be blue.</li>
<li>If there are old reports you can view those using the select list at the upper left.  (Right Click) to view the current selection.</li>
</ol>
</div>
</div>
<form name="markupform" id="markupform"></form>

</div>
</div>

<div style = "margin:auto;text-align:center;width:max-content;font-size:12px;">
<?php  echo $pacs->serverStatusWidget(); ?>
<?php  echo Widgets::PACSSelectorTool("readers_studies"); ?>
</div>

<div id="delegator" class = "myuitabs">

<ul class="centertabs">
	<li><a href="#studylist">STUDY LIST</a></li>
	<li><a href="#toolstab">TOOLS</a></li>
	<li><a href="#contactinfo">CONTACT SUPPORT</a></li>
</ul>

<div id="studylist">

    <?php echo Widgets::studiesSearchForm() ?>
    <?php echo Widgets::dateRadioSelectorstudies("searchform", "#studieswrapper", "searchorthanc") ?>
    <?php echo Widgets::serverStatus() ?>
    <?php echo Widgets::studyRowSelector() ?>
    <?php echo Widgets::studiesLengend() ?>
    <?php echo Widgets::studiesContainer() ?>


</div>
<!-- end of studies -->
<div id="toolstab"><x-slot name="modal"></x-slot></div>
<!-- end of tools -->
<div id="contactinfo">
<x-contactform />
</div>
<!-- end of contactinfo -->
</div>

<!-- end of delegator / wrapper -->
<x-myjs />
<script nonce= "{{ csp_nonce() }}">

$(document).ready(function() {

// From Old Site
// 	$("#ccpa-wrapper").fadeIn( 1000, function() {
//     // Animation complete
//     });

// SCRIPTS SPECIFIC TO READERS PAGE

   // DO NOT REMOVE !!, TAG CHANGER FOR REPORT EDITOR

    (function($){
    var $newTag = null;
    $.fn.tagName = function(newTag){
        this.each(function(i, el){
            var $el = $(el);
            $newTag = $("<" + newTag + ">");

            // attributes
            $.each(el.attributes, function(i, attribute){
				//console.log(attribute);
                $newTag.attr(attribute.nodeName, attribute.nodeValue);
            });
            // content
            $newTag.html($el.val().replace(/\r?\n/g, '<br />'));

            $el.replaceWith($newTag);
        });
        return $newTag;
    };
    })(jQuery);


	// Preview, stores the current report fields into arrays, changes the tags, basically working

    $(document).contents().on("click", "#previewreport", function(){  // seems to be working for the most part.

            undoHTML = $(".reportdiv .htmlmarkup").clone(true,true);  //deep clone.
            var selects = $(".reportdiv .htmlmarkup").find("select");
            $(selects).each(function(i) {
                var select = this;
                $(undoHTML).find("select").eq(i).val($(select).val());
            });
            setButtons(buttonstatus.preview);

            $.each($(".reportdiv .htmlmarkup input"), function (index,value) {
                $(this).replaceWith("<span>" + $(this).val() + "</span>");

            });
            // need to check if this breaks ABMRA, style tags on the spans, can fix that with p > span
            $.each($(".reportdiv .htmlmarkup textarea"), function (index,value) {
                $(this).replaceWith("<span style='display:inline-block'>" + $(this).val() + "</span>");
            });

            $.each($(".reportdiv .htmlmarkup select"), function (index,value) {
                $(this).replaceWith("<span style='display:inline-block'>" + $(this).find(":selected").text() + "</span>");
            });
            $.each($(".reportdiv .htmlmarkup > section > section"), function (index,value) {
                $(this).replaceWith("<section>" + $(this).html() + "</section>");
            });

            $.each($(".reportdiv .htmlmarkup > section"), function (index,value) {
                $(this).replaceWith("<section>" + $(this).html() + "</section>");
            });
            $.each($(".reportdiv .htmlmarkup header"), function (index,value) {
                $(this).replaceWith("<header>" + $(this).html() + "</header>");
            });
            $.each($(".reportdiv .htmlmarkup label"), function (index,value) {
                $(this).replaceWith("<label>" + $(this).html() + "</label>");
            });

            $(".reportdiv .htmlmarkup header").each(function(){ $(this).html($(this).html().toUpperCase());});
    });

	// end Preview


	// Undo feature, restores the previously stored version.  undoHTML is a deep clone of the saved markup.

    $(document).contents().on( 'click', '#undoreport',  function() {

        $(".reportdiv .htmlmarkup").replaceWith(undoHTML);
        setButtons(buttonstatus.loaded);

    });

	function cyclicObject (obj) {

		var seen = [];

	JSON.stringify(obj, function(key, val) {
	   if (val != null && typeof val == "object") {
			if (seen.indexOf(val) >= 0) {
				return;
			}
			seen.push(val);
		}
		return val;
	});


		}

	// load the chosen report from the select list

    $(document).off().on("change", "#templateid", function(e) {

      e.preventDefault(e);
      loadtemplate(null);

    });

    function loadtemplate() {

     // loads a report, either from an existing one (with callbackfunction) or from the chooser (no callbackfunction), this should be the trigger to set the lockout status to In Use on AMBRA and/or In Use / locked out by uuid on our server, or both.  Callback is defined when loading a report from the old report viewer.

            var formdata = $("#templateid").serialize();
            formdata += "&modality=" + activestudytags["modality"] + "&description=" + activestudytags["description"] + "&option=loadreport" + "&uuid=" + activestudytags["uuid"];

            $.ajax({

                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                url: '/Reports/choose_template',
                type: 'POST',
                data: formdata,
                dataType: "json",
                complete: function(xhr, textStatus) {

                },
                success: function(data, textStatus, xhr) {
                    response = parseMessages(data, true);

                    if(!response.error) {

                        user = data.user;
                        report = data.report;

                        $('#markupform').html('<div class="htmlmarkup" name="htmlmarkup">' +  report + '</div>');
                        $(".reportdiv input").off();
                        $(".reportdiv input").keyup(function(event) {
                            if ($(".input1").is(":focus") && event.key == "Enter") {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                        });
                        $(".reportdiv input").on("mouseleave", function() {$(this).parent().find("label").css({"background-color" : "transparent"})});
                        $(".reportdiv input").on("mouseenter",function() {$(this).parent().find("label").css({"background-color" : "#555"})});

                        $('textarea').each( function() {  // set the text area to grow to content on load, applies to both.
                            $(this).outerHeight( 'auto' ).outerHeight( this.scrollHeight );
                        });

                        setButtons(buttonstatus.loaded);
                        $("#clinical_information").val($(".headerindication").html());
                        // if (user != "ME") $("#lockedwarning").html("Study is locked by user:  " + user);
                        if (user != "ME") showMessage("Warning","Study is locked by user:  " + user);

                    }

                    else {  // some other type of error
                        setTimeout(function () {location.href = "/"}, 2000); // redirect home
                    }
                }
            });
    }


    function printreport(markup) {

    var printWindow = window.open('', '', 'height=800, width=640, toolbars=no, status=no, titebar=no, location=no, top=0, left=0, menubar=no, scrollbars=yes');
    printWindow.document.write(markup);
    printWindow.document.close();
    printWindow.print();

}

	// handler to print a report from the viewer

	$(document).on("click", "#viewerprint", function () {

				var report = $("#apiresults")[0].outerHTML;
				printreport(report);

	});

	// function to save a report, any type, prelim, final or addendum

    $(document).on("click", "#prelimreport, #finalreport,#addendumreport", function () {  // preview occurs first

          	var template_id = $("#templateid option:selected").val();  // the template id used for the report, could add the radreports id as well
			var status = $(this).val();  // from this page, prelim, etc., from the button
			var oldstatus = workingelement.data( "reportstatus" );
			var type ="HL7";

            if ($("#textmarkup").length != 0) {
            content = $("#textmarkup").val();
            }
            else { //
            content = $(".reportdiv .htmlmarkup").html();
            }
			let PACS = $(this).closest("#tools").data("pacs");
			$.ajax({
			    headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
          		url: '/HL7/submit_report',
          		type: 'post',
          		dataType: 'json',
          		data: {"uuid":activestudytags["uuid"], "oldstatus":oldstatus, "newstatus":status, "report":content, "template_id":template_id},
          		complete: function(xhr, textStatus) {

          		},
          		success: function(data, textStatus, xhr) {
          		 response = parseMessages(data, true);
//           		 alert(response.HL7Message);
//                 data=response.reports;

                    $("[data-uuid='" + activestudytags["uuid"] + "'].worklist").data( "reportstatus", status );  // set data status for all studies with that uuid
                    $("[data-uuid='" + activestudytags["uuid"] + "'].worklist").find(".reportstatus").html(status);  // set status for all studies with that uuid
                    setButtons (buttonstatus.preview);  // update the button status, should always be in preview mode after saving a report
                    createreportlist();  // update the reports list

          		}
          	});
    });

// PlaceOrder Handler, Dynamically attached, should do that for all with enclosing div


$('#delegator').on('click', '.placeorder, .modifyorder', function(e) {

	e.preventDefault();
    e.stopImmediatePropagation();
	PlaceModifyOrder($(this));

});

var orderlistheader = '<div class="row divtable widemedia worklistheader"><div class="col-sm-3 nopadding"><div class="col-sm-6" data-sort-param="data-name" data-sort-order="up"><span>Name</span></div><div class="col-sm-6" data-sort-param="data-age" data-sort-order="up"><span class="narrowmedia">Age:  </span><span>Age</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-2" data-sort-param="data-sex" data-sort-order="up"><span>Sex</span></div><div class="col-sm-5" data-sort-param="data-mrn" data-sort-order="up"><span>MRN</span></div><div class="col-sm-5" data-sort-param="data-description" data-sort-order="up"><span>Descripiton</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-2" data-sort-param="data-modality" data-sort-order="up"><span>Modality</span></div><div class="col-sm-5" data-sort-param="data-referring_physician" data-sort-order="up"><span>Referring Physician</span></div><div class="col-sm-5" data-sort-param="data-date" data-sort-order="up"><span>Date</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-2" data-sort-param="data-time" data-sort-order="up"><span>Time</span></div><div class="col-sm-6" data-sort-param="data-accession" data-sort-order="up"><span>Accession</span></div><div class="col-sm-4" data-sort-param="data-status" data-sort-order="up"><span>Status</span></div></div></div>';

$("#delegator").on ("click", ".showorders", function(e) {

    var activepatient = $(this).closest('.worklist');
    let element = activepatient.next();
    if (element.attr("id") != "patientdiv") {
    e.preventDefault(e);
    $.ajax({

        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    	url: '/Orders/order_list',
    	type: 'POST',
    	dataType: 'json',
    	data: {"id" : activepatient.data('id'), "mrn" : activepatient.data('mrn'), "data-mrn" : activepatient.data('mrn')}, // for later maybe, one will be set for existing patient.
    	beforeSend: function(e) {
            $("body").addClass("loading");
        },
    	success: function(data, textStatus, xhr) {
			data = parseMessages(data, true);
			if (data.status) showMessage(data.status,"");
			else {
            $("#patientdiv").remove();  // remove it since it might already be there.
    	    $(activepatient).after('<div id="patientdiv" class = "listwrapper">' + orderlistheader + '<div class="RISpaginator" data-target="#showorderswrapper" data-url="/Orders/order_list">' + data.RISpaginator + '</div><div id ="showorderswrapper">' + data.html + '</div></div>');
            colorrows($("#patientdiv .worklist"));

            }
    	}
    });
    }
    else element.remove();

});


//  GETS THE MARKUP FOR THE ORDER WORKLIST ROWS AND REPLACES THE OLD ONE BY ACCESSION, FOR THE PATIENTS, ORDERS AND ADMIN BILLING PAGES.

window.replaceOrderRow = function (accession) {

    $.ajax({

        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    	url: '/Orders/order_row_getByAccession',
    	type: 'POST',
    	dataType: 'json',
    	data: {"data-accession":  accession}, // for later maybe, one will be set for existing patient.
    	complete: function(xhr, textStatus) {

    	},
    	success: function(data, textStatus, xhr) {
    	    $("[data-accession=" + accession + "][data-rowtype='order'].worklist").replaceWith(data.markup);
    	    colorrows ($('#patientdiv .worklist, #orderswrapper .worklist'));
    	}
    });
}


$('body').on('click', '#newpatient, #view_update_selected', function(e) {

    e.preventDefault(e);
    if (($(this).attr('id') == "view_update_selected" && $('.selectPatient').length > 0) || $(this).attr('id') == "newpatient") {
    if ($(this).attr('id') == "newpatient") {
    $('.selectPatient').removeClass('selectPatient');
    var mrn = '';
    }
    else {
        mrn = $('.selectPatient').data('mrn');
    }
    if ($(this).get(0).hasAttribute('data-processme')) {
    	loadPatient(mrn, datafornewpatient);
    }
    else {
    	loadPatient(mrn);
    }

    }
    else {
        alert ("Select a Patient");
    }
});

$('#patientswrapper').on('click', '.patient', function() {  // simple code for highlight and current patient.

    $('#patientswrapper .worklist').removeClass('selectPatient');
    $(this).addClass('selectPatient');

});

$('.datepicker input[type=date]').prop("type", "text");  // something to do with Chrome


$(".closeresults").on('click', function(e) {
        $('#apiresults').html("");
        $("#APImonitor").css("display","none");

});


//  Defaults on loading the page, working list and studies

var studytable = $('#studieswrapper');
var studies = $('#studieswrapper .worklist');
// colorrows(studies);
// colorrows($(".colorrows"));  // f
colorrows($(".worklist"));
$("body").css("display", "block");

$("#studieswrapper").css("display", "block");



//  Generic thing to allow editing single fields in a form, submits the name/value pair, the action, the csrf when the selected element is changed.

//  generic function, like below, for loading html via ajax, data-target is the url, puts the response in ajaxdata.  can pass in ?x=y as a get.

    $(document).on("click", ".ajaxload", function(e) {
//     alert($(this).data("scroll"));

    e.preventDefault();
    e.stopPropagation();

     $(".ajaxdata").load($(this).data("target"), function(responseTxt, statusTxt, xhr) {

     });  //general form for load

    });

//  enctype="multipart/form-data", generic format for sending a form with the class .ajaxpostform and data-target the url, gets back html, can be used with files and images also.

    $(document).on("submit", ".ajaxpostform", function(e) {

    e.preventDefault();
    e.stopPropagation();
    var formData = new FormData(this);

    $.ajax({

        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    	url: $(this).data("target"),
    	type: 'POST',
    	dataType: 'html',
    	data: formData,
    	cache:false,
    	contentType: false,
    	processData:false,

    	complete: function(xhr, textStatus) {

    	},
    	success: function(data, textStatus, xhr) {
    	$(".ajaxdata").html(data);

    	}
    });

    });



    $("body").css("visibility", "visible");
    $("body").css("height", "100%");
    $("body").css("width", "100%");
    $("#spinner").css("display", "none");


   attachDateTimePicker();
   $('input[type=date]').prop("type", "text");

   	$(document).on("click", '.loadcontent', function(event) {

	 event.preventDefault();
	 $("#spinner").css("display", "block");
	 $("#dynamiciframe").append($('<iframe style="width:100%;border:none;margin:0px;overflow:scroll;background-color:transparent;height: 100vh;" class="vieweroverlay-content" id="viewerframe" src="' + this.href +  '"></iframe>'));
	 $("#viewerframe").on("load", function(e) {
	 $("#spinner").css("display", "none");
	 document.getElementById("myNav").style.width = "100%";
	 $("body").css("overflow", "hidden");
	 });
	});


});  // end of document.ready

</script>

<script nonce= "{{ csp_nonce() }}">
//var greetES6 = `Hello ${name}`;//using ticks
function template(study) {

	let html = `<!-- BEGIN STUDY -->
	<div class="row divtable worklist" data-uuid="${study.ID}" data-studyinstanceuid="${study.MainDicomTags.StudyInstanceUID}" data-name="${study.PatientMainDicomTags.PatientName}" data-dob="${study.dob}" data-age="${study.age}" data-sex="${study.sex}" data-mrn="${study.PatientMainDicomTags.PatientID}" data-accession="${study.MainDicomTags.AccessionNumber}" data-description="${study.MainDicomTags.StudyDescription}" data-modality="${study.modalities}" data-images="${study.imagecount}" data-studydate="${study.studydate}" data-orthancstatus="${study.stable}" data-reportstatus="${study.reportstatus}" data-indication="${study.indication}" data-referring_physician="${study.MainDicomTags.ReferringPhysicianName}" data-billing_status="${study.billingingstatus}">
	<div class="col-sm-3 nopadding">
	<div class="col-sm-6"><span class="rowcounter">${study.rowno}</span><span class="narrowmedia">View: </span><a class="viewstudy" href="#" target="_blank"><img class="uiicons" src="/images/view_images.png" title="View"></a><span style="max-width: 50px;display: inline-block;text-overflow: ellipsis;overflow: hidden;">${study.MainDicomTags.ReferringPhysicianName}</span> <br><span class="narrowmedia">Name: </span><span data-toggle="tooltip" data-placement="top" title="${study.PatientMainDicomTags.PatientName}, Doctor:  ${study.MainDicomTags.ReferringPhysicianName}">${study.PatientMainDicomTags.PatientName}</span></div>
	<div class="col-sm-6"><span class="narrowmedia">DOB / Age: </span> <a href="#"><img class="createOrthancReport uiiconslarge" src="/images/report.png" title="Reports"></a><span data-toggle="tooltip" data-placement="top" title="DOB / Age">${study.dob} / ${study.age}</span><a href="#"><img class="create-dicom uiiconslarge" src="/js/create_dicom/img/studydoc.png" title="CreateDicom"></a> <div class="reportstatus">${study.reportstatus}</div></div>
	</div>
	<div class="col-sm-3 nopadding">
	<div class="col-sm-2"><span class="narrowmedia">Sex: </span><i class="showselect far fa-paper-plane"></i><select class="route_select"></select><span>&nbsp;${study.sex}</span></div>
	<div class="col-sm-5"><span class="narrowmedia">Download:&nbsp;&nbsp;</span><a href="#"><span class="downloadiso_orthanc uibuttonsmallred">"DCM"</span></a><br><span class="narrowmedia">MRN: </span><span data-toggle="tooltip" data-placement="top" title="${study.PatientMainDicomTags.PatientID}">&nbsp;${study.PatientMainDicomTags.PatientID}</span></div>
	<div class="col-sm-5"><span class="narrowmedia">Download:&nbsp;&nbsp;</span><a href="#"><span class="downloadzip_orthanc uibuttonsmallred">"ZIP"</span></a><br><span class="narrowmedia">Accession: </span><span data-toggle="tooltip" data-placement="top" title="${study.MainDicomTags.AccessionNumber}">&nbsp;${study.MainDicomTags.AccessionNumber}</span></div>
	</div>
	<div class="col-sm-3 nopadding">
	<div class="col-sm-8"><span class="narrowmedia">Show All:</span> <a href="#" data-description="${study.MainDicomTags.StudyDescription}" data-mrn="${study.PatientMainDicomTags.PatientID}"><span class="allstudies_orthanc uibuttonsmallred">"ALL"</span></a><br><span class="narrowmedia">Description:</span><span>${study.MainDicomTags.StudyDescription}</span> <a href="#" data-uuid="${study.ID}" class="share" style="position: absolute;top: 0px;right: 0px;"><span class="uibuttonsmallred">"Share"</span></a></div>
	<div class="col-sm-2"><span class="narrowmedia">Modality: </span><span>${study.modalities}</span></div>
	<div class="col-sm-2"><span class="narrowmedia">Images: </span><span>${study.imagecount}</span></div>
	</div>
	<div class="col-sm-3 nopadding">
	<div class="col-sm-4"><span class="showpatienthistory uibuttonsmallred">Patient History</span> <span class="narrowmedia">History: </span><span data-toggle="tooltip" data-placement="top" title="${study.indication}" style="width: auto;white-space: nowrap;left: 0px;bottom: 0px;text-overflow: ellipsis;overflow: hidden;display: block;">${study.indication}</span> </div>
	<div class="col-sm-8" data-toggle="tooltip" data-placement="top" title="${study.studydate}"><span class="narrowmedia">Study Date: </span><span>${study.studydate}</span><br><span class="narrowmedia">Stable: </span><span>${study.stable}</span> </div>
	</div>
	</div><!-- END STUDY -->`;
return html;

}

</script>

<!-- SCRIPTS FOR READERS -->

<script nonce= "{{ csp_nonce() }}">

//  set of global js variables for report generation

    var activestudytags;  // from the data- elements for the study that is being worked on
    var dataobject;  // object to attach to the active study for easy access, and attaching the report objects to a study after retrieval.
    var undofieldsarray;
    var undoselectsarray;
    var reportobjects;
    var reportslistoptions
    var parentelement;
    var workingelement;
    var loadedreportmarkup;
    var undoHTML;  // needs to be global aparently.
    var undotextmarkup;
    var Markup;

	// open = handler for form .viewreport, loaded = handler for form templatechooser, preview = handler for #previewreport
    // buttons are #loadreport, #previewreport, #undoreport
    // primary = blue, danger = red, info = light blue, success = green, danger = red

    var buttonstatus = new Object;
    var undoHTML = "";

    buttonstatus.opened = {

    Preview: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Undo: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Prelim: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Final: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Addendum: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    }
    }

    buttonstatus.loaded = {

    Preview: {
    class: "btn-primary btn-sm btn-show",
    disabled:  "false"
    },
    Undo: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Prelim: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Final: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Addendum: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    }
    }

    buttonstatus.preview = {

    Preview: {
    class: "btn-danger btn-sm btn-hide",
    disabled:  "true"
    },
    Undo: {
    class: "btn-primary btn-sm btn-show",
    disabled:  "false"
    },
    Prelim: {
    class: "btn-primary btn-sm btn-show",
    disabled:  "false"
    },
    Final: {
    class: "btn-primary btn-sm btn-show",
    disabled:  "false"
    },
    Addendum: {
    class: "btn-primary btn-sm btn-show",
    disabled:  "false"
    }
    }

    function setButtons (buttonobject) {

    $("#loadreport,#previewreport,#undoreport,#prelimreport,#finalreport,#addendumreport").removeClass();  // revmoves all classes

    // preview by default has undo, prelim, final and addendum enabled
    // need to check the current status
    // if prelim, then allow only another prelim or Final
    // if final, then allow only an addendum
    // if addendum, then allow another Addendum

    if (buttonobject == buttonstatus.preview) {
    	//console.log(workingelement.data( "reportstatus"));
        if (workingelement.data( "reportstatus").toUpperCase() == "PRELIM") {  // disable the addendum, can still issue another Prelim or Final
            buttonobject.Prelim.class = "btn-primary btn-sm btn-show";
            buttonobject.Prelim.disabled = "false";
            buttonobject.Final.class = "btn-primary btn-sm btn-show";
            buttonobject.Final.disabled = "false";
            buttonobject.Addendum.class = "btn-danger btn-sm btn-show";
            buttonobject.Addendum.disabled = "true";

        }
        else if (workingelement.data( "reportstatus").toUpperCase() == "FINAL") { // disable the prelim and final, only can create an addendum
            buttonobject.Prelim.class = "btn-danger btn-sm btn-show";
            buttonobject.Prelim.disabled = "true";
            buttonobject.Final.class = "btn-danger btn-sm btn-show";
            buttonobject.Final.disabled = "true";
            buttonobject.Addendum.class = "btn-primary btn-sm btn-show";
            buttonobject.Addendum.disabled = "false";
        }
        else if (workingelement.data( "reportstatus").toUpperCase() == "ADDENDUM") {  // disable the prelim and final, only can create an addendum
            buttonobject.Prelim.class = "btn-danger btn-sm btn-show";
            buttonobject.Prelim.disabled = "true";
            buttonobject.Final.class = "btn-danger btn-sm btn-show";
            buttonobject.Final.disabled = "true";
            buttonobject.Addendum.class = "btn-primary btn-sm btn-show";
            buttonobject.Addendum.disabled = "false";
        }
        else if (workingelement.data( "reportstatus").toUpperCase() == "NONE") {  // disable the addendum
            buttonobject.Prelim.class = "btn-primary btn-sm btn-show";
            buttonobject.Prelim.disabled = "false";
            buttonobject.Final.class = "btn-primary btn-sm btn-show";
            buttonobject.Final.disabled = "false";
            buttonobject.Addendum.class = "btn-danger btn-sm btn-show";
            buttonobject.Addendum.disabled = "true";
        }
    }

//     $("#loadreport").addClass(buttonobject.Load.class);
//     $("#loadreport").prop("disabled",(buttonobject.Load.disabled === "true"));

    $("#previewreport").addClass(buttonobject.Preview.class);
    $("#previewreport").prop("disabled",(buttonobject.Preview.disabled === "true"));

    $("#undoreport").addClass(buttonobject.Undo.class);
    $("#undoreport").prop("disabled", (buttonobject.Undo.disabled === "true"));

    $("#prelimreport").addClass(buttonobject.Prelim.class);
    $("#prelimreport").prop("disabled", (buttonobject.Prelim.disabled === "true"));

    $("#finalreport").addClass(buttonobject.Final.class);
    $("#finalreport").prop("disabled", (buttonobject.Final.disabled === "true"));

    $("#addendumreport").addClass(buttonobject.Addendum.class);
    $("#addendumreport").prop("disabled", (buttonobject.Addendum.disabled === "true"));

    }

    $("#studieswrapper").on('click', ".createOrthancReport", function(e) {

        e.preventDefault();
        if (false) { //if (!($('#teleRadDivOverlay').is(':empty'))) {
        // Do nothing, they are in the editor, it actually still does toggle the editor div though, need to attach event to the other handler to indicate the report is open for editing when they load a template

        }
        // load the reports window
        else {

        // want to add an API call here to fetch the radreports for this study from AMBRA, which would be an array or JSON.  Need to parse and process that so that we can determine what if any reports already exist, allow prelimns, finals, addendums as appropriate, etc.

          $("#teleRadDivOverlay").html(""); // make sure the overlay is empty
        // add the framework
          parentelement = $(this).closest(".worklist");
          workingelement = parentelement.clone(true,true);  // the row for the study that we want to read
          workingelement.find("#patientdiv").remove();
          workingelement.find(".showselect").remove();  // get rid of the fetch thing
          $("#teleRadDivOverlay").prepend(workingelement);
          workingelement.css("background-color", "#888");
          $("#teleRadDivOverlay").prepend($("#studieswrapper > .worklistheader").clone()); // just the primary header
          $("#teleRadDivOverlay").prepend("<div id='reportselectorwrapper'></div>");
          $("#teleRadDivOverlayWrapper").css("width", "100%");
          $("#teleRadDivOverlayWrapper").css("display", "block");
        //   $("#teleRadDivOverlay").append('');
          $("#delegator").css("display", "none");  // hide the content below
          //$( "#teleRadDivOverlayWrapper .reporticon" ).trigger( "click" ); // opens the report div, probably don't need this


         // set up the buttons in the toolbar for the stage of the report, maybe don't need this anymore
          //setButtons(buttonstatus.opened);

          // var formdata = $(this).parents().eq(2).data("modality");

          // populates js array for later use for the active report, put the data-xxx tag values into an object and we then have NVP's for the data as below, activestudytags[name] is the value
          dataobject = $(this).closest(".worklist").data();
            //  initializes array for data- elements in the rows for every study
           activestudytags = [];
          for (var k in dataobject){
            if (dataobject.hasOwnProperty(k)) {
              activestudytags[k] = dataobject[k];  // create key-value pairs, name, age, dob, sex, mrn,accession,description,modality,images,harvested,studydate,status
            }
          }

          //populate the header

          $(".headername").html(activestudytags["name"]);
          $(".headerage").html(activestudytags["age"]);
          $(".headerdob").html(activestudytags["dob"]);
          $(".headersex").html(activestudytags["sex"]);
          $(".headermrn").html(activestudytags["mrn"]);
          $(".headeracc").html(activestudytags["accession"]);
          $(".headerdesc").html(activestudytags["description"]);
          $(".headermod").html(activestudytags["modality"]);
          $(".headerexamdate").html(activestudytags["studydate"]);
          $(".headerprovider").html(activestudytags["referring_physician"]);
          $(".headerindication").html(activestudytags["indication"]);

          // create the select list if we are editing, loads the template choices from API call, filtered by modality in this case.

          formdata = "modality=" + activestudytags["modality"] + "&description=" + activestudytags["description"] + "&option=getlist";
            $.ajax({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            url: '/Reports/radreport_templates_list',  // calls the Reports controller.
            type: 'POST',
            dataType: 'json',
            data: formdata,  // gets all of the hl7 reports encoded as JSON, api gets all hl7 reports for study
            complete: function(xhr, textStatus) {
            },
            success: function(data, textStatus, xhr) {
                $("#templateid").html(data.selectlist);
            }
          });
            $("[name='htmlmarkup']").html("");
          createreportlist();
        }
        // end of setting up the overlay

    });

    // function to create the select list for reports and to attach the report objects / key, call when opening the editor and after saving a report

    function createreportlist() {

            let uuid = activestudytags["uuid"];
			let accession_number = activestudytags["accession"];

          $.ajax({
            headers: {
	        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
	        },
            url: '/HL7/getallhl7_reports',  // calls the AJAX controller.
            type: 'POST',
            dataType: 'json',
            data: {"uuid": uuid,"accession_number": accession_number },  // gets all of the hl7 reports encoded as JSON, api gets all hl7 reports for study
			beforeSend: function(e) {
			    $("body").addClass("loading");
			},
            success: function(data, textStatus, xhr) {
                // create the select list for existing reports
                $("#reportselectorwrapper").html('&nbsp;&nbsp;<span style="color:white;">. . . loading reports</span>');
                response = parseMessages(data, true);
                if (!data.error) {
                    data=response.reports;
                    console.log(data.hl7.length);
                    reportslistoptions = "";
                    reportobjects = [];
                    email = data.user_email;
                    if (data.hl7.length != 0) {
                    data.hl7.forEach(function(currentValue,index) {
                	currentValue.OBX.header = currentValue.header;
                	currentValue.OBX.footer = currentValue.footer;
                	currentValue.OBX.body = currentValue.body;
                    addReport(currentValue.OBX);   // proceses the AJAX request, segments has all the OBX data for each report.
                });
                    finishReportSetup(email);
                    }
                    else {
                    $("#reportselectorwrapper").html('<span id="noreports">There are no reports for this study</span>');
                }
                }
            }
            });
            }

    function finishReportSetup(email) {

            reportslistoptions = '<form id ="reportsselectorform" name="reportsselectorform"><select id="reportsselector" name="reportsselector">' + reportslistoptions + '</select></form>';

            $("#reportselectorwrapper").html(reportslistoptions + '<input id ="loadoldreport" type="submit" class = "btn-primary btn-sm btn-show" value="Load into Editor">');  // put the select list on the page

            $.each($("#reportsselector option"), function(key,value) {  // attach the report object to the option, along with the key value for report

                $(this).data("reportobject", reportobjects[key]);
                $(this).data("report", reportobjects[key].header + reportobjects[key].body + reportobjects[key].footer);

            });


            var selectList = $('#reportsselector option');  // sorts

            selectList.sort(function(a,b){
                a = a.dataset.reportdate;
                b = b.dataset.reportdate;
                return a.localeCompare(b);
            });

            selectList.appendTo('#reportsselector');


            $("#reportsselector option:last-child").attr("selected", "selected");  // set the last element in the list, the latest report

            $( "#reportsselector").on( "change", function() {
                loadreport($("#reportsselector option:selected").data("report"), email);
            });

            $("#reportsselector").on("contextmenu", function(e) {
            	e.preventDefault();
            	e.stopImmediatePropagation();
				loadreport($("#reportsselector option:selected").data("report"), email);
            });

            // attaches the loadoldreport handler to the button, template id varies and is for the selected report, check to see if set for hl7

            $("#loadoldreport").on("click", function(e) {
                // put the body into the editor and set content to editable
                e.preventDefault();
                //console.log($("#reportsselector option:selected").data("reportobject"));
                $("#markupform").html($("#reportsselector option:selected").data("reportobject").body);
                $("#markupform .htmlmarkup").attr("contenteditable", true);
                $("#templateid").val("");
                setButtons(buttonstatus.preview);
                $("#undoreport").addClass("btn-danger btn-sm btn-show");
                $("#undoreport").prop("disabled", true);
            });
    }

    function addReport(obxmessage) {  // created, segments, uuid

    reportobjects.push(obxmessage);
    // Mapping from HL7 obxmessage[11][0], Observation Result Status
    statuses = {
    "P":"PRELIM",
    "F":"FINAL",
    "C":"ADDENDUM"
    }
    displaystatus = statuses[obxmessage[11][0]];
    reportslistoptions+='<option data-reportobject="" data-reporttype="' + 'hl7' + '" data-reportdate="' + formatHL7date(obxmessage[14][0]) + '">' +  formatHL7date(obxmessage[14][0]) + ", " + displaystatus + ':' + (obxmessage[16][2] != ""?obxmessage[16][2] + " ":"") + (obxmessage[16][3] != ""?obxmessage[16][3] + " ":"") + (obxmessage[16][1] != ""?obxmessage[16][1] + " ":"") + (obxmessage[16][5] != ""?obxmessage[16][5]+ " ":"")   + '</option>';  // need to add reading doctor, 15 and 16, 14 is the date
    }

    function formatHL7date(date) {  // convert YYYYMMDDHHMMSS to YYYY-MM=DD HH:MM:SS

    year =date.substring(0,4);
    month =date.substring(4,6);
    day  =date.substring(6,8);
    hours  =date.substring(8,10);
    minutes  =date.substring(10,12);
    seconds  =date.substring(12,14);
    return (year + "-" + month + "-" + day + " " + hours + ":" + minutes + ":" + seconds);
    }


</script>

</x-app-layout>
