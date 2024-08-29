<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use App\Service\StudentAbsenceManager;
use App\Service\TeacherAttendanceManager;
use App\Entity\ProgramCourse;
use App\Entity\TaughtCourse;
use App\Entity\ScheduleItem;
use App\Entity\Group;
use App\Entity\Teacher;
use App\Entity\Room;
use App\Entity\EnrolledStudent;
use App\Entity\ClassType;
use App\Entity\StudentAbsence;
use App\Entity\Faculty;
use App\Entity\Department;
use App\Entity\TeacherAttendance;
use App\Entity\TeacherWorkItem;
use App\Entity\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DataEditorController extends AbstractController {

    /**
     * @Route("/custom/editor/index", name="custom_editor_index")
     */
    public function index(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        return $this->render('custom/editor.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => '',
        ]);
    }

    /**
     * @Route("/custom/tableeditor/importform", name="custom_tableeditor_importform")
     */
    public function importForm(Request $request) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $content = "";
        $content .= "<h3>Table Editor Import Form</h3>";
        $content .= "<form name='mainform' id='mainform'>";
        $content .= "<textarea id='import_data' name='import_data' style='width:100%;height: 300px;'></textarea><br>";
        $content .= "<input type='text' id='import_type' name='import_type' value='0'>&nbsp;&nbsp;&nbsp;&nbsp;";
        $content .= "<input type='text' id='table' name='table' value='enrolled_student'><br>";
        $content .= "</form>";
        $content .= "<button onclick='importData(0);'>Simulate</button>&nbsp;&nbsp;&nbsp;&nbsp;";
        $content .= "<button onclick='importData(1);'>Import</button>&nbsp;&nbsp;&nbsp;&nbsp;";

        return $this->render('custom/editor.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => $content,
        ]);
    }

    /**
     * @Route("/custom/tableeditor/importdata", name="custom_tableeditor_importdata")
     */
    public function importData(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $type = ['testing', 'updating'];
        $import_type = intval($request->request->get('import_type'));
        $table = $request->request->get('table');
        $data = $request->request->get('import_data');
        $currentImportType = $type[$import_type];
        $content = "<br>PROCESSING DATA .." . $currentImportType . "<p>";
        //try {
        $rows = explode("\n", $data);
        $fieldNames = explode("\t", $rows[0]);
        $fieldNamesArray = explode("|", $fieldNames[0]);
        $id_field = $fieldNamesArray[0];
        $content .= "<table class='table table-striped table-compact'>";
        $content .= "<thead>";
        $content .= "<tr>";
        foreach ($fieldNames as $fieldName) {
            $content .= "<th>" . $fieldName . "</th>";
        }
        $content .= "<tr>";
        $content .= "</thead>";
        $content .= "<tbody>";
        $rowCount = sizeof($rows);
        $repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        for ($i = 1; $i < $rowCount; $i++) {
            if (strlen($rows[$i]) > 0) {
                $fieldValues = explode("\t", $rows[$i]);
//                $sql = "SELECT * FROM " . $table . " WHERE " . $id_field . "='" . $fieldValues[0] . "';";
//                echo $sql . "<br>";
//                $stmt = $connection->prepare($sql);
//                $stmt->execute();
//                $dataRow = $stmt->fetch();
                $entity = $repository->findOneBy([$id_field => $fieldValues[0]]);
                $content .= "<tr>";
                $columnNumber = 0;
                foreach ($fieldNames as $fieldName) {
                    $fieldNameArray = explode("|", $fieldName);
                    if ($fieldNameArray[1] == '0') {
                        $fieldNameCombinedItems = explode(",", $fieldNameArray[0]);
                        $content .= "<td>";
                        foreach ($fieldNameCombinedItems as $item) {
                            $content .= $entity->getFieldGeneral($item) . " ";
                        }
                        $content .= "</td>";
                    } else {
////                        echo $fieldNameArray[0] . "--->";
////                        echo $fieldNameArray[1] . "--->";
////                        echo sizeof($dataRow) . "--->";
//                        if (array_key_exists($fieldNameArray[1], $dataRow)) {
////                            echo $dataRow[$fieldNameArray[1]];
////                            foreach ($dataRow as $col => $val) {
////                                echo $col . ", ";
////                            }
////                            echo "<br>";
//                            $jsonData = json_decode($dataRow[$fieldNameArray[1]], true);
//                            $jsonItemValue = $this->getDataField($jsonData, $fieldNameArray[0]);
                        $content .= "<td>" . $entity->getFieldGeneral($fieldNameArray[0]) . "</td>";
//                            $jsonNewData = json_encode($this->setDataField($jsonData, $fieldNameArray[0], $fieldValues[$columnNumber]));
//                            $sqlUpdate = "UPDATE " . $table . " SET " . $fieldNameArray[1] . " = '" . $jsonNewData . "' ";
//                            $sqlUpdate .= "WHERE " . $id_field . "='" . $fieldValues[0] . "';";
//                            if (!$import_type) {
//                                echo $sqlUpdate . "<br>";
//                            }
                        $entity->setFieldGeneral($fieldNameArray[0], $fieldValues[$columnNumber]);
                    }
                    $columnNumber++;
                }
            }
            if ($import_type) {
                $repository->save($entity);
            }
        }
        $content .= "</tr>";

        $content .= "<tr>";
        foreach ($fieldValues as $fieldValue) {
            $content .= "<td>" . $fieldValue . "</td>";
        }
        $content .= "</tr>";


        $content .= "</tbody>";
        $content .= "</table>";
        // } catch (\Exception $ex) {
        //     $content .= "Error: " . $ex->getMessage() . " File:" . $ex->getFile() . " Line:" . $ex->getLine();
        // }

        return new Response($content);
    }

    private function getDataField($jsonData, ?string $fieldName): ?string {
        if (array_key_exists($fieldName, $jsonData)) {
//            echo "Key exists: " . $fieldName;
//            echo " Key value:" . $jsonData[$fieldName];
//            echo " Key value:" . json_decode($jsonData[$fieldName]);
//            echo "<br>";
            if (json_decode($jsonData[$fieldName]) == null || json_decode($jsonData[$fieldName]) == 'null') {
                if ($jsonData[$fieldName] == 'null') {
                    return "";
                } else {
                    return $jsonData[$fieldName];
                }
            } else {
                return json_decode($jsonData[$fieldName]);
            }
        } else {
//            echo "Key does not exist: " . $fieldName;
//            echo "<br>";
            return "none";
        }
    }

    private function setDataField($jsonData, $column, $value) {
        $jsonResult = $jsonData;
        if (array_key_exists($column, $jsonResult)) {
            $jsonResult[$column] = json_encode($value);
            //$jsonResult[$column] = $value;
        } else {
            $jsonResult += array($column => json_encode($value));
            //$jsonResult += array($column => $value);
        }
        return $jsonResult;
    }

    private function setDataFieldNew($jsonData, $column, $value) {
        $jsonResult = $jsonData;
        if (array_key_exists($column, $jsonResult)) {
            $jsonResult[$column] = $value;
            //$jsonResult[$column] = $value;
        } else {
            $jsonResult += array($column => json_encode($value));
            //$jsonResult += array($column => $value);
        }
        return $jsonResult;
    }

    /**
     * @Route("/custom/tableeditor/{table}/{condition}/{fields?}/{orderby?}/{mode?0}", name="custom_tableeditor")
     */
    public function tableEditor(Request $request, Connection $connection) {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
        $table = $request->attributes->get('table');
        $condition = $request->attributes->get('condition');
        $fieldsString = $request->attributes->get('fields');
        $orderBy = $request->attributes->get('orderby');
        $mode = $request->attributes->get('mode');
        $content = '';
        //$repository = $this->getDoctrine()->getRepository(EnrolledStudent::class);
        //$students = $repository->findBy([$selectorField => $selectorValue]);
        $sql = "SELECT * FROM `" . $table . "` WHERE " . $condition . " ORDER BY " . $orderBy;
        $rows = $connection->fetchAll($sql);

        $content .= "<h5>" . $table . ", " . $condition . "</h5>";
        $content .= "<button onclick='saveData();'>Save</button><br>";
        $content .= "<form name='mainform' id='mainform'>";
        $inputType = 'hidden';
        $content .= "<input type='$inputType' name='table' value='" . $table . "' size=200><br>";
        $content .= "<input type='$inputType' name='fields' value='" . $fieldsString . "' size=200><br>";
        $content .= "<table class='table table-sm'>";
        $i = 1;
        $fields = explode(",", $fieldsString);
        $content .= '<tr>';
        $content .= "<th>#</th>";
        $content .= "<th>id</th>";
        foreach ($fields as $field) {
            $content .= "<th>" . $field . "</th>";
        }
        $content .= '</tr>';
        foreach ($rows as $row) {
            $content .= "<tr>";
            $content .= "<td>" . $i . "</td>";
            $content .= "<td>";
            if ($mode == 1) {
                $content .= "<span>" . $row['id'] . "</span>";
            } else {
                $content .= "<input size='6' type='text' name='id[]' value='" . $row['id'] . "'>";
            }
            $content .= "</td>";
            foreach ($fields as $field) {
                $content .= "<td>";
                if ($field == 'region') {
                    $content .= $this->makeDropdown($field, $row[$field]);
                } else {
                    if ($mode == 1) {
                        $content .= $row[$field];
                    } else {
                        $content .= "<textarea class='data' name='" . $field . "[]'>";
                        $content .= $row[$field];
                        $content .= "</textarea>";
                    }
                }
                $content .= "</td>";
            }
            if ($table == 'taught_course') {
                $content .= "<td>";
                $jsonData = json_decode($row['data'], true);
                if ($mode == 1) {
                    $content .= $this->getDataField($jsonData, 'course_name');
                } else {
                    $content .= "<textarea class='datawide' name='course_name_special[]'>";
                    $content .= $this->getDataField($jsonData, 'course_name');
                    $content .= "</textarea>";
                }
                $content .= "</td>";
            }
            $content .= "</tr>";
            $i++;
        }
        $content .= "</table>";
        $content .= "</form>";
        $content .= "<button onclick='saveData();'>Save</button>";
        return $this->render('custom/editor.html.twig', [
                    'controller_name' => 'AttendanceController',
                    'content' => $content,
        ]);
    }

    private function makeDropdown($field, $selectedValue) {
        $regions = [
            'Ýok',
            'Ahal',
            'Balkan',
            'Daşoguz',
            'Lebap',
            'Mary',
            'Aşgabat',
            'Daşary ýurt'
        ];

        $result = "<select name='" . $field . "[]'>";
        $i = 0;
        foreach ($regions as $region) {
            if ($selectedValue == $i) {
                $result .= "<option value=" . $i . " selected>" . $region . "</option>";
            } else {
                $result .= "<option value=" . $i . ">" . $region . "</option>";
            }
            $i++;
        }
        $result .= "</select>";
        return $result;
    }

    private function makeRadio($field, $selectedValue) {
        $regions = [
            'AH',
            'BN',
            'DZ',
            'LB',
            'MR',
            'AG',
            'ZZ'
        ];

        $result = "";
        $i = 0;
        foreach ($regions as $region) {
            if ($selectedValue == $i) {
                $result .= "<input type=radio name='" . $field . "[]' value=" . $i . " checked='checked'> ";
                $result .= " " . $region . " ";
            } else {
                $result .= "<input type=radio name='" . $field . "[]' value=" . $i . "> ";
                $result .= " " . $region . " ";
            }
            $i++;
        }
        $result .= "";
        return $result;
    }

    /**
     * @Route("/custom/tableeditor/update", name="custom_tableeditor_update")
     */
    public function update(Request $request, Connection $connection) {
        $table = $request->request->get('table');
        $fields = $request->request->get('fields');
        $ids = $request->request->get('id');
        $content = '';
        $fieldsList = explode(",", $fields);
        $i = 0;
        $taughtCourseRepository = $this->getDoctrine()->getRepository(TaughtCourse::class);
        foreach ($ids as $id) {
            $fieldPairs = [];
            $query = "UPDATE `" . $table . "` SET ";
            foreach ($fieldsList as $field) {
                $fieldValue = $request->request->get($field);
                $fieldPairs[] = $field . "='" . $fieldValue[$i] . "'";
            }
            if ($table == 'taught_course') {
                $courseNames = $request->request->get("course_name_special");
                $taughtCourse = $taughtCourseRepository->find($id);
                $taughtCourse->setDataField("course_name", $courseNames[$i]);
                $taughtCourseRepository->save($taughtCourse);
                $content .= "course updated = " . $courseNames[$i]."<br>";
            }
            $query .= join(", ", $fieldPairs);
            $query .= " WHERE id=" . $id . ";";
            try {
                $stmt = $connection->prepare($query);
                $result = $stmt->execute();
                $content .= $query . " Result = " . $result . " <br>";
            } catch (Exception $ex) {
                $content .= $query . " Result = " . $ex->getMessage() . " <br>";
            }
            $i++;
        }

        return new Response($content);
    }

}
