<?php

namespace Bolt\Extension\CND\Brightcove\Controller;

use Bolt\Application;
use Bolt\Content;
use Exception;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Filesystem\Filesystem;

class BrightcoveController implements ControllerProviderInterface
{
    private $app;
    private $config;

    const IMAGE_FOLDER = "brightcove";

    public function __construct (Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->app['twig.loader.filesystem']->prependPath(__DIR__."/../twig");
    }

    public function connect(\Silex\Application $app)
    {
        $ctr = $app['controllers_factory'];
        $ctr->get('/search', array($this, 'search'));
        $ctr->post('/importImage', array($this, 'importImage'));

        return $ctr;
    }

    /**
     * List content as specified
     *
     * request body has to contain a JSON string in this format:
     * {content: [ <type>/<id>, <type>/<id>, <type>/<id>, ... ] }
     *
     * @param string $searchterm
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $searchterm = $request->get("term", false);

        if(!$this->app["users"]->isValidSession())
            return new JsonResponse("Insufficient access rights!");

        // Build from configured search filters and date range
        $q = array( "%2B".urlencode($searchterm));
        foreach($this->config["search-filter"] as $key => $value)
            $q[] = "%2B".$key.":".urlencode($value);

        $action = "videos?".($q ? "q=".implode("+",$q)."&" : "")."limit=25";
        $result = $this->sendAPIRequest($action);

        $videos = array();
        foreach($result as $video){
            $videos[] = array_intersect_key( $video, array_flip( array(
                "created_at",
                "cue_points",
                "custom_fields",
                "description",
                "duration",
                "id",
                "images",
                "long_description",
                "name",
                "tags",
                "updated_at")));
        }

        return new JsonResponse($videos);
    }

    // ------------------------------------------------------------------------

    /**
     * Get a Brightcove token from credentials if non requested yet or expired
     *
     * @return string
     * @throws Exception
     */
    protected function getAPIToken()
    {
        $session = $this->app["session"];
        $token = $session->get("brightcove.token", null);
        $token_expire = $session->get("brightcove.token_expire", 0);

        if($token && time() < $token_expire)
            return $token;

        // Request new token
        $headers = array("Content-Type: application/x-www-form-urlencoded");
        $auth_string = $this->config["client"].":".$this->config["secret"];

        $ssl_validation = isset($this->config["ssl-validation"]) ? (bool)$this->config["ssl-validation"] : true;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,            $this->config["authapi"]);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_validation ? 2 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_validation ? 1 : 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($ch, CURLOPT_USERPWD,        $auth_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $start = microtime(true);
        $output = curl_exec($ch);
        $json = json_decode($output, true);

        if(curl_errno($ch))
            throw new Exception("Brightcove OAuth call failed: Request failed: ".curl_error($ch));
        if(!$json)
            throw new Exception("Brightcove OAuth call failed: Response invalid");
        if(!isset($json["access_token"]) || !isset($json["expires_in"]))
            throw new Exception("Brightcove OAuth call failed: No token received");

        curl_close($ch);

        $this->app['logger.system']->debug("Brightcove API - token requestes - ".round(microtime(true)-$start, 1)."s");

        $session->set("brightcove.token", $json["access_token"]);
        $session->set("brightcove.token_expire", time() + $json["expires_in"] - 10); // time seems to be off sometimes, so we expire 10s earlier to be sure

        return $json["access_token"];
    }

    protected function sendAPIRequest($action, $body = "", $method = "GET", $api = "cmsapi"){
        $token = $this->getAPIToken();

        $headers = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Authorization: Bearer ".$token
        );

        if(!isset($this->config[$api]))
            throw new Exception("Unknown API '".$api."' specified");

        $url  = str_replace("{account}", $this->config["account"], $this->config[$api]).$action;

        $ssl_validation = isset($this->config["ssl-validation"]) ? (bool)$this->config["ssl-validation"] : true;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_validation ? 2 : 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_validation ? 1 : 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST,           $body ? 1 : 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $body);

        $start = microtime(true);
        $output = curl_exec($ch);

        if(curl_errno($ch) || !$output)
            throw new Exception("Brightcove API call failed: (".curl_error($ch).")");

        curl_close($ch);
        $this->app['logger.system']->debug("Brightcove API - request to ".$api." endpoint wth action ".$action." - ".round(microtime(true)-$start, 1)."s");

        $result = json_decode($output, true);
        if($result === false)
            throw new Exception("Brighcove API call failed: Response was invalid.".(true ? "<pre>".$output."</pre>" : ""));

        if(isset($result[0]["error_code"]) && $result[0]["error_code"] == "NOT_FOUND")
            return array(); // return null because twig can't properly redirect the "NOT FOUND" exception to the 404 page

        if(isset($result[0]["error_code"]))
            throw new Exception("Brightcove API call returned Error: ".$result[0]["error_code"]);

        return $result;

    }

    public function importImage(Request $request){

        if(!$this->app["users"]->isValidSession())
            return new JsonResponse("Insufficient access rights!");

        $targetFolder = $this->app["resources"]->getPath("filespath")."/".self::IMAGE_FOLDER;

        //Prepare image folder if necessary
        if(!is_dir($targetFolder)){
            mkdir($targetFolder, 0777, true);
        }

        $url = $request->get("url");
        $bcId = $request->get("bcid");

        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

        $data = curl_exec($ch);

        if(curl_errno($ch)){
            $this->app['logger.system']->info("Curl error ".curl_errno($ch)." while downloading image for url: ".$url);
            return null;
        }

        $info = curl_getinfo($ch);
        if($info["http_code"] != 200) {
            $this->app['logger.system']->info("Http error ".$info["http_code"]." while downloading image for url: ".$url);
            return null;
        }

        curl_close ($ch);

        $filename = "brightcove-".$bcId.".jpg";

        if(file_exists($targetFolder."/".$filename)){
            unlink($targetFolder."/".$filename);
        }
        $fp = fopen($targetFolder."/".$filename,'x');
        fwrite($fp, $data);
        fclose($fp);

        return new JsonResponse( array("path" => self::IMAGE_FOLDER."/".$filename));
    }


}