<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
define("JIKI_USERAGENT","JIKI via MediaWiki");
define("JIKI_REST_ENDPOINT","/rest/api/2/search");
define("JIKI_REST_EXPAND","names,renderedFields");
define("JIKI_REST_FIELDS","summary,description,issuetype,fixVersions,status");
define("JIKI_REST_MAXRESULTS","250");
# follow location
# ssl verify
/**
 * interract with JIRA REST API
 */
class Rest
{
  /**
   * get the data from JIRA
   *
   * @param array data a container to hold the response
   * @param string host the host name of the JIRA API
   * @param string username the JIRA API username
   * @param string password the JIRA API password
   * @param string jql the jql statement
   */
  public static function getJIRAData(&$data,$host,$username,$password)
  {
    $data["host"] = "{$host}";
    $data["endpoint"] = "{$host}".
      JIKI_REST_ENDPOINT.
      "?jql=".urlencode($data["jql"]).
      "&expand=".JIKI_REST_EXPAND.
      "&fields=".JIKI_REST_FIELDS.
      "&maxResults=".JIKI_REST_MAXRESULTS;
    $response = self::callJIRARest($data["endpoint"],$username,$password);
    if($response===false)#Request failed for some reason
    {
      $data["success"] = false;
      unset($data["total"],$data["data"]);
      return false;
    }
    if($response["total"]<=0)#no results
    {
      $data["success"] = true;
      $data["total"] = 0;
      unset($data["data"]);
      return true;
    }
    $data["data"] = self::cleanData($response["issues"]);
    $data["total"] = sizeof($data["data"]);
    return true;
  }
  /**
   * call the JIRA REST API
   *
   * @param string url the URL to call
   * @param string username the username use when calling url
   * @param string password the password to use when calling url
   * @param array successCodes the HTTP codes that are considered successful
   */
  private static function callJIRARest($url,$username,$password,$successCodes=array(200))
  {
    global $jikiCurlOpts;
    $curl = curl_init("{$url}");
    $baseOpts = array
      (
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => JIKI_USERAGENT,
        CURLOPT_HTTPHEADER => array
        (
          "Accept: application/json",
          "Content-Type: application/json",
          "Authorization: Basic ".base64_encode("{$username}:{$password}"),
        ),
      );
    if(isset($jikiCurlOpts))#merge base opts with optional opts
    {
      foreach($jikiCurlOpts as $optKey => $curlOpt)
      {
        if(isset($baseOpts["{$optKey}"]))
        {
          continue;
        }
        $baseOpts["{$optKey}"] = $curlOpt;
      }
    }
    curl_setopt_array(
      $curl,
      $baseOpts
    );
    $response = curl_exec($curl);
    if(!in_array(curl_getinfo($curl,CURLINFO_HTTP_CODE),$successCodes))
    {
      curl_close($curl);
      unset($curl);
      return false;
    }
    if($response = json_decode($response,true))#Valid JSON
    {
      curl_close($curl);
      unset($curl);
      return $response;
    }
    curl_close($curl);
    unset($curl);
    return false;
  }
  /**
   * clean the data from REST API into some consistent structure
   *
   * @param array rawData and array of raw data returned from the endpoint
   */
  private static function cleanData($rawData)
  {
    #TODO: Implement this if SOAP or some other endpoint is to be used
    return $rawData;
  }
}
