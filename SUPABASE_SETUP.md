# Supabase setup


SUPABASE ACCOUNT IS IN WEBMAIL OF info@theglobaloutsource.com


1. Create a Supabase project and run `supabase/migrations/20261023000000_create_admin_careers_contacts.sql` in the SQL Editor.
2. Create the first administrator in **Authentication → Users**. Then run the bootstrap SQL at the end of the migration, replacing `ADMIN_EMAIL_HERE` with that user's email address.
3. Add the project URL, anon key, and service-role key to the root `.env.local` file. Set `APP_BASE_URL` to the exact site origin, such as `https://acctechs.ie` or `http://localhost:8000`. The local `.env.local` is excluded from Git and denied by the PHP web server.
4. Add `{APP_BASE_URL}/admin/reset-password` to the Supabase Auth redirect URL allow list. Configure Supabase Auth email delivery so administrator invitations and password reset messages can be sent.

The service-role key is used only by PHP and must never be added to browser JavaScript, HTML, or a public environment variable. The public contact form, administrator dashboard, and careers listing require the migration to be applied and all three Supabase keys to be configured.

Open `/admin/login` after the first administrator has been bootstrapped. Administrators can invite or remove other administrators, manage contact enquiries, and create, edit, publish, close, or delete career listings. Only jobs with status **Open** are displayed on `/careers`.

If the dashboard reports a Supabase connection issue, check the PHP error log for the server-side cause. A missing-table or schema-cache message means the migration has not completed or Supabase's API schema cache needs a refresh. A 401/403 message usually means the server-role key is incorrect or the migration's service-role grants have not been applied. The dashboard shows available counts separately, so one unavailable table will not hide the other sections.
