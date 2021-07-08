<?php
use App\Helpers\Widgets;
use App\Actions\Orthanc\OrthancAPI;
$pacs = new OrthancAPI();
?>
<?php if (isset($component)) { $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\AppLayout::class, []); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header'); ?> 
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Patient Study List')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

<div id="myNav" class="vieweroverlay"><a href="" class="closebtn"><i class="fas fa-window-close"></i></a><div id="dynamiciframe"></div></div>
<div id = "teleRadDivOverlayWrapper">
<button type="button" class="btn btn-primary btn-xs closeresults">Clear Viewer <span class = "spanclosex">x</span></button>
<button id="teleradClose" type="button" class="btn-primary btn-xs">Report Viewer - Editor <span class = "spanclosex">x</span></button>
<div id="APImonitor">
<div id="viewertools">
<button data-filename = "RadiologyReport.pdf" data-css = "report" data-content = "#reportnoheader" type="button" class="btn-primary btn-sm wkdownload" value="Download">Download</button>
<button data-filename = "RadiologyReport.pdf" data-css = "report" data-content = "#reportnoheader" type="button" class="btn-primary btn-sm wknewtab" value="Print">Print</button>

<form id="emailreport" style="display:inline-block;">
<label for="emailreport_to">E-mail to:  </label>
<input id="emailreport_to" name="emailreport_to" type="email" class = "jqvalidmyemail" value = "<?php echo !empty(Auth::user()->email)?Auth::user()->email:''?>">
<input type="submit" class="btn-danger btn-sm" value="Send Report">
</form>

</div>
<div id="apiresults"></div>
</div>
<div id="teleRadDivOverlay"></div>
</div>
<div style = "margin:auto;text-align:center;width:max-content;font-size:12px;">
<?php  echo $pacs->serverStatusWidget(); ?>
<?php  echo Widgets::PACSSelectorTool("studies"); ?>
</div>

<div id="delegator">

<div id="studylist">

    <?php echo Widgets::studiesSearchFormPatient() ?>
    <?php echo Widgets::dateRadioSelectorstudies("searchform", "#studieswrapper", "searchorthanc") ?>
    <?php echo Widgets::studyRowSelector() ?>
    <?php echo Widgets::studiesLengend() ?>
    <?php echo Widgets::studiesContainer() ?>

</div>
<!-- end of studies -->
</div>
<!-- end of delegator / wrapper -->
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
//var greetES6 = `Hello ${name}`;//using ticks
function template(study) {

	let html = '<!-- BEGIN STUDY --><div class="row divtable worklist" data-uuid="'+study.ID+'" data-studyinstanceuid="'+study.MainDicomTags.StudyInstanceUID+'" data-name="'+study.PatientMainDicomTags.PatientName+'" data-dob="'+study.dob+'" data-age="'+study.age+'" data-sex="'+study.sex+'" data-mrn="'+study.PatientMainDicomTags.PatientID+'" data-accession="'+study.MainDicomTags.AccessionNumber +'" data-description="'+study.MainDicomTags.StudyDescription+'" data-modality="'+study.modalities+'" data-images="'+study.imagecount+'" data-studydate="'+study.studydate+'" data-orthancstatus="'+study.stable+'" data-reportstatus="'+study.reportstatus+'" data-indication="'+study.indication + '" data-referring_physician="'+study.MainDicomTags.ReferringPhysicianName+'" data-billing_status="'+study.billingingstatus+'"><div class="col-sm-3 nopadding"><div class="col-sm-6"><span class="rowcounter">' + study.rowno + '</span><span class="narrowmedia">View: </span><a class="viewstudy" href="#" target="_blank"><img class="uiicons" src="/images/view_images.png" title="View"></a><span style="max-width: 50px;display: inline-block;text-overflow: ellipsis;overflow: hidden;">'+study.MainDicomTags.ReferringPhysicianName+'</span> <br><span class="narrowmedia">Name: </span><span data-toggle="tooltip" data-placement="top" title="' +study.PatientMainDicomTags.PatientName+ ', Doctor:  ' +study.MainDicomTags.ReferringPhysicianName+'">'+study.PatientMainDicomTags.PatientName+'</span></div><div class="col-sm-6"><span class="narrowmedia">DOB / Age: </span> <a href="#"><img class="latestHL7 uiiconslarge" src="/images/report.png" title="Reports"></a><span data-toggle="tooltip" data-placement="top" title="DOB / Age">' + study.dob + ' / ' + study.age + '</span> <div class="reportstatus">'+study.reportstatus+'</div></div></div><div class="col-sm-3 nopadding"><div class="col-sm-2"><span class="narrowmedia">Sex: </span><span>&nbsp;'+study.sex+'</span> </div><div class="col-sm-5"><span class="narrowmedia">Download:&nbsp;&nbsp;</span><a href="#"><span class="downloadiso_orthanc uibuttonsmallred">"DCM"</span></a><br><span class="narrowmedia">MRN: </span><span data-toggle="tooltip" data-placement="top" title="'+study.PatientMainDicomTags.PatientID+'">&nbsp;'+study.PatientMainDicomTags.PatientID+'</span></div><div class="col-sm-5"><span class="narrowmedia">Download:&nbsp;&nbsp;</span><a href="#"><span class="downloadzip_orthanc uibuttonsmallred">"ZIP"</span></a><br><span class="narrowmedia">Accession: </span><span data-toggle="tooltip" data-placement="top" title="'+study.MainDicomTags.AccessionNumber+ '">&nbsp;'+study.MainDicomTags.AccessionNumber+'</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-8"><span class="narrowmedia">Show All:</span> <a href="#" data-description="' +study.MainDicomTags.StudyDescription+ '" data-mrn="' +study.PatientMainDicomTags.PatientID+ '"><span class="narrowmedia">Description:</span><span>'+study.MainDicomTags.StudyDescription+'</span></div><div class="col-sm-2"><span class="narrowmedia">Modality: </span><span>'+study.modalities+'</span></div><div class="col-sm-2"><span class="narrowmedia">Images: </span><span>'+study.imagecount+'</span></div></div><div class="col-sm-3 nopadding"><div class="col-sm-4"><span class="showpatienthistory uibuttonsmallred">Patient History</span> <span class="narrowmedia">History: </span><span data-toggle="tooltip" data-placement="top" title="'+study.indication+'" style="width: auto;white-space: nowrap;left: 0px;bottom: 0px;text-overflow: ellipsis;overflow: hidden;display: block;">'+study.indication+'</span> </div><div class="col-sm-8" data-toggle="tooltip" data-placement="top" title="'+study.studydate+'"><span class="narrowmedia">Study Date: </span><span>'+study.studydate+'</span><br><span class="narrowmedia">Stable: </span><span>'+study.stable+'</span> </div></div></div><!-- BEGIN STUDY -->';
	return html;
}
</script>
 <?php if (isset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da)): ?>
<?php $component = $__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da; ?>
<?php unset($__componentOriginal8e2ce59650f81721f93fef32250174d77c3531da); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php /**PATH /nginx-home/laravel/resources/views/patientportal/studies.blade.php ENDPATH**/ ?>