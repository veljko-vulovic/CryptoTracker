<?php

require(WP_PLUGIN_DIR . '/CryptoTracker/includes/Api.php');


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

		$apiWrapper = new CoinMarketCapWrapper();
		$ids = $apiWrapper->fetchIds();

		return $ids;
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


		$options = $this->getCryptoMapList();

		$repeater = new \Elementor\Repeater();

		$repeater->add_control(
			'crypto_id',
			[
				'label' => __('Chose crypto', 'crypto-tracker'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '1',
				'options' => $options
			]
		);
		$this->add_control(
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
		// $crypto_list_ids = implode(',', $crypto_list_ids);
		$apiWrapper = new CoinMarketCapWrapper();
		$response = $apiWrapper->fetchLatesQuotes($crypto_list_ids);

		$list = [];


		// var_dump($response);
		// die();
		foreach ($response['data'] as $crypto) {
			$currency = $crypto['quote']['USD'];

			$id = $crypto['id'];
			$name = $crypto['name'];
			$symbol = $crypto['symbol'];
			$price = $currency['price'];
			$price = number_format_i18n($price, 2);
			$change24h = $currency['percent_change_24h'];
			$change7d = $currency['percent_change_7d'];


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
		$tracked_crypto = $settings['crypto_list'];
		$ids = $this->getCryptoIds($tracked_crypto);
		// $newRepeterList = [];
		// foreach ($tracked_crypto as $crypto) {
		// 	$newRepeterList[$crypto['crypto_id']] =
		// 		[
		// 			// 'show_7d_change' => $crypto['show_7d_change']
		// 		];
		// }
		// $currentPrices = $this->getCurrentPrices(array_keys($newRepeterList));
		$currentPrices = $this->getCurrentPrices($ids);


?>
		<div class="container">
			<div class="row">

				<?php foreach ($currentPrices as $key => $currentPrice) : ?>
					<div class="col-md-4 col-sm-6">
						<div class="crypto-card">
							<div class="crypto-name"><?php echo $currentPrice['name'] ?> (<?php echo $currentPrice['symbol'] ?>)</div>
							<div class="crypto-price">$<?php echo $currentPrice['price'] ?></div>
							<div class="crypto-change">
								<!-- <span style="color:white">24h </span> -->
								<span class="<?php echo $currentPrice['change24h'] > 0 ? 'increase' : 'decrease' ?>"><?php echo $currentPrice['change24h'] ?>%</span>
								<br>
								<?php if ($settings['show_7d_change'] == 'yes') :  ?>
									<!-- <span style="color:white">7d </span> -->
									<span class="<?php echo $currentPrice['change7d'] > 0 ? 'increase' : 'decrease' ?>"><?php echo $currentPrice['change7d'] ?>%</span>
								<?php endif ?>
							</div>
						</div>
					</div>
				<?php endforeach ?>
			</div>
		</div>

<?php }
}
