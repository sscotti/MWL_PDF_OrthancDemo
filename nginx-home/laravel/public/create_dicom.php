<!--

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/dark-hive/jquery-ui.min.css" integrity="sha512-Sg7rco5qUS4Y3wzqjxMhM5zj3DJuxnJAildKRzTIJeIdV8xSlIlFgngli3eOBq6Yv0kbz6IjdCvIpqu54d2YIA==" crossorigin="anonymous" />
 -->
<link type="text/css" rel="stylesheet" href="js/create_dicom/css/jquery.ui.plupload.css" media="screen" />


<!-- <link rel="stylesheet" href="../css/mycss.css" /> -->
<!--
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" ></script>
 -->
<script type="text/javascript" src="js/create_dicom/js/plupload.full.min.js" charset="UTF-8"></script>
<!--
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" ></script>
 -->
<script type="text/javascript" src="js/create_dicom/js/jquery.ui.plupload.min.js" charset="UTF-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js" integrity="sha512-Z8CqofpIcnJN80feS2uccz+pXWgZzeKxDsDNMD/dJ6997/LSRY+W4NmEt9acwR+Gt9OHN0kkI1CTianCwoqcjQ==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.worker.min.js" integrity="sha512-lHibs5XrZL9hXP3Dhr/d2xJgPy91f2mhVAasrSbMkbmoTSm2Kz8DuSWszBLUg31v+BM6tSiHSqT72xwjaNvl0g==" crossorigin="anonymous"></script>


<!--
<script type="text/javascript" src="https://www.plupload.com/plupload/js/plupload.full.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="https://www.plupload.com/plupload/js/jquery.plupload.queue/jquery.plupload.queue.min.js" charset="UTF-8"></script>

<script type="text/javascript" src="https://www.plupload.com/js/bootstrap.js" charset="UTF-8"></script>
<script type="text/javascript" src="https://www.plupload.com/js/shCore.js" charset="UTF-8"></script>
<script type="text/javascript" src="https://www.plupload.com/js/shBrushPhp.js" charset="UTF-8"></script>
<script type="text/javascript" src="https://www.plupload.com/js/shBrushjScript.js" charset="UTF-8"></script>
 -->

	<div id="uploader" style = "display:none;">
		<p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
	</div>
	<button id = "clearlist" class = "btn btn-primary">Clear List</button>
	<button id = "togglelog" class = "btn btn-primary">Toggle Log</button>
	<div id = "statuslog" style = "display:none;">
	<div>Just a demo.  Should be able to attach pdf's, jpg, gif, png and maybe mp4 ?</div>
	<div><span>Parent UUID:  </span><input style="width: 400px;" id="parentuuid", name = "parentuuid" value = "660e7608-23e3eecb-da0a810a-c4e4619f-eb394b26"></div>
	<div>86e1a292-07ffb90a-50b2d752-3074f6bd-6895e89a</div>
	<pre id="log" style="height: 300px; overflow: auto"></pre>
	</div>

<script>

// You should always specify the workerSrc explicitly, i.e. by setting pdfjsLib.GlobalWorkerOptions.workerSrc before calling pdfjsLib.getDocument, since the fallback is only a best effort solution which is not guaranteed to work correctly in every situation.
var pdfjsLib = window['pdfjs-dist/build/pdf'];
pdfjsLib.GlobalWorkerOptions.workerSrc = '//cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.worker.min.js';
// pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';

function loadCanvas(element) {
	var canvas = document.createElement('canvas');
	canvas.id     = "CursorLayer";
	canvas.width  = 240;
	canvas.height = 240;
	canvas.style.zIndex   = 8;
	canvas.style.position = "relative";
	canvas.style.border   = "1px solid";
	canvas.style.margin   = "10px";
	element.innerHTML = "";
	element.appendChild(canvas);
	return canvas;
}

function initLoader() {

    $("#uploader").plupload({

        // General settings
        runtimes : 'html5,html4',
        url : "/upload.php",

        // Maximum file size
        max_file_size : 0,
        max_file_count: 1,
        prevent_duplicates: true,
        thumb_width: 120,
    	thumb_height: 120,

        // chunk_size: "4mb",

        /* Resize images on clientside if we can
        resize : {
            width : 360,
            height : 360,
            quality : 90,
            crop: true // crop to exact dimensions
        },
        */

        // Specify what files to browse for
        filters : [
            {title : "Image files", extensions : "jpg,gif,png"},
            {title : "Zip files", extensions : "zip,avi,mp4"},
            {title : "PDF files", extensions : "pdf"}
        ],

        // Rename files by clicking on their titles
        rename: true,

        // Sort files
        sortable: true,

        // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
        dragdrop: true,

        // Views to activate
        views: {
            list: true,
            thumbs: true, // Show thumbs
            active: 'thumbs'
        },

        // PreInit events, bound before any internal events
		preinit : {
			Init: function(up, info) {
				log('[Init]', 'Info:', info, 'Features:', up.features);
								$("#clearlist").on("click", function(e) {
					handle.splice();
				});
				$(".plupload_logo").css({"background":"none"});
				$(".plupload_header_text").html("<div>Select File for Upload, .pdf, .png, .jpg, .gif, .mp4</div><div>You can change title by clicking on name and hitting return when done.</div>");
					$('[data-view=thumbs],[data-view=list]').on("click", function(e) {
		$('[data-view=thumbs],[data-view=list]').removeClass('ui-checkboxradio-checked');
		$('[data-view=thumbs],[data-view=list]').removeClass(' ui-state-active');
		$(this).addClass('ui-checkboxradio-checked');
		$(this).addClass(' ui-state-active');
		$(".ui-checkboxradio-icon").css({"background":"white"});
		$("#uploader").show();
	});
			},

			UploadFile: function(up, file) {
				log('[UploadFile]', file);

				// You can override settings before the file is uploaded
                // up.setOption('url', 'upload.php?id=' + file.id);
                // up.setOption('multipart_params', {param1 : 'value1', param2 : 'value2'});
			}
		},

		// Post init events, bound after the internal events
		init : {

			PostInit: function() {
				// Called after initialization is finished and internal event handlers bound
				//log('[PostInit]');
				handle = this;
// 				document.getElementById('uploadfiles').onclick = function() {
// 					uploader.start();
// 					return false;
// 				};
			},

			Browse: function(up) {
                // Called when file picker is clicked
                //log('[Browse]');
            },

            Refresh: function(up) {
                // Called when the position or dimensions of the picker change
                log('[Refresh]');
            },

            StateChanged: function(up) {
                // Called when the state of the queue is changed
                //log('[StateChanged]', up.state == plupload.STARTED ? "STARTED" : "STOPPED");
            },

            QueueChanged: function(up) {
                // Called when queue is changed by adding or removing files
                //log('[QueueChanged]');
            },

			OptionChanged: function(up, name, value, oldValue) {
				// Called when one of the configuration options is changed
				log('[OptionChanged]', 'Option Name: ', name, 'Value: ', value, 'Old Value: ', oldValue);
			},

			BeforeUpload: function(up, file) {
				// Called right before the upload for a given file starts, can be used to cancel it if required
				this.setOption("multipart_params", {parent: '<?php echo $_POST["parent"] ?>'});
				//alert("change to base64");
				log('[BeforeUpload]', 'File: ', file);
			},

            UploadProgress: function(up, file) {
                // Called while file is being uploaded
                //log('[UploadProgress]', 'File:', file, "Total:", up.total);
            },

			FileFiltered: function(up, file) {
				// Called when file successfully files all the filters
                log('[FileFiltered]', 'File:', file);
			},

            FilesAdded: function(up, files) {
                // Called when files are added to queue
                log('[FilesAdded]');

                plupload.each(files, function(file) {
                    //log('  File:', file.id);
                    if(file.type == "application/pdf"){
                    var fileReader = new FileReader();
                    fileReader.onload = function() {

						var pdfData = new Uint8Array(this.result);
						// Using DocumentInitParameters object to load binary data.
						var loadingTask = pdfjsLib.getDocument({data: pdfData});
						loadingTask.promise.then(function(pdf) {
							console.log('PDF loaded');

							// Fetch the first page
							var pageNumber = 1;
							pdf.getPage(pageNumber).then(function(page) {
							// console.log(page._pageInfo.view);
							scale = 120 / (Math.max(page._pageInfo.view[2], page._pageInfo.view[3])) ;
							console.log('Page loaded');
							var viewport = page.getViewport({scale: scale});
							// Prepare canvas using PDF page dimensions
							let thumbcontainer = $('[id=' + file.id + ']').find(".plupload_file_dummy");
							var canvas = loadCanvas(thumbcontainer[0]);
							var context = canvas.getContext('2d');
							// Render PDF page into canvas context
							var renderContext = {
							canvasContext: context,
							viewport: viewport
							};
							var renderTask = page.render(renderContext);
							renderTask.promise.then(function () {
							console.log('Page rendered');
							});
						});
						}, function (reason) {
						// PDF loading error
						console.error(reason);
						});
					};
					fileReader.readAsArrayBuffer(file.getNative());
					}
                });

            },

            FilesRemoved: function(up, files) {
                // Called when files are removed from queue
                log('[FilesRemoved]');

                plupload.each(files, function(file) {
                    log('  File:', file);
                });
            },

            FileUploaded: function(up, file, info) {
                // Called when file has finished uploading
                log('[FileUploaded] File:', file, "\nInfo:", info);
            },

            ChunkUploaded: function(up, file, info) {
                // Called when file chunk has finished uploading
                log('[ChunkUploaded] File:', file, "Info:", info);
            },

			UploadComplete: function(up, files) {
				// Called when all files are either uploaded or failed
                log('[UploadComplete]');
			},

			Destroy: function(up) {
				// Called when uploader is destroyed
                log('[Destroy] ');
			},

            Error: function(up, args) {
                // Called when error occurs
                log('[Error] ', args);
            }
		}
    });

}

function convertToBase64() {
	//Read File
	var selectedFile = document.getElementById("inputFile").files;
	//Check File is not Empty
	if (selectedFile.length > 0) {
		// Select the very first file from list
		var fileToLoad = selectedFile[0];
		// FileReader function for read the file.
		var fileReader = new FileReader();
		var base64;
		// Onload of file read the file content
		fileReader.onload = function(fileLoadedEvent) {
			base64 = fileLoadedEvent.target.result;
			// Print data in console
			console.log(base64);
		};
		// Convert data to base64
		fileReader.readAsDataURL(fileToLoad);
	}
}

function log() {
		var str = "";

		plupload.each(arguments, function(arg) {
			var row = "";

			if (typeof(arg) != "string") {
				plupload.each(arg, function(value, key) {
					// Convert items in File objects to human readable form
					if (arg instanceof plupload.File) {
						// Convert status to human readable
						switch (value) {
							case plupload.QUEUED:
								value = 'QUEUED';
								break;

							case plupload.UPLOADING:
								value = 'UPLOADING';
								break;

							case plupload.FAILED:
								value = 'FAILED';
								break;

							case plupload.DONE:
								value = 'DONE';
								break;
						}
					}

					if (typeof(value) != "function") {
						row += (row ? ', ' : '') + key + '=' + value;
					}
				});

				str += row + " ";
			} else {
				str += arg + " ";
			}
		});

		var log = $('#log');
		log.append(str + "\n");
		log.scrollTop(log[0].scrollHeight);
	}
	$("#togglelog").on("click", function(e) {
		$("#statuslog").toggle();

	});

</script>
