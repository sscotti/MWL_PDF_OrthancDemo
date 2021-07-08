<?php
use App\Models\Patients\Patients;
use App\Models\Patients\Insurance;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;
// $patient = Patients::where('mrn', $mrn)->first();
// $avatar = Patients::getAvatar($mrn);
?>

<form class="form-horizontal" role="form" id="insurance" name="insurance">

    <div style="text-align:center;" class="form-group shadedform row"><h4 style="text-align: center;margin: auto;">Policy Holder</h4></div>
    <input type="hidden" class="form-control" readonly id="ins_id" name="ins_id" placeholder= "assigned" value="<?php echo $data->ins_id ?>">  <!-- empty if new, set if editing, stored locally for now. -->
	<div class="form-group shadedform row">
		<label for="ins_fname" class="control-label col-sm-2">
			First name:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input required type="text" class="form-control jqvalidmynames" id="ins_fname" name="ins_fname" placeholder="Enter name" value = "<?php echo $data->ins_fname  ?>">
		</div>
		<label for="ins_lname" class="control-label col-sm-2">
			Last name:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input required type="text" class="form-control jqvalidmynames" id="ins_lname" name="ins_lname" placeholder="Enter name" value = "<?php echo  $data->ins_lname  ?>">
		</div>
		<label for="ins_mname" class="control-label col-sm-2">
			Middle name
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="ins_mname" name="ins_mname" placeholder="Optional" value = "<?php echo  $data->ins_mname  ?>">
		</div>
	</div>
	
	
	
	<div class="form-group shadedform row">
		<label required for="ins_birth_date"" class="control-label col-sm-2">
			DOB:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input required type="text" class="jqvalidmydates form-control datepicker" id="ins_birth_date" name="ins_birth_date" placeholder="Enter DOB" value = "<?php if ($data->ins_birth_date != '') echo date("Y-m-d", strtotime($data->ins_birth_date)) ?>">
		</div>
		<label for="ins_sex" class="control-label col-sm-2">
			Sex:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<select class="SumoUnder" id="ins_sex" name="ins_sex" required>
				<option disabled selected>
					Select option
				</option>

				<option value='M' <?php if ($data->ins_sex == 'M') {echo 'selected';}?>>
					M
				</option>
				<option value='F' <?php if ($data->ins_sex == 'F') {echo 'selected';}?>>
					F
				</option>
			</select>
		</div>

		<div class="col-sm-4">
		</div>
	</div>
	
	
	<div class="form-group shadedform row">
		<label for="ins_mobile_phone" class="control-label col-sm-2">
			Phone Country:
		</label>

		<div class="col-sm-4">
			<select class="SumoUnder" id="ins_mobile_phone" name="ins_mobile_phone[]">

<?php              $phonectrycodes = DatabaseFactory::getPhoneCountries($data->ins_mobile_phone_country, true, false);

                echo $phonectrycodes; ?>

			</select>
		</div>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidphone" id="ins_mobile_phone_suffix" name="ins_mobile_phone[]" placeholder="Digits & Dashes" value = "<?php echo $data->ins_mobile_phone ?>">
		</div>
		<label for="ins_email" class="control-label col-sm-2">
			e-mail:
		</label>
		<div class="col-sm-2">
			<input class="form-control jqvalidmyemail" id="ins_email" name="ins_email" type="email" placeholder="Enter email address" value = "<?php echo $data->ins_email ?>">
		</div>
	</div>



	<div class="form-group shadedform row">
		<label for="ins_address_1" class="control-label col-sm-2">
			Address:
		</label>
		<label for="ins_address_2" class="control-label col-sm-0">
		</label>
		<div class="col-sm-4">
			<input class="form-control" id="ins_address_1" name="ins_address_1" type="text" placeholder="Enter address" style="display:block;" value = "<?php echo $data->ins_address_1 ?>">
			<input class="form-control" id="ins_address_2" name="ins_address_2" type="text" placeholder="Enter address" value = "<?php echo $data->ins_address_2 ?>">
		</div>
		<div class="col-sm-6">

		</div>
	</div>
	
 <div class="form-group shadedform row">
		<label for="ins_city" class="control-label col-sm-2">
			City:
		</label>
		<div class="col-sm-2">
			<input class="form-control" id="ins_city" name="ins_city" type="text" placeholder="Enter City" value = "<?php echo $data->ins_city ?>">
		</div>
			</div>



	<div class="form-group shadedform row">
		<label for="ins_state" class="control-label col-sm-2">
			State:<i class="fas fa-asterisk"></i>
		</label>

		<div class="col-sm-2">
			<select required class="SumoUnder" id="ins_state" name="ins_state">

<?php
                $fetchstates = DatabaseFactory::getStates($data->ins_state);
                echo $fetchstates;
?>
			</select>
		</div>
		<label for="ins_country" class="control-label col-sm-2">
			Country:<i class="fas fa-asterisk"></i>
		</label>

		<div class="col-sm-2">
			<select required class="SumoUnder" id="ins_country" name="ins_country">

<?php
                $fetchcountries = DatabaseFactory::getCountries($data->ins_country);
                echo $fetchcountries;

                 ?>
			</select>
		</div>
		<label for="ins_postal" class="control-label col-sm-2">
			Postal Code:
		</label>
		<div class="col-sm-2">
			<input class="form-control" id="ins_postal" name="ins_postal" type="text" placeholder="Enter postal code" value = "<?php echo $data->ins_postal ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="carrier_id" class="control-label col-sm-2">
			Insurance Name:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">

			<select required class="SumoUnder" id="carrier_id" name="carrier_id">
			    <?php 
				echo DatabaseFactory::getCarrierList($data->carrier_id);
                ?>
			</select>
		</div>
		<label for="member_id" class="control-label col-sm-2">
			Member ID:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="member_id" name="member_id" placeholder="Member ID" value = "<?php echo $data->member_id ?>">
		</div>
		<label for="group_id" class="control-label col-sm-2">
			Group ID:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="group_id" name="group_id" placeholder="Group ID" value = "<?php echo $data->group_id ?>">
		</div>
	</div>

	<div class="form-group shadedform row">
		<label for="effective_date" class="control-label col-sm-2">
			Effective Date:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input type="text" required class="form-control datepicker jqvalidmydates" id="effective_date" name="effective_date" placeholder="Effective Date" value = "<?php echo $data->effective_date ?>">
		</div>
		<label for="expiration_date" class="control-label col-sm-2">
			Expiration Date:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control datepicker jqvalidmydates" id="expiration_date" name="expiration_date" placeholder="Expiration Date" value = "<?php echo $data->expiration_date ?>">
		</div>
		<label for="priority" class="control-label col-sm-2">
			Priority:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<select required class="SumoUnder" id="priority" name="priority">
				<option disabled selected value="">
					Select option
				</option>
				<option value= "1"<?php if ($data->priority == '1') echo " selected" ?>>Primary</option>
				<option value= "2"<?php if ($data->priority == '2') echo " selected" ?>>Secondary</option>
				<option value= "3"<?php if ($data->priority == '3') echo " selected" ?>>Tertiary</option>
				<option value= "4"<?php if ($data->priority == '4') echo " selected" ?>>Fourth</option>

			</select>		
		</div>
	</div>
	
		<div class="form-group shadedform row">
		<label for="relationship" class="control-label col-sm-2">
			Relationship:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<select required class="SumoUnder" id="relationship" name="relationship">
				<option disabled selected value="">
					Select option
				</option>
				<option value= "Self"<?php if ($data->relationship == 'Self') echo " selected" ?>>Self</option>
				<option value= "Mother"<?php if ($data->relationship == 'Mother') echo " selected" ?>>Mother</option>
				<option value= "Father"<?php if ($data->relationship == 'Father') echo " selected" ?>>Father</option>
				<option value= "Spouse"<?php if ($data->relationship == 'Spouse') echo " selected" ?>>Spouse</option>
				<option value= "Partner"<?php if ($data->relationship == 'Partner') echo " selected" ?>>Partner</option>
				<option value= "Child"<?php if ($data->relationship == 'Child') echo " selected" ?>>Child</option>
				<option value= "Other"<?php if ($data->relationship == 'Other') echo " selected" ?>>Other</option>
				

			</select>
		</div>
		<label for="plan_name" class="control-label col-sm-2">
			Plan Name:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="Plan Name" value = "<?php echo $data->plan_name ?>">
		</div>

	</div>
	
	<div class="form-group shadedform row">
		<label for="co_pay_amount" class="control-label col-sm-2">
			Co-Pay Amount:
		</label>
		<div class="col-sm-2">
		<input type="text" class="form-control jqvalidcurrency" id="co_pay_amount" name="co_pay_amount" placeholder="Co-Pay Amount" value = "<?php echo $data->co_pay_amount ?>">
		</div>
		<label for="co_pay_percent" class="control-label col-sm-2">
			Co-Pay %:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidpercentage" id="co_pay_percent" name="co_pay_percent" placeholder="Co-Pay %" value = "<?php echo $data->co_pay_percent ?>">
		</div>
		<label for="deductible_amount" class="control-label col-sm-2">
			Deductible:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidcurrency" id="deductible_amount" name="deductible_amount" placeholder="Deductible" value = "<?php echo $data->deductible_amount ?>">
		</div>

	</div>

<div style="text-align:center;" class="form-group shadedform">
     <button type="submit" id="submitinsurance" class="uibuttonsmallred">Submit</button>
     <?php if ($data->ins_id != "New") {
     echo '<input type="checkbox" name="deleteinsurance" id="deleteinsurance" class="uibuttonsmallred">Delete</button>';
     }
     ?>
<input type="checkbox" id="copyfrompatient" class="uibuttonsmallred">Copy from Patient</button>
</div>
<input type="hidden" id = "ins_mrn" name = "mrn" value ="<?php echo $data->mrn ?>">
</form>

<script>

$(".fa-window-close").on("click", function(e) {
$("html, body").animate({ scrollTop: 0 }, "slow");
});



    attachDateTimePicker();
    attachSumoSelect("#insurance");
    var dateToday = new Date();
    var yearrange = dateToday.getFullYear() - 5 + ":" + (dateToday.getFullYear() + 5);
    $( "#effective_date" ).datepicker( "option", "yearRange",  yearrange );
    $( "expiration_date" ).datepicker( "option", "yearRange",  yearrange );
    $("#insurance").validate();



$('#insurance').on("submit", function(e) {

	e.preventDefault();
	if ($("#deleteinsurance").is(":checked") || $(this).valid() ) { // skip validation if deletion
	
	formdata = $("#insurance").serializeArray();  // like this method to add n/v pairs to the form submission.

          $.ajax({
                type: "POST",
                url: "/Insurance/add_edit",
                dataType: "json",
                data: formdata

            }).done(function(data, textStatus, jqXHR) {
            
				if(data.ins_id) {
				parseMessages('[{"status":"Updated"}]', true);
                $('#ins_id').val(data.ins_id);
                $('#insurancebuttons').html(data.buttons);
                if (data.delete == "1") {
                $("#EditingForm").html("");
                $("#EditingForm").hide();
                }
                }
                else parseMessages(data, true);

            });
        }
    });
    
    $('#copyfrompatient').on("change", function(e) {

	e.preventDefault();
	$("#ins_fname").val($("#first").val());
	$("#ins_lname").val($("#last").val());
	$("#ins_mname").val($("#mname").val());
	$("#ins_birth_date").val($("#birth_date").val());
	$("#ins_sex")[0].sumo.selectItem($("#sex").val());
	$("#ins_mobile_phone")[0].sumo.selectItem($("#mobile_phone").val());
	$("#ins_mobile_phone_suffix").val($("#mobile_phone_suffix").val());
	$("#ins_email").val($("#email").val());
	$("#ins_address_1").val($("#address_1").val());
	$("#ins_address_2").val($("#address_2").val());
	$("#ins_city").val($("#city").val());
	$("#ins_state")[0].sumo.selectItem($("#state").val());
	$("#ins_country")[0].sumo.selectItem($("#country").val());
	$("#ins_postal").val($("#postal").val());


    });
</script>