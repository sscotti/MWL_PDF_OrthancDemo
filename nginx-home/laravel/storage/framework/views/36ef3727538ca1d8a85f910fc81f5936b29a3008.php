<?php
use App\Models\Facility;
$facilityid = env('DEFAULT_FACILITY_ID');
(Auth::user())?$name = Auth::user()->name:$name = "";
(Auth::user())?$email = Auth::user()->email:$email = "";
?>
<!-- ======= Contact Us Section ======= -->
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo e(config('services.recaptcha.sitekey')); ?>" nonce= "<?php echo e(csp_nonce()); ?>"></script>
<section id="contact" class="contact">
<div class="container">
  <div class="section-title">
    <h2>Contact Us</h2>
  </div>
<!-- Begin Support Section -->
  <div class="row">

<div style ="font-size: .5em;font-weight: bold;" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2 m-2">
        <?php echo Facility::getMailingAddress($facilityid) ?>
        <?php echo Facility::getPhoneFAXEmailWeb($facilityid) ?><br>
    </div>


    <div class="col-lg-12 aos-init" data-aos="fade-up" data-aos-delay="300">
      <form method="post" class="php-email-form"  data-route = "/mail/webform" >
         <?php echo csrf_field(); ?>
        <div class="form-row">
          <div class="col-lg-6 form-group">
            <input value = "<?php echo $name ?>" type="text" name="name" class="form-control" id="name" placeholder="Your Name" data-rule="minlen:4" data-msg="Please enter at least 4 chars">
            <div class="validate"></div>
          </div>
          <div class="col-lg-6 form-group">
            <input value = "<?php echo $email ?>" type="email" class="form-control" name="email" id="email" placeholder="Your Email" data-rule="email" data-msg="Please enter a valid email">
            <div class="validate"></div>
          </div>
        </div>
        <div class="form-group">
          <input type="text" class="form-control" name="subject" id="subject" placeholder="Subject" data-rule="minlen:4" data-msg="Please enter at least 8 chars of subject">
          <div class="validate"></div>
        </div>
        <div class="form-group">
          <textarea class="form-control" name="message" rows="5" data-rule="required" data-msg="Please write something for us" placeholder="Message"></textarea>
          <div class="validate"></div>
        </div>
        <div class="form-group">
            <input type="hidden" name="recaptcha" id="recaptcha">
        </div>

        <div class="text-center">
          <button type="submit" id="btn_send" class = "uibuttonsmallred">Send Message</button>
          <div id = "grecaptcha-pp"></div>
        </div>
      </form>
    </div>
  </div>
</div>
</section>

<!-- End Contact Us Section -->

<script nonce= "<?php echo e(csp_nonce()); ?>">


$(".php-email-form").on("submit", function(e) {
    e.preventDefault();
    mailForm($(this));
});

 grecaptcha.ready(function() {
     grecaptcha.execute("<?php echo e(config('services.recaptcha.sitekey')); ?>", {action: 'contact'}).then(function(token) {
        if (token) {
          $("[name='recaptcha']").val(token);
          document.getElementById('recaptcha').value = token;
          $('.grecaptcha-badge').detach().appendTo("#grecaptcha-pp");
          $('.grecaptcha-badge').css({"position":"relative","right":"0px","left":"0px"});
        }
     });
 });
         
function mailForm(form) {
 
    $.ajax({

        type: "POST",
        url: form.data("route"),
        dataType: "json",
        data: form.serialize(),
        context: $(this),

    })
    .done(function(data, textStatus, jqXHR) {
        // For Captcha Errors
        $("body").removeClass("loading");
        if (data.message.success == false) {
            showMessage(data.message['error-codes']);
        }
        else if (data.error == true) {
            showMessage(data.message);
        }
        else {
            showMessage(data.message);
        }   
    });
 
}
</script><?php /**PATH /nginx-home/laravel/resources/views/includes/contactform.blade.php ENDPATH**/ ?>