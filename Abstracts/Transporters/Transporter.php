<?php

namespace Apiato\Core\Abstracts\Transporters;

use Apiato\Core\Abstracts\Requests\Request;
use Apiato\Core\Traits\SanitizerTrait;
use Dto\Dto;
use Dto\RegulatorInterface;
use Illuminate\Support\Str;

/**
 * Class Transporter
 *
 * @author  Johannes Schobel <johannes.schobel@googlemail.com>
 * @author  Mahmoud Zalt  <mahmoud@zalt.me>
 */
abstract class Transporter extends Dto
{

    use SanitizerTrait;

    /**
     * Overrides the Dto constructor to extend it for supporting Requests objects as $input.
     *
     * Transporter constructor.
     *
     * @param null                         $input
     * @param null                         $schema
     * @param \Dto\RegulatorInterface|null $regulator
     */
    public function __construct($input = null, $schema = null, RegulatorInterface $regulator = null)
    {
        // if the transporter got a Request object, get the content and headers
        // and pass them as array input to the Dto constructor..
        if ($input instanceof Request) {
            $content = $input->toArray();
            $heders = ['_headers' => $input->headers->all()];

            $input = array_merge($heders, $content);
        }

        parent::__construct($input, $schema, $regulator);
    }

    /**
     * This method mimics the $request->input() method but works on the "decoded" values
     *
     * @param null $key
     * @param null $default
     *
     * @return  mixed
     */
    public function getInputByKey($key = null, $default = null)
    {
        return array_get($this->toArray(), $key, $default);
    }

    /**
     * Override the __GET function in order to directly return the "raw value" (e.g., the containing string) of a field
     *
     * @param $name
     *
     * @return  mixed|null
     */
    public function __get($name)
    {
        // first, check if the field exists, otherwise return null (like the default laravel behavior)
        if (!$this->exists($name)) {
            return null;
        }

        $field = parent::__get($name);
        $type = $field->getStorageType();

        $value = call_user_func([$field, 'to' . Str::ucfirst($type)]);

        return $value;
    }

}