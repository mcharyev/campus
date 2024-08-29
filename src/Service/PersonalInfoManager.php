<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\EnrolledStudent;
use App\Entity\AlumnusStudent;
use App\Entity\ExpelledStudent;
use App\Hr\Entity\Employee;

/**
 * Description of FacultyManager
 *
 * @author nazar
 */
class PersonalInfoManager {

//put your code here
    public function viewStudentInfo(EnrolledStudent $student, $info) {
        $result = '';
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b></b></td><td><img src='/build/photos/" . $student->getGroupCode() . "/" . $student->getSystemId() . ".jpg' height='100'></td></tr>";
        $result .= "<tr><td><b>Raýat:</b></td><td>" . $student->getDataField('name') . "</td></tr>";
        $result .= "<tr><td><b>Işleýän ýeri we wezipesi:</b></td><td>" . $student->getDataField('position') . "</td></tr>";
        $result .= "<tr><td><b>Doglan senesi we ýeri:</b></td><td>" . $student->getDataField('dob') . "</td></tr>";
        $result .= "<tr><td><b>Milleti:</b></td><td>" . $student->getDataField('nationality') . "</td></tr>";
        $result .= "<tr><td><b>Bilimi:</b></td><td>" . $student->getDataField('education') . "</td></tr>";
        $result .= "<tr><td><b>Haçan we haýsy okuw mekdebini tamamlady:</b></td><td>" . $student->getDataField('school') . "</td></tr>";
        $result .= "<tr><td><b>Bilimi boýunça hünäri:</b></td><td>" . $student->getDataField('profession') . "</td></tr>";
        $result .= "<tr><td><b>Alymlyk derejesi we ady:</b></td><td>" . $student->getDataField('degree') . "</td></tr>";
        $result .= "<tr><td><b>Haýsy daşary ýurt dillerini bilýär:</b></td><td>" . $student->getDataField('languages') . "</td></tr>";
        $result .= "<tr><td><b>Hökümet sylaglary:</b></td><td>" . $student->getDataField('awards') . "</td></tr>";
        $result .= "<tr><td><b>Daşary ýurtlarda bolmagy:</b></td><td>" . $student->getDataField('trips') . "</td></tr>";
        $result .= "<tr><td><b>Türkmenistanyň Mejlisiniň deputatymy:</b></td><td>" . $student->getDataField('mp') . "</td></tr>";
        $result .= "</table>";

        $result .= "<div align='center'  style='font-family:Times New roman, serif;'><b>IŞLÄN ÝERLERI</b></div><br>";
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        if (gettype($info['positions']) != 'array')
            $positions = json_decode($info['positions']);
        else
            $positions = json_decode(json_encode($info['positions']));
        foreach ($positions as $position) {
            $result .= "<tr><td><b>" . $position->{'period'} . "</b><td>" . $position->{"position"} . "</td></td></tr>";
        }
        $result .= "</table>";

        $result .= "<div align='center' style='font-family:Times New roman, serif;'><b>" . $student->getDataField('name') . "</b><br>";
        $result .= "üç arkasy, maşgala agzalary, özüniň hem-de ýanýoldaşynyň ýakyn dogan-garyndaşlary barada<br><b>maglumat</b></div>";
        $result .= "<table  class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr style='font-weight:bold;'><td>Familiýasy, ady, atasynyň ady</td><td>Garyndaşlyk derejesi</td><td>Doglan ýyly</td><td>Doglan ýeri</td><td>Işleýän (okaýan) ýeri we wezipesi</td><td>Ýaşaýan ýeri, öý salgysy</td><td>Kazyýet jogapkärçiligine çekilenmi?</td></tr>";

        if (gettype($info['relatives']) != 'array')
            $relatives = json_decode($info['relatives']);
        else
            $relatives = json_decode(json_encode($info['relatives']));
        foreach ($relatives as $r) {
            $result .= "<tr valign='top'><td>" . $r->{"name"} . "</td><td>" .
                    $r->{"relation"} . "</td><td>" . $r->{"dob"} . "</td><td>" . $r->{"pob"} . "</td><td>" .
                    $r->{"job"} . "</td><td>" . $r->{"address"} . "</td><td>" . $r->{"penalty"} . "</td></tr>";
        }
        $result .= "</table>";

        $result .= "<table class='table table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b>Öý salgysy:</b></td><td>" . $student->getDataField('address') . "</td></tr>";
        $result .= "<tr><td><b>Häzirki ýaşaýan salgysy:</b></td><td>" . $student->getDataField('address2') . "</td></tr>";
        $result .= "<tr><td><b>Telefon belgisi:</b></td><td>";
        if (strlen($student->getDataField('phone')) > 0) {
            $result .= $student->getDataField('phone') . "(öý)";
        }
        if (strlen($student->getDataField('mobile_phone')) > 0) {
            $result .= " ".$student->getDataField('mobile_phone') . "(ykjam)";
        }
        $result .= "</td></tr>";
        $result .= "</table>";

        return $result;
    }

    public function viewExpelledStudentInfo(ExpelledStudent $student, $info) {
        $result = '';
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b></b></td><td><img src='/build/photos/expelled/" . $student->getSystemId() . ".jpg' height='100'></td></tr>";
        $result .= "<tr><td><b>Raýat:</b></td><td>" . $student->getDataField('name') . "</td></tr>";
        $result .= "<tr><td><b>Işleýän ýeri we wezipesi:</b></td><td>" . $student->getDataField('position') . "</td></tr>";
        $result .= "<tr><td><b>Doglan senesi we ýeri:</b></td><td>" . $student->getDataField('dob') . "</td></tr>";
        $result .= "<tr><td><b>Milleti:</b></td><td>" . $student->getDataField('nationality') . "</td></tr>";
        $result .= "<tr><td><b>Bilimi:</b></td><td>" . $student->getDataField('education') . "</td></tr>";
        $result .= "<tr><td><b>Haçan we haýsy okuw mekdebini tamamlady:</b></td><td>" . $student->getDataField('school') . "</td></tr>";
        $result .= "<tr><td><b>Bilimi boýunça hünäri:</b></td><td>" . $student->getDataField('profession') . "</td></tr>";
        $result .= "<tr><td><b>Alymlyk derejesi we ady:</b></td><td>" . $student->getDataField('degree') . "</td></tr>";
        $result .= "<tr><td><b>Haýsy daşary ýurt dillerini bilýär:</b></td><td>" . $student->getDataField('languages') . "</td></tr>";
        $result .= "<tr><td><b>Hökümet sylaglary:</b></td><td>" . $student->getDataField('awards') . "</td></tr>";
        $result .= "<tr><td><b>Daşary ýurtlarda bolmagy:</b></td><td>" . $student->getDataField('trips') . "</td></tr>";
        $result .= "<tr><td><b>Türkmenistanyň Mejlisiniň deputatymy:</b></td><td>" . $student->getDataField('mp') . "</td></tr>";
        $result .= "</table>";

        $result .= "<div align='center'  style='font-family:Times New roman, serif;'><b>IŞLÄN ÝERLERI</b></div><br>";
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        if (gettype($info['positions']) != 'array')
            $positions = json_decode($info['positions']);
        else
            $positions = json_decode(json_encode($info['positions']));
        foreach ($positions as $position) {
            $result .= "<tr><td><b>" . $position->{'period'} . "</b><td>" . $position->{"position"} . "</td></td></tr>";
        }
        $result .= "</table>";

        $result .= "<div align='center' style='font-family:Times New roman, serif;'><b>" . $student->getDataField('name') . "</b><br>";
        $result .= "üç arkasy, maşgala agzalary, özüniň hem-de ýanýoldaşynyň ýakyn dogan-garyndaşlary barada<br><b>maglumat</b></div>";
        $result .= "<table  class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr style='font-weight:bold;'><td>Familiýasy, ady, atasynyň ady</td><td>Garyndaşlyk derejesi</td><td>Doglan ýyly</td><td>Doglan ýeri</td><td>Işleýän (okaýan) ýeri we wezipesi</td><td>Ýaşaýan ýeri, öý salgysy</td><td>Kazyýet jogapkärçiligine çekilenmi?</td></tr>";

        if (gettype($info['relatives']) != 'array')
            $relatives = json_decode($info['relatives']);
        else
            $relatives = json_decode(json_encode($info['relatives']));
        foreach ($relatives as $r) {
            $result .= "<tr valign='top'><td>" . $r->{"name"} . "</td><td>" .
                    $r->{"relation"} . "</td><td>" . $r->{"dob"} . "</td><td>" . $r->{"pob"} . "</td><td>" .
                    $r->{"job"} . "</td><td>" . $r->{"address"} . "</td><td>" . $r->{"penalty"} . "</td></tr>";
        }
        $result .= "</table>";

        $result .= "<table class='table table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b>Öý salgysy:</b></td><td>" . $student->getDataField('address') . "</td></tr>";
        $result .= "<tr><td><b>Häzirki ýaşaýan salgysy:</b></td><td>" . $student->getDataField('address2') . "</td></tr>";
        $result .= "<tr><td><b>Telefon belgisi:</b></td><td>" . $student->getDataField('phone') . "</td></tr>";
        $result .= "</table>";

        return $result;
    }

    public function viewAlumnusStudentInfo(AlumnusStudent $student, $info) {
        $result = '';
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b></b></td><td><img src='/build/photos/" . $student->getGroupCode() . "/" . $student->getSystemId() . ".jpg' height='100'></td></tr>";
        $result .= "<tr><td><b>Raýat:</b></td><td>" . $student->getDataField('name') . "</td></tr>";
        $result .= "<tr><td><b>Işleýän ýeri we wezipesi:</b></td><td>" . $student->getDataField('position') . "</td></tr>";
        $result .= "<tr><td><b>Doglan senesi we ýeri:</b></td><td>" . $student->getDataField('dob') . "</td></tr>";
        $result .= "<tr><td><b>Milleti:</b></td><td>" . $student->getDataField('nationality') . "</td></tr>";
        $result .= "<tr><td><b>Bilimi:</b></td><td>" . $student->getDataField('education') . "</td></tr>";
        $result .= "<tr><td><b>Haçan we haýsy okuw mekdebini tamamlady:</b></td><td>" . $student->getDataField('school') . "</td></tr>";
        $result .= "<tr><td><b>Bilimi boýunça hünäri:</b></td><td>" . $student->getDataField('profession') . "</td></tr>";
        $result .= "<tr><td><b>Alymlyk derejesi we ady:</b></td><td>" . $student->getDataField('degree') . "</td></tr>";
        $result .= "<tr><td><b>Haýsy daşary ýurt dillerini bilýär:</b></td><td>" . $student->getDataField('languages') . "</td></tr>";
        $result .= "<tr><td><b>Hökümet sylaglary:</b></td><td>" . $student->getDataField('awards') . "</td></tr>";
        $result .= "<tr><td><b>Daşary ýurtlarda bolmagy:</b></td><td>" . $student->getDataField('trips') . "</td></tr>";
        $result .= "<tr><td><b>Türkmenistanyň Mejlisiniň deputatymy:</b></td><td>" . $student->getDataField('mp') . "</td></tr>";
        $result .= "</table>";

        $result .= "<div align='center'  style='font-family:Times New roman, serif;'><b>IŞLÄN ÝERLERI</b></div><br>";
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        if (gettype($info['positions']) != 'array')
            $positions = json_decode($info['positions']);
        else
            $positions = json_decode(json_encode($info['positions']));
        foreach ($positions as $position) {
            $result .= "<tr><td><b>" . $position->{'period'} . "</b><td>" . $position->{"position"} . "</td></td></tr>";
        }
        $result .= "</table>";

        $result .= "<div align='center' style='font-family:Times New roman, serif;'><b>" . $student->getDataField('name') . "</b><br>";
        $result .= "üç arkasy, maşgala agzalary, özüniň hem-de ýanýoldaşynyň ýakyn dogan-garyndaşlary barada<br><b>maglumat</b></div>";
        $result .= "<table  class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr style='font-weight:bold;'><td>Familiýasy, ady, atasynyň ady</td><td>Garyndaşlyk derejesi</td><td>Doglan ýyly</td><td>Doglan ýeri</td><td>Işleýän (okaýan) ýeri we wezipesi</td><td>Ýaşaýan ýeri, öý salgysy</td><td>Kazyýet jogapkärçiligine çekilenmi?</td></tr>";

        if (gettype($info['relatives']) != 'array')
            $relatives = json_decode($info['relatives']);
        else
            $relatives = json_decode(json_encode($info['relatives']));
        foreach ($relatives as $r) {
            $result .= "<tr valign='top'><td>" . $r->{"name"} . "</td><td>" .
                    $r->{"relation"} . "</td><td>" . $r->{"dob"} . "</td><td>" . $r->{"pob"} . "</td><td>" .
                    $r->{"job"} . "</td><td>" . $r->{"address"} . "</td><td>" . $r->{"penalty"} . "</td></tr>";
        }
        $result .= "</table>";

        $result .= "<table class='table table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b>Öý salgysy:</b></td><td>" . $student->getDataField('address') . "</td></tr>";
        $result .= "<tr><td><b>Häzirki ýaşaýan salgysy:</b></td><td>" . $student->getDataField('address2') . "</td></tr>";
        $result .= "<tr><td><b>Telefon belgisi:</b></td><td>" . $student->getDataField('phone') . "</td></tr>";
        $result .= "</table>";

        return $result;
    }

//put your code here
    public function viewEmployeeInfo(Employee $employee, $info) {
        $result = '';
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b></b></td><td><img src='/build/employee_photos/" . $employee->getSystemId() . ".jpg' height='100'></td></tr>";
        $result .= "<tr><td><b>Raýat:</b></td><td>" . $info['name'] . "</td></tr>";
        $result .= "<tr><td><b>Işleýän ýeri we wezipesi:</b></td><td>" . $info['position'] . "</td></tr>";
        $result .= "<tr><td><b>Doglan senesi we ýeri:</b></td><td>" . $info['dob'] . "</td></tr>";
        $result .= "<tr><td><b>Milleti:</b></td><td>" . $info['nationality'] . "</td></tr>";
        $result .= "<tr><td><b>Bilimi:</b></td><td>" . $info['education'] . "</td></tr>";
        $result .= "<tr><td><b>Haçan we haýsy okuw mekdebini tamamlady:</b></td><td>" . $info['school'] . "</td></tr>";
        $result .= "<tr><td><b>Bilimi boýunça hünäri:</b></td><td>" . $info['profession'] . "</td></tr>";
        $result .= "<tr><td><b>Alymlyk derejesi we ady:</b></td><td>" . $info['degree'] . "</td></tr>";
        $result .= "<tr><td><b>Haýsy daşary ýurt dillerini bilýär:</b></td><td>" . $info['languages'] . "</td></tr>";
        $result .= "<tr><td><b>Hökümet sylaglary:</b></td><td>" . $info['awards'] . "</td></tr>";
        $result .= "<tr><td><b>Daşary ýurtlarda bolmagy:</b></td><td>" . $info['trips'] . "</td></tr>";
        $result .= "<tr><td><b>Türkmenistanyň Mejlisiniň deputatymy:</b></td><td>" . $info['mp'] . "</td></tr>";
        $result .= "</table>";

        $result .= "<div align='center'  style='font-family:Times New roman, serif;'><b>IŞLÄN ÝERLERI</b></div><br>";
        $result .= "<table class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        if (gettype($info['positions']) != 'array')
            $positions = json_decode($info['positions']);
        else
            $positions = json_decode(json_encode($info['positions']));
        foreach ($positions as $position) {
            $result .= "<tr><td><b>" . $position->{'period'} . "</b><td>" . $position->{"position"} . "</td></td></tr>";
        }
        $result .= "</table>";

        $result .= "<div align='center' style='font-family:Times New roman, serif;'><b>" . $info['name'] . "</b><br>";
        $result .= "üç arkasy, maşgala agzalary, özüniň hem-de ýanýoldaşynyň ýakyn dogan-garyndaşlary barada<br><b>maglumat</b></div>";
        $result .= "<table  class='table table-bordered table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr style='font-weight:bold;'><td>Familiýasy, ady, atasynyň ady</td><td>Garyndaşlyk derejesi</td><td>Doglan ýyly</td><td>Doglan ýeri</td><td>Işleýän (okaýan) ýeri we wezipesi</td><td>Ýaşaýan ýeri, öý salgysy</td><td>Kazyýet jogapkärçiligine çekilenmi?</td></tr>";

        if (gettype($info['relatives']) != 'array')
            $relatives = json_decode($info['relatives']);
        else
            $relatives = json_decode(json_encode($info['relatives']));
        foreach ($relatives as $r) {
            $result .= "<tr valign='top'><td>" . $r->{"name"} . "</td><td>" .
                    $r->{"relation"} . "</td><td>" . $r->{"dob"} . "</td><td>" . $r->{"pob"} . "</td><td>" .
                    $r->{"job"} . "</td><td>" . $r->{"address"} . "</td><td>" . $r->{"penalty"} . "</td></tr>";
        }
        $result .= "</table>";

        $result .= "<table class='table table-sm' style='font-family:Times New roman, serif;'>";
        $result .= "<tr><td><b>Öý salgysy:</b></td><td>" . $info['address'] . "</td></tr>";
        $result .= "<tr><td><b>Häzirki ýaşaýan salgysy:</b></td><td>" . $info['address2'] . "</td></tr>";
        $result .= "<tr><td><b>Telefon belgisi:</b></td><td>" . $info['phone'] . "</td></tr>";
        $result .= "</table>";

        return $result;
    }

}
