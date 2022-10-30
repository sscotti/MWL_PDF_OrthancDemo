FROM ohif/viewer:latest as ohif

FROM nginx:1.21-alpine
RUN mkdir /host_tls
COPY --from=ohif /usr/share/nginx/html/ /usr/share/nginx/html/
COPY mime.types /etc/nginx/mime.types
