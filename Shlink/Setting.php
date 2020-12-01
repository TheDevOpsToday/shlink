<?php
namespace Shlink;

class Setting
{
  
  const parent_page = 'options-general.php';
  const page_title  = 'Shlink Settings';
  const menu_title  = 'Shlink';
  const capability  = 'manage_options';
  const menu_slug   = 'shlink-setting';
  const key_name = 'shlink';
  const api_timeout = 30;
  const api_min_version = 1;
  const api_max_version = 2;
  const api_config_title = 'API Configuration';
  const api_config_desc  = 'Enter your shlink API configuration detail below. More detail <a href="https://shlink.io/documentation/api-docs/">here</a>.';

  public static function get_setting( $key )
  {
    $settings = get_option( self::key_name );
    if( empty( $settings ) ) return null;
    else return isset( $settings[$key] ) ? $settings[$key] : null;
  }

  public static function action_settings_link( $links )
  {
    $links[] = '<a href="' .
      admin_url( sprintf('%s?page=%s', self::parent_page, self::menu_slug ) ) .
      '">' . __('Settings') . '</a>';
    return $links;
  }

  final public static function get_settings()
  {
    $default = array(
      'api_host' => '',
      'api_key'  => '',
      'api_version' => self::api_max_version,
      'api_timeout' => self::api_timeout,
    );
    $settings = get_option( self::key_name );
    if( empty( $settings ) ) $settings = (object) $default;
    else $settings = (object) array_merge( $default, $settings );
    return $settings;
  }

  public static function admin_menu()
  {
    add_submenu_page( self::parent_page, self::page_title, self::menu_title, self::capability, self::menu_slug, array( __CLASS__, 'settings_page' ) );
  }

  public static function settings_page()
  {
    ?>
    <div class="wrap">
      <h1><?php echo self::page_title; ?></h1>
      <form action='options.php' method='post'>
        <?php
          settings_fields( self::key_name );
          do_settings_sections( self::key_name );
          submit_button();
        ?>
      </form>
    </div>
    <?php
  }

  public static function admin_init()
  {
    $settings = self::get_settings();
    $prefix = self::key_name.'_settings';
    $section_id = $prefix.'_section';
    register_setting( self::key_name, self::key_name );
    add_settings_section(
      $section_id,
      self::api_config_title,
      array( __CLASS__, 'api_config_section' ),
      self::key_name
    );
    add_settings_field(
      $prefix.'_host',
      'API Host',
      array( __CLASS__, 'api_config_input' ),
      self::key_name,
      $section_id,
      array(
        'name'  => sprintf( '%s[api_host]', self::key_name ),
        'id'    => sprintf( '%s-api-host', self::key_name ),
        'type'  => 'url',
        'value' => $settings->api_host,
        'required' => 'required',
        'class' => 'regular-text',
      )
    );
    add_settings_field(
      $prefix.'_key',
      'API Key',
      array( __CLASS__, 'api_config_input' ),
      self::key_name,
      $section_id,
      array(
        'name'  => sprintf( '%s[api_key]', self::key_name ),
        'id'    => sprintf( '%s-api-key', self::key_name ),
        'type'  => 'password',
        'value' => $settings->api_key,
        'required' => 'required',
        'class' => 'regular-text',
      )
    );
    add_settings_field(
      $prefix.'_version',
      'API Version',
      array( __CLASS__, 'api_config_input' ),
      self::key_name,
      $section_id,
      array(
        'name'  => sprintf( '%s[api_version]', self::key_name ),
        'id'    => sprintf( '%s-api-version', self::key_name ),
        'type'  => 'number',
        'value' => $settings->api_version,
        'required' => 'required',
        'class' => 'tiny-text',
        'min'   => self::api_min_version,
        'setp'  => 1,
        'max'   => self::api_max_version,
      )
    );
    add_settings_field(
      $prefix.'_timeout',
      'API Timeout',
      array( __CLASS__, 'api_config_input' ),
      self::key_name,
      $section_id,
      array(
        'name'  => sprintf( '%s[api_timeout]', self::key_name ),
        'id'    => sprintf( '%s-api-timeout', self::key_name ),
        'type'  => 'number',
        'value' => $settings->api_timeout,
        'required' => 'required',
        'class' => 'tiny-text',
        'min'   => self::api_timeout,
        'setp'  => 1,
      )
    );

  }

  public static function api_config_section()
  {
    echo sprintf( '<p>%s</p>', self::api_config_desc );
  }

  public static function api_config_input( $args )
  {
    $atts = '';
    foreach ($args as $key => $value) {
      $atts .= sprintf(' %s="%s"', $key, $value );
    }
    echo sprintf('<input %s>', $atts );
  }
  
}
