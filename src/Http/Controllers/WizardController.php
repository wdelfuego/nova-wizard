<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 *
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 *
 *    (it boils down to: do what you want as long as you're building and/or
 *     using wizards, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */

namespace Wdelfuego\NovaWizard\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Laravel\Nova\Http\Requests\NovaRequest;


class WizardController extends BaseController
{
    // Must match the hard-coded value in Tool.vue's reload() method
    const API_PATH_PREFIX = '/nova-vendor/wdelfuego/nova-wizard/';

    private $request;
    private $wizards = [];

    public function __construct(NovaRequest $request)
    {
        $this->request = $request;

        // Load data providers, keyed by uri
        foreach(config('nova-wizard', []) as $wizardKey => $wizardConfig)
        {
            // We are assuming these keys to exist since the Nova Tool
            // does all sorts of checks on initiation
            // Not sure if that assumption is completely valid but assuming valid config for now
            $wizard = new ($wizardConfig['class']);
            $wizard->setConfig($wizardConfig);
            $this->wizards[$wizardConfig['uri']] = $wizard;
        }
    }

    protected function getWizardForUri(string $uri)
    {
        if(!isset($this->wizards[$uri]))
        {
            throw new \Exception("Unknown wizard uri: $uri");
        }

        return $this->wizards[$uri];
    }

    protected function toolUri()
    {
        $requestUri = substr($this->request->url(), strlen($this->request->schemeAndHttpHost()));
        // $toolUri = substr($toolUri, 0, strrpos($toolUri, '/'));
        return substr($requestUri, strlen(self::API_PATH_PREFIX));
    }

    public function getWizardData()
    {
        $wizard = $this->getWizardForUri($this->toolUri())->withRequest($this->request);
        return $wizard->wizardViewData();
    }

    public function submitWizard()
    {
        $wizard = $this->getWizardForUri($this->toolUri())->withRequest($this->request);
        return $wizard->submitWizard($this->request);
    }

    public function getFieldDefinition(string $step, NovaRequest $request)
    {
        $forms = config('nova-wizard');
        if (!isset($forms[$step])) {
            return response()->json([
                'message' => 'Step not found'
            ], 404);
        }
        $class = $forms[$step]['class'];
        $instance = new $class();
        $steps = $instance->wizardViewData();
        $field = collect($steps['steps'])
            ->map(
                fn ($step) => collect($step['fields'])
                    ->filter(
                        fn ($field) => $field->attribute === $request->get('field')
                    )
            )
            ->filter(
                fn ($field) => $field->isNotEmpty()
            )
            ->flatten(1)
            ->first();
        if (isset($field)) {
            $field->syncDependsOn($request);
        }
        return response()->json(
            $field
        );
    }

}
