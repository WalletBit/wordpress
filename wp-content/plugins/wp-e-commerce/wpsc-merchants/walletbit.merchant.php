<?php
	$nzshpcrt_gateways[$num]['name'] = 'Bitcoins';
	$nzshpcrt_gateways[$num]['internalname'] = 'walletbit';
	$nzshpcrt_gateways[$num]['function'] = 'gateway_walletbit';
	$nzshpcrt_gateways[$num]['form'] = 'form_walletbit';
	$nzshpcrt_gateways[$num]['submit_function'] = "submit_walletbit";

	function form_walletbit()
	{	
		$rows = array();
		
		$rows[] = array('E-Mail', '<input name="walletbit_email" type="text" value="' . get_option('walletbit_email') . '" />', 'Your WalletBit email');
		$rows[] = array('Token', '<input name="walletbit_token" type="text" value="' . get_option('walletbit_token') . '" />', 'copy from walletbit.com/businesstools/IPN');
		$rows[] = array('Security Word', '<input name="walletbit_securityword" type="text" value="' . get_option('walletbit_securityword') . '" />', 'Enter your Security Word');

		foreach($rows as $r)
		{
			$output .= '<tr> <td>' . $r[0] . '</td> <td>' . $r[1];
			
			if (isset($r[2]))
			{
				$output .= '<BR/><small>' . $r[2] . '</small></td> ';
			}

			$output .= '</tr>';
		}
		
		return $output;
	}

	function submit_walletbit()
	{
		$params = array('walletbit_email', 'walletbit_token', 'walletbit_securityword');
		foreach($params as $p)
			if ($_POST[$p] != null)
				update_option($p, $_POST[$p]);
		return true;
	}

	function gateway_walletbit($seperator, $sessionid)
	{	
		//$wpdb is the database handle,
		//$wpsc_cart is the shopping cart object
		global $wpdb, $wpsc_cart;
		
		//This grabs the purchase log id from the database
		//that refers to the $sessionid
		$purchase_log = $wpdb->get_row("SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1", ARRAY_A) ;

		//This grabs the users info using the $purchase_log
		// from the previous SQL query
		$usersql = "SELECT `".WPSC_TABLE_SUBMITED_FORM_DATA."`.value, `".WPSC_TABLE_CHECKOUT_FORMS."`.`name`, `".WPSC_TABLE_CHECKOUT_FORMS."`.`unique_name` FROM `".WPSC_TABLE_CHECKOUT_FORMS."` LEFT JOIN `".WPSC_TABLE_SUBMITED_FORM_DATA."` ON `".WPSC_TABLE_CHECKOUT_FORMS."`.id = `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`form_id` WHERE `".WPSC_TABLE_SUBMITED_FORM_DATA."`.`log_id`=".$purchase_log['id'];

		$userinfo = $wpdb->get_results($usersql, ARRAY_A);
		
		// convert from awkward format 
		foreach((array)$userinfo as $value) 
			if (strlen($value['value']))
				$ui[$value['unique_name']] = $value['value'];
		$userinfo = $ui;
		
		// name
		if (isset($userinfo['billingfirstname']))
		{
			$options['buyerName'] = $userinfo['billingfirstname'];
			if (isset($userinfo['billinglastname']))
			{
				$options['buyerName'] .= ' '.$userinfo['billinglastname'];
			}
		}
		
		//address -- remove newlines
		if (isset($userinfo['billingaddress']))
		{
			$newline = strpos($userinfo['billingaddress'],"\n");
			if ($newline !== FALSE)
			{
				$options['buyerAddress1'] = substr($userinfo['billingaddress'], 0, $newline);
				$options['buyerAddress2'] = substr($userinfo['billingaddress'], $newline+1);
				$options['buyerAddress2'] = preg_replace('/\r\n/', ' ', $options['buyerAddress2'], -1, $count);
			}
			else
			{
				$options['buyerAddress1'] = $userinfo['billingaddress'];
			}
		}
		// state
		if (isset($userinfo['billingstate']))
		{
			$options['buyerState'] = wpsc_get_state_by_id($userinfo['billingstate'], 'code');
		}

		// more user info
		foreach(array('billingphone' => 'buyerPhone', 'billingemail' => 'buyerEmail', 'billingcity' => 'buyerCity',  'billingcountry' => 'buyerCountry', 'billingpostcode' => 'buyerZip') as $f => $t)
		{
			$options[$t] = $userinfo[$f];
		}

		// itemDesc
		if (count($wpsc_cart->cart_items) == 1)
		{
			$item = $wpsc_cart->cart_items[0];
			$options['itemDesc'] = $item->product_name;
			if ( $item->quantity > 1 )
			{
				$options['itemDesc'] = $item->quantity.'x '.$options['itemDesc'];
			}
		}
		else
		{
			foreach($wpsc_cart->cart_items as $item) 
			{
				$quantity += $item->quantity;
			}

			$options['itemDesc'] = $quantity.' items';
		}
		
		//currency
		$currencyId = get_option('currency_type');
		$currency = $wpdb->get_var($wpdb->prepare("SELECT `code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id` = %d LIMIT 1", $currencyId));

		$price = number_format($wpsc_cart->total_price, 2, '.', '');

		//$options = http_build_query($options, '', '|');

		//$url = 'https://walletbit.com/pay?token=' . get_option('walletbit_token') . '&item_name=' . $options['itemDesc'] . '&amount=' . $price . '&currency=' . $currency . '&returnurl=' . rawurlencode(get_option('siteurl')) . '&additional=sessionid=' . $sessionid;

print '<form id="pay" method="post" action="https://walletbit.com/pay">';
print '  <input type="hidden" name="token" value="' . get_option('walletbit_token') . '" />';
print '  <input type="hidden" name="item_name" value="' . $options['itemDesc'] . '" />';
print '  <input type="hidden" name="amount" value="' . $price . '" />';
print '  <input type="hidden" name="currency" value="' . $currency . '" />';
print '  <input type="hidden" name="returnurl" value="' . rawurlencode(get_option('shopping_cart_url')) . '" />';
print '  <input type="hidden" name="additional" value="purchaseid=' . $purchase_log['id'] . '|sessionid=' . $sessionid . '|email=' . $options['buyerEmail'] . '" />';
print '  <input type="hidden" name="test" value="0" />';
print '</form>';
print '<script type="text/javascript">';
print '	document.getElementById("pay").submit();';
print '</script>';

		$wpsc_cart->empty_cart();
		unset($_SESSION['WpscGatewayErrorMessage']);

		//header('Location: ' . $url);
		//print '<meta http-equiv="refresh" content="0; url=' . $url . '"/>';
	}

	function walletbit_callback()
	{
		global $wpdb;

		$str =
		$_POST["merchant"].":".
		$_POST["customer_email"].":".
		$_POST["amount"].":".
		get_option('walletbit_securityword');

		$hash = strtoupper(hash('sha256', $str));

		// proccessing payment only if hash is valid
		if (isset($_POST['type']) && strtolower($_POST['type']) == 'cancel' && $_POST["merchant"] == get_option('walletbit_email') && $_POST["encrypted"] == $hash)
		{
			$sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '6' WHERE `id`=" . intval($_POST['purchaseid']);
			$wpdb->query($sql);

			print '1';
			exit;
		}
		else
		{
			$str =
			$_POST["merchant"].":".
			$_POST["customer_email"].":".
			$_POST["amount"].":".
			$_POST["batchnumber"].":".
			$_POST["txid"].":".
			$_POST["address"].":".
			get_option('walletbit_securityword');

			$hash = strtoupper(hash('sha256', $str));

			// proccessing payment only if hash is valid
			if ($_POST["merchant"] == get_option('walletbit_email') && $_POST["encrypted"] == $hash && $_POST["status"] == 1)
			{
				$purchase_log = $wpdb->get_row("SELECT totalprice FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `id`= " . intval($_POST['purchaseid']) . " LIMIT 1", ARRAY_A) ;

				$bitcoin = number_format($_POST['amount'] * $_POST['rate'], 2, '.', '');
				$amount = number_format($purchase_log['totalprice'], 2, '.', '');

				if ($bitcoin >= $amount)
				{
					$sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '3' WHERE `id`=" . intval($_POST['purchaseid']);
					$wpdb->query($sql);
				}
				else if ($bitcoin < $amount && $bitcoin > 0)
				{
					$sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '2' WHERE `id`=" . intval($_POST['purchaseid']);
					$wpdb->query($sql);
				}
				else
				{
					$sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `processed`= '6' WHERE `id`=" . intval($_POST['purchaseid']);
					$wpdb->query($sql);
				}

				print '1';
				exit;
			}
		}
	}

	add_action('init', 'walletbit_callback');
?>
