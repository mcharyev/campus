<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Enum;

/**
 * Description of ClassroomTypeEnum
 *
 * @author nazar
 */
class EntityTypeEnum extends TypeEnum {

    //put your code here
    const ENTITY_NULL = 0;
    const ENTITY_USER = 1;
    const ENTITY_TEACHER = 2;
    const ENTITY_FACULTY = 3;
    const ENTITY_DEPARTMENT = 4;
    const ENTITY_GROUP = 5;
    const ENTITY_ENROLLEDSTUDENT = 6;
    const ENTITY_EXPELLEDSTUDENT = 7;
    const ENTITY_ALUMNUSSTUDENT = 8;
    const ENTITY_STUDYPROGRAM = 9;
    const ENTITY_PROGRAMCOURSE = 10;
    const ENTITY_EMPLOYEE = 11;
    const ENTITY_TAUGHTCOURSE = 12;
    const ENTITY_SCHEDULEITEM = 13;
    const ENTITY_SCHEDULE = 14;
    const ENTITY_STUDENTABSENCE = 15;
    const ENTITY_TEACHERATTENDANCE = 16;
    const ENTITY_CLASSROOM = 17;
    const ENTITY_MOVEMENT = 18;
    const ENTITY_LIBRARYITEM = 19;
    const ENTITY_TEACHERWORKITEM = 20;
    const ENTITY_DEPARTMENTWORKITEM = 21;
    const ENTITY_REPORTEDWORK = 22;

    /** @var array user friendly named type */
    protected static $typeName = [
        self::ENTITY_NULL => 'Null',
        self::ENTITY_USER => 'User',
        self::ENTITY_TEACHER => 'Teacher',
        self::ENTITY_FACULTY => 'Faculty',
        self::ENTITY_DEPARTMENT => 'Department',
        self::ENTITY_GROUP => 'Group',
        self::ENTITY_ENROLLEDSTUDENT => 'Enrolled student',
        self::ENTITY_EXPELLEDSTUDENT => 'Expelled student',
        self::ENTITY_ALUMNUSSTUDENT => 'Alumnus',
        self::ENTITY_STUDYPROGRAM => 'Study program',
        self::ENTITY_PROGRAMCOURSE => 'Program course',
        self::ENTITY_EMPLOYEE => 'Employee',
        self::ENTITY_TAUGHTCOURSE => 'Taught course',
        self::ENTITY_SCHEDULEITEM => 'Schedule item',
        self::ENTITY_SCHEDULE => 'Schedule',
        self::ENTITY_STUDENTABSENCE => 'Student absence',
        self::ENTITY_TEACHERATTENDANCE => 'Teacher attendance',
        self::ENTITY_CLASSROOM => 'Classroom',
        self::ENTITY_MOVEMENT => 'Movement',
        self::ENTITY_LIBRARYITEM => 'Library item',
        self::ENTITY_TEACHERWORKITEM => 'Teacher Work item',
        self::ENTITY_DEPARTMENTWORKITEM => 'Department Work item',
        self::ENTITY_REPORTEDWORK => 'Reported work'
    ];

}
