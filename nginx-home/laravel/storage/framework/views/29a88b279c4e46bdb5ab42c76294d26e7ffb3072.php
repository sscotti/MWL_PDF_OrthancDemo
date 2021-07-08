<?php
use App\Models\Facility;
$facilityid = env('DEFAULT_FACILITY_ID');
?>
<style nonce="<?php echo e(csp_nonce()); ?>">	

footer {
position: sticky;
bottom: 0;
left: 0;
right: 0;
border: 2px solid;
z-index:10;
}

footer h5 {
font-size:12px;
}
footer li {
font-size:8px;
}
html {
visibility:visible;
}

footer a {
    display:inline-block !important;
    padding-right:10px;
    text-decoration:underline;
}
#copyright {
font-size:8px;
}
</style>
<footer class="bg-gray-100">
	<div class="container" style="position:relative;margin:0px !important;">
		<div class="flex flex-wrap">
<!-- 
			<div style="font-size: .5em;font-weight: bold;" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2 m-2">
<?php echo Facility::getMailingAddress($facilityid) ?>
<?php echo Facility::getPhoneFAXEmailWeb($facilityid) ?>
				<br>
				&copy; Copyright 2021 
			</div>
 -->
			<div style="height:max-content;width:max-content;margin:5px auto 0px auto !important;">
<!-- 
				<h5 class="uppercase mb-1 font-bold">
					Links
				</h5>
 -->
				<div>
					<a href="#" class="text-xs hover:underline text-gray-900 block">Support / Contact</a> <a href="#" class="text-xs hover:underline text-gray-900 block">About Us</a> <a href="#" target="_blank" class="text-xs hover:underline text-gray-900 block">FaceBook</a> <a href="/terms-of-service" class="terms-of-service text-xs hover:underline text-gray-900 block" target="_blank">Terms</a> <a href="/privacy-policy" class="privacy-policy text-xs hover:underline text-gray-900 block" target="_blank">Privacy</a><span id = "copyright"><?php echo Facility::getPropertyValue($facilityid, "name") . ' &copy;' .  Date("Y") ?></span>
				</div>
			</div>
		</div>
	</div>
</footer>
<script nonce="<?php echo e(csp_nonce()); ?>">

	$(function() {
	
	    $(document).ajaxComplete(function( event, request, settings ) {
	
	        $("body").removeClass("loading");
	        console.log("Request");
	        console.log(request);
	        console.log("settings");
	        console.log(settings);
	        console.log("event");
	        console.log(event);
	    });
	});
</script>
<?php /**PATH /nginx-home/laravel/resources/views/layouts/appfooter.blade.php ENDPATH**/ ?>