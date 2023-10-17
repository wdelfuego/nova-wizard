<?php

namespace Wdelfuego\NovaWizard;

use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
    
abstract class AbstractWizard
{
    private $config = [];
    protected $request = null;
    
    abstract public function wizardViewData() : array;    
    abstract public function onSubmit($formData, &$context) : bool;
    abstract public function successViewData($context) : array;
    
    public function withRequest(Request $request) : self
    {
        $this->request = $request;
        return $this;
    }
    
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
    
    public function submitWizard()
    {
        $errors = [];
        $isValid = $this->validateFormData($this->request, $errors);
        if(!$isValid)
        {
            return response()->json(['errors' => $errors], 422);
        }
        else
        {
            $context = [];
            if($this->onSubmit($this->request->all(), $context))
            {
                $out = $this->successViewData($context);
                $out['success'] = true;
                return $out;
            }
            
            return $this->wizardViewData();
        }
    }
    
    protected function fields() : array
    {
        $out = [];
        foreach($this->wizardViewData()['steps'] as $step)
        {
            $out = array_merge($out, $step['fields']);
        }
        return $out;
    }
    
    public function validateFormData(Request $request, array &$errors) : bool
    {
        $fields = $this->fields();

        $rules = [];
        foreach ($fields as $field) {
            if (!empty($field->rules)) {
                $rules[$field->attribute] = $field->rules;
            }
        }
        
        $validator = Validator::make($request->all(), $rules);
        $isValid = !$validator->fails();

        if (!$isValid) {
            $errors = $validator->errors();
        }
        
        return $isValid;
    }
}
