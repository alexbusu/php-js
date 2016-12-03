<?php

namespace Phpjs;

class Phpjs implements \JsonSerializable
{
    /** @var self */
    private static $response;
    /** @var bool */
    public static $debug = false;

    /** @var array */
    private $triggers = [];
    /** @var array */
    private $additionals = [];

    const UNDO_MESSAGE_DEFAULT_TIMEOUT = 8000;
    const MESSAGE_DEFAULT_TIMEOUT = 5000;

    const TRIGGER_MESSAGE = 'doc.Status';
    const TRIGGER_REDIRECT = 'winrd'; // window.redirect
    const TRIGGER_RELOAD = 'winreload'; // window.redirect
    const TRIGGER_CONSOLE_ERROR = 'error.console';
    const TRIGGER_CONSOLE_WARN = 'warn.console';
    const TRIGGER_CONSOLE_TABLE = 'table.console';
    const TRIGGER_CONSOLE_INFO = 'info.console';
    const TRIGGER_CONSOLE_LOG = 'log.console';
    const TRIGGER_SET_COOKIE = 'set-cookie';

    public function __construct( array $options = [] )
    {
        isset( $options[ 'debug' ] ) && ( self::$debug = (bool) $options[ 'debug' ] );
    }

    /**
     * @param string|\Exception $message
     * @param array                   $additional
     * @return $this
     */
    public function message( $message = '' , array $additional = [] )
    {
        if( $message instanceof \Exception ){
            $message = self::exceptionMessage( $message , $additional );
        } elseif( is_string( $message ) ) {
            $message = self::successMessage( $message , $additional );
        } else {
            return $this;
        }
        $this->trigger( self::TRIGGER_MESSAGE , $message );
        return $this;
    }

    /**
     * @param mixed $exception
     * @param array $additional
     * @return array
     */
    public static function exceptionMessage( $exception , array $additional = [] ){
        if ( $exception instanceof \Exception ) {
            /** @var \Exception $exception */
            $message = $exception->getMessage();
            $exception = self::$debug
                ? [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]
                : null;
        } else {
            $message = print_r( $exception, true );
            $exception = null;
        }
        return array_merge( [
            'success' => false ,
            'message' => $message ,
            'type' => 'warning' ,
            'msgTimeout' => self::MESSAGE_DEFAULT_TIMEOUT ,
            'closeBtn' => true ,
            'cssClass' => '',
            'exception' => $exception,
        ] , $additional );
    }

    /**
     * @param string $message
     * @param array  $additional
     * @return array
     */
    public static function successMessage( $message = '' , array $additional = [] ){
        return array_merge( [
            'success' => true,
            'message' => print_r( $message , true ),
            'type' => 'success',
            'msgTimeout' => self::MESSAGE_DEFAULT_TIMEOUT ,
            'closeBtn' => true ,
            'cssClass' => ''
        ] , $additional );
    }

    /**
     * @param string $message
     * @param array  $additional
     * @return array
     */
    public static function failMessage( $message = '' , array $additional = [] ){
        return self::exceptionMessage( $message , $additional );
    }

    public static function response( array $options = [] ){
        if( !( self::$response instanceof self ) ) self::$response = new self( $options );
        return self::$response;
    }

    /**
     * Prepare the redirect trigger info
     *
     * In options you can specify the 'timeout', in secons, for when the redirect should trigger
     *
     * @param string $url
     * @param array $options
     * @return $this
     */
    public function redirect($url , array $options = [] ){
        $defaults = [
            'url' => $url,
            'timeout' => 0, // seconds
        ];
        $options = array_merge( $defaults , $options );
        $this->trigger( self::TRIGGER_REDIRECT , $options );
        return $this;
    }

    /**
     * Attach additional data to json respone
     * @param array $additionals
     * @return self
     */
    public function attach( array $additionals = [] ){
        $this->additionals = array_merge( $this->additionals , $additionals );
        return $this;
    }

    public function consoleWarn( $data ){
        $this->trigger( self::TRIGGER_CONSOLE_WARN , $data );
        return $this;
    }

    public function consoleTable( $data ){
        $this->trigger( self::TRIGGER_CONSOLE_TABLE , $data );
        return $this;
    }

    public function consoleError( $message , $data ){
        $this->trigger( self::TRIGGER_CONSOLE_ERROR , [ 'message' => $message , 'trace' => $data ] );
        return $this;
    }

    public function consoleInfo( $data ){
        $this->trigger( self::TRIGGER_CONSOLE_INFO , $data );
        return $this;
    }

    public function consoleLog( $data ){
        $this->trigger( self::TRIGGER_CONSOLE_LOG , $data );
        return $this;
    }

    public function trigger( $triggerName , $triggerDataOrSelector , array $triggerData = [] )
    {
        if( func_num_args() == 2 ){
            $this->triggers[] = [
                'trigger' => $triggerName ,
                'selector' => null ,
                'data' => $triggerDataOrSelector ,
            ];
        } else {
            $this->triggers[] = [
                'trigger' => $triggerName ,
                'selector' => $triggerDataOrSelector ,
                'data' => $triggerData ,
            ];
        }
        return $this;
    }

    public function toArray()
    {
        return array_merge( $this->additionals , [
            '_' => $this->triggers ,
        ] );
    }

    /**
     * @param bool|false $returnOutput
     * @return int|string
     */
    public function toHtml( $returnOutput = false )
    {
        $out = '<div data-trigger style="display:none">' . self::json_encode( $this->triggers ) . '</div>';
        json_last_error();
        json_last_error_msg();
        $this->triggers = array();
        if( $returnOutput ) return $out;
        else return print $out;
    }

    private static function json_encode($mixed , $options = null){
        if ( TRUE === version_compare( PHP_VERSION , '5.3.0' , '>=' ) ){
            is_null( $options ) && $options = JSON_HEX_TAG; // @link http://www.php.net/manual/en/json.constants.php#constant.json-hex-tag
            return json_encode( $mixed , $options );
        } else {
            return json_encode( $mixed );
        }
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }
}