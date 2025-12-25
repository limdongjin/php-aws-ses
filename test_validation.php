<?php
/**
 * Input Validation Examples
 *
 * This file demonstrates the input validation features of SimpleEmailService
 */

require_once 'SimpleEmailService.php';

// Configure (use fake credentials for testing validation)
$ses = new SimpleEmailService('FAKE_KEY', 'FAKE_SECRET');

echo "=== SimpleEmailService Input Validation Tests ===\n\n";

// Test 1: Valid email
echo "Test 1: Valid email format\n";
$result = $ses->sendEmail(
    'valid@example.com',
    'recipient@example.com',
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Failed (expected - no real credentials)" : "Passed") . "\n\n";

// Test 2: Invalid sender email
echo "Test 2: Invalid sender email (should trigger error)\n";
error_reporting(E_ALL);
$result = $ses->sendEmail(
    'invalid-email',  // Invalid format
    'recipient@example.com',
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - rejected invalid sender" : "Failed") . "\n\n";

// Test 3: Invalid recipient email
echo "Test 3: Invalid recipient email (should trigger error)\n";
$result = $ses->sendEmail(
    'sender@example.com',
    'not-an-email',  // Invalid format
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - rejected invalid recipient" : "Failed") . "\n\n";

// Test 4: Empty subject
echo "Test 4: Empty subject (should trigger error)\n";
$result = $ses->sendEmail(
    'sender@example.com',
    'recipient@example.com',
    '',  // Empty subject
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - rejected empty subject" : "Failed") . "\n\n";

// Test 5: Empty message body
echo "Test 5: Empty message body (should trigger error)\n";
$result = $ses->sendEmail(
    'sender@example.com',
    'recipient@example.com',
    'Test Subject',
    ''  // Empty body
);
echo "Result: " . ($result === false ? "Validation worked - rejected empty body" : "Failed") . "\n\n";

// Test 6: Too many recipients
echo "Test 6: Too many recipients (should trigger error)\n";
$tooManyRecipients = array();
for ($i = 0; $i < 51; $i++) {
    $tooManyRecipients[] = "user{$i}@example.com";
}
$result = $ses->sendEmail(
    'sender@example.com',
    $tooManyRecipients,
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - rejected too many recipients" : "Failed") . "\n\n";

// Test 7: Subject too long
echo "Test 7: Subject exceeds maximum length (should trigger error)\n";
$longSubject = str_repeat('A', 1000);  // 1000 characters, exceeds 998 limit
$result = $ses->sendEmail(
    'sender@example.com',
    'recipient@example.com',
    $longSubject,
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - rejected long subject" : "Failed") . "\n\n";

// Test 8: Multiple recipients with one invalid
echo "Test 8: Multiple recipients with one invalid (should trigger error)\n";
$mixedRecipients = array(
    'valid1@example.com',
    'invalid-email',  // Invalid
    'valid2@example.com'
);
$result = $ses->sendEmail(
    'sender@example.com',
    $mixedRecipients,
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - detected invalid recipient in list" : "Failed") . "\n\n";

// Test 9: Email with whitespace (should be trimmed and accepted)
echo "Test 9: Email with surrounding whitespace (should be trimmed)\n";
$result = $ses->sendEmail(
    '  sender@example.com  ',  // Whitespace will be trimmed
    '  recipient@example.com  ',
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Failed (expected - no real credentials)" : "Passed") . "\n\n";

// Test 10: Invalid email - missing @
echo "Test 10: Invalid email - missing @ symbol (should trigger error)\n";
$result = $ses->sendEmail(
    'sender.example.com',  // Missing @
    'recipient@example.com',
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - rejected email without @" : "Failed") . "\n\n";

// Test 11: Invalid email - missing domain
echo "Test 11: Invalid email - missing domain (should trigger error)\n";
$result = $ses->sendEmail(
    'sender@',  // Missing domain
    'recipient@example.com',
    'Test Subject',
    'Test message body'
);
echo "Result: " . ($result === false ? "Validation worked - rejected email without domain" : "Failed") . "\n\n";

// Test 12: verifyEmailAddress validation
echo "Test 12: Verify invalid email address (should trigger error)\n";
$result = $ses->verifyEmailAddress('not-valid-email');
echo "Result: " . ($result === false ? "Validation worked - rejected invalid email for verification" : "Failed") . "\n\n";

echo "=== All validation tests completed ===\n";
