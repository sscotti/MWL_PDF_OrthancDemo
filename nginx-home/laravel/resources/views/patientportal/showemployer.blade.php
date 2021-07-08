<?php
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;
?>
<form class="form-horizontal" role="form" id="contact" name="contact">
    <div style="text-align:center;" class="form-group shadedform row"><h4 style="text-align: center;margin: auto; padding:5px;font-size:14px !important;">Employer.  If there are others, please put in "Notes".</h4></div>

	<div class="form-group shadedform row">
		<label for="employer_name" class="control-label col-sm-2">
			Employer Name:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="employer_name" name="employer_name" placeholder="Enter Company Name" value = "<?php echo $employer->employer_name  ?>">
		</div>
		<label for="contact_lname" class="control-label col-sm-2">
			Contact Last Name:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="contact_lname" name="contact_lname" placeholder="Enter name" value = "<?php echo  $employer->contact_lname  ?>">
		</div>
		<label for="contact_fname" class="control-label col-sm-2">
			Contact First Name:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="contact_fname" name="contact_fname" placeholder="Optional" value = "<?php echo  $employer->contact_fname  ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="mobile_phone_ctry" class="control-label col-sm-2">
			Country Code:
		</label>

		<div class="col-sm-4">
			<select class="SumoUnder" id="mobile_phone_ctry" name="mobile_phone[]">

<?php              $phonectrycodes = DatabaseFactory::getPhoneCountries($employer->mobile_phone_ctry, true, false);

                echo $phonectrycodes; ?>

			</select>
		</div>
		<label for="mobile_phone" class="control-label col-sm-2">
			Phone:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidphone" id="mobile_phone_suffix" name="mobile_phone[]" placeholder="Digits & Dashes" value = "<?php echo $employer->mobile_phone_suffix ?>">
		</div>
		

	</div>


	<div class="form-group shadedform row">
		<label for="email" class="control-label col-sm-2">
			e-mail:
		</label>
		<div class="col-sm-4">
			<input class="form-control jqvalidmyemail" id="email" name="email" type="email" placeholder="Enter email address" value = "<?php echo $employer->email ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="address_1" class="control-label col-sm-2">
			Address:
		</label>
		<label for="address_2" class="control-label col-sm-0">
		</label>
		<div class="col-sm-4">
			<input class="form-control" id="address_1" name="address_1" type="text" placeholder="Enter address" style="display:block;" value = "<?php echo $employer->address_1 ?>"> <input class="form-control" id="address_2" name="address_2" type="text" placeholder="Enter address" value = "<?php echo $employer->address_2 ?>">
		</div>
		<div class="col-sm-6">
		</div>
	</div>

		<div class="form-group shadedform row">
		<label for="city" class="control-label col-sm-2">
			City:
		</label>
		<div class="col-sm-4">
			<input class="form-control" id="city" name="city" type="text" placeholder="Enter city" value = "<?php echo $employer->city ?>">
		</div>
		<div class="col-sm-6">
		</div>
	</div>




	<div class="form-group shadedform row">
		<label for="state" class="control-label col-sm-2">
			State:
		</label>

		<div class="col-sm-2">
			<select class="SumoUnder" id="state" name="state">

<?php
                $fetchstates = DatabaseFactory::getStates($employer->state);
                echo $fetchstates;
?>
			</select>
		</div>
		<label for="country" class="control-label col-sm-2">
			Country:
		</label>

		<div class="col-sm-2">
			<select class="SumoUnder" id="country" name="country">

<?php
                $fetchcountries = DatabaseFactory::getCountries($employer->country);
                echo $fetchcountries;

                 ?>
			</select>
		</div>
		<label for="postal" class="control-label col-sm-2">
			Postal Code:
		</label>
		<div class="col-sm-2">
			<input class="form-control" id="postal" name="postal" type="text" placeholder="Enter postal code" value = "<?php echo $employer->postal ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="notes" class="control-label col-sm-2">
			Notes:
		</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="notes" name="notes" value = "<?php echo $employer->notes ?>"><?php echo $employer->notes ?></textarea>
		</div>
		<div class="col-sm-12" style="text-align: center;margin: auto;">

		<button type="submit" class="uibuttonsmallred">Submit</button>

		</div>
	</div>

<input type="hidden" name = "mrn" value ="<?php echo $employer->mrn ?>">
<input type="hidden" name = "employer_id" value ="<?php echo $employer->employer_id ?>">
</form>


<script nonce= "{{ csp_nonce() }}">

    attachDateTimePicker();
    attachSumoSelect("#contact");
    var dateToday = new Date();
    var yearrange = dateToday.getFullYear() - 5 + ":" + (dateToday.getFullYear() + 5);
    $( "#contact .datepicker" ).datepicker( "option", "yearRange",  yearrange );
    $("#contact").validate();



$('#contact').on("submit", function(e) {

	let test = $(this).valid();
	e.preventDefault();
	if (test) {
	formdata = $("#contact").serializeArray();  // like this method to add n/v pairs to the form submission.

          $.ajax({
                type: "POST",
                url: "/ManagePatients/editemployers",
                dataType: "json",
                data: formdata

            }).done(function(data, textStatus, jqXHR) {

                parseMessages(data, true);

            });
        }
    });
</script>