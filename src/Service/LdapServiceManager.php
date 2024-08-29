<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

/**
 * Description of LdapServiceManager
 *
 * @author NMammedov
 */
class LdapServiceManager {

//put your code here
    public function changeUserPassword($username, $newPassword) {
        $result = "";

        $ldap = ldap_connect($_SERVER['APP_AD_SERVER']);
        $ldaprdn = $_SERVER['APP_AD_DOMAIN'] . "\\" . $_SERVER['APP_AD_USER'];
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

        $bind = @ldap_bind($ldap, $ldaprdn, $_SERVER['APP_AD_PASSWORD']);
        if ($bind) {
//$searchResults = ldap_search($ldap, 'dc=UNI.TM', 'CN=MNazarov');
            $attributes_ad = array("cn");
            $searchResults = ldap_search($ldap, 'dc=uni,dc=tm', '(sAMAccountName=' . $username . ')', $attributes_ad);
//            echo ldap_error($ldap) . "<br>";
//            echo "Search results:" . $searchResults . "<br>";
            $info = ldap_get_entries($ldap, $searchResults);
//            echo $info["count"] . " entries returned\n";
//            print_r($info);
            $entry = ldap_first_entry($ldap, $searchResults);
//            echo "Entry:" . $entry . "<br>";
//            echo ldap_error($ldap) . "<br>";
            $userDn = ldap_get_dn($ldap, $entry);
//            echo ldap_error($ldap) . "<br>";
            $userdata['unicodePwd'] = $this->convertPasswordToActiveDirectory($newPassword);
            try {
                $queryResult = ldap_mod_replace($ldap, $userDn, $userdata);
                if ($queryResult) {
                    $result .= "Password changed!";
                } else {
                    $result .= "There was a problem: " . ldap_error($ldap);
                }
            } catch (Exception $e) {
                $result .= "There was a problem: " . $e->getMessage();
            } finally {
                ldap_close($ldap);
            }
        }

        return $result;
    }

    private function convertPasswordToActiveDirectory($pw) {
        return iconv("UTF-8", "UTF-16LE", '"' . $pw . '"');
    }

//put your code here
    public function getComputerInfo($computerName) {
        $result = "";

        $ldap = ldap_connect($_SERVER['APP_AD_SERVER']);
        $ldaprdn = $_SERVER['APP_AD_DOMAIN'] . "\\" . $_SERVER['APP_AD_USER'];
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

        $bind = @ldap_bind($ldap, $ldaprdn, $_SERVER['APP_AD_PASSWORD']);
        if ($bind) {
//$searchResults = ldap_search($ldap, 'dc=UNI.TM', 'CN=MNazarov');
            $attributes = array("cn", "operatingsystem", "badpasswordtime", "lastlogon", "logoncount", "operatingsystemservicepack", "operatingsystemversion");
            $searchResults = ldap_search($ldap, 'OU=Classrooms,OU=UNI,DC=UNI,DC=tm', '(objectClass=computer)', $attributes);
//            echo ldap_error($ldap) . "<br>";
//            echo "Search results:" . $searchResults . "<br>";
            $entries = ldap_get_entries($ldap, $searchResults);
//            echo $info["count"] . " entries returned\n";
//            print_r($info);
            $entry = ldap_first_entry($ldap, $searchResults);
//            echo "Entry:" . $entry . "<br>";
//            echo ldap_error($ldap) . "<br>";
            $today = new \DateTime();
            try {
                if ($entries["count"] > 0) {
                    $result .= "<table id='mainTable' class='table table-compact table-bordered'>";
                    $result .= "<thead><tr>";
                    $result .= "<th>Tag</th>";
                    $result .= "<th>Operating System</th>";
                    $result .= "<th>Version</th>";
                    $result .= "<th>Service Pack</th>";
                    $result .= "<th>Logon count</th>";
                    $result .= "<th>Last Logon</th>";
                    $result .= "<th>Last Logon Days Ago</th>";
                    $result .= "<th>Bad password time</th>";
                    $result .= "</tr></thead>";
                    $result .= "<tbody>";
                    for ($i = 0; $i < $entries["count"]; $i++) {
                        $result .= "<tr>";
                        $result .= "<td>" . $entries[$i]["cn"][0] . "</td>";
                        $result .= "<td>" . $entries[$i]["operatingsystem"][0] . "</td>";
                        $result .= "<td>" . $entries[$i]["operatingsystemservicepack"][0] . "</td>";
                        $result .= "<td>" . $entries[$i]["operatingsystemversion"][0] . "</td>";
                        $result .= "<td>" . $entries[$i]["logoncount"][0] . "</td>";
                        $lastLogonDate = $this->ldapTimeToUnixTime($entries[$i]["lastlogon"][0]);
                        $interval = $lastLogonDate->diff($today);
                        $result .= "<td>" . $this->ldapTimeToUnixTime($entries[$i]["lastlogon"][0])->format('Y-m-d H:i:s') . "</td>";
                        $result .= "<td>" . $interval->format('%R%a') . "</td>";
                        if (isset($entries[$i]["badpasswordtime"])) {
                            $result .= "<td>" . $this->ldapTimeToUnixTime($entries[$i]["badpasswordtime"][0])->format('Y-m-d H:i:s') . "</td>";
                        } else {
                            $result .= "<td></td>";
                        }
                        $result .= "</tr>";
                    }
                    $result .= "</tbody></table>";
                }
            } catch (Exception $e) {
                $result .= "There was a problem: " . $e->getMessage();
            } finally {
                ldap_close($ldap);
            }
        }

        return $result;
    }

    private function ldapTimeToUnixTime($ldapTime) {
        $winSecs = (int) ($ldapTime / 10000000); // divide by 10 000 000 to get seconds
        $unixTimestamp = ($winSecs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds
        $date = new \DateTime();
        $date->setTimestamp($unixTimestamp);
        return $date;
    }

}
