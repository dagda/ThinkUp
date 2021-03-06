<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/config.inc.php';

class TestOfSignIn extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();

        //Add owner
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $q = "INSERT INTO tu_owners (id, email, pwd, is_activated) VALUES (1, 'me@example.com', '".
        $cryptpass."', 1)";
        $this->db->exec($q);

        //Add instance
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public) VALUES (1, 1234,
        'thinkupapp', 1)";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
    }

    public function testSignInSuccessAndPrivateDashboard() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
    }

    public function testSignInFailureAttemptThenSuccess() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me2@example.com');
        $this->setField('pwd', 'wrongemail');
        $this->click("Log In");

        $this->assertText('Incorrect email');

        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'wrongpassword');
        $this->click("Log In");

        $this->assertText('Incorrect password');
        $this->assertField('email', 'me@example.com');

        $this->setField('pwd', 'secretpassword');
        $this->click("Log In");

        $this->assertTitle("thinkupapp's Dashboard | ThinkUp");
        $this->assertText('Logged in as: me@example.com');
    }
}