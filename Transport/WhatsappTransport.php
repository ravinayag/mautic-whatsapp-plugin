<?php
/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @author      Jan Kozak <galvani78@gmail.com>
 * @author      Ravinayag <ravinayag@gmail.com>
 */

namespace MauticPlugin\MauticWhatsappBundle\Transport;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Mautic\LeadBundle\Entity\Lead;
use Mautic\PluginBundle\Helper\IntegrationHelper;
use Mautic\SmsBundle\Api\AbstractSmsApi;
use Monolog\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class WhatsappTransport extends AbstractSmsApi
{
    protected $logger;
    protected $integrationHelper;
    protected $client;
    private $apiKey;
    private $apiUrl;
    protected $connected;

    public function __construct(IntegrationHelper $integrationHelper, Logger $logger, Client $client)
    {
        $this->integrationHelper = $integrationHelper;
        $this->logger = $logger;
        $this->client = $client;
        $this->connected = false;
    }

    public function sendSms(Lead $contact, $content)
    {
        $number = $contact->getLeadPhoneNumber();
        if (empty($number)) {
            $this->logger->error("Empty phone number for contact", ['contact_id' => $contact->getId()]);
            return false;
        }
    
        try {
            if (!$this->connected && !$this->configureConnection()) {
                throw new \Exception("WhatsApp is not configured properly.");
            }
    
            $number = $this->sanitizeNumber($number);
            $content = $this->sanitizeContent($content, $contact);
            if (empty($content)) {
                throw new \Exception('Message content is empty.');
            }
    
            $response = $this->send($number, $content);
            $this->logger->info("WhatsApp message sent successfully.", ['response' => json_encode($response)]);
            return true;
        } catch (\Exception $e) {
            $this->logger->error("WhatsApp message request failed.", [
                'exception' => $e->getMessage(),
                'contact_id' => $contact->getId(),
                'number' => $number
            ]);
            return $e->getMessage();
        }
    }

    protected function send($number, $content, $retryCount = 0)
    {
        $data = [
            'phone_no' => $number,
            'key' => $this->apiKey,
            'message' => $content,
            'skip_link' => true
        ];
    
        $this->logger->info("Attempting to send WhatsApp message", ['number' => $number, 'retryCount' => $retryCount]);
    
        try {
            $response = $this->client->post($this->apiUrl, [
                'json' => $data,
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => 30,
                'connect_timeout' => 30
            ]);
    
            $responseBody = $response->getBody()->getContents();
            $decodedResponse = json_decode($responseBody, true);
    
            if (isset($decodedResponse['error']) && strpos($decodedResponse['error'], 'Session closed') !== false) {
                if ($retryCount < 3) {
                    $this->logger->warning("WhatsApp session closed. Retrying...", ['retryCount' => $retryCount + 1]);
                    sleep(pow(2, $retryCount)); // Exponential backoff
                    return $this->send($number, $content, $retryCount + 1);
                } else {
                    throw new \Exception("WhatsApp session repeatedly closed after multiple retries.");
                }
            }
    
            $this->logger->info("WhatsApp API response", ['response' => $responseBody]);
            return $decodedResponse;
        } catch (GuzzleException $e) {
            $this->logger->error("WhatsApp API request failed", [
                'exception' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
                'request' => [
                    'method' => 'POST',
                    'url' => $this->apiUrl,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($data)
                ]
            ]);
            throw new \Exception("Failed to send WhatsApp message: " . $e->getMessage());
        }
    }

    protected function sanitizeNumber($number)
    {
        $util = PhoneNumberUtil::getInstance();
        try {
            $parsed = $util->parse($number, 'IN');
            return $util->format($parsed, PhoneNumberFormat::E164);
        } catch (NumberParseException $e) {
            $this->logger->addWarning('Invalid number format, using as is.', ['number' => $number, 'exception' => $e->getMessage()]);
            return preg_replace('/^\++/', '+', $number); // Remove multiple leading + signs
        }
    }




    protected function configureConnection()
    {
        $this->logger->info('Attempting to configure WhatsApp connection');
        
        $integration = $this->integrationHelper->getIntegrationObject('Whatsapp');
        if (!$integration) {
            $this->logger->error('WhatsApp integration object not found');
            return false;
        }
        
        if (!$integration->getIntegrationSettings()->getIsPublished()) {
            $this->logger->error('WhatsApp integration is not published');
            return false;
        }
        
        $keys = $integration->getDecryptedApiKeys();
        if (empty($keys['apiKey']) || empty($keys['apiUrl'])) {
            $this->logger->error('WhatsApp API key or URL is missing');
            return false;
        }
        
        $this->apiKey = $keys['apiKey'];
        $this->apiUrl = $keys['apiUrl'];
        $this->connected = true;
        
        $this->logger->info('WhatsApp connection configured successfully');
        return $this->connected;
    }

    protected function sanitizeContent(string $content, Lead $contact) {
        return strtr($content, [
            '{contact_title}' => $contact->getTitle(),
            '{contact_firstname}' => $contact->getFirstname(),
            '{contact_lastname}' => $contact->getLastname(),
            '{contact_name}' => $contact->getName(),
            '{contact_company}' => $contact->getCompany(),
            '{contact_email}' => $contact->getEmail(),
            '{contact_address1}' => $contact->getAddress1(),
            '{contact_address2}' => $contact->getAddress2(),
            '{contact_city}' => $contact->getCity(),
            '{contact_state}' => $contact->getState(),
            '{contact_country}' => $contact->getCountry(),
            '{contact_zipcode}' => $contact->getZipcode(),
            '{contact_location}' => $contact->getLocation(),
            '{contact_phone}' => $contact->getLeadPhoneNumber(),
        ]);
    }
}
