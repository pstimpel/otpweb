### WORK IN PROGRESS

# OTP web

## TOTP Token Generator

### What is OTP web?

OTP web lets you create TOTP tokens like they are used on many websites 
for 2 factor authentication (2FA). Usually, you receive a "secret" if 
you set 2FA for a webpage, and this secret enables you to create Time-based One Time Passwords - TOTP. 

There are many apps out there, but I found no web application I would trust, or have
pleased me based on user epxerience. So, I wrote my own.

"But there are cloud apps out there doing this ..." - I would never trust a cloud software on this, sorry.

### Requirements and Setup

- Postgres (9.5 or higher)
- Webserver that could run PHP7.4 or higher, like Apache
- Since you are dealing with security related stuff, enabling TLS on that webserver should be mandatory

Before proceeding with the setup, you will need to create a database, username and password on a PostgreSQL server your future OTP web can reach out to.

After seting up the database access, just download the archive, unpack it into a folder on your webserver. Point your browser to that URL.

Provide the PostgreSQL servers address, port, database name, database user and password. During this step, the OTP web tries to create folders and files needed for the upload of icons / logos, and database access. If this failes in your setup, please allow write access to your OTP webroot for the user your webserver is using (www-data?).

If it works out, you are almost done.

### Chosing a password

Chosing a password is a crucial thing. The more complex your password is, the better the security is. At the same time, it is harder to remember.

**OTP web** does not store your password in a database. And it uses your password to encrypt all the TOTP related data with strong crypto. This means, in case you lose your password, you will lose your data.

** THERE IS NOT PASSWORD RECOVERY POSSIBLE IN OTP WEB **

Therefore I suggest you using a strong password, but one that is easy to remember. I suggest a group of words creating a non-sense sentence:

"Last-night-I-ate-2-shoes!"

By using such an approach, you can create easo to remember, but complex passwords. Another way is to use such a sentence, and use the first characters of each word to form your password:

LnIa2s!

Simple to remember, but of course quite short and therefore less secure.

### Using OTP web

Using OTP web is quite easy. Just add a new entry, give it a description, enter the secret you got from that other page. After saving it, the entry appears on OTP web. You can add icons. Just find the logo of that page, make sure the graphics file is at least 64x64 px. in dimension, and make sure it is square. After you uploaded it, you can click that placeholder icon in front of a TOTP entry, and select your freshly uploaded icon.

### Backup and restore

You could backup your data by downloading a json file. This json file contains:

- the name of the TOTP entry, encrypted, base64 encoded
- the icon name of the TOTP entry, encrypted, base64 encoded
- the secret of the TOTP entry, encrypted, base64 encoded
- the "IV" of the TOTP entry, base64 encoded
- the timestamp the entry was created
- base64 encoded binary data of all the icons you uploaded to your installation of OTP web

You can put this file elsewhere, even on public clouds. For decrypting the the entries, the other party needs your password. And of course your password is strong, not stored in that backup-file, and not publicly available.

You can restore your data by just uploading the json file. 

**Attention: Restore will delete all existing data in your installation, and all icons as well - before restoring the data from the json file.**

### Update

Just git clone this repo, and overwrite your installation with the files from the git clone. 

### Security explained, and possible issues

All relevant data are enrypted by AES-256-CBC. Each data row has its own random "IV". The decryption happens using your hashed password. The password it stored nowhere, except in the session variable at the webserver. So yes, if someone can read your session, he/she might be able to decrypt the database entries - given this person has access to the database as well. 

I think this is a quite reasonable level of security for TOTPs, given an intruder would need the other credentials like usernames and passwords of that other webpage, to break into an 2FA secured web application.

### License, credits, technology used

Licensed under GPL3, see file LICENSE for more information.

TOTP generator itself and related stuff was originally written by **lfkeitel**, https://github.com/lfkeitel/php-totp, provided under MIT license. I used that code as it was provided, but changed the way it was implemented in OTP web. He was reusing code from **bbars**, https://github.com/lfkeitel/php-totp, provided under MIT license. 

NotifIt was created by **naoxink**, https://github.com/naoxink/notifIt, provided under MIT license.

Driven by 
- PHP https://www.php.net/
- Smarty 4 https://www.smarty.net/download 
- JQuery 3 https://jquery.com/
- Bootstrap 4 https://getbootstrap.com/
- FontAwesome 4 https://fontawesome.com/

### FAQ

**Is there a dockerized version?**

I did not create one. Feel free to do so. If you notify me, I will add a link to it. 

**I provided the wrong password, but the page did not refuse access**

Sure. It show empty entries on your OTP web, or filled the entries with gibberish. It might even be able to generate tokens. But if you double-check the entries, all those tokens are wrong - guaranteed. This is because your password was not stored anywhere, any therefore OTP web cannot know if the password was wrong. Is this a security issue? I do not think so, because your TOTPs are still secure.



