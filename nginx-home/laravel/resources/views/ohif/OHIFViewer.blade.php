<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />

	<meta name="description" content="Open Health Imaging Foundation DICOM Viewer" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1,maximum-scale=1,user-scalable=no" />
	<meta name="theme-color" content="#000000" />
	<meta http-equiv="cleartype" content="on" />
	<meta name="MobileOptimized" content="320" />
	<meta name="HandheldFriendly" content="True" />
	<meta name="apple-mobile-web-app-capable" content="yes" />

	<!-- WEB FONTS -->
	<link href="https://fonts.googleapis.com/css?family=Sanchez" rel="stylesheet" />

	<title>OHIF Standalone Viewer</title>
</head>

<body>
	<noscript> You need to enable JavaScript to run this app. </noscript>

	<div id="root"></div>
<script src="https://unpkg.com/react@16.14.0/umd/react.production.min.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@16.14.0/umd/react-dom.production.min.js" crossorigin></script>
<script src="https://unpkg.com/@ohif/viewer@4.9.15/dist/index.umd.js" crossorigin></script>
  
  <script>
  
  
    var containerId = "root";
    var componentRenderedOrUpdatedCallback = function(){
      console.log('OHIF Viewer rendered/updated');
    }
window.config = {
  // default: '/'
  routerBasename: '/OHIFViewer',
  extensions: [],
  showStudyList: false,
  filterQueryParam: false,
  servers: {
    dicomWeb: [
      {
        name: 'ORTHANC',
        wadoUriRoot: '/pacs-1/wado',
        qidoRoot: '/pacs-1/dicom-web',
        wadoRoot: '/pacs-1/dicom-web',
        qidoSupportsIncludeField: false,
        imageRendering: 'wadors',
        thumbnailRendering: 'wadors'
      },
    ],
  },
  cornerstoneExtensionConfig: {},
};

window.OHIFViewer.installViewer(window.config, containerId, componentRenderedOrUpdatedCallback);

//https://portal.medical.ky/OHIFViewer/viewer/1.3.76.2.1.1.4.1.2.5388.669111771
    
	</script>
<style>
.header-brand {
display:none;
}
</body>
</html>
