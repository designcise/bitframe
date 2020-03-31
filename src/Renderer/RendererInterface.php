<?php

/**
 * BitFrame Framework (https://www.bitframephp.com)
 *
 * @author    Daniyal Hamid
 * @copyright Copyright (c) 2017-2020 Daniyal Hamid (https://designcise.com)
 * @license   https://bitframephp.com/about/license MIT License
 */

declare(strict_types=1);

namespace BitFrame\Renderer;

/**
 * Interface defining required template capabilities.
 */
interface RendererInterface
{
    /**
     * Add a namespaced template path to rendering engine.
     *
     * @param string $namespace
     * @param string $path
     */
    public function addPath(string $namespace, string $path);

    /**
     * Add default parameters to use with template(s).
     *
     * Use this method to provide default parameters to use when a template is
     * rendered. A parameter may be overridden by providing it when calling
     * `render()`, or by calling this method again with a `null` value.
     *
     * The parameter will be specific to the template name provided (e.g.
     * `namespace::template`). To make the parameter available to any template,
     * pass `null` for the template name.
     *
     * If the default parameter existed previously, subsequent invocations with
     * the same template name and parameter name will overwrite.
     *
     * @param string $templateName Name of template to which the param applies;
     *                             if `null` apply to all templates.
     * @param array $data Key/value data pair.
     */
    public function addDefaults(array $data, ?string $templateName = null);

    /**
     * Render a template, optionally with parameters.
     *
     * Implementations MUST support the `namespace::template` naming convention,
     * and allow omitting the filename extension.
     *
     * @param string $nsPath Namespaced Path (e.g. `namespace::template`)
     * @param array $data
     *
     * @return string
     */
    public function render(string $nsPath, array $data = []): string;
}