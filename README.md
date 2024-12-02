# Sign up and login
Sample implementation of sign up with email activation, login and password reset via email confirmation in PHP.

It uses the [PHPMailer](https://github.com/PHPMailer/PHPMailer) library to send emails and [SendMail](https://www.mailersend.com/) as SMTP server.

## Code diagram
![Code diagram](docs/code_diagram.png)

## Class diagram
![Class diagram](docs/class_diagram.png)

## Installation
1. A `.env` file must be created in the root folder with the following values:
    ```
    APP_BASE_URL=<your_application_base_url>
    MAILER_HOST=<your_smtp_provider_host_address>
    MAILER_PORT=<your_smtp_provider_port>
    MAILER_USERNAME=<your_smtp_provider_username>
    MAILER_PASSWORD=<your_smtp_provider_password>
    ```

2. The MySQL/MariaDB database `login_db` must be generated by running the script `docs/login_db.sql`

## Tools
MariaDB / PHPMailer / PHP8 / Water.css / CSS3 / HTML5

## Author
Arturo Mora-Rioja, based on the course <em>[PHP Signup and Login](https://www.youtube.com/playlist?list=PLFbnPuoQkKsecy8YatFtdcQ2epiakgbrd)</em> by [Dave Hollingworth](https://davehollingworth.com/).