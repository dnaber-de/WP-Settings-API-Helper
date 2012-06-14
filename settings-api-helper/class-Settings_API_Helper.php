<?php
/**
 * helper for the settings API of Wordpress
 *
 * @version 0.1
 * @package Wordpress
 * @subpackage WP Settings API Helper
 * @author David Naber <kontakt@dnaber.de>
 */

if ( ! class_exists( 'Settings_API_Helper' ) ) :

class Settings_API_Helper {

	/**
	 * section name
	 *
	 * @var string
	 */
	protected $section = '';

	/**
	 * description of this section
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * general error message
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * option key
	 *
	 * @var string
	 */
	protected $option_key = '';

	/**
	 * page
	 *
	 * @var string
	 */
	protected $page = '';

	/**
	 * fields
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * constructor
	 *
	 * @param string $option_key
	 * @param string $page
	 * @param string $lable (The heading text)
	 * @param string $description (Optional) (Small description of the section, may contain HTML)
	 * @param string $error_msg (Optional) (A general error message, unused at the moment)
	 * @param string $section (Optional)
	 */
	public function __construct( $option_key, $page, $label, $description = '', $error_msg = '', $section = '' ) {

		$this->option_key = $option_key;
		$this->page = $page;
		$this->description = $description;
		$this->error_message = $error_msg;

		if ( empty( $section ) )
			$section = $option_key . '_section';
		$this->section = $section;

		register_setting(
			$this->page,
			$this->option_key,
			array( $this, 'validate' )
		);

		add_settings_section(
			$this->section,
			$label,
			array( $this, 'description' ),
			$this->page
		);

	}

	/**
	 * print description
	 *
	 * @return void
	 */
	public function description() {

		if ( empty( $this->description ) )
			return;
		?>
		<div class="inside">
			<?php echo wpautop( $this->description ); ?>
		</div>
		<?php
	}

	/**
	 * validate the input
	 *
	 * @param array $request
	 * @return array (sanitized input)
	 */
	public function validate( $request ) {

		foreach ( $this->fields as $field ) {
			$field->validate( &$request );
			if ( $field->is_invalid() ) {
				$this->invalid_fields[] = $field;
				add_settings_error(
					$this->section,
					$field->get_id(),
					$field->get_error_message()
				);
			}
		}

		return $request;
	}


	/**
	 * add field
	 *
	 * @param string $name
	 * @param string $type
	 * @param array $options (Optional)
	 * @return void
	 */
	public function add_field( $name, $label, $type, $options ) {

		$new_field
			= new Settings_API_Field(
				$name,
				$label,
				$type,
				$options,
				$this->section,
				$this->page,
				$this->option_key
		);
		$this->fields[] = $new_field;

	}

	/**
	 * add a text field
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $options (Optional)
	 * @return void
	 */
	public function add_text( $name, $label, $options = array() ) {

		$this->add_field( $name, $label, 'text', $options );
	}

	/**
	 * add a checkbox
	 *
	 * @param string $name
	 * @param string $label (Optional)
	 * @param array $options
	 * @return void
	 */
	public function add_checkbox( $name, $label, $options = array() ) {

		$this->add_field( $name, $label, 'checkbox', $options );
	}

	/**
	 * add a select-element
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $options (Optional)
	 * @return void
	 */
	public function add_select( $name, $label, $options = array() ) {

		$defaults = array(
			'options' => array( '' )
		);
		$options = wp_parse_args( $options, $defaults );
		$this->add_field( $name, $label, 'select', $options );
	}

	/**
	 * add a textarea
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $options (Optional)
	 * @return void
	 */
	public function add_textarea( $name, $label, $options = array() ) {

		$this->add_field( $name, $label, 'textarea', $options );
	}

	/**
	 * add a bunsh of radio-buttons
	 *
	 * @param string $name
	 * @param string $label
	 * @param array $options (Optional)
	 * @return void
	 */
	public function add_radio( $name, $label, $options = array() ) {

		$this->add_field( $name, $label, 'radio', $options );
	}

}

endif; # class exists
