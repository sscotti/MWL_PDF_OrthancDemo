<?php
use Spatie\Permission\Models\Permission;
use App\Models\Patients\Patients;
use App\Models\Referrers\ReferringPhysician;
use App\Actions\Orthanc\OrthancAPI;
$user = Auth::user();
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2 m-2">
                <div class = 'dashboardheader'>Profile Summary</div><img class="inline-block h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="" />
                
                <div class="form-group shadedform row">
                    <div class="col-sm-4">
                        <span>Name:  </span><?php echo $user->name ?>
                    </div>
                    <div class="col-sm-4">
                        <span>E-mail:  </span><?php echo $user->email ?>
                    </div>
                    <div class="col-sm-2">
                        <span>DOB:  </span><?php echo $user->dob ?>
                    </div>
                </div>
                <div class="form-group shadedform row">
                    <div class="col-sm-4">
                        <span>Last Name:  </span><?php echo $user->lname ?>
                    </div>
                    <div class="col-sm-4">
                        <span>First Name:  </span><?php echo $user->fname ?>
                    </div>
                    <div class="col-sm-4">
                        <span>Middle Name:  </span><?php echo $user->mname ?>
                    </div>
                </div>
                
<!-- 
                <div class="form-group shadedform row">
                    <div class="col-sm-10">
                        <span>Permissions:  </span><?php echo Auth::user()->getPermissionNames() ?>
                    </div>
                </div>
                <div class="form-group shadedform row">
                    <div class="col-sm-12">
                    <span>Roles:  </span><?php echo $user->user_roles ?> / <?php echo $user->getRoleNames(); ?>
                    </div>
                </div>
 -->
                @if (Auth::user()->getPermissionNames()->contains('patient_data'))
                    <div class="form-group shadedform row">
                    <div class="col-sm-12">
                    <span>PatientID:  </span><?php echo Auth::user()->patientid ?>
                    </div>
                    </div>
                @endif
                
                @if (Auth::user()->getPermissionNames()->contains('provider_data'))
                    <div class="form-group shadedform row">
                    <div class="col-sm-12">
                    <span>Doctor ID:  </span><?php echo Auth::user()->doctor_id ?>
                    </div>
                    </div>
                @endif
                
                @if (Auth::user()->getPermissionNames()->contains('reader_data'))
                    <div class="form-group shadedform row">
                    <div class="col-sm-12">
                    <span>Reader ID:</span><?php echo Auth::user()->reader_id ?>
                    </div>
                    </div>
                @endif
                
            </div>
            
            <?php $PACS = new OrthancAPI(); ?>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2 m-2">
                <div class = 'dashboardheader'>Studies Summary</div>
            
            @if (Auth::user()->getPermissionNames()->contains('provider_data'))
                <p>For ReferringPhysicianName <?php echo $user->doctor_id . ':  ' . $PACS->studyCountByReferringPhysicianName([$user->doctor_id . ':*'])[$user->doctor_id . ':*']; ?></p>
            @endif
            @if (Auth::user()->getPermissionNames()->contains('patient_data'))
                <p>For PatientID <?php echo $user->patientid . ':  ' . $PACS->studyCountByPatientId([$user->patientid])[$user->patientid]; ?></p>
            @endif
            </div>
            @if (Auth::user()->getPermissionNames()->contains('provider_data'))
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2 m-2">
                <div class = 'dashboardheader'>Orders Summary</div>
                <p>Order Count:  <?php echo ReferringPhysician::getOrderCount($user->doctor_id) ?></p>
            </div>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2 m-2">
                <div class = 'dashboardheader'>Requests Summary</div>
                <p>Active Requests:  <?php echo ReferringPhysician::getActiveRequests($user->doctor_id) ?></p>
            </div>
            @endif
            
            @if (Auth::user()->getPermissionNames()->contains('reader_data') || Auth::user()->getPermissionNames()->contains('admin_data') || Auth::user()->getPermissionNames()->contains('staff_data'))
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2 m-2">
                <div class = 'dashboardheader'>Statistics</div>
                <p>
                <?php
                $stats =  json_decode($PACS->getStatistics()); 
                echo '<ul><li>Total Patients:  ' . $stats->CountPatients . '</li><li>Total Studies:  ' . $stats->CountStudies . '</li></li><li>Total Images:  ' . $stats->CountInstances . '</li><li>Total Disk (MB):  ' . $stats->TotalDiskSizeMB . '</li></ul>';
                
                ?></p>
            </div>
            @endif



        </div>
    </div>
</x-app-layout>
<style>
.shadedform span {
width:80px;
display:inline-block;
}
.dashboardheader {
text-decoration:underline;
font-weight:bold;
}
</style>
