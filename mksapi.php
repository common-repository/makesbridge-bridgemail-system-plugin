<?php

class mksapi {

    var $url = 'https://api.bridgemailsystem.com/pms/services/'; //MakesBridge API URL
    var $apiKey; //MakesBridge API Access Token
    var $userId; //MakesBridge API Username
    var $authToken; //Authentication Token

    function mksapi($userId, $apiKey) {
        $this->apiKey = $apiKey;
        $this->userId = $userId;
    }

    /*
     * Login to MakesBridge
     *
     * @param string $apiKey MakesBridge API Access Token
     * @param string $userId MakesBridge UserId
     *
     */

    function login() {
        $content = "<?xml version='1.0' ?>
		 <login>
		 <userId>$this->userId</userId>
		 <api_tk>$this->apiKey</api_tk>
		 </login>";
        $headers = array(
            'Content-type' => 'text/xml'
        );
        $result = wp_remote_post($this->url . 'login/', array(
            'headers' => $headers,
            'sslverify' => false,
            'body' => $content,
                ));
        $authTk = wp_remote_retrieve_header($result, 'auth_tk');
        $this->authToken = $authTk;
        return($authTk);
    }

    //Returns true if settings are correct or false if incorrect
    function testSettings() {
        $response = $this->login();
        return(($response) ? true : false);
    }

    function retrieveLists() {
        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );
        $response = wp_remote_post($this->url . 'getlistinfo/', array(
            'headers' => $headers,
            'sslverify' => false
                ));
        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return($data);
    }

    //Create Subscriber Function
    function createSubscriber($data, $list) {
        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );
        $xmlstr = "<?xml version='1.0' encoding='utf-8'?>
	    <addsubscriber></addsubscriber>";
        $xml = new SimpleXMLElement($xmlstr);
        $xml->addAttribute('listName', $list);
        $subscriber = $xml->addChild('subscriber');

        if (isset($data['standard'])) {
            //StandardFields
            foreach ($data['standard'] as $key => $value) {
                $subscriber->$key = $data['standard'][$key];
            };
        };

        //CustomFields
        $customField = $subscriber->addChild('customFields');

        if (isset($data['custom'])) {
            foreach ($data['custom'] as $customKey => $customValue) {
                $customFields = $customField->addChild('customField');
                $customFields->addAttribute('name', $customKey);
                $customFields->addAttribute('value', $customValue);
            }
        }

        $response = wp_remote_post($this->url . 'addsubscriber/', array(
            'headers' => $headers,
            'sslverify' => false,
            'body' => $xml->asXML()
                ));

        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);

        return $response;
    }

    //Custom Fields Function
    function retrieveCustomFields() {
        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );
        $response = wp_remote_post($this->url . 'getcustinfo/', array(
            'headers' => $headers,
            'sslverify' => false
                ));
        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return($data);
    }

    function retrieveWorkflowList() {
        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );
        $response = wp_remote_post($this->url . 'getworkflowlist/', array(
            'headers' => $headers,
            'sslverify' => false
                ));
        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return($data);
    }

    function addToWorkflow($workflowId, $subscriberId, $stepId) {

        $xml = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                <addtoworkflow>
                  <workflowId>" . $workflowId . "</workflowId>    
                  <subscriberId>" . $subscriberId . "</subscriberId>
                  <stepOrder>" . $stepId . "</stepOrder>
                </addtoworkflow>";


        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );
        $response = wp_remote_post($this->url . 'addtoworkflow/', array(
            'headers' => $headers,
            'sslverify' => false,
            'body' => $xml
                ));

        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return($data);
    }

    function getCampaignInfo($page) {
        $xml = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                <getcampaign>
                   <page>" . $page . "</page>
                </getcampaign>";

        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );

        $response = wp_remote_post($this->url . 'getcampaigninfo/', array(
            'headers' => $headers,
            'sslverify' => false,
            'body' => $xml
                ));
        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return($data);
    }

    function getCampaignDetail($campaignId) {
        $xml = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                <campaign>
                    <id>" . $campaignId . "</id>
                </campaign>";

        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );
        $response = wp_remote_post($this->url . 'getcampaigndetail/', array(
            'headers' => $headers,
            'sslverify' => false,
            'body' => $xml
                ));

        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return($data);
    }

    function getCampaignStatistics($campaignId) {
        $xml = "<?xml version='1.0' encoding='ISO-8859-1' ?>
                <campaign>
                    <id>" . $campaignId . "</id>
                </campaign>";

        $headers = array(
            'Content-type' => 'text/xml',
            'userId' => $this->userId,
            'auth_tk' => $this->authToken
        );
        $response = wp_remote_post($this->url . 'getcampaignstat/', array(
            'headers' => $headers,
            'sslverify' => false,
            'body' => $xml
                ));

        $data = wp_remote_retrieve_body($response);
        $data = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        return($data);
    }

}

//End MakesBridge API
