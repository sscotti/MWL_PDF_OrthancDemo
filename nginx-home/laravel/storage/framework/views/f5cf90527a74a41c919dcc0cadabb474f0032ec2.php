<?php
use App\Models\Referrers\ReferringPhysician;
use App\Helpers\DatabaseFactory;
$doctor = ReferringPhysician::where('identifier', $doctor_id)->first();
?>
<div class = "user_avatar"><img src = "<?php echo ReferringPhysician::getAvatar($doctor) ?>"></div>
<form id = "demographics" class = "editableform" role="form" data-action="/referrers/updateprofileitem" data-key = "identifier" data-keyvalue = "<?php echo $doctor_id ?>">
            <div style = "width:max-content;margin:auto;color:red;">Items Update after leaving field, or ENTER (look for the GREEN check)</div>
            <div class="form-group shadedform row">
            
            <label for="identifier" class ="col-form-label-sm col-sm-3">Identifer:</label>
            <div class="col-sm-3">
            <span><?php echo $doctor->identifier ?></span>
            </div>

            <label for="referrer_npi" class ="col-form-label-sm col-sm-3">NPI:</label>
            <div class="col-sm-3">
            <input  type="text" class="form-control form-control-sm" id="referrer_npi" name="referrer_npi" value="<?php echo $doctor->referrer_npi ?>">
            </div>
            </div>


            <div class="form-group shadedform row">
            <label for="lname" class ="col-form-label-sm col-sm-3">Last Name:</label>
            <div class="col-sm-3">
            <input type="text" class="form-control form-control-sm" id="lname" name="lname" value="<?php echo $doctor->lname ?>" required>
            </div>

            <label for="fname" class ="col-form-label-sm col-sm-3">First Name:</label>
            <div class="col-sm-3">
            <input  type="text" class="form-control form-control-sm" id="fname" name="fname" value="<?php echo $doctor->fname ?>" required>
            </div>
            </div>

            <div class="form-group shadedform row">
            <label for="mobile_phone_country" class ="col-form-label-sm col-sm-3">Phone Ctry:</label>
            <div class="col-sm-3">
            <select class="SumoUnder" id="mobile_phone_country" name="mobile_phone_country">

            <?php
            
            $phonectrycodes = DatabaseFactory::getPhoneCountries($doctor->mobile_phone_country, true, false);
            echo $phonectrycodes;
            ?>

            </select>
            </div>

            <label for="mobile_phone" class ="col-form-label-sm col-sm-3">Phone:</label>
            <div class="col-sm-3">
            <input  type="text" class="form-control form-control-sm jqvalidphone" id="mobile_phone" name="mobile_phone" placeholder="Digits & Dashes" value="<?php echo $doctor->mobile_phone ?>" required>
            </div>
            </div>

            <div class="form-group shadedform row">
            <label for="alt_mobile_phone_country" class ="col-form-label-sm col-sm-3">Alt Phone Ctry:</label>
            <div class="col-sm-3">
            <select class="SumoUnder" id="alt_mobile_phone_country" name="alt_mobile_phone_country">
            <?php
            $phonectrycodes = DatabaseFactory::getPhoneCountries($doctor->alt_mobile_phone_country, true, false);
            echo $phonectrycodes; 
            ?>

            </select>
            </div>

            <label for="alt_mobile_phone" class ="col-form-label-sm col-sm-3">Alt Phone:</label>
            <div class="col-sm-3">
            <input  type="text" class="form-control form-control-sm jqvalidphone" id="alt_mobile_phone" name="alt_mobile_phone" placeholder="Digits & Dashes" value="<?php echo $doctor->alt_mobile_phone ?>" >
            </div>
            </div>

            <div class="form-group shadedform row">
            <label for="email" class ="col-form-label-sm col-sm-3">email:</label>
            <div class="col-sm-3">
            <input  type="text" class="form-control form-control-sm jqvalidmyemail" id="email" name="email" value="<?php echo $doctor->email ?>" required>
            </div>

            <label for="specialty" class ="col-form-label-sm col-sm-3">Specialty:</label>
            <div class="col-sm-3">

            <select class="SumoUnder" id="specialty" name="specialty">
            <option disabled selected value="">
                Select option
            </option>

            <?php
             $selectlist = DatabaseFactory::getSpecialties();  // array of database data
                $selectitems = "";
                foreach ($selectlist as $item) {
                $selectitems.= '<option value="' . $item->cmscode . '"';
                if ($doctor->specialty == $item->cmscode) {
                $selectitems.= ' selected';
                }
                $selectitems.='>' . $item->specialty . '</option>';
                }
                echo $selectitems;
            ?>
            </select>

            </div>
            </div>

            <div class="form-group shadedform row">

            <label for="address_1" class="col-form-label-sm col-sm-3">
            Address1:
            </label>

            <div class="col-sm-4">
            <input class="form-control form-control-sm" id="address1" name="address1" type="text" placeholder="Enter address" style="display:block;" value = "<?php echo $doctor->address1 ?>">
            </div>

            <label for="city" class="col-form-label-sm col-sm-2">
            City:
            </label>

            <div class="col-sm-3">
            <input class="form-control form-control-sm" id="city" name="city" type="text" placeholder="Enter city" value = "<?php echo $doctor->city ?>">
            </div>

            </div>

            <div class="form-group shadedform row">

            <label for="address_2" class="col-form-label-sm col-sm-3">
            Address2:
            </label>
            <div class="col-sm-4">
            <input class="form-control form-control-sm" id="address2" name="address2" type="text" placeholder="Enter address" value = "<?php echo $doctor->address2 ?>">
            </div>


            <label for="state" class="col-form-label-sm col-sm-2">
            State:
            </label>


            <div class="col-sm-3">
            <select class="SumoUnder" id="state" name="state">
            <?php
            $fetchstates = DatabaseFactory::getStates($doctor->state);
            echo $fetchstates;
            ?>
            </select>
            </div>

            </div>

            <div class="form-group shadedform row">
            <label for="postal" class="col-form-label-sm col-sm-3">
            Postal Code:
            </label>
            <div class="col-sm-4">
            <input class="form-control form-control-sm" id="postal" name="postal" type="text" placeholder="Enter postal code" value = "<?php echo $doctor->postal ?>">
            </div>
            <label for="country" class="col-form-label-sm col-sm-2">
            Country:
            </label>
            <div class="col-sm-3">
            <select class="SumoUnder" id="country" name="country">
            <?php
            $fetchcountries = DatabaseFactory::getCountries($doctor->country );
            echo $fetchcountries;
            ?>
            </select>
            </div>
            </div>
            
            	<div class="form-group shadedform row">
		<label for="main_office_name" class="control-label col-sm-3">
			Main Office:
		</label>
		<div class="col-sm-4">
			<input class="form-control" id="main_office_name" name="main_office_name" type="text" placeholder="Main Office" value = "<?php echo $doctor->main_office_name ?>">
		</div>
		<label for="provider_type" class="control-label col-sm-2">
			Provider Type:
		</label>
		<div class="col-sm-3">
			<select class="SumoUnder" id="provider_type" name="provider_type">
			<?php
                $fetchtypes = DatabaseFactory::getProviderType($doctor->provider_type);
                echo $fetchtypes;
			?>
			</select>
		</div>
	</div>
	
		<div class="form-group shadedform row">

		<div class="col-sm-6">
		<label for="taxonomy" class="control-label">
			Taxonomy:
		</label>
			<select class="SumoUnder" id="taxonomy" name="taxonomy">
<?php
                $fetchtypes = DatabaseFactory::getTaxonomySelect($doctor->taxonomy);
                echo $fetchtypes;
			?>
			</select>
		</div>
		<div class="col-sm-3">
		<label for="dea_number" class="control-label">
			DEA:
		</label>
			<input class="form-control" id="dea_number" name="dea_number" type="text" placeholder="Enter DEA" value = "<?php echo $doctor->dea_number ?>">

		</div>
		
		<div class="col-sm-3">
		<label for="tax_id" class="control-label">
			Tax ID:
		</label>
			<input class="form-control" id="tax_id" name="tax_id" type="text" placeholder="Enter Tax ID" value = "<?php echo $doctor->tax_id ?>">

		</div>		
	</div>	

            <div class="form-group shadedform row">
            <label for="notes" class="col-form-label-sm col-sm-1">
            Notes:
            </label>
            <div class="col-sm-11">
            <textarea class="form-control form-control-sm" id="notes" name="notes" required value = "<?php echo $doctor->notes ?>"><?php echo $doctor->notes ?></textarea>
            </div>
            </div>
            <div class="form-group shadedform row">
            	<label for="reports_notification" class="col-form-label-sm col-sm-2" style="text-align:right">
				Report Notifications:
				</label>
				<div class="col-sm-2">
					<input style="width:auto;" type="checkbox" class="form-control" id="reports_notification" name="reports_notification" <?php echo ($doctor->reports_notification == 1)?"checked":"" ?> >
				</div>
				<div class="col-sm-8">
				</div>
				<label for="send_reports" class="col-form-label-sm col-sm-2" style="text-align:right">
					Send Reports:
				</label>
				<div class="col-sm-2">
					<input style="width:auto;" type="checkbox" class="form-control" id="send_reports" name="send_reports" <?php echo ($doctor->send_reports == 1)?"checked":"" ?> >
				</div>
				<div class="col-sm-8">
				</div>
            </div>
            </form><?php /**PATH /nginx-home/laravel/resources/views/referrers/referrers_demographics.blade.php ENDPATH**/ ?>