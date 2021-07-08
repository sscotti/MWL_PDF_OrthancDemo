<?php
print_r($data);


?>

<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8" />
	<title>Dicom Study Uploader</title>
	<meta name="generator" content="BBEdit 12.6" />
	<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- 	<link rel="stylesheet" href="/bower/dropzone/dist/dropzone.css" /> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.6/dropzone.min.css" integrity="sha512-jU/7UFiaW5UBGODEopEqnbIAHOI8fO6T99m7Tsmqs2gkdujByJfkCbbfPSN4Wlqlb9TGnsuC0YgUgWkRBK7B9A==" crossorigin="anonymous" />
<!-- 	<link rel="stylesheet" href="/bower//dropzone/dist/basic.css" /> -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.6/basic.min.css" integrity="sha512-MeagJSJBgWB9n+Sggsr/vKMRFJWs+OUphiDV7TJiYu+TNQD9RtVJaPDYP8hA/PAjwRnkdvU+NsTncYTKlltgiw==" crossorigin="anonymous" />
<!-- 	<script src="/bower/jquery/dist/jquery.min.js"></script> -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	<!--
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/black-tie/theme.min.css" integrity="sha512-pEDe6i0LP1yehVI1LerbiD+OUvitp8sGZEStFS/8jhVM9wotaPAIRds41SYdEYUPC2Hym3Xqh9sn3l0nuX7zpw==" crossorigin="anonymous" />
 	-->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/dot-luv/jquery-ui.min.css" integrity="sha512-hGYkKzSMWaKWJ0MGIVy3h8Dk1VlEOQhR4NFB18jH3KCSzOhH5Zg6T54f526ne8eZVTlnFMcieY7Zlad9glJ9Mw==" crossorigin="anonymous" />
<!-- 	<script src="/bower//dropzone/dist/dropzone.js"></script> -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.7.6/dropzone.min.js" integrity="sha512-s9Ud0IV97Ilh2e46hhMIez0TyGyBrBcHS+6duvJnmAxyIBwinHEVYKLLWIwmQi3lsQPA7CL+YMtOAFgeVNt6+w==" crossorigin="anonymous"></script>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BmbxuPwQa2lc/FVzBcNJ7UAyJxM6wuqIj61tLrc4wSX0szH/Ev+nYRRuWlolflfl" crossorigin="anonymous">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>


	<style>

		body {
			overflow: scroll;
			/* Show scrollbars */
		}

		#mydropzone {
			position: relative;
			cursor: pointer;
			height: max-content;
			height: 100px;
			height: max-content;
			height: max-content !important;
			min-height: 50px;
		}

		#dropzonetext {
			pointer-events: none;
		}

		.dz-success-mark,
		.dz-error-mark {
			display: none;
		}

		#dicomuploader {
			text-align: center;
			background: white;
		}

		#uploadinstructions {
			text-align: left;
			margin: 0px 40px;
		}

		.infowrapper {
			border: 1px solid black;
			font-size: 12px;
			font-weight: bold;
		}

		.infowrapper span {
			width: 60px;
			margin: 0px 5px;
		}

		#tabswrapper {
			font-size: 14px;
			font-weight: bold;
		}

		.centertabs {
			width: max-content;
			margin: auto !important;
		}

		.pageheader {
			width: 100%;
			background: black;
			color: white;
			text-align: center;
			margin: auto;
			padding: 2px;
			font-size: 14px;
		}

		.sectionheader {
			width: max-content;
			font-size: 12px;
		}

		.form-switch .form-check-input {
			/* background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e"); */
			background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" height="4" width="4"><circle cx="2" cy="2" r="2" stroke="black" stroke-width="1" fill="white" /></svg>') !important;
		}

		#edited_tag_inputs {
			display: none;
		}

		.ui-tabs-panel {
			/* 		height:100vh; */
		}
		.picker {
		    color:black;
		}

	</style>

</head>
<body>
	<link rel="stylesheet" href="/orthanc_upload_support/style.css" type="text/css">
	<h4 class = "pageheader">
		Dicom Study Uploader (.zip files or entire folder)
	</h4>
	<div class="loadcontent" id = "dicomuploader">

	<h4>
	There are multiple options when uploading
	</h4>
	<div class = "infowrapper">
	<p>Normal preserves DICOM Tags, Anonymize strips out identifying data.  Altering the MRN is not an option if anonymization is chosen.</p>
	<div><span>Normal</span><span>Anonymize</span></div>
	<div class="form-check form-switch">
	<label class="form-check-label" for="anonymize">
	<input class="form-check-input" type="checkbox" id="anonymize" /></label>
	</div>
	</div>

	<div class = "infowrapper" id ="internal_outside_wrapper">
	<p>Alter Tags allows editing of some DICOM tags.</p>
	<p>If Alter Tags is chosen, then the edited tags are overwritten and the old values are saved in "0010,1000" => 'RETIRED_OtherPatientIDs' as MRN|AccessionNumber|InstitutionName.  This is only allowed for now if uploading a .zip to this app (tab 1) or when uploading a folder. </p>
	<div><span>No changes</span><span>Alter Tags</span></div>
	<div class="form-check form-switch">
	<label class="form-check-label" for="altertags">
	<input class="form-check-input" type="checkbox" id="altertags" /></label>
	<div id = "edited_tag_inputs">
	<input type="hidden" name = "taglistname[]" value="PatientID">
	<label for="PatientID">MRN:
	<input type="text" id = "PatientID" name = "taglistvalue[]" Placeholder="Internal MRN" value="test">
	</label>
	<input type="hidden" name = "taglistname[]" value="AccessionNumber">
	<label for="AccessionNumber">Accession:
	<input type="text" id = "AccessionNumber" name = "taglistvalue[]" Placeholder="Internal Accession" value="test">
	</label>
	<input type="hidden" name = "taglistname[]" value="InstitutionName">
	<label for="InstitutionName">Institution:
	<input type="text" id = "InstitutionName" name = "taglistvalue[]" Placeholder="Internal Institution" value="test">
	</label>
	</div>
	</div>
	</div>

	<div class = "infowrapper" id = "options_wrapper">
	<p>Finally, there are several methods for uploading.</p>
	<ol>
	<li>Upload a .zip file and preprocess that before sending to the PACS server.  All of the options are available in that case.</li>
	<li>Upload a .zip file directly to the PACS server.  This method currently does not allow for editing the tags,, but probably the fastest.</li>
	<li>Upload an entire folder and preprocess that before sending to the PACS server.  All options are again available in that case.</li>
	</ol>
	</div>

	<div id = "tabswrapper">

		<ul class="centertabs">
				<li>
				<a href="#preprocess_zip">Pre-Process .zip</a>
				 </li>
				<li>
				<a href="#zip_to_pacs">.zip to PACS</a>
				</li>
				<li>
				<a href="#preprocess_folder">Upload Folder</a>
				</li>
				<li>
				<a href="#resultswrapper">Upload Summary</a>
				</li>
		</ul>

		<div id="preprocess_zip">Pre-Process .zip</div>

		<div id="zip_to_pacs">.zip to PACS</div>

		<div id="preprocess_folder">
			Upload Folder
			<ol id ="uploadinstructions">
			<li>Check that you have a complete study (unpacked / unzipped ) in a folder on a CD or on your computer.</li>
			<li>Typically, there will be several folders with files there that end in .dcm, although they may not have a file extension, and there may be other files as well (e.g. .exe, .pdf, etc.).</li>
			<li>Using the button below, select the folder containing the files you need to upload, and then the files will upload.  If there is an error, a message will be displayed.  It typically takes a minute or two for the study to be available on the server.</li>
			<li>The entire folder should upload, including any contained subfolders.</li>
			<li>Note that the uploader will just upload DICOM files, with or without the .dcm extension.  Those are the images for your study or studies.  You are able to upload multiple studies at once.  A brief summary will be displayed below.</li>
			</ol>

			<h3>
				Choose Folder
			</h3>
			<div class="picker">
				<input type="file" id="picker" name="fileList" webkitdirectory multiple data-timestamp = "">
			</div>

			<div>
				Percentage Processed
			</div>
			<span id="box">0%</span>
			<h5>
				Percentage Uploaded
			</h5>
			<div id="myProgress">
				<div id="myBar"></div>
			</div>
			<h5 id = "displayafterinit">
				Sending File . . <span id = "progress_text"></span>
				<div><img id="loader" src="/orthanc_upload_support/loader.gif"></div>
			</h5>
			<h3>
				Files Uploaded
			</h3>
			<div id="preprocess_notice"></div>
			<div id = "statusheader"><span>File Name</span><span>File Size</span><span>MIME Type</span><span>Status</span></div>
			<ol id="listing"></ol>
		</div>
		<div id="resultswrapper">
		Upload Summary
		<div id="uploadresults"></div>
		</div>
	</div>

	<div id ="dropzonewrapper" >
	<div id="mydropzone" style="width:200px;height:200px;margin:auto;border:1px black dashed;">
    <div id = "dropzonetext">
    Drag Zip or Choose Zip from here.
    </div>
    </div>
    <button id ="clearzip">Clear</button>
    </div>

</div>

<!-- The Modal -->
<div class="modal fade hide" id="uploaderModal" data-keyboard="true" data-backdrop="true" tabindex='-1'>

  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title"></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <!-- Modal body -->
      <div class="modal-body"></div>
      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="uibuttonsmallred" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>

function showuploaderModal (title, body) {

	$("#uploaderModal .modal-body").html(body);
	$("#uploaderModal .modal-header").html(title);
	$('#uploaderModal').modal('show');

}

$("#altertags").prop("checked", false);
$("#anonymize").prop("checked", false);
$("#tabswrapper").tabs();


$(".ui-tabs-tab").on("click", function(e) {

	if ($(this).attr("aria-controls") == "preprocess_zip" || $(this).attr("aria-controls") == "zip_to_pacs") {
		$("#dropzonewrapper").show();
	}
	else {
		$("#dropzonewrapper").hide();
	}
});

$("#anonymize").on("change", function(e) {

	if($("#anonymize").is(":checked")) {

		$("#internal_outside_wrapper").hide();
		$("#altertags").prop("checked", false);
		$("#edited_tag_inputs").hide();

	}
	else {
		$("#internal_outside_wrapper").show();

	}
});

$("#altertags").on("change", function(e) {

	if($("#altertags").is(":checked")) {

		$("#edited_tag_inputs").show();
	}
	else {
		$("#edited_tag_inputs").hide();
	}

});
<?php
// Dropzone.prototype.defaultOptions.dictDefaultMessage = "Drag Zip or Choose Zip from here.";
// Dropzone.prototype.defaultOptions.dictDefaultMessage = "Drop files here to upload";
// Dropzone.prototype.defaultOptions.dictFallbackMessage = "Your browser does not support drag'n'drop file uploads.";
// Dropzone.prototype.defaultOptions.dictFallbackText = "Please use the fallback form below to upload your files like in the olden days.";
// Dropzone.prototype.defaultOptions.dictFileTooBig = "File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.";
// Dropzone.prototype.defaultOptions.dictInvalidFileType = "You can't upload files of this type.";
// Dropzone.prototype.defaultOptions.dictResponseError = "Server responded with {{statusCode}} code.";
// Dropzone.prototype.defaultOptions.dictCancelUpload = "Cancel upload";
// Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "Are you sure you want to cancel this upload?";
// Dropzone.prototype.defaultOptions.dictRemoveFile = "Remove file";
// Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "You can not upload any more files.";
?>

var dropzone = new Dropzone("#mydropzone", {

  paramName: "file", // The name that will be used to transfer the file
  maxFilesize: 4000, // MB
  url: 'PlaceHolder', // dynamically assigned
  maxFiles: 1,
  acceptedFiles: ".zip",
  addRemoveLinks: true,
  params: {
//   method:"UploadZipPreProcess",
  },
  createImageThumbnails: true,
  dictDefaultMessage: "Drag Zip or Choose Zip from here."

});

dropzone.on("success", function(file, response){

  response =  JSON.parse(response);
  console.log(response);
  $("#uploadresults").html(response.results);
  $('[href="#uploadresults"]').trigger("click");
  showuploaderModal ("", response.status);
  $(".dz-success-mark svg").css("background", "green");
  $(".dz-success-mark").css("display", "block");

});

dropzone.on("error", function(file) {
  $(".dz-error-mark svg").css("background", "red");
  $(".dz-error-mark").css("display", "block");
});

dropzone.on("drop", function(file) {
  $("#dropzonetext").hide();
});

dropzone.on("processing", function(file) {
	$("#uploadresults").html("");
  	// Add the selected params before uploading with this method.
	selectedtab = $(".ui-tabs-active").attr("aria-controls");
	if (selectedtab != "preprocess_zip" && selectedtab != "zip_to_pacs") {
		showuploaderModal("", "Please choose a .zip upload tab.")
	}
	else {
		if (selectedtab == "preprocess_zip") this.options.url = '/PACSUploadStudies/UploadZipPreProcess';
		if (selectedtab == "zip_to_pacs") this.options.url = '/PACSUploadStudies/UploadZipToPACS';

	}
	alert(this.options.url);

});

dropzone.on("sending", function(file, xhr, formData) {

	formData.append("anonymize", $("#anonymize").is(":checked"));
	formData.append("altertags", $("#altertags").is(":checked"));
	// Things to add the list of tags modifications to the post, used in other sectins also.
	$('#edited_tag_inputs [name = "taglistname[]"]').each(function( index ) {
		formData.append($( this ).val() , $('#edited_tag_inputs [name = "taglistvalue[]"]')[index].value);
  		console.log( index + ": " + $( this ).val() + $('#edited_tag_inputs [name = "taglistvalue[]"]')[index].value);
	});
	$("#dropzonetext").hide();

});

dropzone.on("removedfile", function(file) {
    console.log(this.getQueuedFiles());
    $(".dz-error-mark").css("display", "none");
    $(".dz-success-mark").css("display", "none");
   $("#dropzonetext").show();
});

document.querySelector("#clearzip").onclick = function() {
  dropzone.removeAllFiles(true);
};

</script>

<script>

    XMLHttpRequest.prototype.origOpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open   = function (method,url) {
    this.origOpen.apply(this, arguments);
    this.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
    this.setRequestHeader('nonce', '<?php  // echo $_SESSION["nonce"] ?>');


};
</script>
<script src="/orthanc_upload_support/main.js"></script>
</body>
</html>