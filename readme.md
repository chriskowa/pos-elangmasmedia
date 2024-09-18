## About Ultimate POS

Ultimate POS is a POS application by [Ultimate Fosters](http://ultimatefosters.com), a brand of [The Web Fosters](http://thewebfosters.com).

## Installation & Documentation
You will find installation guide and documentation in the downloaded zip file.
Also, For complete updated documentation of the ultimate pos please visit online [documentation guide](http://ultimatefosters.com/ultimate-pos/).

## Security Vulnerabilities

If you discover a security vulnerability within ultimate POS, please send an e-mail to support at thewebfosters@gmail.com. All security vulnerabilities will be promptly addressed.

## License

The Ultimate POS software is licensed under the [Codecanyon license](https://codecanyon.net/licenses/standard).

==================

APP_NAME="Gangsar Jaya"
APP_TITLE="Gangsar Jaya"
APP_ENV="live"
APP_KEY=base64:W8UqtE9LHZW+gRag78o4BCbN1M0w4HdaIFdLqHJ/9PA=
APP_DEBUG="false"
APP_LOG_LEVEL=debug
APP_URL="http://localhost/gangsar_makmur/public"
APP_LOCALE=en
APP_TIMEZONE="Asia/Kolkata"

ADMINISTRATOR_USERNAMES=gangsar
ALLOW_REGISTRATION=true

LOG_CHANNEL=daily

DB_CONNECTION=mysql
DB_HOST="localhost"
DB_PORT="3306"
DB_DATABASE="gangsar_makmur"
DB_USERNAME="root"
DB_PASSWORD=

BROADCAST_DRIVER=pusher
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER="sendmail"
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="gangsar@gmail.com"
MAIL_FROM_NAME="Gangsar Jaya"

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=

ENVATO_PURCHASE_CODE="MalangJakarta"
MAC_LICENCE_CODE=

BACKUP_DISK="local"
DROPBOX_ACCESS_TOKEN=

#Configuration details for superadmin modules

#Stripe payment details for superadmin module
STRIPE_PUB_KEY=
STRIPE_SECRET_KEY=

#PayPal payment setup details (NEW)
PAYPAL_CLIENT_ID=
PAYPAL_APP_SECRET=
PAYPAL_MODE=sandbox


#PayPal payment details for superadmin module (OLD)
#PayPal Setting & API Credentials - sandbox
PAYPAL_SANDBOX_API_USERNAME=
PAYPAL_SANDBOX_API_PASSWORD=
PAYPAL_SANDBOX_API_SECRET=
#PayPal Setting & API Credentials - live
PAYPAL_LIVE_API_USERNAME=
PAYPAL_LIVE_API_PASSWORD=
PAYPAL_LIVE_API_SECRET=

#Razor pay API credentials
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=

#Pesapal API details
PESAPAL_CONSUMER_KEY=
PESAPAL_CONSUMER_SECRET=
PESAPAL_CURRENCY=KES
PESAPAL_LIVE=false
GOOGLE_MAP_API_KEY=

#Paystack API details
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
PAYSTACK_PAYMENT_URL=https://api.paystack.co
MERCHANT_EMAIL="${MAIL_USERNAME}"

#Flutterwave API details
FLUTTERWAVE_PUBLIC_KEY=
FLUTTERWAVE_SECRET_KEY=
FLUTTERWAVE_ENCRYPTION_KEY=

ENABLE_GST_REPORT_INDIA=

#OpenAI Key details for AI Assistance module
OPENAI_API_KEY=
OPENAI_ORGANIZATION=

#S3 Backup
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=

# MyFatoorah gateway

MY_FATOORAH_API_KEY=
MY_FATOORAH_IS_TEST=
MY_FATOORAH_COUNTRY_ISO=

# GOOGLE RECAPTCHA

ENABLE_RECAPTCHA="false"
GOOGLE_RECAPTCHA_KEY=
GOOGLE_RECAPTCHA_SECRET=