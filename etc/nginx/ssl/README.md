# SSL configuration
This folder is empty because we can not share our SSL certificates, they should stay a secret. Of course we could check
in the public stuff that anybody can grab from us anyways, but it does not make a lot of sense to check this stuff in.

Instead we’d like to include a little how to at this point and explain our SSL configuration. Right now we’re using
certificates from [StartSSL](https://startssl.com/), the reason for this is simple, they are free of charge. So if you
want to experiment with SSL this is your start point, go to StartSSL and get yourself a certificate. For a normal
website it’s more than sufficient to create a key with 2048 bits and use a SHA-1 algorithm to generate the signature.
If you think that you need more than that be advised that it will mainly affect the performance of establishing a
connection to your website, because the initial handshake will take much more time.

The process of deploying an SSL certificate for one of your servers is as follows, all commands executed from this
folder and as root user:

```Bash
mkdir example.com/www
# Put your passphrase protected private key in this file and be sure to include an empty line at the end of the file
editor example.com/www/pass
# Put your server certificate in this file and be sure to include an empty line at the end of the file
editor example.com/www/crt
openssl rsa -in example.com/www/pass -out example.com/www/key
wget http://www.startssl.com/certs/ca.pem
wget http://www.startssl.com/certs/sub.class1.server.ca.pem
cat example.com/www/crt sub.class1.server.ca.pem > example.com/www/unified.crt
```

I won’t go into more detail on each step, as it is pretty straight forward and I’ve included some weblinks where you can
find more info. If you want to know what you have to add to your nginx configuration, check our various server
configurations in the sites directory.

## Weblinks
* [Qualys SSL Labs: SSL Server Test](https://www.ssllabs.com/ssltest/analyze.html)
* [Qualys SSL Labs: SSL/TLS Deployment Best Practices](https://www.ssllabs.com/projects/best-practices/index.html)
* [Qualys SSL Labs: Deploying Forward Secrecy](https://community.qualys.com/blogs/securitylabs/2013/06/25/ssl-labs-deploying-forward-secrecy)
* [StartSSL: NGINX Server](https://www.startssl.com/?app=42)
* [Hynek Schlawack: Hardening Your Web Server’s SSL Ciphers](http://hynek.me/articles/hardening-your-web-servers-ssl-ciphers/)
* [Unhandled expression: 5 easy tips to accelerate SSL](http://unhandledexpression.com/2013/01/25/5-easy-tips-to-accelerate-ssl/)
