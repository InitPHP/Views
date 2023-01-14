<?php
/**
 * AdapterAbstract.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

namespace InitPHP\Views\Adapters;

use InitPHP\Views\Exceptions\ViewInvalidArgumentException;

use function array_merge;
use function is_object;
use function is_array;
use function get_object_vars;
use function array_key_exists;

abstract class AdapterAbstract implements \InitPHP\Views\Interfaces\ViewAdapterInterface
{

    private array $configurations = [];

    protected array $views = [];

    protected array $data = [];

    public function __toString()
    {
        return $this->render();
    }

    /**
     * @inheritDoc
     */
    public function setView(string ...$views): self
    {
        $this->views = array_merge($this->views, $views);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setData(array|object $data): self
    {
        if(is_object($data)){
            $data = get_object_vars($data);
        }
        if(!is_array($data)){
            throw new ViewInvalidArgumentException('$data can be just an array or an object.');
        }
        if(!empty($data)){
            $this->data = array_merge($this->data, $data);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData(?string $key, mixed $default = null): mixed
    {
        if (null === $key) {
            return $this->data;
        }

        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    /**
     * @inheritDoc
     */
    abstract public function render(): string;

    protected function setConfigurations(array $configurations = []): self
    {
        $this->configurations = $configurations;

        return $this;
    }

    protected function getConfigurations(): array
    {
        return $this->configurations;
    }

    protected function getConfiguration(string $key, $default = null)
    {
        return array_key_exists($key, $this->configurations) ? $this->configurations[$key] : $default;
    }

}
