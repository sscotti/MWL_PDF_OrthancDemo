FROM osimis/orthanc:22.9.2
ENV DEBIAN_FRONTEND=noninteractive
# disable http bundle since we're specifying http parameters in the orthanc.json configuration file
ENV HTTP_BUNDLE_DEFAULTS=false
# disable the auth defaults since we specify them in orthanc.json
ENV AC_BUNDLE_DEFAULTS=false

RUN mkdir /python
# /python is bound to the host folder ./orthanc_python, but Orthanc needs to be restarted to see changes.
# /lua-scripts already exists in the container, and bound to lua in this folder.  No need to restart to see changes, at least seems that way.

RUN apt-get update && apt-get --assume-yes install -y \
wget \
xz-utils \
fontconfig \
libfreetype6 \
libjpeg62-turbo \
libpng16-16 \
libx11-6 \
libxcb1 \
libxext6 \
libxrender1 \
xfonts-75dpi \
xfonts-100dpi \
xfonts-scalable \
xfonts-base \
dcmtk \
libpq-dev

# added libpq-dev for import psycopg2
# Below is for the ARM M1 Architecture, library to support the PDF functions

# RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_arm64.deb
# RUN dpkg -i wkhtmltox_0.12.6-1.buster_arm64.deb

RUN wget https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6-1/wkhtmltox_0.12.6-1.buster_amd64.deb
RUN dpkg -i wkhtmltox_0.12.6-1.buster_amd64.deb

RUN pip3 install pydicom pynetdicom pdfkit imgkit hl7 wkhtmltopdf mysql-connector-python requests
RUN pip3 install psycopg2

COPY docker-entrypoint.sh /