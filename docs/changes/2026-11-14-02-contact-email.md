# 2026-11-14 — Contact Us sends real email

- **`.env`:** Gmail SMTP (`MAIL_MAILER=smtp`, `smtp.gmail.com:587`, `MAIL_USERNAME`/`MAIL_FROM_ADDRESS` = bakr.dnsc@gmail.com, App Password).
  - `.env` is git-ignored.
  - `MAIL_ENCRYPTION` is gone from `CONTACT-SETUP.md`: Laravel 13 does not use it.
- **First attempt failed** with "certificate verify failed". AVG Web/Mail Shield was intercepting SMTP with its own root certificate. The owner uninstalled AVG; certificate checking was never turned off.
- **Test email sent:** Gmail certificate chain WE2 <- GTS Root R4. It arrived in the bakr.dnsc@gmail.com inbox.
- **Full Contact Us path tested** (save to `contact_messages`, then email): `emailed_at` was set, and the test row was deleted afterwards.
- **MySQL crash after the AVG removal:** InnoDB "aio write returned OS error 206" on `ibtmp1`.
  - Fixed by adding `innodb_use_native_aio = 0` under `[mysqld]` in `C:
mpp\mysqlin\my.ini`.
  - Backup: `my.ini.bak-20261114-184052`. The data was intact.
