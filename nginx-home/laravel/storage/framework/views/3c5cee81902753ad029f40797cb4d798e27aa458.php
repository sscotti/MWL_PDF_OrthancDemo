<!--
Cross Site Request Forgery (CSRF) TOKEN genereated by Laravel, USAGE
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') in ajax
<form method="POST" action="/profile">
    <?php echo csrf_field(); ?>
    YIELDS
    <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>" />

</form>
COOKIE:  X-XSRF-TOKEN , set that header if you want to read the COOKIE

https://datatables.net/download/
-->
<!-- Jquery Validate -->

<script nonce = "<?php echo e(csp_nonce()); ?>">

$.validator.addMethod("jqvalidmyemail",function(value, element) {


		 if(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/.test( value ))
			{
				 return true;
			}
			else if (value == "") {
			    return true;
			}
			else
			{
				return false;
			}
},"Please enter a valid Email.");

$.validator.addMethod("jqvalidmydates",function (dateString) {
var regEx = /^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/;
return ((dateString == "") || regEx.test(dateString));
},"Please enter a valid date format like yyyy-mm-dd."
)

$.validator.addMethod(
    "jqvalidcurrency",  // name to reference in rules

    function isValidCurrency(currency) {

      var regEx = /^[-]?([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2})$/;
      return regEx.test(currency);  // Invalid format
    },"Please enter a valid currency without commas"

);

$.validator.addMethod(
  "jqvalidmynames",
      function isValidName(name) {
        if (name != "") {
        return true;
        }
    },"Please enter a name."
);

$.validator.addMethod(
  "jqrequired",
      function isPresent(item) {
        if (item != "") {
        return true;
        }
    },'<span style = "color:red">This is required.</span>'
);

$.validator.addMethod(

  "passwordmatch",
      function isValidMatch() {

        if ($(".passwordmatch")[0].value == $(".passwordmatch")[1].value)  {
        $("#" + $(".passwordmatch").attr("id") + "-error").remove();
        $(".passwordmatch").removeClass("error");
        return true;
        }

    },"Passwords must match."
);

$.validator.addMethod(

  "jqvalidpercentage",

      function isValidPercentage(entry) {

       var regEx = /^((100)|(\d{1,2}(.\d*)?))$/;
      return regEx.test(entry);  // Invalid format

    },"enter as 0 to 100 +- 2 digit decimal"
);

$.validator.addMethod(

  "jqvalidphone",

      function isValidPercentage(entry) {
	  if (entry != "") {
      var regEx = /^[\d-]+$/;  // up to 20 digits only
      return regEx.test(entry);  // Invalid format
      }
      else return true;

    },"Enter Only Digits & Dashes"
);

jQuery.validator.addClassRules("jqvalidphone", {  // classes to add method to.
  jqvalidphone: true
});

jQuery.validator.addClassRules("jqvalidpercentage", {  // classes to add method to.
  required:true,
  jqvalidpercentage: true
});

jQuery.validator.addClassRules("jqrequired", {  // classes to add method to.
  required:true
});

jQuery.validator.addClassRules("jqvalidcurrency", {  // classes to add method to.
  required:true,
  jqvalidcurrency: true
});

jQuery.validator.addClassRules("passwordmatch", {  // classes to add method to.
  required:true,
  passwordmatch: true
});

jQuery.validator.addClassRules("jqvalidmydates", {  // classes to add method to.
  jqvalidmydates: true
});

jQuery.validator.addClassRules("jqvalidmyemail", {  // classes to add method to.
  required: false,
  jqvalidmyemail: true
});

jQuery.validator.addClassRules("jqvalidmynames", {  // classes to add method to.
  jqvalidmynames: true
});

jQuery.validator.addClassRules("validate_password", {  // classes to add method to.
  validate_password: true
});

// thing to automatically validate the whole form once an field is changed.

$("form[novalidate='novalidate'] input:not(.search-txt), form[novalidate='novalidate'] select, form[novalidate='novalidate'] textarea ").on("change", function(e) {
$(this).closest("form").valid();
});
// Add all of the form to add validation to

$("#demographics").validate();
$("#patientform").validate();
$("#orderrequestform").validate();
$("#userform").validate();
$("#new_password_form").validate();

</script>

<script nonce = "<?php echo e(csp_nonce()); ?>">

// FUNCTIONS & Setup

var datachangeflag;

function confirmSave(element) {

    if (datachangeflag !== true) {
    datachangeflag = false;
    $("#confirmtextwrapper").remove();
    element.after('<div id = "confirmtextwrapper"><div id = "confirmtext">Confirm ?</div></div>');
    $("#confirmtext").on("click", function(e) {
         datachangeflag = true;
         $("#confirmtextwrapper").remove();
         element.trigger("click");
    });
    $("#exittext").on("click", function(e) {
         $("#confirmtextwrapper").remove();
    });
    }

}

function colorrows (e) {

    return true;
    // may not need this anymore !!
    //e.filter(function(index) {return $(this).css('display') == 'flex' || $(this).css('display') == 'table'}).filter(':even').css({"background-color" : "#DDD"});
    //e.filter(function(index) {return $(this).css('display') == 'flex' || $(this).css('display') == 'table'}).filter(':odd').css({"background-color" : "#AAA"});

}

function attachDateTimePicker()  {

//             year = new Date().getFullYear();
            $('.datepicker:not(.hasdatepicker)').datepicker({

            orientation: "top",
            autoclose: true,
            showOtherMonths: true,
            selectOtherMonths: true,
            showButtonPanel: true,
            changeMonth: true,
            changeYear: true,
            dateFormat: 'yy-mm-dd',
            showWeek: true,
            firstDay: 1,
            yearRange: '1920:2030' ,
            constrainInput: false,
            beforeShow: function(element, ui){
                //console.log(ui.dpDiv);
                ui.dpDiv.css('font-size', 12),
                ui.dpDiv.css('z-index', 100000);
            },
            onSelect: function(d,i){
            if(d !== i.lastVal){
                 $(this).change();
                 $(this).valid();
            }
            }
            });

            $('.timepicker:not(.ui-timepicker-input)').timepicker({
              timeFormat: 'H:i',
              step: "15",
              dynamic:true,
              maxHour: 24
            });
}

attachDateTimePicker();

function attachSumoSelect(element) {

            // sets direction of opening
            if ($("#insurance" + " select").closest("#insurance").length == 1) up = true;
            else up = false;
            $(element + " select").SumoSelect({

                placeholder: 'Select Here',
                csvDispCount: 0,
                captionFormat: '{2} Selected',
                floatWidth: 500,
                forceCustomRendering: false,
                nativeOnDevice: ['Android', 'BlackBerry', 'iPhone', 'iPad', 'iPod', 'Opera Mini', 'IEMobile', 'Silk'],
                outputAsCSV : false,
                csvSepChar : ',',
                okCancelInMulti: true,
                isClickAwayOk: false,
                triggerChangeCombined : true,
                selectAll : false,
                search : true,
                searchText : 'Search...',
                noMatch : 'No matches for "{0}"',
                prefix : '',
                locale :  ['OK', 'Cancel', 'Select All'],
                up : up,
                showTitle : false
            });

            $(element +" .CaptionCont").removeAttr("title");  // the showTitle option does not work.

            /*
            $(".SumoSelect").on("keydown", function(e) {

                select = $(this).find(".SumoUnder");
                count = select.find("option").size();
                currentindex = select.prop('selectedIndex');
                handle = $(this)[0];
                console.log(handle);
                switch(e.which) {
                case 37: // left
                break;

                case 38: // up
                currentindex = Math.max(0, currentindex-1);
                console.log(currentindex);
                select.prop('selectedIndex', currentindex);
                break;

                case 39: // right
                break;

                case 40: // down
                currentindex = Math.min(count -1 , currentindex+1);
                console.log(currentindex);
                select.prop('selectedIndex', currentindex);
                break;

                default: return; // exit this handler for other keys
                }
                e.preventDefault(); // prevent the default action (scroll / move caret)

            });

            */
            /*  Country Flags, performance hit, but nice.
            CountryCode = [];
            original = $(element + " #country option, " + element + " #mobile_phone option, " + element + " #alt_mobile_phone_country option," + element + " #mobile_phone_ctry option, " + element + " #ins_mobile_phone option, " + element + " #ins_country option" );
            $.each(original, function(key, value) {

            CountryCode[key] = $(value).attr("value");
            });


            Rendered = original.closest(element +" .SumoSelect").find(".opt" );
            ListItems = [];
            $.each(Rendered, function(key, value) {
            if (CountryCode[key] == "" || CountryCode[key] =="" || CountryCode[key] =="") CountryCode[key] = "US";
            $(value).prepend("<img src='/css/flags/" + CountryCode[key] + ".ico' >");
            ListItems[key] = value;
            });
            */


}

function isValidDate(date) {

	var date  = moment(date, 'YYYY-MM-DD', true);
	var isValid = date.isValid();
	return isValid;
}

function isJsonString(str) {  // checks to see if a string is JSON for those instances when AJAX response could be HTML or JSON string, kind of weird, but a way to pass error codes back.
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return isNaN(str);
}

function parseMessages(messages, display) {  // Accepts an Array of objects, or a String of an array of objects, returns false if not an array.
// displays status notices, error messages and redirects for CSFR token issues or not authorized.
// Also returns other values that might be in the returns data.
// Does not take an object or a JSON string.

                error = false;
                var returned = {};
                var displayedtitle = "";
                var displayedtext = "";

                try {
                    if (typeof messages == "string") { // might be a string or not JSON
                    messages = JSON.parse(messages);
                    }
                }
                catch {
                    return messages;
                }
                if (!Array.isArray(messages)) messages = [messages];

                $.each(messages, function( index, value ) {

                    if(isJsonString(value)) message = JSON.parse(value);
                    else message = value;
                    console.log(message);
                  if (message.status != undefined) {  // if message is set then display.
                  displayedtext  += message.status + "<br>";
                  }
                // if bad token or AJAXRedirect then redirect home, but be logged out with token.

                  if (message.token != undefined  || message.AJAXRedirect != undefined) {
                    setTimeout(function () {location.href = "/"}, 2000);
                    showmodal("Error", message.error);
                    return false;
                  }
                  if (message.error != undefined) {  // if error is set then display that and set error to true
                  displayedtext  += message.error + "<br>";
                  error = true;
                  error = message.error;
                  }


                  else {
                    for (var k in message) {
                        returned[k] = message[k];
                  }
                  }

                });
if (displayedtext != "" && display) showMessage("", displayedtext);
                returned.error = error;
                return returned;
}

function logViewStudy(study, event) {


    $.ajax({

        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        beforeSend: function(e) {
            $("body").addClass("loading");
        },
    	async: false,
        type: "POST",
        url: '/PACS/logViewStudy',
        dataType: "json",
        data: {StudyInstanceUID: study.data('studyinstanceuid'), uuid: study.data('uuid'), mrn: study.data('mrn'), accession: study.data('accession'), description: study.data('description')},

    })
    .done(function(data, textStatus, jqXHR) {

        response = parseMessages(data,true);

        if (response.link == "DOWN") {
            showMessage("", "No Connectivity with Image Server");
        }

        else if (response.error) {

            showMessage("", response.error);
        }
        else if (event.type == "click") {

            $("#dynamiciframe").append($('<iframe style="width:100%;border:none;margin:0px;overflow:scroll;background-color:transparent;height: 100vh;" class="vieweroverlay-content" id="viewerframe" name ="viewerframe" src="' + response.link + '"></iframe>'));
            //postToViewer(response.link,JWT,"viewerframe");
            document.getElementById("myNav").style.width = "100%";
            $("body").css("overflow", "hidden");

        }
        else if (response.viewstudy == "success" && event.type == "contextmenu") {
            //postToViewer(response.link,JWT,"_blank");
            window.open(response.link);

        }
        else {
            showMessage("", "Unknown Error");
        }
    });

}

function submitorderhandler() {


$('#submitorder').on('click', function(e) {
testing = false;
var mrn = $("#patientid").val();
if (testing == true || $('#orderform').valid()) {
e.preventDefault(e);

$.ajax({
    url: '/HL7/submit_order',
    type: 'POST',
    dataType: 'json',
    data: $('#orderform').serialize(),
    context: $('#orderform'),
    beforeSend: function(e) {
    $("#spinner").css("display", "block");
    },
    success: function(data, textStatus, xhr) {

            response = parseMessages(data, true);
            if (!response.error || response.error == "OK") {

                if ($("#referrerrequest").html() != "") {
                    removefromQueue($("#referrerrequest").data("id"));
                }
                $("#accession_number").val(response.accession_number);
                $("#appointment_id").val(response.appointment_id);
                $("#status")[0].sumo.selectItem(response.ourstatus);
                date = $('#scheduled_procedure_step_start_date').val();
                if (isValidDate(date) && typeof scrollCalendar == 'function') {
                dateobject = splitDate(date);
                // goes to the specified date using the current date, no week or callback
                scrollCalendar(dateobject.year, dateobject.month, dateobject.day, "" ,"");
                }
            }
            else {
                // alert("error");
            }
    }
});
}
else {
//invalid form
}
});
}

// Beginning of Document Ready

$(function() {



    $("#studylist [name='changestudydate'][value='3650']").trigger("click");

	$("#delegator").on ("click", ".latestHL7", function(e) {

	e.preventDefault();
	e.stopImmediatePropagation();
	let activepatient = $(this).closest('.worklist');
	let uuid = $(activepatient).data("uuid");
	let accession_number = $(activepatient).data("accession");


		$.ajax({
		url: '/HL7/get_last_hl7',

			type: 'POST',
			data: {"uuid": uuid, "accession_number": accession_number },
			dataType: "json",
			beforeSend: function(e) {
			$("#spinner").css("display", "block");
			},
			success: function(data, textStatus, xhr) {  // json object of report, status if OK and error if no reports.
			response = parseMessages(data, true);

			if(!response.error) {
			loadreport(response.HL7, response.email);

			$("#delegator").css("display", "none");  // hide the content below
			}

			}
		});
	});
    

    $(".myuitabs").tabs();  // for uitabs, since the bootstrap ones do not seem to work that well.

    $("[data-dbsearch]").on("click", function(e) {

    let form = $(this).closest("form");
    e.preventDefault();

    if ($(this).data("dbsearch") == "searchorthanc") {

    getSearchParams();
    searchOrthanc();
    }
    else if ($(this).data("dbsearch") == "search_shared") {
    searchShared($('[name=searchform]').serialize());
    }

    else {

    var wrapper = form.parent().find(".listwrapper");

        $.ajax({

            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            type: "POST",
            url: form.data('action'),
            dataType: "json",
            data: $(this).closest("form").serialize(),
            context: $(this),
            beforeSend: function(e) {
                $("body").addClass("loading");
            },

        })
        .done(function(data, textStatus, jqXHR) {

            if (data.status) {
                showMessage("","No Matches");
            }
            else {
            wrapper.html(data.html);
            colorrows(wrapper.find(".worklist"));
            form.find(".RISpaginator").html(data.RISpaginator);

            if ($(this).attr("data-dbsearch") == "search_patients")   {

                getStudyCount($("#patientswrapper .patient"));
                getOrderCount($("#patientswrapper .patient"));
            }
            }
        });
    }
    });

    $(".clearsearchform").on('click', function(e) {

            e.preventDefault();
            let list = $(this).closest("form").parent().find(".listwrapper .worklist");
            list.css("display", "flex", "important");
            $(".searchparam").val("");
            colorrows(list);

    });

    $("body").on ("click", ".showpatienthistory",  function(e) {

        e.preventDefault();
        e.stopImmediatePropagation();
        if ($(this).closest(".worklist").next().attr("id") != "historydiv") {
        var activepatient = $(this).closest('.worklist');
        $.ajax({

            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            url: '/patients/history',
            type: 'POST',
            dataType: 'html',
            data: {"id" : activepatient.data('mrn')}, // for later maybe, one will be set for existing patient.
            beforeSend: function(e) {
            $("#spinner").css("display", "block");
            },
        })
        .done(function(data, textStatus, xhr) {

            if (isJsonString(data)) {
                parseMessages(data, true);
            }
            else {
            $("#historydiv").remove();  // remove it if is is already there.
            showMessage("", '<div id="historydiv" style="display:contents;">' + data + '</div>')
            // $(activepatient).after('<div id="historydiv" style="display:contents;">' + data + '</div>');
            colorrows($("#historydiv .worklist"));
            //$('#historydiv').append('<div class = "form-group shadedform col-sm-12" style="text-align:center;"><button type="button" id="closeorderoverlay" class="uibuttonsmallred">Close</button></div>');  // ajax does not come with the close button
    // 		$('#closeorderoverlay').on('click', function() {
    // 			$("#historydiv").remove();
    // 			$("html, body").animate({scrollTop: 0}, 500);
    // 		});
            }
        })
        }
        else {
        $("#historydiv").remove();
        }

    });

    //  Kind of a long function to retrieve the sharelist, either from the SESSION (current set), vs. making an API call everytime.
    //  Submits the uuid, the doctor identifier and the sharenote.  The sharing doctor is stored in the SESSION as doctor_id.  The receiver is the identifier.

    $("#delegator").on("click", ".share", function(e) {

        e.preventDefault();
        e.stopImmediatePropagation();

        var studyrow = $(this).closest(".worklist");
        if (studyrow.next().hasClass("shareform")) {
             studyrow.next().remove();
        }

        else {

            let uuid = studyrow.data("uuid");
            let accession_number = studyrow.data("accession");
            $.ajax({

            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            type: "POST",
            url: "/referrers/sharelist",
            dataType: "html",
            data: {},
            beforeSend: function(e) {
                $("body").addClass("loading");
            },
            })
            .done(function(data, textStatus, jqXHR) {
                studyrow.after('<form style = "width:100%;text-align:center;margin:auto;" class = "shareform"><input type="hidden" name="uuid" value="' + uuid + '"><input type="hidden" name="accession_number" value="' + accession_number + '">' + data +  '<textarea style="width:100%;margin:0px 20px 0px 20px;" name = "sharenote"></textarea><br><button class = "uibuttonsmallred" type = "submit">Share</button></form>');
                $("#sharenote").focus();
                var form = studyrow.next();
                form.validate();
                form.on("submit", function(e) {
                    if (form.valid()) {
                    e.preventDefault();

                    $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    type: "POST",
                    url: "/referrers/share",
                    dataType: "json",
                    data: $(this).serialize(),
                    beforeSend: function(e) {
                        $("body").addClass("loading");
                    },
                    })
                    .done(function(data, textStatus, jqXHR) {
                        parseMessages(data, true);
                    });
                }
                    else {
                    alert("not valid");
                }
                });
            })
        }
    });
    
     document.getElementsByTagName("html")[0].style.visibility = "visible";

});


$("#togglelegend").on("click", function(e) {
	$('#studieslegend').toggle();
});

function attachtoolstip(e) { // takes a wrapper or a $ object with data-toggle = "tooltip" and data-placement = "top" set

    if (e instanceof jQuery) {

        $(this).tooltip({
        content: function () {
            return this.getAttribute("title");
        },
        });

    }
    else {

    $(e +' [data-toggle = "tooltip"]').each(function(i) {
        $(this).tooltip({
        content: function () {
            return this.getAttribute("title");
        },
        });
    });
    }

}

attachtoolstip('#orderswrapper, .worklistheader, #studieswrapper, #patientswrapper, #worklistwrapper');

$('[data-toggle="popover"]').popover({
  trigger: "hover",
  container: "body",
  delay: { "show": 300, "hide": 100 }

});


</script>

<script nonce = "<?php echo e(csp_nonce()); ?>">

// Scripts for New Study Browser

// function template is on the actual page where the template is rendered.
var routelist;
var apiurl;
var query;
var studyheader = '<div class="row divtable widemedia worklistheader"><div class="col-sm-3 nopadding"><div class="col-sm-6 padding" data-sort-param="data-name" data-sort-order="up"><span>Name / View</span></div><div class="col-sm-6 padding" data-sort-param="data-age" data-sort-order="up"><span>Age/DOB/Reports</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-2 padding" data-sort-param="data-sex" data-sort-order="up"><span>Sex</span></div><div class="col-sm-5 padding" data-sort-param="data-mrn" data-sort-order="up"><span>MRN</span></div><div class="col-sm-5 padding" data-sort-param="data-accession" data-sort-order="up"><span>Accession</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-8 padding" data-sort-param="data-description" data-sort-order="up"><span>Description</span></div><div class="col-sm-2 padding" data-sort-param="data-modality" data-sort-order="up"><span>Type</span></div><div class="col-sm-2 padding" data-sort-param="data-images" data-sort-order="up"><span>#</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-4 padding" data-sort-param="data-harvested" data-sort-order="up"><span>History</span></div><div class="col-sm-8 padding" data-sort-param="data-studydate" data-sort-order="down"><span>Study Date</span></div></div></div>';

$("#itemsperpage").val("10");
$("#sortparam").val("StudyDate");
$("#reverse").val("1");

$("#studieswrapper").on("click", ".create-dicom", function(e) {

if ($("#ajaxdiv").length == 0) {
var parent = $(this).closest(".worklist");

    $.ajax({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    url: '/PACS/create_dicom',
    type: 'POST',
    dataType: 'html',
    data: {parent:parent.data("uuid")},
    beforeSend: function(e) {
        $("body").addClass("loading");
    },
    })
    .done(function(data, textStatus, jqXHR) {

        parent.after('<div id ="ajaxdiv">' + data + '</div>');
        initLoader();
    })
    }
    else {
        $("#ajaxdiv").remove();

    }
});

function getSearchParams() {

	// For Reference, these are embedded in the search form data-orthanc attribute, others are for RIS.
	var map = {

	"data-name": "PatientName",
	"data-dob": "PatientBirthDate",
	"data-sex": "PatientSex",
	"data-mrn": "PatientID",
	"data-accession": "AccessionNumber",
	"data-description": "StudyDescription",
	"data-modality": "StudyDescription",
	"data-studydate": "StudyDate",
	"data-referringphysician" : "ReferringPhysicianName",
	"data-modality" : "Modality",
	"data-institution" : "InstitutionName",
	"data-otherpatientids": "RETIRED_OtherPatientIDs" //(0010,1000) retired tag

	}


	var subquery = {};
	var tags  = {};

	params = $("[name=searchform]").find(".searchparam");
	$.each(params, function( index, value ) {

		let currentvalue = $(this).val();
		//if (currentvalue == "") currentvalue = "*";
		if ($(this).data('orthanc') == "PatientName" && currentvalue != "") subquery[$(this).data('orthanc')] = "*" + currentvalue + "*";
		else if ($(this).data('orthanc') == "PatientID" && currentvalue != "") subquery[$(this).data('orthanc')] = currentvalue;
		else if ($(this).data('orthanc') == "PatientBirthDate" && currentvalue != "") subquery[$(this).data('orthanc')] = currentvalue.replace(/-/g,"");
		else if ($(this).data('orthanc') == "PatientSex" && currentvalue != "") subquery[$(this).data('orthanc')] = currentvalue;
		else if ($(this).data('orthanc') == "AccessionNumber" && currentvalue != "") subquery[$(this).data('orthanc')] = currentvalue;
		else if ($(this).data('orthanc') == "StudyDescription" && currentvalue != "") subquery[$(this).data('orthanc')] = "*" + currentvalue + "*";
		else if ($(this).data('orthanc') == "StudyDate" && currentvalue != "") subquery[$(this).data('orthanc')] = currentvalue.replace(/-/g,"");
		else if ($(this).data('orthanc') == "ReferringPhysicianName" && currentvalue != "") subquery[$(this).data('orthanc')] = "*" + currentvalue + "*";
		else if ($(this).data('orthanc') == "Modality" && currentvalue != "") tags["0008,0060"] = currentvalue;  // tag for Modality
		else if ($(this).data('orthanc') == "InstitutionName" && currentvalue != "") subquery[$(this).data('orthanc')] = currentvalue;
		else if ($(this).data('orthanc') == "0010,1000" && currentvalue != "" ) subquery[$(this).data('orthanc')] = currentvalue;
		else if ($(this).data('orthanc') == "0010,21b0" && currentvalue != "") subquery[$(this).data('orthanc')] = currentvalue;
		else if ($(this).data('orthanc') == "reportstatus" && currentvalue != "") tags["0008,103e"] = currentvalue;

	});
	if (!subquery.hasOwnProperty("StudyDate")) {
		subquery.StudyDate = $("[name=begindate]").val().replace(/-/g, "") + "-" +$("[name=enddate]").val().replace(/-/g, "");
	}

	var fullquery = {};
	fullquery.Query = subquery;
	fullquery.Level = "Study"
	fullquery.Expand = true;
	fullquery.Normalize = false;
	fullquery.MetaData = {};
	//fullquery.Tags = {"0008,0005":"ISO_IR 100","0008,1032":{"0008,0100":"IMG131"},"0008,0096":{"0040,1101":{"0008,0100":"0001","0008,0102":"L"}}};
	//fullquery.MetaData.LastUpdate = "*";
	fullquery.pagenumber = 1;
	fullquery.itemsperpage = parseInt($("#itemsperpage").val());
	fullquery.sortparam = $("#sortparam").val();
	fullquery.reverse = parseInt($("#reverse").val());
	fullquery.widget = 1;
	fullquery.Tags = tags;
	query = fullquery; // store it for use with the pagination thing.
	return fullquery;
}

function getStudyRow(study, rowno, template) {

//console.log(study);
study.rowno = rowno;
study.sex = (study.PatientMainDicomTags.PatientSex != undefined)?study.PatientMainDicomTags.PatientSex:"?";
study.indication = (study.indication != undefined)?study.indication:"None";
study.stable = (study.IsStable)?"Stable":"Unstable";
study.dob = (study.PatientMainDicomTags.PatientBirthDate != undefined)?study.PatientMainDicomTags.PatientBirthDate.toString().replace(/(\d{4})(\d{2})(\d{2})/g, '$1-$2-$3'):"No DOB";
if (isValidDate(study.dob)) {
study.age = moment().diff(moment(study.dob, "YYYY-MM-DD"), 'years') + ' y';
}
else {
study.age = "NA";
study.dob = "No DOB";
}
study.studydate = (study.MainDicomTags.StudyDate != undefined)?study.MainDicomTags.StudyDate.toString().replace(/(\d{4})(\d{2})(\d{2})/g, '$1-$2-$3'):"No Date";
study.modalities = study.modalities.join(',');
return template(study);

}

// Iterates through the result set and gets the HTML for the whole set and then fills up the wrapper.
function showStudies(studies,offset) {
let html = '';
studies.forEach(function (study, i) {
   html += getStudyRow(study, i+1+offset, template);
});
$("#studieswrapper").html(html);

}

function renderData(data) {

	widget = data[0].widget;  // get the Widget from the response as HTML, 1st element in the returned array.
	offset = data[0].offset;
	data.splice(0, 1);  // delete the first elements that are not part of the study dataset, probably could return fewer params if the widget is returned and the query is saved in JS or LocalStorage
	//console.log(data);
	showStudies(data,offset);
	$("#widget").html(widget); // put the widget in the div above the study table header.

}

function searchOrthanc() {

$.ajax({
headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
},
url: '/studies/page',
type: 'POST',
dataType: 'json',
contentType: 'application/json; charset=utf-8',  // post JSON
data: JSON.stringify(query),
})
.done(function(data, textStatus, jqXHR) {

    if (data == null) {
    alert("Emtpy Response from Server");
    }
    else if (data.error) {
    alert(data.error);
    }
    else if (data.curl_error) {
    alert(data.curl_error);
    }
    else {

	widget = data[0].widget;  // get the Widget from the response as HTML, 1st element in the returned array.
	offset = data[0].offset;
	demoauth = "PatientID:  " + data[0].PatientID + "<br>  ReferringPhysicianName:  " + data[0].ReferringPhysicianName;
	// showMessage("Demo Authorization", demoauth);
	console.log(demoauth);
	data.splice(0, 1);  // delete the first elements that are not part of the study dataset, probably could return fewer params if the widget is returned and the query is saved in JS or LocalStorage
	//console.log(data);
	showStudies(data,offset);
	$("#widget").html(widget); // put the widget in the div above the study table header.
	//colorrows($("#studieswrapper .worklist"));  // Color the Rows, pretty, see function below this one.
	/*  Left over from WebApp, probably don't want that, although StudyCount by MRN might be nice.
	if ($("#patientswrapper").length != 0 ){
	getStudyCount($("#patientswrapper .patient"));
	getOrderCount($("#patientswrapper .patient"));
	}
	*/
	}
})
.fail(function( jqXHR, textStatus, errorThrown) {
})
.always(function() {
});
}


$("#studycountselect select").on("change", function(e) {
getSearchParams();
searchOrthanc();
});

$("[name=changestudydate]").on("change", function(e) {

e.preventDefault();

let selected = $(".changestudydate:checked").val();
let end = $(this).closest(".startenddate").find(".enddate");
let begin = $(this).closest(".startenddate").find(".begindate");
if (selected != 0 && selected != "NOW" ) {
end.val(moment().format('YYYY-MM-DD'));
//endforquery = moment().format('YYYYMMDD');
begin.val(moment().subtract(selected,'d').format('YYYY-MM-DD'));
//beginforquery = moment().subtract(selected,'d').format('YYYYMMDD');
//query.Query.StudyDate = beginforquery + "-";
}
else if (selected == "NOW") {
begin.val(moment().format('YYYY-MM-DD'));
end.val(moment().format('YYYY-MM-DD'));
}
else {
	//query.Query.StudyDate = "";
	begin.val("");
	end.val("");
}
let database = $(this).closest('[data-searchdb]').data("searchdb");

if (database == "searchorthanc") {

getSearchParams();
searchOrthanc();
}
else if (database == "search_shared") {
searchShared($('[name=searchform]').serialize());
}


});

$("body").on("click", ".paginator a", function(e) {

	e.preventDefault();
	let clickedparent = $(this).closest(".paginator");
	//let wrapperselector = clickedparent.data("wrapper");
	query.pagenumber = parseInt($(this).data("page"));
	searchOrthanc();

});

// this one is setup different because the handler can't be attached to the body tag.

$("#studieswrapper, #patientswrapper, #orderswrapper, #requestswrapper").on("click", ".paginator a", function(e) {

	e.stopImmediatePropagation();  // prevent the function above from executing.
	e.preventDefault();
	let parent = $(this).closest("#patientdiv").prev();
	let mrn = parent.data("mrn");
	loadAllPatientDiv(parent,$(this).data("page"));
});

function loadAllPatientDiv(worklist,page) {

	let mrn = worklist.data("mrn");
	$.ajax({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
		//let mrn = worklist.data("mrn");
    	url: '/OrthancDev/load_all_studies',
    	type: 'POST',
    	dataType: 'json',
    	data: {"data-mrn" : mrn, "page": page}, // for later maybe, one will be set for existing patient.
    	beforeSend: function(e) {
            $("body").addClass("loading");
        },
    	success: function(data, textStatus, xhr) {
            $("body").removeClass("loading");
            if (data == null) {
            alert("Emtpy Response from Server");
            }
            else if (data.error) {
            	showMessage("",data.error);
            }
            else if (data.curl_error) {
            alert(data.curl_error);
            }
			else {
            $("#patientdiv").remove();  // remove it if is is already there.
			widget = data[0].widget;  // get the Widget from the response as HTML, 1st element in the returned array.
			offset = data[0].offset;
			data.splice(0, 1);  // delete the first elements that are not part of the study dataset, probably could return fewer params if the widget is returned and the query is saved in JS or LocalStorage
			//console.log(data);
			let html = '';
			data.forEach(function (study, i) {
	   			html += getStudyRow(study, i+1+offset, template_old);
			});
			header = "";
			if ($("#patientlist")) header = studyheader;
    		$(worklist).after('<div id="patientdiv" class = "listwrapper">' + header + widget + html + '</div>');
    		colorrows($("#patientdiv .worklist"));
    //query = '{"Query":{"PatientID":"'+mrn+ '"},"Level":"Study","Expand":true,"Normalize":false,"MetaData":{},"pagenumber":' + page + ',"itemsperpage":10,"sortparam":"StudyDate","reverse":1,"widget":1,"Local":{}}';
    //query = JSON.parse(query);

			}
    	},
    	error: function(xhr, textStatus, errorThrown) {

    	}
    });


}



function fetchstudy(uuid, id) {

    $.ajax({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    url: '/Studies/fetch_study',
    type: 'POST',
    dataType: 'json',
    data: {"uuid":uuid, "id":id},
    complete: function(xhr, textStatus) {
        $("body").removeClass("loading");
    },
    success: function(data, textStatus, xhr) {
        showMessage("",data.message);

    },
    error: function(xhr, textStatus, errorThrown) {

    }
    });
}

$("body").on("click", ".showselect", function(e) {

    e.preventDefault();
    if (!$(this).next().is(':empty')) {
     $(this).next().empty();
     $(this).next().hide();
    }
    else {
        get_routes_list($(this).next());
    }
});

function get_routes_list(selectlist) {

	$.ajax({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
	url: 'get_modalities',
	type: 'POST',
	dataType: 'html',
	data: {},
	beforeSend: function(e) {
		$("body").addClass("loading");
	},
	success: function(data, textStatus, xhr) {

		selectlist.html(data);
		selectlist.show();
	}
	});

	}

$("body").on("change", ".route_select", function(e) {

		e.preventDefault();
		let id = $(this).val();
		if (id != "") {
		let uuid = $(this).closest(".worklist").data("uuid");
		fetchstudy(uuid, id);
		}
});

$("body").on ("click", ".allstudies_orthanc", function(e) {

	e.preventDefault();
    e.stopImmediatePropagation();
    let activepatient = $(this).closest('.worklist');
    let element = activepatient.next();
    if (element.attr("id") != "patientdiv") {
    loadAllPatientDiv(activepatient,null);
    }
    else element.remove();
});


// ORTHANC SCRIPTS FOR VIEWER, will not attach to document for some reason ?, using body


$("body").on("click", '.viewstudy', function(e) {

    e.preventDefault();
    e.stopImmediatePropagation();
    logViewStudy($(this).closest(".worklist"), e);
});

$("body").on("click, contextmenu", '.viewstudy', function(e) {

    e.preventDefault();
    e.stopImmediatePropagation();
    logViewStudy($(this).closest(".worklist"), e);
});

$("body").on("click", '.closebtn', function(e) {
    e.preventDefault();
    $("#dynamiciframe").children().remove();
    document.getElementById("myNav").style.width = "0px";
    $("body").css("overflow", "visible");
});
	
	


$("#orthanc_host").on("change", function(e) {
    $(this).closest("form").submit();
});


$('#contactform').on('submit', function(e) {

e.preventDefault();
$.ajax({
        type: "POST",
        url: '/sendmail',
        dataType: "json",
        data: $(this).serialize(),
        beforeSend: function(e) {
        },
    })
    .done(function(data, textStatus, jqXHR) {
        alert(data.message);
    });
});



// ORTHANC SCRIPTS DOWNLOAD SCRIPTS

function downloadstudy_orthanc(type, clicked)  {

$("body").addClass("loading");

fetch('/OrthancDev/downloadStudyUUID', {

    body: JSON.stringify({command: type, "uuid": clicked.data( "uuid")}),
    method: 'POST',
    headers: {
        'Content-Type': 'application/json; charset=utf-8',
        "_token": "<?php echo e(csrf_token()); ?>",
        'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>"

    },
})
.then(response => response.blob())
.then(response => {
    $("body").removeClass("loading");
    const blob = new Blob([response], {type: 'application/zip'});
    const downloadUrl = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = downloadUrl;
    a.download = clicked.data("name") + ".zip";
    document.body.appendChild(a);
    a.click();
})

}

$("body").on("click", ".downloadiso_orthanc",  function(e) {

    e.preventDefault();
    downloadstudy_orthanc("iso", $(this).closest(".worklist"));
});

$("body").on("click", ".downloadzip_orthanc", function(e) {
    e.preventDefault();
    downloadstudy_orthanc("zip", $(this).closest(".worklist"));
});


    // display an existing report in the viewer, relies on the reports for a study to have the report object attached to it, which is done when the reports are loaded, all in JS

    function loadreport(report, email) {

        //console.log(activestudytags);
        $("#teleRadDivOverlayWrapper").show();
        $("#APImonitor").css("display","block");
        $("#apiresults").html(report);
        $("#viewersend").removeClass();
        if ( email == "" ) {
           $("#viewersend").attr("disabled", true);
           $("#viewersend").addClass("btn-danger btn-sm");
        }
        else {
            $("#viewersend").attr("disabled", false);
            $("#viewersend").addClass("btn-primary btn-sm");
        }

    }
    
        $( "#teleradClose" ).on( "click", function(e) {
    	e.preventDefault();
    	$("#teleRadDivOverlay").html("");
    	$("#apiresults").html("");
    	$("#APImonitor").css("display","none");
  		$("#teleRadDivOverlayWrapper").css("display", "none");
  		$("#delegator").css("display", "block");  // show the content below the overlay again
	});
	
function dynamicPostForm (path, params, target) {

    method = "post";
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    if (target) {
    	form.setAttribute("target", "_blank");
    }
    for(var key in params) {

        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
         }
    }

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "_token");
    hiddenField.setAttribute("value", '<?php echo e(csrf_token()); ?>');
    form.appendChild(hiddenField);
    document.body.appendChild(form);
    form.submit();
    $(form).remove();
}
	
$("body").on("click", ".wkdownload", function(e) {

		dynamicPostForm ('/Utilities/getPDFfromBody', {markup: $($(this).data("content"))[0].outerHTML, extra: $(this).data("css"), filename:  $(this).data("filename"), disposition: "attachment"}, false);

});

$("body").on("click", ".wknewtab", function(e) {

		dynamicPostForm ('/Utilities/getPDFfromBody', {markup: $($(this).data("content"))[0].outerHTML, extra: $(this).data("css"), filename:  $(this).data("filename"), disposition: "inline"}, true);
});

    $("#emailreport").on("submit", function(e) {

        e.preventDefault();
        if (isEmail($("#emailreport_to").val())) {
        	var markup = $("#reportnoheader").html();
        	emailreport(markup,$("#emailreport_to").val());
        }
        else {
        showMessage("Invalid e-mail", "Enter a valid e-mail address");
        }

    });

    function emailreport(markup,email) {
    
        if ($("#report_name").length & $("#report_mrn").length) {
        data = {"markup": markup, "email": email, "name": $("#report_name").html(),"mrn":$("#report_mrn").html()};
        }
        else {
        data = {"markup": markup, "email": email};
        }
        $.ajax({

            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            url: 'emailReport',
            type: 'post',
            dataType: 'json',
            data: data,
            complete: function(xhr, textStatus) {

            },
            success: function(data, textStatus, xhr) {
                if (data.status == "true") {
                    alert("report sent");
                }
                else if (data.status == "false") {
                    alert("There was an error sending the report");
                }
            }
        });
    }
    
    function isEmail(email) {
      var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,6})+$/;
      return regex.test(email);
    }
    
    function template_old(study) {

	let html = '<!-- BEGIN STUDY --><div class="row divtable worklist" data-uuid="'+study.ID+'" data-studyinstanceuid="'+study.MainDicomTags.StudyInstanceUID+'" data-name="'+study.PatientMainDicomTags.PatientName+'" data-dob="'+study.dob+'" data-age="'+study.age+'" data-sex="'+study.sex+'" data-mrn="'+study.PatientMainDicomTags.PatientID+'" data-accession="'+study.MainDicomTags.AccessionNumber +'" data-description="'+study.MainDicomTags.StudyDescription+'" data-modality="'+study.modalities+'" data-images="'+study.imagecount+'" data-studydate="'+study.studydate+'" data-orthancstatus="'+study.stable+'" data-reportstatus="'+study.reportstatus+'" data-indication="'+study.indication + '" data-referring_physician="'+study.MainDicomTags.ReferringPhysicianName+'" data-billing_status="'+study.billingingstatus+'"><div class="col-sm-3 nopadding"><div class="col-sm-6"><span class="rowcounter">' + study.rowno + '</span><span class="narrowmedia">View: </span><a class="viewstudy" href="#" target="_blank"><img class="uiicons" src="/images/view_images.png" title="View"></a><span style="max-width: 50px;display: inline-block;text-overflow: ellipsis;overflow: hidden;">'+study.MainDicomTags.ReferringPhysicianName+'</span> <br><span class="narrowmedia">Name: </span><span data-toggle="tooltip" data-placement="top" title="' +study.PatientMainDicomTags.PatientName+ ', Doctor:  ' +study.MainDicomTags.ReferringPhysicianName+'">'+study.PatientMainDicomTags.PatientName+'</span></div><div class="col-sm-6"><span class="narrowmedia">DOB / Age: </span> <a href="#"><img class="latestHL7 uiiconslarge" src="/images/report.png" title="Reports"></a><span data-toggle="tooltip" data-placement="top" title="DOB / Age">' + study.dob + ' / ' + study.age + '</span> <div class="reportstatus">'+study.reportstatus+'</div></div></div><div class="col-sm-3 nopadding"><div class="col-sm-2"><span class="narrowmedia">Sex: </span><i class="showselect far fa-paper-plane"></i><select class="route_select"></select><span>&nbsp;'+study.sex+'</span> </div><div class="col-sm-5"><span class="narrowmedia">Download:&nbsp;&nbsp;</span><a href="#"><span class="downloadiso_orthanc uibuttonsmallred">"DCM"</span></a><br><span class="narrowmedia">MRN: </span><span data-toggle="tooltip" data-placement="top" title="'+study.PatientMainDicomTags.PatientID+'">&nbsp;'+study.PatientMainDicomTags.PatientID+'</span></div><div class="col-sm-5"><span class="narrowmedia">Download:&nbsp;&nbsp;</span><a href="#"><span class="downloadzip_orthanc uibuttonsmallred">"ZIP"</span></a><br><span class="narrowmedia">Accession: </span><span data-toggle="tooltip" data-placement="top" title="'+study.MainDicomTags.AccessionNumber+ '">&nbsp;'+study.MainDicomTags.AccessionNumber+'</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-8"><span class="narrowmedia">Description:</span><span>'+study.MainDicomTags.StudyDescription+'</span> </div><div class="col-sm-2"><span class="narrowmedia">Modality: </span><span>'+study.modalities+'</span></div><div class="col-sm-2"><span class="narrowmedia">Images: </span><span>'+study.imagecount+'</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-4"><span class="narrowmedia">History: </span><span data-toggle="tooltip" data-placement="top" title="'+study.indication+'" style="width: auto;white-space: nowrap;left: 0px;bottom: 0px;text-overflow: ellipsis;overflow: hidden;display: block;">'+study.indication+'</span> </div><div class="col-sm-8" data-toggle="tooltip" data-placement="top" title="'+study.studydate+'"><span class="narrowmedia">Study Date: </span><span>'+study.studydate+'</span><br><span class="narrowmedia">Stable: </span><span>'+study.stable+'</span> </div></div></div><!-- END STUDY -->';
return html;

}

    $("body").on("change", ".editableform input, .editableform textarea, .editableform select", function(e) {
        
        if ($(this).attr("type") == "checkbox" || $(this).attr("type") == "radio") {
            propertyvalue = $(this).prop("checked");
        }
        else {
            propertyvalue = $(this).val();
        }
        form = $(this).closest("form");
        params = {};
        params.key = form.data("key");
        params.keyvalue = form.data("keyvalue");
        params.propertyname = $(this).attr("name");
        params.propertyvalue = propertyvalue;
        
        $.ajax({
        
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            type: "POST",
            url: form.data("action"),
            dataType: "json",
            data: params,
            context: $(this),
            beforeSend: function(e) {
                $("body").addClass("loading");
            },
        })
        .done(function(data, textStatus, jqXHR) {
            if (data.status == 1) {  // of data.data is not empty then by gets whatever the controller returns, keep in JS and update upon closing the form.
						this.before('<span style="color:green !important;z-index:2;background:white !important;font-size:14px !important;position:absolute;top:0px;" class="control-label fas fa-check edited_green"></span>');
			}
			else if (data.status == "error") {
			
                if (this.attr("type") == "radio" || this.attr("type") == "checkbox" ) {
                this.prop("checked",propertyvalue);
                }
                else if (this.prop("tagName") ==  "SELECT") {
                alert("select list error" + propertyvalue);
                }
                else {
                this.val(propertyvalue);
                }
                this.before('<span style="color:red !important;z-index:2;background:white !important;" class="edited_red">Error</span>');
			}
			//console.log(this);
			setTimeout(function () {$(".edited_green, .edited_red").remove();}, 2000);
        });
    
    });
</script>
<?php /**PATH /nginx-home/laravel/resources/views/components/myjs.blade.php ENDPATH**/ ?>