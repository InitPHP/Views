<?php

/**
 * ViewAdapterInterface.php
 *
 * This file is part of InitPHP Views.
 *
 * @author     Muhammet ŞAFAK <info@muhammetsafak.com.tr>
 * @copyright  Copyright © 2022 InitPHP
 * @license    https://github.com/InitPHP/Views/blob/main/LICENSE  MIT
 * @link       https://github.com/InitPHP/Views
 */

declare(strict_types=1);

namespace InitPHP\Views\Interfaces;

/**
 * Contract every view engine adapter must fulfil.
 *
 * The workflow is always the same: queue one or more views with
 * {@see self::setView()}, optionally attach data with {@see self::setData()},
 * then produce the output with {@see self::render()}.
 */
interface ViewAdapterInterface
{
    /**
     * Queue one or more views to render, in the order given.
     *
     * Names are appended to any views queued by earlier calls.
     *
     * @param string ...$views One or more view identifiers understood by the adapter.
     * @return static
     */
    public function setView(string ...$views): static;

    /**
     * Merge data that will be exposed to the queued views.
     *
     * An object is converted to an associative array of its accessible
     * properties. Repeated keys overwrite previously set values.
     *
     * @param array<string, mixed>|object $data
     * @return static
     */
    public function setData(array|object $data): static;

    /**
     * Read a single value from the merged data, or the whole data set.
     *
     * @param string|null $key     Key to read, or null to return every value.
     * @param mixed        $default Returned when $key is not present.
     * @return mixed The value for $key, the full data array when $key is null,
     *               or $default when the key is missing.
     */
    public function getData(?string $key = null, mixed $default = null): mixed;

    /**
     * Render every queued view and return the concatenated output.
     *
     * @return string
     */
    public function render(): string;
}
