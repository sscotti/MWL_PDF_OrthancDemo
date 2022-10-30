window.config = {

    routerBasename: '/',
    showStudyList: false,
    extensions: [],
    filterQueryParam: false,
    servers: {
      dicomWeb: [
        {
          name: 'Orthanc',
          wadoUriRoot: '/orthanc/wado',
          qidoRoot: '/orthanc/dicom-web',
          wadoRoot: '/orthanc/dicom-web',
          imageRendering: 'wadors',
          thumbnailRendering: 'wadors',
          enableStudyLazyLoad: true,
          supportsFuzzyMatching: true,
        },
      ],
    },
    maxConcurrentMetadataRequests: 10,
  };