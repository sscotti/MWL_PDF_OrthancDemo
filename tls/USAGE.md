NOTE:

You can use the generate.tls.sh script to generate self-signed certs for usage with the container, or use your own legitimate certificates.  The docker-compose.yml maps this folder to the container certificates locations, at least for the nginx containers, so no need to copy for development.  Not really secure though for production setup.

It is immportant the you rename / keep the .crt and .key files to have the names nginx-crt.pem and nginx-key.pem  respectively since this is what the nginx default.conf file expects.  They also need to be in .pem format.

```
Or, visit:  https://letsencrypt.org/  to see how to get free ones.
```

