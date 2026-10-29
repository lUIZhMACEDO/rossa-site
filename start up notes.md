start up notes

#mailhog  
Move the File to a Folder (e.g., C:\MailHog)

You can place it anywhere, but keeping it in a dedicated folder is cleaner.
Run MailHog:

Open Command Prompt (cmd) or PowerShell.
Navigate to the directory where you placed the executable (cd C:\MailHog).
Run MailHog by typing:
powershell
Copy code
.\MailHog_windows_amd64.exe
Open your browser and go to http://localhost:8025 to see intercepted emails.

# next terminal
get into rossa directory and type: npm run dev
in the other rossa directory type: php artisan serve

# ClamAV setup
Run these commands from the rossa project directory:

```bash
sudo apt-get update
sudo apt-get install clamav clamav-daemon
sudo systemctl enable --now clamav-daemon clamav-freshclam
sudo systemctl status clamav-daemon clamav-freshclam --no-pager
clamdscan --version 
# you should see a version number here showing that it is properly installed
```

Run the first two commands and then change the following setting in the config
```bash
sudo cp /etc/clamav/clamd.conf /etc/clamav/clamd.conf.bak
sudo nano /etc/clamav/clamd.conf
```

MaxFileSize 2G
MaxScanSize 4G
MaxScanTime 1800000
StreamMaxLength 2G

sudo systemctl restart clamav-daemon

# ClamAV scan queue worker
php artisan queue:work --queue=malware-scans --timeout=1440

We do have a config file for this, for the server you may have to put the binary at a specific path using the env value #MEDIA_UPLOAD_CLAMD_SCAN_BINARY

#bypassing email verification 

php artisan tinker

$u = \App\Models\User::where('email', 'pmaye1@hotmail.com')->first();
$u->email_verified_at = now();
$u->save();
