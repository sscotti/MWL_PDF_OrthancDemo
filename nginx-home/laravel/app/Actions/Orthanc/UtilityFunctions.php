<?php
namespace App\Actions\Orthanc;
use \DB;
use \Debugbar;
use Illuminate\Support\Facades\Log;


//  THESE ARE SETUP TO BE CALLED STATICALLY, WITH THE ORTHNAC API PASSED IN AS AN ARGUMENT, ALONG WITH OTHER ARGS.

class UtilityFunctions  {

    public static function attachMIMEToStudy() {

// 		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// 		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// 		header("Cache-Control: no-store, no-cache, must-revalidate");
// 		header("Cache-Control: post-check=0, pre-check=0", false);
// 		header("Pragma: no-cache");

		/*
		// Support CORS
		header("Access-Control-Allow-Origin: *");
		// other CORS headers if any...
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			exit; // finish preflight CORS requests here
		}
		*/

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Uncomment this one to fake upload time
		// usleep(5000);

		// Settings
		$targetDir = config('myconfigs.PATH_PLUPLOAD_TEMP');
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 24 * 3600; // Temp file age in seconds, 1 day

		// Create target dir
		if (!file_exists($targetDir)) {
			@mkdir($targetDir);
		}

		// Get a file name
		if (isset($_REQUEST["name"])) {
			$fileName = $_REQUEST["name"];
		} elseif (!empty($_FILES)) {
			$fileName = $_FILES["file"]["name"];
		} else {
			$fileName = uniqid("file_");
		}

		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		// Chunking might be enabled
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


		// Remove old temp files
		if ($cleanupTargetDir) {
			if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}

			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

// 				If temp file is current file proceed to the next
// 				if ($tmpfilePath == "{$filePath}.part") {
// 					continue;
// 				}

				// Remove temp file if it is older than the max age and is not the current file
//				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
				if (filemtime($tmpfilePath) < time() - $maxFileAge) {

					@unlink($tmpfilePath);
				}
			}
			closedir($dir);
		}


		// Open temp file
		if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		if (!empty($_FILES)) {
			if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
			}

			// Read binary input stream and append it to temp file
			if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		} else {
			if (!$in = @fopen("php://input", "rb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
			}
		}

		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}

		@fclose($out);
		@fclose($in);

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {

			// Strip the temp .part suffix off
			rename("{$filePath}.part", $filePath);
			$file = file_get_contents($filePath);
			$JSONQuery = [];
			$JSONQuery["Tags"] = array(
				"Modality" =>"OT",
				"SeriesDescription"=> $fileName,
				"SOPClassUID"=>"1.2.840.10008.5.1.4.1.1.104.1"
				);
			$JSONQuery["Parent"] = $_REQUEST["parent"];
			$base64pdf = base64_encode ($file);
			$file_type  = mime_content_type($_FILES['file']['tmp_name']);
			Debugbar::error($file_type);
			$JSONQuery["Content"] = "data:" . $file_type . ";base64," . $base64pdf;
			$JSONQuery = json_encode($JSONQuery);
			$result = (new OrthancAPI())->executeCURLPOSTJSON($JSONQuery, "tools/create-dicom");
			Debugbar::error($result);
			die($result);
		}
		/*
		PHP message: {
			"Details" : "Not a PDF file",
			"HttpError" : "Bad Request",
			"HttpStatus" : 400,
			"Message" : "Bad file format",
			"Method" : "POST",
			"OrthancError" : "Bad file format",
			"OrthancStatus" : 15,
			"Uri" : "/tools/create-dicom"
		}"

		PHP message: {
		   "ID" : "96143209-3499a07d-786916b5-693eb54e-668be038",
		   "ParentPatient" : "4481916d-55b353cc-ae4cbad4-4c0e7cbd-33c0b045",
		   "ParentSeries" : "718249e3-9f7da3d9-ebacdbe1-fef59a67-4f84e63d",
		   "ParentStudy" : "660e7608-23e3eecb-da0a810a-c4e4619f-eb394b26",
		   "Path" : "/instances/96143209-3499a07d-786916b5-693eb54e-668be038",
		   "Status" : "Success"
		}
		*/
		// Return Success JSON-RPC response
		//die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

	}

    public static function downloadStudyUUID ($uuid, $command, $api) {

    	Log::info($uuid,);
    	Log::info($command);

    	if($command == "iso") {

    	    $result =  $api->executeCURL("studies/" . $uuid . '/media');
		    echo $result;
		}
        else {
//             $filename = "test.zip";
//             header("Content-type: application/zip");
//             header("Content-Disposition: attachment; filename=\"".$filename."\""); 
            $result = $api->executeCURL("studies/" . $uuid . '/archive');
             echo $result;
    	}

    }
}
?>
