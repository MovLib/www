# SMTP configuration
We are using an SMTP server to deliver our mails. This configuration file is read by the `\MovLib\Utility\Mailer` class
upon instantiation. Rename the `smtp.example.json` file to `smtp.json` and fill out all fields according to your SMTP
server configuration.

Please note that as of now the Mailer has the following requirements:
* Only TLS encrypted connections are supported.
* Only SMTP servers with authentication are supported.
* Only SMTP servers with secure login (´CRAM-MD5´) are supported.
