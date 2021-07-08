// Global variables

let picker = document.getElementById('picker');
let listing = document.getElementById('listing');
let progress_text = document.getElementById('progress_text');
let preprocess_notice = document.getElementById('preprocess_notice');
let results = document.getElementById('uploadresults');
let box = document.getElementById('box');
let elem = document.getElementById("myBar");
let loader = document.getElementById("loader");
let displayafterinit = document.getElementById("displayafterinit");
var requestcounter;
var responsecounter;
var total;
// var excludedextensions = [".exe",".zip",".pdf",".jpg",".jpeg",".png",".gif",".doc",".docx", ".xml"];
var parsedepoch;
var datetimestamp;
var skipotherrequests;
var counts;
var UUIDs = [];

function checkDicomMime(file) {

  const fileReader = new FileReader();
  return new Promise((resolve, reject) => {
	var blob = file.slice(0, 132); //read the first few bytes to get the header
	fileReader.readAsArrayBuffer(blob);
    //fileReader.readAsArrayBuffer(file);
    fileReader.onload = function(event) {

      const target = event.target;
      const array = new Uint8Array(target.result);
      const start = 128
      const end = 132;
      const str = [...array.slice(128, 132)].map(value => String.fromCharCode(value)).join('');

      const result = {
        file,
        fileName: file.name,
        isBadFile: true
      }

      if (str == "DICM") {
        result.isBadFile = false;
        counts.process++;
      }
      else {
      	counts.omit++;
      }

      fileReader.onload = null;
      resolve(result);
    }
  });
}


picker.addEventListener('change', async event => {

	results.innerHTML = "";
	// Reset previous upload progress
    elem.style.width = "0px";
    listing.innerHTML = "";

    // Display image animation
    displayafterinit.style.display = "block";
    loader.style.display = "block";
    preprocess_notice.innerHTML = "";
    //
	counts = {
		process:0,
		omit:0
	};
	requestcounter = 0;
	responsecounter = 0;
	total = 0;
	skipotherrequests = 0;
	const parsedepoch = new Date().toISOString().match(/(\d{4}\-\d{2}\-\d{2})T(\d{2}:\d{2}:\d{2})/);
	const datetimestamp = parsedepoch[1] + "-" + parsedepoch[2].replace(/:/g, "-");
	//alert(datetimestamp);
	picker.setAttribute('data-timestamp', datetimestamp);

	// Reset previous upload progress
    elem.style.width = "0px";
    listing.innerHTML = "";


	let files = Array.from(picker.files);

    const processList = await Promise.all(files.map(file => checkDicomMime(file)));

	processList.sort((prev, next) => {

    	return prev.fileName > next.fileName;
  	});

  	const badlist = processList.filter(({
      isBadFile
    }) => isBadFile)
    .reduce((acc, result) => acc += '<li>' +result.fileName + '</li>', '')

  	total = counts.process;
  	if (counts.omit > 0) preprocess_notice.innerHTML = '<div style = "color:red;">Omitting ' + counts.omit + ' file(s) that did not pass criteria"</div><ol>' + badlist + '</ol>';

    for (let result of processList) {
		const file = result.file;
		const type = file.type;
		//console.log(result);
		if (!result.isBadFile) {
		//console.log('sending file', file)
		sendFile(file, datetimestamp, total, type);
		}
	}

});

statusitem = function(file, status) {

	let html = '<li><span>' + file.name + '</span><span>' + file.size + ' bytes</span><span>' + file.type + '</span><span>' + status + '</span></li>';
	return html;
}

// Function to send a file, call PHP backend


var sendFile = function(file, timestamp, total, type) {

        //console.log(file);
        const formData = new FormData();
        // Set post variables
        requestcounter = requestcounter + 1;
        formData.set('method', "UploadFolder"); // One object file
        formData.set('timestamp', timestamp); // One object file
        formData.set('counter', requestcounter);
        formData.set('total', total);
        formData.set('type', type);
        formData.set('webkitpath', file.webkitRelativePath); // One object file
        formData.set('file', file); // One object file
        formData.set('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content')); // One object file
        formData.set('anonymize', $("#anonymize").is(":checked")); // One object file
        formData.set('altertags', $("#altertags").is(":checked")); // One object file
        $('#edited_tag_inputs [name = "taglistname[]"]').each(function( index ) {
            formData.set($( this ).val(), $('#edited_tag_inputs [name = "taglistvalue[]"]')[index].value); // One object file
        });
        console.log(file);

        $.ajax({

//             headers: {
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
//             },
            beforeSend: function(e) {
                $("body").addClass("loading");
            },
            type: "POST",
            processData: false,
            contentType: false,
            url: '/PACSUploadStudies/PACSupload',
            dataType: "json",
            data: formData,
        })
        .done(function(data, textStatus, jqXHR) {

            progress_text.innerHTML = file.name + " (" + (responsecounter + 1) + " of " + total + " ) ";
            // Add file name to list
            item = statusitem(data.file, data.file.status);
            listing.insertAdjacentHTML('beforeend', item);
            listing.scrollTop = listing.scrollHeight;

            responsecounter++;
            if (!UUIDs.includes(data.UUID)) UUIDs.push(data.UUID); // array of study uuids for client side.
            // progress_text.innerHTML = request.response.file.name + " (" + responsecounter + " of " + total + " ) ";

            // Show percentage
            box.innerHTML = Math.min(responsecounter / total * 100, 100).toFixed(2) + "%";

            // Show progress bar
            elem.innerHTML = Math.round(responsecounter / total * 100, 100) + "%";
            elem.style.width = Math.round(responsecounter / total * 100) + "%";

            if (responsecounter == total) {

                console.log(UUIDs);
                progress_text.innerHTML = "Sending " + total + " file(s) is done!";
                loader.style.display = "none";
                // Send another request to intitiate processing and getting the report.
                getFinalResults();

            }
//              if (data.file.status == "Done") {
//
//                 getFinalResults();
//                 console.log(request.response);
//                 $("#uploadresults").html(request.response.results);
//                 showuploaderModal("Done","Click on Upload Summary to see Results");
//             }

        });

}

function getFinalResults() {

    data = {};
    data.FLAG = "FINISH";
    data._token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    data.UUIDs = UUIDs;
    $.ajax({
        beforeSend: function(e) {
            $("body").addClass("loading");
        },
        type: "POST",
        url: '/PACSUploadStudies/PACSupload',
        dataType: "json",
        data: data,
    })
    .done(function(data, textStatus, jqXHR) {
        $("#uploadresults").html(data.results);
        showuploaderModal("Done","Click on Upload Summary to see Results");
    });

}
