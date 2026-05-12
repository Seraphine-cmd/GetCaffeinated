# Get Caffeinated

Website for Get Caffeinated in Barangay Caunayan, City of Batac, Ilocos Norte, under Three Jewels Tower.

## Project Structure

- `Pages/` - public-facing HTML/PHP pages.
- `Assets/styles/` - page stylesheets.
- `Assets/scripts/` - browser JavaScript.
- `Assets/Images/` - site images and icons.
- `php/` - server-side PHP handlers and database connection.
- `database/` - SQL schema for deployment.

## Pages

- `Home.html` - landing page.
- `Menu.html` - menu and cart add actions.
- `Cart.html` - browser-saved cart review.
- `About.html` - cafe story and values.
- `Contact.html` - contact details and message form.
- `Review.html` - customer feedback form with 0-5 star rating.
- `LoginPage.html` - member login.
- `Signup.html` - account registration.
- `ForgotPassword.html` - password reset form.
- `Profile.php` - member dashboard with visits and order tracking.
- `EditProfile.php` - account profile and password settings.

## Database Setup

1. Import `database/schema.sql` into MySQL or MariaDB.
2. Set database credentials in the server environment when deploying:
   - `DB_HOST`
   - `DB_USER`
   - `DB_PASSWORD`
   - `DB_NAME`
3. If environment variables are not set, `php/db.php` defaults to local XAMPP-style credentials and database `get_caffeinated`.

## Deployment Notes

- Serve the project through a PHP-enabled web server such as Apache with PHP and MySQL.
- Keep `php/` server-side files enabled so `Review.html` can submit to `php/submit_review.php`.
- Keep sessions enabled for login, signup, logout, checkout order saving, and `Profile.php`.
- The circular profile menu in the header links to dashboard, profile editing, order/visit tracking, settings, and logout.
- Replace the placeholder contact email in `Pages/Contact.html` before production launch.
- For public production, connect password resets to a real email token flow before allowing open reset requests.
