#### Postgres DB operations from CLI in container

Initialization is not yet automatic for the Postgres orthanc_ris.

There is a starter DB, dump.sql here.
_
psql -U postgres -d postgres

DROP DATABASE IF EXISTS orthanc_ris;

CREATE DATABASE orthanc_ris;

pg_dump -U postgres -d orthanc_ris -W -f /postgres_init/dump.sql

psql -U postgres orthanc_ris < /postgres_init/dump.sql

\l is list

\connect orthanc_ris

DELETE FROM mwl;

\q is quit

