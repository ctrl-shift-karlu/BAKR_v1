# Contact page: how messages reach you

Every message is (1) saved in the `contact_messages` table and (2) emailed to BAKR_CONTACT_TO with Reply-To set to the student.
If email fails, the message is still in the table (`emailed_at` stays empty).

## Setup
1. Copy files, add the two routes from routes_site.php (or use the cleaned web.php), then:  php artisan migrate
2. .env while developing (writes the email into storage/logs/laravel.log instead of sending):
     MAIL_MAILER=log
     BAKR_CONTACT_TO=bakr.dnsc@gmail.com
3. .env for real sending with the Gmail account (turn on 2-Step Verification, then create an "App password"):
     MAIL_MAILER=smtp
     MAIL_HOST=smtp.gmail.com
     MAIL_PORT=587
     MAIL_USERNAME=bakr.dnsc@gmail.com
     MAIL_PASSWORD=<16-character app password, not the account password>
     MAIL_FROM_ADDRESS=bakr.dnsc@gmail.com
     MAIL_FROM_NAME="BAKR"
     BAKR_CONTACT_TO=bakr.dnsc@gmail.com
   then  php artisan config:clear
   (Laravel 13 has no MAIL_ENCRYPTION: port 587 upgrades to TLS by itself. Leave MAIL_SCHEME=null.)
4. Test: send a message from /contact; it should arrive in the bakr.dnsc@gmail.com inbox within seconds.
   If it does not, storage/logs/laravel.log has the reason and the message is still in contact_messages.

## Public feedback
Nothing is shown on the site until the student ticked "you may show my feedback" AND you set approved_at, for example:
  php artisan tinker  ->  App\Models\ContactMessage::find(3)->update(['approved_at' => now()]);
The invented sample testimonials and the fixed 4.7 rating were removed. The section appears only when approved feedback exists.
