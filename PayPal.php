<?php

/*
 * PayPal NVP Library
 * Author: Mahmoud Al-Qudsi <mqudsi@neosmart.net>
 * Copyright (C) 2012 by NeoSmart Technologies
 * This code is released under the terms of the MIT License
*/

namespace neosmart
{
	class PayPal
	{
		var $ppVersion = 87.0;
		
		var $ppEndpoint;
		var $user;
		var $pass;
		var $sig;

		public function __construct($user, $pass, $signature, $sandbox = false)
		{
			$this->user = $user;
			$this->pass = $pass;
			$this->sig = $signature;
			$this->ppEndpoint = ($sandbox) ? "https://api-3t.sandbox.paypal.com/nvp" : "https://api-3t.paypal.com/nvp";
		}
		
		private function EncodeNvpString($fields)
		{
			$nvpstr = "";

			foreach ($fields as $key => $value)
			{
				$nvpstr .= sprintf("%s=%s&", urlencode(strtoupper($key)), urlencode($value));
			}

			$nvpstr = rtrim($nvpstr, "&");

			return $nvpstr;
		}

		private function DecodeNvpString($nvpstr)
		{
			$pairs = explode("&", $nvpstr);

			$fields = array();

			foreach ($pairs as $pair)
			{
				$items = explode("=", $pair);
				$fields[urldecode($items[0])] = urldecode($items[1]);
			}

			return $fields;
		}

		public function GenericNvp($method, $fields)
		{
			$fields["USER"] = $this->user;
			$fields["PWD"] = $this->pass;
			$fields["SIGNATURE"] = $this->sig;
			$fields["VERSION"] = $this->ppVersion;
			$fields["METHOD"] = $method;

			$nvpstr = $this->EncodeNvpString($fields);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->ppEndpoint);
			//curl_setopt($ch, CURLOPT_VERBOSE, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpstr);

			$response = curl_exec($ch);

			$fields = $this->DecodeNvpString($response);

			return $fields;
		}

		//Explicit convenience functions
		//Will be removed soon
		public function SetExpressCheckout($fields)
		{
			$method = "SetExpressCheckout";
			return $this->GenericNvp($method, $fields);
		}

		public function GetExpressCheckoutDetails($fields)
		{
			$method = "GetExpressCheckoutDetails";
			return $this->GenericNvp($method, $fields);
		}

		public function DoExpressCheckoutPayment($fields)
		{
			$method = "DoExpressCheckoutPayment";
			return $this->GenericNvp($method, $fields);
		}
		
		public function RefundTransaction($fields)
		{
			$method = "RefundTransaction";
			return $this->GenericNvp($method, $fields);
		}

		//Implicit convenience functions
		public function __call($method, $fields)
		{
			return $this->GenericNvp($method, $fields);
		}
	}
}

?>
