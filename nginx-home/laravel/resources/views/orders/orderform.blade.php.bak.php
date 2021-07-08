<?php
//use App\Models\Interface;

use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;

?>
<form class="" role="form" id="orderform" name="orderform">
	<input name = "order_id" type="hidden" value ="<?php echo $order->id ?>">
	<div class="form-group shadedform row">
		<div class="col-sm-2">
	    <label for="fname" class ="control-label">First name:<i class="fas fa-asterisk"></i></label>
	      <input  readonly type="text" class="form-control jqvalidmynames" id="fname" name="fname" placeholder="Enter name" required value = "<?php echo $patient->first ?>">
		</div>
		<div class="col-sm-2">
		<label for="lname" class ="control-label">Last name:<i class="fas fa-asterisk"></i></label>
	      <input  readonly type="text" class="form-control jqvalidmynames" id="lname" name="lname" placeholder="Enter name" required value = "<?php echo $patient->last ?>">
		</div>
		<div class="col-sm-2">
		<label for="mname" class ="control-label">Middle name</label>
	      <input  readonly type="text" class="form-control" id="mname" name="mname" placeholder="Enter name" value = "<?php echo $patient->mname ?>">
		</div>
		<div class="col-sm-2">
		<label for="patient_birth_date" class ="control-label">DOB:<i class="fas fa-asterisk"></i></label>

	      <input readonly  type="text" class="form-control" id="patient_birth_date" name="patient_birth_date" placeholder="Enter DOB" required value = "<?php echo $patient->birth_date ?>">
		</div>
		<div class="col-sm-2">
		<label for="patient_sex" class ="control-label">Sex:<i class="fas fa-asterisk"></i></label>

			<select class="SumoUnder" id="patient_sex" name="patient_sex" required disabled>
				<option disabled selected>
					Select option
				</option>

				<option value='M' <?php if ($patient->sex == 'M') {echo 'selected';}?>>
					M
				</option>
				<option value='F' <?php if ($patient->sex == 'F') {echo 'selected';}?>>
					F
				</option>
			</select>
            <input  type="hidden" class="form-control"  name="patient_sex"  required value = "<?php echo $patient->sex ?>">
		</div>
		<div class="col-sm-2">
		<label for="patientid" class ="control-label">MRN:<i class="fas fa-asterisk"></i></label>

	      <input  type="text" class="form-control" id="patientid" name="patientid" readonly placeholder="Hard Coded from DB" required value = "<?php echo $patient->mrn ?>">
		</div>
    </div>
    <div class="form-group shadedform row">
        <div class="col-sm-2">
		<label for="priority" class ="control-label">Urgency:<i class="fas fa-asterisk"></i></label>
		<select class="SumoUnder" id="priority" name="priority" required>
		<?php $options = DatabaseFactory::getOrderPriorities($order->priority);
			echo $options;
        ?>
		</select>
		</div>
		<div class="col-sm-2">
		<label for="status" class ="control-label">Order Status:<i class="fas fa-asterisk"></i></label>

            <select class="search-box SumoUnder" id="status" name="status" required> <!-- multiple -->
            <option disabled selected>Select option</option>
            <?php if ($ordertype == "NW" || $order->ourstatus == "NW" ) {
            ?>
            <option value='NW' selected>New Order</option>
            <?php
            }
            else {
            ?>
            <option value='XO' <?php if( $order->ourstatus == "XO") echo " selected" ?>>Modify(ied)</option>
            <option value='CA' <?php if( $order->ourstatus == "CA") echo " selected" ?>>Cancel(ed)</option>
            <option value='NS' <?php if( $order->ourstatus == "NS") echo " selected" ?>>No Show</option>
            <option value='SC' <?php if( $order->ourstatus == "SC") echo " selected" ?>>Schedule(d)</option>
            <option value='IP' <?php if( $order->ourstatus == "IP") echo " selected" ?>>In Progress (Arrived)</option>
            <option value='CM' <?php if( $order->ourstatus == "CM") echo " selected" ?>>Completed</option>
            <?php
            }
            ?>
            </select>

		</div>
		<div class="col-sm-2">
		<label for="scheduled_procedure_step_start_date"  class ="control-label">Date:</label>

	      <input  type="text" class="form-control datepicker jqvalidmydates" id="scheduled_procedure_step_start_date" name="scheduled_procedure_step_start_date" value = "<?php echo $order->scheduled_procedure_step_start_date ?>" placeholder="">
	     <!--  <button  type="button" class="uibuttonsmallred form-control" id="ordeformshowday">show day</button> -->
		</div>
		<div class="col-sm-2">
		<label for="scheduled_procedure_step_start_time" class ="control-label">TIme:</label>
	      <input  type="text" class="form-control timepicker" id="scheduled_procedure_step_start_time" name="scheduled_procedure_step_start_time" value = "<?php echo $order->scheduled_procedure_step_start_time ?>" placeholder="">
		</div>
		<?php

		if (!empty($order->accession_number)) {

			$appointments = DatabaseFactory::getAppointentsByAccessionNumber($order->accession_number);
			Log::info($appointments);
			if (count($appointments) == 1) $appointments = $appointments[0]->id;
			else if (count($appointments) > 1) $appointments = "? Multiple Appointments";
			else if (count($appointments) == 0) $appointments = "No Appointment";
		}
		else {
			$result = "New Order";
		}
		?>
		<div class="col-sm-2">
		<label for="accession_number" class ="control-label">Accession No:</label>

	      <input  readonly type="text" class="form-control" id="accession_number" name="accession_number" value="<?php echo $order->accession_number ?>" placeholder = "System Generated" >
		</div>

		<div class="col-sm-2">
		<label for="accession_number" class ="control-label">Appt. ID:</label>
	      <input  readonly type="text" class="form-control" id="appointment_id" name="appointment_id" value="<?php echo $appointments ?>" placeholder = "System Generated" >
		</div>


    </div>

    <div class="form-group shadedform row">

		<div class="col-sm-4">
			<label for="referring_physician" class ="control-label">Referring Doctor:<i class="fas fa-asterisk"></i></label>
			<select class="SumoUnder" id="referring_physician" name="referring_physician" class="col-sm-4" required>
			<option disabled selected>Select option</option>
			<?php  // will need code to check the doctor identifier to set the selected.

			$result = DatabaseFactory::getReferrersBy_ID_Email_SortBy(null, null, null); // just gets them all
			Log::info($result);
            foreach ($result as $row) {

                echo '<option value="' . $row->identifier . '"';
                if ($order->referring_physician_id == $row->identifier) echo "selected";
                echo '>'. $row->identifier . '-' . $row->lname . '^' . $row->fname . '</option>';

            }
            ?>
            </select><br>
        <label for="priorauth" class ="control-label">Prior Auth #:</label>
	      <input type="text" class="form-control" id="priorauth" name="priorauth" value="<?php echo $order->priorauth ?>" placeholder = "Prior Auth" >

        </div>
		<div class="col-sm-2">
		<label for="description" class ="control-label">Device:<i class="fas fa-asterisk"></i></label>

            <select class="search-box SumoUnder" id="device" name="device" required>
            <option disabled selected>Select option</option>
				<?php
                $result = DatabaseFactory::getDeviceList();
                foreach ($result as $row) {
                $json = json_encode($row);
                echo '<option value=\'' . $json . '\'';
                if ($row->scheduled_station_aetitle == $order->scheduled_station_aetitle) {
                 echo " selected";
                }
                echo '>'. $row->device_name . ' - ' . $row->scheduled_station_aetitle . '</option>';
                }

    		?>
            </select>

		</div>
		<div class="col-sm-6">
			<div id = "referrerrequest"></div>
			<label for="description" class ="control-label">Exam ID, Name:<i class="fas fa-asterisk"></i></label>
            <select class="search-box SumoUnder" id="description" name="description" required> <!-- multiple -->
            <option disabled selected>Select option</option>

			<?php
            $result = DatabaseFactory::getExamList();
            $examlength = "";
            $examcodes = "";
			foreach ($result as $row) {
				echo '<option data-exam_length = "' .$row->exam_length . '" data-linked_exams = "' .str_replace('"', '', $row->linked_exams) . '" value="' . $row->requested_procedure_id . '"';
				if ($row->requested_procedure_id == $order->requested_procedure_id ) {
					echo " selected";
					$examlength = $row->exam_length;
					$examcodes  = str_replace('"', '', $row->linked_exams);
				}
				echo '>'. $row->requested_procedure_id . ' - '  . $row->exam_name . '</option>';
			}

    ?>
            </select>
            <div style = "clear:both;"><span>Exam Length:  </span><span id="exam_length"><?php echo $examlength  ?></span></div>
            <div style = "clear:both;"><span>Linked Codes::  </span><span id="linked_exams"><?php echo $examcodes  ?></span></div>

		</div>
    </div>


<!--
On the HL7 side the operation and status are requested in the ORC segment of the order message (ORM^O001).

    ORC-1 (field 1) is the requested operation (HL7-OP)
    ORC-5 (field 5) is the requested status (HL7-STATUS)


Standard operation (HL7-OP) codes are:

    NW - new order
    XO - change an order
    CA - cancel an order
    SC - scheduling status change

…and standard status (HL7-STATUS) codes are:

    SC - SCHEDULED
    CM - COMPLETED
    CA - CANCELLED
    IP - IN_PROGRESS
    AR - ARRIVED
    DC - DISCONTINUEDL

 -->


    <div class="form-group shadedform row">
		<div class="col-sm-12">
		<label for="indication"  class ="control-label">Indication:<i class="fas fa-asterisk"></i></label>

	      <textarea type="text" class="form-control" id="indication" name="indication" required value = "<?php echo $order->indication ?>"><?php echo $order->indication ?></textarea>
		</div>
    </div>

    <div class="form-group shadedform row">

		<div style = "text-align:center;" class ="col-sm-12">Related To</div>

		<label style = "text-align:center;" for="related_employment"  class ="control-label col-sm-2">Employment:
		<?php $checked = ($order->related_employment == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "related_employment"></label>
		<label style = "text-align:center;" for="related_auto"  class ="control-label col-sm-2">Auto:
		<?php $checked = ($order->related_auto == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "related_auto"></label>
		<label style = "text-align:center;" for="related_otheraccident"  class ="control-label col-sm-2">Other Accident:
		<?php $checked = ($order->related_otheraccident == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "related_otheraccident"></label>
		<label style = "text-align:center;" for="related_emergency"  class ="control-label col-sm-2">Emergency:
		<?php $checked = ($order->related_emergency == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "related_emergency"></label>
		<label style = "text-align:center;" for="related_drugs"  class ="control-label col-sm-1">Drugs:
		<?php $checked = ($order->related_drugs == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "related_drugs"></label>
		<label style = "text-align:center;" for="related_pregnancy"  class ="control-label col-sm-1">Pregnancy LMP:</label>
		<?php $date = ($order->related_pregnancy != "") ? $order->related_pregnancy:""; ?>
		<input style = "text-align:center;" type = "text" class ="col-sm-2 datepicker" name = "related_pregnancy" value = "<?php echo $date ?>"></label>

		</div>

    <div class="form-group shadedform row">

		<div style = "text-align:center;" class ="col-sm-12">Employment Status / Onset illness</div>

		<label style = "text-align:center;" for="employed"  class ="control-label col-sm-2">Employed:
		<?php $checked = ($order->employed == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "employed">
		</label>
		<label style = "text-align:center;" for="employed_student"  class ="control-label col-sm-2">Student:
		<?php $checked = ($order->employed_student == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "employed_student"></label>
		<label style = "text-align:center;" for="employed_other"  class ="control-label col-sm-1">Other:
		<?php $checked = ($order->employed_other == "on") ? " checked ":""; ?>
		<input <?php echo $checked ?> style = "text-align:center;" type = "checkbox" name = "employed_other"></label>
		<label style = "text-align:center;" for="illness_date"  class ="control-label col-sm-1">Illness Date:</label>
		<?php $date = ($order->illness_date != "") ? $order->illness_date:""; ?>
		<input style = "text-align:center;" type = "text" class ="col-sm-2 datepicker" name = "illness_date" value = "<?php echo $date ?>">

	</div>

    <div class="orderformbuttons">
     <button type="submit" id="submitorder" class="uibuttonsmallred">Submit</button>
     <button type="button" id="closeorderoverlay" class="uibuttonsmallred">Close</button>
     <label>Calendar <input name = "orderformradio" id="calendarswitch" type="radio" ></label>
     <label>Patient <input name = "orderformradio" id="patientswitch" type="radio" ></label>
     <label>None <input name = "orderformradio" id="noneswitch" type="radio" ></label>
<!--
     <iframe src="/calendar/index" id="calendarframe" style="height: 100vh;width: 70%;display: block;text-align: center;margin: auto;"></iframe>
 -->
     <?php
//              $data->View->renderWithoutHeaderAndFooter('calendar/index');
     ?>
    </div>


<style>

	.form-group orderform {
	    text-align:right;
	}
	.orderformbuttons {
	    text-align: center;
	}

</style>

<script>

	$("#description").on("change", function (e) {
		e.preventDefault();
		$("#exam_length").html($(this).find(':selected').data("exam_length"));
		$("#linked_exams").html($(this).find(':selected').data("linked_exams"));
	});

	$('#calendarswitch').on("change", function(e) {

	if ($(this).is(":checked")) {

		if (isValidDate($('#scheduled_procedure_step_start_date').val())) {

			dateobject = splitDate($('#scheduled_procedure_step_start_date').val());
			suffix = "?year=" + dateobject.year + "&month=" + dateobject.month + "&day=" + dateobject.day;
		}
		else {
			suffix = "";
		}
		$('#calendardiv').show();
	    $('#calendardiv').load("/Calendar/noheaders" + suffix, function(e) {
	    // loadappointments($("#calendaryear").data("year"), $("#calendarmonth").data("month"), null);
		$("#calendardiv").height($("#modalDiv").height() - $("#orderform").height());
		});
	}

	else {
		$('#calendardiv').html("");
		$('#calendardiv').hide();
	}

	});


	$('#patientswitch').on("change", function(e) {

	if ($(this).is(":checked")) {

		$('#calendardiv').show();
			$.post("/ManagePatients/patientform", {mrn: "<?php echo $mrn ?>"}).done(function( data ) {
			$('#calendardiv').html(data);
		});
	}
	else {
	    $('#calendardiv').html("");
	    $('#calendardiv').hide();
	}
	});

	$('#noneswitch').on("change", function(e) {
		if ($(this).is(":checked")) {
			$('#calendardiv').html("");
			$('#calendardiv').hide();
		}
	});

</script>

</form>
<?php if ($fromcalendar) {
echo '<div  id="calendardiv"></div>';
}
?>
