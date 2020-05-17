<?php

namespace VCR;

use VCR\LibraryHooks\CurlHook;
use VCR\LibraryHooks\SoapHook;
use VCR\Storage\Recording;
use VCR\Storage\Storage;
use VCR\Util\StreamProcessor;
use VCR\Configuration;
use VCR\Util\HttpClient;
use VCR\CodeTransform\SoapCodeTransform;
use VCR\CodeTransform\CurlCodeTransform;

class VCRFactory
{
    /**
     * @var Configuration
     **/
    protected $config;

    /**
     * @var object[]
     */
    protected $mapping = [];

    /**
     * @var self
     */
    protected static $instance;

    /**
     * Creates a new VCRFactory instance.
     *
     * @param Configuration $config
     */
    protected function __construct(Configuration $config = null)
    {
        $this->config = $config ?? $this->getOrCreate(Configuration::class);
    }

    /**
     * @return Videorecorder
     */
    protected function createVCRVideorecorder(): Videorecorder
    {
        return new Videorecorder(
            $this->config,
            $this->getOrCreate(HttpClient::class),
            $this
        );
    }

    /**
     * Provides an instance of the StreamProcessor.
     *
     * @return StreamProcessor
     */
    protected function createVCRUtilStreamProcessor(): StreamProcessor
    {
        return new StreamProcessor($this->config);
    }

    /**
     * @param string $cassetteName
     *
     * @return Storage<Recording>
     */
    protected function createStorage(string $cassetteName): Storage
    {
        $dsn = $this->config->getCassettePath();
        $class = $this->config->getStorage();

        return new $class($dsn, $cassetteName);
    }

    protected function createVCRLibraryHooksSoapHook(): SoapHook
    {
        return new LibraryHooks\SoapHook(
            $this->getOrCreate(SoapCodeTransform::class),
            $this->getOrCreate(StreamProcessor::class)
        );
    }

    protected function createVCRLibraryHooksCurlHook(): CurlHook
    {
        return new LibraryHooks\CurlHook(
            $this->getOrCreate(CurlCodeTransform::class),
            $this->getOrCreate(StreamProcessor::class)
        );
    }

    /**
     * Returns the same VCRFactory instance on ever call (singleton).
     *
     * @param  Configuration $config (Optional) configuration.
     *
     * @return VCRFactory
     */
    public static function getInstance(Configuration $config = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Returns an instance for specified class name and parameters.
     *
     * @param string $className Class name to get a instance for.
     * @param mixed[] $params Constructor arguments for this class.
     *
     * @return mixed An instance for specified class name and parameters.
     */
    public static function get(string $className, array $params = [])
    {
        return self::getInstance()->getOrCreate($className, $params);
    }

    /**
     * Returns an instance for specified classname and parameters.
     *
     * @param string $className Class name to get a instance for.
     * @param mixed[] $params Constructor arguments for this class.
     *
     * @return mixed
     */
    public function getOrCreate(string $className, array $params = [])
    {
        $key = $className . implode('-', $params);

        if (isset($this->mapping[$key])) {
            return $this->mapping[$key];
        }

        if (method_exists($this, $this->getMethodName($className))) {
            /** @var callable $callback */
            $callback = [$this, $this->getMethodName($className)];
            $instance =  call_user_func_array($callback, $params);
        } else {
            $instance = new $className;
        }

        return $this->mapping[$key] = $instance;
    }

    /**
     *
     * Example:
     *
     *   ClassName: \Tux\Foo\Linus
     *   Returns: createTuxFooLinus
     *
     * @param string $className
     *
     * @return string
     */
    protected function getMethodName(string $className): string
    {
        return 'create' . str_replace('\\', '', $className);
    }
}
