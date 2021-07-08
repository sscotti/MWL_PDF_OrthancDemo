<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Document List') }}
        </h2>
    </x-slot>
    <hr>
		<div id="filelist">
		<h4 style = "text-align:center;margin:auto;">File List for Patient MRN  <?php echo basename($dir_path) ?></h4><hr>
		<div class = "row"><div class = "type col-sm-4">Name</div><div class = "type col-sm-2">Type</div><div class = "type col-sm-6">Action</div></div>
		<?php 
        if ($dir_path != Storage::disk('patients')->getAdapter()->getPathPrefix() . Auth::user()->patientid) {
            echo '<div class = "row"><div class = "type col-sm-4">..</div><div class = "type col-sm-2"></div><div class = "type col-sm-6"><button data-path = "" type="button" style = "font-size:9px;" class="uibuttonsmallred openfolder">Up</button></div></div>'; 
        }
   
		foreach ($filelist as $key => $value) {

			if ($key != ".DS_Store" ) {
			
            echo '<div class = "row"><div class = "type col-sm-4">' . $key . '</div>';
            if ($value['type'] == "dir") $icon = '<i class="fas fa-folder-open"></i>';
            if ($value['type'] == "file") $icon = '<i class="fas fa-file"></i> - ' .  $value['mimetype'];
            echo '<div class = "type col-sm-2">' . $icon . '</div>';
        
            if ($value['type'] == "file") {
            echo '<div class = "type col-sm-6"><button data-file = "' .$dir_path.DIRECTORY_SEPARATOR.$key.'" type="button" style = "font-size:9px;" class="uibuttonsmallred viewwidgetdoc">View Document</button>';
            echo '<button data-file = "' .$dir_path .DIRECTORY_SEPARATOR.$key.'" type="button" style = "font-size:9px;" class="uibuttonsmallred downloadwidgetdoc">Download Document</button>';
    // 		if (!self::$omitdelete) {
    // 		$html .= '<button data-callback = "' . $callback. '" data-path = "' . $path . $key. '" type="button" style = "font-size:9px;" class="uibuttonsmallred deletewidgetdoc">Delete Document</button>';
    // 		}
            echo '</div></div>';
            }
            // Folder
            else {
                echo '<div class = "type col-sm-6"><button data-folder = "' .$dir_path .DIRECTORY_SEPARATOR.$key. '" type="button" style = "font-size:9px;" class="uibuttonsmallred openfolder">Open</button></div></div>';
            }
            }
            
        }
		echo '</div>';

        ?>
    
<x-myjs /> 

 <script nonce= "{{ csp_nonce() }}">

</script>   

<style>
	
</style>

</x-app-layout>