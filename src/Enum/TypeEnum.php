<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of TypeEnum
 *
 * @author nazar
 */
abstract class TypeEnum {

    /**
     * @param  string $typeShortName
     * @return string
     */
    public static function getTypeName($typeKey) {
        if (!isset(static::$typeName[$typeKey])) {
            return "Unknown type ($typeKey)";
        }

        return static::$typeName[$typeKey];
    }

    /**
     * @param  string $typeShortName
     * @return string
     */
    public static function getByName($typeShortName) {
        $availableConstList = self::getAvailableConstantList();

        if (isset($availableConstList[$typeShortName])) {
            return $availableConstList[$typeShortName];
        } else {
            throw new \UnexpectedValueException('Unknown constant name');
        }
    }

    /**
     * @return string[]
     */
    public static function getAvailableTypes() {
        return self::getAvailableConstantList();
    }

    /**
     * @param $constantName - value to check
     * @return bool
     */
    public static function isValidName($constantName) {
        $availableConstList = self::getAvailableConstantList();
        return isset($availableConstList[$constantName]);
    }

    /**
     * @param $constantValue - value to check
     * @return bool
     */
    public static function isValidValue($constantValue) {
        $availableConstList = self::getAvailableConstantList();
        return array_search($constantValue, $availableConstList, true) !== false;
    }

    /**
     * returns a array with available constants
     * @return array|null
     */
    private static function getAvailableConstantList() {
        return (new \ReflectionClass(static::class))->getConstants();
    }

    public static function getNameValuePairs() {
        return static::$typeName;
    }

    public static function getChoiceTypeArray() {
        $resultArray = [];
        $availableConstants = self::getAvailableConstantList();
        foreach ($availableConstants as $constant) {
            $resultArray = array_merge($resultArray, [self::getTypeName($constant) => $constant]);
        }
        return $resultArray;
    }
    
    public static function getJsonArray()
    {
        return json_encode(self::getNameValuePairs());
    }

}
