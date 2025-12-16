# Check If Email Exists (PHP)

This is a PHP library to check if an email address exists and is deliverable.

## Installation

```bash
composer require shyim/check-if-email-exists
```

## Usage

### CLI

```bash
# After cloning the repo
./bin/check-email test@example.com

# After composer install (global or local)
vendor/bin/check-email test@example.com
```

### Programmatic API

```php
use Shyim\CheckIfEmailExists\EmailChecker;

require 'vendor/autoload.php';

$checker = new EmailChecker();
$result = $checker->check('test@example.com');

print_r($result->toArray());
```

## Features

- Syntax validation
- DNS MX record check
- SMTP connection verification (HELO, MAIL FROM, RCPT TO)
- Catch-all detection
- Role account detection
- Disposable email detection

## License

MIT