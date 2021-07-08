<?php
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;
?>
<form class="form-horizontal" role="form" id="contact" name="contact">
    <div style="text-align:center;" class="form-group shadedform row"><h4 style="text-align: center;margin: auto; padding:5px;font-size:14px !important;">Emergency Contact(s), Add to Notes if more than 1.</h4></div>

	<div class="form-group shadedform row">
		<label for="fname" class="control-label col-sm-2">
			First name:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidmynames" id="fname" name="fname" placeholder="Enter name" value = "<?php echo $contact->fname  ?>">
		</div>
		<label for="lname" class="control-label col-sm-2">
			Last name:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidmynames" id="lname" name="lname" placeholder="Enter name" value = "<?php echo  $contact->lname  ?>">
		</div>
		<label for="mname" class="control-label col-sm-2">
			Middle name
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="mname" name="mname" placeholder="Optional" value = "<?php echo  $contact->mname  ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="mobile_phone_ctry" class="control-label col-sm-2">
			Country Code 1:
		</label>

		<div class="col-sm-4">
			<select class="SumoUnder" id="mobile_phone_ctry" name="mobile_phone[]">

<?php              $phonectrycodes = DatabaseFactory::getPhoneCountries($contact->mobile_phone_ctry, true, false);

                echo $phonectrycodes; ?>

			</select>
		</div>
		<label for="mobile_phone_suffix" class="control-label col-sm-2">
			Phone:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidphone" id="mobile_phone_suffix" name="mobile_phone[]" placeholder="Digits & Dashes" value = "<?php echo $contact->mobile_phone ?>">
		</div>
		

	</div>


	<div class="form-group shadedform row">
		<label for="alt_mobile_phone" class="control-label col-sm-2">
			Country Code 2:
		</label>

		<div class="col-sm-4">
			<select class="SumoUnder" id="alt_mobile_phone_ctry" name="alt_mobile_phone[]">

<?php              $phonectrycodes = DatabaseFactory::getPhoneCountries($contact->alt_mobile_phone_ctry, true, false);
                echo $phonectrycodes; ?>
			</select>
		</div>
		<label for="alt_mobile_phone_suffix" class="control-label col-sm-2">
			Phone:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidphone" id="alt_mobile_phone_suffix" name="alt_mobile_phone[]" placeholder="Digits & Dashes" value = "<?php echo $contact->alt_mobile_phone ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="email" class="control-label col-sm-2">
			e-mail 1:
		</label>
		<div class="col-sm-4">
			<input class="form-control jqvalidmyemail" id="email" name="email" type="email" placeholder="Enter email address" value = "<?php echo $contact->email ?>">
		</div>
	</div>

		<div class="form-group shadedform row">

		<label for="alt-email" class="control-label col-sm-2">
			email 2:
		</label>
		<div class="col-sm-4">
			<input class="form-control jqvalidmyemail" id="alt_email" name="alt_email" type="email" placeholder="Enter email address" value = "<?php echo $contact->alt_email ?>">
		</div>

	</div>


	<div class="form-group shadedform row">
		<label for="address_1" class="control-label col-sm-2">
			Address:
		</label>
		<label for="address_2" class="control-label col-sm-0">
		</label>
		<div class="col-sm-4">
			<input class="form-control" id="address_1" name="address_1" type="text" placeholder="Enter address" style="display:block;" value = "<?php echo $contact->address_1 ?>"> <input class="form-control" id="address_2" name="address_2" type="text" placeholder="Enter address" value = "<?php echo $contact->address_2 ?>">
		</div>
		<label for="relationship" class="control-label col-sm-2">
			Relationship:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<select required class="SumoUnder" id="relationship" name="relationship">
				<option disabled selected value="">
					Select option
				</option>
				<option value= "Mother"<?php if ($contact->relationship == 'Mother') echo " selected" ?>>Mother</option>
				<option value= "Father"<?php if ($contact->relationship == 'Father') echo " selected" ?>>Father</option>
				<option value= "Spouse"<?php if ($contact->relationship == 'Spouse') echo " selected" ?>>Spouse</option>
				<option value= "Partner"<?php if ($contact->relationship == 'Partner') echo " selected" ?>>Partner</option>
				<option value= "Brother"<?php if ($contact->relationship == 'Brother') echo " selected" ?>>Brother</option>
				<option value= "Sister"<?php if ($contact->relationship == 'Sister') echo " selected" ?>>Sister</option>
				<option value= "Child"<?php if ($contact->relationship == 'Child') echo " selected" ?>>Child</option>
				<option value= "Other"<?php if ($contact->relationship == 'Other') echo " selected" ?>>Other</option>
				
			</select>
		</div>
		<div class="col-sm-2"></div>
	</div>

		<div class="form-group shadedform row">
		<label for="city" class="control-label col-sm-2">
			City:
		</label>
		<div class="col-sm-4">
			<input class="form-control" id="city" name="city" type="text" placeholder="Enter city" value = "<?php echo $contact->city ?>">
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
                $fetchstates = DatabaseFactory::getStates($contact->state);
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
                $fetchcountries = DatabaseFactory::getCountries($contact->country);
                echo $fetchcountries;

                 ?>
			</select>
		</div>
		<label for="postal" class="control-label col-sm-2">
			Postal Code:
		</label>
		<div class="col-sm-2">
			<input class="form-control" id="postal" name="postal" type="text" placeholder="Enter postal code" value = "<?php echo $contact->postal ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="notes" class="control-label col-sm-2">
			Notes:
		</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="notes" name="notes" value = "<?php echo $contact->notes ?>"><?php echo $contact->notes ?></textarea>
		</div>
		<div class="col-sm-12" style="text-align: center;margin: auto;">

		<button type="submit" class="uibuttonsmallred">Submit</button>

		</div>
	</div>

<input type="hidden" name = "mrn" value ="<?php echo $contact->mrn ?>">
<input type="hidden" name = "contact_id" value ="<?php echo $contact->contact_id ?>">
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
                url: "/ManagePatients/editcontacts",
                dataType: "json",
                data: formdata

            }).done(function(data, textStatus, jqXHR) {

                parseMessages(data, true);

            });
        }
    });
</script>