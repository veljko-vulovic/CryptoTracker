<?php

/**
 * Elementor Crypto Tracker Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Crypto_Tracker_Widget extends \Elementor\Widget_Base
{

	/**
	 * Get widget name.
	 *
	 * Retrieve Crypto Tracker widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name()
	{
		return 'Crypto Tracker';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Crypto Tracker widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title()
	{
		return __('Crypto Tracker', 'crypto-tracker');
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Crypto Tracker widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon()
	{
		return 'eicon-code';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Crypto Tracker widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories()
	{
		return ['Crypto Tracker'];
	}


	protected function getCryptoMapList()
	{
		$options = get_option('crypto_tracker_settings');
		$api_key = $options['crypto_tracker_api_key'];
		$url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/map';
		$parameters = [
			'start' => '1',
			'sort' => 'cmc_rank',
			'limit' => '100',
			// 'convert' => 'USD'
		];

		$headers = [
			'Accepts: application/json',
			'X-CMC_PRO_API_KEY: ' . $api_key
		];
		$qs = http_build_query($parameters); // query string encode the parameters
		$request = "{$url}?{$qs}"; // create the request URL


		$curl = curl_init(); // Get cURL resource
		// Set cURL options
		curl_setopt_array($curl, array(
			CURLOPT_URL => $request,            // set the request URL
			CURLOPT_HTTPHEADER => $headers,     // set the headers 
			CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
		));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);


		$response = curl_exec($curl); // Send the request, save the response
		// print_r(json_decode($response)); // print json decoded response
		curl_close($curl); // Close request
		$deresponse = json_decode($response);

		$list = [];

		foreach ($deresponse->data as $crypto) {
			$list[$crypto->id] = $crypto->name;
		}

		return $list;
	}

	/**
	 * Register Crypto Tracker widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls()
	{
		$this->start_controls_section(
			'content_section',
			[
				'label' => __('Content', 'crypto-tracker'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);


		// $cryptoList = $this->getCryptoMapList();
		$cryptoList = [
			'1' => 'BTC',
			'1027' => 'ETH',

		];




		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'crypto_id',
			[
				'label' => __('Chose crypto', 'crypto-tracker'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '1',
				'options' => $cryptoList
			]
		);
		$repeater->add_control(
			'show_7d_change',
			[
				'label' => esc_html__('Show 7D change', 'crypto-tracker'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'crypto-tracker'),
				'label_off' => esc_html__('Hide', 'crypto-tracker'),
				'return_value' => 'yes',
				'default' => 'no',
			]
		);


		$this->add_control(
			'crypto_list',
			[
				'label' => esc_html__('Crypto List', 'crypto-tracker'),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
			]
		);

		$this->end_controls_section();
	}


	protected function getCurrentPrices($crypto_list_ids)
	{
		$options = get_option('crypto_tracker_settings');
		$api_key = $options['crypto_tracker_api_key'];
		$url = 'https://pro-api.coinmarketcap.com/v2/cryptocurrency/quotes/latest';
		$parameters = [
			'id' => $crypto_list_ids
		];

		$headers = [
			'Accepts: application/json',
			'X-CMC_PRO_API_KEY: ' . $api_key
		];
		$qs = http_build_query($parameters); // query string encode the parameters
		$request = "{$url}?{$qs}"; // create the request URL


		$curl = curl_init(); // Get cURL resource
		// Set cURL options
		curl_setopt_array($curl, array(
			CURLOPT_URL => $request,            // set the request URL
			CURLOPT_HTTPHEADER => $headers,     // set the headers 
			CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
		));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($curl); // Send the request, save the response
		// print_r(json_decode($response)); // print json decoded response
		curl_close($curl); // Close request
		$deresponse = json_decode($response);



		$list = [];

		foreach ($deresponse->data as $crypto) {
			$currency = $crypto->quote->USD;

			$id = $crypto->id;
			$name = $crypto->name;
			$symbol = $crypto->symbol;
			$price = $currency->price;
			$price = number_format_i18n($price, 2);
			$change24h = $currency->percent_change_24h;
			$change7d = $currency->percent_change_7d;


			$list[$id] = [
				'name' => $name,
				'symbol' => $symbol,
				'price' => $price,
				'change24h' => $change24h,
				'change7d' => $change7d,
			];
		}

		return $list;


		// return $deresponse;
	}


	protected function getCryptoIds($crypto_list)
	{

		$crypto_list_ids = [];
		foreach ($crypto_list as $crypto) {
			$crypto_list_ids[] = $crypto['crypto_id'];
		}
		$crypto_list_ids = implode(',', $crypto_list_ids);


		return $crypto_list_ids;
	}


	/**
	 * Render Crypto Tracker widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render()
	{

		$settings = $this->get_settings_for_display();
		$crypto_list = $settings['crypto_list'];
		$crypto_list_ids = $this->getCryptoIds($crypto_list);
		$currentPrices = $this->getCurrentPrices($crypto_list_ids);


?>
		<div class="container">
			<div class="row">

				<?php foreach ($currentPrices as $currentPrice) : ?>
					<div class="col-md-4 col-sm-6">
						<div class="crypto-card">
							<div class="crypto-name"><?php echo $currentPrice['name'] ?> (<?php echo $currentPrice['symbol'] ?>)</div>
							<div class="crypto-price">$<?php echo $currentPrice['price'] ?></div>
							<div class="crypto-change">
								<span class="<?php echo $currentPrice['change24h'] > 0 ? 'increase' : 'decrease' ?>"><?php echo $currentPrice['change24h'] ?>%</span>
								<br>
								<span class="<?php echo $currentPrice['change7d'] > 0 ? 'increase' : 'decrease' ?>"><?php echo $currentPrice['change7d'] ?>%</span>
							</div>
						</div>
					</div>
				<?php endforeach ?>
			</div>
		</div>

<?php }
}
