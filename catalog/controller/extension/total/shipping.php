<?php
class ControllerExtensionTotalShipping extends Controller {
	public function index() {
		if ($this->config->get('shipping_status') && $this->config->get('shipping_estimator') && $this->cart->hasShipping()) {
			$this->load->language('extension/total/shipping');

			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_shipping'] = $this->language->get('text_shipping');
			$data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$data['text_select'] = $this->language->get('text_select');
			$data['text_none'] = $this->language->get('text_none');
			$data['text_loading'] = $this->language->get('text_loading');

			$data['entry_country'] = $this->language->get('entry_country');
			$data['entry_zone'] = $this->language->get('entry_zone');

        $data['entry_district'] = $this->language->get('entry_district');//frd 1
        $data['entry_subdistrict'] = $this->language->get('entry_subdistrict');//frd 1
      
			$data['entry_postcode'] = $this->language->get('entry_postcode');

			$data['button_quote'] = $this->language->get('button_quote');
			$data['button_shipping'] = $this->language->get('button_shipping');
			$data['button_cancel'] = $this->language->get('button_cancel');

			if (isset($this->session->data['shipping_address']['country_id'])) {
				$data['country_id'] = $this->session->data['shipping_address']['country_id'];
			} else {
				$data['country_id'] = $this->config->get('config_country_id');
			}

			$this->load->model('localisation/country');

			$data['countries'] = $this->model_localisation_country->getCountries();

			if (isset($this->session->data['shipping_address']['zone_id'])) {
				$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
			} else {
				$data['zone_id'] = '';
			}


        //frd 2
        if (isset($this->session->data['shipping_address']['district_id'])) {
  				$data['district_id'] =  $this->session->data['shipping_address']['district_id'];
  			} else {
  				$data['district_id'] = '';
  			}
  			if (isset($this->session->data['shipping_address']['subdistrict_id'])) {
  				$data['subdistrict_id'] =  $this->session->data['shipping_address']['subdistrict_id'];
  			} else {
  				$data['subdistrict_id'] = '';
  			}

  			//------

      
			if (isset($this->session->data['shipping_address']['postcode'])) {
				$data['postcode'] = $this->session->data['shipping_address']['postcode'];
			} else {
				$data['postcode'] = '';
			}

			if (isset($this->session->data['shipping_method'])) {
				$data['shipping_method'] = $this->session->data['shipping_method']['code'];
			} else {
				$data['shipping_method'] = '';
			}

			return $this->load->view('extension/total/shipping', $data);
		}
	}

	public function quote() {
		$this->load->language('extension/total/shipping');

		$json = array();

		if (!$this->cart->hasProducts()) {
			$json['error']['warning'] = $this->language->get('error_product');
		}

		if (!$this->cart->hasShipping()) {
			$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
		}

		if ($this->request->post['country_id'] == '') {
			$json['error']['country'] = $this->language->get('error_country');
		}

        //frd 6
    		if ($this->request->post['country_id'] == 100) {
    			if (!isset($this->request->post['district_id']) || $this->request->post['district_id'] == '' || !is_numeric($this->request->post['district_id'])) {
    				$json['error']['district'] = $this->language->get('error_district');
    			}
    		}

      

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
			$json['error']['zone'] = $this->language->get('error_zone');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$json['error']['postcode'] = $this->language->get('error_postcode');
		}

		if (!$json) {
			$this->tax->setShippingAddress($this->request->post['country_id'], $this->request->post['zone_id']);

			if ($country_info) {
				$country = $country_info['name'];
				$iso_code_2 = $country_info['iso_code_2'];
				$iso_code_3 = $country_info['iso_code_3'];
				$address_format = $country_info['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$this->load->model('localisation/zone');

			$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

			if ($zone_info) {
				$zone = $zone_info['name'];
				$zone_code = $zone_info['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			$this->session->data['shipping_address'] = array(
				'firstname'      => '',
				'lastname'       => '',
				'company'        => '',
				'address_1'      => '',
				'address_2'      => '',
				'postcode'       => $this->request->post['postcode'],
				'city'           => '',
				'zone_id'        => $this->request->post['zone_id'],

        'district_id'    => $this->request->post['district_id'],//frd 3
        'subdistrict_id' => $this->request->post['subdistrict_id'],//frd 3
      
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $this->request->post['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);


        //frd 4
        $this->load->model('localisation/districtpro');
  			$district = $this->model_localisation_districtpro->getDistrict($this->session->data['shipping_address']['district_id']);
  			if (isset($district['rajaongkir']['results']['city_name'])){
  				$this->session->data['shipping_address']['district'] = $district['rajaongkir']['results']['city_name'] . ' - ' . $district['rajaongkir']['results']['type'];
  			} else {
  				$this->session->data['shipping_address']['district'] = '';
  			}

  			$this->load->model('localisation/subdistrictpro');
  			$subdistrict = $this->model_localisation_subdistrictpro->getSubdistrict($this->session->data['shipping_address']['subdistrict_id']);
  			if (isset($subdistrict['rajaongkir']['results']['subdistrict_name'])){
  				$this->session->data['shipping_address']['subdistrict'] = $subdistrict['rajaongkir']['results']['subdistrict_name'];
  			} else {
  				$this->session->data['shipping_address']['subdistrict'] = '';
  			}

  			//---

      
			$quote_data = array();

			$this->load->model('extension/extension');

			$results = $this->model_extension_extension->getExtensions('shipping');

        //frd 5
        if ($this->config->get('shindopro_status')==true) {
  				$results[] = array('code'=>'igspospro');
  				$results[] = array('code'=>'igstikipro');
  				$results[] = array('code'=>'igsjnepro');
  				$results[] = array('code'=>'igswahanapro');
  				$results[] = array('code'=>'igsjntpro');
  			}
  			foreach ($results as $key => $result) {
  				if ($result['code']=='shindopro') {
  					unset ($results[$key]);
  				}
  			}
  			//----

      

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('extension/shipping/' . $result['code']);

					$quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

					if ($quote) {
						$quote_data[$result['code']] = array(
							'title'      => $quote['title'],
							'quote'      => $quote['quote'],
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
						);
					}
				}
			}

			$sort_order = array();

			foreach ($quote_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $quote_data);

			$this->session->data['shipping_methods'] = $quote_data;

			if ($this->session->data['shipping_methods']) {
				$json['shipping_method'] = $this->session->data['shipping_methods'];
			} else {
				$json['error']['warning'] = sprintf($this->language->get('error_no_shipping'), $this->url->link('information/contact'));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function shipping() {
		$this->load->language('extension/total/shipping');

		$json = array();

		if (!empty($this->request->post['shipping_method'])) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
				$json['warning'] = $this->language->get('error_shipping');
			}
		} else {
			$json['warning'] = $this->language->get('error_shipping');
		}

		if (!$json) {
			$shipping = explode('.', $this->request->post['shipping_method']);

			$this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];

			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('checkout/cart');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


        //frd 7
        public function zone() {
      		$json = array();

      		$this->load->model('localisation/zone');
      		$zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);
      		$json = array();
      		if ($zone_info) {
      			$this->load->model('localisation/districtpro');
      			if (!empty($zone_info['raoprop_id'])) {
      				$json = array(
      					'zone_id'       => $zone_info['country_id'],
      					'name'          => $zone_info['name'],
      					'code'        	=> $zone_info['code'],
      					'raoprop_id'    => $zone_info['raoprop_id'],
      					'raoprop'       => $this->model_localisation_districtpro->getDistricts($zone_info['raoprop_id']),
      				);

      			}
      		}

      		$this->response->addHeader('Content-Type: application/json');
      		$this->response->setOutput(json_encode($json));
      	}

      	public function district() {
      		$json = array();
      		if (trim($this->request->get['district_id']) <> '' ) {
      			$this->load->model('localisation/districtpro');
      			$district = $this->model_localisation_districtpro->getDistrict($this->request->get['district_id']);
      			$json = array();
      			if (!empty($district['rajaongkir']['results'])) {
      				$this->load->model('localisation/subdistrictpro');
      					$json = array(
      						'city_id'      => $district['rajaongkir']['results']['city_id'],
      						'province_id'  => $district['rajaongkir']['results']['province_id'],
      						'province'     => $district['rajaongkir']['results']['province'],
      						'type'         => $district['rajaongkir']['results']['type'],
      						'city_name'    => $district['rajaongkir']['results']['city_name'],
      						'postal_code'  => $district['rajaongkir']['results']['postal_code'],
      						'subdistricts' => $this->model_localisation_subdistrictpro->getSubdistricts($district['rajaongkir']['results']['city_id']),
      					);

      			}
      		}
      		$this->response->addHeader('Content-Type: application/json');
      		$this->response->setOutput(json_encode($json));
      	}
      
	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}