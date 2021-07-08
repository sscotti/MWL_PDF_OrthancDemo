<?php
use Illuminate\Support\Facades\Log;
use App\Models\Facility;
// Need to change this to choose the active facility if different
$facility = config('myconfigs.DEFAULT_FACILITY_ID');
$facility = Facility::where('id', $facility)->first();
$base64 = $facility->facilitylogo_dataurl;
$address = !empty($facility->address_2)?$facility->address_1.'<br>'.$facility->address_2:$facility->address_1;
$city_state = ($facility->state != 'OS')?$facility->city.', '.$facility->state:$facility->city;

?>

<x-email-layout>

    <div id = "wrapper" style = "width:640px;border-radius:10px;border:1px solid black;margin:0px auto 10px auto;padding:10px;">
    <div id = "headerwrapper" style = "padding:10px;font-size:0.8em;">
    <div id = "logo" style = "display:inline-block;vertical-align:top;"><img src = "<?php echo $base64 ?>" ></div>
    <div id = "headerleft"  style = "display:inline-block;width:200px;margin-left:10px;"><?php echo $facility->name ?><br><?php echo $address ?><br><?php echo $city_state  ?> <?php echo $facility->postal_code  ?><br><?php echo $facility->country  ?><br><?php echo $facility->phone_ctry  ?> <?php echo $facility->phone ?><br><a href = "<?php echo $facility->website ?>" target = "_blank"><?php echo $facility->website ?></a><br><a href="mailto:<?php echo $facility->email ?>"><?php echo $facility->email ?></a>
    </div>
    </div>
    <div id = "greeting">TO:  <?php echo $data['email'] ?><br>RE:  <?php echo $data['subject'] ?></div>
    <div id = "message" style = "margin:10px;">We have received your message:<p><?php echo $data['message'] ?></p></div>
    <div id = "saluation">Thank you</div>
    <div id = "footer"><?php echo $facility->name ?></div>
    </div>
    
</x-email-layout>
