<?php
use App\Models\Patients\Patients;
use App\Models\Patients\Insurance;
use App\Helpers\DatabaseFactory;
use Illuminate\Support\Facades\Log;
// $patient = Patients::where('mrn', $mrn)->first();
// $avatar = Patients::getAvatar($mrn);
?>
<div style = "width:720px">

    <h4>Policy Holder</h4>
	<div class="form-group shadedform row">
		<label for="ins_fname" class="control-label col-sm-2">
			First name:
		</label>
		<div class="col-sm-2">
			<?php echo $data->ins_fname  ?>
		</div>
		<label for="ins_lname" class="control-label col-sm-2">
			Last name:
		</label>
		<div class="col-sm-2">
			<?php echo  $data->ins_lname  ?>
		</div>
		<label for="ins_mname" class="control-label col-sm-2">
			Middle name
		</label>
		<div class="col-sm-2">
			<?php echo  $data->ins_mname  ?>
		</div>
	</div>
	
	<div class="form-group shadedform row">
		<label required for="ins_birth_date"" class="control-label col-sm-2">
			DOB:
		</label>
		<div class="col-sm-2">
			<?php if ($data->ins_birth_date != '') echo date("Y-m-d", strtotime($data->ins_birth_date)) ?>
		</div>
		<label for="ins_sex" class="control-label col-sm-2">
			Sex:
		</label>
		<div class="col-sm-2">
            <?php echo $data->ins_sex ?>
		</div>

		<div class="col-sm-4">
		</div>
	</div>	
	
	<div class="form-group shadedform row">
		<label for="ins_mobile_phone" class="control-label col-sm-2">
			Phone:
		</label>

		<div class="col-sm-2">
            <?php echo $data->ins_mobile_phone_country ?> <?php echo $data->ins_mobile_phone ?>
		</div>

		<label for="ins_email" class="control-label col-sm-2">
			e-mail:
		</label>
		<div class="col-sm-2">
            <?php echo $data->ins_email ?>
        </div>
	</div>

	<div class="form-group shadedform row">
		<label for="ins_address_1" class="control-label col-sm-2">
			Address:
		</label>
		<label for="ins_address_2" class="control-label col-sm-0">
		</label>
		<div class="col-sm-4">
			<?php echo $data->ins_address_1 ?>
			<?php echo $data->ins_address_2 ?>
		</div>
		<div class="col-sm-6">

		</div>
	</div>
	
     <div class="form-group shadedform row">
            <label for="ins_city" class="control-label col-sm-2">
                City:
            </label>
            <div class="col-sm-2">
                <?php echo $data->ins_city ?>
            </div>
    </div>

	<div class="form-group shadedform row">
		<label for="ins_state" class="control-label col-sm-2">
			State:
		</label>
		<div class="col-sm-2">
		<?php echo $data->ins_state ?>
		</div>
		<label for="ins_country" class="control-label col-sm-2">
			Country:
		</label>

		<div class="col-sm-2">
		<?php echo $data->ins_country ?>
		</div>
		<label for="ins_postal" class="control-label col-sm-2">
			Postal Code:
		</label>
		<div class="col-sm-2">
		<?php echo $data->ins_postal ?>
		</div>
	</div>

	<div class="form-group shadedform row">
		<label for="carrier_id" class="control-label col-sm-2">
			Insurance Name:
		</label>
		<div class="col-sm-2">
            <?php echo $data->carrier_name ?>
		</div>
		<label for="member_id" class="control-label col-sm-2">
			Member ID:
		</label>
		<div class="col-sm-2">
		<?php echo $data->member_id ?>
		</div>
		<label for="group_id" class="control-label col-sm-2">
			Group ID:
		</label>
		<div class="col-sm-2">
		<?php echo $data->group_id ?>
		</div>
	</div>

	<div class="form-group shadedform row">
		<label for="effective_date" class="control-label col-sm-2">
			Effective Date:
		</label>
		<div class="col-sm-2">
		<?php echo $data->effective_date ?>
		</div>
		<label for="expiration_date" class="control-label col-sm-2">
			Expiration Date:
		</label>
		<div class="col-sm-2">
		<?php echo $data->expiration_date ?>
		</div>
		<label for="priority" class="control-label col-sm-2">
			Priority:
		</label>
		<div class="col-sm-2">
		<?php echo $data->priority ?>		
		</div>
	</div>
	
    <div class="form-group shadedform row">
		<label for="relationship" class="control-label col-sm-2">
			Relationship:
		</label>
		<div class="col-sm-2">
		<?php echo $data->relationship ?>	
		</div>
		<label for="plan_name" class="control-label col-sm-2">
			Plan Name:
		</label>
		<div class="col-sm-2">
		<?php echo $data->plan_name ?>	
		</div>

	</div>
	
	<div class="form-group shadedform row">
		<label for="co_pay_amount" class="control-label col-sm-2">
			Co-Pay Amount:
		</label>
		<div class="col-sm-2">
		<?php echo $data->co_pay_amount ?>
		</div>
		<label for="co_pay_percent" class="control-label col-sm-2">
			Co-Pay %:
		</label>
		<div class="col-sm-2">
		<?php echo $data->co_pay_percent ?>
		</div>
		<label for="deductible_amount" class="control-label col-sm-2">
			Deductible:
		</label>
		<div class="col-sm-2">
		<?php echo $data->deductible_amount ?>
		</div>

	</div>

</div>

