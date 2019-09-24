<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

require_once 'CiviTest/CiviSeleniumTestCase.php';

/**
 * Class WebTest_Report_DonarReportTest
 */
class WebTest_Report_DonarReportTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  public function testDonarReportPager() {
    $this->webtestLogin();

    // now create new donar detail report instance
    $this->openCiviPage('report/contribute/detail', 'reset=1', '_qf_Detail_submit');

    // preview result
    $this->click("_qf_Detail_submit");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    // Create report

    $reportName = 'ContributeDetail_' . substr(sha1(rand()), 0, 7);
    $reportDescription = "New Contribute Detail Report";

    $this->click("xpath=//div[@id='mainTabContainer']/ul/li[4]/a");
    $this->waitForElementPresent("xpath=//div[@class='crm-submit-buttons']");
    $this->click("xpath=//div[@class='crm-submit-buttons']/input[@name='_qf_Detail_submit_save']");

    // Fill Report Title
    $this->waitForElementPresent("xpath=//div[@class='crm-confirm-dialog ui-dialog-content ui-widget-content modal-dialog']/table/tbody/tr[1]/td[2]/input[@type='text']");
    $this->type("xpath=//div[@class='crm-confirm-dialog ui-dialog-content ui-widget-content modal-dialog']/table/tbody/tr[1]/td[2]/input[@type='text']", $reportName);

    // Fill Report Description
    $this->waitForElementPresent("xpath=//div[@class='crm-confirm-dialog ui-dialog-content ui-widget-content modal-dialog']/table/tbody/tr[2]/td[2]/input[@type='text']");
    $this->type("xpath=//div[@class='crm-confirm-dialog ui-dialog-content ui-widget-content modal-dialog']/table/tbody/tr[2]/td[2]/input[@type='text']", $reportDescription);
    $this->click("xpath=//div[@class='ui-dialog-buttonset']/button[1]/span[2]");

    // We want navigation menu
    $this->waitForElementPresent('_qf_Detail_submit_next');
    $this->click("xpath=//div[@id='mainTabContainer']/ul/li[6]/a");
    $this->click("is_navigation");
    $this->waitForElementPresent("parent_id");

    // Navigation menu under Reports section
    $this->select("parent_id", "label=Reports");

    // Set permission as access CiviCRM
    $this->select("permission", "value=access CiviCRM");

    // click to create report
    $this->click("_qf_Detail_submit_save");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    // Open report list
    $this->openCiviPage('report/list', 'reset=1');

    // Is report is resent in list?
    $this->assertElementContainsText('css=div#Contribute > table.report-layout', $reportName);

    // Visit report
    $this->click("xpath=//div[@id='Contribute']//table/tbody//tr/td/a/strong[text() = '$reportName']");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    //now select the criteria
    //click report criteria accordion
    $this->click("xpath=//div[@id='mainTabContainer']/ul/li[3]/a");
    $this->waitForElementPresent('_qf_Detail_submit_next');

    //enter contribution amount
    $this->waitForAjaxContent();
    $this->select('total_amount_op', "value=gte");
    $this->type('total_amount_value', "10");

    // click preview
    $this->click("_qf_Detail_submit");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    //Is greater than or equal to 100
    //check for criteria
    $this->verifyText("xpath=//table[@class='report-layout statistics-table']/tbody/tr[3]/td", "Is greater than or equal to 10");

    //click on next link
    $this->click("_qf_Detail_submit");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    // check if criteria still exits
    $this->verifyText("xpath=//table[@class='report-layout statistics-table']/tbody/tr[3]/td", "Is greater than or equal to 10");
  }

  /**
   * CRM-18484: To check the filter criteria 'is between'
   */
  public function testCheckIsBetweenFilter() {
    $this->webtestLogin();

    //add contact
    $this->openCiviPage('contact/add', 'reset=1&ct=Individual', '_qf_Contact_cancel-bottom');
    $firstName = 'Jo' . substr(sha1(rand()), 0, 4);
    $lastName = 'Ad' . substr(sha1(rand()), 0, 7);
    $this->type('first_name', $firstName);
    $this->type('last_name', $lastName);

    //address section
    $this->click("xpath=//div[@id='addressBlockId']/div");
    $this->waitForElementPresent("address_1_street_address");
    $this->type("address_1_street_address", "902C El Camino Way SW");
    $this->type("address_1_city", "Dumfries");
    $this->type("address_1_postal_code", "1234");
    $this->click("address_1_country_id");
    $this->select("address_1_country_id", "value=" . $this->webtestGetValidCountryID());
    $this->click("_qf_Contact_upload_view-bottom");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    $this->openCiviPage('report/instance/1', 'reset=1&output=criteria', '_qf_Summary_submit_csv');
    $this->click("fields_postal_code");
    $this->click("xpath=//div[@class='crm-report-criteria']/div[@id='mainTabContainer']/ul//li/a[text()='Filters']");
    $this->waitForElementPresent('gid_value');
    $this->select('postal_code_op', "value=bw");
    $this->waitForElementPresent('postal_code_max');
    $this->type('postal_code_max', '1234');
    $this->click("_qf_Summary_submit");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->assertElementContainsText("xpath=//table[@class='report-layout display']/tbody//tr/td[1]/a", "{$lastName}, {$firstName}");
  }

}
