<style>
.dxrxheader {
	text-align: left;
	font-weight: bold;
	text-decoration: underline;
	margin: 10px 0px 0px 0px;

}
#patienthistoryinfo {
margin-top:20px;
}
#patienthistoryinfo .row label {
text-align:right;
}
#patienthistoryinfo .row > div {
text-align:left;
}
</style>
<div id="historywrapper" class="container">
<?php
// omit if coming from patient or referrer page
$includefor = false;
if ($includefor) {
?>

<div id = "icdwrapper"><?php //echo WidgetsModel::ICD10DX(); ?></div>
<div id = "rxwrapper"><?php //echo WidgetsModel::RX(); ?></div>
<input type="hidden" class="form-control" readonly id="mrnforlivesearch" name="mrnforlivesearch" placeholder= "assigned" value="<?php echo $patient->mrn ?>">  <!-- empty if new, set if editing, stored locally for now. -->
<?php
}
?>
<!-- form to display and edit ICD-10 Codes and RX for a patient based on their MRN for the form -->

<form class="form-horizontal" role="form" id="icd10rxform" name="icd10rxform">

	<h5>Medical History</h5>
	<?php
	if (!$includefor ) {
	echo '<div style = "text-align:left;color:red;" class="col-sm-10">
		Medical History is maintained by the clinic.  Please notify if there are changes.
	</div>';
	}
	?>
	<div class = "dxrxheader">ICD10's:</div>
	<div class ="form-group row">

	<div id = "icd10dxcodes" class="col-sm-12">
	<?php
	$codes =[]; // PatientModel::getICDCodes ($patient->mrn);
	foreach ($codes as $code => $description) {
	if ($includefor) {
	echo '<div>' . $code . ':  ' .$description . '<i class="far fa-trash-alt deleteicd" data-value= "' . $code  . '"></i></div>';
	}
	else {
	echo '<div>' . $code . ':  ' .$description . '</div>';
	}
	}
	?>
	</div>
	</div>
	<h4 class = "dxrxheader">Medications:</h4>
	<div class ="form-group row">
	<div id = "medicationlist" class="col-sm-12">
	<?php
	$codes = []; //PatientModel::getRXCodes ($patient->mrn);
	foreach ($codes as $code => $description) {
	if ($includefor) {
	echo '<div>'  .$description . '<i class="far fa-trash-alt deleterx" data-value= "' . $code  . '"></i></div>';
	}
	else {
	echo '<div>'  .$description . '</div>';
	}
	}
	?>

	</div>
	</div>

</form>

<!-- "Editable" form to edit the surg history, medical history, free text meds and allergies -->
<!-- "Live" updates for this form and the form above form to edit the surg history, medical history, free text meds and allergies -->
<?php
if ($includefor) {
?>
<form class="form-horizontal editableform" role="form" id="medhistory" name="medhistory" data-action="/ManagePatients/edithistory">

<input type="hidden" class="form-control" readonly id="mrnforhistory" name="mrnforhistory" placeholder= "assigned" value="<?php echo $patient->mrn ?>">


    <div class="form-group shadedform row">
		<div class="col-sm-2">
		</div>
		<div style = "text-align:left;color:red;" class="col-sm-10">
			Please enter plain text for items below.  Updates after leaving field.  Look for the green check to the left.
		</div>

	</div>

	<div class="form-group shadedform row">
		<label for="surgical_history" class="control-label col-sm-2">
			Surgical History:
		</label>
		<div class="col-sm-10">
			<textarea type="text" class="form-control" id="surgical_history" name="surgical_history" placeholder="Enter history" value = "<?php echo $patient->surgical_history  ?>"><?php echo $patient->surgical_history  ?></textarea>
		</div>

	</div>
	<div class="form-group shadedform row">
		<label for="medical_history" class="control-label col-sm-2">
			Medical History:
		</label>
		<div class="col-sm-10">
			<textarea type="text" class="form-control" id="medical_history" name="medical_history" placeholder="Enter history" value = "<?php echo $patient->medical_history ?>"><?php echo $patient->medical_history ?></textarea>
		</div>
	</div>

	<div class="form-group shadedform row">
		<label for="medications_text" class="control-label col-sm-2">
			Medications
		</label>
		<div class="col-sm-10">
			<textarea type="text" class="form-control" id="medications_text" name="medications_text" placeholder="Enter medications" value = "<?php echo $patient->medications_text ?>"><?php echo $patient->medications_text ?></textarea>
		</div>
	</div>

	<div class="form-group shadedform row">
		<label for="allergies" class="control-label col-sm-2">
			Allergies
		</label>
		<div class="col-sm-10">
			<textarea type="text" class="form-control" id="allergies" name="allergies" placeholder="Enter allergies" value = "<?php echo $patient->allergies ?>"><?php echo $patient->allergies ?></textarea>
		</div>
	</div>

</form>
<?php
}
else {
?>
<div id = "patienthistoryinfo">
	<div class="shadedform row">

		<label for="surgical_history" class="control-label col-sm-2">
			Surgical History:
		</label>
		<div class="col-sm-10">
			<div><?php echo $patient->surgical_history  ?></div>
		</div>

	</div>
	<div class="shadedform row">
		<label for="medical_history" class="control-label col-sm-2">
			Medical History:
		</label>
		<div class="col-sm-10">
			<div type="text"><?php echo $patient->medical_history ?></div>
		</div>
	</div>

	<div class="shadedform row">
		<label for="medications_text" class="control-label col-sm-2">
			Medications
		</label>
		<div class="col-sm-10">
			<div type="text"><?php echo $patient->medications_text ?></div>
		</div>
	</div>

	<div class="shadedform row">
		<label for="allergies" class="control-label col-sm-2">
			Allergies
		</label>
		<div class="col-sm-10">
			<div type="text"><?php echo $patient->allergies ?></div>
		</div>
	</div>
</div>

<?php
}
?>


</div>

<script>


if ( typeof attacheditableform == 'function' ) attacheditableform("#medhistory") ;


$('.widget').on("click", function(e) {
	$(this).next().toggle();
});


$('#addicddx').on("click", function(e) {
	e.preventDefault();
	value = $(this).closest("form").find("select").val();
	if (!value) {
	alert ("nothing chosen");
	}
	else editicddx("add", $("#mrnforlivesearch").val(), value);
});

$('#icd10rxform').on("click", '.deleteicd', function(e) {
	e.preventDefault();
	editicddx("delete", $("#mrnforlivesearch").val(), $(this).data('value'));
});


function editicddx(action, mrn, value) {

	  $.ajax({
			type: "POST",
			url: "/ManagePatients/editicddx",
			dataType: "json",
			data: {action:action,mrn:mrn,value:value}

		}).done(function(data, textStatus, jqXHR) {
			var newcontent = "";
			for (let [key, value] of Object.entries(data)) {
			newcontent += '<div>' + key +':  ' + value +  '<i class="far fa-trash-alt deleteicd" data-value= "' + key  + '"></i></div>';
			}
			$("#icd10dxcodes").html(newcontent);

		});

}

$('#addrx').on("click", function(e) {
	e.preventDefault();
	value = $(this).closest("form").find("select").val();
	if (!value) {
	alert ("nothing chosen");
	}
	else editrx("add", $("#mrnforlivesearch").val(), value);
});

$('#icd10rxform').on("click", '.deleterx', function(e) {
	e.preventDefault();
	editrx("delete", $("#mrnforlivesearch").val(), $(this).data('value'));
});


function editrx(action, mrn, value) {

	  $.ajax({
			type: "POST",
			url: "/ManagePatients/editrx",
			dataType: "json",
			data: {action:action,mrn:mrn,value:value}

		}).done(function(data, textStatus, jqXHR) {
			var newcontent = "";
			for (let [key, value] of Object.entries(data)) {
			newcontent += '<div>' + value +  '<i class="far fa-trash-alt deleterx" data-value= "' + key  + '"></i></div>';
			}
			$("#medicationlist").html(newcontent);

		});

}

$('#historywrapper [data-toggle="popover"]').popover({
  trigger: "focus",
  container: "body"
});
</script>
