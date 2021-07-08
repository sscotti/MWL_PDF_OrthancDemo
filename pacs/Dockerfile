FROM osimis/orthanc
ENV DEBIAN_FRONTEND=noninteractive
# disable http bundle since we're specifying http parameters in the orthanc.json configuration file
ENV HTTP_BUNDLE_DEFAULTS=false
# disable the auth defaults since we specify them in orthanc.json
ENV AC_BUNDLE_DEFAULTS=false

RUN pip3 install pydicom
RUN pip3 install pynetdicom
RUN pip3 install pdfkit
RUN pip3 install hl7
RUN pip3 install wkhtmltopdf
RUN pip3 install mysql-connector-python
RUN pip3 install requests


RUN mkdir /python
# /python is bound to the host folder ./pacs-1/python, but Orthanc needs to be restarted to see changes.
# /lua-scripts already exists in the container, and bound to lua in this folder.  No need to restart to see changes, at least seems that way.

RUN apt-get update

# RUN apt-get --assume-yes install wkhtmltopdf
# These replace the above, QT support
# asks for a keyboard type with xorg installation.

RUN apt-get --assume-yes install xvfb
RUN apt-get --assume-yes install openssl
RUN apt-get --assume-yes install xorg
RUN apt-get --assume-yes install libssl1.1
RUN apt-get --assume-yes install libcurl4

COPY wkhtmltox-0.12.4_linux-generic-amd64.tar.xz /
RUN tar xvJf wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
RUN cp wkhtmltox/bin/wkhtmlto* /usr/bin/

RUN apt-get --assume-yes install dcmtk

COPY orthanc.json /etc/orthanc/
COPY osimis_viewer.json /etc/orthanc/
COPY stone.json /etc/orthanc/
COPY docker-entrypoint.sh /