#!/usr/bin/python3
# -*- coding: utf-8 -*-
# Scripts Created and Maintained by:  Stephen D. Scotti, M.D., sscotti@sias.dev

# 1. Large number of packages and PIP modules here as below.  These need to be also installed on the host or Docker system so they can be imported.
# 2. https://book.orthanc-server.com/plugins/python.html


import inspect
import time
import numbers
import json
import io
from io import BytesIO
import orthanc
import base64 # part of python, https://docs.python.org/3/library/base64.html
import pdfkit # https://pypi.org/project/pdfkit/, sudo python3 -m pip install pdfkit
from pdfkit import configuration
from pdfkit import from_string
import imgkit # sudo python3 -m pip install imgkit
import pydicom # https://github.com/pydicom/pydicom, sudo python3 -m pip install pydicom
from pydicom.datadict import dictionary_keyword
from pydicom import dcmread, dcmwrite
from pydicom.filebase import DicomFileLike
from pydicom.dataset import Dataset, FileDataset, FileMetaDataset
from pydicom.uid import ExplicitVRLittleEndian, generate_uid
import hl7 # https://python-hl7.readthedocs.io/en/latest/

import re
import platform
import logging
import os
import sys
import smtplib
import ssl
from email.mime.text import MIMEText
import subprocess
import shutil
import mysql.connector #  sudo python3 -m pip install mysql-connector-python  https://pypi.org/project/mysql-connector-python/
# import the psycopg2 database adapter for PostgreSQL
import psycopg2 # RUN pip3 install psycopg2, https://www.psycopg.org/docs/install.html#quick-install
import datetime
from datetime import datetime
import requests # for sending CURL to Orthanc endpoint, https://www.w3schools.com/python/module_requests.asp, https://www.w3schools.com/python/ref_requests_post.asp, sudo python3 -m pip install requests
import pprint # pretty printer
import zipfile
from zipfile import ZipFile, ZipInfo


def get_current_datetimestring():
    # datetime object containing current date and time
    now = datetime.now()
    dt_string = now.strftime("%Y.%m.%d-%H.%M.%S")
    return dt_string


logging.basicConfig(filename='/etc/orthanc/logs/' + get_current_datetimestring() + '_custom.log', level=logging.DEBUG, format ='%(asctime)s | %(name)s | %(levelname)-8s | %(message)s') #  encoding='utf-8' only added in python v 3.9
logging.info('Date/Time|Name|Level|CATEGORY|SUBCATEGORY|MESSAGE')

# Get the Config Items from the orthanc.json file, used mostly for the RISDB settings and the WORKLIST_DIR, which is actually just the default location in Docker, mapped to some directory on the host system when using Docker.
ORTHANC_CONFIG = json.loads(orthanc.GetConfiguration())

#     "Worklists" : {
# 
#         "Enable": false,
#         "Database": "/var/lib/orthanc/worklists",
#         "FilterIssuerAet": false, // Some modalities do not specify 'ScheduledStationAETitle (0040,0001)'
#         // in the C-Find and may receive worklists not related to them.  This option
#         // adds an extra filtering based on the AET of the modality issuing the C-Find.
#         "LimitAnswers": 0,  // Maximum number of answers to be returned (new in release 1.7.3)
#         "MWL_DB": "orthanc_ris"
#         // If MWL_DB is set and not empty, then this overrides the built-in storage and finds.
#     },
# Note that if Enable is set to false, then the alternative handler in this script will handle MWLFindRequests

if ('Worklists' in ORTHANC_CONFIG and 'MWL_DB' in ORTHANC_CONFIG['Worklists'] and ORTHANC_CONFIG ['Worklists']['MWL_DB'] != ""):
    RISDB = ORTHANC_CONFIG ['Worklists']['MWL_DB']
else:
    RISDB = 'orthanc_ris'
logging.info('Using DB ' + RISDB + ' for MWL DB Storage.')

if ('Worklists' in ORTHANC_CONFIG and 'Database' in ORTHANC_CONFIG['Worklists'] and ORTHANC_CONFIG ['Worklists']['Database'] != ""):
    WORKLIST_DIR = str(ORTHANC_CONFIG['Worklists']['Database']) + '/'
else:
    WORKLIST_DIR = "/var/lib/orthanc/worklists/"
logging.info('Using ' + WORKLIST_DIR + ' for MWL File System Storage.')

if ('Worklists' in ORTHANC_CONFIG and 'Enable' in ORTHANC_CONFIG['Worklists'] and ORTHANC_CONFIG ['Worklists']['Enable'] == False):
    USE_DB_MWL_SERVER = True
else:
    USE_DB_MWL_SERVER = False
    
logging.info('Using DB for MWL responses is:  ' +  ("True" if  USE_DB_MWL_SERVER else  "False"))

pp = pprint.PrettyPrinter(indent=4)

# Method to open a mysql connection to the DB.

def get_DB():
    password = os.getenv('MYSQL_ROOT_PASSWORD','')
    try:
        conn = mysql.connector.connect(host="mysql_db", port = 3306, user="root",password=password,database=RISDB)
        return conn
    except mysql.connector.Error as err:
        # MySQL server is not avaable
        print(err)
        print("Error Code:", err.errno)
        print("SQLSTATE", err.sqlstate)
        print("Message", err.msg)
        return False
        
        
# demo on how to convert SR to PDF on receiving an SR instance through the RestApiGet using dsr2html and wkhtmltopdf

def ReceivedInstanceCallback(receivedDicom, origin):

    # Only do the modifications if via DICOM and ideally filter by AET.
    orthanc.LogWarning('DICOM instance received in ReceivedInstanceCallback from ' + str(origin))
    dataset = dcmread(BytesIO(receivedDicom))
    jsonTags = json.loads(orthanc.DicomBufferToJson(receivedDicom, orthanc.DicomToJsonFormat.HUMAN, orthanc.DicomToJsonFlags.NONE, 0))
    # orthanc.LogWarning(json.dumps(jsonTags, indent = 2, sort_keys = True))
    
    if origin == orthanc.InstanceOrigin.DICOM_PROTOCOL:

        # Do Nothing for now
        return orthanc.ReceivedInstanceAction.KEEP_AS_IS, None
        
    elif origin == orthanc.InstanceOrigin.REST_API:
    
        if "Modality" in jsonTags and jsonTags['Modality'] == "SR":
        
            logging.info("NEW SR INSTANCE VIA RESTAPI"+json.dumps(jsonTags, indent = 2, sort_keys = True))
            # If it an SR Modality type, use dcmtk dsr2html to convert to HTML, then
            # use wkhtmltopdf to convert to an encapsulated PDF for easier diaplay.
            # see https://support.dcmtk.org/docs/dsr2html.html
            pathtobinary = shutil.which("dsr2html")
            dataset.save_as("/development/temp.dcm" ,write_like_original=True) 
            cmd =  pathtobinary + " -Ei /development/temp.dcm /development/temp.html"
            os.system(cmd) # returns the exit status
            pathtobinary = shutil.which("wkhtmltopdf")
            config = pdfkit.configuration(wkhtmltopdf=pathtobinary)
            options = {
                'page-size': 'Letter',
                'margin-top': '0.75in',
                'margin-right': '0.75in',
                'margin-bottom': '0.75in',
                'margin-left': '0.75in',
                'footer-line':'',
                'footer-font-size':'12',
                'footer-center': 'Page [page] of [toPage], [date]',
                'encoding': 'utf-8'
            }
            pdf = pdfkit.from_file("/development/temp.html", False,options=options)
            encoded = base64.b64encode(pdf).decode()
            dicomdata = dict()
            dicomdata['Force'] = True
            dicomdata['Tags'] = {
            "PatientID":jsonTags['PatientID'],
            "PatientName":jsonTags['PatientName'],
            "PatientBirthDate":jsonTags['PatientBirthDate'],
            "PatientSex":jsonTags['PatientSex'],
            "Modality":"OT",
            "SeriesDescription": jsonTags['SeriesDescription']+", SR Converted to PDF",
            "SequenceName" : "NA",
            "ImageComments":"SR Converted using dsr2html & wkhtmltopdf",
            "SOPClassUID":"1.2.840.10008.5.1.4.1.1.7.4",
            "StudyInstanceUID":jsonTags['StudyInstanceUID']
            }
            dicomdata['Content'] = "data:application/pdf;base64,"+encoded;
            convertedSR = json.loads(orthanc.RestApiPost('/tools/create-dicom', json.dumps(dicomdata)))
            return orthanc.ReceivedInstanceAction.MODIFY, dataset_to_bytes(dataset)
        
#   PyDict_SetItemString(sdk_OrthancPluginInstanceOrigin_Type.tp_dict, "UNKNOWN", PyLong_FromLong(1));
#   PyDict_SetItemString(sdk_OrthancPluginInstanceOrigin_Type.tp_dict, "DICOM_PROTOCOL", PyLong_FromLong(2));
#   PyDict_SetItemString(sdk_OrthancPluginInstanceOrigin_Type.tp_dict, "REST_API", PyLong_FromLong(3));
#   PyDict_SetItemString(sdk_OrthancPluginInstanceOrigin_Type.tp_dict, "PLUGIN", PyLong_FromLong(4));
#   PyDict_SetItemString(sdk_OrthancPluginInstanceOrigin_Type.tp_dict, "LUA", PyLong_FromLong(5));
#   PyDict_SetItemString(sdk_OrthancPluginInstanceOrigin_Type.tp_dict, "WEB_DAV", PyLong_FromLong(6));
    else:
        return orthanc.ReceivedInstanceAction.KEEP_AS_IS, None
        
orthanc.RegisterReceivedInstanceCallback(ReceivedInstanceCallback)
        
# Method to open a postgres connection to the DB.

# def get_PostGres_DB():
# 
#     host = os.getenv('ORTHANC__POSTGRESQL__HOST','')
#     password = os.getenv('ORTHANC__POSTGRESQL__PASSWORD','')
#     
#     try:
#         DB_NAME = "mwl"
#         conn = psycopg2.connect(host=host, port = 5432, dbname=DB_NAME, user="postgres", password=password)
#         # object type: psycopg2.extensions.connection
#         print ("\ntype(conn):", type(conn))
#         # string for the new database name to be created
#         # get the isolation leve for autocommit
#         autocommit = psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT
#         print ("ISOLATION_LEVEL_AUTOCOMMIT:", psycopg2.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
#         # set the isolation level for the connection's cursors
#         # will raise ActiveSqlTransaction exception otherwise
#         conn.set_isolation_level( autocommit )
#         # instantiate a cursor object from the connection
#         cursor = conn.cursor()
#         cursor.execute('CREATE DATABASE ' + str(DB_NAME))
#         cursor.execute("CREATE TABLE test (id serial PRIMARY KEY, num integer, data varchar);")
#         # close the cursor to avoid memory leaks
#         cursor.close()
# 
#         # close the connection to avoid memory leaks
#         conn.close()
#         return True
#     except psycopg2.Error as err:
#         print(err)
#         print("Error Code:", err.pgcode)
#         print("Message", err.pgerror)
#         return False


# METHOD TO CONSTRUCT DATASET FROM JSON, SEE SAMPLE, PASS IN the JSON for the Dataset and a Blank Dataset
# See orthanc.RegisterRestCallback('/mwl/create_from_json', MWLFromJSONCreateAndSave)

def getMWLFromJSON(MWLDict, DataSet):

    for key, value in MWLDict.items():
        if (isinstance(value, str) or isinstance(value, int)):
            setattr(DataSet, key, value)
        else: # must be a list or sequence
            # setattr(mwlDataSet, key, Dataset())
            sequence = []
            # Create the Sequence Blank Dataset
            for i in range(len(value)):
                sequenceSet = Dataset()
                sequenceSet = getMWLFromJSON(value[i], sequenceSet)
                sequence.append(sequenceSet)
            setattr(DataSet, key, sequence)
    return DataSet

# CREATES RAW DATA TO STORE DATASET IN DB, from Dataset object
def dataset_to_bytes(dataset):

    # create a buffer
    with BytesIO() as buffer:
        # create a DicomFileLike object that has some properties of DataSet
        memory_dataset = DicomFileLike(buffer)
        # write the dataset to the DicomFileLike object
        dcmwrite(memory_dataset, dataset)
        # to read from the object, you have to rewind it
        memory_dataset.seek(0)
        # read the contents as bytes
        return memory_dataset.read()

# CONVERTS BYTES BACK INTO A DATASET, I.E. FOR RETRIEVING FROM DATABASE
def bytes_to_dataset(blob):

    # you can just read the dataset from the byte array
    dataset = dcmread(BytesIO(blob), force=True)
    # do some interesting stuff
    dataset.is_little_endian = False
    return dataset
    
# Get MWLfrom DB:  id, AccessionNumber, StudyInstanceUID, ScheduledProcedureStepStartDate, AET, MWLJSON, Dataset, updated_at, created_at

def getMWLJSONDataset (AccessionNumber, StudyInstanceUID):

    conn = get_DB()
    if (conn):
        mycursor = conn.cursor(dictionary=True)
        try:
            mycursor.execute("SELECT * from mwl WHERE AccessionNumber = %s AND StudyInstanceUID = %s ORDER BY created_at DESC LIMIT 1", (AccessionNumber,StudyInstanceUID,))
            myresult = mycursor.fetchone()
            mycursor.close()
            conn.close()
            return myresult;

        except mysql.connector.Error as err:
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            mycursor.close()
            conn.close()
            return False
    else:
        return False;
    

# Save Dataset to DB

def SaveDatasetDB(JSON, dataset):

    response = dict()
    conn = get_DB()
    if (conn):
        AccessionNumber = ""
        if ('AccessionNumber' in JSON): AccessionNumber = JSON['AccessionNumber'] # Not sure if this belongs is SPSS
        StudyInstanceUID = ""
        if ('StudyInstanceUID' in JSON): StudyInstanceUID = JSON['StudyInstanceUID'] # Not sure if this belongs is SPSS
        ScheduledProcedureStepStartDate = ""
        AET = ""
        if ('ScheduledProcedureStepSequence' in JSON):
            if ('ScheduledProcedureStepStartDate' in JSON['ScheduledProcedureStepSequence'][0]):
                ScheduledProcedureStepStartDate = JSON['ScheduledProcedureStepSequence'][0]['ScheduledProcedureStepStartDate']
            if ('ScheduledStationAETitle' in JSON['ScheduledProcedureStepSequence'][0]):
                AET = JSON['ScheduledProcedureStepSequence'][0]['ScheduledStationAETitle']
        JSONinsert = json.dumps(JSON,sort_keys = True)
        mycursor = conn.cursor()
        datasetBytes = dataset_to_bytes(dataset)
        try:
            mycursor.execute("INSERT INTO mwl (completed, AccessionNumber, StudyInstanceUID, ScheduledProcedureStepStartDate, AET, MWLJSON, Dataset) VALUES (%s, %s, %s, %s, %s, %s, %s)", (False, AccessionNumber, StudyInstanceUID, ScheduledProcedureStepStartDate, AET, JSONinsert, datasetBytes))
            conn.commit()
            mycursor.close()
            conn.close()

        except mysql.connector.Error as err:
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            mycursor.close()
            conn.close()
            response['DB'] = False
            return response
        response['DB'] = True
        return response
    else:
        response['DB'] = False

# curl -X POST -H "Content-Type: application/json" -H  "Authorization:Bearer CURLTOKEN" -H  "Token:wxwzisme" https://cayman.medical.ky/pacs-2/mwl/create_from_json -d '{"AccessionNumber":"CMACC00000002","AdditionalPatientHistory":"test","AdmittingDiagnosesDescription":"","Allergies":"","ImageComments":"Tech:  SP","MedicalAlerts":"","Modality":"MR","Occupation":"","OperatorsName":"Tech^SP","PatientAddress":"^^George Town^OS^KY1-1111^KY","PatientBirthDate":"20010101","PatientComments":"","PatientID": "CM0000001","PatientName":"Person^Test1^","PatientSex": "M","PatientSize":"","PatientTelecomInformation":"KY-9261863^WPN^PH^","PatientWeight":"","ReferringPhysicianIdentificationSequence":[  {"InstitutionName": "Cayman Medical Ltd.","PersonIdentificationCodeSequence":[{"CodeMeaning":"Local Code","CodeValue":"0001","CodingSchemeDesignator":"L"}],"PersonTelephoneNumbers":"US-6513130209^WPN^PH^sscotti@sscotti.org"}],"ReferringPhysicianName":"0001:Scotti^Stephen^Douglas^Dr.","ScheduledProcedureStepSequence":[{"Modality": "MR","ScheduledProcedureStepDescription":"MRI BRAIN / BRAIN STEM - WITHOUT CONTRAST","ScheduledProcedureStepID": "0001","ScheduledProcedureStepStartDate":"20210704","ScheduledProcedureStepStartTime":"110000","ScheduledProtocolCodeSequence":[{"CodeMeaning": "[\"70551\"]","CodeValue": "70551","CodingSchemeDesignator": "C4"}],"ScheduledStationAETitle": "NmrEsaote"}],"SpecificCharacterSet":"ISO_IR 192","StudyInstanceUID":"1.3.6.1.4.1.56016.1.1.1.55.1626553968"}'

def MWLFromJSONCreateAndSave(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        query = json.loads(request['body'])
        logging.info("WORKLIST|MWLFromJSONCreateAndSave|" + json.dumps(query))
        dataset = Dataset()
        dataset = getMWLFromJSON(query, dataset)
        dataset.file_meta = FileMetaDataset()
        dataset.file_meta.ImplementationClassUID = pydicom.uid.PYDICOM_IMPLEMENTATION_UID
        dataset.file_meta.ImplementationVersionName = "ORTHANC_PY_MWL"
        dataset.file_meta.MediaStorageSOPClassUID = "0"
        dataset.file_meta.MediaStorageSOPInstanceUID = "0"
        dataset.file_meta.TransferSyntaxUID = pydicom.uid.ExplicitVRLittleEndian
        dataset.is_little_endian = dataset.file_meta.TransferSyntaxUID.is_little_endian
        dataset.is_implicit_VR = dataset.file_meta.TransferSyntaxUID.is_implicit_VR
        # Set creation date/time
        dt = datetime.now()
        dataset.ContentDate = dt.strftime('%Y%m%d')
        timeStr = dt.strftime('%H%M%S.%f')  # long format with micro seconds
        dataset.ContentTime = timeStr
        response = SaveDatasetDB(query, dataset)
        response['status'] = ((".  Saved to PACS DB " + RISDB)  if  response['DB'] else ".  Error Saving to Orthanc RIS")
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

orthanc.RegisterRestCallback('/mwl/create_from_json', MWLFromJSONCreateAndSave)

def getActiveMWLs ():

    conn = get_DB()
    if (conn):
        mycursor = conn.cursor(dictionary=True)
        try:
            # For now, gets only the most recent one for a given AccessionNumber and also where completed is 0.
            mycursor.execute("SELECT * from mwl m1 WHERE created_at = (SELECT MAX(created_at) from mwl m2 WHERE m1.AccessionNumber = m2.AccessionNumber ) AND Completed = 0 ORDER BY AccessionNumber ASC")
            myresult = mycursor.fetchall()
            mycursor.close()
            conn.close()
            return myresult;

        except mysql.connector.Error as err:
            print(err)
            print("Error Code:", err.errno)
            print("SQLSTATE", err.sqlstate)
            print("Message", err.msg)
            mycursor.close()
            conn.close()
            return False
    else:
        return False;
     
# DROP IN REPLACEMENT FOR THE NATIVE ORTHANC MWL PLUG-IN.  ONLY ONE CAN BE ENABLED AT A TIME.

# findscu 127.0.0.1 4242  -W -d --anonymous-tls --ignore-peer-cert -k "ScheduledProcedureStepSequence[0].Modality=MR"

if (USE_DB_MWL_SERVER == True):

    def OnWorklist(answers, query, issuerAet, calledAet):

        orthanc.LogWarning('Received incoming C-FIND worklist request from %s:' % issuerAet)
        # Get a memory buffer containing the DICOM instance
        dicom = query.WorklistGetDicomQuery()
        # Get the DICOM tags in the JSON format from the binary buffer
        jsonTags = json.loads(orthanc.DicomBufferToJson(dicom, orthanc.DicomToJsonFormat.HUMAN, orthanc.DicomToJsonFlags.NONE, 0))
        orthanc.LogWarning('C-FIND worklist request to be handled in Python: %s' % json.dumps(jsonTags, indent = 4, sort_keys = True))
        MWLfromDB = getActiveMWLs ()
        # Loop over the available DICOM worklists
        for order in MWLfromDB:
            content = order['Dataset']
            # Test whether the query matches the current worklist
            if query.WorklistIsMatch(content):
                orthanc.LogWarning('Matching worklist: %s' % order['AccessionNumber'])
                answers.WorklistAddAnswer(query, content)

    orthanc.RegisterWorklistCallback(OnWorklist)




def getTimeDifference(TimeStart, TimeEnd):
    timeDiff = TimeEnd - TimeStart
    return timeDiff.total_seconds() / 60


# Pass in the studyuuid, curl http://localhost:8042/studies/calculate_time/fa19ef43-287129d4-7ae5a8be-ee81ccbe-f3f00fad

def CalculateStudyTime(output, uri, **request):

    if request['method'] != 'GET':
        output.SendMethodNotAllowed('GET')
    else:
        response = dict()
        studyID = request['groups'][0]
        studySeries = json.loads(orthanc.RestApiGet('/studies/%s' % studyID))['Series']
        sortedByTime = []
        results = dict()
        for series in studySeries:
            seriesData = json.loads(orthanc.RestApiGet('/series/%s' % series))
            item = {
                'uuid':series,
                'datetime':seriesData['MainDicomTags']['SeriesDate'] + seriesData['MainDicomTags']['SeriesTime']
            }
            sortedByTime.append(item)
        sortedByTime.sort(key = lambda i: i['datetime']);
        TimeStart = datetime.strptime(sortedByTime[0]['datetime'], '%Y%m%d%H%M%S')
        TimeEnd = datetime.strptime(sortedByTime[len(sortedByTime)-1]['datetime'], '%Y%m%d%H%M%S')
        StudyLength = getTimeDifference(TimeStart,TimeEnd)
        response['StudyLength'] = StudyLength
        orthanc.LogWarning('Resorted Series for Study:  ' + studyID + '  Series Dump:  \n\n'  + json.dumps(response))
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')


orthanc.RegisterRestCallback('/studies/calculate_time/(.*)', CalculateStudyTime)


def RenumberSeries(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        response = dict()
        uuid = json.loads(request['body'])['uuid']
        response['status'] = renumber_sequences(uuid)
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')


orthanc.RegisterRestCallback('/renumber_series', RenumberSeries)

# Method to get all of the series for a study, sort them by datetime, and then renumber them from 1 to n.
# Useful mostly for cases where studies have to be merged and renaming the sequence number might make sense since it takes a bit of processing.

# curl -k  -d '{"uuid":"0cc9fb82-726d3dfc-e6f2b353-e96558d7-986cbb2c"}' https://caymanmed:caymanmed@127.0.0.1:8042/renumber_series

def renumber_sequences(study_uuid):

    study = json.loads(orthanc.RestApiGet('/studies/%s' % study_uuid))
    if 'Series' in study:
        studySeries = study['Series']
        sortedByTime = []
        results = dict()
        for series in studySeries:
            seriesData = json.loads(orthanc.RestApiGet('/series/%s' % series))
            item = {
                'uuid':series,
                'datetime':seriesData['MainDicomTags']['SeriesDate'] + seriesData['MainDicomTags']['SeriesTime']
            }

            sortedByTime.append(item)
        sortedByTime.sort(key = lambda i: i['datetime']);
        orthanc.LogWarning('Resorted Series for Study:  ' + study_uuid + '  Series Dump:  \n\n'  + json.dumps(sortedByTime))
        # KeepSource set to False deletes the originals and uses Orthanc's UID generator, good and bad really.
        # In the future Orthanc will allow specifying one's own root ID.
        i = 1
        for series in sortedByTime:
            command = {
                "Replace": {
                    "SeriesNumber": "%s" % i
                },
                "KeepSource":False,
                "Asynchronous":True
            }
            modify = orthanc.RestApiPost('/series/%s/modify' % series['uuid'],json.dumps(command))
            # modify['datetime'] = series['datetime']
            results[series['uuid']] =  json.loads(modify)
            i+=1

        logging.info("STUDY|RENUMBER_SEQUENCES|" + json.dumps(results))
        return results
    else:
        return 'error'


# METADATA SYSTEM TAGS FOR INSTANCES

#     ReceptionDate: records when a DICOM instance was received by Orthanc. Similarly, LastUpdate records, for each patient/study/series, the last time a DICOM instance was added to this resource.
#     RemoteAET: records the AET of the modality that has sent some DICOM instance to Orthanc using the DICOM protocol.
#     ModifiedFrom and AnonymizedFrom: hold from which original resource, a resource was modified or anonymized. The presence of this metadata indicates that the resource is the result of a modification or anonymization that was carried on by Orthanc.
#     Origin: records through which mechanism the instance was received by Orthanc (may be Unknown, DicomProtocol, RestApi, Plugins, or Lua).
#     IndexInSeries records the expected index of a DICOM instance inside its parent series. Conversely, ExpectedNumberOfInstances associates to each series, the number of DICOM instances this series is expected to contain. This information is not always available.
#     Starting with Orthanc 1.2.0, TransferSyntax and SopClassUid respectively stores the transfer syntax UID and the SOP class UID of DICOM instances, in order to speed up the access to this information.
#     RemoteIP (new in Orthanc 1.4.0): The IP address of the remote SCU (for REST API and DICOM protocol).
#     CalledAET (new in Orthanc 1.4.0): The AET that was called by the SCU, which normally matches the AET of Orthanc (for DICOM protocol).
#     HttpUsername (new in Orthanc 1.4.0): The username that created the instance (for REST API).
#     PixelDataOffset (new in Orthanc 1.9.1): Offset (in bytes) of the Pixel Data DICOM tag in the DICOM file, if available.

# Still working on this, but does some basic logging, optional mark the orderstatus, which does work as now configured.

def OnChange(changeType, level, resource):

    if changeType == orthanc.ChangeType.NEW_INSTANCE:

        orthanc.LogWarning('A new instance was uploaded: %s' % resource)

    elif changeType == orthanc.ChangeType.NEW_STUDY:

        study = json.loads(orthanc.RestApiGet('/studies/%s' % resource))
        orthanc.LogWarning('NEW_STUDY from Modality, ID: %s' % resource)
        SendNotification("NEW_STUDY from Modality:", json.dumps(study, indent = 3))

    elif changeType == orthanc.ChangeType.ORTHANC_STARTED:

        orthanc.LogWarning('DICOM server Started, in Python Plug-In')

    elif changeType == orthanc.ChangeType.ORTHANC_STOPPED:

        orthanc.LogWarning('Stopping Orthanc, in Plug-In')

    elif changeType == orthanc.ChangeType.JOB_SUCCESS:

        job = json.loads(orthanc.RestApiGet('/jobs/%s' % resource))
        if (job['Type'] == "MergeStudy"):
        # Finish task of renumbering the sequences and rebuilding study
#         {
#         "CompletionTime": "20210908T232227.042726",
#         "Content": {
#             "Description": "REST API",
#             "FailedInstancesCount": 0,
#             "InstancesCount": 10,
#             "TargetStudy": "fa19ef43-287129d4-7ae5a8be-ee81ccbe-f3f00fad"
#         },
#         "CreationTime": "20210908T232226.381476",
#         "EffectiveRuntime": 0.66,
#         "ErrorCode": 0,
#         "ErrorDescription": "Success",
#         "ErrorDetails": "",
#         "ID": "43c70a78-4aa1-48fd-88af-232610122ebd",
#         "Priority": 0,
#         "Progress": 100,
#         "State": "Success",
#         "Timestamp": "20210908T232227.045331",
#         "Type": "MergeStudy"
#         }
            logging.info("STUDY|MERGE_JOB_COMPLETE|" + json.dumps(job))
            renumber = renumber_sequences(job['Content']['TargetStudy'])
            SendNotification("Merge Complete, and Renumbering Sequences Jobs Queued", "Merge Job"  +json.dumps(job, indent = 3) + '\n\nRenumbering Jobs:  ' +json.dumps(renumber, indent = 3))

        elif (job['Type'] == "ResourceModification" and job['Content']['Type'] == "Series"):

            reconstruct = orthanc.RestApiPost('%s/reconstruct' % job['Content']['Path'],'{}')
            # It is necessary to reconstruct the series DB after the modification, so do that here after the job is complete and send an e-mail.
            SendNotification("Series Modification Finished.", "Reconstruction of Series in Progress.\n\n\n.Job Summary:\n\n\n"  +json.dumps(job, indent = 3))

        elif (job['Type'] == "ResourceModification" and job['Content']['Type'] == "Study"):
            try:
                study = json.loads(orthanc.RestApiGet(job['Content']['Path']))
            except:
                orthanc.LogWarning('Study Reconstruct Job Complete.  Error Fetching Original Study'  + job['Content']['Path'])
                SendNotification("Study Modification Finished, Study no longer exists, must have reassigned StudyInstanceUID or other issue.", "Job Summary:\n\n\n" + json.dumps(job, indent = 3))
            else:
                orthanc.LogWarning('Study Reconstruct Job Complete.' + json.dumps(study))
                reconstruct = orthanc.RestApiPost('%s/reconstruct' % job['Content']['Path'],'{}')
                SendNotification("Study Modification Finished, Reconstructing Study", "Job Summary:\n\n\n" + json.dumps(job, indent = 3))

        elif (job['Type'] == "Archive" or job['Type'] == "Media"):

            SendNotification("Arhive of Type " + job['Type'] + " created." , "Job Summary:\n\n\n" + json.dumps(job, indent = 3))

    elif changeType == orthanc.ChangeType.JOB_FAILURE:

        job = json.loads(orthanc.RestApiGet('/jobs/%s' % resource))
        SendNotification("JOB FAILED", json.dumps(job, indent = 3))

orthanc.RegisterOnChangeCallback(OnChange)


def MakeKeyImage(output, uri, **request):

    # POST has {"StudyInstanceUID":"StudyInstanceUID"","data-url":"data-url","ImageComments":"user_id: 1 user_name: sscotti reader_id: 0001"})
    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:

        response = dict();
        query = json.loads(request['body'])
        study = json.loads(orthanc.RestApiPost('/tools/lookup', query['StudyInstanceUID']))[0] # assume a unique StudyInstancUID on server and that it exists
        series = json.loads(orthanc.RestApiGet('/studies/' + study["ID"] + '/series')) # get the series uuid's, there has to be at least one
        keyimagesID = False
        instancenumber = 1
        for seriesitem in series:
            # Check to see if the 'KEY_IMAGES' series already exists, sort of assumes that this will always be unqiue on this system
            if 'SeriesDescription' in seriesitem['MainDicomTags'] and seriesitem['MainDicomTags']['SeriesDescription'] == 'KEY_IMAGES':
                keyimagesID = seriesitem['ID']
                instancenumber = len(seriesitem['Instances']) + 1

        dicomdata = dict()
        # 1.2.840.10008.5.1.4.1.1.7 is SOPClassUID for Secondary Capture Image Storage
        # Set things up to add to an existing series, or create one if it does not already exist
        if keyimagesID:
            parent = keyimagesID
            dicomdata['Tags'] = {"ImageComments":query['ImageComments'],"SOPClassUID":"1.2.840.10008.5.1.4.1.1.7.4","InstanceNumber": str(instancenumber)}
        else:
            parent = study["ID"]
            dicomdata['Tags'] = {"Modality":"OT","SeriesDescription": "KEY_IMAGES","SequenceName" : "KEY_IMAGES","ImageComments":query['ImageComments'],"SeriesNumber":"0","SOPClassUID":"1.2.840.10008.5.1.4.1.1.7.4","InstanceNumber": str(instancenumber)}
        dicomdata['Parent'] = parent
        # image to save is html converted to a jpeg image using wkhtmltoimage.
        # https://pypi.org/project/imgkit/

#         options = {
#             'format': 'jpeg',
#             'encoding': "UTF-8"
#         }
        # False means to store it to a variable rather than to a file on disk
#         jpegimage = imgkit.from_string(query['html'], False, options=options)
        # Need to be a data-url in base64
#         jpegimage = base64.b64encode(jpegimage).decode()
#         jpegimage = 'data:image/jpeg;base64,'+jpegimage
        jpegimage = query['data_url']
        # logging.info(jpegimage)
        dicomdata['Content'] = jpegimage
        dicom = json.loads(orthanc.RestApiPost('/tools/create-dicom', json.dumps(dicomdata)))
        response['keyimagesID'] = keyimagesID
        response['status'] = study
        response['create-dicom'] =  dicom
        output.AnswerBuffer(json.dumps(response), 'application/json')

orthanc.RegisterRestCallback('/make_key_image', MakeKeyImage)


# EMAIL NOTIFICATION FUNCTION, can be called from within script or via REST callback
# curl http://localhost:8042/sendemail -d '{"subject":"This is a test","body":"string"}'

def SendNotification(subject, body):

    msg = MIMEText(body)
    msg['Subject'] = subject
    msg['From'] = os.getenv('PYTHON_EMAIL_FROM','sscotti@sscotti.org')
    msg['To'] =  os.getenv('PYTHON_EMAIL_TO','sscotti@sias.dev')
    msg['Cc'] = ""
    recipients = msg['To'] + msg['Cc'] 
    context = ssl.create_default_context()
    server = smtplib.SMTP_SSL(os.getenv('PYTHON_EMAIL_HOST',''), 465, context)
    server.login(os.getenv('PYTHON_EMAIL_USER',''), os.getenv('PYTHON_EMAIL_PASS',''))
    server.sendmail(msg['From'], os.getenv('PYTHON_EMAIL_TO','sscotti@sias.dev'), msg.as_string())
    server.quit()
    
def SendEmail(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    response = dict()
    payload = json.loads(request['body'])
    subject = payload['subject']
    body = payload['body']
    try:
        SendNotification(subject, body)
        response['status'] = "SUCCESS"
    except Exception as e:
        response['status'] = str(e)
    output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

orthanc.RegisterRestCallback('/sendemail', SendEmail)

# BEGINNING OF STUDIES/PAGE, TAGROUP IS NICE TO HAVE JUST GENERALLY.

# curl http://localhost:8042/studies/page -d '{"Query":{"PatientName":"**","PatientBirthDate":"","PatientSex":"","PatientID":"","AccessionNumber":"","StudyDescription":"**","ReferringPhysicianName":"**","StudyDate":""},"Level":"Study","Expand":true,"MetaData":{},"pagenumber":1,"itemsperpage":5,"sortparam":"StudyDate","reverse":1,"widget":1}'

# Typical response is like below, where the first element of the array is the "pagination object", and the following elements are the Expanded Study data, with imagecount and modalities added.  Modalities is an array.

# [
#    {
#       "count": 11,
#       "widget": "<div data-url = \"/studies/page \" class = \"paginator\"><a data-page = \"1\" class = \"pageactive\" href=\"\">1</a><a data-page = \"2\" class = \"\"  href=\"\">2</a><a data-page = \"3\" class = \"\"  href=\"\">3</a><a data-page = \"4\" class = \"\"  href=\"\">4</a><a data-page = \"5\" class = \"\"  href=\"\">5</a><a data-page = \"6\" class = \"\"  href=\"\">6</a> ... <a data-page = \"11\" class = \"\" href=\"\">11</a><span class = \"totalperpage\"> Total per page:  1</span></div>",
#       "results": 1,
#       "pagenumber": 1,
#       "offset": 0,
#       "limit": 1
#    },
#    {
#       "IsStable": true,
#       "LastUpdate": "20200629T170852",
#       "PatientMainDicomTags": {
#          "PatientBirthDate": "19571116",
#          "PatientSex": "M",
#          "PatientID": "DEV0000001",
#          "OtherPatientIDs":"OtherPatientIDs",
#          "PatientName": "SCOTTI^STEPHEN^D^^"
#       },
#       "Series": [
#          "e46bfef4-2b166666-468cc957-4b942aa8-3a5c6ef8"
#       ],
#       "modalities": [
#          "CR"
#       ],
#       "ParentPatient": "fa21ff2d-33e9b60a-daedf6a0-64d018da-682fd0a4",
#       "MainDicomTags": {
#          "AccessionNumber": "DEVACC00000006",
#          "StudyDate": "20190829",
#          "StudyDescription": "XR HIP LT 1 VW",
#          "InstitutionName": "MHealth CSC",
#          "ReferringPhysicianName": "0002^Talanow^Roland",
#          "RequestingPhysician": "2VASKE^SHANNON^M^^",
#          "StudyTime": "090425",
#          "StudyID": "UC4839619",
#          "StudyInstanceUID": "2.16.840.1.114151.1052214956401694179114379854103077382390190829"
#       },
#       "Type": "Study",
#       "ID": "e8263ed6-56adfc56-a9951260-db8c21f3-c78d7103",
#       "imagecount": 1,
#       "Metadata": {
#          "LastUpdate": "20200629T170852"
#       }
#    }
# ]

def GetTagGroupFromKey(key):

    lookup =    {

    "AccessionNumber": "MainDicomTags",
    "StudyDate": "MainDicomTags",
    "AccessionNumber": "MainDicomTags",
    "StudyDescription": "MainDicomTags",
    "InstitutionName": "MainDicomTags",
    "ReferringPhysicianName": "MainDicomTags",
    "RequestingPhysician": "MainDicomTags",
    "StudyTime": "MainDicomTags",
    "StudyID": "MainDicomTags",
    "StudyInstanceUID": "MainDicomTags",
    "PatientBirthDate": "PatientMainDicomTags",
    "PatientSex": "PatientMainDicomTags",
    "PatientID": "PatientMainDicomTags",
    "PatientName": "PatientMainDicomTags"
    }
    return lookup[key]

# returns the path for a study from the answers loop, used to construct and path for the metadata query.

def GetPath(resource):
    return '/studies/%s' % resource['ID']

# Function to recursively search down tag hierarchy to find matches from the query['Tags'] passed in to studies/find
# Skip the modality tag because that takes too long for a common search, handled further down  in code when modalities
# and image counts are caluculated.
# Not currently used.
def CheckTagLevel(tags,dictlist):

        print(tags)
        print(dictlist)
        for tagitem, value in dictlist:

            if (isinstance(value, str)):
                if tagitem in tags:
                    print(tags[tagitem] + ' ' + value)
                    if (tags[tagitem] != value):
                        return False
                    else:
                        continue
                else:
                    return False
            elif (tagitem in tags.keys()):  #must be a dict
                if (CheckTagLevel(tags[tagitem][0],value.items()) == True):
                    continue
                else:
                    return False
            else:
                return False
        return True


def FindWithMetadata(output, uri, **request):

    # The "/tools/find" route expects a POST method
    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        response = dict();
        # Check the Level and Generate an error response if not a Study
        # Parse the query provided by the user, and backup the "Expand" field
        query = json.loads(request['body'])
        if query['Level'] != "Study":
            response["error"] = "Can only Query Studies"
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')
        elif ('pagenumber' not in query) or ('itemsperpage' not in query) or not (isinstance(query['pagenumber'], int)) or not (isinstance(query['itemsperpage'], int)):
            response["error"] = "Page Number and/or Items Per Page Error"
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')
        else:
            userprofilejwt = json.loads(request['headers']['userprofilejwt'])
            logging.info("STUDY|STUDIES_PAGE_QUERY|" + json.dumps({"query":query,"userprofilejwt":userprofilejwt}))
# userprofilejwt
# {
# 'doctor_id': '0001',
# 'ip': '172.18.0.1',
# 'patientid': 'DEV0000001',
# 'reader_id': '0001',
# 'user_email': 'sscotti@sscotti.org',
# 'user_id': '1',
# 'user_name': 'sscotti',
# 'user_roles': [1, 2, 3, 4, 5, 6, 7, 8]
# }
            # For loading all studies for a PatientID, zero out the Referring Physician and del the flag
            reader_id = userprofilejwt.get('reader_id') or ''
            if ('LoadALL' in query['Query']):

                response['ReferringPhysicianName'] = "Set to *"
                query['Query']['ReferringPhysicianName'] = "*"
                response['PatientID'] = "Set to"  + str(query['Query']['PatientID'])
                del query['Query']['LoadALL']
            # If they have a reader_id they have global access, leave query unchanged
            elif (reader_id != '' and reader_id is not None):
                response['ReferringPhysicianName'] = "Reader, Unchanged"
                response['PatientID'] = "Reader, Unchanged"
            else:
                doctor_id = userprofilejwt.get('doctor_id') or ''
                if (doctor_id != '' and doctor_id is not None):
                    query['Query']['ReferringPhysicianName'] = str(doctor_id) + ':*'
                    response['ReferringPhysicianName'] = "Set To " + str(doctor_id) + ':*'
                else:
                    response['ReferringPhysicianName'] = "Unchanged"

                PatientID = userprofilejwt.get('patientid') or ''
                if (PatientID != '' and PatientID is  not None):
                    query['Query']['PatientID'] = str(PatientID)
                    response['PatientID'] = "Set To " + str(PatientID)
                else:
                    response['PatientID'] = "Unchanged"
            # pprint.pprint(query)
            # Allows specifying the Modality in the Query, or using the Tag ["0008,0060"].  Modality is handled as an exception at the Series Level instead of searching tags.
            modality = False
            if 'Tags' in query and "0008,0060" in query['Tags']:
                modality  = query['Tags']["0008,0060"]
                del query['Tags']["0008,0060"]
            if 'Modality' in query['Query']:
                modality  = query['Query']['Modality']
                del query['Query']['Modality']
            if 'Expand' in query:
                originalExpand = query['Expand']
            else:
                originalExpand = False
            if modality:
                query['Query']['ModalitiesInStudy'] = modality
                
            # Call the core "/tools/find" route
            query['Expand'] = True
            query['RequestedTags'] =  ['ModalitiesInStudy', 'NumberOfStudyRelatedInstances']
            answers = orthanc.RestApiPost('/tools/find', json.dumps(query))

            # Loop over the matching resources, bypass the Metadata filtering if there are no params specified (i.e. len)

            filteredAnswers = []
            # Used mostly for Study Level Metatags, ReportStatusJSON - 1024 and OutsideStudyFlag - 1025 to filter on the Report Statuses at the Study Level.
            if 'MetaData' in query and len(query['MetaData']) > 0:

                for answer in json.loads(answers):

                    try:
                        # Read the metadata that is associated with the resource
                        # Check whether the metadata matches the regular expressions
                        # that were provided in the "Metadata" field of the user request
                        metadata = json.loads(orthanc.RestApiGet('%s/metadata?expand' % GetPath(answer)))
                        for (name, pattern) in query['MetaData'].items():
                            print("name" + name + "  pattern" + pattern)
                            if name in metadata:
                                value = metadata[name]
                            else:
                                value = ''
                            if re.match(pattern, value) == None:
                                break

                            # If all the metadata matches the provided regular
                            # expressions, add the resource to the filtered answers

                            if originalExpand:
                                answer['Metadata'] = metadata
                                filteredAnswers.append(answer)
                            else:
                                filteredAnswers.append(answer['ID'])
                    except:
                        # The resource was deleted since the call to "/tools/find"
                        pass
            else:
                filteredAnswers = json.loads(answers)
            filteredAnswers2 = filteredAnswers
            #query['Tags']
            #added section to allow searching on any instance tag at primary or secondary level in hierarchy, for SQ tags
            if 'Tags' in query and len(query['Tags']) > 0:
                filteredAnswers2 = []
                for answer in filteredAnswers:
                    instances = json.loads(orthanc.RestApiGet('/studies/' + answer["ID"] + '/instances'))
                    instance = instances[0]["ID"]
                    tags = json.loads(orthanc.RestApiGet('/instances/'+ instance + '/tags?short'))

                    matchedall = CheckTagLevel(tags, query['Tags'].items())

                    if (matchedall == True):
                        answer['matchinginstance'] = instance
                        simplifiedtags = json.loads(orthanc.RestApiGet('/instances/'+ instance + '/simplified-tags'))
                        answer['simplifiedtags'] = simplifiedtags
                        filteredAnswers2.append(answer)

            # Just used the tools/find results if no Metadata
            #    curl -s https://demo.orthanc-server.com/studies/27f7126f-4f66fb14-03f4081b-f9341db2-53925988/instances | grep '"ID"' | head -n1
            # The globals are used in the GetSortParam function for the taggroup, sortparam and reverse
            # sortparam can be omitted, in which case it defaults to StudyDate
            global param
            if 'sortparam' in query:
                param = query['sortparam']
            else:
                param = 'StudyDate'
            global taggroup
            taggroup = GetTagGroupFromKey(query['sortparam'])
            global reverse
            reverse = query['reverse']
            # Sort the studies according to the "StudyDate" DICOM tag
            studiessorted = sorted(filteredAnswers2, key = GetSortParam, reverse=reverse)
            count = len(studiessorted)
            #default for pagenumber
            pagenumber = 1
            if 'pagenumber' in query:
                pagenumber = query['pagenumber']
            #default for itemsperpage if not in the query, which it should be
            itemsperpage = 10
            if 'itemsperpage' in query:
                itemsperpage = query['itemsperpage']
            limit = itemsperpage
            offset = (pagenumber -1) * itemsperpage
            #offset = 0
            #if 'offset' in query:
            #    offset = query['offset']
            #limit = 0
            #if 'limit' in query:
            #    limit = query['limit']
            # Truncate the list of studies
            # Pass in 0 for limit if you want to just want to list all from the offset
            if limit == 0:
                studiessorted = studiessorted[offset : ]
            else:
                studiessorted = studiessorted[offset : offset + limit]
                
            #  This will get rid of the Series Key since it probably isn't needed really.
            studies = [{k: v for k, v in d.items() if k != 'Series'} for d in studiessorted]
#             # Section that gets the instance counts, the modalities, and filters by modalities at the Series Level, not using Tags.  Tag searches Take Forever with a lot of studies.
#             for study in studiessorted:
#                 modalities = []
#                 imagecount = 0
#                 for seriesuuid in study['Series']:
#                     series = json.loads(orthanc.RestApiGet('/series/%s' % seriesuuid))
#                     # print series
#                     imagecount = imagecount + len(series['Instances'])
#                     if series['MainDicomTags']['Modality'] not in modalities:
#                         modalities.append(series['MainDicomTags']['Modality'])
# 
#                 study['imagecount'] = imagecount
#                 study['modalities'] = modalities
#                 study.pop('Series', None) # Get rid of the Series since not needed in response.
#                 # print(modality if modality else "Modality Not Searched For")
#                 if modality:
#                     if modality in study['modalities']:
#                         studies.append(study)
#                 else:
#                     studies.append(study)

            url = '/studies/page'

            # Return the truncated list of studies

            widget = ""
            if 'widget' in query:
                widget = CreateWidget(limit, pagenumber, url , count)
                #studies.insert(0,{"paginationwidget":widget})
            studies.insert(0, {"widget": widget, "results":len(studies), "limit":limit, "offset":offset, "pagenumber":pagenumber, "count":count,"PatientID":response['PatientID'],"ReferringPhysicianName":response['ReferringPhysicianName']})
            # Return the filtered answers in the JSON format
#             if int(platform.python_version_tuple()[0]) < 3:
#                 logging.warning("Suggest using Python 3.x.x, using:  " + platform.python_version())
#             else:
#                 print(platform.python_version_tuple()[0])
#                 logging.warning("Suggest using Python 3.x.x, using:  " + platform.python_version())
            output.AnswerBuffer(json.dumps(studies, indent = 3), 'application/json')

orthanc.RegisterRestCallback('/studies/page', FindWithMetadata)

#param is the tag to sortby
#taggroup is the taggroup for the param
#defined as globals in FindWithMetadata


def ceildiv(a, b):
    return -(-a // b)
    
# Could extend this such that the passed in widget id/number is a selection of preconfigured widgets for pagination, since it include HTML markup.

def CreateWidget(limit, pagenumber, url, count):

    total_pages = ceildiv(count, limit);
    links = '<div data-url = "' + url +' " class = "paginator">'
    if (total_pages >= 1 and pagenumber <= total_pages):
        active = "";
        if pagenumber == 1:
            active = "pageactive"
        links += '<a data-page = "1" class = "' + active  + '" href="">1</a>'
        active = ""
        i = max(2, pagenumber - 5)
        if i > 2:
            links += " ... "
        for i in range(i, min(pagenumber + 6, total_pages)):
            if pagenumber == i:
                active = "pageactive"
            links += '<a data-page = "' + str(i) + '" class = "' + active  + '"  href="">' + str(i) + '</a>'
            active = ""
        if i != total_pages:
            links += " ... "
        if pagenumber == total_pages:
            active = "pageactive"
        links += '<a data-page = "' + str(total_pages) + '" class = "' + active + '" href="">' + str(total_pages) + '</a>'

    links += '<span class = "totalperpage"> Total per page:  ' + str(limit) + '</span>'
    links += '</div>'
    # Sends an e-mail
    # SendNotification("/studies/find request", "Just an notification")
    return links

def GetSortParam(study):
    if param in study[taggroup]:
        return study[taggroup][param]
    else:
        return ''


def attachbase64pdftostudy(query):

    attachresponse = dict()
    #  Modality types SR is an option, but for legacy was set to OT
    if query['studyuuid'] != "":
        # print(json.dumps(query))
        query = '{"Tags" : {"Modality":"DOC", "Manufacturer": "REPORT", "OperatorsName":"' + query['author'] + '", "SeriesDescription":"' + query['title'] + '","SOPClassUID":"1.2.840.10008.5.1.4.1.1.104.1"},"Content" : "data:application/pdf;base64,' + query['base64'] + '", "Parent":"' + query['studyuuid']+ '"}'
        temp = orthanc.RestApiPost('/tools/create-dicom',query)
        attachresponse['status'] = json.loads(temp)
        attachresponse['error'] = "false"
    else:
        attachresponse['error'] = "Missing UUID for parent study."

    return attachresponse;

def getpdf(query, output):

    response = dict()

    if query['method'] == "html":

        try:
            options = {
                'page-size': 'Letter',
                'margin-top': '0.75in',
                'margin-right': '0.75in',
                'margin-bottom': '0.75in',
                'margin-left': '0.75in',
                'footer-line':'',
                'footer-font-size':'12',
                'footer-center': 'Page [page] of [toPage], [date]',
                'encoding': 'utf-8'
            }
            pathtobinary = shutil.which("wkhtmltopdf")
            config = pdfkit.configuration(wkhtmltopdf=pathtobinary)
            pdf = pdfkit.from_string(query['html'], False,options=options)
            encoded = base64.b64encode(pdf).decode()
            # If attach flag is 1 then attach it to the studyuuid

            if query['attach'] == 1:
                query['base64'] = encoded
                response['attachresponse'] = attachbase64pdftostudy(query)
            if query['return'] == 1:
                response['base64'] = encoded
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

        except Exception as e:

            response['error'] = str(e)
            output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')

    elif query['method'] == "base64":
        response['attachresponse'] = attachbase64pdftostudy(query)
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')
    else:
        response['error'] = "Invalid Method"
        output.AnswerBuffer(json.dumps(response, indent = 3), 'application/json')


def HTMLTOPDF(output, uri, **request):

    if request['method'] != 'POST':
        output.SendMethodNotAllowed('POST')
    else:
        query = json.loads(request['body']) # allows control characters ?
        pdf = getpdf(query, output)

orthanc.RegisterRestCallback('/pdfkit/htmltopdf', HTMLTOPDF)


# Intercept native method to enable logging, custom .zip archive creation, e.g. with Radiant Viewer

# curl -k -v https://localhost:8042/studies/8a8cf898-ca27c490-d0c7058c-929d0581-2bbf104d/archive > Study.zip

def OnDownloadStudyArchive(output, uri, **request):

    host = "Not Defined"
    userprofilejwt = "Not Defined"
    if "headers" in request and "host" in request['headers']:
        host = request['headers']['host']
    if "headers" in request and "userprofilejwt" in request['headers']:
        userprofilejwt = request['headers']['userprofilejwt']
    logging.info("STUDY|DOWNLOAD_ARCHIVE|ID=" + request['groups'][0] + "  HOST=" + host + "  PROFILE=  " + userprofilejwt)
    new_zip = BytesIO()
    archive = orthanc.RestApiGet(uri)
    with ZipFile('/python/radiant_cd.zip', 'r') as radiant_zip:
        with ZipFile(new_zip, 'w') as new_archive:
            for item in radiant_zip.filelist:
                print(item.filename)
                new_archive.writestr(item, radiant_zip.read(item.filename))
            new_archive.writestr('archive.zip', archive)
    output.AnswerBuffer(new_zip.getvalue(), 'application/zip')

orthanc.RegisterRestCallback('/studies/(.*)/archive', OnDownloadStudyArchive)


# DISABLE FOR Now, Generates Documentation on Startup

# for (name, obj) in inspect.getmembers(orthanc):
#     if inspect.isroutine(obj):
#         print('Function %s():\n  Documentation: %s\n' % (name, inspect.getdoc(obj)))
#
#     elif inspect.isclass(obj):
#         print('Class %s:\n  Documentation: %s' % (name, inspect.getdoc(obj)))
#
#         # Loop over the members of the class
#         for (subname, subobj) in inspect.getmembers(obj):
#             if isinstance(subobj, numbers.Number):
#                 print('  - Enumeration value %s: %s' % (subname, subobj))
#             elif (not subname.startswith('_') and
#                 inspect.ismethoddescriptor(subobj)):
#                 print('  - Method %s(): %s' % (subname, inspect.getdoc(subobj)))
#         print('')
