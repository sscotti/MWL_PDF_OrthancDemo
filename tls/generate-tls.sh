#!/bin/bash
# Simply run sudo sh generate-tls.sh from the CLI
# lots of useful info grabbed from https://engineering.circle.com/https-authorized-certs-with-node-js-315e548354a2
#
# warning: this scripts are not intended to be used in a production environment.  Always ask a security expert !

# generate a new certificate authority
#-------------------------------------
openssl req -new -x509 -days 9999 -config ca.cnf -keyout ca-key.pem -out ca-crt.pem

# server
#-------

# generate a private key for the nginx server
openssl genrsa -out server-key.pem 4096

# generate a CSR (Certificate Signing Request) for the server certificate with the server private key
openssl req -new -config server.cnf -key server-key.pem -out server-csr.pem

# now let's sign the requests with our CA
openssl x509 -req -extfile server.cnf -days 999 -passin "pass:password" -in server-csr.pem -CA ca-crt.pem -CAkey ca-key.pem -CAcreateserial -out server-crt.pem

# verification
#-------------
openssl verify -CAfile ca-crt.pem server-crt.pem
