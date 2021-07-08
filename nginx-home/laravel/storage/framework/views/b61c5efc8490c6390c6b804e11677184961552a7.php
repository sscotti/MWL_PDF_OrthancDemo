<?php
use App\Models\Patients\Patients;
use App\Models\Patients\Insurance;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;
$patient = Patients::where('mrn', $mrn)->first();
$avatar = Patients::getAvatar($mrn);
?>

<?php if (isset($component)) { $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\AppLayout::class, []); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header'); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Patient Profile')); ?>

        </h2>
     <?php $__env->endSlot(); ?>
<div class = "user_avatar"><img src = "<?php echo $avatar ?>"><br><span>Online Account Avatar</span></div>   

<form class="editableform" role="form" id="patientform" name="patientform" data-action="/patients/updateprofileitem" data-key = "mrn" data-keyvalue = "<?php echo $mrn ?>">
	<div class="form-group shadedform row">
		<label for="first" class="control-label col-sm-2">
			First name:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input required type="text" class="form-control jqvalidmynames" id="first" name="first" placeholder="Enter name" value = "<?php echo $patient->first ?>">
		</div>
		<label for="last" class="control-label col-sm-2">
			Last name:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input required type="text" class="form-control jqvalidmynames" id="last" name="last" placeholder="Enter name" value = "<?php echo $patient->last ?>">
		</div>
		<label for="mname" class="control-label col-sm-2">
			Middle name
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="mname" name="mname" placeholder="Optional" value = "<?php echo $patient->mname ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="birth_date"" class="control-label col-sm-2">
			DOB:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input required type="text" class="jqvalidmydates form-control datepicker" id="birth_date" name="birth_date" placeholder="Enter DOB" value = "<?php if ($patient->birth_date != '') echo date("Y-m-d", strtotime($patient->birth_date)) ?>">
		</div>
		<label for="sex" class="control-label col-sm-2">
			Sex:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<select class="SumoUnder" id="sex" name="sex" required>
				<option disabled selected>
					Select option
				</option>
				<option value='M' <?php if ($patient->sex == 'M') {echo 'selected';}?>>
					M
				</option>
				<option value='F' <?php if ($patient->sex == 'F') {echo 'selected';}?>>
					F
				</option>
				<option value='O' <?php if ($patient->sex == 'O') {echo 'selected';}?>>
					O
				</option>
				<option value='U' <?php if ($patient->sex == 'U') {echo 'selected';}?>>
					U
				</option>
			</select>
		</div>
		<label for="mrn" class="control-label col-sm-2">
			MRN:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="mrn" name="mrn" readonly placeholder="Hard Coded from DB" value = "<?php echo $patient->mrn ?>">
		</div>
	</div>
	<div class="form-group shadedform row">
		<label for="mobile_phone" class="control-label col-sm-2">
			Country Code 1:
		</label>

		<div class="col-sm-4">
			<select class="SumoUnder" id="mobile_phone" name="mobile_phone[]">

<?php              $phonectrycodes = DatabaseFactory::getPhoneCountries($patient->mobile_phone_country, true, false);

                echo $phonectrycodes; ?>

			</select>
		</div>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidphone" id="mobile_phone_suffix" name="mobile_phone[]" placeholder="Digits & Dashes" value = "<?php echo $patient->mobile_phone ?>">
		</div>
		<label for="alias" class="control-label col-sm-2">
			Alias:
		</label>
		<div class="col-sm-2">
			<input type="text" class="form-control" id="alias" name="alias" placeholder="Alias" value = "<?php echo $patient->alias ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="alt_mobile_phone" class="control-label col-sm-2">
			Country Code 2:
		</label>

		<div class="col-sm-4">
			<select class="SumoUnder" id="alt_mobile_phone_country" name="alt_mobile_phone[]">

<?php              $phonectrycodes = DatabaseFactory::getPhoneCountries($patient->alt_mobile_phone_country, true, false);
                echo $phonectrycodes; ?>
			</select>
		</div>
		<div class="col-sm-2">
			<input type="text" class="form-control jqvalidphone" id="alt_mobile_phone_suffix" name="alt_mobile_phone[]" placeholder="Digits & Dashes" value = "<?php echo $patient->alt_mobile_phone ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="email" class="control-label col-sm-2">
			e-mail 1:
		</label>
		<div class="col-sm-4">
			<input class="form-control jqvalidmyemail" id="email" name="email" type="email" placeholder="Enter email address" value = "<?php echo $patient->email ?>">
		</div>
	</div>

		<div class="form-group shadedform row">

		<label for="alt-email" class="control-label col-sm-2">
			email 2:
		</label>
		<div class="col-sm-4">
			<input class="form-control jqvalidmyemail" id="alt_email" name="alt_email" type="email" placeholder="Enter email address" value = "<?php echo $patient->alt_email ?>">
		</div>
		<label for="marital_status" class="control-label col-sm-2">
			Marital Staus:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-4">
			<select class="SumoUnder" id="marital_status" name="marital_status" required>

<?php              $types = DatabaseFactory::getMaritalStatusTypes($patient->marital_status, true, false);
                echo $types; ?>
			</select>
		</div>

	</div>


	<div class="form-group shadedform row">
		<label for="address_1" class="control-label col-sm-2">
			Address:
		</label>
		<label for="address_2" class="control-label col-sm-0">
		</label>
		<div class="col-sm-4">
			<input class="form-control" id="address_1" name="address_1" type="text" placeholder="Enter address" style="display:block;" value = "<?php echo $patient->address_1 ?>"> <input class="form-control" id="address_2" name="address_2" type="text" placeholder="Enter address" value = "<?php echo $patient->address_2 ?>">
		</div>
		<div class="col-sm-6">
		</div>
	</div>

		<div class="form-group shadedform row">
		<label for="city" class="control-label col-sm-2">
			City:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-4">
			<input required class="form-control" id="city" name="city" type="text" placeholder="Enter city" value = "<?php echo $patient->city ?>">
		</div>
		<div class="col-sm-6">
		</div>
	</div>




	<div class="form-group shadedform row">
		<label for="state" class="control-label col-sm-2">
			State:<i class="fas fa-asterisk"></i>
		</label>

		<div class="col-sm-2">
			<select required class="SumoUnder" id="state" name="state">

<?php
                $fetchstates = DatabaseFactory::getStates($patient->state);
                echo $fetchstates;
?>
			</select>
		</div>
		<label for="country" class="control-label col-sm-2">
			Country:<i class="fas fa-asterisk"></i>
		</label>

		<div class="col-sm-2">
			<select required class="SumoUnder" id="country" name="country">

<?php
                $fetchcountries = DatabaseFactory::getCountries($patient->country);
                echo $fetchcountries;

                 ?>
			</select>
		</div>
		<label for="postal" class="control-label col-sm-2">
			Postal Code:<i class="fas fa-asterisk"></i>
		</label>
		<div class="col-sm-2">
			<input required class="form-control" id="postal" name="postal" type="text" placeholder="Enter postal code" value = "<?php echo $patient->postal ?>">
		</div>
	</div>


	<div class="form-group shadedform row">
		<label for="patient_notes" class="control-label col-sm-2">
			Notes:
		</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="patient_notes" name="patient_notes" value = "<?php echo $patient->patient_notes ?>"><?php echo $patient->patient_notes ?></textarea>
		</div>
	</div>
	
    <div class="form-group shadedform row">
		<label for="appt_reminders" class="col-form-label-sm col-sm-6" style="text-align:right">
			Appointment Reminders:
		</label>
		<div class="col-sm-6">
			<input type="checkbox" class="form-control" id="appt_reminders" name="appt_reminders" <?php echo ($patient->appt_reminders == 1)?"checked":"" ?> >
		</div>
		<label for="reports_notification" class="col-form-label-sm col-sm-6" style="text-align:right">
			Report Notifications:
		</label>
		<div class="col-sm-6">
			<input type="checkbox" class="form-control" id="reports_notification" name="reports_notification" <?php echo ($patient->reports_notification == 1)?"checked":"" ?> >
		</div>
		<label for="send_reports" class="col-form-label-sm col-sm-6" style="text-align:right">
			Send Reports:
		</label>
		<div class="col-sm-6">
			<input type="checkbox" class="form-control" id="send_reports" name="send_reports" <?php echo ($patient->send_reports == 1)?"checked":"" ?> >
		</div>
		<input type="hidden" class="form-control" id="prefs_mrn" name="prefs_mrn" value = "<?php echo $patient->mrn ?>">
	</div>

</form>
<div>

      <div class="formselectorbuttons" id="insurancebuttons" >
      
		<?php
		echo '<div style="font-size:14px;color:black;font-weight:bold;">Insurance(s), Display Only</div>';
		$insurances = Insurance::getPatientInsurancesByMRN ($patient->mrn);

		foreach ($insurances as $insurance) {
		    $json['mrn'] = $patient->mrn;
 		    $json['ins_id'] = $insurance->ins_id;
		    echo '<button class="uibuttonsmallred" data-controller = "/patients/showinsurance" data-json =' . json_encode($json) .'>' .$insurance->carrier_name . '</button>';
		}
// 		echo '<button class="uibuttonsmallred" data-controller = "/patients/addinsurance" value="New">New</button>';
		?>
        </div>

	<div class="formselectorbuttons">
	
		<button class="uibuttonsmallred" data-title = "History" data-controller = "/patients/showhistory"  data-json = '{"mrn":"<?php echo $patient->mrn ?>"}'>History</button>
		<button class="uibuttonsmallred" data-title = "Contacts" data-controller = "/patients/showcontacts" data-json = '{"mrn":"<?php echo $patient->mrn ?>"}'>Contacts</button>
		<button class="uibuttonsmallred" data-title = "Employer" data-controller = "/patients/showemployer" data-json = '{"mrn":"<?php echo $patient->mrn ?>"}'>Employer</button>
		
	</div>

</div>


<style>

#insuranceform {
display:none;
}

</style>
    <?php if (isset($component)) { $__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\Myjs::class, []); ?>
<?php $component->withName('myjs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
<?php if (isset($__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3)): ?>
<?php $component = $__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3; ?>
<?php unset($__componentOriginal6b44892cf265e56cdfc262f28fc33c7c88b51cc3); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
    <script>
    
    attachSumoSelect("#patientform");
    
    $('[data-controller]').on("click", function(e) {
        e.preventDefault();
        let url = $(this).data('controller');
        let data = $(this).data();
        console.log();
        $.ajax({
            type: "POST",
            url: url,
            dataType: "html",
            data: $(this).data("json")
        })
        .done(function(data, textStatus, jqXHR) {
            showMessage($(this).data("title"), data);
        });
    
    });

    </script>
 <?php if (isset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da)): ?>
<?php $component = $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da; ?>
<?php unset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?><?php /**PATH /nginx-home/laravel/resources/views/patientportal/profile.blade.php ENDPATH**/ ?>