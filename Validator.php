<?php
	
	namespace Dansnet;

	/**
	 * Klasse zur Definition von Validierungs-Regeln von Objekten oder Arrays.
	 *
	 * @author deschmid
	 */
	class Validator {
		
		const VALIDATE_STRING			= 1;
		const VALIDATE_INTEGER			= 2;
		const VALIDATE_BOOLEAN			= 3;
		const VALIDATE_FLOAT			= 4;
		const VALIDATE_EMAIL			= 5;
		const VALIDATE_URL				= 6;
		const VALIDATE_DATETIME			= 7;
		const VALIDATE_NOT_EMPTY		= 8;
		const VALIDATE_EXPRESSION		= 9;
				
		private $VALIDATION_FUNCTION_MAPPING = [
			VALIDATOR::VALIDATE_STRING			=> "validateString",
			VALIDATOR::VALIDATE_INTEGER			=> "validateInteger",
			VALIDATOR::VALIDATE_BOOLEAN			=> "validateBoolean",
			VALIDATOR::VALIDATE_FLOAT			=> "validateFloat",
			VALIDATOR::VALIDATE_EMAIL			=> "validateEmail",
			VALIDATOR::VALIDATE_URL				=> "validateUrl",
			VALIDATOR::VALIDATE_DATETIME		=> "validateDateTime",
			VALIDATOR::VALIDATE_NOT_EMPTY		=> "validateNotEmpty",
			VALIDATOR::VALIDATE_EXPRESSION		=> "validateExpression"
		];
		
		private $VALIDATION_MESSAGE_MAPPING = [
			VALIDATOR::VALIDATE_STRING			=> "Input is not a string.",
			VALIDATOR::VALIDATE_INTEGER			=> "Input is not an integer.",
			VALIDATOR::VALIDATE_BOOLEAN			=> "Input is not a boolean.",
			VALIDATOR::VALIDATE_FLOAT			=> "Input is not a float.",
			VALIDATOR::VALIDATE_EMAIL			=> "Input is not an E-Mail.",
			VALIDATOR::VALIDATE_URL				=> "Input is not an URL.",
			VALIDATOR::VALIDATE_DATETIME		=> "Input is not a DateTime.",
			VALIDATOR::VALIDATE_NOT_EMPTY		=> "Input is empty.",
			VALIDATOR::VALIDATE_EXPRESSION		=> "Expression failed."
		];
		
		/**
		 * Validierungsregeln
		 * @var array 
		 */
		private $rules;
		
		/**
		 * Liste der Validierungsergebnisse
		 * @var ValidationMessageList
		 */
		private $messages;
		
		public function __construct() {
			$this->rules = [];
			$this->messages = new ValidationMessageList();
		}
		
		/**
		 * Fügt eine Validierungsregel hinzu.
		 * 
		 * @param string $key
		 * @param integer $rule
		 * @param ... $params
		 */
		public function addRule( $key, $rule, ...$params ) {
			$this->rules[] = new ValidationRule($key, $rule, $params);
		}
		
		/**
		 * Gibt alle Regeln zurück.
		 * 
		 * @return array
		 */
		public function getRules() {
			return $this->rules;
		}
		
		/**
		 * Validiert die übergebenen Daten anhand der definierten Regeln.
		 * Sind die übergebene Daten kein Array oder Objekt wird FALSE zurückgegeben.
		 * 
		 * @param mixed $data
		 * @return boolean|FALSE
		 */
		public function validate( $data ) {
			$this->messages->clearMessages();
			if( is_array($data) ) {
				return $this->validateArray($data);
			} else if ( is_object($data) ) {
				return $this->validateObject($data);
			} 
			return FALSE;
		}
		
		/**
		 * Validiert ein Objekt. Dabei können nur öffentlichen Attribute verwerdet werden.
		 * 
		 * @param object $data
		 * @return boolean|FALSE
		 */
		public function validateObject( $data ) {
			if( !is_object($data) ) return FALSE;
			return $this->validateArray((array)$data);
		}
		
		/**
		 * Validiert ein Array. Falls der gesuchte Key nicht im Array existiert
		 * wird FALSE zurückgegeben. Im Fehlerfall wird auch FALSE zurückgegeben.
		 * 
		 * @param array $data
		 * @return boolean|FALSE
		 */
		public function validateArray( array $data ) {
			/* @var $rule ValidationRule */
			$validationResult = true;
			foreach( $this->rules as $rule ) {
				
				$validationKey = $rule->getKey();
				$value = array_key_exists($validationKey, $data) ? $data[$validationKey] : "";
				$validationParams = array_merge([$value], $rule->getAdditional()); 
				$validationRule = $rule->getRule();
				
				$isValid = call_user_func_array(
					array($this, $this->VALIDATION_FUNCTION_MAPPING[$validationRule]),
					$validationParams
				);
				
				$validationResult = $validationResult && $isValid; 
				
				$this->messages->createMessage($isValid, $validationKey, $validationRule, $this->VALIDATION_MESSAGE_MAPPING[$validationRule]);
			} 
			return $validationResult;
		}
		
		/**
		 * Prüft, ob ein Wert vom Typ string ist.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateString( $value ) {
			return  is_string($value);
		}
		
		/**
		 * Prüft, ob ein Wert vom Typ integer ist.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateInteger( $value, $min=NULL, $max=NULL ) {
			return is_integer($value) && (is_null($min)||$min<$value) && (is_null($max)||$max>$value);
		}
		
		/**
		 * Prüft, ob ein Wert vom Typ boolean ist. Gibt TRUE für die Werte "1", 
		 * "true", "on" und "yes" zurück. Sonst FALSE.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateBoolean( $value ) {
			return filter_var($value, FILTER_VALIDATE_BOOLEAN);
		}
		
		/**
		 * Prüft, ob ein Wert vom Typ float ist. 
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateFloat( $value ) {
			return filter_var($value, FILTER_VALIDATE_FLOAT);
		}
		
		/**
		 * Prüft, ob ein string eine Zeitangabe entspricht. Optional kann ein
		 * Zeitraum mitgegeben werden, in dem geprüft wird, ob das Datum in diesem
		 * Zeitraum liegt.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateDateTime( $value, $min=NULL, $max=NULL ) {
			return is_string($value) && date_create($value) !== FALSE 
					&& ( is_null($min)||date_create($min)<date_create($value) )
					&& ( is_null($max)||date_create($max)>date_create($value) );
		}
		
		/**
		 * Prüft, ob ein Wert vom Typ string ist und den Vorgaben einer E-Mail-Adresse entspricht.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateEmail( $value ) {
			return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL);
		}
		
		/**
		 * Prüft, ob ein Wert vom Typ string ist und den Vorgaben einer URL entspricht.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateUrl( $value ) {
			return is_string($value) && filter_var($value, FILTER_VALIDATE_URL);
		}
		
		/**
		 * Prüft, ob eine Variable einen Wert hat.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateNotEmpty( $value ) {
			return !empty($value);
		}
		
		/**
		 * Prüft, ob eine der Ausdruck wahr ist.
		 * @param mixed $value
		 * @return boolean
		 */
		public function validateExpression( $value ) {
			return is_bool($value);
		}
		
		/**
		 * Gibt die Validierungsergebnisse zurück.
		 * @return array
		 */
		public function getResult() {
			return $this->messages->getMessages();
		}
		
		/**
		 * Gibt alle erfolgreichen Validierungsergebnisse zurück.
		 * @return array
		 */
		public function getResultSuccess() {
			/* @var $message ValidationMessage */
			$success = [];
			foreach( $this->getResult() as $message ) {
				if( $message->getCode() === ValidationMessage::CODE_OK ) {
					$success[] = $message;
				}
			}
			return $success;
		}
		
		/**
		 * Gibt alle erfolgreichen Validierungsergebnisse zurück.
		 * @return array
		 */
		public function getResultErrors() {
			/* @var $message ValidationMessage */
			$errors = [];
			foreach( $this->getResult() as $message ) {
				if( $message->getCode() !== ValidationMessage::CODE_OK ) {
					$errors[] = $message;
				}
			}
			return $errors;
		}
	}
	
	/**
	 * Definiert eine Validierungsregel. Sie enthält Informationen darüber,
	 * welches Attribut anhand welcher Regel geprüft werden soll. Manche Regeln
	 * benötigen zusätzliche Validierungsparameter (z.B. VALIDATE_MIN_LENGTH).
	 */
	class ValidationRule {
		
		/**
		 * Key des Arrays oder Attribut des Objekts, das geprüft werden soll.
		 * @var string
		 */
		private $key;
		
		/**
		 * Validierungsregel (s. Validator)
		 * @var integer
		 */
		private $rule;
		
		/**
		 * Zusätzliche Validierungsparameter.
		 * @var array
		 */
		private $additional;
		
		public function __construct( $key, $rule, array $additional ) {
			$this->key = $key;
			$this->rule = $rule;
			$this->additional = $additional;
		}
		
		public function getKey() { return $this->key; }
		public function getRule() { return $this->rule; }
		public function getAdditional() { return $this->additional; }
		
	}
	
	/**
	 * Klasse zum sammeln von Validierungsfehlern. Jeder Fehler enthält einen 
	 * Fehlercode und einen Text. Diese Daten werden jeweils einem Key zugeordnet,
	 * der dem entsprechenden Attribut aus der Benutzereingabe entspricht. 
	 */
	class ValidationMessage {
		
		const CODE_OK					= 0;
		const CODE_REQUIRED				= 1;
		const CODE_NOT_STRING			= 2;	
		const CODE_NOT_INTEGER			= 3;
		const CODE_NOT_FLOAT			= 4;
		const CODE_NOT_BOOL				= 5;
		const CODE_NOT_EMAIL			= 6;
		const CODE_NOT_URL				= 7;
		const CODE_NOT_DATETIME			= 8;
		const CODE_EXPRESSION_FAILED	= 9;
		
		/**
		 * Fehler-Code
		 * @var string
		 */
		private $code;
		
		/**
		 * Fehlermeldung
		 * @var string
		 */
		private $msg;
		
		/**
		 * Attributszuordnung
		 * @var string
		 */
		private $key;
		
		public function __construct( $key, $code, $msg ) {
			$this->key = $key;
			$this->code = $code;
			$this->msg = $msg;
		}
		
		public function getCode() { return $this->code; }
		
	}

	/**
	 * Liste aller Validierungsergebnisse
	 */
	class ValidationMessageList {
		
		/**
		 * Liste der Ergebnisse
		 * @var array 
		 */
		private $messages;
		
		public function __construct( array $messages=[] ) {
			$this->messages = $messages;
		}
		
		/**
		 * Erzeugt eine neue Instanz eines Validierungsergebnisses und fügt diese
		 * der Liste hinzu. Ist das Ergebnis valide, so wirde $code immer mit
		 * ValidationMessage::CODE_OK überschrieben.
		 * @param boolean $valid
		 * @param integer $code
		 * @param string $msg
		 */
		public function createMessage( $valid, $key, $code, $msg ) {
			$realCode = $valid ? ValidationMessage::CODE_OK : $code;
			$realMsg = $valid ? "Input is valid" : $msg;
			$newMessage = new ValidationMessage($key, $realCode, $realMsg);
			$this->messages[] = $newMessage;
		}
		
		/**
		 * Fügt ein Validierungsergebnis der Liste hinzu.
		 * @param \Dansnet\ValidationMessage $msg
		 */
		public function addMessage( ValidationMessage $msg ) {
			$this->messages[] = $msg;
		}
		
		/*
		 * Gibt die Liste aller Validierungsergebnisse zurück.
		 * @return array
		 */
		public function getMessages() {
			return $this->messages;
		}
		
		/**
		 * Löscht die Ergebnisliste.
		 */
		public function clearMessages() {
			$this->messages = [];
		}
	}