<?php if (isset($component)) { $__componentOriginalc3251b308c33b100480ddc8862d4f9c79f6df015 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\GuestLayout::class, []); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
<style  nonce = "<?php echo e(csp_nonce()); ?>">
section {
border: 1px solid;
padding: 10px;
border-radius: 10px;
margin-bottom: 10px;
background:white;
}
.section-title {
    font-size:20px;
    font-weight:bold;
    margin:10px 0px 20px 0px;

}
.tech_logos {
    height:60px;
    display:inline-block;
}
a:any-link, a:hover {
    color:#183651 !important;
    font-weight:bold !important;
    text-decoration:underline !important;

}
section .collapsed {
    font-weight:bold;
}

section [data-toggle="collapse"] .bx-chevron-down {
    display:inline;
    color:green;
    font-size:2em;
}

section [data-toggle="collapse"] .bx-chevron-up {
        display:none;
}

section [data-toggle="collapse"][aria-expanded="true"] .bx-chevron-down {
    display:none;
}

section [data-toggle="collapse"][aria-expanded="true"] .bx-chevron-up {
        display:inline;
        color:green;
        font-size:2em;
}
section a, section a:hover, section a:link {
color:black;
text-decoration:underline;
}

section ul {

font-size:14px;
list-style:disc;
list-style-position: inside;

}

.collapse.show p {
margin: 1em;
font-weight: bold;
color: forestgreen;
}
h2, h3, h4 {
    font-size:20px !important;
}
.member img, .doctor-view img {
    height:120px;

}
</style>
    <div class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">

        <?php if(!Auth::check()): ?>
            <div class="fixed top-0 right-0 sm:block" style = "background: white;opacity: 1;z-index: 2;padding: 5px;border: 2px solid black;">
                <!-- Logo -->
                <a href="/" style = "display: inline-block;">
                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'jetstream::components.application-mark','data' => ['class' => 'block h-9 w-auto']]); ?>
<?php $component->withName('jet-application-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'block h-9 w-auto']); ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                    <img style = "height:40px;" src="<?php echo e(asset('images/mylogo.png')); ?>">
                </a>

                <a href="<?php echo e(route('login')); ?>" class="text-sm text-gray-700 underline">Login</a>
                <a href="<?php echo e(route('register')); ?>" class="ml-4 text-sm text-gray-700 underline">Register</a>
            </div>
        <?php endif; ?>
        <?php if(Auth::check()): ?>
        
            <div class="fixed top-0 right-0 sm:block" style = "background: white;opacity: 1;z-index: 2;padding: 5px;border: 2px solid black;">
                <!-- Logo -->
                <a href="/" style = "display: inline-block;">
                    <?php if (isset($component)) { $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4 = $component; } ?>
<?php $component = $__env->getContainer()->make(Illuminate\View\AnonymousComponent::class, ['view' => 'jetstream::components.application-mark','data' => ['class' => 'block h-9 w-auto']]); ?>
<?php $component->withName('jet-application-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes(['class' => 'block h-9 w-auto']); ?>
<?php if (isset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4)): ?>
<?php $component = $__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4; ?>
<?php unset($__componentOriginalc254754b9d5db91d5165876f9d051922ca0066f4); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
                    <img style = "height:40px;" src="<?php echo e(asset('images/mylogo.png')); ?>">
                </a>
                <a href="<?php echo e(route('dashboard')); ?>" class="text-sm text-gray-700 underline">DashBoard</a>
            </div>
          
        <?php endif; ?>


            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <main id="main">
      <!-- ======= About Us Section ======= -->
      <section id="about" style = "margin-top:100px;">
        <div class="row">
            <div class="col-lg-8 col-md-12 about-content">
                <div id = "headerwrapper">
                <a href = "https://www.orthanc-server.com"><img class = "tech_logos" src = "/images/orthanc_logo.png"></a>
                <a href = "https://laravel.com"><img class = "tech_logos" src = "/images/laravel_logo.png"></a>
                <a href = "https://www.nginx.com/"><img class = "tech_logos" src = "/images/nginx_logo.png"></a>
                <div class="section-title">
                  <h1>Orthanc Portal Demo</h1>
                </div>
                <p>This is an open source project with resources maintained on GitHub and on DockerHub.  It is made available as a Docker package that is relatively easy to deploy in a short period of time for testing and development on a development server.  It integrates an instance of Orthanc available either through an NGINX reverse proxy (with authentication hooks), or directly via the localhost if the Docker Compose is setup to expose the DICOM and REST API ports, done by default and configurable in the docker-compose.yml file.</p>
                <p>The <a href = "https://github.com/sscotti" target = "_blank">GitHub Respositories are Here</a> and the <a href = "https://hub.docker.com/u/sdscotti" target = "_blank">Docker Hub Containers are Here.</a>
                </div>
                <div class="section-title">Click <a href = "/dashboard">Here</a> or on the DashBoard (Upper Right) for menus and summary.</div>
                <div id="faq" class="faq">
                  <div class="faq-list">
                    <ul>
                      <li data-aos="fade-up" class="aos-init aos-animate">
                        <a data-toggle="collapse" class="collapsed" href="#faq-list-1">
                        ADMIN -> Dev Tool for development Tools
                        <i class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-1" class="collapse" data-parent=".faq-list">
                          <p>PlaceHolder</p>
                        </div>
                      </li>

                      <li data-aos="fade-up" data-aos-delay="100" class="aos-init aos-animate">
                        <a data-toggle="collapse" href="#faq-list-2" class="collapsed">
                        ADMIN -> Back Panel for administration (license required)
                        <i class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-2" class="collapse" data-parent=".faq-list">
                          <p>PlaceHolder</p>
                        </div>
                      </li>


                      <li data-aos="fade-up" data-aos-delay="200" class="aos-init aos-animate">
                        <a data-toggle="collapse" href="#faq-list-3" class="collapsed">
                        Readers, Providers and Patients Menus.
                        <i class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-3" class="collapse" data-parent=".faq-list">
                          <p>PlaceHolder</p>
                        </div>
                      </li>


                      <li data-aos="fade-up" data-aos-delay="200" class="aos-init aos-animate">
                        <a data-toggle="collapse" href="#faq-list-4" class="collapsed">
                        Modality WorkList Under Development
                        <i class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-4" class="collapse" data-parent=".faq-list">
                         <p>PlaceHolder</p>
                        </div>
                      </li>


                      <li data-aos="fade-up" data-aos-delay="200" class="aos-init aos-animate">
                        <a data-toggle="collapse" href="#faq-list-5" class="collapsed">
                        MPPS Server Development Under Development
                        <i class="bx bx-chevron-down icon-show"></i><i class="bx bx-chevron-up icon-close"></i>
                        </a>
                        <div id="faq-list-5" class="collapse" data-parent=".faq-list">
                        <p>PlaceHolder</p>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
        </div>
      </section>

    <!-- ======= Contact Us Section ======= -->
        <?php echo $__env->make('includes.contactform', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> 
      
<!-- ======= Google map ======= -->
<div>
<!-- <iframe src=""></iframe> -->
</div>
<!-- End Google map Section -->
    </main>
            </div>
        </div>

 <?php if (isset($__componentOriginalc3251b308c33b100480ddc8862d4f9c79f6df015)): ?>
<?php $component = $__componentOriginalc3251b308c33b100480ddc8862d4f9c79f6df015; ?>
<?php unset($__componentOriginalc3251b308c33b100480ddc8862d4f9c79f6df015); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php /**PATH /nginx-home/laravel/resources/views/welcome.blade.php ENDPATH**/ ?>