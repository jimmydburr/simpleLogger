<?php
ini_set('display_errors', TRUE);
require_once ('../library/db.php');
#
# Define constants
define('DRIVER_TYPE_CODE_COMPANY_DRIVER', 300611);
define('DRIVER_TYPE_CODE_OWNER_OPERATOR', 68);
define('DRIVER_TYPE_CODE_STUDENT', 300612);
define('EOL', "\n");
#
# Set output format
header('Content-type: text/plain');
#
# Init vars
$appId = (empty($_GET['appId'])) ? '' : $_GET['appId'];
$key = (empty($_GET['key'])) ? '' : $_GET['key'];
$testing = (empty($_GET['testing'])) ? '' : $_GET['testing'];
#
# Error check
if (empty($key)) {
    die("API key was not sent with the request. Please check your request syntax and try again." . EOL);
}
#
# More init
if (empty($testing)) {
    if (empty($appId)) {
        $updateLastAppId = TRUE; // Client is not testing and didn't send specific app id
    } else {
        $updateLastAppId = FALSE; // Client is not teseting and did send specific app id
    }
} else {
    $updateLastAppId = FALSE; // Client is testing
}
#
# Open db connection
$db = mysql_connect(DB_HOST, DB_USER, DB_PASS) or die("Could not connect to DB host " . mysql_error() . EOL);
mysql_select_db(DB_NAME) or die("Could not select db " . mysql_error() . EOL);
#
# Escape vars
$appId = mysql_real_escape_string($appId);
$key = mysql_real_escape_string($key);
#
# Grab customer record
$result = mysql_query("SELECT * FROM `customer` WHERE `api_key` = '{$key}'");
#
# Error check
if (!$result) {
    die("There was a database connection error. Please try again." . EOL);
} else if (mysql_num_rows($result) == 0) {
    die("Customer record not found. Please check your api key and try again." . EOL);
} else if (mysql_num_rows($result) > 1) {
    die("More than one customer record found. Please alert the system administrator." . EOL);
}
#
# Convert customer record to array
$customer = mysql_fetch_assoc($result);
#
# Branch on app id
if ( empty($appId) ) {
    # Find parsed and unexported apps for this customer
    $query = "SELECT `app`.*, `site`.`url` AS site, `person`.`email` AS recruiter_email FROM `app`, `person`, `site` WHERE `app`.`id_site` = `site`.`id` AND `app`.`id_recruiter` = `person`.`id` AND `app`.`id_customer` = {$customer['id']} AND `app`.`id` > {$customer['api_last_app_id']} AND `app`.`parsed_at` IS NOT NULL ORDER BY `app`.`id` LIMIT 500";
} else {
    # Find this app for this customer
    $query = "SELECT `app`.*, `site`.`url` AS site, `person`.`email` AS recruiter_email FROM `app`, `person`, `site` WHERE `app`.`id_site` = `site`.`id` AND `app`.`id_recruiter` = `person`.`id` AND `app`.`id_customer` = {$customer['id']} AND `app`.`id` = {$appId} AND `app`.`parsed_at` IS NOT NULL ORDER BY `app`.`id` LIMIT 1";
}
$apps = mysql_query($query) or die("Could not find apps " . mysql_error() . EOL);
#
# Open the xml
echo getXmlHeader();
#
# Process found apps
while ($app = mysql_fetch_assoc($apps)) {
    echo getAppAsXml($app);
    $lastId = $app['id'];
}
#
# Close the xml
echo getXmlFooter();
#
# Update the customer record
if ($updateLastAppId and isset($lastId)) {
    $result = mysql_query("UPDATE customer SET api_last_app_id = {$lastId} WHERE id = {$customer['id']} LIMIT 1");
}
#
##################################
#           FUNCTIONS
#---------------------------------
#
function getAppAsXml($app) {
    #
    # xml encode the app
    foreach($app as $key => $value) {
        $app[$key] = trim(htmlspecialchars(str_replace('&amp;', '&', $app[$key])));
    }
    #
    # Convert some values to ieg format
    $app['cdl_expire_date'] = (empty($app['cdl_expire_date'])) ? '' : date('Ymd', strtotime($app['cdl_expire_date']));
    $app['date_available'] = (empty($app['date_available'])) ? '' : date('Ymd', strtotime($app['date_available']));
    $app['dob'] = (empty($app['dob'])) ? '' : date('Ymd', strtotime($app['dob']));
    $app['emp0_from_date'] = (empty($app['emp0_from_date'])) ? '' : date('Ymd', strtotime($app['emp0_from_date']));
    $app['emp1_from_date'] = (empty($app['emp1_from_date'])) ? '' : date('Ymd', strtotime($app['emp1_from_date']));
    $app['emp2_from_date'] = (empty($app['emp2_from_date'])) ? '' : date('Ymd', strtotime($app['emp2_from_date']));
    $app['emp3_from_date'] = (empty($app['emp3_from_date'])) ? '' : date('Ymd', strtotime($app['emp3_from_date']));
    $app['emp0_to_date'] = (empty($app['emp0_to_date'])) ? '' : date('Ymd', strtotime($app['emp0_to_date']));
    $app['emp1_to_date'] = (empty($app['emp1_to_date'])) ? '' : date('Ymd', strtotime($app['emp1_to_date']));
    $app['emp2_to_date'] = (empty($app['emp2_to_date'])) ? '' : date('Ymd', strtotime($app['emp2_to_date']));
    $app['emp3_to_date'] = (empty($app['emp3_to_date'])) ? '' : date('Ymd', strtotime($app['emp3_to_date']));
    $app['rckdrv'] = (empty($app['tickets_reckless'])) ? 'N' : 'Y';
    $app['ssn'] = str_replace(array('-', ' '), '', $app['ssn']);
    #
    # Break out endorsements
    $app['dbltpl'] = (stripos($app['cdl_endorsements'], 'double/triple') === FALSE) ? '' : 'Y';
    $app['hazmat'] = (stripos($app['cdl_endorsements'], 'haz') === FALSE) ? '' : 'Y';
    $app['passng'] = (stripos($app['cdl_endorsements'], 'passenger') === FALSE) ? '' : 'Y';
    $app['tank'] = (stripos($app['cdl_endorsements'], 'tanker') === FALSE) ? '' : 'Y';
    #
    # Set driver type
    if ($app['owner_operator'] == 'Yes') {
        $app['driver_type'] = DRIVER_TYPE_CODE_OWNER_OPERATOR;
    } else if ($app['company_driver'] == 'Yes') {
        $app['driver_type'] = DRIVER_TYPE_CODE_COMPANY_DRIVER;
    } else if ($app['student'] == 'Yes') {
        $app['driver_type'] = DRIVER_TYPE_CODE_STUDENT;
    } else {
        $app['driver_type'] = '';
    }
    #
    # Handle the employment section
    $empRows = '';
    $empFormat = '<RowEmployers PREMNAME="%s" CONTCT="%s" JOBPRF="%s" ADDR1="%s" CITY="%s" ST="%s" ZIP="%s" PHONE="%s" BEGDTE="%s" ENDDTE="%s" />' . "\n            ";
    for ($i = 0;$i < 4;$i++) {
        if (!empty($app['emp' . $i . '_employer'])) {
            #
            # Insert the values into the format string
            $empRows.= sprintf($empFormat, $app['emp' . $i . '_employer'], $app['emp' . $i . '_supervisor'], $app['emp' . $i . '_position_held'], $app['emp' . $i . '_address'], $app['emp' . $i . '_city'], $app['emp' . $i . '_state'], $app['emp' . $i . '_zip'], $app['emp' . $i . '_phone'], $app['emp' . $i . '_from_date'], $app['emp' . $i . '_to_date']);
        }
    }
    $empRows = trim($empRows);
    #
    # Handle the app itself
    $driverFormat = <<<XML
    <ROW AVLDTE="%s" RECTYP="%s" EMAILADDR="%s" ADRID="%s" RECRID="%s" DRRANK="%s" DRVFN="%s" MIDDLENAME="%s" DRVLN="%s" SSN="%s" LICSUS="%0.1s" DUI="%0.1s" FELONY="%0.1s" RCKDRV="%0.1s" TMMEMB="%0.1s" DOB="%s" ADDR1="%s" ADDR2="%s" CITY="%s" ST="%s" ZIP="%s" PHONE="%s" DRPHON2="%s">
        <CDLs>
            <RowCDLs CDLNUM="%s" CDLST="%s" EXPDT="%s" DBLTPL="%s" PASSNG="%s" TANK="%s" HAZMAT="%s" LCLASS="%s"  />
        </CDLs>
        <Employers>
            $empRows
        </Employers>
    </ROW>

XML;
    #
    # Insert the values into the format string
    $result = sprintf($driverFormat, $app['date_available'], $app['driver_type'], $app['email'], $app['site'], $app['recruiter_email'], $app['rating'], $app['full_name_first'], $app['full_name_middle'], $app['full_name_last'], $app['ssn'], $app['license_revoked'], $app['dui'], $app['felony'], $app['rckdrv'], $app['part_of_team'], $app['dob'], $app['address_1'], $app['address_2'], $app['city'], $app['state'], $app['zip'], $app['phone'], $app['phone_cell'], $app['cdl_number'], $app['cdl_state'], $app['cdl_expire_date'], $app['dbltpl'], $app['passng'], $app['tank'], $app['hazmat'], $app['cdl_class']);
    return $result;
}
function getXmlHeader() {
    $result = <<<XML
<?xml version="1.0" standalone="yes"?>
<!-- All Dates are in YYYYMMDD format. -->
<!-- To include time use this format YYYYMMDDTHH:MM:SSnnn format. -->
<DATAPACKET Version="2.0">
  <METADATA>
    <FIELDS>
      <FIELD attrname="DRVFN" fieldtype="string" WIDTH="20"/>      <!-- Driver's First Name  -->
      <FIELD attrname="MIDDLENAME" fieldtype="string" WIDTH="20"/> <!-- Driver's Middle Name -->
      <FIELD attrname="DRVLN" fieldtype="string" WIDTH="20"/>      <!-- Driver's Last Name   -->
      <FIELD attrname="DRVSFX" fieldtype="string" WIDTH="3"/>      <!-- Driver's Suffix      -->
      <FIELD attrname="SSN" fieldtype="i4"/>                       <!-- Driver's SSN         -->
      <FIELD attrname="GENDER" fieldtype="string" WIDTH="1"/>      <!-- Driver's Gender M/F  -->
      <FIELD attrname="DOB" fieldtype="dateTime"/>                 <!-- Birthdate YYYYMMDD   -->
      <FIELD attrname="ORAPDT" fieldtype="dateTime"/>              <!-- Application Date     -->
      <FIELD attrname="AVLDTE" fieldtype="dateTime"/>              <!-- Date Available       -->
      <FIELD attrname="ADDR1" fieldtype="string" WIDTH="35"/>      <!-- Driver's Address 1   -->
      <FIELD attrname="ADDR2" fieldtype="string" WIDTH="35"/>      <!-- Driver's Address 2   -->
      <FIELD attrname="CITY" fieldtype="string" WIDTH="25"/>       <!-- Driver's City        -->
      <FIELD attrname="ST" fieldtype="string" WIDTH="2"/>          <!-- Driver's State       -->
      <FIELD attrname="ZIP" fieldtype="string" WIDTH="13"/>        <!-- Driver's Zip         -->
      <FIELD attrname="PHONE" fieldtype="string" WIDTH="15"/>      <!-- Driver's Phone       -->
      <FIELD attrname="DRPHON2" fieldtype="string" WIDTH="15"/>    <!-- Driver's Alt Phone   -->
      <FIELD attrname="EMAILADDR" fieldtype="string" WIDTH="50"/>  <!-- Driver's Email Addr  -->

      <!--These fields can change meaning between diffrent customers char 260-277 in old format. -->
      <FIELD attrname="ACD3YR" fieldtype="i2"/>                    <!-- Accident Last 3 years -->
      <FIELD attrname="VIO3YR" fieldtype="i2"/>                    <!-- Violations Last 3 yrs -->
      <FIELD attrname="TOTEXP" fieldtype="i2"/>                    <!-- Total Months OTR exp  -->
      <FIELD attrname="EXP3YR" fieldtype="i2"/>                    <!-- OTR Exp Last 3 years  -->
      <FIELD attrname="LICSUS" fieldtype="string" WIDTH="1"/>      <!-- License Ever Suspended-->
      <FIELD attrname="DUI" fieldtype="string" WIDTH="1"/>         <!-- Any DUIs              -->
      <FIELD attrname="FELONY" fieldtype="string" WIDTH="1"/>      <!-- Any Felony Convictions-->
      <FIELD attrname="RCKDRV" fieldtype="string" WIDTH="1"/>      <!-- Any Reckless Driving  -->
      <FIELD attrname="TMMEMB" fieldtype="string" WIDTH="1"/>      <!-- Will Work as Team memb-->

      <FIELD attrname="MEXICO" fieldtype="string" WIDTH="1"/>      <!-- Mexico Access OK -->
      <FIELD attrname="CANADA" fieldtype="string" WIDTH="1"/>      <!-- Canada Access OK -->
      <FIELD attrname="USA" fieldtype="string" WIDTH="1"/>         <!-- USA Access OK -->
      <FIELD attrname="DRVCODE" fieldtype="string" WIDTH="10"/>    <!-- Driver Code -->
      <FIELD attrname="DRRANK" fieldtype="i4"/>                    <!-- Driver's Rank -->

      <FIELD attrname="RECRID" fieldtype="string" WIDTH="10"/>     <!-- Recruitier User ID -->
      <FIELD attrname="HNDLBY" fieldtype="string" WIDTH="10"/>     <!-- Processor User ID  -->

      <!--The following fields are lookup fields. They are integer fields that represent -->
      <!--items in drop down boxes in the program. These values can be diffrent at each customer. -->
      <FIELD attrname="CORID" fieldtype="i4"/>                     <!-- Company ID -->
      <FIELD attrname="MARSTS" fieldtype="i4"/>                    <!-- Marital Status  -->
      <FIELD attrname="ADRID" fieldtype="i4"/>                     <!-- Ad Responed To -->
      <FIELD attrname="EXTRID" fieldtype="i4"/>                    <!-- External Recruiter -->
      <FIELD attrname="QUARID" fieldtype="i4"/>                    <!-- Qualification Code -->
      <FIELD attrname="EMSTS" fieldtype="i4"/>                     <!-- Employment Status -->
      <FIELD attrname="RECTYP" fieldtype="i4"/>                    <!-- Driver Type -->
      <FIELD attrname="DIVIS" fieldtype="i4"/>                     <!-- Division -->
      <FIELD attrname="REFRID" fieldtype="i4"/>                    <!-- Referred By -->
      <FIELD attrname="OWNRID" fieldtype="i4"/>                    <!-- Owner ID -->

      <!--The following field unquiely identifies drivers. This field is for export only. -->
      <FIELD attrname="DRVRID" fieldtype="i4"/>

      <FIELD attrname="Comments" fieldtype="nested">
        <FIELDS>
           <FIELD attrname="COMMNT" fieldtype="string" WIDTH="1000"/>
           <FIELD attrname="CMTDTE" fieldtype="dateTime"/>
        </FIELDS>
      </FIELD>

      <FIELD attrname="CDLs" fieldtype="nested">
        <FIELDS>
           <FIELD attrname="CDLNUM" fieldtype="string" WIDTH="20"/>
           <FIELD attrname="CDLST" fieldtype="string" WIDTH="2"/>
           <FIELD attrname="EXPDT" fieldtype="dateTime"/>
           <FIELD attrname="DBLTPL" fieldtype="string" WIDTH="1"/>
           <FIELD attrname="PASSNG" fieldtype="string" WIDTH="1"/>
           <FIELD attrname="TANK" fieldtype="string" WIDTH="1"/>
           <FIELD attrname="HAZMAT" fieldtype="string" WIDTH="1"/>
           <FIELD attrname="LCLASS" fieldtype="string" WIDTH="2"/>
        </FIELDS>
      </FIELD>

      <FIELD attrname="Addresses" fieldtype="nested">
        <FIELDS>
           <FIELD attrname="ADRTYP" fieldtype="string" WIDTH="10"/>
           <FIELD attrname="ADDR1" fieldtype="string" WIDTH="35"/>
           <FIELD attrname="ADDR2" fieldtype="string" WIDTH="35"/>
           <FIELD attrname="ST" fieldtype="string" WIDTH="2"/>
           <FIELD attrname="ZIP" fieldtype="string" WIDTH="13"/>
           <FIELD attrname="PHONE" fieldtype="string" WIDTH="15"/>
           <FIELD attrname="CITY" fieldtype="string" WIDTH="25"/>
           <FIELD attrname="BEGDATE" fieldtype="dateTime"/>
           <FIELD attrname="ENDDATE" fieldtype="dateTime"/>
        </FIELDS>
      </FIELD>

      <FIELD attrname="Employers" fieldtype="nested">
        <FIELDS>
           <FIELD attrname="PREMNAME" fieldtype="string" WIDTH="40"/>    <!-- Employer Name-->
           <FIELD attrname="CONTCT" fieldtype="string" WIDTH="40"/>      <!-- Contact Name-->
           <FIELD attrname="CONTITLE" fieldtype="string" WIDTH="40"/>    <!-- Contact Title-->
           <FIELD attrname="JOBPRF" fieldtype="string" WIDTH="40"/>      <!-- Job Performed-->
           <FIELD attrname="ADDR1" fieldtype="string" WIDTH="35"/>       <!-- Employer Address 1-->
           <FIELD attrname="ADDR2" fieldtype="string" WIDTH="35"/>       <!-- Employer Address 2-->
           <FIELD attrname="CITY" fieldtype="string" WIDTH="25"/>        <!-- Employer City-->
           <FIELD attrname="ST" fieldtype="string" WIDTH="2"/>           <!-- Employer St-->
           <FIELD attrname="ZIP" fieldtype="string" WIDTH="15"/>         <!-- Employer Zip-->
           <FIELD attrname="PHONE" fieldtype="string" WIDTH="15"/>       <!-- Employer Phone-->
           <FIELD attrname="FAXPHONE" fieldtype="string" WIDTH="15"/>    <!-- Employer Fax-->
           <FIELD attrname="LICSUS" fieldtype="string" WIDTH="1"/>       <!-- License Ever Suspended-->
           <FIELD attrname="REFDRT" fieldtype="string" WIDTH="1"/>       <!-- Refused Drug Tests-->
           <FIELD attrname="DISPRB" fieldtype="string" WIDTH="1"/>       <!-- Disiplinary Problems-->
           <FIELD attrname="ELGRHR" fieldtype="string" WIDTH="1"/>       <!-- Eligible for Rehire-->
           <FIELD attrname="DRVJOB" fieldtype="string" WIDTH="1"/>       <!-- Is driving job -->
           <FIELD attrname="BEGDTE" fieldtype="dateTime"/>               <!-- Driver Supplied Begin Date -->
           <FIELD attrname="ENDDTE" fieldtype="dateTime"/>               <!-- Driver Supplied End Date -->
           <FIELD attrname="EMPBDT" fieldtype="dateTime"/>               <!-- Employer Supplied Begin Date -->
           <FIELD attrname="EMPEDT" fieldtype="dateTime"/>               <!-- Employer Supplied End Date -->

           <!--Lookup Field-->
           <FIELD attrname="RSNRID" fieldtype="i4"/>                     <!--Reason for leaving-->

           <FIELD attrname="Comments" fieldtype="nested">
             <FIELDS>
               <FIELD attrname="COMMNT" fieldtype="string" WIDTH="1000"/>
               <FIELD attrname="CMTDTE" fieldtype="dateTime"/>
             </FIELDS>
           </FIELD>

        </FIELDS>
      </FIELD>

      <FIELD attrname="UDF" fieldtype="nested">
        <FIELDS>
          <FIELD attrname="CHAR25A" fieldtype="string" WIDTH="20"/>
          <FIELD attrname="CHAR25B" fieldtype="string" WIDTH="20"/>
          <FIELD attrname="CHAR25C" fieldtype="string" WIDTH="20"/>
          <FIELD attrname="CHAR25D" fieldtype="string" WIDTH="20"/>
          <FIELD attrname="INTGRA" fieldtype="i4"/>
          <FIELD attrname="INTGRB" fieldtype="i4"/>
          <FIELD attrname="INTGRC" fieldtype="i4"/>
          <FIELD attrname="INTGRD" fieldtype="i4"/>
          <FIELD attrname="FLOATA" fieldtype="r8"/>
          <FIELD attrname="FLOATB" fieldtype="r8"/>
          <FIELD attrname="FLOATC" fieldtype="r8"/>
          <FIELD attrname="FLOATD" fieldtype="r8"/>
          <FIELD attrname="FLAGA" fieldtype="string" WIDTH="1"/>
          <FIELD attrname="FLAGA" fieldtype="string" WIDTH="1"/>
          <FIELD attrname="FLAGA" fieldtype="string" WIDTH="1"/>
          <FIELD attrname="FLAGA" fieldtype="string" WIDTH="1"/>
          <FIELD attrname="DATEA" fieldtype="dateTime"/>
          <FIELD attrname="DATEB" fieldtype="dateTime"/>
          <FIELD attrname="DATEC" fieldtype="dateTime"/>
          <FIELD attrname="DATED" fieldtype="dateTime"/>
          <FIELD attrname="PICKA" fieldtype="i4"/>
          <FIELD attrname="PICKB" fieldtype="i4"/>
          <FIELD attrname="PICKC" fieldtype="i4"/>
          <FIELD attrname="PICKD" fieldtype="i4"/>
        </FIELDS>
      </FIELD>

    </FIELDS>
  </METADATA>
  <ROWDATA>

XML;
    return $result;
}
function getXmlFooter() {
    $result = <<<XML
  </ROWDATA>
</DATAPACKET>

XML;
    return $result;
}
