<?php
/**
 * View.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 Muhammet ŞAFAK
 * @license    ./LICENSE  MIT
 * @version    1.0
 * @link       https://www.muhammetsafak.com.tr
 */

namespace InitPHP\Views\Interfaces;

interface ViewAdapterInterface
{

    /**
     * @param string ...$views
     * @return $this
     */
    public function setView(string ...$views): self;

    /**
     * @param array|object $data
     * @return $this
     */
    public function setData(array|object $data): self;


    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getData(?string $key, mixed $default = null): mixed;

    /**
     * @return string
     */
    public function render(): string;

}
