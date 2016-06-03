<?php

	require_once "../Validator.php";
	
	use Dansnet\Validator;
	
	class ValidatorTest extends PHPUnit_Framework_TestCase  {
		
		public function testValidatorInit() {
			$validator = new Validator();
			$validator->addRule("mystring", Validator::VALIDATE_STRING);
			$validator->addRule("myinteger", Validator::VALIDATE_INTEGER);
			$validator->addRule("myinteger", Validator::VALIDATE_INTEGER, 5);
			$validator->addRule("myinteger", Validator::VALIDATE_INTEGER, null, 30);
			$validator->addRule("myinteger", Validator::VALIDATE_INTEGER, 5, 30);
			$validator->addRule("myboolean", Validator::VALIDATE_BOOLEAN);
			$validator->addRule("mynumber", Validator::VALIDATE_FLOAT);
			$validator->addRule("myemail", Validator::VALIDATE_EMAIL);
			$validator->addRule("myurl", Validator::VALIDATE_URL);
			$validator->addRule("mydate", Validator::VALIDATE_DATETIME);
			$validator->addRule("mydate", Validator::VALIDATE_DATETIME, "2016-05-03");
			$validator->addRule("mydate", Validator::VALIDATE_DATETIME, null, "2016-07-03");
			$validator->addRule("mydate", Validator::VALIDATE_DATETIME, "2016-05-03", "2016-07-03");
			$validator->addRule("mynullvalue", Validator::VALIDATE_NOT_EMPTY);
			$validator->addRule("myexpression", Validator::VALIDATE_EXPRESSION);
			$rules = $validator->getRules();
			$this->assertEquals(15, sizeof($rules));
			return $validator;
		}
		
		/**
		 * @depends testValidatorInit
		 */
		public function testValidation( Validator $validator ) {
			$this->assertTrue($validator->validate(ValidationObjectMock::getInstanceObjectOK()));
			$this->assertFalse($validator->validate(ValidationObjectMock::getInstanceObjectNoString()));
			$this->assertFalse($validator->validate(ValidationObjectMock::getInstanceObjectNoInteger()));
			$this->assertFalse($validator->validate(ValidationObjectMock::getInstanceObjectNoNumber()));
			$this->assertFalse($validator->validate(ValidationObjectMock::getInstanceObjectNoEmail()));
			$this->assertFalse($validator->validate(ValidationObjectMock::getInstanceObjectNoUrl()));
			$this->assertFalse($validator->validate(ValidationObjectMock::getInstanceObjectNull()));
			
			$validator->addRule("notexistingmember", Validator::VALIDATE_NOT_EMPTY);
			$this->assertFalse($validator->validate(ValidationObjectMock::getInstanceObjectOK()));
			return $validator;
		}
		
		/**
		 * @depends testValidation
		 */
		public function testValidationResult( Validator $validator ) {
			$validator->validate(ValidationObjectMock::getInstanceObjectOK());
			$this->assertEquals(sizeof($validator->getResult()), 16);
			$this->assertEquals(sizeof($validator->getResultSuccess()), 15);
			$this->assertEquals(sizeof($validator->getResultErrors()), 1);
		}
		
	}
	
	class ValidationObjectMock {
		
		public $myinteger;
		public $mystring;
		public $mydate;
		public $mynumber;
		public $myemail;
		public $myurl;
		public $mynullvalue;
		public $myexpression;

		public static function getInstanceObjectOK() {
			$mock = new ValidationObjectMock();
			$mock->mystring = "teststring";
			$mock->mydate = "2016-06-03";
			$mock->myinteger = 10;
			$mock->mynumber = 33.545;
			$mock->myboolean = true;
			$mock->myemail = "max.mustermann@web.de";
			$mock->myurl = "http://google.de";
			$mock->myexpression = true;
			$mock->mynullvalue = "notnull";
			return $mock;
		}
		
		public static function getInstanceObjectNoString() {
			$mock = static::getInstanceObjectOK();
			$mock->mystring = 1.29999;
			return $mock;
		}
		
		public static function getInstanceObjectNoInteger() {
			$mock = static::getInstanceObjectOK();
			$mock->myinteger = 1.29999;
			return $mock;
		}
		
		public static function getInstanceObjectNoNumber() {
			$mock = static::getInstanceObjectOK();
			$mock->mynumber = "s1.29999";
			return $mock;
		}
		
		public static function getInstanceObjectNoEmail() {
			$mock = static::getInstanceObjectOK();
			$mock->myemail = "s1.29999";
			return $mock;
		}
		
		public static function getInstanceObjectNoUrl() {
			$mock = static::getInstanceObjectOK();
			$mock->myurl = "test.ss-";
			return $mock;
		}
		
		public static function getInstanceObjectNull() {
			$mock = static::getInstanceObjectOK();
			$mock->mynullvalue = null;
			return $mock;
		}
		
	}
