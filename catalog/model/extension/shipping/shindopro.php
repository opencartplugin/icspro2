<?php
ini_set('display_errors',1);
class ModelExtensionShippingShindopro extends Model {
	function getQuote($address) {
		$classname = str_replace('vq2-catalog_model_shipping_', '', basename(__FILE__, '.php'));
		$this->load->language('extension/shipping/' . $classname);
		$title = $this->config->get($classname.'_title');
		$days = $this->language->get('text_days');
		$error_currency = $this->language->get('error_currency');
		$mod = array('igsjnepro','igspospro','igstikipro', 'igswahanapro', 'igsjntpro', 'igssicepatpro');
		$textmod = array('JNE','POS','TIKI', 'Wahana', 'J&T', 'SiCEPAT');
		$paramod = array('jne','pos','tiki', 'wahana', 'jnt', 'sicepat');
		$iconmod = array('jne128x64.png','pos128x64.png','tiki128x64.png', 'wahana128x64.png', 'jnt128x64.png', 'sicepat128x64.png');
		$method_data = array();
		$quote_data = array();
		//check IDR currency
		$this->load->model('localisation/currency');
		$curr = $this->model_localisation_currency->getCurrencyByCode('IDR');
		if (!$curr) {
			$method_data = array(
				'code'       => 'shindopro',
				'title'      => $title,
				'quote'      => array(),
				'sort_order' => 0,//$this->config->get($classname . '_sort_order'),
				'error'      => $error_currency
			);
			return $method_data;
		}
		$ke = 0;
		//print_r($mod);
		foreach ($mod as $m) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get($m . '_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
			//geo zone
			$status = false;
			if (!$this->config->get($m . '_geo_zone_id') || $query->num_rows) {
				$status = true;
			}
			if (!$this->config->get($m . '_status')) {
				$status = false;
			}
			if ($status) {
				$shipping_weight = $this->cart->getWeight();
				$from = $this->config->get('config_weight_class_id');
				$to = $this->config->get($m . '_weight_class_id');

				$shipping_weight = str_replace(',','',$this->weight->convert($shipping_weight, $from, $to));
				//weight not allowed 0
				if ($shipping_weight == 0) {
					$shipping_weight = 1;
				}
				$hf = 0;
				if ($this->config->get($m . '_handling')) {
						$hf = $this->config->get($m . '_handling');
				}
				$origin_id = $this->config->get('shindopro_city_id');
				$destId = $address['district_id'];
				if ($address['subdistrict_id']) {
						$destId = $address['subdistrict_id'];
						$destType = 'subdistrict';
				}

				$key = $this->config->get('shindopro_apikey');
				if (isset($destType)) {
					$json = $this->getCost($origin_id, $destId, $shipping_weight, $key, $paramod[$ke], $destType);
				} else {
					$json = $this->getCost($origin_id, $destId, $shipping_weight, $key, $paramod[$ke]);
				}
				//print_r($json);
				if (isset($json['rajaongkir']) && isset($json['rajaongkir']['results']) && isset($json['rajaongkir']['results'][0]) && isset($json['rajaongkir']['results'][0]['costs'])) {
					foreach ($json['rajaongkir']['results'][0]['costs'] as $res) {
						$stat = false;
						$services = $this->config->get($m . '_service');
						if ($services) {
							foreach ($this->config->get($m . '_service') as $s) {
								if ($s == $res['service']) {
									$stat = true;
									//break;
								}
							}
						}
						if ($stat) {
							$cost = $res['cost'][0]['value'];
							if ($this->config->get($m . '_handlingmode') == 2) {
								$cost = $cost + ($hf * ($shipping_weight/1000));
							} else {
								$cost = $cost + $hf;
							}

							if ($this->config->get('config_currency') <>'IDR') {
								$cost = $cost / $curr['value'];
							}
							$etd = '';
							if ($res['cost'][0]['etd'] <> '') {
								$etd =  ($res['cost'][0]['etd'] === '1-1' ? '1' : $res['cost'][0]['etd']) . ' '. $days . ' ';
							}
							$quote_data[$m . '-' . $res['service']] = array(
								'code'         => 'shindopro' . '.' . $m . '-' . $res['service'],
								'title'        => $res['service'] . ' ('. $textmod[$ke].')' ,// . $etd,
								'cost'         => $cost,
								'tax_class_id' => $this->config->get($m .'_tax_class_id'),
								'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get($m.'_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']),
								'icon'			 => 'image/shipping/' . $iconmod[$ke],
								'courier' => $textmod[$ke],
								'sername' => $m . '-' . $res['service']
							);
							if ($etd <> '') {
								$quote_data[$m . '-' . $res['service']]['etd'] = $etd;
							} else {
								$quote_data[$m . '-' . $res['service']]['etd'] = ' - ';
							}
						}
					}
				}
			}
			$ke++;
		}

		function compare_cost($a, $b) {
			return strnatcmp($a['cost'], $b['cost']);
		}

		if ($this->config->get($classname . '_sort')) {
			$tmpquote_data = $quote_data;
			usort($tmpquote_data, 'compare_cost');
			$quote_data = array();
			foreach ($tmpquote_data as  $d) {
				$quote_data[$d['sername']] = array(
					'code'         => $d['code'],
					'title'        => $d['title'],// . $etd,
					'cost'         => $d['cost'],
					'tax_class_id' => $d['tax_class_id'],
					'text'         => $d['text'],
					'icon'			 => $d['icon'],
					'courier' => $d['courier'],
				);

			}
		}

		$method_data = array(
			'code'       => 'shindopro',
			'title'      => $title,
			'quote'      => $quote_data,
			'sort_order' => 0,//$this->config->get($classname . '_sort_order'),
			'error'      => false
		);

		return $method_data;
	}

	public function getCost($origin, $destination, $weight, $key, $param, $destType='city') {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://pro.rajaongkir.com/api/cost",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "origin=" . (int)$origin . "&originType=city&destination=" . (int)$destination . "&destinationType=" . $destType . "&weight=" . (int)$weight ."&courier=" . $param,
		  CURLOPT_HTTPHEADER => array(
				"content-type: application/x-www-form-urlencoded",
		    "key: " . $key,
		  ),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  return "cURL Error #:" . $err;
		} else {
			return json_decode($response, true);
		}
	}


	public function sortBySubValue($array, $value, $asc = true, $preserveKeys = false)
	{
	    if (is_object(reset($array))) {
	        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
	            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} - $b->{$value}) * ($asc ? 1 : -1);
	        }) : usort($array, function ($a, $b) use ($value, $asc) {
	            return $a->{$value} == $b->{$value} ? 0 : ($a->{$value} - $b->{$value}) * ($asc ? 1 : -1);
	        });
	    } else {
	        $preserveKeys ? uasort($array, function ($a, $b) use ($value, $asc) {
	            return $a[$value] == $b[$value] ? 0 : ($a[$value] - $b[$value]) * ($asc ? 1 : -1);
	        }) : usort($array, function ($a, $b) use ($value, $asc) {
	            return $a[$value] == $b[$value] ? 0 : ($a[$value] - $b[$value]) * ($asc ? 1 : -1);
	        });
	    }
	    return $array;
	}
}
