<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Interfaces;
/**
 *
 * @author NMammedov
 */
interface ReportableInterface {
    //put your code here
    /**
     * Checks if the object is tagged with the given tag.
     *
     * @return array
     */
    public function isTagged(string $tag): bool;
}
