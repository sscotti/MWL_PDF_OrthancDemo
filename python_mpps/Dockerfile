FROM python:latest
ENV DEBIAN_FRONTEND=noninteractive
# disable http bundle since we're specifying http parameters in the orthanc.json configuration file
# ENV HTTP_BUNDLE_DEFAULTS=false
# disable the auth defaults since we specify them in orthanc.json
# ENV AC_BUNDLE_DEFAULTS=false

RUN pip3 install pydicom
RUN pip3 install pynetdicom
RUN pip3 install pdfkit
RUN pip3 install hl7
RUN pip3 install wkhtmltopdf
RUN pip3 install mysql-connector-python
RUN pip3 install requests
RUN pip3 install pyorthanc

RUN mkdir /scripts
RUN mkdir /MWL
RUN mkdir /etc/python/tls

RUN apt-get update

RUN apt-get --assume-yes install dcmtk

COPY entrypoint.sh /
ENTRYPOINT ["/bin/bash","/entrypoint.sh"]


