<?php

use App\Helpers\Widgets;
use App\Models\Referrers\ReferringPhysician;
use App\Models\Orders\Orders;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;

$doctor = ReferringPhysician::where('identifier', $doctor_id)->first();

?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order Requests') }}
        </h2>
    </x-slot>
    <hr>
    <div style = "text-align: justify;margin: 20px;" >Complete the form below and submit to request an exam.  Please include all / most of the requested information so that we can correctly identify and register the patient if necessary.  If you do not have the MRN that is fine.  However, please include some contact information for the patient.</div>
    <h5 style = "text-align: center;">Order Request Form</h5>

<form class="" role="form" id="orderrequestform" name="orderrequestform">

<input type = "hidden" value = "<?php echo $doctor_id ?>" id="referring_physician_id" name = "referring_physician_id">

	<div class="form-group shadedform row">
		<div class="col-sm-2">
		<label for="patient_fname" class ="control-label">First name:<i class="fas fa-asterisk"></i></label>
	      <input type="text" class="form-control jqvalidmynames" id="patient_fname" name="patient_fname" placeholder="Enter name" required value = "">
		</div>
		<div class="col-sm-2">
		<label for="patient_lname" class ="control-label">Last name:<i class="fas fa-asterisk"></i></label>
	      <input type="text" class="form-control jqvalidmynames" id="patient_lname" name="patient_lname" placeholder="Enter name" required value = "">
		</div>
		<div class="col-sm-2">
		<label for="patient_mname" class ="control-label">Middle name</label>
	      <input type="text" class="form-control" id="patient_mname" name="patient_mname" placeholder="Enter name (optional)" value = "">
		</div>
		<div class="col-sm-2">
	    <label for="patient_birth_date" class ="control-label">DOB:<i class="fas fa-asterisk"></i></label>
	      <input type="text" class="form-control datepicker jqvalidmydates" id="patient_birth_date" name="patient_birth_date" placeholder="YYYY-MM-DD" required value = "">
		</div>
		<div class="col-sm-2">
		<label for="patient_sex" class ="control-label">Sex:<i class="fas fa-asterisk"></i></label>
			<select class="SumoUnder" id="patient_sex" name="patient_sex" required>
				<option disabled selected>
					Select option
				</option>
				<option value='M'>
					M
				</option>
				<option value='F'>
					F
				</option>
				<option value='O'>
					O
				</option>
			</select>
		</div>
		<div class="col-sm-2">
		<label for="patientid" class ="control-label col-sm-2">MRN:</label>
	      <input type="text" class="form-control" id="patientid" name="patientid" placeholder="If Available" value = "">
		</div>
    </div>
    
    <div class="form-group shadedform row">
		<div class="col-sm-4">
		<label for="patient_phone_ctry" class="control-label">Patient Phone:</label>

			<select class="SumoUnder" id="patient_phone_ctry" name="patient_phone_ctry">

<?php              $phonectrycodes = DatabaseFactory::getPhoneCountries("", true, false);

                echo $phonectrycodes; ?>

			</select>
			<input type="text" class="form-control jqvalidphone" id="patient_phone" name="patient_phone" placeholder="Digits & Dashes" value = "">
		</div>
		<div class="col-sm-2">
		        <label for="scheduled_procedure_step_start_date"  class ="control-label">Date:</label>

	      <input  type="text" class="form-control datepicker jqvalidmydates" id="scheduled_procedure_step_start_date" name="scheduled_procedure_step_start_date" value = "" placeholder="Desired Date (optional)">
	     <!--  <button  type="button" class="uibuttonsmallred form-control" id="ordeformshowday">show day</button> -->
		</div>
		<div class="col-sm-2">
			      <label for="scheduled_procedure_step_start_time" class ="control-label">TIme:</label>

	      <input type="text" class="form-control timepicker" id="scheduled_procedure_step_start_time" name="scheduled_procedure_step_start_time" value = "" placeholder="Desired Time (optional)">
		</div>
		<div class="col-sm-2">
		<label for="priority" class ="control-label">Urgency:<i class="fas fa-asterisk"></i></label>
		<select class="SumoUnder" id="priority" name="priority" required>
		<?php $options = DatabaseFactory::getOrderPriorities (null);
			echo $options;
        ?>
		</select>
		</div>
	</div>
	
	<div class="form-group shadedform row">
		<div class="col-sm-4">
	   <label for="patient_email" class="control-label">Patient e-mail:</label>
			<input class="form-control jqvalidmyemail" id="email" name="patient_email" type="patient_email" placeholder="Enter email address" value = "">
		</div>
		<div class="col-sm-6">
			<div id = "referrerrequest"></div>
			<label for="requested_procedure_id" class ="control-label">Exam ID, Name:<i class="fas fa-asterisk"></i></label>
            <select class="search-box SumoUnder" id="requested_procedure_id" name="requested_procedure_id" required> <!-- multiple -->
            <option disabled selected>Select option</option>

			<?php
            
            $exams =  DatabaseFactory::getExams();
            $examlength = "";
            $examcodes = "";
            
			foreach ($exams as $row) {
				echo '<option data-exam_length = "' .$row->exam_length . '" data-linked_exams = "' .str_replace('"', '', $row->linked_exams) . '" value="' . $row->requested_procedure_id . '"';
// 				if ($row->requested_procedure_id == $order->requested_procedure_id ) {
// 					echo " selected";
// 					$examlength = $row->exam_length;
// 					$examcodes  = str_replace('"', '', $row->linked_exams);
// 				}
				echo '>'. $row->requested_procedure_id . ' - '  . $row->exam_name . '</option>';
			}

    ?>
            </select>
            <div style = "clear:both;"><span>Exam Length:  </span><span id="exam_length"><?php echo $examlength  ?></span></div>
            <div style = "clear:both;"><span>Linked Codes::  </span><span id="linked_exams"><?php echo $examcodes  ?></span></div>

		</div>
	</div>

    <div class="form-group shadedform row">



    </div>

    <div class="form-group shadedform row">
		<label for="indication"  class ="control-label col-sm-2">Indication & Notes:<i class="fas fa-asterisk"></i></label>
		<div class="col-sm-10">
	      <textarea  type="text" class="form-control" id="indication" name="indication" required value = ""></textarea>
		</div>
    </div>
    
    <div class="form-group shadedform row">
 
		<h4 style = "text-align:center;" class ="col-sm-12">Related To</h4>

		<label style = "text-align:center;" for="related_employment"  class ="control-label col-sm-1">Employment:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "related_employment">
		<label style = "text-align:center;" for="related_auto"  class ="control-label col-sm-1">Auto:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "related_auto">
		<label style = "text-align:center;" for="related_otheraccident"  class ="control-label col-sm-1">Other Accident:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "related_otheraccident">
		<label style = "text-align:center;" for="related_emergency"  class ="control-label col-sm-1">Emergency:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "related_emergency">
		<label style = "text-align:center;" for="related_drugs"  class ="control-label col-sm-1">Drugs:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "related_drugs">
		<label style = "text-align:center;" for="related_pregnancy"  class ="control-label col-sm-1">Pregnancy LMP:</label>
		<input style = "text-align:center;" type = "text" class ="col-sm-1 datepicker" name = "related_pregnancy" value = "">

	</div>
    
    <div class="form-group shadedform row">
 
		<h4 style = "text-align:center;" class ="col-sm-12">Employment Status / Onset illness</h4>

		<label style = "text-align:center;" for="employed"  class ="control-label col-sm-1">Employed:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "employed">
		<label style = "text-align:center;" for="employed_student"  class ="control-label col-sm-1">Student:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "employed_student">
		<label style = "text-align:center;" for="employed_other"  class ="control-label col-sm-1">Other:</label>
		<input style = "text-align:center;" type = "checkbox" class ="col-sm-1" name = "employed_other">
		<label style = "text-align:center;" for="illness_date"  class ="control-label col-sm-1">Illness Date:</label>
		<input style = "text-align:center;" type = "text" class ="col-sm-1 datepicker" name = "illness_date" value = "">

	</div>
    
    <div class="formbuttons shadedform">
     <button type="submit" id="requestorder" class="uibuttonsmallred">Submit</button>
     <button type="button" id="clearrequest" class="uibuttonsmallred">Clear</button>
</div>

</form>
    <div id = "orderrequests" class = "listwrapper">
    <div class="container mt-5">
        <div class="orderstatusicons"><h5>PENDING REQUESTS</h5></div>
        <table class="table table-bordered yajra-datatable" id = "pending_requests">
            <thead>
                <tr>
                    <th>Last</th>
                    <th>First</th>
                    <th>DOB / AGE</th>
                    <th>Sex</th>
                    <th>MRN</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Requested</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    </div>
    
    <div id = "orders" class = "listwrapper">
    <div class="container mt-5">
        <div class="orderstatusicons">

<h5>PLACED ORDERS</h5>
<span class="uibuttonsmallred ">NW</span><span>New</span>
<span class="uibuttonsmallred ">XO</span><span>Modified</span>
<span class="uibuttonsmallred ">CA</span><span>Cancelled</span>
<span class="uibuttonsmallred ">NS</span><span>No Show</span>
<span class="uibuttonsmallred ">IP</span><span>In Progress</span>
<span class="uibuttonsmallred ">SC</span><span>Scheduled</span>
<span class="uibuttonsmallred ">CM</span><span>Completed</span>
</div>

        <table class="table table-bordered yajra-datatable" id = "placed_orders">
            <thead>
                <tr>
                    <th>Last</th>
                    <th>First</th>
                    <th>DOB / AGE</th>
                    <th>Sex</th>
                    <th>MRN</th>
                    <th>Accession</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Last Update</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    </div>
<x-myjs /> 
 <script nonce= "{{ csp_nonce() }}">

  $(function () {
    
    var table = $('#pending_requests').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 10,
        order: [[ 8, "desc" ]], // sort by request date descending
        ajax: {
            url: '/referrers/requests_datatable',
            type: 'POST'
        },
        
        columns: [
            {data: 'patient_lname', name: 'patient_lname'},
            {data: 'patient_fname', name: 'patient_fname'},
            {data: 'patient_birth_date', name: 'patient_birth_date'},
            {data: 'patient_sex', name: 'patient_sex'},
            {data: 'patientid', name: 'patientid'},
            {data: 'requested_procedure_id', name: 'requested_procedure_id'},
            {data: 'scheduled_procedure_step_start_date', name: 'scheduled_procedure_step_start_date'},
            {data: 'scheduled_procedure_step_start_time', name: 'scheduled_procedure_step_start_time'},
            {data: 'created_at', name: 'created_at'},
        ],
        "lengthMenu": [ 2,5,10, 25, 50, 75, 100 ]
    });
    
    var table2 = $('#placed_orders').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 10,
        order: [[ 5, "desc" ]], // sort by accession descending
        ajax: {
            url: '/referrers/placedorders_datatable',
            type: 'POST'
        },
        
        columns: [
            {data: 'patient_lname', name: 'patient_lname'},
            {data: 'patient_fname', name: 'patient_fname'},
            {data: 'patient_birth_date', name: 'patient_birth_date'},
            {data: 'patient_sex', name: 'patient_sex'},
            {data: 'patientid', name: 'patientid'},
            {data: 'accession_number', name: 'accession_number'},
            {data: 'requested_procedure_id', name: 'requested_procedure_id'},
            {data: 'scheduled_procedure_step_start_date', name: 'scheduled_procedure_step_start_date'},
            {data: 'scheduled_procedure_step_start_time', name: 'scheduled_procedure_step_start_time'},
            {data: 'timestamp', name: 'timestamp'},
            {data: 'ourstatus', name: 'ourstatus'},
        ],
        "lengthMenu": [ 2,5,10, 25, 50, 75, 100 ]
    });
    
  });
</script>   
<style>
	
	.form-group orderform {
	    text-align:right;
	}

	.orderstatusicons * {
	    color:black;
	}
	.orderstatusicons {
	    text-align:center;
	    margin:auto;
	    background:black;
	}
	.formbuttons {
	
	    text-align:center;
	    margin:auto;
	}

</style>

<script nonce= "{{ csp_nonce() }}">

$(document).ready(function() {

attachSumoSelect("#orderrequestform");

$('#clearrequest').on('click', function(e) {

$("#orderrequestform")[0].reset();

});

$("#requested_procedure_id").on("change", function (e) {
	e.preventDefault();
	$("#exam_length").html($(this).find(':selected').data("exam_length"));
	$("#linked_exams").html($(this).find(':selected').data("linked_exams"));
});


$('#requestorder').on('click', function(e) {

if ($('#orderrequestform').valid()) {

e.preventDefault(e);

    $.ajax({
    	url: '/referrers/submitorderrequest',
    	type: 'POST',
    	dataType: 'json',
    	data: $('#orderrequestform').serialize(),
    	beforeSend: function(e) {
        $("#spinner").css("display", "block");
        },
    	success: function(data, textStatus, xhr) {
    	console.log(data);
                showMessage("",data.message);
    	        $("#pending_requests").DataTable().ajax.reload();
    	}
    });
    }
    else {
    //invalid form
    }
});

});

</script>
</x-app-layout>