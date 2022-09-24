NOTE:

You can use the generate.tls.sh script to generate self-signed certs for usage with the container, or use your own legitimate certificates.

It is immportant the you rename the .crt and .key files to have the names nginx-crt.pem and nginx-key.pem  respectively since this is what the nginx default.conf file expects.  They also need to be in .pem format.

```
Or, visit:  https://letsencrypt.org/  to see how to get free ones.
```

The copy-tls-to-docker-volumes script does not work that well really.

The .gitignore automatically excludes:

ca-crt.pem
ca-key.pem
ca-crt.srl
nginx-key.pem
nginx-csr.pem
nginx-crt.pem


