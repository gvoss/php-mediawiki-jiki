<?php
if(!defined("MEDIAWIKI"))
{
   echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
   die(1);
}
$wgExtensionCredits["parserhook"][] = array
(
  "path" => __FILE__,
  "name" => "JIKI",
  "namemsg" => "JIKI",
  "description" => "JIKI - JIRA in your WIKI",
  "descriptionmsg" => "JIKI - JIRA in your WIKI",
  "version" => "1.2",
  "author" => "gvoss",
  "url" => "https://github.com/gvoss/php-mediawiki-jiki",
  "license-name" => "https://github.com/gvoss/php-mediawiki-jiki/blob/master/LICENSE",
);
#jikiSupportedParams - base parameters supported by Jiki
$jikiSupportedParams = array
(
  "id",
  "key",
  "issuetype",
  "project",
  "fixversion",
  "affectedversion",
  "reporter",
  "assignee",
  "priority",
  "type",
  "status",
  "sprint",
  "resolution",
  "labels",
  "component",
  "description",
  "createddate",
  "updateddate",
  array("param"=>"epiclink","jql"=>"\"Epic Link\""),
);
if(isset($jikiExtraParams))
{
  $jikiSupportedParams = array_merge($jikiSupportedParams,$jikiExtraParams);#Allows for extending the base params
}
#jikiDataContainer - common container for response from JIRA
$jikiDataContainer = array
  (
    "jql" => "",
    "host" => "",
    "endpoint" => "",
    "success" => false,
    "total" => 0,
    "data" => array(),
  );

$wgHooks["ParserFirstCallInit"][] = "jikiSetHook";

#Import API Handlers
$wgAutoloadClasses['Rest'] = __DIR__ . '/api/Rest.php';#Calls JIRA REST API

#Import View Handlers
$wgAutoloadClasses['Simple'] = __DIR__ . '/view/Simple.php';#Allows to display in plain text
$wgAutoloadClasses['Hypertext'] = __DIR__ . '/view/Hypertext.php';#Allows to display in HTML

#Import Utilities
$wgAutoloadClasses['JQL'] = __DIR__ . '/util/JQL.php';#JQL Helper functions
$wgAutoloadClasses['JIRA'] = __DIR__ . '/util/JIRA.php';#JIRA Helper functions

function jikiSetHook($parser)
{
  $parser->setHook("jira","jikiRender");
  return true;
}

function jikiRender($input,$args,$parser)
{
  global $jikiJiraHost,$jikiJiraUser,$jikiJiraPassword,$jikiSupportedParams,$jikiCurlOpts,$jikiDataContainer;#Configuration from the LocalSettings
  $jikiJQL = "";#jql to send to JIRA
  $jikiOutput = "";#output to the wiki page
  $jikiFormat = "html";#How to format JIKI output
  $jikiFullDetails = false;#Whether or not to show full ticket details
  $jikiLinkToJira = true;#Whether to include a link to JIRA
  if(!isset($jikiJiraHost)||!isset($jikiJiraUser)||!isset($jikiJiraPassword))#No Configuration
  {
    $jikiOutput = "No JIRA Configuration...";
    return $jikiOutput;
  }
  if(!is_array($jikiCurlOpts))#unexpected instantiation
  {
    unset($jikiCurlOpts);
  }
  if(!filter_var($jikiJiraHost,FILTER_VALIDATE_URL))#Bad URL
  {
    $jikiOutput = "Bad JIRA Host URL...";
    return $jikiOutput;
  }
  if(strtolower(parse_url($jikiJiraHost,PHP_URL_SCHEME))!=="https")
  {

    $jikiOutput = "Consider using JIRA on HTTPS...";
    return $jikiOutput;
  }
  if(isset($args["jql"]))#allow override of JQL
  {
    $jikiDataContainer["jql"] = "{$args["jql"]}";
  }
  else
  {
    $jikiDataContainer["jql"] = JQL::getJQL($input,$args,$jikiSupportedParams);
  }
  #cleanup JQL to allow use of variables
  # https://github.com/idimitrakopoulos/php-mediawiki-jiki/commit/dcf7c6b8824387a7c1a3a9dd6ef048ba84476988
  $jikiDataContainer["jql"] = $parser->replaceVariables($jikiDataContainer["jql"]);
  if(isset($args["jikiformat"]))#user specified a format
  {
    $jikiFormat = strtolower($args["jikiformat"]);
  }
  if(isset($args["jikifulldetails"])&&$args["jikifulldetails"]=="true")#user specified full ticket details
  {
    $jikiFullDetails = true;
  }
  if(isset($args["id"])||isset($args["key"]))#user specified a single issue
  {
    $jikiLinkToJira = false;
  }
  #set up the arguments for the render methods
  $jikiRenderArgs = array
    (
      "renderLink" => $jikiLinkToJira,
      "renderDetails" => $jikiFullDetails,
    );
  if(Rest::getJIRAData($jikiDataContainer,$jikiJiraHost,$jikiJiraUser,$jikiJiraPassword,$jikiJQL))#Some data is returned
  {
    switch(strtolower($jikiFormat))
    {
      case "html":
      case "html.table":
      case "html.bullets":
      {
        $jikiOutput = Hypertext::getRenderedView($jikiFormat,$jikiDataContainer,$jikiRenderArgs);
        break;
      }
      default:
      {
        $jikiOutput = Simple::getRenderedView($jikiFormat,$jikiDataContainer,$jikiRenderArgs);
        break;
      }
    }
  }
  else#Failure to get data
  {
    $jikiOutput = "Error while calling JIRA...";
  }
  return $jikiOutput;
}
