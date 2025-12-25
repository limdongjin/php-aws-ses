# Simple AWS SES PHP Library

A simplified, single-file PHP library for sending emails via Amazon Simple Email Service (SES).

## Features

- **Single File**: No dependencies, no composer required
- **PHP 5.6+ Compatible**: Works with PHP 5.6 and higher
- **Simple API**: Easy to use, minimal configuration
- **AWS Signature V4**: Secure authentication with AWS

## Requirements

- PHP 5.6 or higher
- cURL extension
- SimpleXML extension
- Valid AWS credentials with SES permissions

## Installation

Simply download `SimpleEmailService.php` and include it in your project:

```php
require_once 'SimpleEmailService.php';
```

## Quick Start

### 1. Send a Simple Email

```php
<?php
require_once 'SimpleEmailService.php';

$ses = new SimpleEmailService(
    'YOUR_AWS_ACCESS_KEY',
    'YOUR_AWS_SECRET_KEY',
    SimpleEmailService::AWS_US_EAST_1
);

$result = $ses->sendEmail(
    'sender@example.com',           // From
    'recipient@example.com',         // To
    'Test Email',                    // Subject
    'This is a plain text message',  // Text body
    '<h1>This is HTML message</h1>'  // HTML body (optional)
);

if ($result) {
    echo "Email sent successfully!";
} else {
    echo "Failed to send email.";
}
```

### 2. Send to Multiple Recipients

```php
$result = $ses->sendEmail(
    'sender@example.com',
    array('user1@example.com', 'user2@example.com'),
    'Test Email',
    'Message to multiple recipients'
);
```

### 3. Verify Email Address

Before sending emails, you need to verify your email address (or domain):

```php
$result = $ses->verifyEmailAddress('your-email@example.com');
if ($result) {
    echo "Verification email sent. Please check your inbox.";
}
```

### 4. List Verified Email Addresses

```php
$result = $ses->listVerifiedEmailAddresses();
if ($result) {
    print_r($result);
}
```

### 5. Get Send Quota

```php
$quota = $ses->getSendQuota();
if ($quota) {
    echo "24 Hour Send Limit: " . $quota['GetSendQuotaResult']['Max24HourSend'] . "\n";
    echo "Sent Last 24 Hours: " . $quota['GetSendQuotaResult']['SentLast24Hours'] . "\n";
}
```

## Available AWS Regions

```php
SimpleEmailService::AWS_US_EAST_1    // US East (N. Virginia)
SimpleEmailService::AWS_US_WEST_2    // US West (Oregon)
SimpleEmailService::AWS_EU_WEST_1    // EU (Ireland)
```

## API Reference

### Constructor

```php
new SimpleEmailService($accessKey, $secretKey, $host = SimpleEmailService::AWS_US_EAST_1)
```

### Methods

#### sendEmail($from, $to, $subject, $messageText, $messageHtml = null)

Send an email via SES.

- `$from` (string): Sender email address (must be verified)
- `$to` (string|array): Recipient email address(es)
- `$subject` (string): Email subject
- `$messageText` (string): Plain text message body
- `$messageHtml` (string, optional): HTML message body
- Returns: `array|false` - Response array on success, false on failure

#### verifyEmailAddress($email)

Request verification for an email address.

- `$email` (string): Email address to verify
- Returns: `array|false` - Response array on success, false on failure

#### listVerifiedEmailAddresses()

Get list of verified email addresses.

- Returns: `array|false` - Array of verified emails on success, false on failure

#### getSendQuota()

Get your sending quota information.

- Returns: `array|false` - Quota information on success, false on failure

## Error Handling

Methods return `false` on error. For production use, consider adding proper error logging:

```php
$result = $ses->sendEmail($from, $to, $subject, $text);
if ($result === false) {
    error_log("Failed to send email to: " . $to);
}
```

## Security Notes

1. **Never commit AWS credentials** to version control
2. Use environment variables or configuration files outside web root
3. Use IAM roles when running on EC2/Lambda
4. Grant minimum required SES permissions

## Examples

See `example.php` for more usage examples.

## What Changed (Simplified Version)

This is a simplified, single-file version of the original php-aws-ses library:

- **Removed**: Composer, tests, CI/CD configurations
- **Removed**: Complex features (attachments, raw email, bulk mode, message tags)
- **Kept**: Core functionality for sending simple emails
- **Simplified**: Single class with straightforward API
- **Compatible**: PHP 5.6+ (no modern PHP syntax required)

For the full-featured version, see [original repository](https://github.com/daniel-zahariev/php-aws-ses).

## License

MIT License

Copyright (c) 2014-2025

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
