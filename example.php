<?php
/**
 * Simple AWS SES Usage Example
 */

require_once 'SimpleEmailService.php';

// Configure your AWS credentials
$awsAccessKey = 'YOUR_AWS_ACCESS_KEY';
$awsSecretKey = 'YOUR_AWS_SECRET_KEY';
$awsRegion = SimpleEmailService::AWS_US_EAST_1;

// Create SES instance
$ses = new SimpleEmailService($awsAccessKey, $awsSecretKey, $awsRegion);

// Example 1: Send a simple text email
echo "Example 1: Sending simple text email...\n";
$result = $ses->sendEmail(
    'sender@example.com',
    'recipient@example.com',
    'Test Email from Simple SES',
    'This is a plain text message sent via Amazon SES.'
);

if ($result) {
    echo "Success! Email sent.\n";
    print_r($result);
} else {
    echo "Failed to send email.\n";
}

echo "\n---\n\n";

// Example 2: Send HTML email
echo "Example 2: Sending HTML email...\n";
$htmlBody = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>Hello from Amazon SES!</h1>
    <p>This is an <strong>HTML</strong> email.</p>
</body>
</html>
';

$result = $ses->sendEmail(
    'sender@example.com',
    'recipient@example.com',
    'HTML Email Test',
    'This is the plain text version.',
    $htmlBody
);

if ($result) {
    echo "HTML email sent successfully!\n";
}

echo "\n---\n\n";

// Example 3: Send to multiple recipients
echo "Example 3: Sending to multiple recipients...\n";
$recipients = array(
    'user1@example.com',
    'user2@example.com',
    'user3@example.com'
);

$result = $ses->sendEmail(
    'sender@example.com',
    $recipients,
    'Bulk Email Test',
    'This message is sent to multiple recipients.'
);

if ($result) {
    echo "Bulk email sent successfully!\n";
}

echo "\n---\n\n";

// Example 4: Verify an email address
echo "Example 4: Verifying email address...\n";
$result = $ses->verifyEmailAddress('newemail@example.com');

if ($result) {
    echo "Verification email sent. Please check the inbox.\n";
}

echo "\n---\n\n";

// Example 5: List verified email addresses
echo "Example 5: Listing verified email addresses...\n";
$result = $ses->listVerifiedEmailAddresses();

if ($result) {
    echo "Verified email addresses:\n";
    print_r($result);
}

echo "\n---\n\n";

// Example 6: Get send quota
echo "Example 6: Getting send quota...\n";
$result = $ses->getSendQuota();

if ($result) {
    if (isset($result['GetSendQuotaResult'])) {
        $quota = $result['GetSendQuotaResult'];
        echo "Max 24 Hour Send: " . $quota['Max24HourSend'] . "\n";
        echo "Max Send Rate: " . $quota['MaxSendRate'] . "\n";
        echo "Sent Last 24 Hours: " . $quota['SentLast24Hours'] . "\n";
    }
}

echo "\nAll examples completed.\n";
