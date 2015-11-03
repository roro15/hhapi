<?php

class HHApiSoapClient {

    private $cookies = null;

    const USER_LOGIN = 'corp\sap_tmg';
    const USER_PASSWORD = '1qaz@WSX';

    public function __construct($fid) {
        $this->fid = (int) $fid;
    }

    public function getCookie() {
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_AUTOREFERER, true);
            curl_setopt($curl, CURLOPT_HEADER, 1);
            curl_setopt($curl, CURLOPT_URL, 'https://sdo-portal-t.rushydro.ru:8443/CookieAuth.dll?Logon');
            curl_setopt($curl, CURLOPT_POST, true);
            $data = array(
                'curl' => 'Z2FsapZ2FbcZ2FsrtZ2FwsdlZ2Fflv_10002A111AD1Z2Fsrvc_urlZ2FsapZ2FbcZ2FsrtZ2FxipZ2FsapZ2FzzfeedbackZ2F300Z2FzzfeedbackZ2FzfeedbackZ3Fsap-clientZ3D300',
                'flags' => 0,
                'forcedownlevel' => 0,
                'formdir' => 3,
                'trusted' => 0,
                'username' => 'corp\sap_tmg',
                'password' => '1qaz@WSX',
                'SubmitCreds' => 'Log On',
            );
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, "corp\sap_tmg:1qaz@WSX");
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            $out = curl_exec($curl);
            $cookies = array();
            $c = array();
            preg_match_all('/Set-Cookie:(?<cookie>\s{0,}.*)$/im', $out, $cookies);
            foreach ($cookies['cookie'] as $cookie) {
                $part = explode('=', trim($cookie));
                $c[$part[0]] = $part[1];
            }
            curl_close($curl);
            $this->cookies = true;
        }
    }

    public function sendFeedback() {
        if (!$this->cookies) {
            $this->getCookie();
        }

        $options = array(
            //'location' => 'http://hydroschool.ru/webservice/index.php',
            //'uri'      => 'http://hydroschool.ru/webservice/index.php',
            'cache_wsdl' => WSDL_CACHE_NONE,
            'login' => 'corp\sap_tmg',
            'password' => '1qaz@WSX',
                //'encoding'    => 'UTF-8',
        );

        try {
            $client = new Zend\Soap\Client();
            $client->setOptions($options);
            $client->setWSDL('https://sdo-portal-t.rushydro.ru:8443/sap/bc/srt/wsdl/flv_10002A101AD1/bndg_url/sap/bc/srt/xip/sap/zzfeedback/300/zzfeedback/zfeedback?sap-client=300');
            //$client->setWSDL('http://hydroschool.ru/webservice/wsdl.php?v=2');
            $feedback = new Feedback(getElements('Feedback', FEEDBACK_IBLOCK_ID, $this->fid));
            $result = $client->getFeedback(array('feedback' => $feedback));
            //var_dump ($result);
        } catch (SoapFault $s) {
            die('ERROR: [' . $s->faultcode . '] ' . $s->faultstring);
        } catch (Exception $e) {
            die('ERROR: ' . $e->getMessage());
        } catch (Exception $e) {
            var_dump($e);
        }
    }

}
