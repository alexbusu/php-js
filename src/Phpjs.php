<?php

namespace Alexbusu;

use ArrayAccess;
use JsonSerializable;
use Throwable;

/**
 * @psalm-type JsonData array<array-key, mixed>|JsonSerializable|scalar|ArrayAccess
 * @psalm-type DebugOption array{'debug': bool}
 * @psalm-type TimeoutOptions array{'timeout': int|float}
 * @psalm-type MaybeNothing array<never, never>
 */
final class Phpjs implements JsonSerializable
{
    private static ?self $response = null;

    public static bool $debug = false;

    /** @var array<int|string, mixed> */
    private array $triggers = [];

    private array $additional = [];

    public const UNDO_MESSAGE_DEFAULT_TIMEOUT = 8000;

    public const MESSAGE_DEFAULT_TIMEOUT = 5000;

    public const TRIGGER_MESSAGE = 'doc.Status';

    public const TRIGGER_REDIRECT = 'winrd';

    public const TRIGGER_RELOAD = 'winreload';

    public const TRIGGER_CONSOLE_ERROR = 'error.console';

    public const TRIGGER_CONSOLE_WARN = 'warn.console';

    public const TRIGGER_CONSOLE_TABLE = 'table.console';

    public const TRIGGER_CONSOLE_INFO = 'info.console';

    public const TRIGGER_CONSOLE_LOG = 'log.console';

    public const TRIGGER_SET_COOKIE = 'set-cookie';

    /**
     * @param DebugOption|MaybeNothing $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['debug'])) {
            self::$debug = $options['debug'];
        }
    }

    /**
     * @param string|Throwable $message
     */
    public function message($message = '', array $additional = []): self
    {
        if ($message instanceof Throwable) {
            $message = self::exceptionMessage($message, $additional);
        } else {
            $message = self::successMessage($message, $additional);
        }

        $this->trigger(self::TRIGGER_MESSAGE, $message);

        return $this;
    }

    /**
     * @param mixed $exception
     *
     * @return ((int|string)[]|bool|int|mixed|null|string)[]
     *
     * @psalm-return array{success: false|mixed, message: mixed|string, type: 'warning'|mixed, msgTimeout: 5000|mixed, closeBtn: mixed|true, cssClass: ''|mixed, exception: array{code: int|string, message: string, trace: string}|mixed|null,...}
     */
    public static function exceptionMessage($exception, array $additional = []): array
    {
        if ($exception instanceof Throwable) {
            $message = $exception->getMessage();
            $exception = self::$debug
                ? [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]
                : null;
        } else {
            $message = print_r($exception, true);
            $exception = null;
        }

        return array_merge([
            'success' => false,
            'message' => $message,
            'type' => 'warning',
            'msgTimeout' => self::MESSAGE_DEFAULT_TIMEOUT,
            'closeBtn' => true,
            'cssClass' => '',
            'exception' => $exception,
        ], $additional);
    }

    /**
     * @return (int|mixed|string|true)[]
     *
     * @psalm-return array{success: mixed|true, message: mixed|string, type: 'success'|mixed, msgTimeout: 5000|mixed, closeBtn: mixed|true, cssClass: ''|mixed,...}
     */
    public static function successMessage(string $message, array $additional = []): array
    {
        return array_merge([
            'success' => true,
            'message' => print_r($message, true),
            'type' => 'success',
            'msgTimeout' => self::MESSAGE_DEFAULT_TIMEOUT,
            'closeBtn' => true,
            'cssClass' => '',
        ], $additional);
    }

    public static function failMessage(string $message = '', array $additional = []): array
    {
        return self::exceptionMessage($message, $additional);
    }

    /**
     * @param DebugOption|MaybeNothing $options
     */
    public static function response(array $options = []): self
    {
        if (!(self::$response instanceof self)) {
            self::$response = new self($options);
        }

        return self::$response;
    }

    /**
     * Prepare the redirect trigger info.
     * In options, you can specify the 'timeout', in seconds, for when the redirect should trigger.
     *
     * @param TimeoutOptions|MaybeNothing $options
     */
    public function redirect(string $url, array $options = []): self
    {
        $defaults = [
            'url' => $url,
            'timeout' => 0, // seconds
        ];
        $options = array_merge($defaults, $options);
        $this->trigger(self::TRIGGER_REDIRECT, $options);

        return $this;
    }

    /**
     * Attach additional data to json response.
     */
    public function attach(array $additional = []): self
    {
        $this->additional = array_merge($this->additional, $additional);

        return $this;
    }

    /**
     * @param array<int|string, JsonSerializable|scalar|array>|scalar $data
     */
    public function consoleWarn($data): self
    {
        $this->trigger(self::TRIGGER_CONSOLE_WARN, $data);

        return $this;
    }

    /**
     * @param array<int|string, JsonSerializable|scalar|array>|scalar $data
     */
    public function consoleTable($data): self
    {
        $this->trigger(self::TRIGGER_CONSOLE_TABLE, $data);

        return $this;
    }

    /**
     * @param array<int|string, JsonSerializable|scalar|array>|scalar $data
     */
    public function consoleError(string $message, $data): self
    {
        $this->trigger(self::TRIGGER_CONSOLE_ERROR, ['message' => $message, 'trace' => $data]);

        return $this;
    }

    /**
     * @param array<int|string, JsonSerializable|scalar|array>|scalar $data
     */
    public function consoleInfo($data): self
    {
        $this->trigger(self::TRIGGER_CONSOLE_INFO, $data);

        return $this;
    }

    /**
     * @param array<int|string, JsonSerializable|scalar|array>|scalar $data
     */
    public function consoleLog($data): self
    {
        $this->trigger(self::TRIGGER_CONSOLE_LOG, $data);

        return $this;
    }

    /**
     * @param string|JsonData $triggerSelectorOrData
     * @param JsonData $triggerData
     */
    public function trigger(string $triggerName, $triggerSelectorOrData, $triggerData = []): self
    {
        if (func_num_args() == 2) {
            $this->triggers[] = [
                'trigger' => $triggerName,
                'selector' => null,
                'data' => $triggerSelectorOrData,
            ];
        } else {
            $this->triggers[] = [
                'trigger' => $triggerName,
                'selector' => $triggerSelectorOrData,
                'data' => is_scalar($triggerData) ? $triggerData : (array)$triggerData,
            ];
        }

        return $this;
    }

    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array{_: array<int|string, mixed>,...}
     */
    public function toArray(): array
    {
        return array_merge($this->additional, [
            '_' => $this->triggers,
        ]);
    }

    /**
     * @return int|string
     */
    public function toHtml(bool $returnOutput = false)
    {
        $out = '<div data-trigger style="display:none">' . $this->jsonEncode($this->triggers) . '</div>';
        $this->triggers = [];
        if ($returnOutput) {
            return $out;
        }

        return print $out;
    }

    /**
     * @param array<int|string, mixed> $mixed
     */
    private function jsonEncode(array $mixed): string
    {
        return (string)json_encode($mixed, JSON_HEX_TAG);
    }

    /**
     * @return (array|mixed)[]
     *
     * @psalm-return array{_: array<int|string, mixed>,...}
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
