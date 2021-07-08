<?php
// Takes the doctor_id, the identifier xxxx and generates the html for the list of licenses.
use App\Models\Referrers\ReferringPhysician;
use App\Helpers\DatabaseFactory;
?>

<div id = "licenselist">
<?php
$licenses = ReferringPhysician::getLicenses($doctor_id);
foreach ($licenses as $license) {
?>

    <form class="form-group row shadedform editableform" role="form" data-action="/provider_licenses/edit" data-key = "license_id" data-keyvalue = "<?php echo $license->license_id ?>">
        <input  type="hidden" class="form-control extrafield" name="license_id" readonly value="<?php echo $license->license_id ?>">
    <!--    <input  type="hidden" class="form-control extrafield" name="action" readonly value="edit">-->
        <input  type="hidden" class="form-control extrafield" name="license_provider_identifier" readonly value="<?php echo $license->license_provider_identifier ?>">
        <label for="license_type" class="control-label col-sm-1" style="cursor:pointer">
            Type: <i class="far fa-trash-alt deletelicense"></i>
        </label>
        <div class="col-sm-2">
            <select class="SumoUnder" name="license_type"><?php $fetchtypes = DatabaseFactory::getlicenseTypes($license->license_type);echo $fetchtypes;?></select>
        </div>

        <label for = "license_number" class="control-label col-sm-1">
            Number:
        </label>
        <div class="col-sm-2">
            <input class="form-control" id="license_number" name="license_number" type="text" placeholder="Enter Number" value = "<?php echo $license->license_number ?>">
        </div>


        <label for = "license_state" class="control-label col-sm-1">
            License State:
        </label>
        <div class="col-sm-2">
            <select class="SumoUnder" id="license_state" name="license_state"><?php $fetchstates = DatabaseFactory::getStates($license->license_state);echo $fetchstates;?>
            </select>
        </div>

        <label for = "license_country"  class="control-label col-sm-1">
            License Country:
        </label>
        <div class="col-sm-2">
            <select class="SumoUnder" name="license_country"><?php $fetchcountries = DatabaseFactory::getCountries($license->license_country);echo $fetchcountries;?></select>
        </div>
    </form>
<?php
}
?>
</div><?php /**PATH /nginx-home/laravel/resources/views/referrers/referrers_licenses.blade.php ENDPATH**/ ?>