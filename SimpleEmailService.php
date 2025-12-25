<?php
/**
 * Simple AWS SES PHP Library for PHP 5.6+
 *
 * A simplified, single-file implementation for sending emails via Amazon SES
 *
 * @version 1.0.0
 * @license MIT
 */

class SimpleEmailService {
    const AWS_US_EAST_1 = 'email.us-east-1.amazonaws.com';
    const AWS_US_WEST_2 = 'email.us-west-2.amazonaws.com';
    const AWS_EU_WEST_1 = 'email.eu-west-1.amazonaws.com';

    protected $accessKey;
    protected $secretKey;
    protected $host;

    /**
     * Constructor
     *
     * @param string $accessKey AWS Access Key
     * @param string $secretKey AWS Secret Key
     * @param string $host AWS SES Region Host
     */
    public function __construct($accessKey, $secretKey, $host = self::AWS_US_EAST_1) {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->host = $host;
    }

    /**
     * Send email
     *
     * @param string $from Sender email
     * @param array|string $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $messageText Plain text message
     * @param string $messageHtml HTML message (optional)
     * @return array|false Response array or false on error
     */
    public function sendEmail($from, $to, $subject, $messageText, $messageHtml = null) {
        $params = array();
        $params['Action'] = 'SendEmail';
        $params['Source'] = $from;

        // Add recipients
        $recipients = is_array($to) ? $to : array($to);
        $i = 1;
        foreach ($recipients as $recipient) {
            $params['Destination.ToAddresses.member.' . $i] = $recipient;
            $i++;
        }

        // Add subject
        $params['Message.Subject.Data'] = $subject;
        $params['Message.Subject.Charset'] = 'UTF-8';

        // Add message body
        if ($messageText) {
            $params['Message.Body.Text.Data'] = $messageText;
            $params['Message.Body.Text.Charset'] = 'UTF-8';
        }

        if ($messageHtml) {
            $params['Message.Body.Html.Data'] = $messageHtml;
            $params['Message.Body.Html.Charset'] = 'UTF-8';
        }

        $response = $this->sendRequest($params);

        if ($response && !isset($response['Error'])) {
            return $response;
        }

        return false;
    }

    /**
     * Verify email address
     *
     * @param string $email Email address to verify
     * @return array|false Response array or false on error
     */
    public function verifyEmailAddress($email) {
        $params = array();
        $params['Action'] = 'VerifyEmailAddress';
        $params['EmailAddress'] = $email;

        return $this->sendRequest($params);
    }

    /**
     * List verified email addresses
     *
     * @return array|false Array of verified emails or false on error
     */
    public function listVerifiedEmailAddresses() {
        $params = array();
        $params['Action'] = 'ListVerifiedEmailAddresses';

        return $this->sendRequest($params);
    }

    /**
     * Get send quota
     *
     * @return array|false Quota information or false on error
     */
    public function getSendQuota() {
        $params = array();
        $params['Action'] = 'GetSendQuota';

        return $this->sendRequest($params);
    }

    /**
     * Send request to AWS SES
     *
     * @param array $params Request parameters
     * @return array|false Response array or false on error
     */
    protected function sendRequest($params) {
        ksort($params);
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC1738);

        $url = 'https://' . $this->host . '/';
        $headers = $this->getHeaders($query);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        }

        curl_close($ch);

        if ($httpCode == 200) {
            $xml = simplexml_load_string($response);
            if ($xml) {
                return $this->parseXmlResponse($xml);
            }
        }

        return false;
    }

    /**
     * Parse XML response from AWS
     *
     * @param SimpleXMLElement $xml XML response
     * @return array Parsed response
     */
    protected function parseXmlResponse($xml) {
        $result = array();

        foreach ($xml->children() as $child) {
            $name = $child->getName();

            if ($child->count() > 0) {
                $result[$name] = $this->parseXmlElement($child);
            } else {
                $result[$name] = (string) $child;
            }
        }

        return $result;
    }

    /**
     * Parse XML element recursively
     *
     * @param SimpleXMLElement $element XML element
     * @return array|string Parsed element
     */
    protected function parseXmlElement($element) {
        $result = array();

        foreach ($element->children() as $child) {
            $name = $child->getName();

            if ($child->count() > 0) {
                $result[$name] = $this->parseXmlElement($child);
            } else {
                $result[$name] = (string) $child;
            }
        }

        return $result;
    }

    /**
     * Get request headers with AWS Signature V4
     *
     * @param string $query Query string
     * @return array Headers array
     */
    protected function getHeaders($query) {
        $datetime = new DateTime('now', new DateTimeZone('UTC'));
        $amzDate = $datetime->format('Ymd\THis\Z');
        $dateStamp = $datetime->format('Ymd');

        $hostParts = explode('.', $this->host);
        $service = $hostParts[0];
        $region = $hostParts[1];

        $canonicalUri = '/';
        $canonicalQuerystring = '';
        $canonicalHeaders = 'host:' . $this->host . "\n" . 'x-amz-date:' . $amzDate . "\n";
        $signedHeaders = 'host;x-amz-date';
        $payloadHash = hash('sha256', $query);

        $canonicalRequest = "POST\n" . $canonicalUri . "\n" . $canonicalQuerystring . "\n" .
                           $canonicalHeaders . "\n" . $signedHeaders . "\n" . $payloadHash;

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = $dateStamp . '/' . $region . '/' . $service . '/aws4_request';
        $stringToSign = $algorithm . "\n" . $amzDate . "\n" . $credentialScope . "\n" .
                       hash('sha256', $canonicalRequest);

        $signingKey = $this->getSigningKey($dateStamp, $region, $service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorizationHeader = $algorithm . ' Credential=' . $this->accessKey . '/' .
                              $credentialScope . ', SignedHeaders=' . $signedHeaders .
                              ', Signature=' . $signature;

        return array(
            'Content-Type: application/x-www-form-urlencoded',
            'X-Amz-Date: ' . $amzDate,
            'Host: ' . $this->host,
            'Authorization: ' . $authorizationHeader
        );
    }

    /**
     * Get AWS Signature V4 signing key
     *
     * @param string $dateStamp Date stamp
     * @param string $region AWS region
     * @param string $service AWS service
     * @return string Signing key
     */
    protected function getSigningKey($dateStamp, $region, $service) {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $this->secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        return $kSigning;
    }
}
