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
interface TemplateInterface
{
    /** @var string Value indicating all templates; used with `addDefaultParam()`. */
    public const TEMPLATE_ALL = '*';

    /**
     * Render a template, optionally with parameters.
     *
     * Implementations MUST support the `namespace::template` naming convention,
     * and allow omitting the filename extension.
     *
     * @param string $templateName
     * @param array $data
     *
     * @return string
     */
    public function render(string $templateName, array $data = []): string;

    /**
     * Add a default parameter to use with a template.
     *
     * Use this method to provide a default parameter to use when a template is
     * rendered. The parameter may be overridden by providing it when calling
     * `render()`, or by calling this method again with a null value.
     *
     * The parameter will be specific to the template name provided. To make
     * the parameter available to any template, pass the TEMPLATE_ALL constant
     * for the template name.
     *
     * If the default parameter existed previously, subsequent invocations with
     * the same template name and parameter name will overwrite.
     *
     * @param string $templateName Name of template to which the param applies;
     *                             use TEMPLATE_ALL to apply to all templates.
     * @param array $params Key/value data pair.
     */
    public function addDefaultParam(string $templateName, $params);

    /**
     * Add a template path to the engine, with optional namespace the templates in
     * that path provide.
     *
     * @param string $path
     * @param null|string $namespace
     */
    public function addPath(string $path, ?string $namespace = null);

    /**
     * Retrieve configured paths from the engine.
     *
     * @return TemplatePath[]
     */
    public function getPaths(): array;
}