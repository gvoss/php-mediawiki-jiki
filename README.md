**JIKI** - Embed a list of issues from a JIRA instance into a Mediawiki wiki page.

![JIKI Sample Image](https://upload.wikimedia.org/wikipedia/commons/2/21/Php-mediawiki-jiki-screenshot.png)

Configuration
=============
In mediawiki **LocalSettings.php** specify the JIRA Hostname:
```php
$jikiJiraHost = "https://jira.atlassian.com";
```
Specify the JIRA User Name:
```php
$jikiJiraUser = "user@example.com";
```
Specify the JIRA User Password:
```php
$jikiJiraPassword = "ILoveC@ts";
```
Specify any custom fields you have in your JIRA:
```php
$jikiExtraParams = array
  (
    array("param"=>"myParamNameInWiki","jql"=>"\"My Field Name in JIRA\""),
  );
```
Basic Use
=========
In any wiki page include the following tag:
```
<jira></jira>
```
Filtering
=========
As long as the field is a basic JIRA field or you have configured it in LocalSettings:
```
<jira project="GO,FP,HELP"></jira>
<jira project="SUPPORT" status="In Progress,!Closed,!Resolved"></jira>
```
Search for text in the issue's text fields:
```
<jira>text to search for</jira>
```
Search multiple fields:
```
<jira project="GO" reporter="gvoss"></jira>
```
Allow subtasks to be returned when not using JQL:
```
<jira key="ABC-123" jikiAllowSubtasks="true"></jira>
```
Or for an advanced user or where the JIKI implementation of JQL is insufficient:
```
<jira jql="project = GO AND reporter in (gvoss)"></jira>
```
If you wish to use greater than (`>`) or less than (`<`) you must escape these:
```
<jira jql="project = CAT AND createdDate &gt;= 1970-01-01"></jira>
```
Ordering
========
Ordering can be done by using the `orderby` parameter:
```
<jira orderby="updateddate ASC"></jira>
```
or by including it in your JQL:
```
<jira jql="ORDER BY updateddate ASC"></jira>
```
Default Filter Fields
=====================
1. id - synonymous with key
1. key - the Key of the issue (e.g. ABCD-1234)
1. issuetype - the type of issue (e.g. bug)
1. project - the key of the project (e.g. ABCD)
1. fixversion - the fix version (e.g. 1.0.0)
1. affectedversion - the affected version (e.g. 1.0.0)
1. reporter - username of the reporter (e.g. gvoss)
1. assignee - username of the assignee (e.g. gvoss)
1. status - name of the status (e.g. In Progress)
1. sprint - ID or exact name of the sprint (e.g. 786)
1. resolution - status of the resolution (e.g. resolved)
1. labels - any labels applied to the issues (e.g. cats)
1. component - any components applied to the issues (e.g. content management)
1. epiclink - name of the epic link (e.g. loading screen)

Formatting
==========
The following parameters exist to allow controlling of format:

1. **jikiformat** - allows you to control the formating (default: html)
 
```
<jira jikiformat="simple"></jira>
<jira jikiformat="html"></jira>
<jira jikiformat="html.table"></jira>
<jira jikiformat="html.bullets"></jira>
<jira jikiformat="html.numbered"></jira>
```
1. **jikifulldetails** - allows you to control how much detail is printed to the screen
```
<jira jikifulldetails="true"></jira>
```
Advanced Configuration
======================
In **LocalSettings.php** you can specify additional Curl Opts to apply:
```php
$jikiCurlOpts = array(CURLOPT_SSL_VERIFYPEER => false);
```
You can also configure some defaults for the renderers:
```php
$jikiRenderDefaults = array();
$jikiRenderDefaults["html"] = array("target" => "_self");
```

See Also
========
[Mediawiki page for JIKI](https://www.mediawiki.org/wiki/Extension:JIKI)
